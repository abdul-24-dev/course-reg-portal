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

$matric = strtoupper($_SESSION['username']);
$student_query = $conn->query("SELECT s.*, d.name AS department_name FROM students s JOIN departments d ON s.department_id = d.id WHERE UPPER(s.matric_number) = '$matric'");
$student = $student_query->fetch_assoc();

$registered_courses = $conn->query("
    SELECT c.id AS course_id
    FROM student_courses sc 
    JOIN courses c ON sc.course_id = c.id 
    WHERE sc.student_id = '{$student['id']}'
");

$FACULTY = "Faculty of Science";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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
            color: #1e293b;
            font-size: 1.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .info-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }

        .info-card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-card-header i {
            color: var(--secondary-color);
            font-size: 1.1rem;
        }

        .info-card-header h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
        }

        .student-profile {
            text-align: center;
            padding: 1rem 0;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid var(--secondary-color);
            object-fit: cover;
            background: white;
            margin-bottom: 1rem;
        }

        .student-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }

        .student-matric {
            color: #64748b;
            font-size: 0.9rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.6rem 0;
            border-bottom: 1px solid #f8fafc;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #64748b;
            font-size: 0.875rem;
        }

        .detail-value {
            color: #1e293b;
            font-weight: 500;
            font-size: 0.875rem;
            text-align: right;
        }

        .study-mode-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .study-mode-badge.full {
            background: #dcfce7;
            color: #16a34a;
        }

        .study-mode-badge.part {
            background: #fef3c7;
            color: #d97706;
        }

        .courses-count {
            text-align: center;
            padding: 1rem;
        }

        .courses-count h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .courses-count p {
            color: #64748b;
            font-size: 0.875rem;
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
                <a href="student_dashboard.php" class="active">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="register_courses.php">
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
            <h1>Welcome, <?php echo explode(' ', $student['full_name'])[0]; ?>!</h1>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <div class="student-profile">
                    <?php 
                    $photo_path = (!empty($student['profile_image'])) ? 'uploads/'.$student['profile_image'] : 'avatar.png';
                    ?>
                    <img src="<?php echo $photo_path; ?>" class="profile-avatar" alt="Student">
                    <div class="student-name"><?php echo $student['full_name']; ?></div>
                    <div class="student-matric"><?php echo $student['matric_number']; ?></div>
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-user"></i>
                    <h3>Personal Details</h3>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value"><?php echo $student['email']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value"><?php echo $student['phone_number']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Level</span>
                    <span class="detail-value"><?php echo $student['level']; ?> Level</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Study Mode</span>
                    <span class="detail-value">
                        <span class="study-mode-badge <?php echo ($student['study_mode'] == 'Full-Time') ? 'full' : 'part'; ?>">
                            <?php echo $student['study_mode']; ?>
                        </span>
                    </span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-building"></i>
                    <h3>Academic Details</h3>
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
                    <span class="detail-label">Registered Courses</span>
                    <span class="detail-value"><?php echo $registered_courses->num_rows; ?></span>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>