<?php
session_start();
include('db.php');

$message = "";

// ONLY ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

if(isset($_POST['restore'])){

    $file = $_FILES['backup']['tmp_name'];

    if($file){

        $command = "mysql -u root kiharu_student_portal < $file";
        system($command);

        $message = "Database restored successfully!";
    } else {
        $message = "Upload backup file!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Restore Database</title>

<style>
body{background:#0f172a;color:white;font-family:Arial;padding:40px;}
.card{background:#1e293b;padding:20px;border-radius:10px;width:400px;margin:auto;}
button{padding:10px;background:#ef4444;color:white;border:none;width:100%;}
</style>
</head>

<body>

<div class="card">

<h2>🔄 Restore Database</h2>

<?php if($message) echo "<p>$message</p>"; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="backup" required><br><br>
    <button name="restore">Restore Now</button>
</form>

<br>
<a href="admin_backup.php">⬅ Back</a>

</div>

</body>
</html>
