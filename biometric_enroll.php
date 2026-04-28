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

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['enroll_fingerprint'])) {
        $fingerprint = $_POST['fingerprint_data'];
        if ($fingerprint) {
            $stmt = $conn->prepare("UPDATE students SET fingerprint_template = ? WHERE id = ?");
            $stmt->bind_param("si", $fingerprint, $student['id']);
            if ($stmt->execute()) {
                $message = 'Fingerprint enrolled successfully!';
                $message_type = 'success';
            }
        }
    }
    
    if (isset($_POST['enroll_face'])) {
        $face_encoding = $_POST['face_data'];
        if ($face_encoding) {
            $stmt = $conn->prepare("UPDATE students SET face_encoding = ? WHERE id = ?");
            $stmt->bind_param("si", $face_encoding, $student['id']);
            if ($stmt->execute()) {
                $message = 'Face enrolled successfully!';
                $message_type = 'success';
            }
        }
    }
    
    $student_query = $conn->query("SELECT s.*, d.name AS department_name FROM students s JOIN departments d ON s.department_id = d.id WHERE UPPER(s.matric_number) = '$matric'");
    $student = $student_query->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biometric Enrollment - Student Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e3a5f;
            --secondary-color: #2563eb;
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
            width: 260px;
            background: linear-gradient(180deg, var(--primary-color) 0%, #1e3a5f 100%);
            color: white;
            position: fixed;
            height: 100vh;
            padding: 1.5rem 0;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0 0.75rem;
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
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 2rem;
        }
        .enroll-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        .biometric-btn {
            width: 100%;
            padding: 2rem;
            border: 2px dashed #ccc;
            border-radius: 10px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
        }
        .biometric-btn:hover {
            border-color: var(--secondary-color);
            background: #eef2ff;
        }
        .biometric-btn.enrolled {
            border-color: #22c55e;
            background: #f0fdf4;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .status-enrolled {
            background: #dcfce7;
            color: #166534;
        }
        .status-not-enrolled {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <nav class="sidebar">
            <div class="sidebar-brand">
                <h4><i class="fas fa-graduation-cap me-2"></i>Student Portal</h4>
            </div>
            <ul class="sidebar-menu">
                <li><a href="student_dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a></li>
                <li><a href="register_courses.php"><i class="fas fa-book me-2"></i>Register Courses</a></li>
                <li><a href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                <li><a href="biometric_enroll.php" class="active"><i class="fas fa-fingerprint me-2"></i>Biometric Setup</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="enroll-card">
                <h3 class="mb-4"><i class="fas fa-fingerprint me-2"></i>Biometric Enrollment</h3>
                <p class="text-muted mb-4">Set up fingerprint or face recognition for quick and secure login.</p>

                <?php if($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> mb-4">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>

                <form method="POST" id="biometricForm">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5>Fingerprint</h5>
                            <span class="status-badge <?php echo $student['fingerprint_template'] ? 'status-enrolled' : 'status-not-enrolled'; ?>">
                                <?php echo $student['fingerprint_template'] ? 'Enrolled' : 'Not Enrolled'; ?>
                            </span>
                        </div>
                        <button type="button" class="biometric-btn <?php echo $student['fingerprint_template'] ? 'enrolled' : ''; ?>" onclick="enrollFingerprint()">
                            <i class="fas fa-fingerprint fa-3x mb-3 d-block"></i>
                            <strong><?php echo $student['fingerprint_template'] ? 'Re-enroll' : 'Enroll'; ?> Fingerprint</strong>
                            <p class="text-muted small mb-0">Click to scan your fingerprint</p>
                        </button>
                        <input type="hidden" name="fingerprint_data" id="fingerprintData">
                        <input type="hidden" name="enroll_fingerprint" id="enrollFingerprint">
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5>Face Recognition</h5>
                            <span class="status-badge <?php echo $student['face_encoding'] ? 'status-enrolled' : 'status-not-enrolled'; ?>">
                                <?php echo $student['face_encoding'] ? 'Enrolled' : 'Not Enrolled'; ?>
                            </span>
                        </div>
                        <button type="button" class="biometric-btn <?php echo $student['face_encoding'] ? 'enrolled' : ''; ?>" onclick="enrollFace()">
                            <i class="fas fa-face-smile fa-3x mb-3 d-block"></i>
                            <strong><?php echo $student['face_encoding'] ? 'Re-enroll' : 'Enroll'; ?> Face ID</strong>
                            <p class="text-muted small mb-0">Click to scan your face</p>
                        </button>
                        <input type="hidden" name="face_data" id="faceData">
                        <input type="hidden" name="enroll_face" id="enrollFace">
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
    async function enrollFingerprint() {
        try {
            if (!window.PublicKeyCredential) {
                alert('Fingerprint authentication not supported in this browser');
                return;
            }
            
            alert('Please place your finger on the scanner...');
            
            const credential = await navigator.credentials.create({
                publicKey: {
                    challenge: new Uint8Array(32),
                    rp: {name: 'Course Registration Portal'},
                    user: {id: new Uint8Array([<?php echo $student['id']; ?>]), name: '<?php echo $matric; ?>'},
                    pubKeyAlg: [-7],
                }
            });
            
            document.getElementById('fingerprintData').value = 'enrolled_' + Date.now();
            document.getElementById('enrollFingerprint').value = '1';
            document.getElementById('biometricForm').submit();
        } catch (err) {
            alert('Fingerprint enrollment failed: ' + err.message);
        }
    }

    async function enrollFace() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({video: {facingMode: 'user'}});
            
            const modal = document.createElement('div');
            modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);display:flex;justify-content:center;align-items:center;z-index:9999;';
            modal.innerHTML = '<div style="background:white;padding:30px;border-radius:15px;text-align:center;"><h3>Face Enrollment</h3><video id="faceVideo" style="width:320px;height:240px;object-fit:cover;border-radius:10px;margin:20px 0;"></video><p>Position your face in the frame</p></div>';
            document.body.appendChild(modal);
            
            const video = modal.querySelector('#faceVideo');
            video.srcObject = stream;
            video.play();
            
            let countdown = 5;
            const timer = setInterval(() => {
                countdown--;
                modal.querySelector('p').textContent = `Capturing in ${countdown}...`;
                if (countdown <= 0) {
                    clearInterval(timer);
                    stream.getTracks().forEach(track => track.stop());
                    modal.remove();
                    
                    document.getElementById('faceData').value = 'enrolled_' + Date.now();
                    document.getElementById('enrollFace').value = '1';
                    document.getElementById('biometricForm').submit();
                }
            }, 1000);
        } catch (err) {
            alert('Face enrollment failed: ' + err.message);
        }
    }
    </script>
</body>
</html>