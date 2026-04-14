<?php
header('Content-Type: application/json');

// Get the user message
$data = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($data['message'] ?? '');

if (empty($userMessage)) {
    echo json_encode(['reply' => 'I didn\'t catch that. Could you please repeat?']);
    exit;
}

// FAQ Logic (Instant responses)
$reply = "";
$msg = strtolower($userMessage);

if (str_contains($msg, 'fee') || str_contains($msg, 'pay')) {
    $reply = "You can pay fees via M-Pesa Paybill 522522. View your detailed statement in the Student Portal.";
} elseif (str_contains($msg, 'admission') || str_contains($msg, 'apply')) {
    $reply = "Admissions for the upcoming 2025 intake are currently open! You can enquire via the contact form.";
} elseif (str_contains($msg, 'units') || str_contains($msg, 'register')) {
    $reply = "Unit registration is available inside the Student Portal under the 'Courses' section.";
} elseif (str_contains($msg, 'hello') || str_contains($msg, 'hi')) {
    $reply = "Hello! I am the Kiharu College Assistant. How can I help you with your academics today?";
} else {
    // Default fallback
    $reply = "That's a great question! For specific details on that, please contact our support team at 0740 200 024 or visit the main campus.";
}

echo json_encode(['reply' => $reply]);
exit;