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
// Handle inline update
if(isset($_POST['ajax_update'])) {
    $category = $conn->real_escape_string($_POST['category']);
    $amount = intval($_POST['amount']);

    $month = intval($_POST['month']);
    $year = intval($_POST['year']);
    
    $sql = "UPDATE budgets SET amount='$amount' WHERE user_id='$user_id' AND category='$category' AND month='$month' AND year='$year'";
    $conn->query($sql);
    exit; // stop further output
}

// Handle inline delete
if(isset($_POST['ajax_delete'])) {
    $category = $conn->real_escape_string($_POST['category']);
    $month = intval($_POST['month']);
    $year = intval($_POST['year']);

    $sql = "DELETE FROM budgets WHERE user_id='$user_id' AND category='$category' AND month='$month' AND year='$year'";
    $conn->query($sql);
    exit;
}

// Handle budget creation/update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $conn->real_escape_string($_POST["category"]);
    $amount = floatval($_POST["amount"]);
    $month = intval($_POST["month"]);
    $year = intval($_POST["year"]);
    
    if ($amount > 0 && $category && $month && $year) {
        // Check if budget already exists for this category/month/year
        $check_sql = "SELECT id FROM budgets WHERE user_id='$user_id' AND category='$category' AND month='$month' AND year='$year'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            // Update existing budget
            $budget_id = $check_result->fetch_assoc()['id'];
            $sql = "UPDATE budgets SET amount='$amount' WHERE id='$budget_id'";
            if ($conn->query($sql)) {
                $message = "🎯 Budget updated successfully!";
                $success = true;
            } else {
                $message = "Error updating budget. Please try again.";
                $success = false;
            }
        } else {
            // Create new budget
            $sql = "INSERT INTO budgets (user_id, category, amount, month, year) VALUES ('$user_id', '$category', '$amount', '$month', '$year')";
            if ($conn->query($sql)) {
                $message = "🎯 Budget set successfully!";
                $success = true;
            } else {
                $message = "Error creating budget. Please try again.";
                $success = false;
            }
        }
    } else {
        $message = "Please fill in all required fields.";
        $success = false;
    }
}

// Get current month and year
$current_month = date('n');
$current_year = date('Y');

// Fetch categories from database
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name");

// Fetch existing budgets for current month/year
$budgets_result = $conn->query("SELECT * FROM budgets WHERE user_id='$user_id' AND year='$current_year'");
$budgets = [];
if ($budgets_result) {
    while ($row = $budgets_result->fetch_assoc()) {
        $budgets[$row['category']] = $row;
    }
}

// Calculate spending for current month by category
$spending_result = $conn->query("SELECT category, SUM(amount) as total FROM expenses 
                                WHERE user_id='$user_id' AND MONTH(date)='$current_month' AND YEAR(date)='$current_year' 
                                GROUP BY category");
$spending = [];
if ($spending_result) {
    while ($row = $spending_result->fetch_assoc()) {
        $spending[$row['category']] = $row['total'];
    }
}
?>
<?php
$currentmonthRows = [];

$sql = "
/* Budgets (with or without expenses) */
SELECT 
    b.category,
    b.amount AS budget,
    IFNULL(SUM(e.amount),0) AS spent
FROM budgets b
LEFT JOIN expenses e
    ON b.user_id = e.user_id
    AND b.category = e.category
    AND MONTH(e.date) = '$current_month'
    AND YEAR(e.date) = '$current_year'
WHERE b.user_id='$user_id'
AND b.month='$current_month'
AND b.year='$current_year'
GROUP BY b.category

UNION ALL

/* Expenses without budgets */
SELECT 
    e.category,
    0 AS budget,
    SUM(e.amount) AS spent
FROM expenses e
LEFT JOIN budgets b
    ON b.user_id = e.user_id
    AND b.category = e.category
    AND b.month = '$current_month'
    AND b.year = '$current_year'
WHERE e.user_id='$user_id'
AND MONTH(e.date)='$current_month'
AND YEAR(e.date)='$current_year'
AND b.id IS NULL
GROUP BY e.category

ORDER BY category
";


$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $currentmonthRows[] = $row;
    }
}
/* ===== UPCOMING BUDGETS (GROUPED) ===== */
$upcomingGrouped = [];

