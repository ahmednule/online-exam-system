<?php
require_once __DIR__ . '/../../config/db.php';

$appRoot = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($appRoot === '/') {
    $appRoot = '';
}

$questionGeneratorUrl = $appRoot . '/dashboard/admin/question_generator.php';

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
        } elseif ($action === 'generate_save') {
            $questions = json_decode($_POST['questions'] ?? '[]', true);
            $unit_id = (int)($_POST['unit_id'] ?? 0);

            if ($unit_id && is_array($questions) && !empty($questions)) {
                $stmt = $pdo->prepare("INSERT INTO questions (unit_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
                foreach ($questions as $q) {
                    if (!isset($q['question_text'], $q['option_a'], $q['option_b'], $q['option_c'], $q['option_d'], $q['correct_option'])) {
                        continue;
                    }

                    $stmt->execute([
                        $unit_id,
                        trim($q['question_text']),
                        trim($q['option_a']),
                        trim($q['option_b']),
                        trim($q['option_c']),
                        trim($q['option_d']),
                        $q['correct_option'],
                    ]);
                }

                header('Location: ?page=questions&msg=ai_added&count=' . count($questions));
                exit;
            }

            header('Location: ?page=questions&msg=error');
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
    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#aiGenerateModal">
            <i class="bi bi-stars"></i> Generate with AI
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#questionModal" onclick="resetQuestionModal()">
            <i class="bi bi-plus-lg"></i> Add Question
        </button>
    </div>
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

<!-- AI Generate Modal -->
<div class="modal fade" id="aiGenerateModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content ai-generator-modal">
            <div class="modal-header ai-generator-header">
                <h5 class="modal-title"><i class="bi bi-stars text-success"></i> Generate Questions with AI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body ai-generator-body">
                <div class="ai-generator-intro mb-4">
                    <div>
                        <div class="text-uppercase small text-success fw-semibold mb-1">Gemini-powered draft generation</div>
                        <h6 class="mb-1 fw-bold">Create a set of exam questions in seconds.</h6>
                        <p class="text-muted mb-0 small">Pick a unit, add an optional topic, and review the draft before saving it.</p>
                    </div>
                    <div class="ai-generator-badge">
                        <i class="bi bi-lightning-charge-fill"></i>
                        AI Drafts
                    </div>
                </div>

                <div id="aiGenerateForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Unit</label>
                            <select id="aiUnit" class="form-select" required>
                                <option value="">Select Unit</option>
                                <?php foreach ($units as $u): ?>
                                    <option value="<?= $u['unit_id'] ?>"><?= htmlspecialchars($u['unit_name']) ?> (<?= htmlspecialchars($u['course_name']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Topic <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="text" id="aiTopic" class="form-control" placeholder="e.g. Sorting algorithms">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Count</label>
                            <input type="number" id="aiCount" class="form-control" value="5" min="1" max="10">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-success w-100" id="aiGenerateBtn" onclick="generateQuestions()">
                                <i class="bi bi-magic"></i> Generate
                            </button>
                        </div>
                    </div>
                </div>

                <div id="aiLoading" class="text-center py-5 d-none">
                    <div class="spinner-border text-success mb-3" role="status" style="width:3rem;height:3rem;"></div>
                    <p class="text-muted mb-0">Generating questions with AI...</p>
                </div>

                <div id="aiPreview" class="d-none">
                    <hr>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0">Preview Generated Questions</h6>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-secondary btn-sm" onclick="regenerateQuestions()"><i class="bi bi-arrow-repeat"></i> Regenerate</button>
                            <button class="btn btn-success btn-sm" id="aiAcceptBtn" onclick="acceptQuestions()"><i class="bi bi-check-all"></i> Accept All</button>
                        </div>
                    </div>
                    <div id="aiQuestionsList"></div>
                </div>

                <div id="aiError" class="d-none">
                    <hr>
                    <div class="alert alert-danger" id="aiErrorMessage"></div>
                    <button class="btn btn-outline-secondary btn-sm" onclick="resetAiModal()"><i class="bi bi-arrow-repeat"></i> Try Again</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
var questions = <?= json_encode($questions) ?>;
var generatedQuestions = [];
var generatedUnitId = 0;

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

function resetAiModal() {
    document.getElementById('aiGenerateForm').classList.remove('d-none');
    document.getElementById('aiLoading').classList.add('d-none');
    document.getElementById('aiPreview').classList.add('d-none');
    document.getElementById('aiError').classList.add('d-none');
    document.getElementById('aiGenerateBtn').disabled = false;
}

function generateQuestions() {
    var unitId = document.getElementById('aiUnit').value;
    var topic = document.getElementById('aiTopic').value;
    var count = document.getElementById('aiCount').value || 5;

    if (!unitId) {
        alert('Please select a unit.');
        return;
    }

    document.getElementById('aiGenerateForm').classList.add('d-none');
    document.getElementById('aiLoading').classList.remove('d-none');
    document.getElementById('aiError').classList.add('d-none');
    document.getElementById('aiGenerateBtn').disabled = true;

    var formData = new FormData();
    formData.append('unit_id', unitId);
    formData.append('topic', topic);
    formData.append('count', count);

    fetch('<?= htmlspecialchars($questionGeneratorUrl, ENT_QUOTES) ?>', {
        method: 'POST',
        body: formData
    })
    .then(function(r) {
        return r.text().then(function(text) {
            var contentType = r.headers.get('content-type') || '';
            var data = null;

            if (contentType.indexOf('application/json') !== -1) {
                try {
                    data = JSON.parse(text);
                } catch (parseErr) {
                    throw new Error('Invalid JSON from server: ' + parseErr.message);
                }
            } else {
                throw new Error('Server returned HTML instead of JSON: ' + text.slice(0, 160));
            }

            if (!r.ok) {
                throw new Error(data && data.error ? data.error : 'Request failed with HTTP ' + r.status);
            }

            return data;
        });
    })
    .then(function(data) {
        document.getElementById('aiLoading').classList.add('d-none');
        document.getElementById('aiGenerateBtn').disabled = false;

        if (data.error) {
            document.getElementById('aiErrorMessage').textContent = data.error;
            document.getElementById('aiError').classList.remove('d-none');
            return;
        }

        generatedQuestions = data.questions;
        generatedUnitId = data.unit_id;

        var html = '';
        for (var i = 0; i < generatedQuestions.length; i++) {
            var q = generatedQuestions[i];
            html += '<div class="card mb-3 ai-question-card">';
            html += '<div class="card-body py-3 px-3">';
            html += '<div class="d-flex align-items-start justify-content-between gap-3 mb-2">';
            html += '<div class="fw-semibold small">Q' + (i + 1) + '. ' + escHtml(q.question_text) + '</div>';
            html += '<span class="badge bg-success-subtle text-success-emphasis flex-shrink-0">Draft</span>';
            html += '</div>';
            html += '<div class="row g-1 small">';
            html += '<div class="col-6"><span class="text-success">A. ' + escHtml(q.option_a) + '</span></div>';
            html += '<div class="col-6"><span class="text-danger">B. ' + escHtml(q.option_b) + '</span></div>';
            html += '<div class="col-6"><span class="text-primary">C. ' + escHtml(q.option_c) + '</span></div>';
            html += '<div class="col-6"><span class="text-warning">D. ' + escHtml(q.option_d) + '</span></div>';
            html += '</div>';
            html += '<div class="mt-1"><span class="badge bg-success">Answer: ' + q.correct_option.toUpperCase() + '</span></div>';
            html += '</div></div>';
        }
        document.getElementById('aiQuestionsList').innerHTML = html;
        document.getElementById('aiPreview').classList.remove('d-none');
    })
    .catch(function(err) {
        document.getElementById('aiLoading').classList.add('d-none');
        document.getElementById('aiGenerateBtn').disabled = false;

        var message = err.message || 'Unknown error.';
        if (message.indexOf('429') !== -1 || message.toLowerCase().indexOf('rate') !== -1) {
            message = 'Gemini is rate-limiting requests right now. Wait a moment and try again, or reduce the count to 1-3 questions.';
        } else if (message.indexOf('HTML instead of JSON') !== -1) {
            message = 'The generator endpoint returned a page instead of JSON. Confirm the request URL and session access.';
        } else {
            message = 'AI generation failed: ' + message;
        }

        document.getElementById('aiErrorMessage').textContent = message;
        document.getElementById('aiError').classList.remove('d-none');
    });
}

function regenerateQuestions() {
    document.getElementById('aiPreview').classList.add('d-none');
    document.getElementById('aiError').classList.add('d-none');
    generateQuestions();
}

function acceptQuestions() {
    if (!generatedQuestions.length) return;

    var formData = new FormData();
    formData.append('action', 'generate_save');
    formData.append('unit_id', generatedUnitId);
    formData.append('questions', JSON.stringify(generatedQuestions));

    fetch('?page=questions', {
        method: 'POST',
        body: formData
    })
    .then(function() {
        window.location.href = '?page=questions&msg=ai_added&count=' + generatedQuestions.length;
    });
}

function escHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
</script>
