<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
include 'db/config.php';
$user_id = $_SESSION["user_id"];

$message = "";
$success = false;

// Handle savings goal creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "create") {
    $title = $conn->real_escape_string($_POST["title"]);
    $target_amount = floatval($_POST["target_amount"]);
    $current_amount = floatval($_POST["current_amount"]);
    $target_date = $conn->real_escape_string($_POST["target_date"]);
    
    if ($title && $target_amount > 0) {
        $sql = "INSERT INTO savings_goals (user_id, title, target_amount, current_amount, target_date) 
                VALUES ('$user_id', '$title', '$target_amount', '$current_amount', '$target_date')";
        
        if ($conn->query($sql)) {
            $message = "🎯 Savings goal created successfully!";
            $success = true;
        } else {
            $message = "Error creating savings goal. Please try again.";
            $success = false;
        }
    } else {
        $message = "Please fill in all required fields.";
        $success = false;
    }
}

// Handle updating goal progress
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "update") {
    $goal_id = intval($_POST["goal_id"]);
    $new_amount = floatval($_POST["new_amount"]);
    
    // Verify goal belongs to user
    $check_sql = "SELECT id FROM savings_goals WHERE id='$goal_id' AND user_id='$user_id'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        $sql = "UPDATE savings_goals SET current_amount='$new_amount' WHERE id='$goal_id'";
        
        if ($conn->query($sql)) {
            // Check if goal is completed
            $check_complete_sql = "SELECT target_amount, current_amount FROM savings_goals WHERE id='$goal_id'";
            $complete_result = $conn->query($check_complete_sql);
            
            if ($complete_result && $row = $complete_result->fetch_assoc()) {
                if ($row['current_amount'] >= $row['target_amount']) {
                    // Mark goal as completed
                    $conn->query("UPDATE savings_goals SET status='completed' WHERE id='$goal_id'");
                    $message = "🎉 Congratulations! You've reached your savings goal!";
                } else {
                    $message = "💰 Progress updated successfully!";
                }
                $success = true;
            }
        } else {
            $message = "Error updating progress. Please try again.";
            $success = false;
        }
    } else {
        $message = "Invalid goal selected.";
        $success = false;
    }
}

// Handle goal deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "delete") {
    $goal_id = intval($_POST["goal_id"]);
    
    // Verify goal belongs to user
    $check_sql = "SELECT id FROM savings_goals WHERE id='$goal_id' AND user_id='$user_id'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        $sql = "DELETE FROM savings_goals WHERE id='$goal_id'";
        
        if ($conn->query($sql)) {
            $message = "Goal deleted successfully.";
            $success = true;
        } else {
            $message = "Error deleting goal. Please try again.";
            $success = false;
        }
    } else {
        $message = "Invalid goal selected.";
        $success = false;
    }
}

