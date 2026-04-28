<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.');</script>";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Check if token is valid and not expired
        $sql = $conn->prepare("SELECT id FROM students WHERE reset_token = ? AND reset_expires > NOW()");
        $sql->bind_param("s", $token);
        $sql->execute();
        $result = $sql->get_result();

        if ($result->num_rows > 0) {
            // Update password and clear reset token
            $stmt = $conn->prepare("UPDATE students SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
            $stmt->bind_param("ss", $hashed_password, $token);
            $stmt->execute();

            echo "<script>alert('Password reset successfully!'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Invalid or expired token.');</script>";
        }
    }
}

$token = $_GET['token'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | University Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
        }

        body {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        .reset-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1.5rem;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
            padding: 2.5rem;
            width: 100%;
            max-width: 440px;
            margin: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-control {
            height: 50px;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            border: 2px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-reset {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        @media (max-width: 576px) {
            .reset-card {
                padding: 1.5rem;
                margin: 1rem;
                border-radius: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="reset-card">
                    <div class="text-center mb-5">
                        <h2 class="mb-3" style="background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            Reset Password
                        </h2>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn-reset w-100">
                            <i class="fas fa-redo me-2"></i>Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>