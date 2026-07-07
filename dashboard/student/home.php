<?php
require_once __DIR__ . '/../../config/db.php';

// Get student's course
$stmt = $pdo->prepare("
    SELECT c.course_name, c.description
    FROM student_courses sc
    JOIN courses c ON c.course_id = sc.course_id
    WHERE sc.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$course = $stmt->fetch();

// Count units available
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM units u
    JOIN student_courses sc ON sc.course_id = u.course_id
    WHERE sc.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$unitCount = $stmt->fetchColumn();

// Count past attempts
$stmt = $pdo->prepare("SELECT COUNT(*) FROM exam_attempts WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$_SESSION['user_id']]);
$attemptCount = $stmt->fetchColumn();

// Avg score
$stmt = $pdo->prepare("SELECT ROUND(AVG(score_percent), 1) FROM exam_attempts WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$_SESSION['user_id']]);
$avgScore = $stmt->fetchColumn();
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?> 👋</h4>
        <p class="text-muted mb-0 small"><?= htmlspecialchars($course['course_name'] ?? 'No course assigned') ?></p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card dash-stat">
            <div class="card-body">
                <div class="dash-stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div class="dash-stat-info">
                    <h3><?= $unitCount ?></h3>
                    <span>Units Available</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dash-stat">
            <div class="card-body">
                <div class="dash-stat-icon bg-success-subtle text-success">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <div class="dash-stat-info">
                    <h3><?= $attemptCount ?></h3>
                    <span>Exams Taken</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dash-stat">
            <div class="card-body">
                <div class="dash-stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-trophy"></i>
                </div>
                <div class="dash-stat-info">
                    <h3><?= $avgScore ?: '—' ?><?= $avgScore ? '%' : '' ?></h3>
                    <span>Average Score</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold">Quick Actions</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <a href="?page=units" class="btn btn-outline-primary w-100 py-3">
                    <i class="bi bi-play-circle d-block fs-3 mb-1"></i>
                    Take an Exam
                </a>
            </div>
            <div class="col-md-4">
                <a href="?page=results" class="btn btn-outline-success w-100 py-3">
                    <i class="bi bi-trophy d-block fs-3 mb-1"></i>
                    View Results
                </a>
            </div>
            <div class="col-md-4">
                <a href="?page=profile" class="btn btn-outline-secondary w-100 py-3">
                    <i class="bi bi-person-circle d-block fs-3 mb-1"></i>
                    My Profile
                </a>
            </div>
        </div>
    </div>
</div>
