<?php
include 'db/config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST["name"]);
    $email = $conn->real_escape_string($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

    $check = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $message = "Email already registered!";
    } else {
        $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
        if ($conn->query($sql)) {
            header("Location: login.php");
            exit();
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Expense Tracker</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: #e6f2ff;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        overflow-x: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        color: #2b2b2b;
    }

    /* Floating background elements */
    .bg-animation {
        position: fixed;
        inset: 0;
        z-index: -1;
        overflow: hidden;
    }

    .floating-shape {
        position: absolute;
        background: rgba(26, 77, 143, 0.08);
        border-radius: 50%;
        animation: float 8s ease-in-out infinite;
    }

    .form-container {
        max-width: 450px;
        width: 100%;
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 25px 70px rgba(26, 77, 143, 0.25);
        padding: 50px 40px;
        text-align: center;
        border: 1px solid #cce0ff;
        position: relative;
    }

   

    h2 {
        color: #1a4d8f;
        margin-bottom: 12px;
        font-size: 2.4rem;
        font-weight: 700;
    }

    .subtitle {
        color: #444;
        font-size: 1.1rem;
        margin-bottom: 35px;
    }

    .form-container form {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .input-group {
        position: relative;
    }

    .input-group input {
        width: 100%;
        padding: 16px 20px 16px 50px;
        border-radius: 50px;
        border: 2px solid #cce0ff;
        font-size: 1rem;
        background: #f7faff;
        transition: all 0.3s ease;
        outline: none;
        color: #2b2b2b;
    }

    .input-group input:focus {
        border-color: #1a4d8f;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(26, 77, 143, 0.2);
    }

    .input-group input::placeholder {
        color: #777;
    }

    .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.2rem;
        color: #1a4d8f;
    }

    button[type="submit"] {
        background: #1a4d8f;
        color: #ffffff;
        border: none;
        border-radius: 50px;
        padding: 16px 0;
        font-size: 1.2rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 10px;
    }

    button[type="submit"]:hover {
        background: #003d80;
        box-shadow: 0 12px 30px rgba(26, 77, 143, 0.35);
    }

    .msg {
        color: #e53935;
        font-size: 1rem;
        padding: 10px;
        border-radius: 12px;
        background: rgba(229, 57, 53, 0.1);
        border-left: 4px solid #e53935;
    }

    .login-link {
        margin-top: 25px;
        color: #444;
        font-size: 1rem;
    }

    .login-link a {
        color: #1a4d8f;
        text-decoration: none;
        font-weight: 600;
    }

    .login-link a:hover {
        color: #003d80;
        text-decoration: underline;
    }

    .back-home {
        position: absolute;
        top: 30px;
        left: 30px;
        color: #1a4d8f;
        text-decoration: none;
        font-size: 1rem;
    }

    .back-home:hover {
        color: #003d80;
    }

    @media (max-width: 600px) {
        .form-container {
            padding: 40px 25px;
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

    * {
        animation: none !important;
        transition: none !important;
    }
</style>

</head>
<body>
    <div class="bg-animation">
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
    </div>

    <a href="home.php" class="back-home">← Back to Home</a>

    <div class="form-container">
     
        <h2>Create Account</h2>
        <p class="subtitle">Join thousands managing their finances smarter</p>
        
        <form method="post" action="" id="registerForm">
            <div class="input-group">
                <input type="text" name="name" id="name" placeholder="Full Name" required>
                <div class="input-icon">👤</div>
            </div>
            
            <div class="input-group">
                <input type="email" name="email" id="email" placeholder="Email Address" required>
                <div class="input-icon">📧</div>
            </div>
            
            <div class="input-group">
                <input type="password" name="password" id="password" placeholder="Create Password" required minlength="6">
                <div class="input-icon">🔒</div>
            </div>
            
            <button type="submit">Create My Account</button>
            
            <?php if($message): ?>
                <div class="msg"><?= $message ?></div>
            <?php endif; ?>
        </form>
        
        <p class="login-link">
            Already have an account? <a href="login.php">Sign in here</a>
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const inputs = form.querySelectorAll('input');
            
            // Add real-time validation
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateInput(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('error')) {
                        validateInput(this);
                    }
                });
            });
            
            // Form submission with animation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!validateInput(input)) {
                        isValid = false;
                    }
                });
                
                if (isValid) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = '<span style="animation: spin 1s linear infinite;">⏳</span> Creating Account...';
                    submitBtn.style.background = 'linear-gradient(45deg, #4caf50, #45a049)';
                }
            });
            
            function validateInput(input) {
                const value = input.value.trim();
                let isValid = true;
                
                // Remove previous error styling
                input.style.borderColor = '';
                input.style.background = '';
                
                if (input.type === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        showInputError(input, 'Please enter a valid email address');
                        isValid = false;
                    }
                } else if (input.type === 'password') {
                    if (value.length < 6) {
                        showInputError(input, 'Password must be at least 6 characters');
                        isValid = false;
                    }
                } else if (input.name === 'name') {
                    if (value.length < 2) {
                        showInputError(input, 'Please enter your full name');
                        isValid = false;
                    }
                }
                
                if (isValid) {
                    input.classList.remove('error');
                    input.style.borderColor = '#4caf50';
                    input.style.background = 'rgba(76, 175, 80, 0.05)';
                }
                
                return isValid;
            }
            
            function showInputError(input, message) {
                input.classList.add('error');
                input.style.borderColor = '#e53935';
                input.style.background = 'rgba(229, 57, 53, 0.05)';
                input.style.animation = 'shake 0.5s ease-in-out';
                
                // Remove animation after it completes
                setTimeout(() => {
                    input.style.animation = '';
                }, 500);
            }
        });

        // Add spinning animation for loading state
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>