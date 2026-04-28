<?php
session_start();
include 'config.php';

$error = '';
$mode = $_GET['mode'] ?? '';
$matric = strtoupper($_GET['matric'] ?? '');

if (!$matric) {
    header("Location: login.php");
    exit();
}

$sql = $conn->prepare("SELECT * FROM students WHERE UPPER(matric_number) = ?");
$sql->bind_param("s", $matric);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows === 0) {
    header("Location: login.php?error=user_not_found");
    exit();
}

$student = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submitted_fp = $_POST['fingerprint_data'] ?? '';
    $submitted_face = $_POST['face_data'] ?? '';
    $face_descriptor = $_POST['face_descriptor'] ?? '';
    $stored_fp = $student['fingerprint_template'] ?? '';
    $stored_face = $student['face_encoding'] ?? '';
    
    $fp_match = false;
    $face_match = false;
    
    if ($mode === 'fingerprint' && !empty($stored_fp) && !empty($submitted_fp)) {
        $fp_match = ($submitted_fp === $stored_fp);
    }
    
    if ($mode === 'face' && !empty($stored_face) && !empty($submitted_face)) {
        $face_match = true;
    }
    
    if (($mode === 'fingerprint' && $fp_match) || ($mode === 'face' && $face_match)) {
        $_SESSION['username'] = $matric;
        header("Location: student_dashboard.php");
        exit();
    } else {
        $error = 'Biometric verification failed. Face not recognized. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Biometric - Student Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="face-api.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #1e1b4b, #312e81);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .verify-card {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        .icon-box {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .icon-box i {
            font-size: 3rem;
            color: white;
        }
        .btn-verify {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 1rem;
        }
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79,70,229,0.3);
        }
        .alert-danger {
            border-radius: 12px;
        }
        .text-decoration-none {
            text-decoration: none !important;
            color: #4f46e5 !important;
        }
        .text-decoration-none:hover {
            text-decoration: underline !important;
        }
    </style>
</head>
<body>

<div class="verify-card">
    <?php if($mode === 'fingerprint'): ?>
        <div class="icon-box">
            <i class="fas fa-fingerprint"></i>
        </div>
        <h3>Fingerprint Verification</h3>
        <p class="text-muted mb-4">Verify your fingerprint to login as <strong><?php echo $matric; ?></strong></p>
    <?php else: ?>
        <div class="icon-box">
            <i class="fas fa-face-smile"></i>
        </div>
        <h3>Face Verification</h3>
        <p class="text-muted mb-4">Verify your face to login as <strong><?php echo $matric; ?></strong></p>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="verifyForm">
        <input type="hidden" name="fingerprint_data" id="fingerprintData">
        <input type="hidden" name="face_data" id="faceData">
        <input type="hidden" name="face_descriptor" id="faceDescriptor">
        
        <?php if($mode === 'fingerprint'): ?>
            <button type="button" class="btn-verify" onclick="verifyFingerprint()">
                <i class="fas fa-fingerprint me-2"></i>Scan Fingerprint
            </button>
        <?php else: ?>
            <button type="button" class="btn-verify" onclick="verifyFace()">
                <i class="fas fa-camera me-2"></i>Scan Face
            </button>
        <?php endif; ?>
        
        <a href="login.php" class="d-block mt-3 text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i>Back to Login
        </a>
    </form>
</div>

<script>
const storedFaceData = <?php echo json_encode($student['face_encoding'] ?? ''); ?>;

// Wait for face-api.js to load
let faceapiReady = false;
(function checkFaceAPI() {
    if (typeof faceapi !== 'undefined') {
        faceapiReady = true;
        console.log('face-api.js loaded successfully');
    } else {
        setTimeout(checkFaceAPI, 100);
    }
})();

async function loadFaceModels() {
    if (!faceapiReady) {
        await new Promise(resolve => setTimeout(resolve, 2000));
        if (!faceapiReady) {
            alert('face-api.js is still loading. Please wait and try again.');
            return false;
        }
    }
    
    try {
        const modelPath = '/course-reg-portal/face-models';
        await faceapi.nets.tinyFaceDetector.loadFromUri(modelPath);
        await faceapi.nets.faceLand68Net.loadFromUri(modelPath);
        await faceapi.nets.faceRecognitionNet.loadFromUri(modelPath);
        console.log('Face models loaded from local path');
        return true;
    } catch (err) {
        console.error('Failed to load face models:', err);
        alert('Failed to load face recognition models. Please ensure all model files are in the face-models folder.');
        return false;
    }
}

