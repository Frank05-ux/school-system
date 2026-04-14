<?php
session_start();
include('db.php');

if ($_SESSION['role'] !== 'admin') exit("Unauthorized");

$id = (int) $_GET['id'];

// Get payment
$p = $conn->query("SELECT * FROM payments WHERE id=$id")->fetch_assoc();

if (!$p) exit("Payment not found");

$student_id = $p['student_id'];
$amount = $p['amount'];

// Approve payment
$conn->query("
UPDATE payments 
SET status='APPROVED', verified_by=".$_SESSION['user_id'].", verified_at=NOW()
WHERE id=$id
");

// Update student balance
$conn->query("
UPDATE students 
SET balance = balance - $amount
WHERE id=$student_id
");

// Notify student
$conn->query("
INSERT INTO notifications (user_id, message)
SELECT user_id, '✅ Your payment of KES $amount has been approved'
FROM students WHERE id=$student_id
");

header("Location: pending_payments.php");
