<?php
require_once __DIR__ . '/auth/session_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Online Exam System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-laptop"></i> Online Exam System</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3 small"><?= htmlspecialchars($_SESSION['full_name']) ?> (<?= $_SESSION['role'] ?>)</span>
                <a href="auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="alert alert-info">
            <h4 class="alert-heading">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h4>
            <p class="mb-0">You are logged in as <strong><?= $_SESSION['role'] ?></strong>. Dashboard pages are coming next.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
