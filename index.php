<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Exam System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="page-wrap">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-laptop"></i> Online Exam System
            </a>
            <div>
                <a href="auth/login.php" class="btn btn-outline-light btn-sm me-2">Log In</a>
                <a href="auth/register.php" class="btn btn-primary btn-sm">Register</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero-section text-center">
        <div class="container">
            <h1>Online Examination Platform</h1>
            <p class="lead mt-3 mb-4">A streamlined exam management system for students and administrators.</p>
            <div>
                <a href="auth/register.php" class="btn btn-light btn-lg px-4 me-2">Get Started</a>
                <a href="auth/login.php" class="btn btn-outline-light btn-lg px-4">Log In</a>
            </div>
        </div>
    </section>

    <!-- Roles -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-md-5">
                    <div class="card role-card shadow-sm p-4 text-center">
                        <div class="role-icon text-primary"><i class="bi bi-mortarboard-fill"></i></div>
                        <h4>Student</h4>
                        <p class="text-muted">Register under your course, take timed exams with randomized questions, and view your scores instantly.</p>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card role-card shadow-sm p-4 text-center">
                        <div class="role-icon text-warning"><i class="bi bi-shield-fill-check"></i></div>
                        <h4>Admin</h4>
                        <p class="text-muted">Manage the question bank per course and unit, view student attempts, and track performance analytics.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-5 bg-white">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">How It Works</h2>
            <div class="row g-4">
                <div class="col-md-3 text-center">
                    <div class="step-number mx-auto">1</div>
                    <h5>Register</h5>
                    <p class="text-muted small">Create an account and select your course.</p>
                </div>
                <div class="col-md-3 text-center">
                    <div class="step-number mx-auto">2</div>
                    <h5>Log In</h5>
                    <p class="text-muted small">Sign in and pick a unit to attempt.</p>
                </div>
                <div class="col-md-3 text-center">
                    <div class="step-number mx-auto">3</div>
                    <h5>Take Exam</h5>
                    <p class="text-muted small">Answer randomly selected questions within the time limit.</p>
                </div>
                <div class="col-md-3 text-center">
                    <div class="step-number mx-auto">4</div>
                    <h5>Get Results</h5>
                    <p class="text-muted small">View your score and review answers immediately.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer bg-dark text-white text-center py-3">
        <small>&copy; 2026 Online Exam System. All rights reserved.</small>
    </footer>

    </div> <!-- /page-wrap -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
