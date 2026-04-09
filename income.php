<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'db/config.php';
$user_id = $_SESSION["user_id"];


/* ================= INLINE UPDATE (AJAX) ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inline_update'])) {
    $id = intval($_POST['id']);
    $desc = $conn->real_escape_string($_POST['description']);
    $amount = intval($_POST['amount']);
    $date = $conn->real_escape_string($_POST['date']);

    $sql = "UPDATE income 
            SET description='$desc', amount='$amount', date='$date'
            WHERE id='$id' AND user_id='$user_id'";

    echo $conn->query($sql) ? "success" : "error";
    exit;
}
$currentMonth = date('m');
$currentYear  = date('Y');
/* ================= CURRENT MONTH INCOME ================= */
$currentSql = "SELECT * FROM income
               WHERE user_id='$user_id'
               AND MONTH(date)='$currentMonth'
               AND YEAR(date)='$currentYear'
               ORDER BY date DESC";
$currentResult = $conn->query($currentSql);

$currentRows = [];
$currentTotal = 0;
if ($currentResult) {
    while ($row = $currentResult->fetch_assoc()) {
        $currentRows[] = $row;
        $currentTotal += $row['amount'];
    }
}

/* ================= PREVIOUS MONTH INCOME ================= */
$previousSql = "SELECT * FROM income
                WHERE user_id='$user_id'
                AND (
                    YEAR(date) < '$currentYear'
                    OR (YEAR(date)='$currentYear' AND MONTH(date) < '$currentMonth')
                )
                ORDER BY date DESC";
$previousResult = $conn->query($previousSql);

$previousIncome = [];
if ($previousResult) {
    while ($row = $previousResult->fetch_assoc()) {
        $monthKey = date('F Y', strtotime($row['date']));
        $previousIncome[$monthKey][] = $row;
    }
}
/* ================= INCOME CHART DATA ================= */

/* Income by source (current month) */
$sourceLabels = [];
$sourceData = [];

