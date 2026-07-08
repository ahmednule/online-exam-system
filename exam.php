<?php
require_once __DIR__ . '/auth/session_check.php';
require_once __DIR__ . '/config/db.php';

$unit_id = (int)($_GET['unit_id'] ?? 0);
$view_attempt = (int)($_GET['attempt_id'] ?? 0);

// ── Handle POST: Submit Exam ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit') {
    $attempt_id = (int)$_POST['attempt_id'];
    $stmt = $pdo->prepare("SELECT * FROM exam_attempts WHERE attempt_id = ? AND user_id = ? AND status = 'in_progress'");
    $stmt->execute([$attempt_id, $_SESSION['user_id']]);
    $attempt = $stmt->fetch();

    if ($attempt) {
        $answers = $_POST['answer'] ?? [];
        $total = $attempt['total_questions'];
        $score = 0;

        foreach ($answers as $question_id => $selected) {
            $selected = substr(trim($selected), 0, 1);
            if (!in_array($selected, ['a','b','c','d'])) $selected = null;

            $qStmt = $pdo->prepare("SELECT correct_option FROM questions WHERE question_id = ?");
            $qStmt->execute([$question_id]);
            $q = $qStmt->fetch();
            $is_correct = ($q && $selected === $q['correct_option']) ? 1 : 0;
            if ($is_correct) $score++;

            $pdo->prepare("UPDATE exam_answers SET selected_option = ?, is_correct = ? WHERE attempt_id = ? AND question_id = ?")
                ->execute([$selected, $is_correct, $attempt_id, $question_id]);
        }

        $percent = $total > 0 ? round($score / $total * 100, 2) : 0;
        $pdo->prepare("UPDATE exam_attempts SET score = ?, score_percent = ?, status = 'completed', end_time = NOW() WHERE attempt_id = ?")
            ->execute([$score, $percent, $attempt_id]);

        // Create notification
        $stmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $uname = $stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT unit_name FROM units WHERE unit_id = ?");
        $stmt->execute([$attempt['unit_id']]);
        $uname2 = $stmt->fetchColumn();
        $pdo->prepare("INSERT INTO notifications (type, title, message, link) VALUES ('exam_completed', 'Exam Completed', ?, '?page=attempts')")
            ->execute(["$uname completed $uname2 exam with {$percent}%."]);

        header("Location: exam.php?attempt_id=$attempt_id");
        exit;
    }
}

