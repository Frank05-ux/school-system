<?php
include('db.php');

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=payments.xls");

$result = mysqli_query($conn, "
SELECT students.full_name, payments.amount, payments.mpesa_code, payments.status, payments.created_at
FROM payments
JOIN students ON payments.student_id = students.id
");

echo "Student\tAmount\tCode\tStatus\tDate\n";

while($row = mysqli_fetch_assoc($result)) {
    echo "{$row['full_name']}\t{$row['amount']}\t{$row['mpesa_code']}\t{$row['status']}\t{$row['created_at']}\n";
}
?>
