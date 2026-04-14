<?php
session_start();
include('db.php');

// Already logged in as admin? Skip login
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$message = "";
$type    = "";

// Show success banner if coming from registration
if (isset($_GET['registered'])) {
    $message = "✅ Admin registered successfully! Please log in.";
    $type    = "success";
}

// =========================
// HANDLE LOGIN SUBMISSION
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reg_number = trim($_POST['reg_number'] ?? "");
    $password   = trim($_POST['password']   ?? "");

    if (empty($reg_number) || empty($password)) {
        $message = "❌ Please fill in all fields.";
        $type    = "error";

    } else {
        $stmt = $conn->prepare(
            "SELECT id, full_name, password, role FROM users WHERE reg_number = ? LIMIT 1"
        );
        $stmt->bind_param("s", $reg_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                if ($user['role'] === 'admin') {
                    // Prevent session fixation
                    session_regenerate_id(true);

                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name']    = $user['full_name'];
                    $_SESSION['role']    = $user['role'];

                    $stmt->close();
                    $conn->close();

                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    $message = "⛔ Access denied! Admins only.";
                    $type    = "error";
                }
            } else {
                $message = "❌ Invalid username or password.";
                $type    = "error";
            }
        } else {
            $message = "❌ Invalid username or password.";
            $type    = "error";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Kiharu Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
        }

        .card {
            width: 420px;
            padding: 40px 35px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(15px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
        }

        .logo {
            text-align: center;
            margin-bottom: 6px;
            font-size: 36px;
        }

        h2 {
            text-align: center;
            color: #fff;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .subtitle {
            text-align: center;
            color: rgba(255,255,255,0.55);
            font-size: 13px;
            margin-bottom: 25px;
        }

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
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.35);
        }
        .error {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.35);
        }

        /* Form groups */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: border 0.3s, background 0.3s;
        }

        .form-group input::placeholder { color: rgba(255, 255, 255, 0.45); }
        .form-group input:focus {
            border-color: rgba(255, 255, 255, 0.7);
            background: rgba(255, 255, 255, 0.2);
        }

        /* Show password checkbox */
        .show-pw {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            margin-bottom: 22px;
            cursor: pointer;
            user-select: none;
        }
        .show-pw input[type="checkbox"] {
            width: 15px;
            height: 15px;
            cursor: pointer;
            accent-color: #4f46e5;
        }

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
            letter-spacing: 0.3px;
        }
        button[type="submit"]:hover    { background: #4338ca; }
        button[type="submit"]:active   { transform: scale(0.98); }
        button[type="submit"]:disabled { opacity: 0.6; cursor: not-allowed; }

        /* Bottom register link */
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }
        .register-link a {
            color: #a5f3fc;
            text-decoration: none;
            font-weight: 500;
        }
        .register-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="card">

    <div class="logo">🔐</div>
    <h2>Admin Login</h2>
    <p class="subtitle">Kiharu Portal — Admins Only</p>

    <?php if (!empty($message)): ?>
        <div class="message <?= htmlspecialchars($type) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="loginForm" novalidate>

        <div class="form-group">
            <label for="reg_number">Username / Staff ID</label>
            <input
                type="text"
                name="reg_number"
                id="reg_number"
                placeholder="Enter your username"
                value="<?= htmlspecialchars($_POST['reg_number'] ?? '') ?>"
                autocomplete="username"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                name="password"
                id="password"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
            >
        </div>

        <label class="show-pw">
            <input type="checkbox" id="showPw" onclick="togglePassword()">
            Show Password
        </label>

        <button type="submit" id="btn">Login as Admin</button>
    </form>

    <div class="register-link">
        No account? <a href="admin_register.php">Register Admin</a>
    </div>

</div>

<script>
    // Toggle show/hide password
    function togglePassword() {
        const pwd = document.getElementById("password");
        pwd.type = (pwd.type === "password") ? "text" : "password";
    }

    // Client-side validation + loading state
    document.getElementById("loginForm").addEventListener("submit", function(e) {
        const reg  = document.getElementById("reg_number").value.trim();
        const pass = document.getElementById("password").value.trim();

        if (!reg || !pass) {
            e.preventDefault();
            alert("❌ Please fill in all fields!");
            return;
        }

        const btn = document.getElementById("btn");
        btn.innerHTML = "Logging in...";
        btn.disabled = true;
    });
</script>

</body>
</html>