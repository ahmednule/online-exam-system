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
    $full_name = trim($_POST['full_name']);
    $admission_no = trim($_POST['admission_no'] ?? '');
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $course_id = $_POST['course_id'] ?? null;
    $role = $_POST['role'] ?? 'student';

    if (empty($full_name) || empty($email) || empty($password) || empty($course_id)) {
        $error = 'Full name, email, password, and course are required.';
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
                $pdo->beginTransaction();
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, admission_no, email, password, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$full_name, $admission_no ?: null, $email, $hashed_password, $role]);
                $user_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO student_courses (user_id, course_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $course_id]);
                $pdo->commit();
                $success = 'Registration successful. You can now log in.';
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}

$courses = $pdo->query("SELECT course_id, course_name FROM courses ORDER BY course_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Exam System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="register-wrapper">
        <div class="register-container">
            <!-- Left Panel -->
            <div class="register-panel">
                <div class="register-panel-content">
                    <div class="mb-4">
                        <a href="../index.php" class="text-white text-decoration-none">
                            <i class="bi bi-laptop"></i> Online Exam System
                        </a>
                    </div>
                    <h2>Start Your Journey</h2>
                    <p class="lead">Join thousands of students taking exams online. Register now and get instant access to your courses.</p>
                    <div class="register-benefits">
                        <div class="benefit-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Timed exams with randomized questions</span>
                        </div>
                        <div class="benefit-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Instant scoring & answer review</span>
                        </div>
                        <div class="benefit-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Track your performance over time</span>
                        </div>
                        <div class="benefit-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Secure & reliable platform</span>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <p class="mb-1 small opacity-75">Already have an account?</p>
                        <a href="login.php" class="btn btn-outline-light btn-sm px-4">Sign In</a>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Form -->
            <div class="register-form">
                <div class="register-form-inner">
                    <div class="text-center mb-4">
                        <h3>Create Account</h3>
                        <p class="text-muted">Fill in your details to get started</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success py-2 text-center"><?= htmlspecialchars($success) ?></div>
                        <div class="text-center mt-3"><a href="login.php" class="btn btn-primary">Log In</a></div>
                    <?php endif; ?>

                    <?php if (!$success): ?>
                    <form method="POST" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="full_name" class="form-control" placeholder="John Doe" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Admission No. <span class="text-muted fw-normal">(optional)</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                    <input type="text" name="admission_no" class="form-control" placeholder="ADM-001" value="<?= htmlspecialchars($_POST['admission_no'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Course</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-book"></i></span>
                                    <select name="course_id" class="form-select" required>
                                        <option value="">Select Course</option>
                                        <?php foreach ($courses as $course): ?>
                                            <option value="<?= $course['course_id'] ?>" <?= (($_POST['course_id'] ?? '') == $course['course_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($course['course_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <select name="role" class="form-select">
                                        <option value="student" selected>Student</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-4 py-2 fw-semibold">
                            <i class="bi bi-person-plus"></i> Create Account
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
