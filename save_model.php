<?php
header('Content-Type: text/plain');

$modelDir = __DIR__ . '/face-models/';
if (!is_dir($modelDir)) {
    mkdir($modelDir, 0777, true);
}

// Check if files exist
if (isset($_GET['check'])) {
    $files = [
        'tiny_face_detector_model-weights_manifest.json',
        'tiny_face_detector_model-shard1',
        'face_landmark_68_model-weights_manifest.json',
        'face_landmark_68_model-shard1',
        'face_recognition_model-weights_manifest.json',
        'face_recognition_model-shard1',
        'face_recognition_model-shard2'
    ];
    
    echo "Checking files in: $modelDir\n\n";
    foreach ($files as $file) {
        $path = $modelDir . $file;
        if (file_exists($path)) {
            $size = round(filesize($path) / 1024, 1);
            echo "✓ $file ($size KB)\n";
        } else {
            echo "✗ $file (missing)\n";
        }
    }
    exit;
}

// Save uploaded file
if (isset($_POST['save']) && isset($_FILES['file'])) {
    $filename = basename($_FILES['file']['name']);
    $dest = $modelDir . $filename;
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
        echo 'OK';
    } else {
        echo 'ERROR';
    }
    exit;
}

echo 'Invalid request';
?>
