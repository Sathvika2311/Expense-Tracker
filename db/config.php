<?php
$host = 'localhost';       // your MySQL server
$user = 'root';            // default username in XAMPP
$pass = '';                // default password is empty
$db = 'expense_tracker';  // your DB name

// Create connection
$conn = new mysqli("localhost", "root", "", "expense_tracker");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>