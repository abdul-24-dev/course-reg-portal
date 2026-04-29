<?php
// Database Import Script with Error Handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
set_time_limit(300);

// Get database credentials
$servername = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASS') ?: "";
$dbname = getenv('DB_NAME') ?: "course_registration_db";

$sql_file = __DIR__ . '/course_registration_db (1).sql';
$connection_error = null;
$file_exists = file_exists($sql_file);

// Try to connect if it's a POST request
$conn = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    $conn = @mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        $connection_error = mysqli_connect_error();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Import | Course Registration Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
            padding: 2rem;
        }
        .import-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .header h1 {
            color: #1e3a5f;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        .header p {
            color: #64748b;
        }
        .status-box {
            background: #f3f4f6;
            border-left: 4px solid #3b82f6;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .status-box.error {
            border-left-color: #dc2626;
            background: #fee2e2;
        }
        .status-box.success {
            border-left-color: #22c55e;
            background: #f0fdf4;
        }
        .status-box h5 {
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
            font-weight: 600;
        }
        .status-box p {
            margin: 0;
            font-size: 0.85rem;
        }
        .btn-import {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-import:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }
        .btn-import:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
        }
        .log-box {
            background: #1e293b;
            color: #10b981;
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            max-height: 250px;
            overflow-y: auto;
            margin-top: 1rem;
            display: none;
            line-height: 1.5;
        }
        .log-box.show {
            display: block;
        }
        .log-entry {
            padding: 0.25rem 0;
        }
        .log-entry.error {
            color: #ef4444;
        }
        .log-entry.success {
            color: #10b981;
        }
        .log-entry.info {
            color: #60a5fa;
        }
    </style>
</head>
<body>

