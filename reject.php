<?php
session_start();
include('db.php');

if ($_SESSION['role'] !== 'admin') exit("Unauthorized");

$id = (int) $_GET['id'];

// Reject
$conn->query("
UPDATE payments 
SET status='REJECTED', verified_by=".$_SESSION['user_id'].", verified_at=NOW()
WHERE id=$id
");

header("Location: pending_payments.php");
