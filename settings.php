<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_settings'])) {
        // Example: Update system settings
        // You can extend this to store settings in database table if needed
        $message = "Settings updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Settings</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2 { color: #333; }
.settings-container { max-width: 600px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
form { margin-top: 20px; }
.form-group { margin-bottom: 15px; }
label { display: block; margin-bottom: 5px; color: #333; font-weight: bold; }
input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
button { background: #4f46e5; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
button:hover { background: #4338ca; }
.message { color: green; margin-bottom: 15px; }
a { text-decoration: none; color: #4f46e5; }
</style>
</head>
<body>
<h2>System Settings</h2>
<a href="admin_dashboard.php">← Back to Dashboard</a>

<div class="settings-container">
    <?php if ($message): ?>
    <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="system_name">System Name:</label>
            <input type="text" id="system_name" name="system_name" value="Student Portal" required>
        </div>

        <div class="form-group">
            <label for="institution">Institution Name:</label>
            <input type="text" id="institution" name="institution" value="University/College Name" required>
        </div>

        <div class="form-group">
            <label for="email">System Email:</label>
            <input type="email" id="email" name="email" value="admin@institution.edu" required>
        </div>

        <div class="form-group">
            <label for="phone">Contact Phone:</label>
            <input type="tel" id="phone" name="phone" value="+254 7xx xxx xxx">
        </div>

        <div class="form-group">
            <label for="mpesa_key">M-Pesa Consumer Key:</label>
            <input type="text" id="mpesa_key" name="mpesa_key" placeholder="Enter your M-Pesa API key">
        </div>

        <div class="form-group">
            <label for="mpesa_secret">M-Pesa Consumer Secret:</label>
            <input type="password" id="mpesa_secret" name="mpesa_secret" placeholder="Enter your M-Pesa API secret">
        </div>

        <div class="form-group">
            <label for="description">System Description:</label>
            <textarea id="description" name="description" rows="4">Online student portal for course registration, grade management, and payment processing.</textarea>
        </div>

        <button type="submit" name="update_settings">Save Settings</button>
    </form>
</div>
</body>
</html>