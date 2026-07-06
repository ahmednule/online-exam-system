<?php
require_once __DIR__ . '/../config/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expires]);

            $resetLink = "http://{$_SERVER['HTTP_HOST']}/online-exam-system/auth/reset_password.php?token=$token";
            $success = "Your password reset link is ready. <br> <a href=\"$resetLink\" class=\"fw-bold\">Click here to reset your password</a>";
            $success .= "<br><small class=\"text-muted\">(In production, this link would be emailed to you.)</small>";
        } else {
            $error = 'No account found with that email address.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Online Exam System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card card">
            <div class="card-body">
                <div class="auth-header">
                    <i class="bi bi-shield-lock-fill text-primary" style="font-size: 2.5rem;"></i>
                    <h3>Forgot Password</h3>
                    <p class="text-muted small">Enter your email to receive a reset link</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success py-2"><?= $success ?></div>
                    <div class="text-center mt-3"><a href="login.php" class="btn btn-primary">Back to Log In</a></div>
                <?php endif; ?>

                <?php if (!$success): ?>
                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                </form>
                <p class="text-center text-muted small mt-3 mb-0">
                    <a href="login.php">Back to Log In</a>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
