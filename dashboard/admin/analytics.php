<?php
require_once __DIR__ . '/../../config/db.php';

$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalAttempts = $pdo->query("SELECT COUNT(*) FROM exam_attempts")->fetchColumn();
$completedAttempts = $pdo->query("SELECT COUNT(*) FROM exam_attempts WHERE status = 'completed'")->fetchColumn();

$avgScore = $pdo->query("SELECT ROUND(AVG(score_percent), 1) FROM exam_attempts WHERE status = 'completed'")->fetchColumn();
$passCount = $pdo->query("SELECT COUNT(*) FROM exam_attempts WHERE status = 'completed' AND score_percent >= 50")->fetchColumn();
$passRate = $completedAttempts > 0 ? round($passCount / $completedAttempts * 100) : 0;

// Per-course stats
$courseStats = $pdo->query("
    SELECT c.course_name,
        COUNT(DISTINCT ea.attempt_id) AS total_attempts,
        COUNT(DISTINCT ea.user_id) AS unique_students,
        ROUND(AVG(ea.score_percent), 1) AS avg_score
    FROM courses c
    LEFT JOIN units u ON u.course_id = c.course_id
    LEFT JOIN exam_attempts ea ON ea.unit_id = u.unit_id AND ea.status = 'completed'
    GROUP BY c.course_id
    ORDER BY total_attempts DESC
")->fetchAll();

// Top students
$topStudents = $pdo->query("
    SELECT u.full_name, u.admission_no, COUNT(ea.attempt_id) AS attempts,
        ROUND(AVG(ea.score_percent), 1) AS avg_score,
        MAX(ea.score_percent) AS best_score
    FROM exam_attempts ea
    JOIN users u ON u.user_id = ea.user_id
    WHERE ea.status = 'completed'
    GROUP BY ea.user_id
    ORDER BY avg_score DESC
    LIMIT 5
")->fetchAll();
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Analytics</h4>
        <p class="text-muted mb-0 small">Performance overview and insights</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card dash-stat">
            <div class="card-body">
                <div class="dash-stat-icon bg-primary-subtle text-primary"><i class="bi bi-people"></i></div>
                <div class="dash-stat-info">
                    <h3><?= $totalStudents ?></h3>
                    <span>Students</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dash-stat">
            <div class="card-body">
                <div class="dash-stat-icon bg-info-subtle text-info"><i class="bi bi-clipboard-data"></i></div>
                <div class="dash-stat-info">
                    <h3><?= $totalAttempts ?></h3>
                    <span>Total Attempts</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dash-stat">
            <div class="card-body">
                <div class="dash-stat-icon bg-warning-subtle text-warning"><i class="bi bi-trophy"></i></div>
                <div class="dash-stat-info">
                    <h3><?= $avgScore ?: '—' ?><?= $avgScore ? '%' : '' ?></h3>
                    <span>Avg Score</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dash-stat">
            <div class="card-body">
                <div class="dash-stat-icon bg-success-subtle text-success"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="dash-stat-info">
                    <h3><?= $passRate ?>%</h3>
                    <span>Pass Rate</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">Per-Course Performance</h6>
            </div>
            <div class="card-body">
                <?php if (empty($courseStats)): ?>
                    <p class="text-muted text-center py-3 mb-0">No data available yet.</p>
                <?php else: ?>
                    <?php foreach ($courseStats as $cs): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <span class="fw-medium small"><?= htmlspecialchars($cs['course_name']) ?></span>
                                <span class="text-muted" style="font-size:0.75rem;"> — <?= $cs['unique_students'] ?> students, <?= $cs['total_attempts'] ?> attempts</span>
                            </div>
                            <span class="fw-bold small"><?= $cs['avg_score'] ?: '—' ?>%</span>
                        </div>
                        <?php $barWidth = min($cs['avg_score'] ?? 0, 100); ?>
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar bg-<?= $barWidth >= 50 ? 'success' : 'danger' ?>" style="width:<?= $barWidth ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">Top Performing Students</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($topStudents)): ?>
                    <p class="text-muted text-center py-4 mb-0">No data available yet.</p>
                <?php else: ?>
                    <table class="table table-borderless align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th class="text-center">Exams</th>
                                <th class="text-center">Avg</th>
                                <th class="text-center">Best</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topStudents as $i => $s): ?>
                            <tr>
                                <td class="fw-bold"><?= $i + 1 ?></td>
                                <td class="small">
                                    <div class="fw-medium"><?= htmlspecialchars($s['full_name']) ?></div>
                                    <div class="text-muted" style="font-size:0.7rem;"><?= htmlspecialchars($s['admission_no'] ?? '') ?></div>
                                </td>
                                <td class="text-center"><?= $s['attempts'] ?></td>
                                <td class="text-center fw-medium text-success"><?= $s['avg_score'] ?>%</td>
                                <td class="text-center fw-medium text-warning"><?= $s['best_score'] ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
