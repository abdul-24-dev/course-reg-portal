<?php
session_start();

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'config.php';

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_dept'] = $admin['department_id'];
        $_SESSION['is_super'] = $admin['is_super_admin'];
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
:root {
    --primary: #1e3a5f;
    --secondary: #2563eb;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', system-ui, sans-serif;
    background: linear-gradient(135deg, #1e3a5f 0%, #1e3a5f 100%) fixed;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-container {
    display: flex;
    max-width: 1000px;
    width: 100%;
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.3);
}

.login-left {
    flex: 1;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    padding: 3rem;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.login-left h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
}

.login-left p {
    opacity: 0.9;
    font-size: 0.95rem;
    line-height: 1.6;
}

.features {
    margin-top: 2rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    margin-bottom: 0.75rem;
}

.feature-item i {
    font-size: 1.1rem;
}

.login-right {
    flex: 1;
    padding: 3rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.login-right h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.login-right .subtitle {
    color: #64748b;
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    display: block;
}

.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    border-color: var(--secondary);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    outline: none;
}

.btn-login {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3);
}

.alert-error {
    padding: 1rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 12px;
    color: #dc2626;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
}

.student-link {
    text-align: center;
    margin-top: 1.5rem;
    color: #64748b;
}

.student-link a {
    color: var(--secondary);
    font-weight: 600;
    text-decoration: none;
}

.student-link a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .login-left {
        display: none;
    }
    
    .login-right {
        padding: 2rem;
    }
}
</style>
</head>
<body>

<div class="login-container">
    <div class="login-left">
        <h2>Admin Portal</h2>
        <p>Manage courses, students, and academic records for your department.</p>
        
        <div class="features">
            <div class="feature-item">
                <i class="fas fa-book"></i>
                <span>Manage Courses</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-users"></i>
                <span>View Students</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-user-plus"></i>
                <span>Add Admin Users</span>
            </div>
        </div>
    </div>
    
    <div class="login-right">
        <h3>Admin Login</h3>
        <p class="subtitle">Sign in to continue</p>

        <?php if($error): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>

            <p class="student-link">
                <a href="login.php"><i class="fas fa-arrow-left me-1"></i>Student Login</a>
            </p>
        </form>
    </div>
</div>

</body>
</html>