<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'db/config.php';
$user_id = $_SESSION["user_id"];
$id = intval($_GET['id'] ?? 0);

$sql = "DELETE FROM income WHERE id='$id' AND user_id='$user_id'";
$conn->query($sql);

header("Location: income.php");
exit();
