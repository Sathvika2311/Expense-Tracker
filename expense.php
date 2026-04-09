<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'db/config.php';
$user_id = $_SESSION["user_id"];
/* ================= INLINE UPDATE EXPENSE (AJAX) ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inline_update_expense'])) {

    $id = intval($_POST['id']);
    $description = $conn->real_escape_string($_POST['description']);
    $amount = intval($_POST['amount']);

    $date = $conn->real_escape_string($_POST['date']);

    $sql = "UPDATE expenses 
            SET description='$description',
                amount='$amount',
                date='$date'
            WHERE id='$id' AND user_id='$user_id'";

    echo $conn->query($sql) ? "success" : "error";
    exit;
}

$currentMonth = date('m');
$currentYear  = date('Y');

/* ================= CURRENT MONTH EXPENSE ================= */
$currentSql = "SELECT * FROM expenses
               WHERE user_id='$user_id'
               AND MONTH(date)='$currentMonth'
               AND YEAR(date)='$currentYear'
               ORDER BY date DESC";
$currentResult = $conn->query($currentSql);

$currentRows = [];
$currentTotal = 0;
if ($currentResult) {
    while ($row = $currentResult->fetch_assoc()) {
        $currentTotal += $row['amount'];
        $currentRows[] = $row;
    }
}

/* ================= PREVIOUS MONTH EXPENSE ================= */
$previousSql = "SELECT * FROM expenses
                WHERE user_id='$user_id'
                AND (
                    YEAR(date) < '$currentYear'
                    OR (YEAR(date)='$currentYear' AND MONTH(date) < '$currentMonth')
                )
                ORDER BY date DESC";
$previousResult = $conn->query($previousSql);

$previousExpense = [];
if ($previousResult) {
    while ($row = $previousResult->fetch_assoc()) {
        $monthKey = date('F Y', strtotime($row['date']));
        $previousExpense[$monthKey][] = $row;
    }
}
/* ================= EXPENSE CHART DATA ================= */

/* Expense by category (current month) */
$catLabels = [];
$catData = [];

