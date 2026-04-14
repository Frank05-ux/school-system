<?php
session_start();
include('db.php');

$message = "";
$type = "";

/* =========================
   SEND NOTIFICATION
========================= */
if (isset($_POST['send_notification'])) {

    $msg = trim($_POST['message']);

    if (!empty($msg)) {

        $users = $conn->query("SELECT id FROM users");

        while ($u = $users->fetch_assoc()) {

            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message)
                VALUES (?, ?)
            ");
            $stmt->bind_param("is", $u['id'], $msg);
            $stmt->execute();
        }

        $message = "✅ Notification sent to all users!";
        $type = "success";

    } else {
        $message = "❌ Message cannot be empty!";
        $type = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Send Notification</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#4f46e5,#06b6d4);
}

/* CONTAINER */
.container{
    width:100%;
    max-width:450px;
}

/* CARD */
.card{
    background:rgba(255,255,255,0.15);
    backdrop-filter:blur(15px);
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
}

/* TITLE */
h2{
    text-align:center;
    color:#fff;
    margin-bottom:20px;
}

/* MESSAGE */
.message{
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
    text-align:center;
    font-size:14px;
}

.success{
    background:rgba(34,197,94,0.2);
    color:#22c55e;
}

.error{
    background:rgba(239,68,68,0.2);
    color:#ef4444;
}

/* INPUT */
textarea{
    width:100%;
    padding:12px;
    border-radius:10px;
    border:none;
    outline:none;
    resize:none;
    height:120px;
    margin-bottom:15px;
}

/* BUTTON */
button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background:#4f46e5;
    color:#fff;
    cursor:pointer;
    font-size:15px;
    transition:0.3s;
}

button:hover{
    background:#4338ca;
}

button:disabled{
    background:gray;
    cursor:not-allowed;
}
</style>
</head>

<body>

<div class="container">
<div class="card">

<h2>📨 Send Notification</h2>

<?php if($message): ?>
<div class="message <?php echo $type; ?>">
    <?php echo $message; ?>
</div>
<?php endif; ?>

<form method="POST" id="notifyForm">

<textarea name="message" id="message" placeholder="Type your notification..." required></textarea>

<button type="submit" name="send_notification" id="sendBtn">
    Send Notification
</button>

</form>

</div>
</div>

<script>

// Form validation + loading state
document.getElementById("notifyForm").addEventListener("submit", function(e){

    let msg = document.getElementById("message").value.trim();
    let btn = document.getElementById("sendBtn");

    if(msg === ""){
        e.preventDefault();
        alert("Message cannot be empty!");
        return;
    }

    // Loading effect
    btn.innerHTML = "Sending...";
    btn.disabled = true;
});

</script>

</body>
</html>
