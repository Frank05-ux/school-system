<?php
session_start();
include('db.php');

$message = "";

// Already logged in
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'lecturer') {
    header("Location: lecturer_dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ✅ FIX: prevent undefined array key
    $reg_number = trim($_POST['reg_number'] ?? '');
    $password   = trim($_POST['password'] ?? '');

    if (empty($reg_number) || empty($password)) {
        $message = "<div class='error'>Please fill all fields!</div>";
    } else {

        $stmt = $conn->prepare("SELECT * FROM users WHERE reg_number = ? AND role = 'lecturer'");
        $stmt->bind_param("s", $reg_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password']) || hash('sha256', $password) === $user['password']) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['full_name'];
                $_SESSION['role']    = $user['role'];

                header("Location: lecturer_dashboard.php");
                exit();

            } else {
                $message = "<div class='error'>Invalid password!</div>";
            }

        } else {
            $message = "<div class='error'>Lecturer not found!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Lecturer Login</title>

<style>
:root {
    --primary:#667eea;
    --danger:#dc3545;
}

body{
    margin:0;
    font-family:'Segoe UI';
    background:linear-gradient(135deg,#667eea,#764ba2);
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

/* CARD */
.card{
    background:white;
    padding:40px;
    border-radius:12px;
    width:350px;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
    position:relative;
}

/* 🔙 BACK ARROW */
.back{
    position:absolute;
    top:15px;
    left:15px;
    font-size:20px;
    text-decoration:none;
    color:var(--primary);
}
.back:hover{
    color:#5568d3;
}

/* TITLE */
h2{
    text-align:center;
    margin-bottom:20px;
    color:var(--primary);
}

/* INPUT */
input{
    width:100%;
    padding:12px;
    margin:10px 0;
    border-radius:8px;
    border:1px solid #ccc;
}

/* BUTTON */
button{
    width:100%;
    padding:12px;
    background:var(--primary);
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:bold;
}

button:hover{
    background:#5568d3;
}

/* LINKS */
.links{
    margin-top:15px;
    text-align:center;
}
.links a{
    text-decoration:none;
    color:var(--primary);
    font-size:14px;
}

/* MESSAGE */
.error{
    background:#f8d7da;
    color:#721c24;
    padding:10px;
    margin-bottom:10px;
    border-radius:6px;
    text-align:center;
}

/* TOGGLE */
.toggle{
    font-size:12px;
    cursor:pointer;
    margin-bottom:10px;
}
</style>
</head>

<body>

<div class="card">

<!-- 🔙 BACK ARROW -->
<a href="login.php" class="back">⬅</a>

<h2>👨‍🏫 Lecturer Login</h2>

<?php echo $message; ?>

<form method="POST" id="loginForm">

<input type="text" name="reg_number" placeholder="Registration Number" required>

<input type="password" name="password" id="password" placeholder="Password" required>

<div class="toggle" onclick="togglePassword()">👁 Show Password</div>

<button type="submit" id="btn">Login</button>

</form>

<div class="links">
    <a href="login.php">← Back to General Login</a><br>
    <a href="lecturer_register.php">👨‍🏫 Create Lecturer Account</a>
</div>

</div>

<script>

// SHOW PASSWORD
function togglePassword(){
    let p = document.getElementById("password");
    p.type = p.type === "password" ? "text" : "password";
}

// LOADING EFFECT
document.getElementById("loginForm").addEventListener("submit", function(){
    let btn = document.getElementById("btn");
    btn.innerHTML = "Logging in...";
    btn.disabled = true;
});
</script>

</body>
</html>
