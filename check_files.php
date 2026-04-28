<?php
// Simple PHP test to verify files are accessible via HTTP
header('Content-Type: text/html; charset=UTF-8');

$baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/course-reg-portal/';
?>

<!DOCTYPE html>
<html>
<head>
    <title>File Accessibility Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        button { background: #4f46e5; color: white; border: none; padding: 12px 24px; font-size: 16px; border-radius: 8px; cursor: pointer; margin: 5px; }
        #results { margin-top: 20px; }
        .file-item { padding: 10px; margin: 5px 0; background: #f9fafb; border-radius: 5px; border-left: 4px solid #ccc; }
        .file-item.success { border-left-color: green; }
        .file-item.error { border-left-color: red; }
    </style>
</head>
<body>
<div class="container">
    <h2>File Accessibility Test</h2>
    <p>This checks if files are accessible via HTTP (browser).</p>
    <button onclick="testFiles()">Test File Access</button>
    <div id="results"></div>
</div>

<script>
const baseUrl = '<?php echo $baseUrl; ?>';
const filesToCheck = [
    'face-api.min.js',
    'face-models/tiny_face_detector_model-weights_manifest.json',
    'face-models/tiny_face_detector_model-shard1',
    'face-models/face_landmark_68_model-weights_manifest.json',
    'face-models/face_landmark_68_model-shard1',
    'face-models/face_recognition_model-weights_manifest.json',
    'face-models/face_recognition_model-shard1',
    'face-models/face_recognition_model-shard2'
];

async function testFiles() {
    const results = document.getElementById('results');
    results.innerHTML = '<p>Testing file access...</p>';
    
    let html = '';
    for (const file of filesToCheck) {
        const url = baseUrl + file;
        try {
            const response = await fetch(url, { method: 'HEAD' });
            if (response.ok) {
                const size = response.headers.get('content-length') || 'unknown';
                html += `<div class="file-item success">✓ ${file} (${formatSize(size)})</div>`;
            } else {
                html += `<div class="file-item error">✗ ${file} (HTTP ${response.status})</div>`;
            }
        } catch (err) {
            html += `<div class="file-item error">✗ ${file} (Error: ${err.message})</div>`;
        }
    }
    
    results.innerHTML = html;
}

function formatSize(bytes) {
    bytes = parseInt(bytes);
    if (isNaN(bytes)) return 'unknown size';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024*1024) return (bytes/1024).toFixed(1) + ' KB';
    return (bytes/(1024*1024)).toFixed(1) + ' MB';
}

// Auto-test on load
window.onload = () => { setTimeout(testFiles, 500); };
</script>
</body>
</html>
