<?php
include('db.php');

/* =========================
   GET PAYMENT DATA
========================= */
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['transaction_id'], $data['amount'], $data['tx_ref'])) {
    http_response_code(400);
    exit("❌ Invalid payment data");
}

$tx_id  = $data['transaction_id'];
$amount = (float)$data['amount'];
$tx_ref = trim($data['tx_ref']);

/* =========================
   VERIFY PAYMENT (OPTIONAL)
========================= */
$secret_key = "FLWSECK_TEST-xxxxxxxxxxxx";

$ch = curl_init("https://api.flutterwave.com/v3/transactions/$tx_id/verify");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $secret_key",
        "Content-Type: application/json"
    ]
]);

$response = curl_exec($ch);
$result = json_decode($response, true);

if (!$result || $result['status'] !== "success") {
    http_response_code(400);
    exit("❌ Payment verification failed");
}

/* =========================
   GET STUDENT (IMPORTANT)
========================= */
/* Ideally match via tx_ref or metadata */
$student_id = 1; // TODO: replace with real mapping

// Get student phone
$student = $conn->query("
SELECT s.phone 
FROM students s 
WHERE s.id = $student_id
")->fetch_assoc();

$phone = $student['phone'] ?? '254700000000';

/* =========================
   SAVE PAYMENT
========================= */
$stmt = $conn->prepare("
INSERT INTO payments 
(student_id, amount, phone, mpesa_code, status) 
VALUES (?, ?, ?, ?, 'APPROVED')
");

$stmt->bind_param("idss", $student_id, $amount, $phone, $tx_ref);
$stmt->execute();

/* =========================
   UPDATE STUDENT BALANCE
========================= */
$conn->query("
UPDATE students 
SET balance = balance - $amount 
WHERE id = $student_id
");

/* =========================
   UPDATE INVOICE (SAFE)
========================= */
$conn->query("
UPDATE invoices 
SET status = 'PAID'
WHERE student_id = $student_id 
AND status = 'UNPAID'
LIMIT 1
");

/* =========================
   SEND SMS (KENYA READY)
========================= */
function sendSMS($phone, $message) {
    $username = "sandbox"; 
    $apiKey = "YOUR_API_KEY";

    $ch = curl_init("https://api.africastalking.com/version1/messaging");

    curl_setopt_array($ch, [
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query([
            "username" => $username,
            "to" => $phone,
            "message" => $message
        ]),
        CURLOPT_HTTPHEADER => [
            "apiKey: $apiKey"
        ],
        CURLOPT_RETURNTRANSFER => true
    ]);

    $response = curl_exec($ch);

    return $response;
}

$message = "Payment received: KES $amount. Ref: $tx_ref";
sendSMS($phone, $message);

/* =========================
   LOG SMS
========================= */
$stmt = $conn->prepare("
INSERT INTO sms_logs (phone, message, status) 
VALUES (?, ?, 'SENT')
");
$stmt->bind_param("ss", $phone, $message);
$stmt->execute();

/* =========================
   NOTIFY ADMINS
========================= */
$res = $conn->query("SELECT id FROM users WHERE role='admin'");

while ($admin = $res->fetch_assoc()) {

    $msg = "💰 New payment: KES $amount (Ref: $tx_ref)";

    $stmt = $conn->prepare("
    INSERT INTO notifications (user_id, message) 
    VALUES (?, ?)
    ");

    $stmt->bind_param("is", $admin['id'], $msg);
    $stmt->execute();
}

/* =========================
   RESPONSE
========================= */
echo json_encode([
    "status" => "success",
    "message" => "✅ Payment processed successfully"
]);
