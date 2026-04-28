<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$matric = $_SESSION['username'];

$stmt = $conn->prepare("SELECT s.*, d.name AS department_name FROM students s JOIN departments d ON s.department_id = d.id WHERE s.matric_number = ?");
$stmt->bind_param("s", $matric);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$FACULTY = "Faculty of Science";
$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($new_password) || !empty($current_password)) {
        if (empty($current_password)) {
            $message = 'Please enter your current password';
            $message_type = 'error';
        } elseif (!password_verify($current_password, $student['password'])) {
            $message = 'Current password is incorrect';
            $message_type = 'error';
        } elseif (empty($new_password)) {
            $message = 'Please enter a new password';
            $message_type = 'error';
        } elseif ($new_password !== $confirm_password) {
            $message = 'New passwords do not match';
            $message_type = 'error';
        } elseif (strlen($new_password) < 8) {
            $message = 'Password must be at least 8 characters';
            $message_type = 'error';
        } else {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE students SET email = ?, phone_number = ?, password = ? WHERE matric_number = ?");
            $stmt->bind_param("ssss", $email, $phone, $hash, $matric);
            $stmt->execute();
            $message = 'Profile updated successfully!';
            $message_type = 'success';
        }
    } else {
        $stmt = $conn->prepare("UPDATE students SET email = ?, phone_number = ? WHERE matric_number = ?");
        $stmt->bind_param("sss", $email, $phone, $matric);
        $stmt->execute();
        $message = 'Profile updated successfully!';
        $message_type = 'success';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Student Portal</title>
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
            background: linear-gradient(135deg, #eef2ff 0%, #f0f4f8 100%);
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
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            width: 100%;
            max-width: 720px;
        }

        .page-header h1 {
            color: #1e293b;
            font-size: 1.75rem;
            font-weight: 700;
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

        .toast i {
            font-size: 1.4rem;
        }

        .profile-container {
            width: 100%;
            max-width: 720px;
        }

        .profile-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 8px 40px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .card-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            padding-bottom: 1.25rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .card-title i {
            font-size: 1.5rem;
            color: var(--secondary-color);
        }

        .card-title h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .info-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #f8fafc;
            color: #1e293b;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            outline: none;
            background: white;
        }

        .form-control:read-only {
            background: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
            border-color: transparent;
        }

        .password-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 8px 40px rgba(0,0,0,0.1);
        }

        .password-section {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 18px;
            padding: 2rem;
        }

        .password-section h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .password-section h4 i {
            color: var(--secondary-color);
        }

        .password-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .password-note {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 0.75rem;
        }

        .btn-save {
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 2rem;
        }

        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.35);
        }

        @media (max-width: 900px) {
            .info-row, .password-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .profile-card, .password-card {
                padding: 1.5rem;
            }

            .password-section {
                padding: 1.25rem;
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
                <a href="profile.php" class="active">
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

    <div class="toast-container">
        <div class="toast <?php echo $message_type; ?>" id="toast">
            <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
            <span><?php echo $message; ?></span>
        </div>
    </div>

    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-user-edit me-2"></i>Edit Profile</h1>
        </div>

        <div class="profile-container">
            <div class="profile-card">
                <div class="card-title">
                    <i class="fas fa-id-card"></i>
                    <h3>Personal Information</h3>
                </div>

                <form method="POST">
                    <div class="info-row">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" class="form-control" value="<?php echo $student['full_name']; ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Matric Number</label>
                            <input type="text" class="form-control" value="<?php echo $student['matric_number']; ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $student['email']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo $student['phone_number']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Level</label>
                            <input type="text" class="form-control" value="<?php echo $student['level']; ?> Level" readonly>
                        </div>

                        <div class="form-group">
                            <label>Study Mode</label>
                            <input type="text" class="form-control" value="<?php echo $student['study_mode']; ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Department</label>
                            <input type="text" class="form-control" value="<?php echo $student['department_name']; ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Faculty</label>
                            <input type="text" class="form-control" value="<?php echo $FACULTY; ?>" readonly>
                        </div>
                    </div>
                </form>
            </div>

            <div class="password-card">
                <div class="card-title">
                    <i class="fas fa-lock"></i>
                    <h3>Change Password</h3>
                </div>

                <div class="password-section">
                    <form method="POST">
                        <div class="password-row">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" class="form-control" placeholder="Enter current password">
                            </div>

                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Enter new password">
                            </div>

                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password">
                            </div>
                        </div>

                        <p class="password-note">Leave blank to keep your current password</p>

                        <button type="submit" class="btn-save">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
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