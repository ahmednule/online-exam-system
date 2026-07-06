<?php
require_once __DIR__ . '/../config/db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $admission_no = trim($_POST['admission_no'] ?? '');
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'] ?? 'student';

    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Full name, email, and password are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? OR (admission_no IS NOT NULL AND admission_no = ?)");
            $stmt->execute([$email, $admission_no]);

            if ($stmt->fetch()) {
                $error = 'Email or Admission No already exists.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, admission_no, email, password, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$full_name, $admission_no ?: null, $email, $hashed_password, $role]);
                $success = 'Registration successful. You can now login.';
            }
        } catch (PDOException $e) {
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label>Full Name:</label>
        <input type="text" name="full_name" required><br>

        <label>Admission No:</label>
        <input type="text" name="admission_no"><br>

        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Password:</label>
        <input type="password" name="password" required><br>

        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required><br>

        <label>Role:</label>
        <select name="role">
            <option value="student">Student</option>
            <option value="admin">Admin</option>
        </select><br>

        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>
