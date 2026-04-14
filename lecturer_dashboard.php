<?php
session_start();
include('db.php');

// Redirect if not logged in as lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lecturer') {
    header('Location: lecturer_login.php');
    exit();
}

$lecturer_id = $_SESSION['user_id'];

$stmt_lecturer = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt_lecturer->bind_param("i", $lecturer_id);
$stmt_lecturer->execute();
$res_lecturer = $stmt_lecturer->get_result();
$lecturer = $res_lecturer->fetch_assoc();
$stmt_lecturer->close();

$lecturer_name = $lecturer['full_name'] ?? 'Lecturer';

$message = "";
$message_type = "success";

/* =========================
   HANDLE FORMS
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    /* ===== ADD GRADES ===== */
    if (isset($_POST['add_grades'])) {
        $student_id = $_POST['student_id'];
        $unit_id    = $_POST['unit_id'];
        $cat        = $_POST['cat_score'];
        $exam       = $_POST['exam_score'];
        $total      = $cat + $exam;

        // Calculate Grade and Points based on new schema
        $grade = 'E'; $points = 0.0;
        if ($total >= 70) { $grade = 'A'; $points = 4.0; }
        elseif ($total >= 60) { $grade = 'B'; $points = 3.0; }
        elseif ($total >= 50) { $grade = 'C'; $points = 2.0; }
        elseif ($total >= 40) { $grade = 'D'; $points = 1.0; }

        $stmt = $conn->prepare("
            INSERT INTO results (student_id, unit_id, marks, grade, points)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            marks = VALUES(marks),
            grade = VALUES(grade),
            points = VALUES(points)
        ");
        $stmt->bind_param("iidsd", $student_id, $unit_id, $total, $grade, $points);

        $message = $stmt->execute() ? "✅ Grade saved successfully!" : "❌ Error saving grade!";
        if (strpos($message, '❌') !== false) $message_type = "error";
    }

    /* ===== ADD UNIT ===== */
    if (isset($_POST['add_unit'])) {
        $unit_name = trim($_POST['unit_name']);
        $unit_code = trim($_POST['unit_code']);
        $course_id = $_POST['course_id'];

        // Check if the unit code already exists first for better UX
        $check = $conn->prepare("SELECT unit_name FROM units WHERE unit_code = ?");
        $check->bind_param("s", $unit_code);
        $check->execute();
        $check_res = $check->get_result();

        if ($check_res->num_rows > 0) {
            $existing = $check_res->fetch_assoc();
            $message = "❌ Error: The code '$unit_code' is already assigned to '{$existing['unit_name']}'.";
            $message_type = "error";
        } else {
            $stmt = $conn->prepare("INSERT INTO units (unit_name, unit_code, course_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $unit_name, $unit_code, $course_id);
            $message = $stmt->execute() ? "✅ Unit '$unit_name' added successfully!" : "❌ Error adding unit!";
            if (strpos($message, '❌') !== false) $message_type = "error";
            $stmt->close();
        }
        $check->close();
    }

    /* ===== ADD COURSE ===== */
    if (isset($_POST['add_course'])) {
        $course_name = trim($_POST['course_name']);

        // Check if the course already exists
        $check = $conn->prepare("SELECT id FROM courses WHERE course_name = ?");
        $check->bind_param("s", $course_name);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $message = "❌ Error: The course '$course_name' already exists.";
            $message_type = "error";
        } else {
            $stmt = $conn->prepare("INSERT INTO courses (course_name) VALUES (?)");
            $stmt->bind_param("s", $course_name);
            $message = $stmt->execute() ? "✅ Course created successfully!" : "❌ Error creating course!";
            if (strpos($message, '❌') !== false) $message_type = "error";
            $stmt->close();
        }
        $check->close();
    }

    /* ===== SEND NOTIFICATION ===== */
    if (isset($_POST['send_notification'])) {
        $msg = trim($_POST['notification']);
        $students = $conn->query("SELECT user_id FROM students");

        while ($s = $students->fetch_assoc()) {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt->bind_param("is", $s['user_id'], $msg);
            $stmt->execute();
        }
        $message = "🔔 Notification sent!";
    }
}

