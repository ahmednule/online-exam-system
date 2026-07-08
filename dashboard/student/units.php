<?php
require_once __DIR__ . '/../../config/db.php';

// Get student's course
$stmt = $pdo->prepare("
    SELECT c.course_id, c.course_name
    FROM student_courses sc
    JOIN courses c ON c.course_id = sc.course_id
    WHERE sc.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$course = $stmt->fetch();

$units = [];
if ($course) {
    $stmt = $pdo->prepare("
        SELECT u.*, (SELECT COUNT(*) FROM questions WHERE unit_id = u.unit_id) AS question_count
        FROM units u WHERE u.course_id = ? ORDER BY u.unit_name
    ");
    $stmt->execute([$course['course_id']]);
    $units = $stmt->fetchAll();

    // Get attempt status for each unit
    $stmt = $pdo->prepare("
        SELECT unit_id, status, score_percent
        FROM exam_attempts
        WHERE user_id = ? AND unit_id IN (
            SELECT unit_id FROM units WHERE course_id = ?
        )
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id'], $course['course_id']]);
    $attempts = $stmt->fetchAll();

    $unitStatus = [];
    foreach ($attempts as $a) {
        if (!isset($unitStatus[$a['unit_id']])) {
            $unitStatus[$a['unit_id']] = [
                'status' => $a['status'],
                'score' => $a['score_percent']
            ];
        }
    }
}
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">My Units</h4>
        <p class="text-muted mb-0 small"><?= htmlspecialchars($course['course_name'] ?? 'No course assigned') ?></p>
    </div>
</div>

<?php if (!$course): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-exclamation-circle text-warning fs-1 d-block mb-3"></i>
            <h6>No Course Assigned</h6>
            <p class="text-muted small mb-0">Please contact your administrator to assign you to a course.</p>
        </div>
    </div>
<?php elseif (empty($units)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-journal-text text-muted fs-1 d-block mb-3"></i>
            <h6>No Units Available</h6>
            <p class="text-muted small mb-0">There are no units set up for your course yet.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($units as $u): ?>
            <?php
                $status = $unitStatus[$u['unit_id']] ?? null;
                $badge = '';
                $btnClass = 'btn-primary';
                $btnText = '<i class="bi bi-play-circle"></i> Take Exam';

                if ($status) {
                    if ($status['status'] === 'in_progress') {
                        $badge = '<span class="badge bg-warning">In Progress</span>';
                        $btnClass = 'btn-warning';
                        $btnText = '<i class="bi bi-arrow-right-circle"></i> Resume';
                    } else {
                        $badge = '<span class="badge bg-success">Completed — ' . $status['score'] . '%</span>';
                        $btnClass = 'btn-outline-secondary';
                        $btnText = '<i class="bi bi-check-circle"></i> Retake';
                    }
                } else {
                    $badge = '<span class="badge bg-secondary-subtle text-secondary">Not Started</span>';
                }
            ?>
            <div class="col-md-6">
                <div class="card unit-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <h5 class="card-title fw-semibold mb-0"><?= htmlspecialchars($u['unit_name']) ?></h5>
                            <?= $badge ?>
                        </div>
                        <p class="card-text text-muted small mb-3"><?= htmlspecialchars($u['description'] ?? 'No description') ?></p>
                        <div class="d-flex gap-3 mb-3 small text-muted">
                            <span><i class="bi bi-clock"></i> <?= $u['duration_minutes'] ?> min</span>
                            <span><i class="bi bi-question-circle"></i> <?= $u['question_count'] ?> questions</span>
                        </div>
                        <a href="exam.php?unit_id=<?= $u['unit_id'] ?>" class="btn <?= $btnClass ?> btn-sm w-100">
                            <?= $btnText ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
