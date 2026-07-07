<?php
require_once __DIR__ . '/../../config/db.php';

$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO questions (unit_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([(int)$_POST['unit_id'], trim($_POST['question_text']), trim($_POST['option_a']), trim($_POST['option_b']), trim($_POST['option_c']), trim($_POST['option_d']), $_POST['correct_option']]);
            header('Location: ?page=questions&msg=added');
            exit;
        } elseif ($action === 'edit') {
            $stmt = $pdo->prepare("UPDATE questions SET unit_id=?, question_text=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_option=? WHERE question_id=?");
            $stmt->execute([(int)$_POST['unit_id'], trim($_POST['question_text']), trim($_POST['option_a']), trim($_POST['option_b']), trim($_POST['option_c']), trim($_POST['option_d']), $_POST['correct_option'], (int)$_POST['question_id']]);
            header('Location: ?page=questions&msg=updated');
            exit;
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM questions WHERE question_id = ?");
            $stmt->execute([(int)$_POST['question_id']]);
            header('Location: ?page=questions&msg=deleted');
            exit;
        }
    } catch (PDOException $e) {
        header('Location: ?page=questions&msg=error');
        exit;
    }
}

$units = $pdo->query("
    SELECT u.unit_id, u.unit_name, c.course_name
    FROM units u JOIN courses c ON c.course_id = u.course_id
    ORDER BY c.course_name, u.unit_name
")->fetchAll();

$selected_unit = $_GET['unit_id'] ?? null;
if ($selected_unit) {
    $stmt = $pdo->prepare("
        SELECT q.*, u.unit_name FROM questions q
        JOIN units u ON u.unit_id = q.unit_id
        WHERE q.unit_id = ? ORDER BY q.question_id
    ");
    $stmt->execute([$selected_unit]);
} else {
    $stmt = $pdo->query("
        SELECT q.*, u.unit_name FROM questions q
        JOIN units u ON u.unit_id = q.unit_id
        ORDER BY u.unit_name, q.question_id LIMIT 50
    ");
}
$questions = $stmt->fetchAll();
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Question Bank</h4>
        <p class="text-muted mb-0 small">Manage questions for each unit</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#questionModal" onclick="resetQuestionModal()">
        <i class="bi bi-plus-lg"></i> Add Question
    </button>
</div>

<?php if ($msg === 'added'): ?>
    <div class="alert alert-success py-2">Question added successfully.</div>
<?php elseif ($msg === 'updated'): ?>
    <div class="alert alert-success py-2">Question updated successfully.</div>
<?php elseif ($msg === 'deleted'): ?>
    <div class="alert alert-success py-2">Question deleted successfully.</div>
<?php elseif ($msg === 'error'): ?>
    <div class="alert alert-danger py-2">An error occurred.</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET">
            <input type="hidden" name="page" value="questions">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Filter by Unit</label>
                    <select name="unit_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Units</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= $u['unit_id'] ?>" <?= $selected_unit == $u['unit_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['unit_name']) ?> (<?= htmlspecialchars($u['course_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($selected_unit): ?>
                    <div class="col-md-2">
                        <a href="?page=questions" class="btn btn-outline-secondary btn-sm">Clear</a>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Question</th>
                        <th style="width:220px">Options</th>
                        <th style="width:80px">Answer</th>
                        <th>Unit</th>
                        <th style="width:120px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($questions)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No questions found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($questions as $i => $q): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="small"><?= htmlspecialchars($q['question_text']) ?></td>
                            <td class="small">
                                <span class="d-block text-success">A. <?= htmlspecialchars($q['option_a']) ?></span>
                                <span class="d-block text-danger">B. <?= htmlspecialchars($q['option_b']) ?></span>
                                <span class="d-block text-primary">C. <?= htmlspecialchars($q['option_c']) ?></span>
                                <span class="d-block text-warning">D. <?= htmlspecialchars($q['option_d']) ?></span>
                            </td>
                            <td><span class="badge bg-success"><?= strtoupper($q['correct_option']) ?></span></td>
                            <td><span class="badge bg-secondary-subtle text-secondary small"><?= htmlspecialchars($q['unit_name']) ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" title="Edit" onclick="editQuestion(<?= $q['question_id'] ?>)"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteQuestion(<?= $q['question_id'] ?>)"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="questionModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionModalTitle">Add Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" id="questionAction" value="add">
                <input type="hidden" name="question_id" id="questionId" value="0">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <select name="unit_id" id="questionUnit" class="form-select" required>
                            <option value="">Select Unit</option>
                            <?php foreach ($units as $u): ?>
                                <option value="<?= $u['unit_id'] ?>"><?= htmlspecialchars($u['unit_name']) ?> (<?= htmlspecialchars($u['course_name']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea name="question_text" id="questionText" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-success">Option A</label>
                            <input type="text" name="option_a" id="optionA" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-danger">Option B</label>
                            <input type="text" name="option_b" id="optionB" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-primary">Option C</label>
                            <input type="text" name="option_c" id="optionC" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-warning">Option D</label>
                            <input type="text" name="option_d" id="optionD" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correct Answer</label>
                        <select name="correct_option" id="correctOption" class="form-select" required>
                            <option value="">Select Correct Answer</option>
                            <option value="a">A</option>
                            <option value="b">B</option>
                            <option value="c">C</option>
                            <option value="d">D</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="question_id" id="deleteQuestionId" value="0">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-3 d-block"></i>
                    <h6 class="fw-bold">Delete Question?</h6>
                    <p class="small text-muted mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var questions = <?= json_encode($questions) ?>;

function resetQuestionModal() {
    document.getElementById('questionAction').value = 'add';
    document.getElementById('questionModalTitle').textContent = 'Add Question';
    document.getElementById('questionId').value = 0;
    document.getElementById('questionUnit').value = '';
    document.getElementById('questionText').value = '';
    document.getElementById('optionA').value = '';
    document.getElementById('optionB').value = '';
    document.getElementById('optionC').value = '';
    document.getElementById('optionD').value = '';
    document.getElementById('correctOption').value = '';
}

function editQuestion(id) {
    var q = questions.find(function(x) { return x.question_id == id; });
    if (!q) return;
    document.getElementById('questionAction').value = 'edit';
    document.getElementById('questionModalTitle').textContent = 'Edit Question';
    document.getElementById('questionId').value = q.question_id;
    document.getElementById('questionUnit').value = q.unit_id;
    document.getElementById('questionText').value = q.question_text;
    document.getElementById('optionA').value = q.option_a;
    document.getElementById('optionB').value = q.option_b;
    document.getElementById('optionC').value = q.option_c;
    document.getElementById('optionD').value = q.option_d;
    document.getElementById('correctOption').value = q.correct_option;
    new bootstrap.Modal(document.getElementById('questionModal')).show();
}

function deleteQuestion(id) {
    document.getElementById('deleteQuestionId').value = id;
    new bootstrap.Modal(document.getElementById('deleteQuestionModal')).show();
}
</script>
