<?php
session_start();
include 'config.php';

$error = '';
$success = '';
$temp_data = null;
$departments = $conn->query("SELECT * FROM departments");

if (isset($_SESSION['pending_registration'])) {
    $temp_data = $_SESSION['pending_registration'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_registration'])) {
    if (!isset($_SESSION['pending_registration']) || empty($_SESSION['pending_registration'])) {
        $error = 'Registration session expired. Please start again.';
    } else {
        $matric = strtoupper(trim($_SESSION['pending_registration']['matric_number']));
        $full_name = trim($_SESSION['pending_registration']['full_name']);
        $department_id = intval($_SESSION['pending_registration']['department_id']);
        $email = trim($_SESSION['pending_registration']['email']);
        $phone = trim($_SESSION['pending_registration']['phone_number']);
        $level = trim($_SESSION['pending_registration']['level']);
        $study_mode = $_SESSION['pending_registration']['study_mode'];
        $password = $_SESSION['pending_registration']['password'];
        $fingerprint = $_POST['fingerprint_data'] ?? '';
        $face_data = $_POST['face_data'] ?? '';
        
        if (empty($fingerprint) && empty($face_data)) {
            $error = 'Please enroll at least one biometric (fingerprint or face).';
        } elseif ($department_id <= 0) {
            $error = 'Invalid department. Please start again.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = $conn->prepare("INSERT INTO students (matric_number, full_name, department_id, email, phone_number, level, study_mode, password, fingerprint_template, face_encoding) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $sql->bind_param("ssssssssss", $matric, $full_name, $department_id, $email, $phone, $level, $study_mode, $hashed_password, $fingerprint, $face_data);

            if ($sql->execute()) {
                unset($_SESSION['pending_registration']);
                $success = 'Account created successfully!';
                echo "<script>setTimeout(() => window.location='login.php', 2500);</script>";
            } else {
                $error = 'Database error: ' . $conn->error;
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['step1_submit'])) {
    $matric = strtoupper(trim($_POST['matric_number']));
    $full_name = trim($_POST['full_name']);
    $department_id = intval($_POST['department_id']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone_number']);
    $level = trim($_POST['level']);
    $study_mode = $_POST['study_mode'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($department_id) || $department_id <= 0) {
        $error = 'Please select a valid department.';
    }
    elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } 
    else if ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } 
    else {
        $check = $conn->prepare("SELECT * FROM students WHERE UPPER(matric_number) = ? OR email = ?");
        $check->bind_param("ss", $matric, $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = 'Account already exists.';
        } else {
            $_SESSION['pending_registration'] = [
                'matric_number' => $matric,
                'full_name' => $full_name,
                'department_id' => $department_id,
                'email' => $email,
                'phone_number' => $phone,
                'level' => $level,
                'study_mode' => $study_mode,
                'password' => $password
            ];
            $temp_data = $_SESSION['pending_registration'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Student Portal</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
:root {
    --primary: #1e3a5f;
    --secondary: #2563eb;
    --accent: #6366f1;
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
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.register-container {
    display: flex;
    max-width: 1000px;
    width: 100%;
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(30, 58, 95, 0.15);
}

.register-left {
    flex: 1;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    padding: 3rem;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.register-left h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.register-left p {
    opacity: 0.9;
    font-size: 1rem;
    line-height: 1.6;
}

.register-left .features {
    margin-top: 2rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: rgba(255,255,255,0.1);
    border-radius: 12px;
}

.feature-item i {
    font-size: 1.25rem;
}

.register-right {
    flex: 1.2;
    padding: 3rem;
}

.register-right h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.register-right .subtitle {
    color: #64748b;
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    background: white;
}

.form-control:focus, .form-select:focus {
    border-color: var(--secondary);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    outline: none;
}

.form-control::placeholder {
    color: #94a3b8;
}

.input-icon {
    position: relative;
}

.input-icon i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.input-icon .form-control {
    padding-left: 2.75rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.btn-register {
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
    margin-top: 1rem;
}

.btn-register:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

.alert {
    padding: 1rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

.login-link {
    text-align: center;
    margin-top: 1.5rem;
    color: #64748b;
}

.login-link a {
    color: var(--secondary);
    font-weight: 600;
    text-decoration: none;
}

.login-link a:hover {
    text-decoration: underline;
}

.biometric-btn {
    padding: 1.5rem;
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
}

.biometric-btn:hover {
    border-color: var(--secondary);
    background: #eff6ff;
}

.biometric-btn.enrolled {
    border-style: solid;
    border-color: #22c55e;
    background: #f0fdf4;
    color: #166534;
}

@media (max-width: 768px) {
    .register-container {
        flex-direction: column;
    }
    
    .register-left {
        padding: 2rem;
        text-align: center;
    }
    
    .register-left .features {
        display: none;
    }
    
    .register-right {
        padding: 2rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
</head>
<body>

<div class="register-container">
    <div class="register-left">
        <h2>Student Portal</h2>
        <p>Create your account to access your academic dashboard, register courses, and view your academic records.</p>
        
        <div class="features">
            <div class="feature-item">
                <i class="fas fa-book-open"></i>
                <span>Register Courses Online</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-user-graduate"></i>
                <span>Track Your Progress</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Upload Profile Photo</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-calendar-alt"></i>
                <span>View Academic Calendar</span>
            </div>
        </div>
    </div>
    
    <div class="register-right">
        <h3>Create Account</h3>
        <p class="subtitle">Fill in your details to get started</p>

        <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if(!$temp_data): ?>
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" required>
                </div>
            </div>

            <div class="form-group">
                <label>Matric Number</label>
                <div class="input-icon">
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="matric_number" class="form-control" placeholder="e.g. FT23CMP0001" required>
                </div>
            </div>

            <div class="form-group">
                <label>Department</label>
                <div class="input-icon">
                    <i class="fas fa-building"></i>
                    <select name="department_id" class="form-select" required>
                        <option value="">Select Department</option>
                        <?php while ($dept = $departments->fetch_assoc()): ?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control" placeholder="your.email@example.com" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Phone Number</label>
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" name="phone_number" class="form-control" placeholder="08012345678" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Level</label>
                    <select name="level" class="form-select" required>
                        <option value="">Select</option>
                        <option value="100">100 Level</option>
                        <option value="200">200 Level</option>
                        <option value="300">300 Level</option>
                        <option value="400">400 Level</option>
                        <option value="500">500 Level</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Study Mode</label>
                <select name="study_mode" class="form-select" required>
                    <option value="Full-Time">Full-Time</option>
                    <option value="Part-Time">Part-Time</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Min 8 characters" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                    </div>
                </div>
            </div>

            <button type="submit" name="step1_submit" class="btn-register">
                <i class="fas fa-arrow-right me-2"></i>Next: Biometric Setup
            </button>

            <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
        </form>
        <?php else: ?>
        <form method="POST" id="biometricForm">
            <div class="text-center mb-4">
                <h4>Step 2: Biometric Enrollment</h4>
                <p class="text-muted">Enroll your fingerprint or face to secure your account</p>
            </div>

            <div class="form-group">
                <label>Matric Number (for verification)</label>
                <input type="text" class="form-control" value="<?php echo $temp_data['matric_number']; ?>" readonly>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Fingerprint</label>
                <button type="button" class="biometric-btn w-100" id="fingerprintBtn" onclick="enrollFingerprint()">
                    <i class="fas fa-fingerprint fa-2x d-block mb-2"></i>
                    <span id="fingerprintText">Tap to Enroll Fingerprint</span>
                </button>
                <input type="hidden" name="fingerprint_data" id="fingerprintData">
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Face Recognition</label>
                <button type="button" class="biometric-btn w-100" id="faceBtn" onclick="enrollFace()">
                    <i class="fas fa-face-smile fa-2x d-block mb-2"></i>
                    <span id="faceText">Tap to Enroll Face</span>
                </button>
                <input type="hidden" name="face_data" id="faceData">
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>You must enroll at least one biometric to continue.
            </div>

            <button type="submit" name="complete_registration" class="btn-register">
                <i class="fas fa-check me-2"></i>Complete Registration
            </button>

            <button type="button" class="btn btn-link w-100 mt-2" onclick="history.back()">
                <i class="fas fa-arrow-left me-2"></i>Go Back
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
let faceModelsLoaded = false;

async function loadFaceModels() {
    if (faceModelsLoaded) return true;
    
    if (typeof faceapi === 'undefined') {
        alert('face-api.js is not loaded. Please check your internet connection and refresh the page.');
        return false;
    }
    
    try {
        const modelPath = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/models';
        await faceapi.nets.tinyFaceDetector.loadFromUri(modelPath);
        await faceapi.nets.faceLand68Net.loadFromUri(modelPath);
        await faceapi.nets.faceRecognitionNet.loadFromUri(modelPath);
        faceModelsLoaded = true;
        console.log('Face models loaded from CDN');
        return true;
    } catch (err) {
        console.error('Failed to load face models:', err);
        alert('Failed to load face recognition models. Please check your internet connection.');
        return false;
    }
}

async function enrollFingerprint() {
    try {
        if (!window.PublicKeyCredential) {
            alert('Fingerprint not supported in this browser');
            return;
        }
        
        alert('Place your finger on the scanner...');
        
        const credential = await navigator.credentials.create({
            publicKey: {
                challenge: new Uint8Array(32),
                rp: {name: 'Course Registration Portal'},
                user: {id: new Uint8Array([1]), name: 'fingerprint'},
                pubKeyAlg: [-7],
            }
        });
        
        document.getElementById('fingerprintData').value = 'fp_' + Date.now() + '_<?php echo $temp_data['matric_number']; ?>';
        document.getElementById('fingerprintBtn').classList.add('enrolled');
        document.getElementById('fingerprintText').textContent = 'Fingerprint Enrolled!';
    } catch (err) {
        alert('Fingerprint enrollment failed: ' + err.message);
    }
}

async function enrollFace() {
    try {
        const modelsLoaded = await loadFaceModels();
        if (!modelsLoaded) return;
        
        const stream = await navigator.mediaDevices.getUserMedia({video: {facingMode: 'user'}});
        
        const modal = document.createElement('div');
        modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);display:flex;justify-content:center;align-items:center;z-index:9999;';
        modal.innerHTML = '<div style="background:white;padding:30px;border-radius:15px;text-align:center;"><h3>Face Enrollment</h3><video id="faceVideo" style="width:320px;height:240px;object-fit:cover;border-radius:10px;margin:20px 0;"></video><p id="faceStatus">Position your face in the frame</p><div id="faceMatch" style="margin-top:10px;color:#4f46e5;"></div></div>';
        document.body.appendChild(modal);
        
        const video = modal.querySelector('#faceVideo');
        const statusEl = modal.querySelector('#faceStatus');
        const matchEl = modal.querySelector('#faceMatch');
        video.srcObject = stream;
        video.play();
        
        let countdown = 5;
        let faceDescriptor = null;
        
        const timer = setInterval(async () => {
            statusEl.textContent = 'Detecting face... (' + countdown + ')';
            
            try {
                const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
                
                if (detection) {
                    faceDescriptor = Array.from(detection.descriptor);
                    statusEl.textContent = 'Face detected! Capturing in ' + countdown + '...';
                    matchEl.textContent = 'Quality: Good';
                    matchEl.style.color = 'green';
                } else {
                    statusEl.textContent = 'No face detected. Please look at camera. (' + countdown + ')';
                    matchEl.textContent = 'Quality: Poor - Please adjust position';
                    matchEl.style.color = 'orange';
                }
            } catch (e) {
                console.error('Detection error:', e);
                statusEl.textContent = 'Detection error, retrying...';
            }
            
            countdown--;
            
            if (countdown <= 0) {
                clearInterval(timer);
                stream.getTracks().forEach(track => track.stop());
                modal.remove();
                
                if (faceDescriptor) {
                    document.getElementById('faceData').value = JSON.stringify({
                        matric: '<?php echo $temp_data['matric_number']; ?>',
                        descriptor: faceDescriptor,
                        timestamp: Date.now()
                    });
                    document.getElementById('faceBtn').classList.add('enrolled');
                    document.getElementById('faceText').textContent = 'Face Enrolled!';
                } else {
                    alert('Failed to capture face. Please try again.');
                }
            }
        }, 1000);
    } catch (err) {
        alert('Face enrollment failed: ' + err.message);
    }
}
</script>

</body>
</html>
