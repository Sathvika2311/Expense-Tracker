<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'db/config.php';
$user_id = $_SESSION["user_id"];
/* ================= AVAILABLE YEARS (FROM HISTORY) ================= */
$yearSql = "
    SELECT DISTINCT YEAR(date) AS yr FROM income WHERE user_id='$user_id'
    UNION
    SELECT DISTINCT YEAR(date) AS yr FROM expenses WHERE user_id='$user_id'
    ORDER BY yr DESC
";
$yearResult = $conn->query($yearSql);

$availableYears = [];
if ($yearResult) {
    while ($y = $yearResult->fetch_assoc()) {
        $availableYears[] = $y['yr'];
    }
}

$currentMonth = date('m');
$currentYear  = date('Y');
$filterYear  = $_GET['year']  ?? '';
$filterMonth = $_GET['month'] ?? '';
$isFiltered = $filterYear || $filterMonth;
$filterMessage = '';

/* ================= FILTERED DATA ================= */
/* ================= FILTERED DATA ================= */
$resFiltered = null;

/* ❌ Month selected without Year */
if (!$filterYear && $filterMonth) {

    $filterMessage = "⚠️ Please select a year when filtering by month.";

}

/* ✅ Year + Month */
elseif ($filterYear && $filterMonth) {

    $sqlFiltered = "
        SELECT 'Income' AS type, source AS title, description, amount, date 
        FROM income 
        WHERE user_id='$user_id'
          AND YEAR(date)='$filterYear'
          AND MONTH(date)='$filterMonth'
        UNION ALL
        SELECT 'Expense' AS type, category AS title, description, amount, date 
        FROM expenses 
        WHERE user_id='$user_id'
          AND YEAR(date)='$filterYear'
          AND MONTH(date)='$filterMonth'
        ORDER BY date DESC
    ";
    $resFiltered = $conn->query($sqlFiltered);

}

/* ✅ Only Year */
elseif ($filterYear) {

    $sqlFiltered = "
        SELECT 'Income' AS type, source AS title, description, amount, date 
        FROM income 
        WHERE user_id='$user_id'
          AND YEAR(date)='$filterYear'
        UNION ALL
        SELECT 'Expense' AS type, category AS title, description, amount, date 
        FROM expenses 
        WHERE user_id='$user_id'
          AND YEAR(date)='$filterYear'
        ORDER BY date DESC
    ";
    $resFiltered = $conn->query($sqlFiltered);

}


/* ================= CURRENT MONTH ================= */
$sqlCurrentMonth = "
    SELECT 'Income' AS type, source AS title, description, amount, date 
    FROM income 
    WHERE user_id='$user_id'
      AND MONTH(date)='$currentMonth'
      AND YEAR(date)='$currentYear'
    UNION ALL
    SELECT 'Expense' AS type, category AS title, description, amount, date 
    FROM expenses 
    WHERE user_id='$user_id'
      AND MONTH(date)='$currentMonth'
      AND YEAR(date)='$currentYear'
    ORDER BY date DESC
";
$resMonth = $conn->query($sqlCurrentMonth);

$currentMonthRows = [];
$monthIncome = $monthExpense = 0;
while ($row = $resMonth->fetch_assoc()) {
    $currentMonthRows[] = $row;
    ($row['type']=='Income') ? $monthIncome += $row['amount'] : $monthExpense += $row['amount'];
}

/* ================= PREVIOUS MONTHS ================= */
$sqlPrevMonths = "
    SELECT 'Income' AS type, source AS title, description, amount, date 
    FROM income 
    WHERE user_id='$user_id'
      AND (YEAR(date) < '$currentYear'
      OR (YEAR(date)='$currentYear' AND MONTH(date) < '$currentMonth'))
    UNION ALL
    SELECT 'Expense' AS type, category AS title, description, amount, date 
    FROM expenses 
    WHERE user_id='$user_id'
      AND (YEAR(date) < '$currentYear'
      OR (YEAR(date)='$currentYear' AND MONTH(date) < '$currentMonth'))
    ORDER BY date DESC
