<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once 'db/config.php';

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $source = trim($_POST["source"] ?? "");
   $amount = intval($_POST["amount"]);

    $description = trim($_POST["description"] ?? "");
    $date = trim($_POST["date"] ?? "");
    $user_id = $_SESSION["user_id"];
    
    // Validation
    $errors = [];
    
    if (empty($source)) {
        $errors[] = "Please select where this income came from";
    }
    
    if ($amount === false || $amount <= 0) {
        $errors[] = "Please enter a valid amount";
    }
    
    if (empty($date)) {
        $errors[] = "Please select when you received this income";
    } elseif (!DateTime::createFromFormat('Y-m-d', $date)) {
        $errors[] = "Invalid date format";
    }
    
    if (empty($errors)) {
        // Use prepared statement for security
        $stmt = $conn->prepare("INSERT INTO income (user_id, source, amount, description, date) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("isdss", $user_id, $source, $amount, $description, $date);
            
            if ($stmt->execute()) {
                $message = "🎉 Amazing! Your income has been recorded successfully. You're doing great managing your finances!";
                $success = true;
                
                // Clear form data after successful submission
                $_POST = [];
            } else {
                $message = "Oops! Something went wrong. Please try again.";
                $success = false;
                error_log("Database error: " . $stmt->error);
            }
            
            $stmt->close();
        } else {
            $message = "Database error. Please try again.";
            $success = false;
            error_log("Prepare statement failed: " . $conn->error);
        }
    } else {
        $message = implode(", ", $errors);
        $success = false;
    }
}

