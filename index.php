<?php
// Check environment and database status
$servername = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASS') ?: "";
$dbname = getenv('DB_NAME') ?: "course_registration_db";

$db_connected = false;
$error_msg = "";

// Try to connect to database
$conn = @mysqli_connect($servername, $username, $password, $dbname);

if ($conn) {
    $db_connected = true;
    mysqli_close($conn);
    // Database is working, redirect to login
    header('Location: login.php');
    exit();
} else {
    $error_msg = "Database not configured. Please set environment variables in Railway.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Registration Portal</title>
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
        .status-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        .status-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }
        .icon-success { color: #22c55e; }
        .icon-warning { color: #f59e0b; }
        .info-box {
            background: #f3f4f6;
            border-left: 4px solid #3b82f6;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            text-align: left;
        }
        .info-box h5 {
            color: #1e3a5f;
            margin-bottom: 0.5rem;
        }
        .info-box code {
            display: block;
            background: white;
            padding: 0.5rem;
            border-radius: 4px;
            margin-top: 0.5rem;
            color: #dc2626;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="status-card">
        <div class="status-icon <?php echo $db_connected ? 'icon-success' : 'icon-warning'; ?>">
            <?php echo $db_connected ? '✓' : '⚠'; ?>
        </div>
        
        <h1><?php echo $db_connected ? 'Welcome!' : 'Configuration Needed'; ?></h1>
        
        <?php if (!$db_connected): ?>
            <p class="text-muted mb-3">The application is running, but the database is not configured.</p>
            
            <div class="info-box">
                <h5>To set up the database in Railway:</h5>
                <ol class="mb-0" style="text-align: left;">
                    <li>Go to your Railway project dashboard</li>
                    <li>Click the <strong>+ Create</strong> button</li>
                    <li>Select <strong>MySQL</strong> (or PostgreSQL)</li>
                    <li>Connect it to your app</li>
                    <li>Railway will automatically set environment variables</li>
                </ol>
            </div>
            
            <div class="info-box" style="border-left-color: #6366f1;">
                <h5>Current Environment:</h5>
                <code>DB_HOST: <?php echo getenv('DB_HOST') ?: 'Not set'; ?></code>
                <code>DB_USER: <?php echo getenv('DB_USER') ?: 'Not set'; ?></code>
                <code>DB_NAME: <?php echo getenv('DB_NAME') ?: 'Not set'; ?></code>
            </div>
            
            <p class="text-muted small mt-3">Once the database is connected and migrated, you'll be redirected to the login page.</p>
        <?php else: ?>
            <p class="text-muted">Redirecting to login...</p>
        <?php endif; ?>
    </div>
</body>
</html>
