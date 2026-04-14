<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

$message = "";
$message_type = ""; // Added to differentiate success/error messages

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        // Prevent admin from deleting themselves or other admins without more robust checks
        if ($user_id == $_SESSION['user_id']) {
            $message = "❌ You cannot delete your own account!";
            $message_type = "error";
        } else {
            // Check if the user being deleted is an admin
            $stmt_check_role = $conn->prepare("SELECT role FROM users WHERE id = ?");
            $stmt_check_role->bind_param("i", $user_id);
            $stmt_check_role->execute();
            $result_check_role = $stmt_check_role->get_result();
            $user_to_delete = $result_check_role->fetch_assoc();
            $stmt_check_role->close();

            if ($user_to_delete && $user_to_delete['role'] === 'admin') {
                $message = "❌ Cannot delete another admin account directly from here.";
                $message_type = "error";
            } else {
                // Delete associated student/lecturer records first if they exist
                // (Assuming ON DELETE CASCADE is not set or explicit handling is preferred)
                // For simplicity, we'll just delete from users. If there are FK constraints, this will fail.
                $stmt_delete = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt_delete->bind_param("i", $user_id);
                if ($stmt_delete->execute()) {
                    $message = "✅ User deleted successfully!";
                    $message_type = "success";
                } else {
                    $message = "❌ Error deleting user: " . $conn->error;
                    $message_type = "error";
                }
                $stmt_delete->close();
            }
        }
    } elseif (isset($_POST['add_user'])) {
        $full_name = trim($_POST['full_name']);
        $reg_number = trim($_POST['reg_number']);
        $password = trim($_POST['password']);
        $role = $_POST['role'];

        if (empty($full_name) || empty($reg_number) || empty($password)) {
            $message = "❌ Please fill in all fields for adding a user.";
            $message_type = "error";
        } elseif (strlen($password) < 4) { // Basic password length check
            $message = "❌ Password must be at least 4 characters.";
            $message_type = "error";
        } else {
            // Check if reg_number already exists
            $check_reg = $conn->prepare("SELECT id FROM users WHERE reg_number = ?");
            $check_reg->bind_param("s", $reg_number);
            $check_reg->execute();
            $check_reg->store_result();

            if ($check_reg->num_rows > 0) {
                $message = "❌ Registration number already exists. Please choose another.";
                $message_type = "error";
            } else {
                // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, reg_number, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $full_name, $reg_number, $hashed_password, $role);
                if ($stmt->execute()) {
                    $new_user_id = $stmt->insert_id;
                    // If adding a student or lecturer, also insert into their respective tables
                    if ($role === 'student') {
                        $stmt_student = $conn->prepare("INSERT INTO students (user_id, course_id, year, semester) VALUES (?, ?, 1, 1)");
                        // IMPORTANT: Assuming a default course_id=1 exists. In a real app, this would be selected.
                        $default_course_id = 1;
                        $stmt_student->bind_param("ii", $new_user_id, $default_course_id);
                        $stmt_student->execute();
                        $stmt_student->close();
                    } elseif ($role === 'lecturer') {
                        $stmt_lecturer = $conn->prepare("INSERT INTO lecturers (user_id, department) VALUES (?, ?)");
                        // IMPORTANT: Assuming a default department. In a real app, this would be selected.
                        $default_department = "General";
                        $stmt_lecturer->bind_param("is", $new_user_id, $default_department);
                        $stmt_lecturer->execute();
                        $stmt_lecturer->close();
                    }
                    $message = "✅ User added successfully!";
                    $message_type = "success";
                } else {
                    $message = "❌ Error adding user: " . $conn->error;
                    $message_type = "error";
                }
                $stmt->close();
            }
            $check_reg->close();
        }
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY role, full_name");
$name = $_SESSION['name'] ?? 'Admin'; // For topbar
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users | Kiharu Portal</title>
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

/* Feedback messages */
.message {
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 18px;
    text-align: center;
    font-size: 13px;
    font-weight: 500;
}
.success {
    background: rgba(34, 197, 94, 0.2);
    color: var(--success);
    border: 1px solid rgba(34, 197, 94, 0.35);
}
.error {
    background: rgba(239, 68, 68, 0.2);
    color: var(--danger);
    border: 1px solid rgba(239, 68, 68, 0.35);
}

/* Form styling */
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-size: 13px; color: #333; }
input[type="text"], input[type="password"], select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
}
input[type="text"]:focus, input[type="password"]:focus, select:focus {
    border-color: var(--primary);
    outline: none;
}
button[type="submit"] {
    padding: 10px 15px;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.2s;
}
button[type="submit"]:hover { background: var(--primary-dark); }
button[type="submit"]:disabled { opacity: 0.6; cursor: not-allowed; }

/* Table styling */
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
th { color: #64748b; font-weight: 600; background: #f8fafc; }
tr:hover { background: #fcfcfc; }
td button {
    padding: 6px 10px;
    font-size: 12px;
    border-radius: 6px;
    background: var(--danger);
    color: #fff;
    border: none;
    cursor: pointer;
    transition: background 0.2s;
}
td button:hover { background: #dc2626; }

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
    <a href="manage_users.php" class="active">Users</a>
    <a href="manage_courses.php">Courses</a>
    <a href="notifications_admin.php">Notifications</a>
    <a href="admin_backup.php">Backups</a>
    <a href="reports.php">Reports</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h3>Manage Users</h3>
        <div>👋 <?= htmlspecialchars($name) ?></div>
    </div>

    <div class="content">
        <?php if (!empty($message)): ?>
            <div class="message <?= htmlspecialchars($message_type) ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="box">
            <h3>➕ Add New User</h3>
            <form method="POST" id="addUserForm">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" name="full_name" id="full_name" placeholder="Full Name" required>
                </div>
                <div class="form-group">
                    <label for="reg_number">Registration / Staff ID</label>
                    <input type="text" name="reg_number" id="reg_number" placeholder="Reg Number / Staff ID" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="role" required>
                        <option value="student">Student</option>
                        <option value="lecturer">Lecturer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="add_user" id="addBtn">Add User</button>
            </form>
        </div>

        <div class="box">
            <h3>👥 All Users</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Reg Number</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['reg_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_user">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.getElementById("addUserForm").addEventListener("submit", function(e) {
        const fullName = document.getElementById("full_name").value.trim();
        const regNumber = document.getElementById("reg_number").value.trim();
        const password = document.getElementById("password").value.trim();

        if (!fullName || !regNumber || !password) {
            e.preventDefault();
            alert("Please fill in all fields for adding a user!");
            return;
        }
        if (password.length < 4) {
            e.preventDefault();
            alert("Password must be at least 4 characters!");
            return;
        }

        const btn = document.getElementById("addBtn");
        btn.innerHTML = "Adding...";
        btn.disabled = true;
    });
</script>

</body>
</html>