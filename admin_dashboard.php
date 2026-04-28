<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include 'config.php';

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];
$admin_dept = $_SESSION['admin_dept'];
$is_super = $_SESSION['is_super'];

$departments = $conn->query("SELECT * FROM departments");
$sessions = $conn->query("SELECT * FROM sessions");
$semesters = $conn->query("SELECT * FROM semesters");

if ($is_super) {
    $courses = $conn->query("
        SELECT c.*, d.name AS department_name, s.name AS session_name, sem.name AS semester_name 
        FROM courses c 
        JOIN departments d ON c.department_id = d.id 
        JOIN sessions s ON c.session_id = s.id 
        JOIN semesters sem ON c.semester_id = sem.id
        ORDER BY c.level, c.id DESC
    ");
} else {
    $courses = $conn->query("
        SELECT c.*, d.name AS department_name, s.name AS session_name, sem.name AS semester_name 
        FROM courses c 
        JOIN departments d ON c.department_id = d.id 
        JOIN sessions s ON c.session_id = s.id 
        JOIN semesters sem ON c.semester_id = sem.id
        WHERE c.department_id = $admin_dept
        ORDER BY c.level, c.id DESC
    ");
}

$dept_name = 'All Departments';
if ($admin_dept) {
    $dept_query = $conn->query("SELECT name FROM departments WHERE id = $admin_dept");
    $dept_row = $dept_query->fetch_assoc();
    if ($dept_row) {
        $dept_name = $dept_row['name'];
    }
}

$message = '';
$message_type = '';

if (isset($_GET['success'])) {
    $message = 'Operation completed successfully!';
    $message_type = 'success';
}

if (isset($_GET['error'])) {
    $message = 'An error occurred. Please try again.';
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
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
    font-size: 1.75rem;
    font-weight: 700;
}

.welcome-banner {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 20px;
    padding: 2rem;
    color: white;
    margin-bottom: 2rem;
}

.welcome-banner h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.welcome-banner p {
    opacity: 0.9;
}

.toast-container {
    position: fixed;
    top: 1.5rem;
    right: 1.5rem;
    z-index: 9999;
}

.toast {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem 2rem;
    border-radius: 14px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    font-size: 1.1rem;
    font-weight: 500;
    transform: translateX(120%);
    transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.toast.show {
    transform: translateX(0);
}

.toast.success {
    background: #059669;
    color: white;
}

.toast.error {
    background: #dc2626;
    color: white;
}

.action-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.card-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f1f5f9;
}

.card-title i {
    font-size: 1.25rem;
    color: var(--secondary-color);
}

.card-title h3 {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
}

.form-group label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-control, .form-select {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    background: #f8fafc;
}

.form-control:focus, .form-select:focus {
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    outline: none;
    background: white;
}

.btn-add {
    padding: 0.875rem 2rem;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

.data-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.table-container {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem;
    text-align: left;
    border-bottom: 2px solid #e2e8f0;
}

.table td {
    padding: 1rem;
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
}

.table tbody tr:hover {
    background: #f8fafc;
}

.badge-dept {
    padding: 0.35rem 0.75rem;
    background: #e0e7ff;
    color: #4338ca;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-unit {
    padding: 0.35rem 0.75rem;
    background: #dcfce7;
    color: #15803d;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.btn-action {
    padding: 0.45rem 0.75rem;
    border: none;
    border-radius: 8px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-right: 0.5rem;
}

.btn-edit {
    background: #fef3c7;
    color: #b45309;
}

.btn-edit:hover {
    background: #fde68a;
}

.btn-delete {
    background: #fee2e2;
    color: #dc2626;
}

.btn-delete:hover {
    background: #fecaca;
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

.nav-badge {
    padding: 0.25rem 0.6rem;
    background: rgba(255,255,255,0.2);
    border-radius: 20px;
    font-size: 0.7rem;
    margin-left: auto;
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .main-content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h4><i class="fas fa-user-shield"></i> Admin Portal</h4>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="admin_dashboard.php" class="active">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="admin_students.php">
                    <i class="fas fa-users"></i> Students
                </a>
            </li>
            <?php if($is_super): ?>
            <li>
                <a href="admin_users.php">
                    <i class="fas fa-user-plus"></i> Admin Users
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <div class="sidebar-divider"></div>

        <ul class="sidebar-menu">
            <li>
                <a href="ad_logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </aside>

    <div class="toast-container">
        <div class="toast <?php echo $message_type; ?>" id="toast">
            <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
            <span><?php echo $message; ?></span>
        </div>
    </div>

    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
        </div>

        <div class="welcome-banner">
            <h2>Welcome, <?php echo $admin_name; ?>!</h2>
            <p>
                <?php if($is_super): ?>
                    Managing all departments
                <?php else: ?>
                    Managing <?php echo $dept_name; ?>
                <?php endif; ?>
            </p>
        </div>

        <div class="action-card">
            <div class="card-title">
                <i class="fas fa-plus-circle"></i>
                <h3>Add New Course</h3>
            </div>

            <form method="POST" action="add_course.php">
                <div class="form-row">
                    <div class="form-group">
                        <label>Course Code</label>
                        <input type="text" name="course_code" class="form-control" placeholder="e.g. CSC101" required>
                    </div>

                    <div class="form-group">
                        <label>Course Name</label>
                        <input type="text" name="course_name" class="form-control" placeholder="Course title" required>
                    </div>

                    <div class="form-group">
                        <label>Credit Units</label>
                        <input type="number" name="credit_unit" class="form-control" placeholder="Units" required>
                    </div>

                    <div class="form-group">
                        <label>Level</label>
                        <select name="level" class="form-select" required>
                            <option value="">Select</option>
                            <option value="100">100L</option>
                            <option value="200">200L</option>
                            <option value="300">300L</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Semester</label>
                        <select name="semester_id" class="form-select" required>
                            <option value="">Select</option>
                            <?php $semesters->data_seek(0); while ($semester = $semesters->fetch_assoc()): ?>
                                <option value="<?php echo $semester['id']; ?>"><?php echo $semester['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Session</label>
                        <select name="session_id" class="form-select" required>
                            <option value="">Select</option>
                            <?php $sessions->data_seek(0); while ($session = $sessions->fetch_assoc()): ?>
                                <option value="<?php echo $session['id']; ?>"><?php echo $session['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <?php if($is_super): ?>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select</option>
                                <option value="all">All Departments</option>
                                <?php $departments->data_seek(0); while ($dept = $departments->fetch_assoc()): ?>
                                    <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" class="form-control" value="<?php echo $dept_name; ?>" readonly>
                            <input type="hidden" name="department_id" value="<?php echo $admin_dept; ?>">
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn-add">
                    <i class="fas fa-save me-2"></i>Add Course
                </button>
            </form>
        </div>

        <div class="data-card">
            <div class="card-title">
                <i class="fas fa-book"></i>
                <h3>Courses (<?php echo $courses->num_rows; ?>)</h3>
            </div>

            <?php if($courses->num_rows > 0): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Units</th>
                                <th>Level</th>
                                <th>Semester</th>
                                <th>Session</th>
                                <th>Department</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($course = $courses->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo $course['course_code']; ?></strong></td>
                                    <td><?php echo $course['course_name']; ?></td>
                                    <td><span class="badge-unit"><?php echo $course['credit_unit']; ?></span></td>
                                    <td><span class="badge-unit"><?php echo $course['level']; ?>L</span></td>
                                    <td><?php echo $course['semester_name']; ?></td>
                                    <td><?php echo $course['session_name']; ?></td>
                                    <td><span class="badge-dept"><?php echo $course['department_name']; ?></span></td>
                                    <td>
                                        <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn-action btn-edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_course.php?id=<?php echo $course['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this course?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <p>No courses added yet.</p>
                </div>
            <?php endif; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php if ($message): ?>
    const toast = document.getElementById('toast');
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 4000);
<?php endif; ?>
</script>
</body>
</html>