<div class="import-card">
    <div class="header">
        <h1>🗄️ Database Import</h1>
        <p>Import course registration database</p>
    </div>

    <div class="status-box <?php echo !$file_exists ? 'error' : 'success'; ?>">
        <h5><?php echo $file_exists ? '✓ SQL File Found' : '✗ SQL File Missing'; ?></h5>
        <p><?php echo $file_exists ? basename($sql_file) . ' (' . round(filesize($sql_file) / 1024, 1) . ' KB)' : 'course_registration_db (1).sql not found'; ?></p>
    </div>

    <div class="status-box <?php echo empty($servername) ? 'error' : 'success'; ?>">
        <h5><?php echo !empty($servername) ? '✓ Database Host Set' : '⚠ Database Host'; ?></h5>
        <p><?php echo htmlspecialchars($servername); ?></p>
    </div>

    <div class="status-box <?php echo empty($username) ? 'error' : 'success'; ?>">
        <h5><?php echo !empty($username) ? '✓ Database User Set' : '⚠ Database User'; ?></h5>
        <p><?php echo htmlspecialchars($username); ?></p>
    </div>

    <?php if ($connection_error): ?>
    <div class="status-box error">
        <h5>✗ Connection Failed</h5>
        <p><?php echo htmlspecialchars($connection_error); ?></p>
        <p style="margin-top: 0.5rem; font-size: 0.8rem;">Make sure MySQL is linked in Railway and environment variables are set.</p>
    </div>
    <?php endif; ?>

    <form method="POST">
        <button type="submit" name="import" class="btn-import" <?php echo !$file_exists || $connection_error ? 'disabled' : ''; ?>>
            📥 Start Database Import
        </button>
    </form>

    <div id="logBox" class="log-box">
        <div id="logContent"></div>
    </div>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import']) && $conn && $file_exists) {
    ?>
    <script>
        const logBox = document.getElementById('logBox');
        const logContent = document.getElementById('logContent');
        logBox.classList.add('show');

        function addLog(message, type = 'info') {
            const entry = document.createElement('div');
            entry.className = 'log-entry ' + type;
            entry.textContent = message;
            logContent.appendChild(entry);
            logBox.scrollTop = logBox.scrollHeight;
        }

        addLog('[INFO] Starting database import...', 'info');
    </script>
    <?php

    $sql_content = file_get_contents($sql_file);
    
    if (!$sql_content) {
        echo '<script>addLog("[ERROR] Failed to read SQL file", "error");</script>';
        die();
    }

    echo '<script>addLog("[SUCCESS] SQL file loaded", "success");</script>';

    // Parse SQL statements more carefully
    $statements = [];
    $current = '';
    foreach (explode("\n", $sql_content) as $line) {
        $line = trim($line);
        if (empty($line) || substr($line, 0, 2) === '--') continue;
        
        $current .= $line . ' ';
        
        if (substr($line, -1) === ';') {
            $stmt = trim($current);
            if (!empty($stmt)) {
                $statements[] = $stmt;
            }
            $current = '';
        }
    }

    echo '<script>addLog("[INFO] Found ' . count($statements) . ' SQL statements", "info");</script>';

    $success = 0;
    $errors = [];

    foreach ($statements as $i => $statement) {
        if (@mysqli_query($conn, $statement)) {
            $success++;
        } else {
            $errors[] = mysqli_error($conn);
        }
        
        if ($i % 20 === 0) {
            echo '<script>addLog("[INFO] Processed ' . $i . '/' . count($statements) . ' statements", "info");</script>';
            flush();
        }
    }

    mysqli_close($conn);

    ?>
    <script>
        addLog('[SUCCESS] ✓ Import completed!', 'success');
        addLog('[SUCCESS] Executed: <?php echo $success; ?> statements', 'success');
        <?php if (count($errors) > 0): ?>
            addLog('[WARNING] Errors: <?php echo count($errors); ?>', 'error');
        <?php endif; ?>
        addLog('[INFO] Redirecting to login in 2 seconds...', 'info');
        
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 2000);
    </script>
    <?php
}
?>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Import | Course Registration Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .import-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .header h1 {
            color: #1e3a5f;
            margin-bottom: 0.5rem;
        }
        .header p {
            color: #64748b;
        }
        .info-box {
            background: #f3f4f6;
            border-left: 4px solid #3b82f6;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .info-box h5 {
            color: #1e3a5f;
            margin-bottom: 0.5rem;
        }
        .info-box p {
            margin: 0;
            font-size: 0.9rem;
            color: #475569;
        }
        .status-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .status-item:last-child {
            border-bottom: none;
        }
        .status-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-weight: bold;
            color: white;
        }
        .status-icon.success {
            background: #22c55e;
        }
        .status-icon.error {
            background: #dc2626;
        }
        .status-icon.warning {
            background: #f59e0b;
        }
        .status-text {
            flex: 1;
        }
        .btn-import {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-import:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }
        .btn-import:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
        }
        .log-box {
            background: #1e293b;
            color: #10b981;
            padding: 1.5rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 1.5rem;
            display: none;
        }
        .log-box.show {
            display: block;
        }
        .log-entry {
            padding: 0.25rem 0;
            line-height: 1.4;
        }
        .log-entry.error {
            color: #ef4444;
        }
        .log-entry.success {
            color: #10b981;
        }
        .log-entry.info {
            color: #60a5fa;
        }
    </style>
</head>
<body>

