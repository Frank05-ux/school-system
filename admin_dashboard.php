<?php
session_start();
include('db.php');

// AUTH CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
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

// =========================
// BASIC STATS
// =========================
$total_students = getValue($conn, "SELECT COUNT(*) FROM users WHERE role='student'");
$total_courses  = getValue($conn, "SELECT COUNT(*) FROM courses");
$total_lecturers= getValue($conn, "SELECT COUNT(*) FROM users WHERE role='lecturer'");
$total_notifications = getValue($conn, "SELECT COUNT(*) FROM notifications");

// =========================
// FINANCE STATS
// =========================
$total_revenue = getValue($conn, "SELECT IFNULL(SUM(amount),0) FROM payments WHERE status='APPROVED'");
$pending_payments = getValue($conn, "SELECT COUNT(*) FROM payments WHERE status='PENDING'");

// =========================
// GPA STATS
// =========================
$avg_gpa = getValue($conn, "SELECT IFNULL(AVG(gpa),0) FROM gpa_summary");

// =========================
// TIMETABLE STATS
// =========================
$total_classes = getValue($conn, "SELECT COUNT(*) FROM timetable");

// =========================
// CHART DATA (MONTHLY REVENUE)
// =========================
$months = [];
$totals = [];

$q = $conn->query("
    SELECT MONTH(created_at) AS m, IFNULL(SUM(amount),0) AS total
    FROM payments 
    WHERE status='APPROVED'
    GROUP BY m
");

if ($q) {
    while ($row = $q->fetch_assoc()) {
        $months[] = $row['m'];
        $totals[] = $row['total'];
    }
}

// Convert month numbers to names for the chart
$monthNames = ["", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
$labels = array_map(fn($m) => $monthNames[$m] ?? $m, $months);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root {
    --primary: #4f46e5;
    --primary-dark: #4338ca;
    --bg: #f1f5f9;
    --sidebar: #0f172a;
    --text-main: #1e293b;
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

.cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
.card { background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); text-align: center; border: 1px solid #e2e8f0; }
.card h3 { font-size: 28px; color: var(--primary); margin-bottom: 5px; }
.card p { color: #64748b; font-size: 14px; font-weight: 500; }

.box { background: #fff; padding: 25px; border-radius: 15px; margin-top: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
.box h3 { margin-bottom: 20px; font-size: 18px; color: var(--sidebar); }

@media (max-width: 768px) {
    .sidebar { width: 80px; padding: 10px; }
    .sidebar h2, .sidebar a span { display: none; }
    .main { margin-left: 80px; width: calc(100% - 80px); }
}
</style>
</head>
<body>
<div class="sidebar">
<h2 style="margin-bottom: 5px;">🛠️ Admin</h2>
<a href="index.php" style="font-size: 11px; text-align: center; color: #fbbf24; margin-bottom: 20px; display: block; border: 1px solid rgba(251,191,36,0.3); padding: 5px; border-radius: 5px;">🌐 Visit Public Site</a>
<a href="admin_dashboard.php" class="active">Dashboard</a>
<a href="admin_finance.php">Finance</a>
<a href="manage_users.php">Users</a>
<a href="manage_courses.php">Courses</a>
<a href="notifications_admin.php">Notifications</a>
<a href="admin_backup.php">Backups</a>
<a href="reports.php">Reports</a>
<a href="logout.php">Logout</a>
</div>

<div class="main">
<div class="topbar">
<h3>Dashboard</h3>
<div>👋 <?= htmlspecialchars($name) ?></div>
</div>

<div class="content">
<div class="cards">
<div class="card"><h3><?= $total_students ?></h3><p>Students</p></div>
<div class="card"><h3><?= $total_courses ?></h3><p>Courses</p></div>
<div class="card"><h3><?= $total_lecturers ?></h3><p>Lecturers</p></div>
<div class="card"><h3><?= $total_notifications ?></h3><p>Notifications</p></div>
</div>

<div class="cards" style="margin-top:20px;">
<div class="card"><h3>KES <?= $total_revenue ?></h3><p>Total Revenue</p></div>
<div class="card"><h3><?= $pending_payments ?></h3><p>Pending Payments</p></div>
</div>

<div class="cards" style="margin-top:20px;">
<div class="card"><h3><?= number_format($avg_gpa,2) ?></h3><p>Average GPA</p></div>
<div class="card"><h3><?= $total_classes ?></h3><p>Total Classes</p></div>
</div>

<div class="box">
<h3>📊 Monthly Revenue</h3>
<canvas id="chart"></canvas>
</div>
</div>
</div>

<script>
new Chart(document.getElementById('chart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Revenue',
            data: <?= json_encode($totals) ?>,
            tension: 0.3
        }]
    }
});
</script>
</body>
</html>
