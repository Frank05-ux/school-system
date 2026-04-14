<?php
session_start();
include('db.php');

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM notifications 
    WHERE user_id = ? AND is_read = 0
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

echo $res['total'];
