<?php
session_start();
include('db.php');

// ONLY ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// BACKUP FUNCTION
function backupDatabase() {
    $db = "kiharu_student_portal";
    $file = "backups/backup_" . date("Y-m-d_H-i-s") . ".sql";

    if (!is_dir("backups")) {
        mkdir("backups", 0777, true);
    }

    $command = "mysqldump -u root $db > $file";
    system($command);

    return $file;
}

// HANDLE BACKUP
if (isset($_POST['backup_now'])) {

    $file = backupDatabase();

    // 🔔 Notify admin
    $admin_id = $_SESSION['user_id'];
    $msg = "New system backup created: " . basename($file);

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $admin_id, $msg);
    $stmt->execute();

    $message = "Backup created successfully!";
}

// GET BACKUPS
$files = glob("backups/*.sql");
?>

<!DOCTYPE html>
<html>
<head>
<title>Backup System</title>

<style>
body{font-family:Arial;background:#0f172a;color:white;padding:20px;}
.card{background:#1e293b;padding:20px;border-radius:10px;}
button{padding:10px;background:#6366f1;color:white;border:none;}
a{color:#22c55e;text-decoration:none;}
.file{margin:10px 0;padding:10px;background:#334155;border-radius:8px;}
</style>
</head>

<body>

<h2>💾 System Backup Panel</h2>

<div class="card">

<?php if($message) echo "<p>$message</p>"; ?>

<form method="POST">
    <button name="backup_now">⚡ Backup Now</button>
</form>

<h3>📂 Available Backups</h3>

<?php foreach($files as $file): ?>
<div class="file">
    <?php echo basename($file); ?>
    - <a href="<?php echo $file; ?>" download>⬇ Download</a>
</div>
<?php endforeach; ?>

<br>
<a href="admin_dashboard.php">⬅ Back to Dashboard</a>

</div>

</body>
</html>
