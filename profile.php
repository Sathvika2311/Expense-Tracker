<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'db/config.php';

$user_id = (int)$_SESSION["user_id"];
$message = "";
$error = "";

/* ================= FETCH USER ================= */
$result = $conn->query("SELECT name, email, password FROM users WHERE id='$user_id'");
$user = $result->fetch_assoc();

/* ================= UPDATE NAME ================= */
if (isset($_POST['update_name'])) {
    $new_name = trim($_POST['name']);

    if ($new_name === "") {
        $error = "Name cannot be empty.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=? WHERE id=?");
        $stmt->bind_param("si", $new_name, $user_id);
        $stmt->execute();
        $stmt->close();

        $message = "Name updated successfully.";
        $user['name'] = $new_name;
    }
}

/* ================= UPDATE PASSWORD ================= */
if (isset($_POST['update_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($current, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new !== $confirm) {
        $error = "New password and confirm password do not match.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed, $user_id);
        $stmt->execute();
        $stmt->close();

        $message = "Password updated successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile | Expense Tracker</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
/* ===== Body & Fonts ===== */
body {
    background: #cce4ff;
    font-family: 'Segoe UI', sans-serif;
    padding: 20px;
	color: #1a4d8f;
}

/* ===== Container ===== */
.container {
    max-width: 1050px;
width:100%;	/* wider container */
    margin: 40px auto;
    background: white;
    padding: 50px;
    border-radius: 20px;
    box-shadow: 0 25px 70px rgba(0,0,0,0.25);
}

/* ===== Top Bar for Back Button ===== */
.top-bar {
    display: flex;
    justify-content: flex-start; /* align left */
    margin-bottom: 30px;
}

.btn-back {
    display: inline-block;
    padding: 10px 16px;
    border: 2px solid #667eea;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
	background: #cce4ff; /* same as page background */
    color: #1a4d8f; /* dark blue text for contrast */
    }
}

.btn-back:hover {
    background: #667eea;
    color: white;
    box-shadow: 0 6px 18px rgba(102,126,234,0.3);
}

/* ===== Headings ===== */
h2 {
    text-align: center;
    color: #1a4d8f;
    margin-bottom: 25px;
}

/* ===== Profile Form ===== */
.profile-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ===== Profile Header ===== */
.profile-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.user-icon i {
    font-size: 60px;
    color: #667eea;
    border: 2px solid #667eea;
    border-radius: 50%;
    padding: 10px;
    background: rgba(102, 126, 234, 0.1);
}

.user-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

/* ===== Form Groups ===== */
.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #1a4d8f;
}

input {
    width: 100%;
    padding: 14px 16px;
    border-radius: 12px;
    border: 1px solid #d1d5db;
    font-size: 15px;
    transition: all 0.2s ease;
}

input:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16,185,129,0.2);
}

/* ===== Buttons ===== */
button {
    padding: 14px 22px;
    background: #cce4ff;
    color: #1a4d8f;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.25s;
    font-size: 15px;
}

button:hover {
    background: #cce4ff9;
}

/* ===== Separators ===== */
hr {
    margin: 30px 0;
    border-color: #e5e7eb;
}

/* ===== Success & Error Messages ===== */
.success {
    background: #ecfdf5;
    color: #065f46;
    padding: 14px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-weight: 600;
}

.error {
    background: #fef2f2;
    color: #991b1b;
    padding: 14px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-weight: 600;
}
</style>
</head>
<body>

<div class="container">
    <div class="top-bar">
        <a href="dashboard.php" class="btn-back">← Dashboard</a>
    </div>

    <h2>My Profile</h2>

    <?php if ($message): ?>
        <div class="success"><?= $message ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <!-- UPDATE NAME FORM -->
    <form method="post" class="profile-form">
       

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Email (readonly)</label>
            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
        </div>

        <button type="submit" name="update_name">Update Name</button>
    </form>

    <hr>

    <!-- UPDATE PASSWORD FORM -->
    <form method="post" class="profile-form">
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" required>
        </div>

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" required>
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required>
        </div>

        <button type="submit" name="update_password">Change Password</button>
    </form>

</div>

</body>
</html>