$up_sql = "
SELECT category, amount, month, year
FROM budgets
WHERE user_id='$user_id'
AND (year > '$current_year'
     OR (year='$current_year' AND month > '$current_month'))
ORDER BY year, month
";

$up_res = $conn->query($up_sql);
if ($up_res) {
    while ($row = $up_res->fetch_assoc()) {
        $key = $row['year'].'-'.$row['month'];
        $upcomingGrouped[$key][] = $row;
    }
}

/* ===== PREVIOUS BUDGETS (GROUPED) ===== */
$previousGrouped = [];

$prev_sql = "
/* Budgets with or without expenses */
SELECT 
    b.category,
    b.amount AS budget,
    b.month,
    b.year,
    IFNULL(SUM(e.amount),0) AS spent
FROM budgets b
LEFT JOIN expenses e
    ON b.user_id = e.user_id
    AND b.category = e.category
    AND MONTH(e.date) = b.month
    AND YEAR(e.date) = b.year
WHERE b.user_id='$user_id'
AND (b.year < '$current_year'
     OR (b.year='$current_year' AND b.month < '$current_month'))
GROUP BY b.category, b.month, b.year

UNION ALL

/* Expenses without budgets */
SELECT
    e.category,
    0 AS budget,
    MONTH(e.date) AS month,
    YEAR(e.date) AS year,
    SUM(e.amount) AS spent
FROM expenses e
LEFT JOIN budgets b
    ON b.user_id = e.user_id
    AND b.category = e.category
    AND b.month = MONTH(e.date)
    AND b.year = YEAR(e.date)
WHERE e.user_id='$user_id'
AND (YEAR(e.date) < '$current_year'
     OR (YEAR(e.date)='$current_year' AND MONTH(e.date) < '$current_month'))
AND b.id IS NULL
GROUP BY e.category, YEAR(e.date), MONTH(e.date)

ORDER BY year DESC, month DESC
";


