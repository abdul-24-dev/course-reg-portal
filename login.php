<?php
session_start();
include 'config.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matric = strtoupper(trim($_POST['matric_number']));
    $password = $_POST['password'];

    $sql = $conn->prepare("SELECT * FROM students WHERE UPPER(matric_number) = ?");
    $sql->bind_param("s", $matric);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $matric;
            header("Location: student_dashboard.php");
            exit();
        } else {
            $error = 'Invalid password';
        }
    } else {
        $error = 'User not found';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Login</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
:root {
    --primary: #4f46e5;
    --secondary: #7c3aed;
}

/* BODY */
body {
    margin: 0;
    height: 100vh;
    display: flex;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #1e1b4b, #312e81);
}

/* LEFT PANEL */
.left-panel {
    flex: 1;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 40px;
}

.left-panel h1 {
    font-weight: 800;
    font-size: 2.5rem;
}

.left-panel p {
    opacity: 0.85;
}

/* RIGHT PANEL */
.right-panel {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* CARD */
.login-card {
    background: rgba(255,255,255,0.96);
    padding: 40px;
    border-radius: 20px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.25);
}

/* HEADER */
.header-text {
    font-weight: 700;
    font-size: 1.8rem;
    margin-bottom: 5px;
}

/* INPUT */
.form-control {
    height: 50px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}

.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(79,70,229,0.2);
}

/* BUTTON */
.btn-login {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border: none;
    height: 50px;
    border-radius: 10px;
    font-weight: 600;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(79,70,229,0.3);
}

/* ERROR */
.alert-danger {
    border-radius: 10px;
}

/* LINKS */
.link-hover {
    font-size: 14px;
    color: var(--primary);
    text-decoration: none;
}

.link-hover:hover {
    text-decoration: underline;
}

/* MOBILE */
@media(max-width:768px){
    .left-panel {
        display: none;
    }
}
</style>
</head>

<body>

<!-- LEFT SIDE -->
<div class="left-panel">
    <div>
        <h1>Student Portal</h1>
        <p>Access your dashboard, courses, and academic records in one place.</p>
    </div>
</div>

<!-- RIGHT SIDE -->
<div class="right-panel">
    <div class="login-card">

        <div class="mb-4 text-center">
            <h2 class="header-text">Welcome Back</h2>
            <small class="text-muted">Login to continue</small>
        </div>

        <?php if($error): ?>
        <div class="alert alert-danger mb-3">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Matric Number</label>
                <input type="text" name="matric_number" class="form-control" placeholder="Enter matric number" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>

            <button class="btn btn-login w-100 mb-3">
                <i class="fas fa-sign-in-alt me-2"></i> Login
            </button>

            <div class="text-center mb-3">
                <span class="text-muted">Or login with</span>
            </div>

            <div class="d-flex gap-2 mb-3">
                <button type="button" class="btn btn-outline-primary flex-fill" onclick="loginWithFingerprint()">
                    <i class="fas fa-fingerprint me-2"></i> Fingerprint
                </button>
                <button type="button" class="btn btn-outline-success flex-fill" onclick="loginWithFace()">
                    <i class="fas fa-face-smile me-2"></i> Face ID
                </button>
            </div>

            <div class="d-flex justify-content-between">
                <a href="register.php" class="link-hover">Create Account</a>
                <a href="forgot_password.php" class="link-hover">Forgot Password</a>
            </div>

        </form>
    </div>
</div>
</body>

<script>
async function loginWithFingerprint() {
    try {
        if (!window.PublicKeyCredential) {
            alert('Fingerprint authentication not supported in this browser');
            return;
        }
        
        const matricInput = prompt('Enter your matric number:');
        if (!matricInput) return;

        const response = await fetch('get_student_for_biometric.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({matric_number: matricInput})
        });
        const data = await response.json();
        
        if (!data.student) {
            alert('Student not found or biometric not enrolled');
            return;
        }

        const credential = await navigator.credentials.create({
            publicKey: {
                challenge: new Uint8Array(32),
                rp: {name: 'Course Registration Portal'},
                user: {id: new TextEncoder().encode(String(data.student.id)), name: matricInput},
                pubKeyAlg: [-7],
            }
        });

        if (credential) {
            window.location.href = 'verify_biometric.php?mode=fingerprint&matric=' + matricInput;
        }
    } catch (err) {
        alert('Fingerprint authentication failed: ' + err.message);
    }
}

async function loginWithFace() {
    try {
        const matricInput = prompt('Enter your matric number:');
        if (!matricInput) return;

        const response = await fetch('get_student_for_biometric.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({matric_number: matricInput})
        });
        const data = await response.json();
        
        if (!data.student) {
            alert('Student not found or biometric not enrolled');
            return;
        }

        const stream = await navigator.mediaDevices.getUserMedia({video: {facingMode: 'user'}});
        const video = document.createElement('video');
        video.srcObject = stream;
        video.play();
        
        const modal = document.createElement('div');
        modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);display:flex;justify-content:center;align-items:center;z-index:9999;';
        modal.innerHTML = '<div style="background:white;padding:30px;border-radius:10px;text-align:center;"><h3>Verifying Face...</h3><video id="faceVideo" style="width:300px;height:300px;object-fit:cover;border-radius:10px;"></video></div>';
        document.body.appendChild(modal);
        
        const faceVideo = modal.querySelector('#faceVideo');
        faceVideo.srcObject = stream;
        faceVideo.play();
        
        setTimeout(() => {
            stream.getTracks().forEach(track => track.stop());
            modal.remove();
            window.location.href = 'verify_biometric.php?mode=face&matric=' + matricInput;
        }, 3000);
    } catch (err) {
        alert('Face recognition not available: ' + err.message);
    }
}
</script>

</html>