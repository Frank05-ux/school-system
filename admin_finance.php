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

function getValue($conn, $sql) {
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_row();
        return $row[0] ?? 0;
    }
    return 0;
}

$name = $_SESSION['name'] ?? 'Admin';

// Finance Stats
$total_revenue = getValue($conn, "SELECT SUM(amount) FROM payments WHERE status='APPROVED'");
$today_revenue = getValue($conn, "SELECT SUM(amount) FROM payments WHERE DATE(created_at) = CURDATE() AND status='APPROVED'");
$pending_count = getValue($conn, "SELECT COUNT(id) FROM payments WHERE status='PENDING'");
$approved_count = getValue($conn, "SELECT COUNT(id) FROM payments WHERE status='APPROVED'");
$rejected_count = getValue($conn, "SELECT COUNT(id) FROM payments WHERE status='REJECTED'");

// Recent Transactions
$recent_payments = $conn->query("
    SELECT p.*, u.full_name 
    FROM payments p 
    JOIN students s ON p.student_id = s.id 
    JOIN users u ON s.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Finance Dashboard | Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #4f46e5;
    --primary-dark: #4338ca;
    --bg: #f1f5f9;
    --sidebar: #0f172a;
    --text-main: #1e293b;
    --success: #22c55e;
    --warning: #f59e0b;
    --danger: #ef4444;
}
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
body { display: flex; min-height: 100vh; background: var(--bg); color: var(--text-main); }

.sidebar { width: 260px; background: var(--sidebar); color: #fff; padding: 25px 20px; position: fixed; height: 100vh; }
.sidebar h2 { margin-bottom: 30px; font-size: 22px; font-weight: 600; text-align: center; }
.sidebar a { display: block; color: #94a3b8; padding: 12px 15px; border-radius: 10px; text-decoration: none; margin-bottom: 8px; transition: 0.3s; }
.sidebar a:hover, .sidebar a.active { background: var(--primary); color: #fff; }

.main { flex: 1; margin-left: 260px; width: calc(100% - 260px); }
.topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }

.content { padding: 30px; }

.cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
.card { background: #fff; padding: 20px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); text-align: center; border: 1px solid #e2e8f0; }
.card h3 { font-size: 22px; color: var(--primary); margin-bottom: 5px; }
.card p { color: #64748b; font-size: 13px; font-weight: 500; }

.box { background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
.box h3 { margin-bottom: 20px; font-size: 18px; color: var(--sidebar); }

table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
th { color: #64748b; font-weight: 600; }

.status { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.APPROVED { background: rgba(34, 197, 94, 0.1); color: var(--success); }
.PENDING { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
.REJECTED { background: rgba(239, 68, 68, 0.1); color: var(--danger); }

.manage-link { display: inline-block; margin-top: 15px; color: var(--primary); text-decoration: none; font-size: 14px; font-weight: 500; }
.manage-link:hover { text-decoration: underline; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>🛠️ Admin</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_finance.php" class="active">Finance</a>
    <a href="manage_users.php">Users</a>
    <a href="manage_courses.php">Courses</a>
    <a href="notifications_admin.php">Notifications</a>
    <a href="admin_backup.php">Backups</a>
    <a href="reports.php">Reports</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h3>Finance Management</h3>
        <div>👋 <?= htmlspecialchars($name) ?></div>
    </div>

    <div class="content">
        <div class="cards">
            <div class="card">
                <h3>KES <?= number_format($total_revenue ?: 0, 2) ?></h3>
                <p>Total Revenue</p>
            </div>
            <div class="card">
                <h3>KES <?= number_format($today_revenue ?: 0, 2) ?></h3>
                <p>Collected Today</p>
            </div>
            <div class="card">
                <h3><?= $pending_count ?></h3>
                <p>Pending Approvals</p>
            </div>
        </div>

        <div class="box">
            <h3>💳 Recent Transactions</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Amount</th>
                        <th>M-Pesa Code</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $recent_payments->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td>KES <?= number_format($row['amount'], 2) ?></td>
                        <td><?= htmlspecialchars($row['mpesa_code']) ?></td>
                        <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                        <td><span class="status <?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <a href="admin_payment.php" class="manage-link">Manage Pending Payments &rarr;</a>
        </div>
    </div>
</div>

</body>
</html>