$prev_res = $conn->query($prev_sql);
if ($prev_res) {
    while ($row = $prev_res->fetch_assoc()) {
        $key = $row['year'].'-'.$row['month'];
        $previousGrouped[$key][] = $row;
    }
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Manager | Expense Tracker</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            padding: 20px;
        }

        .container {
            max-width: 1175px;
			width:100%;
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

       

        h1 {
            text-align: center;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            font-size: 2.5rem;
            font-weight: 700;
			color: #1a4d8f; 
        }

        .subtitle {
            text-align: center;
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 40px;
			color: #1a4d8f; 
        }

        .budget-form {
            background: #f8fafc;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .form-title {
            font-size: 1.4rem;
            color: #1a4d8f; 
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

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            font-size: 1rem;
            background: white;
            transition: all 0.3s ease;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        button {
            background: #cce4ff;
            color: #1a4d8f;
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

        .budget-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .budget-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
            position: relative;
        }

        .budget-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .budget-category {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .category-icon {
            font-size: 1.8rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        .category-name {
            font-weight: 600;
            color: #4a5568;
            font-size: 1.2rem;
        }

        .budget-amount {
            font-size: 1.8rem;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 5px;
        }

        .budget-spent {
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
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            color: #718096;
            font-size: 0.9rem;
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

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                margin: 20px 10px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .budget-form {
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .budget-grid {
                grid-template-columns: 1fr;
			}
            
        }
		table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{padding:14px;border-bottom:1px solid #eee}
thead{background: #cce4ff; /* match page background */
    color: #0d1a40;}
.accordion{
    margin-top:20px;
    border:1px solid #ddd;
    border-radius:10px;
}

.accordion-header{
    padding:15px;
    cursor:pointer;
    background:#f0fdf4;
    font-weight:600;
    display:flex;
    justify-content:space-between;
}

.accordion-body{
    display:none;
    padding:15px;
}
.overspent {
    background-color: #ffe5e5; /* light bright red background */
    color: #d32f2f; /* bright red text */
}
.totals-box {
    display: flex;
    justify-content: flex-end;
    gap: 20px;
    margin-top: 10px;
}

.totals-box div {
    padding: 10px 20px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #ddd;
    font-weight: 600;
}

.totals-box .spent {
    color: #e53e3e;
}

.totals-box .remaining {
    color: #38a169;
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
/* =========================
   GLOBAL PRIMARY TEXT COLOR
   ========================= */
:root {
    --primary-blue: #1a4d8f;
}

/* ===== Budget Manager Main Heading ===== */
h1,
h1 i {
    color: var(--primary-blue) !important;
    -webkit-text-fill-color: var(--primary-blue) !important;
    background: none !important;
}

/* ===== Section Titles ===== */
.form-title,
.form-title i {
    color: var(--primary-blue) !important;
}

/* ===== Table Headers ===== */
thead th {
    color: var(--primary-blue) !important;
}

/* ===== Table Content (except amounts) ===== */
tbody td {
    color: var(--primary-blue);
}

/* Keep spent & remaining colors */
td[style*="#e53e3e"],
td[style*="#38a169"] {
    color: inherit !important;
}

/* ===== Accordion Headers ===== */
.accordion-header {
    color: var(--primary-blue) !important;
}

/* ===== Totals (Bottom Summary Boxes) ===== */
.totals-box div,
div[style*="Total Budget"],
div[style*="Total Spent"],
div[style*="Remaining"] {
    color: var(--primary-blue) !important;
}

/* ===== Inline totals under tables ===== */
div[style*="Total Budget:"] {
    color: var(--primary-blue) !important;
}

/* ===== Dashboard Button ===== */
.btn-back {
    color: var(--primary-blue) !important;
    border-color: var(--primary-blue) !important;
}

.btn-back:hover {
    background: #e6f0fb;
}

/* ===== Icons (edit / chart / calendar etc.) ===== */
i.fas {
    color: var(--primary-blue);
}

/* Keep delete icon red */
.delete-icon {
    color: #e53e3e !important;
}

    </style>
</head>
<body>
    <div class="container">
	<a href="dashboard.php" class="btn-back">← Dashboard</a>
        <h1><i class="fas fa-bullseye" style="margin-right: 15px;"></i>Budget Manager</h1>
        <p class="subtitle">Set and track your spending limits for better financial control</p>
        
        <?php if($message): ?>
            <div class="message <?= $success ? 'success' : 'error' ?>"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="budget-form">
            <h2 class="form-title"><i class="fas fa-plus-circle"></i> Create Budget</h2>
            <form method="post" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select name="category" id="category" required>
                            <option value="">Select Category</option>
                            <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                                <?php while($cat = $categories_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($cat['name']) ?>"><?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?></option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="Food & Dining">🍕 Food & Dining</option>
                                <option value="Transport">🚗 Transport</option>
                                <option value="Shopping">🛒 Shopping</option>
                                <option value="Bills & Utilities">📄 Bills & Utilities</option>
                                <option value="Health & Care">⚕️ Health & Care</option>
                                <option value="Entertainment">🎬 Entertainment</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amount">Budget Amount</label>
                        <input
    type="number"
    name="amount"
    id="amount"
    min="1"
    step="1"
    required
    placeholder="Enter amount"
    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
/>

                    </div>
                </div>
<div class="form-row">
    <div class="form-group">
        <label for="month">Month</label>
        <select name="month" id="month" required>
            <?php
            $months = [
                1 => "January", 2 => "February", 3 => "March", 4 => "April",
                5 => "May", 6 => "June", 7 => "July", 8 => "August",
                9 => "September", 10 => "October", 11 => "November", 12 => "December"
            ];

            // Determine starting month
            $startMonth = $current_month;
            foreach ($months as $num => $name) {
                if ($num >= $startMonth) {
                    $selected = ($num == $current_month) ? 'selected' : '';
                    echo "<option value='$num' $selected>$name</option>";
                }
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <label for="year">Year</label>
        <select name="year" id="year" required>
            <?php
            for ($y = $current_year; $y <= $current_year + 1; $y++) {
                $selected = ($y == $current_year) ? 'selected' : '';
                echo "<option value='$y' $selected>$y</option>";
            }
            ?>
        </select>
    </div>
</div>

                <button type="submit">Save Budget</button>
            </form>
        </div>
        <div class="budget-form">
        <h2 class="form-title"><i class="fas fa-chart-pie"></i> Your Current Budgets (<?= date('F Y', strtotime("$current_year-$current_month-01")) ?>)</h2>
		<?php
$total_budget = 0;
$total_spent = 0;
$total_remaining = 0;
?>

		<table>
<thead>
<tr>
<th>#</th>
<th>Category</th>
<th>Budget</th>
<th>Spent</th>
<th>Remaining</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if($currentmonthRows): ?>
<?php
$total_budget = 0;
$total_spent = 0;
$total_remaining = 0;

if($currentmonthRows) {
    foreach($currentmonthRows as $r) {
        $total_budget += $r['budget'];
        $total_spent += $r['spent'];
        $total_remaining += ($r['budget'] - $r['spent']);
    }
}
?>

<?php $i=1; foreach($currentmonthRows as $r): ?>
<tr 
    data-category="<?= htmlspecialchars($r['category']) ?>"
    class="<?= ($r['budget'] - $r['spent']) < 0 ? 'overspent' : '' ?>"
>


    <td><?= $i++ ?></td>
    <td><?= htmlspecialchars($r['category']) ?></td>
    <td class="editable budget-amount" data-value="<?= $r['budget'] ?>">₹ <?= number_format($r['budget'],0) ?></td>
    <td style="color:#e53e3e;">₹ <?= number_format($r['spent'],0) ?></td>
    <td style="color:#38a169;">₹ <?= number_format($r['budget'] - $r['spent'],0) ?></td>
  <td>
<?php if($r['budget'] > 0): ?>
    <i class="fas fa-edit edit-icon"
       style="color:#4a90e2;cursor:pointer;"
       title="Edit"></i>

    <i class="fas fa-trash delete-icon"
       style="color:#e53e3e;cursor:pointer;margin-left:10px;"
       title="Delete"></i>
<?php else: ?>
    -
<?php endif; ?>
</td>

</tr>
<?php endforeach; ?>

<?php else: ?>
<tr>
<td colspan="6">No budgets found.</td>
</tr>
<?php endif; ?>
</tbody>

</table>
<div style="display:flex; justify-content:flex-end; margin-top:10px; gap:20px;">
    <div style="padding:10px 20px; background:#f8fafc; border-radius:10px; border:1px solid #ddd; font-weight:600;">
        Total Budget: ₹ <?= number_format($total_budget,0) ?>
    </div>
    <div style="padding:10px 20px; background:#f8fafc; border-radius:10px; border:1px solid #ddd; font-weight:600; color:#e53e3e;">
        Total Spent: ₹ <?= number_format($total_spent,0) ?>
    </div>
    <div style="padding:10px 20px; background:#f8fafc; border-radius:10px; border:1px solid #ddd; font-weight:600; color:#38a169;">
        Remaining: ₹ <?= number_format($total_remaining,0) ?>
    </div>
</div>

</div>
<div class="budget-form">
<h2 class="form-title">
    <i class="fas fa-calendar-plus"></i> Upcoming Budgets
</h2>

<?php if(empty($upcomingGrouped)): ?>
    <p>No upcoming budgets.</p>
<?php else: ?>
    <?php foreach($upcomingGrouped as $monthYear => $rows): ?>
<div class="accordion">

    <div class="accordion-header"
         onclick="toggleSection('up_<?= $monthYear ?>')">
        <?= date('F Y', strtotime($monthYear.'-01')) ?>
        <i class="fas fa-chevron-down"></i>
    </div>

    <div class="accordion-body" id="up_<?= $monthYear ?>">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Budget Amount</th>
					<th>Actions</th>
                </tr>
            </thead>
            <tbody>
<?php $i=1; foreach($rows as $r): ?>
<tr data-category="<?= htmlspecialchars($r['category']) ?>" data-month="<?= $r['month'] ?>" data-year="<?= $r['year'] ?>">
    <td><?= $i++ ?></td>
    <td><?= htmlspecialchars($r['category']) ?></td>
    <td class="editable budget-amount" data-value="<?= $r['amount'] ?>">₹ <?= number_format($r['amount'],0) ?></td>
    <td>
        <i class="fas fa-edit edit-icon" style="color:#4a90e2;cursor:pointer;" title="Edit"></i>
        <i class="fas fa-trash delete-icon" style="color:#e53e3e;cursor:pointer;margin-left:10px;" title="Delete"></i>
    </td>
</tr>
<?php endforeach; ?>

</tbody>

        </table>
    </div>

</div>
<?php endforeach; ?>

<?php endif; ?>
</div>
<div class="budget-form">
<h2 class="form-title">
    <i class="fas fa-calendar-minus"></i> Previous Budgets
</h2>

<?php if(empty($previousGrouped)): ?>
    <p>No previous budgets.</p>
<?php else: ?>
    <?php foreach($previousGrouped as $monthYear => $rows): ?>
	<?php
$total_budget_prev = 0;
$total_spent_prev = 0;
$total_remaining_prev = 0;

foreach($rows as $r) {
    $budget = $r['budget'];
    $spent = $r['spent'];
    $remaining = $budget - $spent;

    $total_budget_prev += $budget;
    $total_spent_prev += $spent;
    $total_remaining_prev += $remaining;
}
?>

<div class="accordion">

    <div class="accordion-header"
         onclick="toggleSection('prev_<?= $monthYear ?>')">
        <?= date('F Y', strtotime($monthYear.'-01')) ?>
        <i class="fas fa-chevron-down"></i>
    </div>

    <div class="accordion-body" id="prev_<?= $monthYear ?>" style="display:none;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Budget Amount</th>
                    <th>Spent</th>
                    <th>Remaining</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; foreach($rows as $r): 
                   $budget = $r['budget'];
$spent = $r['spent'];
$remaining = $budget - $spent;

                ?>
                <tr class="<?= ($remaining < 0) ? 'overspent' : '' ?>">


                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($r['category']) ?></td>
                    <td>₹ <?= number_format($budget,0) ?></td>

                    <td style="color:#e53e3e;">₹ <?= number_format($spent,0) ?></td>
                    <td style="color:#38a169;">₹ <?= number_format($remaining,0) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
		<div style="display:flex; justify-content:flex-end; margin-top:10px; gap:20px; color: #1a4d8f;">
    <div style="padding:10px 20px; background:#f8fafc; border-radius:10px; border:1px solid #ddd; font-weight:600;color: #1a4d8f;">
        Total Budget: ₹ <?= number_format($total_budget_prev,0) ?>
    </div>
    <div style="padding:10px 20px; background:#f8fafc; border-radius:10px; border:1px solid #ddd; font-weight:600; color: #1a4d8f;">
        Total Spent: ₹ <?= number_format($total_spent_prev,0) ?>
    </div>
    <div style="padding:10px 20px; background:#f8fafc; border-radius:10px; border:1px solid #ddd; font-weight:600; color: #1a4d8f;">
        Remaining: ₹ <?= number_format($total_remaining_prev,0) ?>
    </div>
</div>

    </div>

</div>
    <?php endforeach; ?>
<?php endif; ?>


</div>






        

        
        <div class="budget-form">
    <h2 class="form-title"><i class="fas fa-chart-bar"></i> Budget Overview (<?= date('F Y', strtotime("$current_year-$current_month-01")) ?>)</h2>
    <canvas id="budgetChart" style="width:100%; max-width:800px; height:400px;"></canvas>
</div>


    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-fill form when editing an existing budget
            const categorySelect = document.getElementById('category');
            categorySelect.addEventListener('change', function() {
                const selectedCategory = this.value;
                const budgets = <?= json_encode($budgets) ?>;
                
                if (budgets[selectedCategory]) {
                    document.getElementById('amount').value = budgets[selectedCategory].amount;
                } else {
                    document.getElementById('amount').value = '';
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
	<script>
function toggleSection(id) {
    const el = document.getElementById(id);
    el.style.display = (el.style.display === "none") ? "block" : "none";
}
</script>
<script>
// Adjust months dynamically when year changes
const yearSelect = document.getElementById('year');
const monthSelect = document.getElementById('month');

const months = [
    '', 'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

yearSelect.addEventListener('change', function() {
    const selectedYear = parseInt(this.value);
    const currentYear = <?= $current_year ?>;
    const currentMonth = <?= $current_month ?>;

    monthSelect.innerHTML = ''; // Clear existing options

    let startMonth = (selectedYear === currentYear) ? currentMonth : 1;

    for (let m = startMonth; m <= 12; m++) {
        const option = document.createElement('option');
        option.value = m;
        option.text = months[m];
        monthSelect.appendChild(option);
    }
});
</script>
<script>
function editBudget(type, id) {
    const row = document.getElementById(id);
    const budgetCell = row.querySelector('.budget');
    const budgetText = budgetCell.textContent.replace('₹','').trim();

    // Replace budget with input field
    budgetCell.innerHTML = `<input type="number" value="${budgetText}" style="width:100px"> 
                            <button onclick="saveBudget('${type}','${id}')">Save</button>
                            <button onclick="cancelEdit('${type}','${id}','${budgetText}')">Cancel</button>`;
}

function saveBudget(type, id) {
    const row = document.getElementById(id);
    const input = row.querySelector('input');
    const newAmount = input.value;

    if(newAmount <= 0) { alert("Enter valid amount"); return; }

    // Send AJAX request to update budget
    fetch('budget_action.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `action=update&type=${type}&id=${id}&amount=${newAmount}`
    })
    .then(res => res.text())
    .then(data => {
        alert(data);
        location.reload(); // reload page after update
    });
}

function cancelEdit(type,id,oldValue) {
    const row = document.getElementById(id);
    const budgetCell = row.querySelector('.budget');
    budgetCell.textContent = '₹ ' + oldValue;
}

function deleteBudget(type, id) {
    if(confirm("Are you sure you want to delete this budget?")) {
        fetch('budget_action.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `action=delete&type=${type}&id=${id}`
        })
        .then(res => res.text())
        .then(data => {
            alert(data);
            location.reload();
        });
    }
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inline editing
    document.querySelectorAll('.edit-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            const td = this.closest('tr').querySelector('.editable');
            const original = td.dataset.value;
            td.innerHTML = `
<input 
    type="number"
    value="${parseInt(original)}"
    step="1"
    min="1"
    inputmode="numeric"
    onkeydown="return event.key !== '.'"
    oninput="this.value=this.value.replace(/[^0-9]/g,'')"
    style="width:80px;"
/>
<i class="fas fa-check" style="color:#10b981;cursor:pointer;margin-left:5px;"></i>
`;

            
            // Save on click
            td.querySelector('.fa-check').addEventListener('click', function() {
                const newVal = td.querySelector('input').value;
                const tr = td.closest('tr');
                const category = tr.dataset.category;
                const month = tr.dataset.month || <?= $current_month ?>;
                const year = tr.dataset.year || <?= $current_year ?>;

                // AJAX request to update
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `ajax_update=1&category=${encodeURIComponent(category)}&amount=${newVal}&month=${month}&year=${year}`
                })
                .then(res => res.text())
                .then(data => {
                    td.dataset.value = newVal;
                    const cleanVal = parseInt(newVal);
td.dataset.value = cleanVal;
td.innerHTML = `₹ ${cleanVal}`;

                });
            });
        });
    });

    // Delete
    document.querySelectorAll('.delete-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            if(!confirm('Delete this budget?')) return;

            const tr = this.closest('tr');
            const category = tr.dataset.category;
            const month = tr.dataset.month || <?= $current_month ?>;
            const year = tr.dataset.year || <?= $current_year ?>;

            // AJAX request to delete
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `ajax_delete=1&category=${encodeURIComponent(category)}&month=${month}&year=${year}`
            })
            .then(res => res.text())
            .then(data => {
                tr.remove();
            });
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare data for chart
    const categories = <?= json_encode(array_map(fn($r) => $r['category'], $currentmonthRows)) ?>;
    const budgetAmounts = <?= json_encode(array_map(fn($r) => floatval($r['budget']), $currentmonthRows)) ?>;
    const spentAmounts = <?= json_encode(array_map(fn($r) => floatval($r['spent']), $currentmonthRows)) ?>;

    const ctx = document.getElementById('budgetChart').getContext('2d');

    const budgetChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categories,
            datasets: [
                {
                    label: 'Budget',
                    data: budgetAmounts,
                    backgroundColor: 'rgba(102, 126, 234, 0.6)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Spent',
                    data: spentAmounts,
                    backgroundColor: 'rgba(229, 62, 62, 0.6)',
                    borderColor: 'rgba(229, 62, 62, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categories = <?= json_encode(array_map(fn($r) => $r['category'], $currentmonthRows)) ?>;
    const spentAmounts = <?= json_encode(array_map(fn($r) => floatval($r['spent']), $currentmonthRows)) ?>;

    const ctxPie = document.getElementById('spendingPie').getContext('2d');

    const pieChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: categories,
            datasets: [{
                data: spentAmounts,
                backgroundColor: categories.map(_ => `hsl(${Math.random()*360}, 70%, 60%)`)
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
});
</script>

</body>
</html> 