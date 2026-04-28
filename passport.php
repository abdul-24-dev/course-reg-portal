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

$current_photo = $student['profile_image'] ?? null;
$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES['passport']) && $_FILES['passport']['error'] == 0) {
        $dir = "uploads/";
        if (!file_exists($dir)) mkdir($dir, 0777, true);

        $ext = strtolower(pathinfo($_FILES["passport"]["name"], PATHINFO_EXTENSION));
        $file = "IMG_" . time() . "_" . str_replace(['/','\\'],'-',$matric) . "." . $ext;
        $target = $dir . $file;

        if (getimagesize($_FILES["passport"]["tmp_name"]) && $_FILES["passport"]["size"] < 2000000) {
            move_uploaded_file($_FILES["passport"]["tmp_name"], $target);

            $stmt = $conn->prepare("UPDATE students SET profile_image = ? WHERE matric_number = ?");
            $stmt->bind_param("ss", $file, $matric);
            $stmt->execute();

            $message = 'Photo uploaded successfully!';
            $message_type = 'success';
        } else {
            $message = 'Invalid file or file too large (max 2MB)';
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Photo | Student Portal</title>
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
            justify-content: center;
            min-height: calc(100vh - 4rem);
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            width: 100%;
            max-width: 480px;
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

        .upload-card {
            background: white;
            border-radius: 28px;
            padding: 3rem;
            box-shadow: 0 12px 50px rgba(0,0,0,0.12);
            width: 100%;
            max-width: 480px;
            text-align: center;
        }

        .photo-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 1.75rem;
        }

        .photo-preview {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid white;
            box-shadow: 0 10px 40px rgba(37, 99, 235, 0.3);
        }

        .photo-badge {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .student-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.35rem;
        }

        .student-matric {
            color: #64748b;
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 18px;
            padding: 2rem;
            background: #f8fafc;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .upload-box:hover {
            border-color: var(--secondary-color);
            background: #eff6ff;
        }

        .upload-box input[type="file"] {
            width: 100%;
            font-size: 0.95rem;
            color: #64748b;
        }

        .btn-upload {
            width: 100%;
            padding: 1.1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-upload:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.35);
        }

        .requirements {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 1.5rem;
            margin-top: 1.75rem;
            text-align: left;
        }

        .requirements h4 {
            font-size: 0.9rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .requirements h4 i {
            color: var(--secondary-color);
        }

        .requirements ul {
            list-style: none;
        }

        .requirements li {
            font-size: 0.85rem;
            color: #64748b;
            padding: 0.45rem 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .requirements li i {
            color: #059669;
            font-size: 0.75rem;
        }

        .requirements li i.fa-times {
            color: #dc2626;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .upload-card {
                padding: 2rem;
            }

            .photo-preview {
                width: 140px;
                height: 140px;
            }

            .photo-badge {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .student-name {
                font-size: 1.25rem;
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
                <a href="passport.php" class="active">
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

    <div class="toast-container">
        <div class="toast <?php echo $message_type; ?>" id="toast">
            <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
            <span><?php echo $message; ?></span>
        </div>
    </div>

    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-camera me-2"></i>Upload Photo</h1>
        </div>

        <div class="upload-card">
            <div class="photo-wrapper">
                <?php 
                $photo = !empty($current_photo) ? "uploads/".$current_photo : "avatar.png";
                ?>
                <img src="<?php echo $photo; ?>" class="photo-preview" alt="Profile Photo">
                <div class="photo-badge">
                    <i class="fas fa-camera"></i>
                </div>
            </div>

            <div class="student-name"><?php echo $student['full_name']; ?></div>
            <div class="student-matric"><?php echo $student['matric_number']; ?></div>

            <form method="POST" enctype="multipart/form-data">
                <div class="upload-box">
                    <input type="file" name="passport" accept="image/*" required>
                </div>

                <button type="submit" class="btn-upload">
                    <i class="fas fa-cloud-upload-alt me-2"></i>Upload Photo
                </button>
            </form>

            <div class="requirements">
                <h4><i class="fas fa-info-circle me-1"></i>Photo Requirements</h4>
                <ul>
                    <li><i class="fas fa-check"></i>JPG or PNG format</li>
                    <li><i class="fas fa-check"></i>Maximum size: 2MB</li>
                    <li><i class="fas fa-check"></i>Square image recommended</li>
                </ul>
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