<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Student';

$student = ['course_name' => 'N/A', 'year_of_study' => 'N/A', 'semester' => 'N/A'];

if ($conn) {
    $stmt = $conn->prepare("SELECT s.year_of_study, s.semester, c.course_name FROM students s JOIN courses c ON s.course_id = c.id WHERE s.user_id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $student = $result->fetch_assoc();
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
<title>Student Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

body {
    display:flex;
    background:#f1f5f9;
    transition:0.3s;
}

.dark {
    background:#0f172a;
    color:#fff;
}

/* Sidebar */
.sidebar {
    width:250px;
    height:100vh;
    background:#1e293b;
    color:#fff;
    padding:20px;
    position:fixed;
}

    .sidebar a {
        display:block;
        color:#cbd5f5;
        padding:12px;
        margin-bottom:10px;
        border-radius:8px;
        text-decoration:none;
        transition:.2s;
    }

    .sidebar a:hover,
    .sidebar a.active {
        background:#4f46e5;
        color:#fff;
    }

/* Main */
.main {
    margin-left:250px;
    width:100%;
}

/* Topbar */
.topbar {
    background:#fff;
    padding:15px 25px;
    display:flex;
    justify-content:space-between;
}

.dark .topbar {
    background:#1e293b;
}

/* Content */
.content {
    padding:25px;
}

/* Cards */
.cards {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
}

.card {
    background:#fff;
    padding:20px;
    border-radius:15px;
}

.dark .card {
    background:#1e293b;
}

/* Sections */
.box {
    background:#fff;
    padding:20px;
    border-radius:15px;
    margin-top:20px;
}

.dark .box {
    background:#1e293b;
}

button {
    padding:10px;
    background:#4f46e5;
    color:#fff;
    border:none;
    border-radius:8px;
    cursor:pointer;
}
</style>
</head>

<body>

<div class="sidebar">
    <h2>🎓 Portal</h2>
    <a href="dashboard.php" class="nav-link">Dashboard</a>
    <a href="profile.php" class="nav-link">Profile</a>
    <a href="courses.php" class="nav-link">Courses</a>
    <a href="grades.php" class="nav-link">Grades</a>
    <a href="timetable.php" class="nav-link">Timetable</a>
    <a href="fees.php" class="nav-link">Fees</a>
    <a href="logout.php" class="nav-link">Logout</a>
</div>

<div class="main">

<div class="topbar">
    <h3>Dashboard</h3>
    <div>
        👋 <?php echo $name; ?>
        <button onclick="toggleDark()">🌙</button>
    </div>
</div>

<div class="content">

<?php if (!empty($_GET['upload_status'])): ?>
    <div class="box" style="background:#d1fae5; color:#065f46; border:1px solid #34d399;">
        <?php echo htmlspecialchars($_GET['upload_status']); ?>
    </div>
<?php endif; ?>

<!-- STATS -->
<div class="cards">
    <div class="card">Course: <?php echo $student['course_name']; ?></div>
    <div class="card">Year: <?php echo $student['year_of_study']; ?></div>
    <div class="card">Semester: <?php echo $student['semester']; ?></div>
</div>

<!-- CHART -->
<div class="box">
    <h3>📊 Performance</h3>
    <canvas id="chart"></canvas>
</div>

<!-- FILE UPLOAD -->
<div class="box">
    <h3>📁 Upload Materials</h3>
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit">Upload</button>
    </form>
</div>

<!-- NOTIFICATIONS -->
<div class="box">
    <h3>🔔 Notifications</h3>
    <div id="notifications">Loading...</div>
</div>

</div>
</div>

<script>

// 🌙 Dark Mode
function toggleDark(){
    document.body.classList.toggle("dark");
}

// Sidebar active link
function setActiveSidebar() {
    const links = document.querySelectorAll('.sidebar a.nav-link');
    const path = window.location.pathname.split('/').pop();
    links.forEach(link => {
        if (link.getAttribute('href') === path) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}
setActiveSidebar();

// 📊 Chart
const ctx = document.getElementById('chart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['CAT 1','CAT 2','Assignment','Exam'],
        datasets: [{
            label: 'Performance',
            data: [60, 70, 80, 75], // sample
            borderWidth: 2
        }]
    }
});

// 🔔 Notifications (auto refresh)
function loadNotifications(){
    fetch('notifications.php')
    .then(res => res.text())
    .then(data => {
        document.getElementById("notifications").innerHTML = data;
    });
}

// Load every 5 seconds
setInterval(loadNotifications, 5000);
loadNotifications();

</script>

</body>
</html>
