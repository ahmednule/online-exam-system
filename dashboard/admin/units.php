<?php
require_once __DIR__ . '/../../config/db.php';

$courses = $pdo->query("SELECT course_id, course_name FROM courses ORDER BY course_name")->fetchAll();

$selected_course = $_GET['course_id'] ?? null;
if ($selected_course) {
    $stmt = $pdo->prepare("
        SELECT u.*, c.course_name,
            (SELECT COUNT(*) FROM questions WHERE unit_id = u.unit_id) AS question_count
        FROM units u
        JOIN courses c ON c.course_id = u.course_id
        WHERE u.course_id = ?
        ORDER BY u.unit_name
    ");
    $stmt->execute([$selected_course]);
} else {
    $stmt = $pdo->query("
        SELECT u.*, c.course_name,
            (SELECT COUNT(*) FROM questions WHERE unit_id = u.unit_id) AS question_count
        FROM units u
        JOIN courses c ON c.course_id = u.course_id
        ORDER BY c.course_name, u.unit_name
    ");
}
$units = $stmt->fetchAll();
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Units</h4>
        <p class="text-muted mb-0 small">Manage units/subjects under each course</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#unitModal">
        <i class="bi bi-plus-lg"></i> Add Unit
    </button>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET">
            <input type="hidden" name="page" value="units">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Filter by Course</label>
                    <select name="course_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['course_id'] ?>" <?= $selected_course == $c['course_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['course_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($selected_course): ?>
                    <div class="col-md-2">
                        <a href="?page=units" class="btn btn-outline-secondary btn-sm">Clear</a>
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
                        <th style="width:60px">#</th>
                        <th>Unit Name</th>
                        <th>Course</th>
                        <th style="width:100px">Duration</th>
                        <th style="width:100px">Questions</th>
                        <th style="width:120px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($units)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No units found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($units as $i => $u): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($u['unit_name']) ?></td>
                            <td><span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($u['course_name']) ?></span></td>
                            <td><?= $u['duration_minutes'] ?> min</td>
                            <td><span class="badge bg-info-subtle text-info"><?= $u['question_count'] ?></span></td>
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

<!-- Add/Edit Modal -->
<div class="modal fade" id="unitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unitModalTitle">Add Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <input type="hidden" name="unit_id" id="unitId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select name="course_id" id="unitCourse" class="form-select" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit Name</label>
                        <input type="text" name="unit_name" id="unitName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="unitDesc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" id="unitDuration" class="form-control" value="30" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>
