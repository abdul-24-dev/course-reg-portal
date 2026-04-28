<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Check if email exists
    $sql = $conn->prepare("SELECT id FROM students WHERE email = ?");
    $sql->bind_param("s", $email);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

        // Store token in the database
        $stmt = $conn->prepare("UPDATE students SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expires, $email);
        $stmt->execute();

        // Display reset link instead of sending email
        $reset_link = "http://localhost/course-reg-portal/reset_password.php?token=$token";
        echo "<script>alert('Password reset link generated. Copy this link: $reset_link');</script>";
    } else {
        echo "<script>alert('Email not found.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | University Portal</title>
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

        .forgot-card {
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

        .alert-info {
            background: rgba(14, 165, 233, 0.15);
            border: none;
            border-radius: 12px;
        }

        @media (max-width: 576px) {
            .forgot-card {
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
                <div class="forgot-card">
                    <div class="text-center mb-5">
                        <h2 class="mb-3" style="background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            Forgot Password
                        </h2>
                    </div>

                    <?php if(isset($reset_link)): ?>
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-link me-2"></i>
                            Reset link: <br>
                            <a href="<?php echo $reset_link; ?>" class="text-break"><?php echo $reset_link; ?></a>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2" style="background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); border: none; border-radius: 12px;">
                            <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                        </button>

                        <div class="text-center mt-3">
                            <a href="login.php" class="text-decoration-none text-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>