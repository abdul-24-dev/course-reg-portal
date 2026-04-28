<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$matric = strtoupper($_SESSION['username']);
$student_query = $conn->query("SELECT s.*, d.name AS department_name FROM students s JOIN departments d ON s.department_id = d.id WHERE UPPER(s.matric_number) = '$matric'");
$student = $student_query->fetch_assoc();

$FACULTY = "Faculty of Science";

$student_level = intval($student['level']);
$sessions_back = $student_level / 100;

$sessions = $conn->query("SELECT * FROM sessions ORDER BY id DESC LIMIT $sessions_back");
$semesters = $conn->query("SELECT * FROM semesters");

$courses = [];
if (isset($_GET['semester_id']) && isset($_GET['session_id'])) {
    $semester_id = intval($_GET['semester_id']);
    $session_id = intval($_GET['session_id']);
    $department_id = $student['department_id'];
    $level = $student['level'];

    $courses = $conn->query("
        SELECT c.*, s.name AS session_name, sem.name AS semester_name 
        FROM courses c 
        JOIN sessions s ON c.session_id = s.id 
        JOIN semesters sem ON c.semester_id = sem.id 
        WHERE c.department_id = $department_id 
        AND c.level = $level
        AND c.semester_id = $semester_id 
        AND c.session_id = $session_id
    ");
} else {
    $courses = $conn->query("SELECT * FROM courses WHERE 1 = 0"); 
}

$registered_courses = $conn->query("
    SELECT c.id AS course_id
    FROM student_courses sc 
    JOIN courses c ON sc.course_id = c.id 
    WHERE sc.student_id = '{$student['id']}'
");
$registered_ids = [];
while ($rc = $registered_courses->fetch_assoc()) {
    $registered_ids[] = $rc['course_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Courses</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e3a5f;
            --secondary-color: #2563eb;
            --sidebar-width: 260px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #eef2ff, #f0f4f8);
            min-height: 100vh;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, #1e3a5f 100%);
            color: white;
            position: fixed;
            height: 100vh;
            padding: 1.5rem 0;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }

        .sidebar-brand h4 {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0 0.75rem;
        }

        .sidebar-menu li {
            margin-bottom: 0.25rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        .sidebar-menu a.active {
            background: var(--secondary-color);
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
        }

        .sidebar-divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 1rem 0;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 1.5rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-header h1 i {
            color: var(--secondary-color);
        }

        .filter-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            margin-bottom: 1.5rem;
        }

        .filter-card h3 {
            font-size: 1rem;
            color: #64748b;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .info-grid {
            display: grid;
            gap: 1rem;
        }

        .info-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }

        .info-card-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-card-header i {
            color: var(--secondary-color);
        }

        .info-card-header h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.4rem 0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #64748b;
            font-size: 0.85rem;
        }

        .detail-value {
            color: #1e293b;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .form-select, .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }

        .form-select:focus, .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .courses-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .courses-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .courses-card-header h3 {
            font-size: 1.1rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .courses-card-body {
            padding: 0;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.875rem 1.25rem;
            border: none;
        }

        .table tbody td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .btn-register {
            background: linear-gradient(135deg, var(--secondary-color), #1d4ed8);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-register:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
            color: white;
        }

        .btn-register:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-registered {
            background: #dcfce7;
            color: #16a34a;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .alert-info {
            background: #eff6ff;
            border: none;
            border-radius: 12px;
            padding: 1.25rem;
            color: #1e40af;
        }

        .alert-info i {
            margin-right: 0.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h4><i class="fas fa-graduation-cap"></i> Student Portal</h4>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="student_dashboard.php">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="register_courses.php" class="active">
                    <i class="fas fa-book-open"></i> Register Course
                </a>
            </li>
        </ul>

        <div class="sidebar-divider"></div>

        <ul class="sidebar-menu">
            <li>
                <a href="passport.php">
                    <i class="fas fa-upload"></i> Upload Photo
                </a>
            </li>
            <li>
                <a href="profile.php">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
        </ul>

        <div class="sidebar-divider"></div>

        <ul class="sidebar-menu">
            <li>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-book-register"></i> Register Courses</h1>
        </div>

        <div class="info-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); margin-bottom: 1.5rem;">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-user"></i>
                    <h3><?php echo $student['full_name']; ?></h3>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Matric Number</span>
                    <span class="detail-value"><?php echo $student['matric_number']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value"><?php echo $student['email']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value"><?php echo $student['phone_number']; ?></span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>Academic Info</h3>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Faculty</span>
                    <span class="detail-value"><?php echo $FACULTY; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Department</span>
                    <span class="detail-value"><?php echo $student['department_name']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Level</span>
                    <span class="detail-value"><?php echo $student['level']; ?> Level</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Study Mode</span>
                    <span class="detail-value"><?php echo $student['study_mode']; ?></span>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <h3><i class="fas fa-filter me-2"></i>Select Semester & Session</h3>
            <form method="GET" action="" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Semester</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-calendar-alt"></i></span>
                            <select name="semester_id" class="form-select" onchange="autoSubmit()" required>
                                <option value="">Select Semester</option>
                                <?php $semesters->data_seek(0); while ($semester = $semesters->fetch_assoc()): ?>
                                    <option value="<?php echo $semester['id']; ?>" <?php echo (isset($_GET['semester_id']) && $_GET['semester_id'] == $semester['id']) ? 'selected' : ''; ?>>
                                        <?php echo $semester['name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Session</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-clock"></i></span>
                            <select name="session_id" class="form-select" onchange="autoSubmit()" required>
                                <option value="">Select Session</option>
                                <?php $sessions->data_seek(0); while ($session = $sessions->fetch_assoc()): ?>
                                    <option value="<?php echo $session['id']; ?>" <?php echo (isset($_GET['session_id']) && $_GET['session_id'] == $session['id']) ? 'selected' : ''; ?>>
                                        <?php echo $session['name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($courses && $courses->num_rows > 0): ?>
            <div class="courses-card">
                <div class="courses-card-header">
                    <h3><i class="fas fa-book me-2"></i>Available Courses</h3>
                </div>
                <div class="courses-card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Course Name</th>
                                    <th>Credits</th>
                                    <th>Semester</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($course = $courses->fetch_assoc()): 
                                    $is_registered = in_array($course['id'], $registered_ids);
                                ?>
                                    <tr>
                                        <td><strong><?php echo $course['course_code']; ?></strong></td>
                                        <td><?php echo $course['course_name']; ?></td>
                                        <td><?php echo $course['credit_unit']; ?></td>
                                        <td><?php echo $course['semester_name']; ?></td>
                                        <td>
                                            <?php if ($is_registered): ?>
                                                <button class="btn-registered" disabled>
                                                    <i class="fas fa-check me-1"></i> Registered
                                                </button>
                                            <?php else: ?>
                                                <form method="POST" action="register_course.php" style="display:inline;">
                                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                    <button type="submit" class="btn-register">
                                                        <i class="fas fa-plus me-1"></i> Register
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Select a Semester and Session to view available courses.
            </div>
        <?php endif; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function autoSubmit() {
        document.getElementById("filterForm").submit();
    }
</script>

</body>
</html>