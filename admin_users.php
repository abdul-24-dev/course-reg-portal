<?php
session_start();

if (!isset($_SESSION['admin_id']) || !$_SESSION['is_super']) {
    header("Location: admin_login.php");
    exit();
}

include 'config.php';

$departments = $conn->query("SELECT * FROM departments");

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_admin'])) {
        $delete_id = $_POST['delete_admin'];
        if ($delete_id == $_SESSION['admin_id']) {
            $message = 'You cannot delete yourself';
            $message_type = 'error';
        } else {
            $stmt = $conn->prepare("DELETE FROM admins WHERE id = ? AND is_super_admin = 0");
            $stmt->bind_param("i", $delete_id);
            if ($stmt->execute()) {
                $message = 'Admin deleted successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error deleting admin';
                $message_type = 'error';
            }
        }
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $full_name = trim($_POST['full_name']);
        $department_id = $_POST['department_id'] ?: null;
        
        if (empty($username) || empty($password) || empty($full_name)) {
            $message = 'All fields are required';
            $message_type = 'error';
        } elseif (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters';
            $message_type = 'error';
        } else {
            $check = $conn->prepare("SELECT id FROM admins WHERE username = ?");
            $check->bind_param("s", $username);
            $check->execute();
            
            if ($check->get_result()->num_rows > 0) {
                $message = 'Username already exists';
                $message_type = 'error';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $is_super = 0;
                $stmt = $conn->prepare("INSERT INTO admins (username, password, full_name, department_id, is_super_admin) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssii", $username, $hash, $full_name, $department_id, $is_super);
                
                if ($stmt->execute()) {
                    $message = 'Admin created successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error creating admin';
                    $message_type = 'error';
                }
            }
        }
    }
}

$admins = $conn->query("SELECT a.*, d.name AS department_name FROM admins a LEFT JOIN departments d ON a.department_id = d.id WHERE a.is_super_admin = 0");
$super_admins = $conn->query("SELECT * FROM admins WHERE is_super_admin = 1");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Users | Admin Portal</title>
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

.page-header { margin-bottom: 2rem; }

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

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    max-width: 1000px;
}

.card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f1f5f9;
}

.card-header i { font-size: 1.25rem; color: var(--secondary-color); }
.card-header h3 { font-size: 1.1rem; font-weight: 700; color: #1e293b; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

.form-group { margin-bottom: 1rem; }

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

.btn-submit {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 0.5rem;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
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

.table td {
    padding: 1rem;
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
}

.table tbody tr:hover { background: #f8fafc; }

.badge {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-dept { background: #e0e7ff; color: #4338ca; }
.badge-super { background: #fef3c7; color: #b45309; }

.btn-delete {
    padding: 0.5rem 1rem;
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-delete:hover { background: #fecaca; }

.empty-state {
    text-align: center;
    padding: 2rem;
    color: #94a3b8;
}

.empty-state i {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
    opacity: 0.5;
}

.super-admin-card {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 20px;
    padding: 1.5rem;
    color: white;
    margin-bottom: 1.5rem;
}

.super-admin-card h3 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.super-admin-card p {
    font-size: 0.9rem;
    opacity: 0.9;
}

@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .main-content { margin-left: 0; padding: 1rem; }
    .content-grid { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
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
                <a href="admin_students.php">
                    <i class="fas fa-users"></i> Students
                </a>
            </li>
            <li>
                <a href="admin_users.php" class="active">
                    <i class="fas fa-user-shield"></i> Admin Users
                </a>
            </li>
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
            <h1><i class="fas fa-user-shield me-2"></i>Admin Users</h1>
        </div>

        <?php 
        $super = $super_admins->fetch_assoc();
        ?>
        <div class="super-admin-card">
            <h3><i class="fas fa-crown"></i> Super Admin</h3>
            <p><strong><?php echo $super['username']; ?></strong> - Full access to all departments</p>
        </div>

        <div class="content-grid">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-plus"></i>
                    <h3>Add New Admin</h3>
                </div>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                        </div>

                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control" placeholder="Enter full name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>

                        <div class="form-group">
                            <label>Department</label>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                <?php $departments->data_seek(0); while ($dept = $departments->fetch_assoc()): ?>
                                    <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-user-plus me-2"></i>Create Admin
                    </button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-users"></i>
                    <h3>Department Admins (<?php echo $admins->num_rows; ?>)</h3>
                </div>

                <?php if($admins->num_rows > 0): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Department</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($admin = $admins->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $admin['username']; ?></strong>
                                            <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.25rem;"><?php echo $admin['full_name']; ?></div>
                                        </td>
                                        <td><span class="badge badge-dept"><?php echo $admin['department_name']; ?></span></td>
                                        <td>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="delete_admin" value="<?php echo $admin['id']; ?>">
                                                <button type="submit" class="btn-delete" onclick="return confirm('Delete this admin?')">
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
                        <i class="fas fa-user-shield"></i>
                        <p>No department admins yet</p>
                    </div>
                <?php endif; ?>
            </div>
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