/* =========================
   FETCH DATA FOR STATS & FORMS
========================= */
$total_students_count = 0;
$total_units_count = 0;
$student_list = null;
$unit_list = null;
$course_list = null;

try {
    $student_list = $conn->query("
        SELECT s.id, u.full_name, u.reg_number, c.course_name 
        FROM students s 
        JOIN users u ON s.user_id = u.id 
        LEFT JOIN courses c ON s.course_id = c.id
    ");
    $total_students_count = $student_list ? $student_list->num_rows : 0;

    $unit_list = $conn->query("
        SELECT u.*, c.course_name 
        FROM units u 
        LEFT JOIN courses c ON u.course_id = c.id
    ");
    $total_units_count = $unit_list ? $unit_list->num_rows : 0;

    $course_list = $conn->query("SELECT * FROM courses");
} catch (mysqli_sql_exception $e) {
    $message = "⚠️ Database tables are missing. Please run the SQL setup script.";
    $message_type = "error";
}

// Reset pointers for reuse in forms
if($student_list) $student_list->data_seek(0);
if($unit_list) $unit_list->data_seek(0);

?>

<!DOCTYPE html>
<html>
<head>
<title>Lecturer Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #4f46e5;
    --primary-dark: #4338ca;
    --success: #22c55e;
    --danger: #ef4444;
    --bg: #f4f6fc;
    --card: #fff;
}

*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif;}
body{display:flex; min-height:100vh; background:var(--bg); color:#333;}
a{text-decoration:none; color:inherit;}
ul{list-style:none;}

/* SIDEBAR */
.sidebar{
    width:250px; background:var(--primary); color:white; display:flex; flex-direction:column;
    padding:20px; transition: width 0.3s;
}
.sidebar h2{margin-bottom:30px; font-size:20px;}
.sidebar a{padding:12px 15px; border-radius:8px; margin-bottom:10px; display:block; color:white; transition:0.2s;}
.sidebar a.active, .sidebar a:hover{background:var(--primary-dark);}

/* MAIN CONTENT */
.main{flex:1; padding:30px; overflow-y:auto;}
.header{display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;}
.header h2{font-weight:500;}
.header a{background:var(--danger); padding:8px 12px; color:white; border-radius:6px; transition:0.2s;}
.header a:hover{opacity:0.85;}

/* MESSAGE */
.message{padding:12px; margin-bottom:20px; border-radius:8px; font-weight: 500;}
.success{background:rgba(34,197,94,0.15); color:var(--success); border:1px solid var(--success);}
.error{background:rgba(239,68,68,0.15); color:var(--danger); border:1px solid var(--danger);}

/* CARDS */
.card{background:var(--card); padding:25px; border-radius:12px; box-shadow:0 10px 20px rgba(0,0,0,0.05); margin-bottom:20px;}
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
.stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; border-bottom: 4px solid var(--primary); }
.stat-card h4 { color: #64748b; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
.stat-card .value { font-size: 28px; font-weight: 700; color: var(--primary); }

/* TABLES */
table { width: 100%; border-collapse: collapse; margin-top: 15px; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
th { background: #f8fafc; color: #64748b; font-weight: 600; }
.btn-view { color: var(--primary); font-weight: 600; }

/* FILTER CONTROLS */
.filter-row { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
.filter-row div { flex: 1; min-width: 200px; }

/* FORMS */
input, select, textarea{width:100%; padding:12px; margin:8px 0 16px; border:1px solid #ccc; border-radius:8px; font-size:14px;}
button{padding:12px 18px; background:var(--primary); color:white; border:none; border-radius:8px; cursor:pointer; transition:0.2s;}
button:hover{background:var(--primary-dark);}

/* SECTIONS */
.section{display:none; animation:fadeIn 0.3s ease;}
.active-section{display:block;}
@keyframes fadeIn{from{opacity:0; transform:translateY(10px);} to{opacity:1; transform:translateY(0);}}

@media(max-width:768px){
    body{flex-direction:column;}
    .sidebar{width:100%; flex-direction:row; overflow-x:auto;}
    .sidebar h2{display:none;}
    .sidebar a{flex:1; text-align:center; margin:5px;}
    .main{padding:15px;}
}
</style>
</head>
<body>

<div class="sidebar">
    <h2 style="margin-bottom: 5px;">👨‍🏫 Lecturer</h2>
    <a href="index.php" style="font-size: 11px; text-align: center; color: #fbbf24; margin-bottom: 20px; display: block; border: 1px solid rgba(251,191,36,0.3); padding: 5px; border-radius: 5px;">🌐 Visit Public Site</a>
    <a href="#" class="nav-link active" data-target="dashboard">Dashboard</a>
    <a href="#" class="nav-link" data-target="add_course_sec">Add Course</a>
    <a href="#" class="nav-link" data-target="add_unit_sec">Add Unit</a>
    <a href="#" class="nav-link" data-target="grades">Grades</a>
    <a href="#" class="nav-link" data-target="view_units">Units</a>
    <a href="#" class="nav-link" data-target="view_students">Students</a>
    <a href="#" class="nav-link" data-target="notifications">Notifications</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <div class="header">
        <h2>Welcome, <?php echo htmlspecialchars($lecturer_name); ?> 👋</h2>
        <a href="logout.php">Logout</a>
    </div>

    <?php if($message): ?>
    <div class="message <?= $message_type ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- DASHBOARD -->
    <div id="dashboard" class="section active-section">
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Students</h4>
                <div class="value"><?= $total_students_count ?></div>
            </div>
            <div class="stat-card">
                <h4>Units Managed</h4>
                <div class="value"><?= $total_units_count ?></div>
            </div>
        </div>
        <div class="card">
            <h3>📊 Overview</h3>
            <p>Welcome to your dashboard. Use the sidebar to manage materials, grades, and notifications.</p>
        </div>
    </div>

    <!-- ADD COURSE -->
    <div id="add_course_sec" class="section">
        <div class="card">
            <h3>🏫 Create New Course</h3>
            <p style="margin-bottom:15px; color:#64748b; font-size:14px;">Create the main program (e.g., Bachelor of IT) before adding specific units.</p>
            <form method="POST">
                <input type="text" name="course_name" placeholder="Course Name (e.g. Diploma in Computer Science)" required>
                <button name="add_course">Create Course</button>
            </form>
        </div>
    </div>

    <!-- ADD UNIT -->
    <div id="add_unit_sec" class="section">
        <div class="card">
            <h3>📘 Add New Unit</h3>
            <form method="POST">
                <input type="text" name="unit_name" placeholder="Unit Name (e.g. Database Systems)" required>
                <input type="text" name="unit_code" placeholder="Unit Code (e.g. COMP201)" required>
                <select name="course_id" required>
                    <option value="">Assign to Course</option>
                    <?php if($course_list) $course_list->data_seek(0);
                    while($c=$course_list->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <button name="add_unit">Add Unit</button>
            </form>
        </div>
    </div>

    <!-- ADD GRADES -->
    <div id="grades" class="section">
        <div class="card">
            <h3>📊 Add Grades</h3>
            <p style="margin-bottom:15px; color:#64748b; font-size:13px;">Pro-tip: Filter by course first to find students faster.</p>
            <form method="POST">
                <select id="course_filter_grades" onchange="filterStudentDropdown()">
                    <option value="">All Courses (Filter First)</option>
                    <?php if($course_list) $course_list->data_seek(0);
                    while($c=$course_list->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($c['course_name']) ?>"><?= htmlspecialchars($c['course_name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <select name="student_id" id="student_select_grades" required>
                    <option value="">Select Student</option>
                    <?php if($student_list) $student_list->data_seek(0);
                    while($s=$student_list->fetch_assoc()): ?>
                    <option value="<?= $s['id'] ?>" data-course="<?= htmlspecialchars($s['course_name'] ?? '') ?>"><?= htmlspecialchars($s['reg_number']) ?> - <?= htmlspecialchars($s['full_name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <select name="unit_id" required>
                    <option value="">Select Unit</option>
                    <?php if($unit_list) $unit_list->data_seek(0);
                    while($u=$unit_list->fetch_assoc()): ?>
                    <option value="<?= $u['id'] ?>"><?= $u['unit_name'] ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="number" name="cat_score" placeholder="CAT Score (Max 30)" min="0" max="30" step="0.5" required>
                <input type="number" name="exam_score" placeholder="Exam Score (Max 70)" min="0" max="70" step="0.5" required>
                <button name="add_grades">Save Grade</button>
            </form>
        </div>
    </div>

    <!-- VIEW UNITS -->
    <div id="view_units" class="section">
        <div class="card">
            <h3>📘 Registered Units</h3>
            <table>
                <thead>
                    <tr>
                        <th>Unit Code</th>
                        <th>Unit Name</th>
                        <th>Course</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($unit_list) $unit_list->data_seek(0);
                    while($u = $unit_list->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['unit_code']) ?></td>
                        <td><?= htmlspecialchars($u['unit_name']) ?></td>
                        <td><?= htmlspecialchars($u['course_name'] ?? 'N/A') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- VIEW STUDENTS -->
    <div id="view_students" class="section">
        <div class="card">
            <h3>👥 Registered Students</h3>
            
            <div class="filter-row">
                <div>
                    <input type="text" id="studentSearch" placeholder="Search by name or reg number..." onkeyup="filterStudentTable()">
                </div>
                <div>
                    <select id="courseFilter" onchange="filterStudentTable()">
                        <option value="">All Courses</option>
                        <?php if($course_list) $course_list->data_seek(0);
                        while($c=$course_list->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($c['course_name']) ?>"><?= htmlspecialchars($c['course_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <table id="studentTable">
                <thead>
                    <tr>
                        <th>Reg Number</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody">
                    <?php if($student_list) $student_list->data_seek(0);
                    while($s = $student_list->fetch_assoc()): ?>
                    <tr data-course="<?= htmlspecialchars($s['course_name'] ?? 'N/A') ?>">
                        <td><?= htmlspecialchars($s['reg_number']) ?></td>
                        <td><?= htmlspecialchars($s['full_name']) ?></td>
                        <td><?= htmlspecialchars($s['course_name'] ?? 'N/A') ?></td>
                        <td><button type="button" style="padding:5px 10px; font-size:12px;" onclick="quickGrade('<?= $s['id'] ?>')">Grade</button></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- NOTIFICATIONS -->
    <div id="notifications" class="section">
        <div class="card">
            <h3>🔔 Send Notification</h3>
            <form method="POST">
                <textarea name="notification" placeholder="Type message..." required></textarea>
                <button name="send_notification">Send</button>
            </form>
        </div>
    </div>
</div>

<script>
// Sidebar navigation functionality
const links = document.querySelectorAll(".nav-link");
const sections = document.querySelectorAll(".section");

links.forEach(link => {
    link.addEventListener("click", function(e){
        e.preventDefault();
        links.forEach(l => l.classList.remove("active"));
        this.classList.add("active");
        sections.forEach(sec => sec.classList.remove("active-section"));
        const target = document.getElementById(this.dataset.target);
        target.classList.add("active-section");
    });
});

// Filter Student Table (Students Tab)
function filterStudentTable() {
    let input = document.getElementById("studentSearch").value.toLowerCase();
    let course = document.getElementById("courseFilter").value.toLowerCase();
    let rows = document.querySelectorAll("#studentTableBody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        let rowCourse = row.getAttribute("data-course").toLowerCase();
        let matchesSearch = text.includes(input);
        let matchesCourse = course === "" || rowCourse === course;

        row.style.display = (matchesSearch && matchesCourse) ? "" : "none";
    });
}

// Filter Student Dropdown (Grades Tab)
function filterStudentDropdown() {
    let course = document.getElementById("course_filter_grades").value.toLowerCase();
    let select = document.getElementById("student_select_grades");
    let options = select.options;

    select.value = ""; // Reset selection

    for (let i = 1; i < options.length; i++) {
        let studentCourse = options[i].getAttribute("data-course").toLowerCase();
        if (course === "" || studentCourse === course) {
            options[i].style.display = "";
        } else {
            options[i].style.display = "none";
        }
    }
}

// Quick Grade Action
function quickGrade(studentId) {
    // Set the student in the dropdown
    const studentSelect = document.getElementById("student_select_grades");
    studentSelect.value = studentId;
    
    // Trigger navigation to grades tab
    const gradesLink = document.querySelector('[data-target="grades"]');
    if(gradesLink) gradesLink.click();
}

</script>

</body>
</html>
