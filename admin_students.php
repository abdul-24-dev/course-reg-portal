<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include 'config.php';

$admin_dept = $_SESSION['admin_dept'];
$is_super = $_SESSION['is_super'];
$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_student'])) {
    $student_id = $_POST['delete_student'];
    
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    
    if ($stmt->execute()) {
        $message = 'Student deleted successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error deleting student';
        $message_type = 'error';
    }
}

if ($is_super) {
    $students = $conn->query("
        SELECT s.*, d.name AS department_name 
        FROM students s 
        JOIN departments d ON s.department_id = d.id 
        ORDER BY s.id DESC
    ");
} else {
    $students = $conn->query("
        SELECT s.*, d.name AS department_name 
        FROM students s 
        JOIN departments d ON s.department_id = d.id 
        WHERE s.department_id = $admin_dept
        ORDER BY s.id DESC
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Students | Admin Portal</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
:root {
    --primary-color: #1e3a5f;
    --secondary-color: #2563eb;
    --sidebar-width: 260px;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Segoe UI', system-ui, sans-serif;
    background: linear-gradient(135deg, #eef2ff, #f0f4f8);
    min-height: 100vh;
}

.wrapper { display: flex; min-height: 100vh; }

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

.sidebar-menu li { margin-bottom: 0.25rem; }

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

.sidebar-menu a.active { background: var(--secondary-color); }

.sidebar-menu i { width: 20px; text-align: center; }

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

.page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }

.page-header h1 {
    color: #1e293b;
    font-size: 1.75rem;
    font-weight: 700;
}

.toast-container { position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999; }

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

.toast.show { transform: translateX(0); }
.toast.success { background: #059669; color: white; }
.toast.error { background: #dc2626; color: white; }
.toast i { font-size: 1.4rem; }

.welcome-banner {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 20px;
    padding: 1.5rem 2rem;
    color: white;
    margin-bottom: 2rem;
}

.welcome-banner h2 {
    font-size: 1.25rem;
    font-weight: 700;
}

.download-section {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.download-section h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.download-buttons {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn-download {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-word {
    background: #2563eb;
    color: white;
}

.btn-word:hover {
    background: #1d4ed8;
}

.btn-excel {
    background: #059669;
    color: white;
}

.btn-excel:hover {
    background: #047857;
}

.btn-pdf {
    background: #dc2626;
    color: white;
}

.btn-pdf:hover {
    background: #b91c1c;
}

.data-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.card-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f1f5f9;
}

.card-title-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.card-title-left i { font-size: 1.25rem; color: var(--secondary-color); }
.card-title-left h3 { font-size: 1.15rem; font-weight: 700; color: #1e293b; }

.count-badge {
    background: var(--secondary-color);
    color: white;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.table-container { overflow-x: auto; }

.table { width: 100%; border-collapse: collapse; }

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

.table th:first-child {
    width: 60px;
    text-align: center;
}

.table td {
    padding: 1rem;
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
}

.table td:first-child {
    text-align: center;
    color: #64748b;
    font-weight: 500;
}

.table tbody tr:hover { background: #f8fafc; }

.badge-dept {
    padding: 0.35rem 0.75rem;
    background: #e0e7ff;
    color: #4338ca;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.btn-delete {
    padding: 0.5rem 0.75rem;
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: 8px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s ease;
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

@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .main-content { margin-left: 0; padding: 1rem; }
    .download-section { flex-direction: column; align-items: flex-start; }
    .btn-download { width: 100%; justify-content: center; }
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
                <a href="admin_dashboard.php">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="admin_students.php" class="active">
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
            <h1><i class="fas fa-users me-2"></i>Students</h1>
        </div>

        <div class="welcome-banner">
            <h2>
                <?php if($is_super): ?>
                    All Students
                <?php else: ?>
                    Students - <?php echo $dept_name; ?>
                <?php endif; ?>
            </h2>
        </div>

        <div class="download-section">
            <h3><i class="fas fa-download"></i> Download Student List</h3>
            <div class="download-buttons">
                <a href="print_students.php?download=1" class="btn-download btn-pdf" target="_blank">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="download_students.php?format=word" class="btn-download btn-word">
                    <i class="fas fa-file-word"></i> Word
                </a>
                <a href="download_students.php?format=excel" class="btn-download btn-excel">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
            </div>
        </div>

        <div class="data-card">
            <div class="card-title">
                <div class="card-title-left">
                    <i class="fas fa-user-graduate"></i>
                    <h3>Student Records</h3>
                </div>
                <span class="count-badge"><?php echo $students->num_rows; ?> Students</span>
            </div>

            <?php if($students->num_rows > 0): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Matric Number</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Level</th>
                                <th>Department</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $serial = 1;
                            while($student = $students->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $serial++; ?></td>
                                    <td><strong><?php echo $student['matric_number']; ?></strong></td>
                                    <td><?php echo $student['full_name']; ?></td>
                                    <td><?php echo $student['email']; ?></td>
                                    <td><?php echo $student['phone_number']; ?></td>
                                    <td><?php echo $student['level']; ?> Level</td>
                                    <td><span class="badge-dept"><?php echo $student['department_name']; ?></span></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="delete_student" value="<?php echo $student['id']; ?>">
                                            <button type="submit" class="btn-delete" onclick="return confirm('Delete this student?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-graduate"></i>
                    <p>No students found.</p>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php if ($message): ?>
    const toast = document.getElementById('toast');
    toast.classList.add('show');
    setTimeout(() => { toast.classList.remove('show'); }, 4000);
<?php endif; ?>
</script>
</body>
</html>