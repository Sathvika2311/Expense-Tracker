<?php
include 'db/config.php';
session_start();

$message = "";
$success = false;
$step = isset($_GET['step']) ? $_GET['step'] : 'request';
$token = isset($_GET['token']) ? $_GET['token'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';

// Step 1: Request password reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && $step == 'request') {
    $email = $conn->real_escape_string($_POST["email"]);
    
    // Check if email exists
    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($result->num_rows == 1) {
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database (create reset_tokens table if it doesn't exist)
        $conn->query("CREATE TABLE IF NOT EXISTS reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Delete any existing tokens for this email
        $conn->query("DELETE FROM reset_tokens WHERE email='$email'");
        
        // Insert new token
        $conn->query("INSERT INTO reset_tokens (email, token, expires) VALUES ('$email', '$token', '$expires')");
        
        // In a real application, you would send an email with the reset link
        // For this demo, we'll just show the link on the page
        $reset_link = "reset_password.php?step=reset&token=$token&email=$email";
        $message = "A password reset link has been generated. In a real application, this would be emailed to you.";
        $success = true;
    } else {
        $message = "No account found with that email address.";
    }
}

// Step 2: Reset password with token
if ($_SERVER["REQUEST_METHOD"] == "POST" && $step == 'reset') {
    $email = $conn->real_escape_string($_POST["email"]);
    $token = $conn->real_escape_string($_POST["token"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    
    // Validate passwords match
    if ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Check token validity
        $result = $conn->query("SELECT * FROM reset_tokens WHERE email='$email' AND token='$token' AND expires > NOW()");
        if ($result->num_rows == 1) {
            // Hash the new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Update user password
            $conn->query("UPDATE users SET password='$hashed_password' WHERE email='$email'");
            
            // Delete used token
            $conn->query("DELETE FROM reset_tokens WHERE email='$email'");
            
            $message = "Your password has been reset successfully. You can now login with your new password.";
            $success = true;
            // Redirect to login page after 3 seconds
            header("refresh:3;url=login.php");
        } else {
            $message = "Invalid or expired reset token.";
        }
    }
}

// If step is 'reset' and token is provided, verify token
if ($step == 'reset' && !empty($token) && !empty($email)) {
    $result = $conn->query("SELECT * FROM reset_tokens WHERE email='$email' AND token='$token' AND expires > NOW()");
    if ($result->num_rows != 1) {
        $message = "Invalid or expired reset token.";
        $step = 'request'; // Go back to request step
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Expense Tracker</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Floating background elements */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .floating-shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        .floating-shape:nth-child(1) {
            width: 120px;
            height: 120px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-shape:nth-child(2) {
            width: 180px;
            height: 180px;
            top: 60%;
            right: 15%;
            animation-delay: 4s;
        }

        .floating-shape:nth-child(3) {
            width: 90px;
            height: 90px;
            bottom: 15%;
            left: 25%;
            animation-delay: 2s;
        }

        .form-container {
            max-width: 450px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.25);
            padding: 50px 40px;
            text-align: center;
            animation: slideInUp 0.8s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #4facfe);
            animation: shimmer 3s ease-in-out infinite;
        }

        .emoji {
            font-size: 3.5rem;
            margin-bottom: 15px;
            animation: bounce 2s infinite, glow 3s ease-in-out infinite alternate;
            display: inline-block;
        }

        h2 {
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 12px;
            font-size: 2.4rem;
            font-weight: 700;
            animation: fadeInDown 0.8s ease-out 0.2s both;
        }

        .subtitle {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 35px;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 25px;
            animation: fadeInUp 0.8s ease-out 0.6s both;
        }

        .input-group {
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border-radius: 50px;
            border: 2px solid rgba(102, 126, 234, 0.1);
            font-size: 1rem;
            background: rgba(248, 249, 255, 0.8);
            transition: all 0.3s ease;
            outline: none;
        }

        .input-group input:focus {
            border-color: #667eea;
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }

        .input-group input::placeholder {
            color: #999;
            transition: all 0.3s ease;
        }

        .input-group input:focus::placeholder {
            color: transparent;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            color: #667eea;
            transition: all 0.3s ease;
        }

        .input-group input:focus + .input-icon {
            transform: translateY(-50%) scale(1.1);
            color: #764ba2;
        }

        button[type="submit"] {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 16px 0;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            margin-top: 10px;
        }

        button[type="submit"]::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        button[type="submit"]:hover::before {
            left: 100%;
        }

        button[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        .msg {
            font-size: 1rem;
            margin: 15px 0;
            padding: 12px;
            border-radius: 12px;
            animation: fadeInUp 0.5s ease-in-out;
        }

        .msg.error {
            color: #e53935;
            background: rgba(229, 57, 53, 0.1);
            border-left: 4px solid #e53935;
        }

        .msg.success {
            color: #43a047;
            background: rgba(67, 160, 71, 0.1);
            border-left: 4px solid #43a047;
        }

        .reset-link {
            margin: 20px 0;
            padding: 15px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 12px;
            word-break: break-all;
            font-family: monospace;
            font-size: 0.9rem;
            color: #667eea;
            border: 1px dashed #667eea;
        }

        .back-link {
            margin-top: 25px;
            color: #666;
            font-size: 1rem;
            animation: fadeInUp 0.8s ease-out 0.8s both;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .back-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }

        .back-link a:hover::after {
            width: 100%;
        }

        .back-link a:hover {
            transform: translateY(-1px);
        }

        .back-home {
            position: absolute;
            top: 30px;
            left: 30px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.3s ease;
            animation: fadeInLeft 0.8s ease-out 1s both;
        }

        .back-home:hover {
            color: #fff;
            transform: translateX(-5px);
        }

        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        @keyframes glow {
            0% { filter: drop-shadow(0 0 5px rgba(102, 126, 234, 0.5)); }
            100% { filter: drop-shadow(0 0 15px rgba(102, 126, 234, 0.8)); }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .form-container {
                padding: 40px 25px;
                margin: 20px;
            }
            h2 {
                font-size: 2rem;
            }
            .back-home {
                top: 20px;
                left: 20px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
    </div>

    <a href="index.php" class="back-home">← Back to Home</a>

    <div class="form-container">
        <?php if ($step == 'request'): ?>
            <div class="emoji">🔑</div>
            <h2>Reset Password</h2>
            <p class="subtitle">Enter your email to reset your password</p>
            
            <form method="post" action="" id="resetRequestForm">
                <div class="input-group">
                    <input type="email" name="email" id="email" placeholder="Email Address" required>
                    <div class="input-icon">📧</div>
                </div>
                
                <button type="submit">Send Reset Link</button>
            </form>
            
            <?php if($message): ?>
                <div class="msg <?= $success ? 'success' : 'error' ?>"><?= $message ?></div>
                <?php if($success): ?>
                    <div class="reset-link">
                        <a href="<?= $reset_link ?>"><?= $reset_link ?></a>
                    </div>
                    <p class="subtitle" style="margin-top: 0; font-size: 0.9rem;">
                        (In a real application, this link would be sent to your email)
                    </p>
                <?php endif; ?>
            <?php endif; ?>
            
            <p class="back-link">
                Remember your password? <a href="login.php">Sign in here</a>
            </p>
        <?php else: ?>
            <div class="emoji">🔒</div>
            <h2>Create New Password</h2>
            <p class="subtitle">Enter your new password below</p>
            
            <form method="post" action="" id="resetPasswordForm">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <div class="input-group">
                    <input type="password" name="password" id="password" placeholder="New Password" required minlength="6">
                    <div class="input-icon">🔒</div>
                </div>
                
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required minlength="6">
                    <div class="input-icon">🔒</div>
                </div>
                
                <button type="submit">Reset Password</button>
            </form>
            
            <?php if($message): ?>
                <div class="msg <?= $success ? 'success' : 'error' ?>"><?= $message ?></div>
            <?php endif; ?>
            
            <?php if(!$success): ?>
                <p class="back-link">
                    <a href="reset_password.php">Back to Reset Request</a>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation
            const resetRequestForm = document.getElementById('resetRequestForm');
            const resetPasswordForm = document.getElementById('resetPasswordForm');
            
            if (resetRequestForm) {
                resetRequestForm.addEventListener('submit', function(e) {
                    const email = document.getElementById('email').value.trim();
                    if (!validateEmail(email)) {
                        e.preventDefault();
                        showError('Please enter a valid email address');
                    }
                });
            }
            
            if (resetPasswordForm) {
                resetPasswordForm.addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
                    if (password.length < 6) {
                        e.preventDefault();
                        showError('Password must be at least 6 characters');
                    } else if (password !== confirmPassword) {
                        e.preventDefault();
                        showError('Passwords do not match');
                    }
                });
            }
            
            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }
            
            function showError(message) {
                // Remove any existing error message
                const existingMsg = document.querySelector('.msg.error');
                if (existingMsg) {
                    existingMsg.remove();
                }
                
                // Create new error message
                const msgDiv = document.createElement('div');
                msgDiv.className = 'msg error';
                msgDiv.textContent = message;
                
                // Insert after the form
                const form = document.querySelector('form');
                form.after(msgDiv);
                
                // Add animation
                msgDiv.style.animation = 'fadeInUp 0.5s ease-in-out';
            }
            
            // Auto-focus first input after animations
            setTimeout(() => {
                const firstInput = document.querySelector('input:not([type="hidden"])');
                if (firstInput) {
                    firstInput.focus();
                }
            }, 1200);
        });
    </script>
</body>
</html>