<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'db/config.php';

$user_id = $_SESSION["user_id"];

/* ================= STEP 5 — PLACE IT HERE ================= */
// Mark all unread notifications as read
$markRead = $conn->prepare(
    "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0"
);
$markRead->bind_param("i", $user_id);
$markRead->execute();
/* ========================================================== */

// Fetch notifications (latest first)
$stmt = $conn->prepare(
    "SELECT description, date_time FROM notifications WHERE user_id = ? ORDER BY date_time DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html>
<head>
<title>Notifications</title>

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
    position: relative;   /* ✅ ADD THIS */
}

h1{text-align:center;margin-bottom:20px}
.btn-back{
    position:absolute;
    top:20px;
    left:20px;
    padding:10px 16px;
    background-color:#fff;
    border:2px solid #667eea;
    border-radius:8px;
    color:#667eea;
    font-weight:600;
    text-decoration:none;
}

/* Notification message style */
.notification-item{
    padding:18px;
    border-bottom:1px solid #eee;
}
.notification-item:last-child{
    border-bottom:none;
}
.notification-text{
    font-weight:600;
    font-size:16px;
}

.notification-time{
    font-size:13px;
    color:#555;
    margin-top:6px;
}
.no-data{
    text-align:center;
    padding:30px;
    color:#777;
}
</style>
</head>

<body>

<div class="container">
<a href="dashboard.php" class="btn-back">← Dashboard</a>
<h1>Notifications</h1>

<?php if($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="notification-item">
            <div class="notification-text">
                🔔 <?= nl2br($row["description"]) ?>


            </div>
            <div class="notification-time">
                <?= date("d M Y, h:i A", strtotime($row["date_time"])) ?>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="no-data">
        No notifications yet.
    </div>
<?php endif; ?>

</div>

</body>
</html>