// ── View completed attempt results ──
if ($view_attempt) {
    $stmt = $pdo->prepare("
        SELECT ea.*, u.unit_name, u.duration_minutes, c.course_name
        FROM exam_attempts ea
        JOIN units u ON u.unit_id = ea.unit_id
        JOIN courses c ON c.course_id = u.course_id
        WHERE ea.attempt_id = ? AND ea.user_id = ?
    ");
    $stmt->execute([$view_attempt, $_SESSION['user_id']]);
    $attempt = $stmt->fetch();

    if (!$attempt || $attempt['status'] !== 'completed') {
        header('Location: dashboard.php');
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT ea.*, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option
        FROM exam_answers ea
        JOIN questions q ON q.question_id = ea.question_id
        WHERE ea.attempt_id = ?
        ORDER BY ea.answer_id
    ");
    $stmt->execute([$view_attempt]);
    $answers = $stmt->fetchAll();

    $passed = $attempt['score_percent'] >= 50;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results - Online Exam System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container py-4" style="max-width: 800px;">
        <div class="text-center mb-4">
            <a href="dashboard.php" class="text-decoration-none text-muted small">&larr; Back to Dashboard</a>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center py-4">
                <div class="display-1 mb-2"><?= $passed ? '🎉' : '😔' ?></div>
                <h3 class="fw-bold"><?= $passed ? 'Congratulations!' : 'Keep Trying!' ?></h3>
                <p class="text-muted"><?= htmlspecialchars($attempt['unit_name']) ?> — <?= htmlspecialchars($attempt['course_name']) ?></p>
                <div class="d-flex justify-content-center gap-4 mt-3">
                    <div>
                        <div class="fs-1 fw-bold text-<?= $passed ? 'success' : 'danger' ?>"><?= $attempt['score_percent'] ?>%</div>
                        <small class="text-muted">Score</small>
                    </div>
                    <div class="border-start ps-4">
                        <div class="fs-1 fw-bold"><?= $attempt['score'] ?>/<?= $attempt['total_questions'] ?></div>
                        <small class="text-muted">Correct Answers</small>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-<?= $passed ? 'success' : 'danger' ?>-subtle text-<?= $passed ? 'success' : 'danger' ?> fs-6 px-3 py-2">
                        <?= $passed ? 'PASSED' : 'FAILED' ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Answer Review</div>
            <div class="card-body p-3">
                <?php foreach ($answers as $i => $ans): ?>
                <?php $correct = $ans['is_correct']; ?>
                <div class="mb-3 p-3 rounded border border-<?= $correct ? 'success' : 'danger' ?> bg-<?= $correct ? 'success' : 'danger' ?>-subtle">
                    <div class="fw-medium small mb-2">Q<?= $i+1 ?>. <?= htmlspecialchars($ans['question_text']) ?></div>
                    <div class="row g-1 small">
                        <?php foreach (['a','b','c','d'] as $opt): ?>
                        <div class="col-6">
                            <span class="<?= $ans['correct_option'] === $opt ? 'fw-bold text-success' : '' ?>">
                                <?= strtoupper($opt) ?>. <?= htmlspecialchars($ans["option_$opt"]) ?>
                                <?php if ($ans['correct_option'] === $opt): ?><i class="bi bi-check-circle-fill text-success ms-1"></i><?php endif; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-2 small">
                        <span class="fw-bold">Your answer:</span>
                        <span class="text-uppercase fw-bold text-<?= $correct ? 'success' : 'danger' ?>">
                            <?= $ans['selected_option'] ? strtoupper($ans['selected_option']) : 'No answer' ?>
                        </span>
                        <span class="ms-3">
                            <?php if ($correct): ?>
                                <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle"></i> Correct</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger"><i class="bi bi-x-circle"></i> Wrong</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="text-center mt-4 mb-5">
            <a href="dashboard.php?page=results" class="btn btn-primary">View All Results</a>
            <a href="dashboard.php?page=units" class="btn btn-outline-secondary ms-2">Back to Units</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    exit;
}

// ── Start or Resume Exam ──
if (!$unit_id) {
    header('Location: dashboard.php');
    exit;
}

// Check unit exists and belongs to student's course
$stmt = $pdo->prepare("
    SELECT u.* FROM units u
    JOIN student_courses sc ON sc.course_id = u.course_id
    WHERE u.unit_id = ? AND sc.user_id = ?
");
$stmt->execute([$unit_id, $_SESSION['user_id']]);
$unit = $stmt->fetch();

if (!$unit) {
    header('Location: dashboard.php');
    exit;
}

// Check for existing in_progress attempt
$stmt = $pdo->prepare("SELECT * FROM exam_attempts WHERE user_id = ? AND unit_id = ? AND status = 'in_progress'");
$stmt->execute([$_SESSION['user_id'], $unit_id]);
$attempt = $stmt->fetch();

if (!$attempt) {
    // Create new attempt with random questions
    $stmt = $pdo->prepare("SELECT question_id FROM questions WHERE unit_id = ? ORDER BY RAND()");
    $stmt->execute([$unit_id]);
    $question_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($question_ids)) {
        header('Location: dashboard.php?page=units');
        exit;
    }

    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO exam_attempts (user_id, unit_id, start_time, total_questions, status) VALUES (?, ?, NOW(), ?, 'in_progress')");
    $stmt->execute([$_SESSION['user_id'], $unit_id, count($question_ids)]);
    $attempt_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO exam_answers (attempt_id, question_id) VALUES (?, ?)");
    foreach ($question_ids as $qid) {
        $stmt->execute([$attempt_id, $qid]);
    }
    $pdo->commit();

    $stmt = $pdo->prepare("SELECT * FROM exam_attempts WHERE attempt_id = ?");
    $stmt->execute([$attempt_id]);
    $attempt = $stmt->fetch();
}

$attempt_id = $attempt['attempt_id'];

// Get questions for this attempt
$stmt = $pdo->prepare("
    SELECT ea.answer_id, ea.selected_option, q.question_id, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d
    FROM exam_answers ea
    JOIN questions q ON q.question_id = ea.question_id
    WHERE ea.attempt_id = ?
    ORDER BY ea.answer_id
");
$stmt->execute([$attempt_id]);
$questions = $stmt->fetchAll();

$total_q = count($questions);
$duration = $unit['duration_minutes'];
$end_time = strtotime($attempt['start_time']) + ($duration * 60);
$remaining_seconds = max(0, $end_time - time());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam - <?= htmlspecialchars($unit['unit_name']) ?> - Online Exam System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <!-- Top bar -->
    <header class="bg-white shadow-sm border-bottom sticky-top">
        <div class="container d-flex align-items-center justify-content-between py-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-laptop text-primary fs-5"></i>
                <span class="fw-bold small">Online Exam</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-center">
                    <div class="small text-muted"><?= htmlspecialchars($unit['unit_name']) ?></div>
                    <div class="small fw-bold" id="qCounter">Question <span id="currentQ">1</span> of <?= $total_q ?></div>
                </div>
                <div class="text-center px-3 border-start border-end">
                    <div class="small text-muted">Time Remaining</div>
                    <div class="fw-bold fs-5 <?= $remaining_seconds <= 300 ? 'text-danger' : 'text-success' ?>" id="timer">
                        <?= gmdate('i:s', $remaining_seconds) ?>
                    </div>
                </div>
                <button class="btn btn-danger btn-sm" onclick="confirmSubmit()">
                    <i class="bi bi-check-lg"></i> Submit
                </button>
            </div>
        </div>
    </header>

    <div class="container py-4" style="max-width: 900px;">
        <form id="examForm" method="POST">
            <input type="hidden" name="action" value="submit">
            <input type="hidden" name="attempt_id" value="<?= $attempt_id ?>">

            <!-- Question Navigator -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-2 d-flex flex-wrap gap-1" id="qNav">
                    <?php for ($i = 0; $i < $total_q; $i++): ?>
                    <button type="button" class="btn btn-sm q-nav-btn btn-outline-secondary" data-q="<?= $i + 1 ?>" onclick="goToQ(<?= $i + 1 ?>)">
                        <?= $i + 1 ?>
                    </button>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Questions -->
            <?php foreach ($questions as $i => $q): ?>
            <div class="question-card card border-0 shadow-sm mb-3" id="qCard<?= $i + 1 ?>" style="<?= $i === 0 ? '' : 'display:none' ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="fw-semibold mb-0">Question <?= $i + 1 ?> of <?= $total_q ?></h6>
                    </div>
                    <p class="mb-3"><?= htmlspecialchars($q['question_text']) ?></p>

                    <div class="d-flex flex-column gap-2">
                        <?php foreach (['a' => $q['option_a'], 'b' => $q['option_b'], 'c' => $q['option_c'], 'd' => $q['option_d']] as $key => $val): ?>
                        <label class="option-label d-flex align-items-center gap-3 p-3 border rounded <?= $q['selected_option'] === $key ? 'border-primary bg-primary-subtle' : '' ?>">
                            <input type="radio" name="answer[<?= $q['question_id'] ?>]" value="<?= $key ?>" class="form-check-input mt-0" <?= $q['selected_option'] === $key ? 'checked' : '' ?> onchange="markAnswered(<?= $i + 1 ?>)">
                            <span><span class="fw-medium"><?= strtoupper($key) ?>.</span> <?= htmlspecialchars($val) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Bottom Navigation -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <button type="button" class="btn btn-outline-secondary" onclick="prevQ()" id="prevBtn"><i class="bi bi-chevron-left"></i> Previous</button>
                <button type="button" class="btn btn-outline-secondary" onclick="nextQ()" id="nextBtn">Next <i class="bi bi-chevron-right"></i></button>
            </div>
        </form>
    </div>

    <!-- Submit Confirmation Modal -->
    <div class="modal fade" id="submitModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-question-circle text-warning fs-1 d-block mb-2"></i>
                    <h6 class="fw-bold">Submit Exam?</h6>
                    <p class="small text-muted mb-0" id="submitStatus">You have answered <span id="answeredCount">0</span> of <?= $total_q ?> questions.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Review</button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('examForm').submit()">Submit Now</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    var totalQ = <?= $total_q ?>;
    var currentQ = 1;
    var answered = {};

    // Track answered questions
    document.querySelectorAll('input[type=radio]:checked').forEach(function(el) {
        var name = el.getAttribute('name');
        var idx = document.querySelector('[name="' + name + '"]').closest('.question-card');
        if (idx) {
            var num = parseInt(idx.id.replace('qCard', ''));
            answered[num] = true;
        }
    });

    // Update answered on change
    document.querySelectorAll('input[type=radio]').forEach(function(el) {
        el.addEventListener('change', function() {
            var card = this.closest('.question-card');
            var num = parseInt(card.id.replace('qCard', ''));
            answered[num] = true;
            updateNav();
        });
    });

    function markAnswered(num) {
        answered[num] = true;
        updateNav();
    }

    function goToQ(num) {
        document.getElementById('qCard' + currentQ).style.display = 'none';
        document.getElementById('qCard' + num).style.display = '';
        currentQ = num;
        updateNav();
    }

    function nextQ() {
        if (currentQ < totalQ) goToQ(currentQ + 1);
    }

    function prevQ() {
        if (currentQ > 1) goToQ(currentQ - 1);
    }

    function updateNav() {
        document.getElementById('currentQ').textContent = currentQ;
        document.getElementById('prevBtn').style.visibility = currentQ > 1 ? 'visible' : 'hidden';
        document.getElementById('nextBtn').style.visibility = currentQ < totalQ ? 'visible' : 'hidden';

        document.querySelectorAll('.q-nav-btn').forEach(function(btn) {
            var num = parseInt(btn.dataset.q);
            btn.classList.remove('btn-primary', 'btn-outline-secondary', 'btn-success', 'border-success');
            if (num === currentQ) {
                btn.classList.add('btn-primary');
            } else if (answered[num]) {
                btn.classList.add('btn-success');
            } else {
                btn.classList.add('btn-outline-secondary');
            }
        });

        var count = Object.keys(answered).length;
        document.getElementById('answeredCount').textContent = count;
    }

    function confirmSubmit() {
        document.getElementById('answeredCount').textContent = Object.keys(answered).length;
        new bootstrap.Modal(document.getElementById('submitModal')).show();
    }

    // Timer
    var remaining = <?= $remaining_seconds ?>;
    var timerEl = document.getElementById('timer');

    if (remaining > 0) {
        var interval = setInterval(function() {
            remaining--;
            var m = Math.floor(remaining / 60);
            var s = remaining % 60;
            timerEl.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');

            if (remaining <= 300) {
                timerEl.classList.remove('text-success');
                timerEl.classList.add('text-danger');
            }

            if (remaining <= 0) {
                clearInterval(interval);
                document.getElementById('examForm').submit();
            }
        }, 1000);
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowRight') nextQ();
        if (e.key === 'ArrowLeft') prevQ();
    });

    updateNav();
    </script>
</body>
</html>
