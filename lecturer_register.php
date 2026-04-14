<?php
session_start();
include('db.php');

$message = "";
$type = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name  = trim($_POST['full_name']);
    $reg_number = trim($_POST['reg_number']);
    $password   = trim($_POST['password']);
    $department = trim($_POST['department']);

    if (empty($full_name) || empty($reg_number) || empty($password) || empty($department)) {
        $message = "Please fill all fields!";
        $type = "error";
    } else {

        $check = $conn->prepare("SELECT id FROM users WHERE reg_number = ?");
        $check->bind_param("s", $reg_number);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Registration number already exists!";
            $type = "error";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (full_name, reg_number, password, role) VALUES (?, ?, ?, 'lecturer')");
            $stmt->bind_param("sss", $full_name, $reg_number, $hashed_password);

            if ($stmt->execute()) {

                $user_id = $stmt->insert_id;

                $stmt2 = $conn->prepare("INSERT INTO lecturers (user_id, department) VALUES (?, ?)");
                $stmt2->bind_param("is", $user_id, $department);
                $stmt2->execute();

                header("refresh:2;url=lecturer_login.php");

                $message = "Registration successful! Redirecting...";
                $type = "success";

            } else {
                $message = "Something went wrong. Try again.";
                $type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Lecturer Registration</title>

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
    justify-content:center;
    align-items:center;
    background:linear-gradient(135deg,#667eea,#764ba2);
}

/* Card */
.card{
    width:400px;
    padding:30px;
    border-radius:20px;
    background:rgba(255,255,255,0.1);
    backdrop-filter:blur(20px);
    box-shadow:0 10px 40px rgba(0,0,0,0.2);
    animation:fadeIn 0.8s ease;
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(20px);}
    to{opacity:1; transform:translateY(0);}
}

h2{
    text-align:center;
    color:#fff;
    margin-bottom:20px;
}

/* Message */
.message{
    padding:10px;
    border-radius:10px;
    margin-bottom:15px;
    text-align:center;
    font-size:14px;
}

.success{background:rgba(34,197,94,0.2);color:#22c55e;}
.error{background:rgba(239,68,68,0.2);color:#ef4444;}

/* Inputs */
.form-group{
    margin-bottom:15px;
}

label{
    color:#fff;
    font-size:13px;
}

input{
    width:100%;
    padding:12px;
    border-radius:10px;
    border:none;
    margin-top:5px;
    transition:0.3s;
}

input:focus{
    outline:none;
    transform:scale(1.02);
}

/* Button */
button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background:#667eea;
    color:#fff;
    cursor:pointer;
    font-weight:600;
    transition:0.3s;
}

button:hover{
    background:#5a67d8;
}

button:disabled{
    background:gray;
}

/* Password strength */
.strength{
    font-size:12px;
    color:#fff;
}

/* Link */
.link{
    text-align:center;
    margin-top:15px;
}

.link a{
    color:#fff;
    text-decoration:none;
}

.link a:hover{
    text-decoration:underline;
}
</style>
</head>

<body>

<div class="card">

<h2>👨‍🏫 Lecturer Registration</h2>

<?php if($message): ?>
<div class="message <?php echo $type; ?>">
    <?php echo $message; ?>
</div>
<?php endif; ?>

<form method="POST" id="form">

<div class="form-group">
<label>Full Name</label>
<input type="text" name="full_name" id="name" required>
</div>

<div class="form-group">
<label>Staff Number</label>
<input type="text" name="reg_number" id="reg" required>
</div>

<div class="form-group">
<label>Password</label>
<input type="password" name="password" id="password" required>
<span class="strength" id="strength"></span>
</div>

<input type="checkbox" onclick="togglePassword()"> 
<span style="color:white;">Show Password</span>

<div class="form-group">
<label>Department</label>
<input type="text" name="department" id="dept" required>
</div>

<button type="submit" id="btn">Register</button>

</form>

<div class="link">
<a href="lecturer_login.php">Already registered? Login</a>
</div>

</div>

<script>

// Show/hide password
function togglePassword(){
    let pass = document.getElementById("password");
    pass.type = pass.type === "password" ? "text" : "password";
}

// Password strength
document.getElementById("password").addEventListener("input", function(){
    let val = this.value;
    let strength = document.getElementById("strength");

    if(val.length < 4){
        strength.innerHTML = "Weak ❌";
    }else if(val.length < 8){
        strength.innerHTML = "Medium ⚠️";
    }else{
        strength.innerHTML = "Strong ✅";
    }
});

// Form validation
document.getElementById("form").addEventListener("submit", function(e){

    let name = document.getElementById("name").value.trim();
    let reg = document.getElementById("reg").value.trim();
    let pass = document.getElementById("password").value.trim();
    let dept = document.getElementById("dept").value.trim();

    if(!name || !reg || !pass || !dept){
        e.preventDefault();
        alert("Please fill all fields!");
        return;
    }

    if(pass.length < 4){
        e.preventDefault();
        alert("Password too short!");
        return;
    }

    // Loading state
    let btn = document.getElementById("btn");
    btn.innerHTML = "Registering...";
    btn.disabled = true;
});
</script>

</body>
</html>
