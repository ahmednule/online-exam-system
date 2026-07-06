<?php
require_once __DIR__ . '/../config/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard.php');
    exit;
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('Invalid reset link.');
}

// Validate token
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    $error = 'This reset link is invalid or has expired.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($password)) {
        $error = 'Please enter a new password.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashed, $reset['email']]);
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$reset['email']]);
            $success = 'Password has been reset successfully. You can now log in.';
        } catch (PDOException $e) {
            $error = 'Failed to reset password: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Online Exam System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card card">
            <div class="card-body">
                <div class="auth-header">
                    <i class="bi bi-key-fill text-primary" style="font-size: 2.5rem;"></i>
                    <h3>Reset Password</h3>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success py-2"><?= htmlspecialchars($success) ?></div>
                    <div class="text-center mt-3"><a href="login.php" class="btn btn-primary">Log In</a></div>
                <?php endif; ?>

                <?php if (!$success && $reset): ?>
                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                </form>
                <?php endif; ?>

                <p class="text-center text-muted small mt-3 mb-0">
                    <a href="login.php">Back to Log In</a>
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
