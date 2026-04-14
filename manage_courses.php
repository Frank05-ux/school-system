<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_course'])) {
        $course_id = (int)$_POST['course_id'];
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("i", $course_id);
        if ($stmt->execute()) {
            $message = "✅ Course deleted successfully!";
            $message_type = "success";
        } else {
            $message = "❌ Error deleting course: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    } elseif (isset($_POST['add_course'])) {
        $course_name = trim($_POST['course_name']);
        if (!empty($course_name)) {
            try {
                $stmt = $conn->prepare("INSERT INTO courses (course_name) VALUES (?)");
                $stmt->bind_param("s", $course_name);
                $stmt->execute();
                $message = "✅ Course added successfully!";
                $message_type = "success";
                $stmt->close();
            } catch (mysqli_sql_exception $e) {
                $message = "❌ Database Error: " . $e->getMessage();
                $message_type = "error";
            }
        }
    }
}

$courses = $conn->query("SELECT * FROM courses ORDER BY course_name ASC");
$name = $_SESSION['name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Courses | Kiharu Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #4f46e5;
    --primary-dark: #4338ca;
    --bg: #f1f5f9;
    --sidebar: #0f172a;
    --text-main: #1e293b;
    --success: #22c55e;
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

.box { background: #fff; padding: 25px; border-radius: 15px; margin-top: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
.box h3 { margin-bottom: 20px; font-size: 18px; color: var(--sidebar); }

.message {
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 18px;
    text-align: center;
    font-size: 13px;
    font-weight: 500;
}
.success { background: rgba(34, 197, 94, 0.2); color: var(--success); border: 1px solid rgba(34, 197, 94, 0.35); }
.error { background: rgba(239, 68, 68, 0.2); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.35); }

.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-size: 13px; color: #333; }
input[type="text"] {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    font-size: 14px;
}
button {
    padding: 10px 15px;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: 0.2s;
}
button:hover { background: var(--primary-dark); }

table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
th { color: #64748b; font-weight: 600; background: #f8fafc; }
.btn-delete { background: var(--danger); font-size: 12px; padding: 6px 10px; }
.btn-delete:hover { background: #dc2626; }

@media (max-width: 768px) {
    .sidebar { width: 80px; padding: 10px; }
    .sidebar h2, .sidebar a span { display: none; }
    .main { margin-left: 80px; width: calc(100% - 80px); }
}
</style>
</head>
<body>

<div class="sidebar">
    <h2>🛠️ Admin</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_finance.php">Finance</a>
    <a href="manage_users.php">Users</a>
    <a href="manage_courses.php" class="active">Courses</a>
    <a href="notifications_admin.php">Notifications</a>
    <a href="admin_backup.php">Backups</a>
    <a href="reports.php">Reports</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h3>Manage Courses</h3>
        <div>👋 <?= htmlspecialchars($name) ?></div>
    </div>

    <div class="content">
        <?php if (!empty($message)): ?>
            <div class="message <?= htmlspecialchars($message_type) ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="box">
            <h3>➕ Add Course</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Course Name</label>
                    <input type="text" name="course_name" placeholder="e.g. Computer Science" required>
                </div>
                <button type="submit" name="add_course">Add Course</button>
            </form>
        </div>

        <div class="box">
            <h3>📚 Available Courses</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Course Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $courses->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                <input type="hidden" name="course_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_course" class="btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>