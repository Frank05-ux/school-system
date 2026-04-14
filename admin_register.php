<?php
// =========================
// DB CONNECTION
// =========================
$host = "localhost";
$db   = "kiharu_portal";
$user = "root";       // change to your DB username
$pass = "";           // change to your DB password

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// =========================
// INIT VARIABLES
// =========================
$message = "";
$type    = "";

// =========================
// HANDLE FORM SUBMISSION
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name  = trim($_POST["full_name"]  ?? "");
    $email      = trim($_POST["email"]      ?? "");
    $reg_number = trim($_POST["reg_number"] ?? "");
    $password   = trim($_POST["password"]   ?? "");

    // --- Server-side validation ---
    if (empty($full_name) || empty($reg_number) || empty($password)) {
        $message = "❌ Full Name, Username, and Password are required.";
        $type    = "error";

    } elseif (strlen($password) < 4) {
        $message = "❌ Password must be at least 4 characters.";
        $type    = "error";

    } else {
        // Check if reg_number already exists
        $check = $conn->prepare("SELECT id FROM users WHERE reg_number = ?");
        $check->bind_param("s", $reg_number);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "❌ Username already exists. Choose another.";
            $type    = "error";
        } else {
            // Check email uniqueness only if provided
            if (!empty($email)) {
                $emailCheck = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $emailCheck->bind_param("s", $email);
                $emailCheck->execute();
                $emailCheck->store_result();

                if ($emailCheck->num_rows > 0) {
                    $message = "❌ Email already in use.";
                    $type    = "error";
                    $emailCheck->close();
                    goto end_processing;
                }
                $emailCheck->close();
            }

            // Hash password
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            // Insert into users — email is NULL if empty
            $emailVal = !empty($email) ? $email : null;
            $role     = "admin";

            $stmt = $conn->prepare(
                "INSERT INTO users (full_name, email, reg_number, password, role)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssss", $full_name, $emailVal, $reg_number, $hashed, $role);

            if ($stmt->execute()) {
                $stmt->close();
                $check->close();
                $conn->close();
                header("Location: admin_login.php?registered=1");
                exit();
            } else {
                $message = "❌ Error: " . $conn->error;
                $type    = "error";
            }
            $stmt->close();
        }
        $check->close();
    }
}

end_processing:
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Registration | Kiharu Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }

body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
}

.card {
    width: 420px;
    padding: 35px 30px;
    border-radius: 20px;
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(15px);
    box-shadow: 0 10px 40px rgba(0,0,0,0.25);
}

h2 {
    text-align: center;
    color: #fff;
    margin-bottom: 22px;
    font-size: 22px;
    font-weight: 600;
}

/* Feedback messages */
.message {
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 15px;
    text-align: center;
    font-size: 14px;
    font-weight: 500;
}
.success { background: rgba(34,197,94,0.2);  color: #22c55e; border: 1px solid rgba(34,197,94,0.4); }
.error   { background: rgba(239,68,68,0.2);  color: #ef4444; border: 1px solid rgba(239,68,68,0.4); }

/* Inputs */
.input-group {
    position: relative;
    margin-bottom: 12px;
}

.input-group input {
    width: 100%;
    padding: 12px 15px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.25);
    background: rgba(255,255,255,0.15);
    color: #fff;
    font-size: 14px;
    outline: none;
    transition: border 0.3s;
}

.input-group input::placeholder { color: rgba(255,255,255,0.65); }
.input-group input:focus { border-color: rgba(255,255,255,0.7); }

/* Password toggle icon */
.toggle-eye {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: rgba(255,255,255,0.7);
    font-size: 16px;
    user-select: none;
}

/* Optional label */
.opt-label {
    font-size: 11px;
    color: rgba(255,255,255,0.5);
    margin-top: -8px;
    margin-bottom: 10px;
    padding-left: 5px;
    display: block;
}

/* Show password checkbox */
.show-pw {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(255,255,255,0.75);
    font-size: 13px;
    margin-bottom: 18px;
    cursor: pointer;
}
.show-pw input { width: auto; margin: 0; }

/* Submit button */
button[type="submit"] {
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 10px;
    background: #4f46e5;
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s, transform 0.1s;
}
button[type="submit"]:hover    { background: #4338ca; }
button[type="submit"]:active   { transform: scale(0.98); }
button[type="submit"]:disabled { opacity: 0.65; cursor: not-allowed; }
</style>
</head>
<body>

<div class="card">
    <h2>🛠️ Create Admin</h2>

    <?php if (!empty($message)): ?>
        <div class="message <?= htmlspecialchars($type) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="adminForm" novalidate>

        <div class="input-group">
            <input type="text" name="full_name" id="name"
                   placeholder="Full Name"
                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                   required>
        </div>

        <div class="input-group">
            <input type="email" name="email" id="email"
                   placeholder="Email (optional)"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <span class="opt-label">* Leave blank if no email</span>

        <div class="input-group">
            <input type="text" name="reg_number" id="reg"
                   placeholder="Username / Staff ID"
                   value="<?= htmlspecialchars($_POST['reg_number'] ?? '') ?>"
                   required>
        </div>

        <div class="input-group">
            <input type="password" name="password" id="password"
                   placeholder="Password (min 4 chars)" required>
        </div>

        <label class="show-pw">
            <input type="checkbox" id="showPw" onclick="togglePassword()">
            Show Password
        </label>

        <button type="submit" id="btn">Register Admin</button>
    </form>
</div>

<script>
// Toggle password visibility
function togglePassword() {
    const pwd = document.getElementById("password");
    pwd.type = (pwd.type === "password") ? "text" : "password";
}

// Client-side validation before submit
document.getElementById("adminForm").addEventListener("submit", function(e) {
    const name = document.getElementById("name").value.trim();
    const reg  = document.getElementById("reg").value.trim();
    const pass = document.getElementById("password").value.trim();
    const email = document.getElementById("email").value.trim();

    if (!name || !reg || !pass) {
        e.preventDefault();
        alert("❌ Full Name, Username, and Password are required!");
        return;
    }

    if (pass.length < 4) {
        e.preventDefault();
        alert("❌ Password must be at least 4 characters!");
        return;
    }

    // Basic email format check if provided
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        e.preventDefault();
        alert("❌ Please enter a valid email address or leave it blank.");
        return;
    }

    // Loading state
    const btn = document.getElementById("btn");
    btn.innerHTML = "Registering...";
    btn.disabled = true;
});
</script>

</body>
</html>