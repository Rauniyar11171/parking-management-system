<?php
session_start();
require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['name']; // Add username to session
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Parking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <div class="form-container">
            <h2 class="text-center mb-4">Login</h2>
            <?php if (isset($_SESSION['registration_success']) && $_SESSION['registration_success'] === true): ?>
                <div class="alert alert-success welcome-message">
                    <div class="text-center mb-3">
                        <i class="bi bi-check-circle-fill display-4 text-success"></i>
                    </div>
                    <h4 class="alert-heading text-center mb-3">
                        Welcome, <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'New User'; ?>!
                    </h4>
                    <p class="text-center mb-0"><?php echo $_SESSION['success_message'] ?? 'Registration successful! You can now login.'; ?></p>
                </div>
                <style>
                    .welcome-message {
                        background-color: #d4edda;
                        border-color: #c3e6cb;
                        box-shadow: 0 0 15px rgba(0,0,0,0.1);
                        padding: 2rem;
                        margin-bottom: 2rem;
                        animation: fadeInDown 0.5s ease-out;
                    }
                    .welcome-message i {
                        color: #28a745;
                        font-size: 3rem;
                        margin-bottom: 1rem;
                    }
                    @keyframes fadeInDown {
                        from {
                            opacity: 0;
                            transform: translateY(-20px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                </style>
                <?php 
                    // Clear the registration success messages
                    unset($_SESSION['registration_success']);
                    unset($_SESSION['success_message']);
                    unset($_SESSION['user_name']);
                ?>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger login-error">
                    <div class="text-center mb-2">
                        <i class="bi bi-exclamation-circle-fill display-4 text-danger"></i>
                    </div>
                    <h4 class="alert-heading text-center mb-2">Login Failed</h4>
                    <p class="text-center mb-0"><?php echo $error; ?></p>
                    <p class="text-center mt-2 small">
                        Please check your credentials and try again.<br>
                        Make sure you have registered an account first.
                    </p>
                </div>
                <style>
                    .login-error {
                        background-color: #f8d7da;
                        border-color: #f5c6cb;
                        box-shadow: 0 0 15px rgba(0,0,0,0.1);
                        padding: 2rem;
                        margin-bottom: 2rem;
                    }
                    .login-error i {
                        color: #dc3545;
                    }
                </style>
            <?php endif; ?>
            <form id="loginForm" method="POST" action="login.php" onsubmit="return validateForm('loginForm')">
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="invalid-feedback">Please enter your password.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <p class="text-center mt-3">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Check for registration success message in session storage
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = sessionStorage.getItem('registration_success');
            if (successMessage) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success text-center';
                alertDiv.role = 'alert';
                alertDiv.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i>${successMessage}`;
                
                const container = document.querySelector('.container');
                container.insertBefore(alertDiv, container.firstChild);
                
                // Clear the message
                sessionStorage.removeItem('registration_success');
            }
        });
    </script>
</body>
</html>