<div class="import-card">
    <div class="header">
        <h1>🗄️ Database Import</h1>
        <p>Import course registration database</p>
    </div>

    <div class="info-box">
        <h5>Pre-Import Checklist</h5>
        <div class="status-item">
            <div class="status-icon <?php echo file_exists($sql_file) ? 'success' : 'error'; ?>">
                <?php echo file_exists($sql_file) ? '✓' : '✗'; ?>
            </div>
            <div class="status-text">
                <strong>SQL File Found</strong>
                <p style="font-size: 0.8rem; margin: 0;"><?php echo file_exists($sql_file) ? basename($sql_file) : 'Not found'; ?></p>
            </div>
        </div>

        <div class="status-item">
            <div class="status-icon <?php echo !empty($servername) ? 'success' : 'warning'; ?>">
                <?php echo !empty($servername) ? '✓' : '⚠'; ?>
            </div>
            <div class="status-text">
                <strong>Database Host</strong>
                <p style="font-size: 0.8rem; margin: 0;"><?php echo $servername ?: 'Using default (localhost)'; ?></p>
            </div>
        </div>

        <div class="status-item">
            <div class="status-icon <?php echo !empty($username) ? 'success' : 'warning'; ?>">
                <?php echo !empty($username) ? '✓' : '⚠'; ?>
            </div>
            <div class="status-text">
                <strong>Database User</strong>
                <p style="font-size: 0.8rem; margin: 0;"><?php echo $username ?: 'Using default (root)'; ?></p>
            </div>
        </div>

        <div class="status-item">
            <div class="status-icon <?php echo !empty($dbname) ? 'success' : 'warning'; ?>">
                <?php echo !empty($dbname) ? '✓' : '⚠'; ?>
            </div>
            <div class="status-text">
                <strong>Database Name</strong>
                <p style="font-size: 0.8rem; margin: 0;"><?php echo $dbname; ?></p>
            </div>
        </div>
    </div>

    <form id="importForm" method="POST">
        <button type="submit" name="import" class="btn-import">
            📥 Start Database Import
        </button>
    </form>

    <div id="logBox" class="log-box">
        <div id="logContent"></div>
    </div>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    ?>
    <script>
        const logBox = document.getElementById('logBox');
        const logContent = document.getElementById('logContent');
        logBox.classList.add('show');

        function addLog(message, type = 'info') {
            const entry = document.createElement('div');
            entry.className = 'log-entry ' + type;
            entry.textContent = '> ' + message;
            logContent.appendChild(entry);
            logBox.scrollTop = logBox.scrollHeight;
        }

        addLog('Starting database import...', 'info');
    </script>
    <?php

    $conn = @mysqli_connect($servername, $username, $password, $dbname);
    
    if (!$conn) {
        echo '<script>addLog("Connection failed: ' . mysqli_connect_error() . '", "error");</script>';
        die();
    }

    echo '<script>addLog("✓ Connected to database", "success");</script>';

    // Read SQL file
    $sql_content = file_get_contents($sql_file);
    
    if (!$sql_content) {
        echo '<script>addLog("Failed to read SQL file", "error");</script>';
        die();
    }

    echo '<script>addLog("✓ SQL file loaded (' . round(filesize($sql_file) / 1024, 2) . ' KB)", "success");</script>';

    // Split SQL statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql_content)),
        fn($stmt) => !empty($stmt) && !preg_match('/^--/', $stmt)
    );

    echo '<script>addLog("Found ' . count($statements) . ' SQL statements to execute", "info");</script>';

    $success_count = 0;
    $error_count = 0;

    foreach ($statements as $index => $statement) {
        // Skip comments and empty statements
        if (empty(trim($statement)) || preg_match('/^\/\*|^--/', trim($statement))) {
            continue;
        }

        if (mysqli_query($conn, $statement)) {
            $success_count++;
            if ($success_count % 10 === 0) {
                echo '<script>addLog("Executed ' . $success_count . ' statements...", "info");</script>';
            }
        } else {
            $error_count++;
            echo '<script>addLog("Error: ' . mysqli_error($conn) . '", "error");</script>';
        }
    }

    mysqli_close($conn);

    ?>
    <script>
        addLog('✓ Import completed!', 'success');
        addLog('Successful statements: <?php echo $success_count; ?>', 'success');
        <?php if ($error_count > 0): ?>
        addLog('Errors encountered: <?php echo $error_count; ?>', 'error');
        <?php endif; ?>
        addLog('Redirecting to login in 3 seconds...', 'info');
        
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 3000);
    </script>
    <?php
}
?>

</body>
</html>
