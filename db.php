<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "kiharu_portal";

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
?>
