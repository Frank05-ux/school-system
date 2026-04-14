<?php
include('db.php');

$id = $_GET['id'];

$p = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT payments.*, students.full_name 
FROM payments 
JOIN students ON payments.student_id = students.id
WHERE payments.id=$id
"));
?>

<h2>Kiharu College Receipt</h2>

<p>Name: <?= $p['full_name'] ?></p>
<p>Amount: KES <?= $p['amount'] ?></p>
<p>M-Pesa Code: <?= $p['mpesa_code'] ?></p>
<p>Status: <?= $p['status'] ?></p>
<p>Date: <?= $p['created_at'] ?></p>

<button onclick="window.print()">Print</button>