$catRes = $conn->query("
    SELECT category, SUM(amount) total 
    FROM expenses 
    WHERE user_id='$user_id'
      AND MONTH(date)='$currentMonth'
      AND YEAR(date)='$currentYear'
    GROUP BY category
");

while ($r = $catRes->fetch_assoc()) {
    $catLabels[] = $r['category'];
    $catData[] = (float)$r['total'];
}

/* Monthly expense trend (last 6 months) */
$monthLabels = [];
$monthExpenses = [];

for ($i = 5; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i months"));
    $label = date('M Y', strtotime($ym));

    $res = $conn->query("
        SELECT SUM(amount) total 
        FROM expenses 
        WHERE user_id='$user_id'
          AND DATE_FORMAT(date,'%Y-%m')='$ym'
    ");

    $amt = $res->fetch_assoc()['total'] ?? 0;

    $monthLabels[] = $label;
    $monthExpenses[] = (float)$amt;
}

/* Top 5 categories (all time) */
$topLabels = [];
$topData = [];

$topRes = $conn->query("
    SELECT category, SUM(amount) total 
    FROM expenses 
    WHERE user_id='$user_id'
    GROUP BY category
    ORDER BY total DESC
    LIMIT 5
");

while ($r = $topRes->fetch_assoc()) {
    $topLabels[] = $r['category'];
    $topData[] = (float)$r['total'];
}

?>

<!DOCTYPE html>
<html>
<head>
<title>My Expenses</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
    background: #cce4ff;
    font-family:Segoe UI;
    padding:20px;
	color: #1a4d8f;
}
.container{
    max-width:1100px;
    margin:auto;
    background:white;
    padding:40px;
    border-radius:20px;
}
h1{text-align:center;margin-bottom:20px}
.btn{
    padding:10px 16px;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
}
.btn-add{ background: #cce4ff; /* same as page background */
    color: #0d1a40; /* dark blue text for contrast */
    border: 2px solid #0d1a40;}
.btn-back{border:2px solid #ddd;color:#333}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}
th,td{padding:14px;border-bottom:1px solid #eee}
thead{ background: #cce4ff; /* match page background */
    color: #0d1a40;}
.actions a{
    margin-right:8px;
    color:#555;
}
.actions a:hover{color:#000}
.accordion{
    margin-top:20px;
    border:1px solid #ddd;
    border-radius:10px;
}
.accordion-header{
    padding:15px;
    cursor:pointer;
    background:#fff5f5;
    font-weight:600;
    display:flex;
    justify-content:space-between;
}
.accordion-body{
    display:none;
    padding:15px;
}
.amount{
    font-weight:bold;
    color:#ef4444;
}
.total{
    text-align:right;
    font-weight:bold;
    padding:10px 0;
}
input{
    width:100%;
    padding:6px;
    border-radius:6px;
    border:1px solid #ccc;
}
.actions i{
    cursor:pointer;
    margin-right:10px;
}
.actions i:hover{
    color:#000;
}

</style>
</head>

<body>

<div class="container">
<a href="dashboard.php" class="btn btn-back">← Dashboard</a>
<h1>My Expenses</h1>



<div style="display:flex;justify-content:space-between;margin-bottom:20px">
<a href="dashboard.php">
<a href="add_expense.php" class="btn btn-add">+ Add Expense</a>
</div>
<h2>This Month (<?= date('F Y') ?>)</h2>


<?php if (!empty($currentRows)): ?>
<table>
<thead>
<tr>
<th>#</th>
<th>Name</th>
<th>Description</th>
<th>Amount</th>
<th>Date</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php $i=1; foreach($currentRows as $row): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($row['category']) ?></td>
<td class="desc"><?= htmlspecialchars($row['description'] ?? '-') ?></td>

<td class="amount" data-value="<?= $row['amount'] ?>">
    ₹ <?= number_format($row['amount'],0) ?>
</td>

<td class="date" data-value="<?= $row['date'] ?>">
    <?= date('d M Y',strtotime($row['date'])) ?>
</td>

<td class="actions">
<i class="fas fa-edit" onclick="enableExpenseEdit(this)"></i>
<i class="fas fa-trash" onclick="deleteExpense(<?= $row['id'] ?>)"></i>
</td>

</tr>
<?php endforeach; ?>
</tbody>
</table>
<p class="total">Total: ₹ <?= number_format($currentTotal, 0) ?></p>
<?php else: ?>
<p>No expense this month.</p>
<?php endif; ?>

<?php if (!empty($previousExpense)): ?>
<h2 style="margin-top:40px">Previous Months</h2>

<?php foreach ($previousExpense as $month=>$items): 
    $monthTotal = 0;
    foreach($items as $ex) { $monthTotal += $ex['amount']; }
?>
<div class="accordion">
<div class="accordion-header" onclick="toggle(this)">
<?= $month ?> <i class="fas fa-chevron-down"></i>
</div>
<div class="accordion-body">
<table>
<thead>
<tr>
<th>#</th>
<th>Name</th>
<th>Description</th>
<th>Amount</th>
<th>Date</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php $j=1; foreach($items as $ex): ?>
<tr>
<td><?= $j++ ?></td>
<td><?= htmlspecialchars($ex['category']) ?></td>
<td class="desc"><?= htmlspecialchars($ex['description'] ?? '-') ?></td>

<td class="amount" data-value="<?= $ex['amount'] ?>">
    ₹ <?= number_format($ex['amount'],0) ?>
</td>

<td class="date" data-value="<?= $ex['date'] ?>">
    <?= date('d M Y',strtotime($ex['date'])) ?>
</td>

<td class="actions">
<i class="fas fa-edit" onclick="enableExpenseEdit(this)"></i>
<i class="fas fa-trash" onclick="deleteExpense(<?= $ex['id'] ?>)"></i>
</td>

</tr>
<?php endforeach; ?>

</tbody>
</table>
<p class="total">Total: ₹ <?= number_format($monthTotal,0) ?></p>
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- ================= EXPENSE CHARTS ================= -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:25px;margin:40px 0">

    <div>
        <h2>Expense Trend (Last 6 Months)</h2>
        <canvas id="monthlyExpenseChart" height="120"></canvas>
    </div>

    <div>
        <h2>Expense Breakdown (This Month)</h2>
        <canvas id="expenseCategoryChart"></canvas>
    </div>

</div>

<div style="margin-bottom:40px">
    <h2>Top 5 Expense Categories</h2>
    <canvas id="topExpenseChart" height="100"></canvas>
</div>

</div>

<script>
function toggle(el){
    let body = el.nextElementSibling;
    body.style.display = body.style.display==="block" ? "none" : "block";
}
</script>
<script>
function enableExpenseEdit(icon){
    const row = icon.closest("tr");
    if(row.classList.contains("editing")) return;

    row.classList.add("editing");

    const desc = row.querySelector(".desc");
    const amount = row.querySelector(".amount");
    const date = row.querySelector(".date");

    // Get today's date in YYYY-MM-DD format
    const today = new Date().toISOString().split('T')[0];

    desc.innerHTML = `<input value="${desc.innerText}" />`;
 amount.innerHTML = `
    <input 
        type="number" 
        step="1" 
        min="0"
        inputmode="numeric"
        onkeydown="return event.key !== '.'"
        value="${Math.round(parseFloat(amount.dataset.value))}"
    />
`;

 

    date.innerHTML = `<input type="date" value="${date.dataset.value}" max="${today}" />`; // <-- max added here

    icon.outerHTML = `
        <i class="fas fa-save" onclick="saveExpense(this)"></i>
        <i class="fas fa-times" onclick="location.reload()"></i>
    `;
}


function saveExpense(icon){
    const row = icon.closest("tr");
    const id = row.querySelector(".fa-trash")
                  .getAttribute("onclick")
                  .match(/\d+/)[0];

    const desc = row.querySelector(".desc input").value;
   const amount = Math.round(
    parseFloat(row.querySelector(".amount input").value)
);

    const date = row.querySelector(".date input").value;

    fetch("", {
        method:"POST",
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`inline_update_expense=1&id=${id}&description=${encodeURIComponent(desc)}&amount=${amount}&date=${date}`
    })
    .then(res=>res.text())
    .then(res=>{
        if(res==="success") location.reload();
        else alert("Update failed");
    });
}

function deleteExpense(id){
    if(confirm("Delete this expense?")){
        window.location = "delete_expense.php?id="+id;
    }
}
</script>
<script>
/* PHP → JS */
const catLabels = <?= json_encode($catLabels) ?>;
const catData = <?= json_encode($catData) ?>;

const monthLabels = <?= json_encode($monthLabels) ?>;
const monthExpenses = <?= json_encode($monthExpenses) ?>;

const topLabels = <?= json_encode($topLabels) ?>;
const topData = <?= json_encode($topData) ?>;

/* Expense Category Donut */
new Chart(document.getElementById('expenseCategoryChart'), {
    type: 'doughnut',
    data: {
        labels: catLabels,
        datasets: [{
            data: catData,
            backgroundColor: [
                '#ef4444','#f59e0b','#3b82f6',
                '#10b981','#8b5cf6','#ec4899'
            ]
        }]
    },
    options: {
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

/* Monthly Expense Trend */
new Chart(document.getElementById('monthlyExpenseChart'), {
    type: 'line',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Expenses',
            data: monthExpenses,
            borderColor: '#ef4444',
            fill: false,
            tension: 0.4
        }]
    },
    options: {
        responsive: true
    }
});

/* Top 5 Expense Categories */
new Chart(document.getElementById('topExpenseChart'), {
    type: 'bar',
    data: {
        labels: topLabels,
        datasets: [{
            label: 'Total Expense',
            data: topData,
            backgroundColor: '#ef4444'
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true
    }
});
</script>



</body>
</html>
