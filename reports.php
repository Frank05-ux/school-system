<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Get statistics
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$total_lecturers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'lecturer'")->fetch_assoc()['count'];
$total_courses = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];
$total_payments = $conn->query("SELECT SUM(amount) as total FROM payments")->fetch_assoc()['total'];
$total_invoices = $conn->query("SELECT COUNT(*) as count FROM invoices")->fetch_assoc()['count'];

// Enrollments by course
$enrollments = $conn->query("SELECT c.course_name, COUNT(s.id) as count FROM courses c LEFT JOIN students s ON c.id = s.course_id GROUP BY c.id, c.course_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports & Analytics</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2 { color: #333; }
.stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
.stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.stat-number { font-size: 32px; font-weight: bold; color: #4f46e5; }
.stat-label { color: #666; font-size: 14px; margin-top: 5px; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
th { background: #4f46e5; color: white; }
a { text-decoration: none; color: #4f46e5; }
</style>
</head>
<body>
<h2>Reports & Analytics</h2>
<a href="admin_dashboard.php">← Back to Dashboard</a>

<h3>System Statistics</h3>
<div class="stats">
    <div class="stat-card">
        <div class="stat-number"><?php echo $total_students; ?></div>
        <div class="stat-label">Total Students</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo $total_lecturers; ?></div>
        <div class="stat-label">Total Lecturers</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo $total_courses; ?></div>
        <div class="stat-label">Total Courses</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">KES <?php echo number_format($total_payments ?? 0, 2); ?></div>
        <div class="stat-label">Total Payments</div>
    </div>
</div>

<h3>Course Enrollments</h3>
<table>
<tr><th>Course Name</th><th>Enrolled Students</th></tr>
<?php while ($row = $enrollments->fetch_assoc()): ?>
<tr>
    <td><?php echo $row['course_name']; ?></td>
    <td><?php echo $row['count']; ?></td>
</tr>
<?php endwhile; ?>
</table>

<h3>Invoice Summary</h3>
<p>Total Invoices: <?php echo $total_invoices; ?></p>
</body>
</html>