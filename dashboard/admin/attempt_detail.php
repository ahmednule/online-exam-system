<?php
require_once __DIR__ . '/../../config/db.php';

$attempt_id = (int)($_GET['attempt_id'] ?? 0);
if (!$attempt_id) {
    echo '<div class="alert alert-danger">Invalid attempt.</div>';
    exit;
}

// Get attempt info
$stmt = $pdo->prepare("
    SELECT ea.*, u.full_name, u.admission_no, un.unit_name, c.course_name
    FROM exam_attempts ea
    JOIN users u ON u.user_id = ea.user_id
    JOIN units un ON un.unit_id = ea.unit_id
    JOIN courses c ON c.course_id = un.course_id
    WHERE ea.attempt_id = ?
");
$stmt->execute([$attempt_id]);
$a = $stmt->fetch();

if (!$a) {
    echo '<div class="alert alert-danger">Attempt not found.</div>';
    exit;
}

// Get answers with questions
$stmt = $pdo->prepare("
    SELECT ea.*, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option
    FROM exam_answers ea
    JOIN questions q ON q.question_id = ea.question_id
    WHERE ea.attempt_id = ?
    ORDER BY ea.answer_id
");
$stmt->execute([$attempt_id]);
$answers = $stmt->fetchAll();
?>

<div class="mb-3">
    <div class="row g-2 small">
        <div class="col-md-6"><strong>Student:</strong> <?= htmlspecialchars($a['full_name']) ?> (<?= htmlspecialchars($a['admission_no'] ?? 'N/A') ?>)</div>
        <div class="col-md-6"><strong>Unit:</strong> <?= htmlspecialchars($a['unit_name']) ?> — <?= htmlspecialchars($a['course_name']) ?></div>
        <div class="col-md-6"><strong>Score:</strong> <?= $a['score'] ?> / <?= $a['total_questions'] ?> (<?= $a['score_percent'] ?>%)</div>
        <div class="col-md-6"><strong>Date:</strong> <?= date('M j, Y g:i A', strtotime($a['created_at'])) ?></div>
    </div>
    <hr>
</div>

<?php if (empty($answers)): ?>
    <p class="text-muted text-center py-3">No answer data available.</p>
<?php else: ?>
    <?php foreach ($answers as $i => $ans): ?>
        <?php $is_correct = $ans['is_correct']; ?>
        <div class="mb-3 p-3 rounded border border-<?= $is_correct ? 'success' : 'danger' ?> bg-<?= $is_correct ? 'success' : 'danger' ?>-subtle">
            <div class="fw-medium small mb-2">Q<?= $i + 1 ?>. <?= htmlspecialchars($ans['question_text']) ?></div>
            <div class="row g-1 small">
                <div class="col-6"><span class="text-<?= $ans['correct_option'] === 'a' ? 'success fw-bold' : '' ?>">A. <?= htmlspecialchars($ans['option_a']) ?></span></div>
                <div class="col-6"><span class="text-<?= $ans['correct_option'] === 'b' ? 'success fw-bold' : '' ?>">B. <?= htmlspecialchars($ans['option_b']) ?></span></div>
                <div class="col-6"><span class="text-<?= $ans['correct_option'] === 'c' ? 'success fw-bold' : '' ?>">C. <?= htmlspecialchars($ans['option_c']) ?></span></div>
                <div class="col-6"><span class="text-<?= $ans['correct_option'] === 'd' ? 'success fw-bold' : '' ?>">D. <?= htmlspecialchars($ans['option_d']) ?></span></div>
            </div>
            <div class="mt-2 small">
                <span class="fw-bold">Selected:</span>
                <?php if ($ans['selected_option']): ?>
                    <span class="text-uppercase text-<?= $is_correct ? 'success' : 'danger' ?> fw-bold"><?= $ans['selected_option'] ?></span>
                <?php else: ?>
                    <span class="text-muted fst-italic">No answer</span>
                <?php endif; ?>
                <span class="mx-2">|</span>
                <span class="fw-bold">Correct:</span>
                <span class="text-uppercase text-success fw-bold"><?= $ans['correct_option'] ?></span>
                <span class="ms-2">
                    <?php if ($is_correct): ?>
                        <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle"></i> Correct</span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger"><i class="bi bi-x-circle"></i> Wrong</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
