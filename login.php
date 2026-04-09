<?php
include 'db/config.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $conn->real_escape_string($_POST["email"]);
    $password = $_POST["password"];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["name"] = $user["name"];

            /* ================= MONTHLY FIRST-LOGIN NOTIFICATION ================= */

            $user_id = $user["id"];
            $currentMonth = date('n');
            $currentYear  = date('Y');

            // Check if notification already exists for this month
            $check = $conn->query("
                SELECT id FROM notifications
                WHERE user_id='$user_id'
                AND description LIKE '%Create your budget for this month%'
                AND MONTH(date_time)='$currentMonth'
                AND YEAR(date_time)='$currentYear'
            ");

            if ($check->num_rows == 0) {

                // Previous month
                $prevMonth = date('n', strtotime('first day of last month'));
                $prevYear  = date('Y', strtotime('first day of last month'));

                // Budget
                $budgetRes = $conn->query("
                    SELECT amount FROM budgets
                    WHERE user_id='$user_id'
                    AND month='$prevMonth'
                    AND year='$prevYear'
                    LIMIT 1
                ");
                $budget = $budgetRes->num_rows > 0 ? $budgetRes->fetch_assoc()['amount'] : 0;

                // Expense
// Expense
$expenseRes = $conn->query("
    SELECT SUM(amount) AS total
    FROM expenses
    WHERE user_id='$user_id'
    AND MONTH(date)='$prevMonth'
    AND YEAR(date)='$prevYear'
");

$expenseRow = $expenseRes->fetch_assoc();
$expense = 0;
if ($expenseRow && $expenseRow['total'] !== null) {
    $expense = (float)$expenseRow['total'];
}

// Income
$incomeRes = $conn->query("
    SELECT SUM(amount) AS total
    FROM income
    WHERE user_id='$user_id'
    AND MONTH(date)='$prevMonth'
    AND YEAR(date)='$prevYear'
");

$incomeRow = $incomeRes->fetch_assoc();
$income = 0;
if ($incomeRow && $incomeRow['total'] !== null) {
    $income = (float)$incomeRow['total'];
}


                // Saved or exceeded
                if ($income >= $expense) {
                    $status = "saved";
                    $difference = $income - $expense;
                } else {
                    $status = "exceeded";
                    $difference = $expense - $income;
                }

if ($budget == 0 && $income == 0 && $expense == 0) {

    $messageText = "📢 Welcome! Create your first budget for this month to start tracking your finances.";

} else {

$messageText = "📢 Create your budget for this month!
&nbsp;&nbsp;&nbsp;&nbsp;Last month summary:
&nbsp;&nbsp;&nbsp;&nbsp;• Budget: ₹$budget
&nbsp;&nbsp;&nbsp;&nbsp;• Income: ₹$income
&nbsp;&nbsp;&nbsp;&nbsp;• Expense: ₹$expense
&nbsp;&nbsp;&nbsp;&nbsp;• You $status ₹$difference";

}



                $conn->query("
                    INSERT INTO notifications (user_id, description)
                    VALUES ('$user_id', '$messageText')
                ");
            }

            header("Location: dashboard.php");
            exit();

        } else {
            $message = "Incorrect password!";
        }

    } else {
        $message = "No account found!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Expense Tracker</title>
    
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

    .register-link {
        margin-top: 25px;
        color: #444;
        font-size: 1rem;
    }

    .register-link a {
        color: #1a4d8f;
        text-decoration: none;
        font-weight: 600;
    }

    .register-link a:hover {
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
    
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to continue your financial journey</p>
        

        
        <form method="post" action="" id="loginForm">
            <div class="input-group">
                <input type="email" name="email" id="email" placeholder="Email Address" required>
                <div class="input-icon">📧</div>
            </div>
            
            <div class="input-group">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <div class="input-icon">🔒</div>
            </div>
            
          
            
            <button type="submit">Sign In</button>
            
            <?php if($message): ?>
                <div class="msg"><?= $message ?></div>
            <?php endif; ?>
        </form>
        
        <p class="register-link">
            Don't have an account? <a href="register.php">Create one here</a>
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
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
                    submitBtn.innerHTML = '<span style="animation: spin 1s linear infinite;">⏳</span> Signing In...';
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
                    if (value.length < 1) {
                        showInputError(input, 'Password is required');
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

        function showForgotPassword() {
            window.location.href = 'reset_password.php';
        }

        // Add spinning animation for loading state
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);

        // Auto-focus first input after animations
        setTimeout(() => {
            document.getElementById('email').focus();
        }, 1200);
    </script>
</body>
</html>