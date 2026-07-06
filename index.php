<?php
require_once __DIR__ . '/auth/session_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Exam System</title>
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h1>
    <p>Role: <?= htmlspecialchars($_SESSION['role']) ?></p>
    <a href="auth/logout.php">Logout</a>
</body>
</html>