";
$resPrevMonths = $conn->query($sqlPrevMonths);

$previousMonths = [];
while ($row = $resPrevMonths->fetch_assoc()) {
    $key = date('F Y', strtotime($row['date']));
    $previousMonths[$key][] = $row;
}

/* ================= CURRENT YEAR ================= */
$sqlCurrentYear = "
    SELECT 'Income' AS type, source AS title, description, amount, date 
    FROM income 
    WHERE user_id='$user_id' AND YEAR(date)='$currentYear'
    UNION ALL
    SELECT 'Expense' AS type, category AS title, description, amount, date 
    FROM expenses 
    WHERE user_id='$user_id' AND YEAR(date)='$currentYear'
    ORDER BY date DESC
";
$resYear = $conn->query($sqlCurrentYear);

$currentYearRows = [];
$yearIncome = $yearExpense = 0;
while ($row = $resYear->fetch_assoc()) {
    $currentYearRows[] = $row;
    ($row['type']=='Income') ? $yearIncome += $row['amount'] : $yearExpense += $row['amount'];
}

/* ================= PREVIOUS YEARS ================= */
$sqlPrevYears = "
    SELECT 'Income' AS type, source AS title, description, amount, date 
    FROM income 
    WHERE user_id='$user_id' AND YEAR(date) < '$currentYear'
    UNION ALL
    SELECT 'Expense' AS type, category AS title, description, amount, date 
    FROM expenses 
    WHERE user_id='$user_id' AND YEAR(date) < '$currentYear'
    ORDER BY date DESC
";
$resPrevYears = $conn->query($sqlPrevYears);

$previousYears = [];
while ($row = $resPrevYears->fetch_assoc()) {
    $key = date('Y', strtotime($row['date']));
    $previousYears[$key][] = $row;
}
/* ================= CHART DATA ================= */

/* Last 6 months labels */
$chartMonthLabels = [];
$chartIncome = [];
$chartExpense = [];