async function verifyFingerprint() {
    try {
        if (!window.PublicKeyCredential) {
            alert('Fingerprint not supported in this browser');
            return;
        }
        
        const credential = await navigator.credentials.create({
            publicKey: {
                challenge: new Uint8Array(32),
                rp: {name: 'Course Registration Portal'},
                user: {id: new Uint8Array([<?php echo $student['id']; ?>]), name: '<?php echo $matric; ?>'},
                pubKeyAlg: [-7],
            }
        });
        
        document.getElementById('fingerprintData').value = '<?php echo $student['fingerprint_template']; ?>';
        document.getElementById('verifyForm').submit();
    } catch (err) {
        alert('Fingerprint verification failed: ' + err.message);
    }
}

async function verifyFace() {
    try {
        const modelsLoaded = await loadFaceModels();
        if (!modelsLoaded) return;
        
        const stream = await navigator.mediaDevices.getUserMedia({video: {facingMode: 'user'}});
        
        const modal = document.createElement('div');
        modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);display:flex;justify-content:center;align-items:center;z-index:9999;';
        modal.innerHTML = '<div style="background:white;padding:30px;border-radius:15px;text-align:center;"><h3>Verifying Face...</h3><video id="faceVideo" style="width:320px;height:240px;object-fit:cover;border-radius:10px;margin:20px 0;"></video><p id="faceStatus">Looking at the camera</p><div id="matchResult" style="margin-top:10px;font-weight:bold;"></div></div>';
        document.body.appendChild(modal);
        
        const video = modal.querySelector('#faceVideo');
        const statusEl = modal.querySelector('#faceStatus');
        const resultEl = modal.querySelector('#matchResult');
        video.srcObject = stream;
        video.play();
        
        let countdown = 5;
        let lastDetection = null;
        
        const timer = setInterval(async () => {
            statusEl.textContent = 'Detecting face... ' + countdown;
            
            try {
                const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
                
                if (detection) {
                    lastDetection = detection;
                    statusEl.textContent = 'Face detected! Verifying in ' + countdown + '...';
                    
                    if (storedFaceData) {
                        try {
                            const storedData = JSON.parse(storedFaceData);
                            const storedDescriptor = new Float32Array(storedData.descriptor);
                            const currentDescriptor = detection.descriptor;
                            
                            const distance = faceapi.euclideanDistance(storedDescriptor, currentDescriptor);
                            resultEl.textContent = 'Match score: ' + (1 - distance).toFixed(3);
                            resultEl.style.color = distance < 0.6 ? 'green' : 'orange';
                        } catch (e) {
                            console.error('Error comparing faces:', e);
                        }
                    }
                } else {
                    statusEl.textContent = 'No face detected. Please look at camera. ' + countdown + '...';
                }
                
                countdown--;
                
                if (countdown <= 0) {
                    clearInterval(timer);
                    stream.getTracks().forEach(track => track.stop());
                    
                    if (!lastDetection) {
                        modal.remove();
                        alert('No face detected. Please try again.');
                        return;
                    }
                    
                    if (storedFaceData) {
                        try {
                            const storedData = JSON.parse(storedFaceData);
                            const storedDescriptor = new Float32Array(storedData.descriptor);
                            const currentDescriptor = lastDetection.descriptor;
                            const distance = faceapi.euclideanDistance(storedDescriptor, currentDescriptor);
                            
                            if (distance < 0.6) {
                                document.getElementById('faceData').value = storedFaceData;
                                document.getElementById('faceDescriptor').value = JSON.stringify(Array.from(currentDescriptor));
                                modal.remove();
                                document.getElementById('verifyForm').submit();
                                return;
                            }
                        } catch (e) {
                            console.error('Error:', e);
                        }
                    }
                    
                    modal.remove();
                    alert('Face verification failed. Please try again.');
                }
            } catch (err) {
                console.error('Detection error:', err);
                countdown--;
                if (countdown <= 0) {
                    clearInterval(timer);
                    stream.getTracks().forEach(track => track.stop());
                    modal.remove();
                    alert('Face detection error. Please try again.');
                }
            }
        }, 1000);
    } catch (err) {
        alert('Face verification failed: ' + err.message);
    }
}
</script>

</body>
</html>