// Set default date to today if not set
$defaultDate = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Your Income | Expense Tracker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #cce4ff;
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
            width: 100px;
            height: 100px;
            top: 15%;
            left: 15%;
            animation-delay: 0s;
        }

        .floating-shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 70%;
            right: 10%;
            animation-delay: 3s;
        }

        .floating-shape:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 20%;
            left: 20%;
            animation-delay: 6s;
        }

       .form-container {
            max-width: 1150px;
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

        

        .header-section {
            margin-bottom: 35px;
            animation: fadeInDown 0.8s ease-out 0.2s both;
        }

        .emoji {
            font-size: 3.5rem;
            margin-bottom: 15px;
            animation: bounce 2s infinite, glow 3s ease-in-out infinite alternate;
            display: inline-block;
        }
    h2 {
    color: #1a4d8f; /* dark blue same as dashboard sidebar text */
    margin-bottom: 8px;
    font-size: 2.6rem;
    font-weight: 700;
}

        .subtitle {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        .welcome-tip {
            background: linear-gradient(45deg, rgba(102, 126, 234, 0.1), rgba(79, 172, 254, 0.1));
            border-radius: 16px;
            padding: 16px 20px;
            margin-bottom: 35px;
            border: 1px solid rgba(102, 126, 234, 0.2);
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

         .tip-text {
            color: #667eea;
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.4;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 25px;
            animation: fadeInUp 0.8s ease-out 0.6s both;
        }

        .section-title {
            text-align: left;
            color: #555;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .source-section {
            margin-bottom: 10px;
        }

        .source-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 10px;
        }

        .source-option {
            background: rgba(248, 249, 255, 0.8);
            border: 2px solid rgba(67, 160, 71, 0.1);
            border-radius: 16px;
            padding: 16px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .source-option:hover {
            border-color: #667eea;
            background: #fff;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 160, 71, 0.2);
        }

        .source-option.selected {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: #fff;
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 160, 71, 0.3);
        }

        .source-option .source-emoji {
            font-size: 1.8rem;
            margin-bottom: 8px;
            display: block;
            animation: none;
        }

        .source-option .source-name {
            font-size: 0.85rem;
            font-weight: 600;
        }

        .input-group {
            position: relative;
            text-align: left;
        }

        .input-label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .input-group input,
        .input-group textarea {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border-radius: 50px;
            border: 2px solid rgba(102, 126, 234, 0.1);
            font-size: 1rem;
            background: rgba(248, 249, 255, 0.8);
            transition: all 0.3s ease;
            outline: none;
            font-family: inherit;
        }

        .input-group textarea {
            border-radius: 20px;
            padding: 16px 20px;
            resize: vertical;
            min-height: 80px;
        }

        .input-group input:focus,
        .input-group textarea:focus {
            border-color: #667eea;
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }

        .input-group input::placeholder,
        .input-group textarea::placeholder {
            color: #999;
            transition: all 0.3s ease;
        }

        .input-group input:focus::placeholder,
        .input-group textarea:focus::placeholder {
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
            pointer-events: none;
        }

        .textarea-group .input-icon {
            top: 45px;
        }

        .input-group input:focus + .input-icon {
            transform: translateY(-50%) scale(1.1);
            color: #764ba2;
        }

        .amount-helper {
            font-size: 0.85rem;
            color: #888;
            margin-top: 5px;
            text-align: left;
        }

        button[type="submit"] {
    background: #cce4ff; /* dark blue */
    color: #1a4d8f;
    border: none;
    border-radius: 50px;
    padding: 18px 0;
    font-size: 1.2rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    margin-top: 15px;
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
            box-shadow: 0 15px 40px rgba(67, 160, 71, 0.4);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        button[type="submit"]:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .msg {
            font-size: 1rem;
            margin: 10px 0 0 0;
            min-height: 20px;
            padding: 14px 18px;
            border-radius: 16px;
            animation: slideInUp 0.5s ease-in-out;
            text-align: center;
            font-weight: 500;
        }

        .msg.success {
            color: #2e7d32;
            background: rgba(46, 125, 50, 0.1);
            border: 2px solid rgba(46, 125, 50, 0.2);
        }

        .msg.error {
            color: #d32f2f;
            background: rgba(211, 47, 47, 0.1);
            border: 2px solid rgba(211, 47, 47, 0.2);
        }

        .msg:empty {
            display: none;
        }

        .back-link {
            margin-top: 30px;
            color: #666;
            font-size: 1rem;
            animation: fadeInUp 0.8s ease-out 0.8s both;
        }

        .back-link a {
            color: #43a047;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(45deg, #43a047, #00c853);
            transition: width 0.3s ease;
        }

        .back-link a:hover::after {
            width: 100%;
        }

        .back-link a:hover {
            transform: translateY(-1px);
        }

        .progress-indicator {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 25px;
            animation: fadeInUp 0.8s ease-out 0.7s both;
        }

        .progress-step {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(67, 160, 71, 0.3);
            transition: all 0.3s ease;
        }

        .progress-step.active {
            background: #43a047;
            transform: scale(1.2);
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
            0% { filter: drop-shadow(0 0 5px rgba(67, 160, 71, 0.5)); }
            100% { filter: drop-shadow(0 0 15px rgba(67, 160, 71, 0.8)); }
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

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .form-container {
                padding: 40px 25px;
                margin: 20px;
            }
            h2 {
                font-size: 2.2rem;
            }
            .source-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            .source-option {
                padding: 12px 6px;
            }
        }
		* {
    animation: none !important;
    transition: none !important;
}
.btn-back {
    position: absolute; /* top-left corner */
    top: 20px;
    left: 20px;
    display: inline-block;
    padding: 10px 16px;
    background-color: #fff;
    border: 2px solid #667eea; /* box border */
    border-radius: 8px;
    color: #667eea;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
    </div>

    <div class="form-container">
        <div class="header-section">
           <a href="income.php" class="btn-back">← Income</a>
            <h2>Record Your Income</h2>
        
        </div>

        <div class="welcome-tip">
            <div class="tip-text">
             Recording your income helps you see your earning patterns and celebrate your financial wins!
            </div>
        </div>
        
        <form method="post" action="" id="incomeForm" novalidate>
            <div class="source-section">
                <div class="section-title">
                    <span></span> Where did this income come from?
                </div>
                <div class="source-grid">
                    <div class="source-option" data-source="Salary">
                        <div class="source-emoji">💼</div>
                        <div class="source-name">Salary</div>
                    </div>
                    <div class="source-option" data-source="Business">
                        <div class="source-emoji">🏢</div>
                        <div class="source-name">Business</div>
                    </div>
                    <div class="source-option" data-source="Freelance">
                        <div class="source-emoji">💻</div>
                        <div class="source-name">Freelance</div>
                    </div>
                    <div class="source-option" data-source="Investment">
                        <div class="source-emoji">📈</div>
                        <div class="source-name">Investment</div>
                    </div>
                    <div class="source-option" data-source="Gift">
                        <div class="source-emoji">🎁</div>
                        <div class="source-name">Gift</div>
                    </div>
                    <div class="source-option" data-source="Other">
                        <div class="source-emoji">📦</div>
                        <div class="source-name">Other</div>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="source" id="selectedSource" required>
            
            <div class="section-title">
                <span>💰</span> How much did you receive?
            </div>
            <div class="input-group">
               <input 
    type="number"
    name="amount"
    id="amount"
    placeholder="Enter amount"
    min="1"
    step="1"
    inputmode="numeric"
    
    required
>

               
            </div>
            
            <div class="section-title">
                <span>📝</span> Description (optional)
            </div>
            <div class="input-group textarea-group">
                <textarea 
                    name="description" 
                    id="description" 
                    placeholder="Add more details..."
                    rows="3"
                    maxlength="500"
                ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            
            <div class="section-title">
             When did you receive this?
            </div>
            <div class="input-group">
                <input 
                    type="date" 
                    name="date" 
                    id="date" 
                    required
                    value="<?= htmlspecialchars($_POST['date'] ?? $defaultDate) ?>"
                    max="<?= date('Y-m-d') ?>"
                >
                <div class="input-icon">📅</div>
            </div>
            
            <button type="submit" id="submitBtn"> Record This Income</button>
            
            <?php if (!empty($message)): ?>
                <div class="msg <?= $success ? 'success' : 'error' ?>" role="alert">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
        </form>
        
       
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('incomeForm');
            const inputs = form.querySelectorAll('input, textarea');
            const sourceOptions = document.querySelectorAll('.source-option');
            const selectedSourceInput = document.getElementById('selectedSource');
            const dateInput = document.getElementById('date');
            const progressSteps = document.querySelectorAll('.progress-step');
            const submitBtn = document.getElementById('submitBtn');
            
            // Set today's date as default if not set
            if (!dateInput.value) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.value = today;
            }
            
            // Source selection with welcoming feedback
            sourceOptions.forEach(option => {
                option.addEventListener('click', function() {
                    sourceOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    selectedSourceInput.value = this.dataset.source;
                    
                    // Update progress
                    progressSteps[1].classList.add('active');
                    
                    // Add selection animation
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(-3px)';
                    }, 100);
                    
                    // Show encouraging message
                    showTempMessage(`Excellent! ${this.dataset.source} selected 👍`, 'success');
                });
            });
            
            // Amount input validation with encouraging feedback
            const amountInput = document.getElementById('amount');
            amountInput.addEventListener('input', function() {
                if (this.value && parseFloat(this.value) > 0) {
                    progressSteps[2].classList.add('active');
                    showTempMessage('Perfect! Amount recorded 💰', 'success');
                }
            });
            
            // Add real-time validation with welcoming messages
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
            
            // Form submission with encouraging animation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validate source selection
                if (!selectedSourceInput.value) {
                    showError('Please select where this income came from - it helps track your earning sources! 😊');
                    isValid = false;
                }
                
                inputs.forEach(input => {
                    if (!validateInput(input)) {
                        isValid = false;
                    }
                });
                
                if (isValid) {
                    submitBtn.innerHTML = '<span style="animation: spin 1s linear infinite;">⏳</span> Recording your income...';
                    submitBtn.style.background = 'linear-gradient(45deg, #4caf50, #45a049)';
                    submitBtn.disabled = true;
                    
                    // Show success animation
                    progressSteps.forEach(step => step.classList.add('active'));
                }
            });
            
            function validateInput(input) {
                const value = input.value.trim();
                let isValid = true;
                
                // Remove previous error styling
                input.style.borderColor = '';
                input.style.background = '';
                
                if (input.type === 'number') {
                    if (!value || parseFloat(value) <= 0) {
                        showInputError(input, 'Please enter a valid amount');
                        isValid = false;
                    }
                } else if (input.type === 'date') {
                    if (!value) {
                        showInputError(input, 'Please select when you received this income');
                        isValid = false;
                    } else {
                        // Check if date is not in the future
                        const selectedDate = new Date(value);
                        const today = new Date();
                        today.setHours(23, 59, 59, 999);
                        
                        if (selectedDate > today) {
                            showInputError(input, 'Date cannot be in the future');
                            isValid = false;
                        }
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
                input.style.borderColor = '#ff6b6b';
                input.style.background = 'rgba(255, 107, 107, 0.05)';
                input.style.animation = 'shake 0.5s ease-in-out';
                
                setTimeout(() => {
                    input.style.animation = '';
                }, 500);
            }
            
            function showError(message) {
                showTempMessage(message, 'error');
            }
            
            function showTempMessage(message, type) {
                // Remove existing temp messages
                const existingMsg = document.querySelector('.temp-msg');
                if (existingMsg) existingMsg.remove();
                
                const messageDiv = document.createElement('div');
                messageDiv.className = `msg ${type} temp-msg`;
                messageDiv.textContent = message;
                messageDiv.style.animation = 'slideInUp 0.5s ease-in-out';
                
                form.insertBefore(messageDiv, submitBtn);
                
                setTimeout(() => {
                    if (messageDiv.parentNode) {
                        messageDiv.style.animation = 'fadeInUp 0.3s ease-in-out reverse';
                        setTimeout(() => messageDiv.remove(), 300);
                    }
                }, 2500);
            }
            
            // Auto-hide success message after 4 seconds
            const successMessage = document.querySelector('.msg.success:not(.temp-msg)');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.opacity = '0';
                    setTimeout(() => {
                        successMessage.style.display = 'none';
                    }, 300);
                }, 4000);
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
	<script>
const amountInput = document.getElementById('amount');

/* Block dot, comma, e, +, - */
amountInput.addEventListener('keydown', function (e) {
    if (['.', ',', 'e', '+', '-'].includes(e.key)) {
        e.preventDefault();
    }
});

/* Clean pasted values */
amountInput.addEventListener('input', function () {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

</body>
</html>