<?php
session_start();
include('db.php');

/* =========================
   AUTH CHECK (ADMIN ONLY)
========================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

/* =========================
   FETCH PENDING PAYMENTS
========================= */
$query = "
SELECT p.id, p.amount, p.mpesa_code, p.created_at,
       u.full_name, s.phone
FROM payments p
JOIN students s ON p.student_id = s.id
JOIN users u ON s.user_id = u.id
WHERE p.status='PENDING'
ORDER BY p.created_at DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Pending Payments</title>

<style>
body {font-family:Poppins;background:#f1f5f9;padding:20px;}

h2 {margin-bottom:15px;}

table {
    width:100%;
    border-collapse:collapse;
    background:#fff;
}

th, td {
    padding:12px;
    border-bottom:1px solid #ddd;
    text-align:left;
}

th {
    background:#4f46e5;
    color:#fff;
}

tr:hover {background:#f9fafb;}

.btn {
    padding:6px 12px;
    text-decoration:none;
    border-radius:6px;
    color:#fff;
}

.approve {background:green;}
.reject {background:red;}
</style>

</head>
<body>

<h2>💰 Pending Payments</h2>

<table>
<tr>
    <th>Student</th>
    <th>Phone</th>
    <th>Amount (KES)</th>
    <th>M-Pesa Code</th>
    <th>Date</th>
    <th>Action</th>
</tr>

<?php if ($result && $result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['full_name']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= number_format($row['amount'], 2) ?></td>
        <td><?= htmlspecialchars($row['mpesa_code']) ?></td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td>
            <a class="btn approve" href="approve_payment.php?id=<?= $row['id'] ?>">Approve</a>
            <a class="btn reject" href="reject_payment.php?id=<?= $row['id'] ?>">Reject</a>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="6">No pending payments</td>
</tr>
<?php endif; ?>

</table>

</body>
</html>
