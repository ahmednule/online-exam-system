<?php
require_once __DIR__ . '/../../config/db.php';

$units = $pdo->query("
    SELECT u.unit_id, u.unit_name, c.course_name
    FROM units u
    JOIN courses c ON c.course_id = u.course_id
    ORDER BY c.course_name, u.unit_name
")->fetchAll();

$selected_unit = $_GET['unit_id'] ?? null;
if ($selected_unit) {
    $stmt = $pdo->prepare("
        SELECT q.*, u.unit_name FROM questions q
        JOIN units u ON u.unit_id = q.unit_id
        WHERE q.unit_id = ?
        ORDER BY q.question_id
    ");
    $stmt->execute([$selected_unit]);
} else {
    $stmt = $pdo->query("
        SELECT q.*, u.unit_name FROM questions q
        JOIN units u ON u.unit_id = q.unit_id
        ORDER BY u.unit_name, q.question_id
        LIMIT 50
    ");
}
$questions = $stmt->fetchAll();
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Question Bank</h4>
        <p class="text-muted mb-0 small">Manage questions for each unit</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#questionModal">
        <i class="bi bi-plus-lg"></i> Add Question
    </button>
</div>

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
                        <th style="width:200px">Options</th>
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
                                <button class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Question Modal -->
<div class="modal fade" id="questionModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionModalTitle">Add Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <input type="hidden" name="question_id" id="questionId">
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
