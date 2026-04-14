<?php
session_start();
include('db.php');

$message = "";

// CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if(isset($_POST['verify'])){

    $otp_input = trim($_POST['otp']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT otp FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if($user && $user['otp'] == $otp_input){

        // CLEAR OTP
        $conn->query("UPDATE users SET otp=NULL WHERE id=$user_id");

        header("Location: dashboard.php");
        exit();

    } else {
        $message = "Invalid OTP!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Verify OTP</title>

<style>
body{
    margin:0;
    font-family:Arial;
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
    text-align:center;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
}

/* INPUT */
input{
    width:100%;
    padding:12px;
    margin:10px 0;
    border-radius:8px;
    border:1px solid #ccc;
    text-align:center;
    font-size:18px;
    letter-spacing:3px;
}

/* BUTTON */
button{
    width:100%;
    padding:12px;
    background:#667eea;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

button:hover{
    background:#5568d3;
}

/* MESSAGE */
.error{
    background:#f8d7da;
    color:#721c24;
    padding:10px;
    margin-bottom:10px;
    border-radius:6px;
}

/* TIMER */
.timer{
    font-size:13px;
    margin-top:10px;
    color:#555;
}
</style>
</head>

<body>

<div class="card">

<h2>🔐 Verify OTP</h2>
<p>Enter the 6-digit code sent to you</p>

<?php if($message): ?>
<div class="error"><?php echo $message; ?></div>
<?php endif; ?>

<form method="POST" id="otpForm">
    <input type="text" name="otp" id="otp" maxlength="6" placeholder="------" required>
    <button type="submit" name="verify" id="btn">Verify</button>
</form>

<div class="timer" id="timer">OTP expires in 60s</div>

</div>

<script>

// AUTO FOCUS
document.getElementById("otp").focus();

// TIMER
let time = 60;
let timer = document.getElementById("timer");

let interval = setInterval(() => {
    time--;
    timer.innerHTML = "OTP expires in " + time + "s";

    if(time <= 0){
        clearInterval(interval);
        timer.innerHTML = "OTP expired!";
    }
}, 1000);

// LOADING EFFECT
document.getElementById("otpForm").addEventListener("submit", function(){
    let btn = document.getElementById("btn");
    btn.innerHTML = "Verifying...";
    btn.disabled = true;
});

</script>

</body>
</html>
