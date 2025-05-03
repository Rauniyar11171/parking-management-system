<?php
session_start();
require_once 'config/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$error = '';
$success = false;

// If this is an AJAX request, process registration and return response
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Set proper headers
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    header('X-Content-Type-Options: nosniff');
    
    $response = ['status' => 'error', 'message' => ''];
    
    // Enable error logging
    ini_set('log_errors', 1);
    ini_set('error_log', 'php_errors.log');
    
    // Log request data
    file_put_contents('debug.log', 'Form submitted at ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    file_put_contents('debug.log', 'POST data: ' . print_r($_POST, true) . "\n\n", FILE_APPEND);
    
    // Log the incoming request
    error_log('Registration request received: ' . print_r($_POST, true));
    
    // Check database connection
    try {
        $pdo->query('SELECT 1');
        error_log('Database connection successful');
    } catch (PDOException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        $response['message'] = 'Database connection error';
        echo json_encode($response);
        exit;
    }
    
    // Get and validate input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';


    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $response['message'] = 'All fields are required';
        echo json_encode($response);
        exit;
    } elseif ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match';
        echo json_encode($response);
        exit;
    } elseif (strlen($password) < 8) {
        $response['message'] = 'Password must be at least 8 characters long';
        echo json_encode($response);
        exit;
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $response['message'] = 'Email already exists';
            echo json_encode($response);
            exit;
        }
        

        
        // Create new user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            // Debug logging
            error_log('Attempting database insert for email: ' . $email);
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, status) VALUES (?, ?, ?, 'active')");
            $result = $stmt->execute([$name, $email, $hashed_password]);
            
            // Debug logging
            error_log('Database execute result: ' . var_export($result, true));
            
            if ($result) {
                error_log('User registration successful for email: ' . $email);
                
                // Clear any existing session data
                session_unset();
                
                // Set success messages in session
                $_SESSION['registration_success'] = true;
                $_SESSION['success_message'] = 'Registration successful! You can now login with your email and password.';
                $_SESSION['user_name'] = $name;
                
                // Send success response with redirect
                $response = [
                    'status' => 'success',
                    'message' => 'Account created successfully! Redirecting to login page...',
                    'redirect' => 'login.php',
                    'name' => $name
                ];
                echo json_encode($response);
                exit;
            } else {
                $error_info = $stmt->errorInfo();
                error_log('Registration failed. Error info: ' . print_r($error_info, true));
                $response['message'] = 'Registration failed: ' . implode(", ", $error_info);
                echo json_encode($response);
                exit;
            }
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            $response['message'] = 'Database error occurred. Please try again later.';
            echo json_encode($response);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Parking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <div class="form-container">
            <h2 class="text-center mb-4">Register</h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger registration-error">
                    <div class="text-center mb-2">
                        <i class="bi bi-exclamation-circle-fill display-1"></i>
                    </div>
                    <p class="text-center"><?php echo htmlspecialchars($error); ?></p>
                </div>
                <style>
                    .registration-error {
                        text-align: center;
                        padding: 2rem;
                        margin-bottom: 2rem;
                    }
                    .registration-error i {
                        color: #dc3545;
                    }
                </style>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success text-center py-4 registration-success">
                    <i class="bi bi-check-circle-fill display-1 text-success mb-3"></i>
                    <h4 class="alert-heading mb-3">Welcome, <?php echo htmlspecialchars($name); ?>!</h4>
                    <p class="mb-3">Your registration was successful!</p>
                    <div class="countdown mb-3">Redirecting to login page in <span id="countdown">5</span> seconds...</div>
                    <p class="mb-0">
                        <a href="login.php" class="btn btn-success">Login Now</a>
                    </p>
                </div>
                <style>
                    .registration-success {
                        background-color: #d4edda;
                        border-color: #c3e6cb;
                        box-shadow: 0 0 15px rgba(0,0,0,0.1);
                    }
                    .registration-success i {
                        color: #28a745;
                    }
                    .countdown {
                        font-size: 1.1em;
                        color: #155724;
                    }
                </style>
                <script>
                    // Countdown timer
                    let timeLeft = 5;
                    const countdownElement = document.getElementById('countdown');
                    const countdownInterval = setInterval(() => {
                        timeLeft--;
                        countdownElement.textContent = timeLeft;
                        if (timeLeft <= 0) {
                            clearInterval(countdownInterval);
                            window.location.href = 'login.php';
                        }
                    }, 1000);
                </script>
            <?php endif; ?>
            <form id="registerForm" novalidate>

                <div class="mb-3">
                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required 
                           pattern="[A-Za-z ]{2,}" title="Name should contain only letters and spaces, minimum 2 characters">
                    <div class="invalid-feedback">Please enter a valid name (only letters and spaces, minimum 2 characters).</div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required 
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address">
                    <div class="invalid-feedback">Please enter a valid email address (e.g., example@domain.com).</div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required 
                               minlength="8" title="Password must be at least 8 characters long">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="form-text">Password must be at least 8 characters long</div>
                    <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                               minlength="8" title="Please confirm your password">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">Passwords must match.</div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="text-center mt-3">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/register.js"></script>
</body>
</html>
