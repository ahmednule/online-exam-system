<?php
require_once __DIR__ . '/../../config/db.php';

$courseCount = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$studentCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$attemptCount = $pdo->query("SELECT COUNT(*) FROM exam_attempts")->fetchColumn();
$questionCount = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Admin Dashboard</h4>
        <p class="text-muted mb-0 small">Manage your examination platform</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card dash-stat">
            <div class="card-body">
                <div class="dash-stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-book"></i>
                </div>
                <div class="dash-stat-info">
                    <h3><?= $courseCount ?></h3>
                    <span>Courses</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dash-stat">
            <div class="card-body">
                <div class="dash-stat-icon bg-info-subtle text-info">
                    <i class="bi bi-question-circle"></i>
                </div>
                <div class="dash-stat-info">
                    <h3><?= $questionCount ?></h3>
                    <span>Questions</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dash-stat">
            <div class="card-body">
                <div class="dash-stat-icon bg-success-subtle text-success">
                    <i class="bi bi-people"></i>
                </div>
                <div class="dash-stat-info">
                    <h3><?= $studentCount ?></h3>
                    <span>Students</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dash-stat">
            <div class="card-body">
                <div class="dash-stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-clipboard-data"></i>
                </div>
                <div class="dash-stat-info">
                    <h3><?= $attemptCount ?></h3>
                    <span>Total Attempts</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">Quick Links</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="?page=courses" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-book d-block fs-3 mb-1"></i>
                            Courses
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="?page=units" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-journal-text d-block fs-3 mb-1"></i>
                            Units
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="?page=questions" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-question-circle d-block fs-3 mb-1"></i>
                            Questions
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="?page=analytics" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-graph-up d-block fs-3 mb-1"></i>
                            Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">Platform Overview</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small>Total Users</small>
                        <small class="fw-bold"><?= $userCount ?></small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: 100%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small>Students</small>
                        <small class="fw-bold"><?= $studentCount ?></small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <?php $pct = $userCount > 0 ? round($studentCount / $userCount * 100) : 0; ?>
                        <div class="progress-bar bg-success" style="width: <?= $pct ?>%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small>Questions in Bank</small>
                        <small class="fw-bold"><?= $questionCount ?></small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: <?= min($questionCount * 5, 100) ?>%"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small>Exam Attempts</small>
                        <small class="fw-bold"><?= $attemptCount ?></small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: <?= min($attemptCount * 10, 100) ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