for ($i = 5; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i months"));
    $label = date('M Y', strtotime($ym));
    $chartMonthLabels[] = $label;

    /* Total Income last 6 months */
    $resInc = $conn->query("
        SELECT SUM(amount) AS total FROM income
        WHERE user_id='$user_id' AND DATE_FORMAT(date,'%Y-%m')='$ym'
    ");
    $chartIncome[] = (float)($resInc->fetch_assoc()['total'] ?? 0);

    /* Total Expense last 6 months */
    $resExp = $conn->query("
        SELECT SUM(amount) AS total FROM expenses
        WHERE user_id='$user_id' AND DATE_FORMAT(date,'%Y-%m')='$ym'
    ");
    $chartExpense[] = (float)($resExp->fetch_assoc()['total'] ?? 0);
}

/* This Month Breakdown */
$incomeSourceLabels = [];
$incomeSourceData = [];
$resIncSources = $conn->query("
    SELECT source, SUM(amount) total 
    FROM income
    WHERE user_id='$user_id' AND MONTH(date)='$currentMonth' AND YEAR(date)='$currentYear'
    GROUP BY source
");
while ($r = $resIncSources->fetch_assoc()) {
    $incomeSourceLabels[] = $r['source'];
    $incomeSourceData[] = (float)$r['total'];
}

$expenseCategoryLabels = [];
$expenseCategoryData = [];
$resExpCats = $conn->query("
    SELECT category, SUM(amount) total 
    FROM expenses
    WHERE user_id='$user_id' AND MONTH(date)='$currentMonth' AND YEAR(date)='$currentYear'
    GROUP BY category
");
while ($r = $resExpCats->fetch_assoc()) {
    $expenseCategoryLabels[] = $r['category'];
    $expenseCategoryData[] = (float)$r['total'];
}

/* Top 5 Income Sources (All time) */
$topIncomeLabels = [];
$topIncomeData = [];
$resTopInc = $conn->query("
    SELECT source, SUM(amount) total 
    FROM income
    WHERE user_id='$user_id'
    GROUP BY source
    ORDER BY total DESC
    LIMIT 5
");
while ($r = $resTopInc->fetch_assoc()) {
    $topIncomeLabels[] = $r['source'];
    $topIncomeData[] = (float)$r['total'];
}

/* Top 5 Expense Categories (All time) */
$topExpenseLabels = [];
$topExpenseData = [];
$resTopExp = $conn->query("
    SELECT category, SUM(amount) total 
    FROM expenses
    WHERE user_id='$user_id'
    GROUP BY category
    ORDER BY total DESC
    LIMIT 5
");
while ($r = $resTopExp->fetch_assoc()) {
    $topExpenseLabels[] = $r['category'];
    $topExpenseData[] = (float)$r['total'];
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Reports</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
body{background:#cce4ff;font-family:Segoe UI;padding:20px}
.container{max-width:1100px;margin:auto;background:white;padding:40px;border-radius:20px}
h1{text-align:center;margin-bottom:20px;color:#1a4d8f}
.btn{padding:10px 16px;border-radius:8px;text-decoration:none;font-weight:600;color: #1a4d8f;background: #cce4ff}
.btn-back{border:2px solid #ddd;color: #1a4d8f}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{padding:14px;border-bottom:1px solid #eee;color: #1a4d8f}
thead{ background: #cce4ff; /* match page background */
    color: #0d1a40;}
.amount-income{color:#059669;font-weight:bold}
.amount-expense{color:#dc2626;font-weight:bold}
.total{text-align:right;font-weight:bold;padding:10px 0;color: #1a4d8f;}
.report-box{margin-top:40px;border:1px solid #e5e7eb;border-radius:18px;padding:30px}
.accordion{margin-top:20px;border:1px solid #ddd;border-radius:10px}
.accordion-header{padding:15px;cursor:pointer;background:#f0fdf4;font-weight:600;display:flex;justify-content:space-between;color: #1a4d8f;}
.accordion-body{display:none;padding:15px}
/* ===== Filter Bar Styles ===== */
.filter-bar{
    margin:25px 0;
    padding:20px;
    background:#f9fafb;
    border-radius:16px;
    display:flex;
    gap:15px;
    align-items:center;
    flex-wrap:wrap;
    box-shadow:0 10px 25px rgba(0,0,0,0.05);
}

.filter-bar select{
    padding:10px 14px;
    border-radius:10px;
    border:1px solid #d1d5db;
    font-size:15px;
    background:white;
    min-width:160px;
}

.filter-bar select:focus{
    outline:none;
    border-color:#10b981;
    box-shadow:0 0 0 3px rgba(16,185,129,.2);
}

.filter-btn{
    padding:10px 18px;
    border-radius:10px;
    font-weight:600;
    border:none;
    cursor:pointer;
    transition:.25s;
    display:flex;
    align-items:center;
    gap:6px;
	color: #1a4d8f;
}

.filter-apply{
    background: #cce4ff;
    color: #1a4d8f;
}

.filter-apply:hover{
    background: #cce4ff;
}

.filter-reset{
    background:#e5e7eb;
    color: #1a4d8f;
    text-decoration:none;
}

.filter-reset:hover{
    background:#d1d5db;
}

.filter-icon{
    font-size:14px;
}
.filter-btn:disabled{
    opacity:.5;
    cursor:not-allowed;
}
/* ===== Global Heading Color ===== */
h1, h2, h3, h4, h5, h6 {
    color: #1a4d8f;
}
</style>
</head>

<body>

<div class="container">
<h1>Financial Reports</h1>
<a href="dashboard.php" class="btn btn-back">← Dashboard</a>
<form method="GET" action="download_report.php" style="margin-top:15px;display:inline-block">

    <!-- pass current filters if any -->
    <input type="hidden" name="year" value="<?= htmlspecialchars($filterYear) ?>">
    <input type="hidden" name="month" value="<?= htmlspecialchars($filterMonth) ?>">

    <button class="btn btn-add" style="margin-left:10px">
        <i class="fas fa-download"></i>
        Download Report
    </button>
</form>

<form method="GET" class="filter-bar">

    <select name="year" id="filterYear">
        <option value="">Select Year</option>
        <?php foreach($availableYears as $y): ?>
            <option value="<?= $y ?>" <?= (isset($_GET['year']) && $_GET['year']==$y)?'selected':'' ?>>
                <?= $y ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="month" id="filterMonth" <?= empty($_GET['year'])?'disabled':'' ?>>
        <option value="">Select Month</option>
        <?php
        for($m=1;$m<=12;$m++){
            $selected = (isset($_GET['month']) && $_GET['month']==$m) ? 'selected' : '';
            echo "<option value='$m' $selected>".date('F', mktime(0,0,0,$m,1))."</option>";
        }
        ?>
    </select>

    <button type="submit" id="filterBtn" class="filter-btn filter-apply" disabled>
        <i class="fas fa-filter filter-icon"></i> Apply Filter
    </button>

    <a href="reports.php" class="filter-btn filter-reset">
        <i class="fas fa-rotate-left filter-icon"></i> Reset
    </a>

</form>


<?php if($filterMessage): ?>
    <div style="margin:20px 0;padding:15px;border-radius:10px;background:#fff3cd;color:#92400e;font-weight:600">
        <?= $filterMessage ?>
    </div>
<?php endif; ?>

<?php if($isFiltered && !$filterMessage): ?>
<div class="report-box">
<h2>
Filtered Report
<?php
if($filterYear && $filterMonth){
    echo " (".date('F',mktime(0,0,0,$filterMonth,1))." $filterYear)";
}elseif($filterYear){
    echo " ($filterYear)";
}
?>
</h2>
<?php if(!$resFiltered || $resFiltered->num_rows == 0): ?>
    <p style="padding:20px;font-weight:600;color:#dc2626">
        No reports found for the selected period.
    </p>
<?php else: ?>

<table>
<thead>
<tr>
<th>#</th><th>Type</th><th>Category</th><th>Description</th><th>Amount</th><th>Date</th>
</tr>
</thead>
<tbody>
<?php
$i=1; $inc=0; $exp=0;
while($r = $resFiltered->fetch_assoc()):
$r['type']=='Income' ? $inc+=$r['amount'] : $exp+=$r['amount'];
?>
<tr>
<td><?= $i++ ?></td>
<td><?= $r['type'] ?></td>
<td><?= htmlspecialchars($r['title']) ?></td>
<td><?= htmlspecialchars($r['description'] ?? '-') ?></td>
<td class="<?= $r['type']=='Income'?'amount-income':'amount-expense' ?>">
₹ <?= number_format($r['amount'],0) ?>
</td>
<td><?= date('d M Y',strtotime($r['date'])) ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<p class="total">
Income: ₹ <?= number_format($inc,0) ?> |
Expense: ₹ <?= number_format($exp,0) ?> |
Balance: ₹ <?= number_format($inc-$exp,0) ?>
</p>
</div>
<?php endif; ?>

<?php endif; ?>
<?php if(!$isFiltered): ?>
<!-- ================= MONTHLY ================= -->
<div class="report-box">
<h2>This Month (<?= date('F Y') ?>)</h2>

<?php if($currentMonthRows): ?>
<table>
<thead>
<tr>
<th>#</th><th>Type</th><th>Category</th><th>Description</th><th>Amount</th><th>Date</th>
</tr>
</thead>
<tbody>
<?php $i=1; foreach($currentMonthRows as $r): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= $r['type'] ?></td>
<td><?= htmlspecialchars($r['title']) ?></td>
<td><?= htmlspecialchars($r['description'] ?? '-') ?></td>
<td class="<?= $r['type']=='Income'?'amount-income':'amount-expense' ?>">₹ <?= number_format($r['amount'],0) ?></td>
<td><?= date('d M Y',strtotime($r['date'])) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<p class="total">
Income: ₹ <?= number_format($monthIncome,0) ?> |
Expense: ₹ <?= number_format($monthExpense,0) ?> |
Balance: ₹ <?= number_format($monthIncome-$monthExpense,0) ?>
</p>
<?php endif; ?>

<h3 style="margin-top:30px">Previous Months</h3>

<?php foreach($previousMonths as $month=>$rows):
$inc=$exp=0;
foreach($rows as $r){ $r['type']=='Income' ? $inc+=$r['amount'] : $exp+=$r['amount']; }
?>
<div class="accordion">
<div class="accordion-header" onclick="toggle(this)">
<?= $month ?> <i class="fas fa-chevron-down"></i>
</div>
<div class="accordion-body">
<table>
<thead>
<tr>
<th>#</th><th>Type</th><th>Category</th><th>Description</th><th>Amount</th><th>Date</th>
</tr>
</thead>
<tbody>
<?php $i=1; foreach($rows as $r): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= $r['type'] ?></td>
<td><?= htmlspecialchars($r['title']) ?></td>
<td><?= htmlspecialchars($r['description'] ?? '-') ?></td>
<td class="<?= $r['type']=='Income'?'amount-income':'amount-expense' ?>">₹ <?= number_format($r['amount'],0) ?></td>
<td><?= date('d M Y',strtotime($r['date'])) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<p class="total">
Income: ₹ <?= number_format($inc,0) ?> |
Expense: ₹ <?= number_format($exp,0) ?> |
Balance: ₹ <?= number_format($inc-$exp,0) ?>
</p>

</div>
</div>
<?php endforeach; ?>
</div>

<!-- ================= YEARLY ================= -->
<div class="report-box">
<h2>This Year (<?= $currentYear ?>)</h2>

<table>
<thead>
<tr>
<th>#</th><th>Type</th><th>Category</th><th>Description</th><th>Amount</th><th>Date</th>
</tr>
</thead>
<tbody>
<?php $i=1; foreach($currentYearRows as $r): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= $r['type'] ?></td>
<td><?= htmlspecialchars($r['title']) ?></td>
<td><?= htmlspecialchars($r['description'] ?? '-') ?></td>
<td class="<?= $r['type']=='Income'?'amount-income':'amount-expense' ?>">₹ <?= number_format($r['amount'],0) ?></td>
<td><?= date('d M Y',strtotime($r['date'])) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<p class="total">
Income: ₹ <?= number_format($yearIncome,0) ?> |
Expense: ₹ <?= number_format($yearExpense,0) ?> |
Balance: ₹ <?= number_format($yearIncome-$yearExpense,0) ?>
</p>

<h3 style="margin-top:30px">Previous Years</h3>

<?php foreach($previousYears as $year=>$rows):
$inc=$exp=0;
foreach($rows as $r){ $r['type']=='Income' ? $inc+=$r['amount'] : $exp+=$r['amount']; }
?>
<div class="accordion">
<div class="accordion-header" onclick="toggle(this)">
<?= $year ?> <i class="fas fa-chevron-down"></i>
</div>
<div class="accordion-body">
<table>
<thead>
<tr>
<th>#</th><th>Type</th><th>Category</th><th>Description</th><th>Amount</th><th>Date</th>
</tr>
</thead>
<tbody>
<?php $i=1; foreach($rows as $r): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= $r['type'] ?></td>
<td><?= htmlspecialchars($r['title']) ?></td>
<td><?= htmlspecialchars($r['description'] ?? '-') ?></td>
<td class="<?= $r['type']=='Income'?'amount-income':'amount-expense' ?>">₹ <?= number_format($r['amount'],0) ?></td>
<td><?= date('d M Y',strtotime($r['date'])) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<p class="total">
Income: ₹ <?= number_format($inc,0) ?> |
Expense: ₹ <?= number_format($exp,0) ?> |
Balance: ₹ <?= number_format($inc-$exp,0) ?>
</p>

</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<hr style="margin:40px 0">
<h2 style="text-align:center">Financial Insights</h2>

<div style="display:grid;grid-template-columns:2fr 2fr;gap:25px;margin-bottom:40px">
    <div>
        <h3 style="text-align:center">Monthly Income vs Expense (Last 6 months)</h3>
        <canvas id="monthlyTrendChart" height="120"></canvas>
    </div>
    
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:25px;margin-bottom:40px">
    <div>
        <h3 style="text-align:center">Top 5 Income Sources</h3>
        <canvas id="topIncomeChart" height="100"></canvas>
    </div>
    <div>
        <h3 style="text-align:center">Top 5 Expense Categories</h3>
        <canvas id="topExpenseChart" height="100"></canvas>
    </div>
</div>

</div>

<script>
function toggle(el){
    const body = el.nextElementSibling;
    body.style.display = body.style.display==="block"?"none":"block";
}
</script>
<script>
const yearSel  = document.getElementById('filterYear');
const monthSel = document.getElementById('filterMonth');
const filterBtn = document.getElementById('filterBtn');

function validateFilter(){
    const year  = yearSel.value;
    const month = monthSel.value;

    // Enable month only if year selected
    monthSel.disabled = !year;
    if(!year) monthSel.value = '';

    // Valid if:
    // 1) Year only
    // 2) Year + Month
    filterBtn.disabled = !(year || (year && month));
}

yearSel.addEventListener('change', validateFilter);
monthSel.addEventListener('change', validateFilter);

// Initial validation (page load)
validateFilter();
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.addEventListener('DOMContentLoaded', () => {

    const monthLabels = <?= json_encode($chartMonthLabels) ?>;
    const incomeData  = <?= json_encode($chartIncome) ?>;
    const expenseData = <?= json_encode($chartExpense) ?>;

    const incSourceLabels = <?= json_encode($incomeSourceLabels) ?>;
    const incSourceData   = <?= json_encode($incomeSourceData) ?>;

    const expCatLabels = <?= json_encode($expenseCategoryLabels) ?>;
    const expCatData   = <?= json_encode($expenseCategoryData) ?>;

    const topIncLabels = <?= json_encode($topIncomeLabels) ?>;
    const topIncData   = <?= json_encode($topIncomeData) ?>;

    const topExpLabels = <?= json_encode($topExpenseLabels) ?>;
    const topExpData   = <?= json_encode($topExpenseData) ?>;

    /* ===== Monthly Trend ===== */
    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [
                { label: 'Income', data: incomeData, borderColor: '#10b981', fill:false, tension:0.4 },
                { label: 'Expense', data: expenseData, borderColor: '#ef4444', fill:false, tension:0.4 }
            ]
        },
        options: { responsive:true }
    });

    /* ===== This Month Breakdown (Doughnut) ===== */
    new Chart(document.getElementById('thisMonthChart'), {
        type: 'doughnut',
        data: {
            labels: [...incSourceLabels, ...expCatLabels],
            datasets: [{
                data: [...incSourceData, ...expCatData],
                backgroundColor: [
                    '#10b981','#3b82f6','#f59e0b','#8b5cf6','#ec4899',
                    '#ef4444','#f87171','#facc15','#34d399','#60a5fa'
                ]
            }]
        },
        options: { plugins: { legend: { position:'bottom' } } }
    });

    /* ===== Top 5 Income Sources (Bar) ===== */
    new Chart(document.getElementById('topIncomeChart'), {
        type:'bar',
        data:{ labels: topIncLabels, datasets:[{label:'Total Income', data: topIncData, backgroundColor:'#10b981'}] },
        options:{ indexAxis:'y', responsive:true }
    });

    /* ===== Top 5 Expense Categories (Bar) ===== */
    new Chart(document.getElementById('topExpenseChart'), {
        type:'bar',
        data:{ labels: topExpLabels, datasets:[{label:'Total Expense', data: topExpData, backgroundColor:'#ef4444'}] },
        options:{ indexAxis:'y', responsive:true }
    });

});
</script>

</body>
</html>