// Fetch user's savings goals
$goals_result = $conn->query("SELECT * FROM savings_goals WHERE user_id='$user_id' ORDER BY status, target_date");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings Goals | Expense Tracker</title>
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
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.25);
            padding: 40px;
            animation: slideInUp 0.8s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #4facfe);
            animation: shimmer 3s ease-in-out infinite;
        }

        h1 {
            text-align: center;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .subtitle {
            text-align: center;
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 40px;
        }

        .savings-form {
            background: #f8fafc;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .form-title {
            font-size: 1.4rem;
            color: #4a5568;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            font-size: 1rem;
            background: white;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        button {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 14px 25px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
            max-width: 200px;
            margin: 0 auto;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .message {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            font-weight: 500;
        }

        .message.success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .message.error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .goals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .goal-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
            position: relative;
        }

        .goal-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .goal-card.completed {
            border-left: 5px solid #10b981;
        }

        .goal-card.active {
            border-left: 5px solid #3b82f6;
        }

        .goal-status {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 0.8rem;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .goal-status.completed {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .goal-status.active {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .goal-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 15px;
            padding-right: 70px;
        }

        .goal-amount {
            font-size: 1.8rem;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 5px;
        }

        .goal-progress {
            color: #718096;
            margin-bottom: 15px;
        }

        .progress-bar {
            height: 10px;
            background: #e2e8f0;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 0.5s ease;
            background: linear-gradient(45deg, #3b82f6, #10b981);
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            color: #718096;
            font-size: 0.9rem;
        }

        .goal-date {
            margin-top: 15px;
            color: #718096;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .goal-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .goal-btn {
            background: none;
            border: none;
            color: #667eea;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
        }

        .goal-btn:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateY(0);
            box-shadow: none;
        }

        .goal-btn.delete {
            color: #ef4444;
        }

        .goal-btn.delete:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        .update-form {
            display: none;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .update-form.active {
            display: block;
        }

        .update-form input {
            width: calc(100% - 100px);
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
            margin-right: 10px;
        }

        .update-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .update-btn:hover {
            background: #2563eb;
        }

        .actions {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary {
            background: #f8fafc;
            color: #667eea;
            border: 2px solid #e2e8f0;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: #f8fafc;
            border-radius: 16px;
            margin-top: 30px;
        }

        .empty-icon {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 15px;
        }

        .empty-title {
            font-size: 1.3rem;
            color: #4a5568;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .empty-message {
            color: #718096;
            margin-bottom: 20px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
            animation: fadeIn 0.3s ease-out;
        }

        .modal-title {
            font-size: 1.5rem;
            color: #4a5568;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .modal-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .modal-btn.cancel {
            background: #f1f5f9;
            color: #64748b;
        }

        .modal-btn.confirm {
            background: #ef4444;
            color: white;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
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

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                margin: 20px 10px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .savings-form {
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .goals-grid {
                grid-template-columns: 1fr;
            }

            .goal-actions {
                flex-direction: column;
                gap: 10px;
            }

            .goal-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-piggy-bank" style="margin-right: 15px;"></i>Savings Goals</h1>
        <p class="subtitle">Set targets and watch your savings grow towards your dreams</p>
        
        <?php if($message): ?>
            <div class="message <?= $success ? 'success' : 'error' ?>"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="savings-form">
            <h2 class="form-title"><i class="fas fa-plus-circle"></i> Create New Savings Goal</h2>
            <form method="post" action="">
                <input type="hidden" name="action" value="create">
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Goal Title</label>
                        <input type="text" name="title" id="title" required placeholder="e.g., New Laptop, Vacation, Emergency Fund">
                    </div>
                    <div class="form-group">
                        <label for="target_amount">Target Amount</label>
                        <input type="number" name="target_amount" id="target_amount" step="0.01" min="0" required placeholder="How much do you want to save?">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="current_amount">Current Amount (if any)</label>
                        <input type="number" name="current_amount" id="current_amount" step="0.01" min="0" value="0" placeholder="How much have you saved so far?">
                    </div>
                    <div class="form-group">
                        <label for="target_date">Target Date (optional)</label>
                        <input type="date" name="target_date" id="target_date">
                    </div>
                </div>
                <button type="submit">Create Goal</button>
            </form>
        </div>
        
        <h2 class="form-title"><i class="fas fa-list-check"></i> Your Savings Goals</h2>
        
        <?php if ($goals_result && $goals_result->num_rows > 0): ?>
            <div class="goals-grid">
                <?php while($goal = $goals_result->fetch_assoc()): 
                    $percentage = $goal['target_amount'] > 0 ? ($goal['current_amount'] / $goal['target_amount']) * 100 : 0;
                    $percentage = min(100, $percentage); // Cap at 100%
                    $is_completed = $goal['status'] == 'completed';
                ?>
                    <div class="goal-card <?= $is_completed ? 'completed' : 'active' ?>">
                        <div class="goal-status <?= $is_completed ? 'completed' : 'active' ?>">
                            <?= $is_completed ? 'Completed' : 'In Progress' ?>
                        </div>
                        <h3 class="goal-title"><?= htmlspecialchars($goal['title']) ?></h3>
                        <div class="goal-amount">RWF <?= number_format($goal['target_amount'], 2) ?></div>
                        <div class="goal-progress">Current: RWF <?= number_format($goal['current_amount'], 2) ?></div>
                        
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $percentage ?>%;"></div>
                        </div>
                        <div class="progress-text">
                            <div><?= round($percentage) ?>% saved</div>
                            <div>RWF <?= number_format($goal['target_amount'] - $goal['current_amount'], 2) ?> to go</div>
                        </div>
                        
                        <?php if($goal['target_date']): ?>
                            <div class="goal-date">
                                <i class="fas fa-calendar"></i> Target: <?= date('M j, Y', strtotime($goal['target_date'])) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="goal-actions">
                            <?php if(!$is_completed): ?>
                                <button class="goal-btn update-trigger" data-goal-id="<?= $goal['id'] ?>">
                                    <i class="fas fa-coins"></i> Update Progress
                                </button>
                            <?php else: ?>
                                <button class="goal-btn" disabled>
                                    <i class="fas fa-check-circle"></i> Goal Achieved
                                </button>
                            <?php endif; ?>
                            <button class="goal-btn delete" data-goal-id="<?= $goal['id'] ?>" data-goal-title="<?= htmlspecialchars($goal['title']) ?>">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                        
                        <div class="update-form" id="update-form-<?= $goal['id'] ?>">
                            <form method="post" action="">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="goal_id" value="<?= $goal['id'] ?>">
                                <input type="number" name="new_amount" step="0.01" min="0" value="<?= $goal['current_amount'] ?>" required>
                                <button type="submit" class="update-btn">Save</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-piggy-bank"></i></div>
                <div class="empty-title">No savings goals yet</div>
                <div class="empty-message">Create your first savings goal above to start tracking your progress</div>
            </div>
        <?php endif; ?>
        
        <div class="actions">
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="budget.php" class="btn btn-secondary">
                <i class="fas fa-bullseye"></i> Budget Manager
            </a>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <h3 class="modal-title">Delete Savings Goal</h3>
            <p>Are you sure you want to delete "<span id="goalTitle"></span>"? This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="modal-btn cancel" id="cancelDelete">Cancel</button>
                <form method="post" action="" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="goal_id" id="deleteGoalId">
                    <button type="submit" class="modal-btn confirm">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update progress form toggle
            const updateTriggers = document.querySelectorAll('.update-trigger');
            updateTriggers.forEach(trigger => {
                trigger.addEventListener('click', function() {
                    const goalId = this.getAttribute('data-goal-id');
                    const form = document.getElementById(`update-form-${goalId}`);
                    form.classList.toggle('active');
                });
            });
            
            // Delete confirmation modal
            const deleteButtons = document.querySelectorAll('.goal-btn.delete');
            const deleteModal = document.getElementById('deleteModal');
            const cancelDelete = document.getElementById('cancelDelete');
            const deleteForm = document.getElementById('deleteForm');
            const deleteGoalId = document.getElementById('deleteGoalId');
            const goalTitle = document.getElementById('goalTitle');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const goalId = this.getAttribute('data-goal-id');
                    const title = this.getAttribute('data-goal-title');
                    
                    deleteGoalId.value = goalId;
                    goalTitle.textContent = title;
                    deleteModal.classList.add('active');
                });
            });
            
            cancelDelete.addEventListener('click', function() {
                deleteModal.classList.remove('active');
            });
            
            // Close modal when clicking outside
            deleteModal.addEventListener('click', function(e) {
                if (e.target === deleteModal) {
                    deleteModal.classList.remove('active');
                }
            });
            
            // Animate progress bars on load
            const progressBars = document.querySelectorAll('.progress-fill');
            setTimeout(() => {
                progressBars.forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 100);
                });
            }, 300);
        });
    </script>
</body>
</html> 