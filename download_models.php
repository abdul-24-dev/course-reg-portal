<?php
header('Content-Type: text/html; charset=UTF-8');

$modelDir = __DIR__ . '/face-models/';
$baseUrl = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/';

$models = [
    'tiny_face_detector_model-weights_manifest.json',
    'tiny_face_detector_model-shard1',
    'face_landmark_68_model-weights_manifest.json',
    'face_landmark_68_model-shard1',
    'face_recognition_model-weights_manifest.json',
    'face_recognition_model-shard1',
    'face_recognition_model-shard2'
];

// Handle AJAX download request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download'])) {
    $model = basename($_POST['download']);
    $url = $baseUrl . $model;
    
    $context = stream_context_create([
        'http' => ['timeout' => 60, 'ignore_errors' => true]
    ]);
    
    $data = @file_get_contents($url, false, $context);
    
    if ($data && strlen($data) > 100) {
        if (!is_dir($modelDir)) {
            mkdir($modelDir, 0777, true);
        }
        file_put_contents($modelDir . $model, $data);
        echo 'OK:' . strlen($data);
    } else {
        echo 'ERROR';
    }
    exit;
}

// Handle verify request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    echo '<h3>File Verification:</h3>';
    foreach ($models as $model) {
        $path = $modelDir . $model;
        if (file_exists($path)) {
            $size = filesize($path);
            echo "<div style='color:green'>✓ $model (" . round($size/1024,1) . " KB)</div>";
        } else {
            echo "<div style='color:red'>✗ $model (missing)</div>";
        }
    }
    exit;
}

// Create directory if needed
if (!is_dir($modelDir)) {
    mkdir($modelDir, 0777, true);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Download Face-API Models</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; }
        button { background: #4f46e5; color: white; border: none; padding: 15px 30px; font-size: 16px; border-radius: 8px; cursor: pointer; margin: 5px; }
        button:hover { background: #4338ca; }
        button:disabled { background: #ccc; cursor: not-allowed; }
        #status { margin-top: 20px; max-height: 400px; overflow-y: auto; }
        .success { color: green; }
        .error { color: red; }
        .model-item { padding: 10px; margin: 5px 0; background: #f9fafb; border-radius: 5px; border-left: 4px solid #ccc; }
        .model-item.success { border-left-color: green; }
        .model-item.error { border-left-color: red; }
        .progress { background: #e5e7eb; height: 20px; border-radius: 10px; overflow: hidden; margin: 10px 0; }
        .progress-bar { background: #4f46e5; height: 100%; width: 0%; transition: width 0.3s; }
    </style>
</head>
<body>
<div class="container">
    <h2>Download Face-API.js Models</h2>
    <p>This will download the required face recognition models to: <code><?php echo $modelDir; ?></code></p>
    <div class="progress"><div class="progress-bar" id="progressBar"></div></div>
    <button onclick="downloadAll()" id="downloadBtn">Download All Models</button>
    <button onclick="verifyFiles()" id="verifyBtn">Verify Downloads</button>
    <div id="status"></div>
</div>

<script>
const models = <?php echo json_encode($models); ?>;

async function downloadAll() {
    const statusDiv = document.getElementById('status');
    const progressBar = document.getElementById('progressBar');
    const btn = document.getElementById('downloadBtn');
    btn.disabled = true;
    statusDiv.innerHTML = '<p>Starting downloads...</p>';
    
    let completed = 0;
    
    for (const model of models) {
        statusDiv.innerHTML += `<div class="model-item">Downloading ${model}...</div>`;
        statusDiv.scrollTop = statusDiv.scrollHeight;
        
        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'download=' + encodeURIComponent(model)
            });
            
            const result = await response.text();
            
            if (result.startsWith('OK')) {
                completed++;
                progressBar.style.width = (completed / models.length * 100) + '%';
                statusDiv.innerHTML += `<div class="model-item success">✓ ${model} downloaded! ${result.replace('OK:', '(') + ' bytes)'}</div>`;
            } else {
                statusDiv.innerHTML += `<div class="model-item error">✗ ${model} failed to download</div>`;
            }
            statusDiv.scrollTop = statusDiv.scrollHeight;
            
        } catch (err) {
            statusDiv.innerHTML += `<div class="model-item error">✗ ${model} error: ${err.message}</div>`;
        }
    }
    
    btn.disabled = false;
    if (completed === models.length) {
        statusDiv.innerHTML += '<h3 class="success">All models downloaded successfully!</h3><p>The face recognition should now work. <a href="register.php">Go to registration</a> to test it.</p>';
    } else {
        statusDiv.innerHTML += '<h3 class="error">Some downloads failed. Please try again.</h3>';
    }
}

async function verifyFiles() {
    const statusDiv = document.getElementById('status');
    statusDiv.innerHTML = '<p>Verifying files...</p>';
    
    const response = await fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'verify=1'
    });
    
    const result = await response.text();
    statusDiv.innerHTML = result;
}
</script>
</body>
</html>




