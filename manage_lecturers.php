<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_lecturer'])) {
        $user_id = $_POST['user_id'];
        $conn->query("DELETE FROM users WHERE id = $user_id AND role = 'lecturer'");
        $message = "Lecturer deleted!";
    } elseif (isset($_POST['add_lecturer'])) {
        $full_name = trim($_POST['full_name']);
        $reg_number = trim($_POST['reg_number']);
        $password = trim($_POST['password']);
        if (!empty($full_name) && !empty($reg_number) && !empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, reg_number, password, role) VALUES (?, ?, ?, 'lecturer')");
            $stmt->bind_param("sss", $full_name, $reg_number, $hashed_password);
            $stmt->execute();
            $message = "Lecturer added!";
        }
    }
}

$lecturers = $conn->query("SELECT * FROM users WHERE role = 'lecturer'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Lecturers</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #333; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
th { background: #f4f4f4; }
form { margin-bottom: 20px; }
input, button { padding: 8px; margin: 5px; }
.message { color: green; }
a { text-decoration: none; color: #4f46e5; }
</style>
</head>
<body>
<h2>Manage Lecturers</h2>
<a href="admin_dashboard.php">← Back to Dashboard</a>

<?php if ($message): ?>
<p class="message"><?php echo $message; ?></p>
<?php endif; ?>

<h3>Add Lecturer</h3>
<form method="POST">
    <input type="text" name="full_name" placeholder="Full Name" required>
    <input type="text" name="reg_number" placeholder="Employee ID" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="add_lecturer">Add Lecturer</button>
</form>

<h3>Lecturers</h3>
<table>
<tr><th>ID</th><th>Name</th><th>Employee ID</th><th>Action</th></tr>
<?php while ($row = $lecturers->fetch_assoc()): ?>
<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['full_name']; ?></td>
    <td><?php echo $row['reg_number']; ?></td>
    <td><form method="POST" style="display:inline;"><input type="hidden" name="user_id" value="<?php echo $row['id']; ?>"><button type="submit" name="delete_lecturer">Delete</button></form></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>