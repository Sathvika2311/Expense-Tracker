<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'db/config.php';

$user_id = $_SESSION["user_id"];
$id = intval($_GET['id'] ?? 0);

/* Fetch expense */
$sql = "SELECT * FROM expenses WHERE id='$id' AND user_id='$user_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: expense.php");
    exit();
}

$row = $result->fetch_assoc();

/* Update expense */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category  = $_POST['category'];
    $amount = $_POST['amount'];
    $date   = $_POST['date'];

    $update = "UPDATE expenses
               SET category='$category', amount='$amount', date='$date'
               WHERE id='$id' AND user_id='$user_id'";
    $conn->query($update);

    header("Location: expense.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Expense</title>

<style>
body{
    font-family: Segoe UI;
    background:#fff5f5;
    padding:30px;
}
form{
    max-width:400px;
    margin:auto;
    background:white;
    padding:25px;
    border-radius:10px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}
h2{
    text-align:center;
    margin-bottom:15px;
}
input,button{
    width:100%;
    padding:10px;
    margin-top:10px;
    font-size:15px;
}
button{
    background:#ef4444;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
}
button:hover{
    background:#dc2626;
}
</style>
</head>

<body>

<form method="post">
    <h2>Edit Expense</h2>

    <input type="text" name="pname"
           value="<?= htmlspecialchars($row['category']) ?>"
           placeholder="Expense Name" required>

    <input type="number" step="0.01" name="pprice"
           value="<?= $row['amount'] ?>"
           placeholder="Amount" required>

    <input type="date" name="date"
           value="<?= $row['date'] ?>" required>

    <button type="submit">Update Expense</button>
</form>

</body>
</html>
