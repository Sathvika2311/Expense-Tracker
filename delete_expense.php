<?php
session_start();
include 'db/config.php';

$id = $_GET['id'];
$conn->query("DELETE FROM expenses WHERE id='$id'");

header("Location: expense.php");
exit();
