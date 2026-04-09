<?php
session_start();
include 'db/config.php';

if(!isset($_SESSION['user_id'])) exit('Unauthorized');

$user_id = $_SESSION['user_id'];

$action = $_POST['action'] ?? '';
$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);

if($action === 'update') {
    if($type=='current') {
        $category = $id; // current budgets: id = category
        $sql = "UPDATE budgets SET amount='$amount' WHERE user_id='$user_id' AND category='$category'";
    } else if($type=='up') {
        list($category,$month,$year) = explode('_',$id);
        $sql = "UPDATE budgets SET amount='$amount' WHERE user_id='$user_id' AND category='$category' AND month='$month' AND year='$year'";
    }
    if($conn->query($sql)) echo "Budget updated successfully!";
    else echo "Error updating budget!";
}

if($action === 'delete') {
    if($type=='current') {
        $category = $id;
        $sql = "DELETE FROM budgets WHERE user_id='$user_id' AND category='$category'";
    } else if($type=='up') {
        list($category,$month,$year) = explode('_',$id);
        $sql = "DELETE FROM budgets WHERE user_id='$user_id' AND category='$category' AND month='$month' AND year='$year'";
    }
    if($conn->query($sql)) echo "Budget deleted successfully!";
    else echo "Error deleting budget!";
}
?>