$sourceRes = $conn->query("
    SELECT source, SUM(amount) total 
    FROM income 
    WHERE user_id='$user_id'
      AND MONTH(date)='$currentMonth'
      AND YEAR(date)='$currentYear'
    GROUP BY source
");

while ($r = $sourceRes->fetch_assoc()) {
    $sourceLabels[] = $r['source'];
    $sourceData[] = (float)$r['total'];
}

/* Monthly income trend (last 6 months) */
$monthLabels = [];
$monthIncome = [];

for ($i = 5; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i months"));
    $label = date('M Y', strtotime($ym));

    $res = $conn->query("
        SELECT SUM(amount) total 
        FROM income 
        WHERE user_id='$user_id'
          AND DATE_FORMAT(date,'%Y-%m')='$ym'
    ");

    $amt = $res->fetch_assoc()['total'] ?? 0;

    $monthLabels[] = $label;
    $monthIncome[] = (float)$amt;
}

/* Top 5 sources (all time) */
$topLabels = [];
$topData = [];

$topRes = $conn->query("
    SELECT source, SUM(amount) total 
    FROM income 
    WHERE user_id='$user_id'
    GROUP BY source
    ORDER BY total DESC
    LIMIT 5
");

while ($r = $topRes->fetch_assoc()) {
    $topLabels[] = $r['source'];
    $topData[] = (float)$r['total'];
}
/* ================= INLINE DELETE (AJAX) ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inline_delete'])) {
    $id = intval($_POST['id']);

    $sql = "DELETE FROM income WHERE id='$id' AND user_id='$user_id'";
    echo $conn->query($sql) ? "success" : "error";
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
<title>My Income</title>
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
    max-width:1125px;
	width:100%;
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
.btn-add{background: #cce4ff; /* same as page background */
    color: #0d1a40; /* dark blue text for contrast */
    border: 2px solid #0d1a40;}
.btn-back{border:2px solid #ddd;color:#333}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}
th,td{padding:14px;border-bottom:1px solid #eee}
thead{background: #cce4ff; /* match page background */
    color: #0d1a40;}
.actions a{margin-right:8px;color:#555}
.actions a:hover{color:#000}
.amount{font-weight:bold;color:#059669}
.total{
    text-align:right;
    font-weight:bold;
    padding:10px 0;
}
.accordion{margin-top:20px;border:1px solid #ddd;border-radius:10px}
.accordion-header{
    padding:15px;
    cursor:pointer;
    background:#f0fdf4;
    font-weight:600;
    display:flex;
    justify-content:space-between;
}
.accordion-body{display:none;padding:15px}
input{
    padding:6px;
    width:100%;
    border-radius:6px;
    border:1px solid #ccc;
}
.actions i{
    cursor:pointer;
    margin-right:10px;
}
.actions i:hover{color:#000}

</style>
</head>

<body>

<div class="container">
<a href="dashboard.php" class="btn btn-back">← Dashboard</a>
<h1>My Income</h1>

<div style="display:flex;justify-content:space-between;margin-bottom:20px">
<a href="dashboard.php">
<a href="add_income.php" class="btn btn-add">+ Add Income</a>
</div>

<h2>This Month (<?= date('F Y') ?>)</h2>

<?php if (!empty($currentRows)): ?>
<table>
<thead>
<tr>
<th>#</th>
<th>Source</th>
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
<td><?= htmlspecialchars($row['source']) ?></td>
<td class="desc"><?= htmlspecialchars($row['description'] ?? '-') ?></td>
<td class="amount" data-value="<?= $row['amount'] ?>">₹ <?= number_format($row['amount'],0) ?></td>
<td class="date" data-value="<?= $row['date'] ?>"><?= date('d M Y',strtotime($row['date'])) ?></td>

<td class="actions">
<i class="fas fa-edit" onclick="enableEdit(this)"></i>
<i class="fas fa-trash" onclick="deleteIncome(<?= $row['id'] ?>)"></i>
</td>

</tr>
<?php endforeach; ?>
</tbody>
</table>
<p class="total">Total: ₹ <?= number_format($currentTotal,0) ?></p>
<?php else: ?>
<p>No income this month.</p>
<?php endif; ?>

<?php if (!empty($previousIncome)): ?>
<h2 style="margin-top:40px">Previous Months</h2>

<?php foreach ($previousIncome as $month=>$items): 
    $monthTotal = 0;
    foreach($items as $inc) { $monthTotal += $inc['amount']; }
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
                    <th>Source</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $j=1; foreach($items as $inc): ?>
                <tr>
                    <td><?= $j++ ?></td>
                    <td><?= htmlspecialchars($inc['source']) ?></td>
                    <td class="desc"><?= htmlspecialchars($inc['description'] ?? '-') ?></td>
                    <td class="amount" data-value="<?= $inc['amount'] ?>">₹ <?= number_format($inc['amount'],0) ?></td>
                    <td class="date" data-value="<?= $inc['date'] ?>"><?= date('d M Y',strtotime($inc['date'])) ?></td>
                    <td class="actions">
                        <i class="fas fa-edit" onclick="enableEdit(this)"></i>
                        <i class="fas fa-trash" onclick="deleteIncome(<?= $inc['id'] ?>)"></i>
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

<hr style="margin:40px 0">
<h2 style="text-align:center">Income Insights</h2>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:25px;margin-bottom:40px">
    <div>
        <h3 style="text-align:center">Monthly Income Trend</h3>
        <canvas id="monthlyIncomeChart" height="120"></canvas>
    </div>
    <div>
        <h3 style="text-align:center">Income Breakdown (This Month)</h3>
        <canvas id="incomeSourceChart" height="120"></canvas>
    </div>
</div>

<div style="margin-bottom:40px">
    <h3 style="text-align:center">Top 5 Income Sources</h3>
    <canvas id="topIncomeChart" height="100"></canvas>
</div>

</div>

<script>
function toggle(el){
    let body = el.nextElementSibling;
    body.style.display = body.style.display==="block" ? "none" : "block";
}
</script>
<script>
function enableEdit(icon){
    const row = icon.closest("tr");

    if (row.classList.contains("editing")) return;
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

    icon.outerHTML = `<i class="fas fa-save" onclick="saveEdit(this)"></i>
                      <i class="fas fa-times" onclick="location.reload()"></i>`;
}


function saveEdit(icon){
    const row = icon.closest("tr");
    const id = row.querySelector(".fa-trash").getAttribute("onclick").match(/\d+/)[0];

    const desc = row.querySelector(".desc input").value;
   const amount = Math.round(
    parseFloat(row.querySelector(".amount input").value)
);

    const date = row.querySelector(".date input").value;

    fetch("", {
        method:"POST",
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`inline_update=1&id=${id}&description=${encodeURIComponent(desc)}&amount=${amount}&date=${date}`
    })
    .then(res=>res.text())
    .then(res=>{
        if(res==="success") location.reload();
        else alert("Update failed");
    });
}

function deleteIncome(id){
    if(!confirm("Delete this income?")) return;

    fetch("", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `inline_delete=1&id=${id}`
    })
    .then(res => res.text())
    .then(res => {
        if(res === "success"){
            location.reload();
        } else {
            alert("Delete failed");
        }
    });
}

</script>
<script>
window.addEventListener('DOMContentLoaded', () => {

    const sourceLabels = <?= json_encode($sourceLabels) ?>;
    const sourceData = <?= json_encode($sourceData) ?>;

    const monthLabels = <?= json_encode($monthLabels) ?>;
    const monthIncome = <?= json_encode($monthIncome) ?>;

    const topLabels = <?= json_encode($topLabels) ?>;
    const topData = <?= json_encode($topData) ?>;

    /* Income Breakdown (Donut) */
    new Chart(document.getElementById('incomeSourceChart'), {
        type: 'doughnut',
        data: {
            labels: sourceLabels,
            datasets: [{
                data: sourceData,
                backgroundColor: [
                    '#10b981','#3b82f6','#f59e0b','#ef4444','#8b5cf6','#ec4899'
                ]
            }]
        },
        options: {
            plugins: { legend: { position: 'bottom' } }
        }
    });

    /* Monthly Income Trend (Line) */
    new Chart(document.getElementById('monthlyIncomeChart'), {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Income',
                data: monthIncome,
                borderColor: '#10b981',
                fill: false,
                tension: 0.4
            }]
        },
        options: { responsive: true }
    });

    /* Top 5 Income Sources (Bar) */
    new Chart(document.getElementById('topIncomeChart'), {
        type: 'bar',
        data: {
            labels: topLabels,
            datasets: [{
                label: 'Total Income',
                data: topData,
                backgroundColor: '#10b981'
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true
        }
    });

});
</script>

</body>
</html>
