<?php
session_start();
include('db.php');

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

$message = "";
$type = "";

/* =========================
   SEND NOTIFICATION
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_notification'])) {

    $message_text = trim($_POST['message']);
    $recipient_type = $_POST['recipient_type'];

    if (!empty($message_text)) {

        // Send to ALL USERS
        if ($recipient_type == 'all') {

            $users = $conn->query("SELECT id FROM users");

            while ($u = $users->fetch_assoc()) {
                $stmt = $conn->prepare("
                    INSERT INTO notifications (user_id, message)
                    VALUES (?, ?)
                ");
                $stmt->bind_param("is", $u['id'], $message_text);
                $stmt->execute();
            }

        // Send to ROLE (student / lecturer / admin)
        } elseif (in_array($recipient_type, ['student','lecturer','admin'])) {

            $stmtUsers = $conn->prepare("SELECT id FROM users WHERE role = ?");
            $stmtUsers->bind_param("s", $recipient_type);
            $stmtUsers->execute();
            $result = $stmtUsers->get_result();

            while ($u = $result->fetch_assoc()) {
                $stmt = $conn->prepare("
                    INSERT INTO notifications (user_id, message)
                    VALUES (?, ?)
                ");
                $stmt->bind_param("is", $u['id'], $message_text);
                $stmt->execute();
            }

        // Send to ONE USER
        } else {

            $user_id = intval($recipient_type);

            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message)
                VALUES (?, ?)
            ");
            $stmt->bind_param("is", $user_id, $message_text);
            $stmt->execute();
        }

        $message = "✅ Notification sent successfully!";
        $type = "success";

    } else {
        $message = "❌ Message cannot be empty!";
        $type = "error";
    }
}

/* =========================
   FETCH USERS
========================= */
$users = $conn->query("SELECT id, full_name, role FROM users ORDER BY full_name");

/* =========================
   FETCH RECENT NOTIFICATIONS
========================= */
$notifications = $conn->query("
    SELECT n.*, u.full_name 
    FROM notifications n 
    JOIN users u ON n.user_id = u.id 
    ORDER BY n.created_at DESC 
    LIMIT 20
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Notifications</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    display:flex;
    background:linear-gradient(135deg,#4f46e5,#06b6d4);
    min-height:100vh;
}

/* SIDEBAR */
.sidebar{
    width:240px;
    background:#0f172a;
    color:white;
    padding:20px;
}

.sidebar h2{
    margin-bottom:20px;
}

.sidebar a{
    display:block;
    padding:12px;
    margin-bottom:10px;
    border-radius:8px;
    color:#cbd5f5;
    text-decoration:none;
}

.sidebar a:hover{
    background:#6366f1;
}

/* MAIN */
.main{
    flex:1;
    padding:20px;
}

/* CARD */
.card{
    background:white;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
    box-shadow:0 5px 20px rgba(0,0,0,0.1);
}

/* FORM */
textarea, select{
    width:100%;
    padding:10px;
    margin-top:10px;
    border-radius:8px;
    border:1px solid #ccc;
}

button{
    margin-top:15px;
    padding:12px;
    width:100%;
    background:#4f46e5;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

button:hover{
    background:#4338ca;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:10px;
    border-bottom:1px solid #eee;
}

/* MESSAGE */
.message{
    padding:10px;
    border-radius:8px;
    margin-bottom:10px;
}

.success{background:#dcfce7;color:#166534;}
.error{background:#fee2e2;color:#991b1b;}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>⚙️ Admin</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="#">Notifications</a>
    <a href="logout.php">Logout</a>
</div>

<!-- MAIN -->
<div class="main">

<h2>📨 Send Notifications</h2>

<?php if($message): ?>
<div class="message <?php echo $type; ?>">
    <?php echo $message; ?>
</div>
<?php endif; ?>

<div class="card">
<form method="POST">

<select name="recipient_type" required>
    <option value="all">🌍 All Users</option>
    <option value="student">🎓 Students</option>
    <option value="lecturer">👨‍🏫 Lecturers</option>
    <option value="admin">⚙️ Admins</option>

    <optgroup label="👤 Specific User">
    <?php while($u = $users->fetch_assoc()): ?>
        <option value="<?php echo $u['id']; ?>">
            <?php echo $u['full_name']." (".$u['role'].")"; ?>
        </option>
    <?php endwhile; ?>
    </optgroup>
</select>

<textarea name="message" placeholder="Type your notification..." required></textarea>

<button type="submit" name="send_notification">Send Notification</button>

</form>
</div>

<div class="card">
<h3>📜 Recent Notifications</h3>

<table>
<tr>
<th>User</th>
<th>Message</th>
<th>Date</th>
</tr>

<?php while($row = $notifications->fetch_assoc()): ?>
<tr>
<td><?php echo htmlspecialchars($row['full_name']); ?></td>
<td><?php echo htmlspecialchars($row['message']); ?></td>
<td><?php echo $row['created_at']; ?></td>
</tr>
<?php endwhile; ?>

</table>
</div>

</div>

<script>
// Button loading effect
document.querySelector("form").addEventListener("submit", function(){
    const btn = document.querySelector("button");
    btn.innerHTML = "Sending...";
    btn.disabled = true;
});
</script>

</body>
</html>
