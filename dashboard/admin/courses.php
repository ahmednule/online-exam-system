<?php
require_once __DIR__ . '/../../config/db.php';

$courses = $pdo->query("
    SELECT c.*, (SELECT COUNT(*) FROM units WHERE course_id = c.course_id) AS unit_count
    FROM courses c ORDER BY c.course_name
")->fetchAll();
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Courses</h4>
        <p class="text-muted mb-0 small">Manage all courses in the system</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal">
        <i class="bi bi-plus-lg"></i> Add Course
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Course Name</th>
                        <th>Description</th>
                        <th style="width:100px">Units</th>
                        <th style="width:120px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courses)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No courses found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($courses as $i => $c): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($c['course_name']) ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($c['description'] ?? '—') ?></td>
                            <td><span class="badge bg-info-subtle text-info"><?= $c['unit_count'] ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" title="Edit" onclick="editCourse(<?= $c['course_id'] ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteCourse(<?= $c['course_id'] ?>)">
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
<div class="modal fade" id="courseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courseModalTitle">Add Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <input type="hidden" name="course_id" id="courseId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Course Name</label>
                        <input type="text" name="course_name" id="courseName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="courseDesc" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteCourseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-3 d-block"></i>
                <h6 class="fw-bold">Delete Course?</h6>
                <p class="small text-muted mb-0">This will also delete all units and questions under this course. This action cannot be undone.</p>
                <input type="hidden" id="deleteCourseId">
            </div>
            <div class="modal-footer border-0 justify-content-center pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteCourse()">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
function editCourse(id) {
    // Stub — will populate form via AJAX later
    document.getElementById('courseModalTitle').textContent = 'Edit Course';
    new bootstrap.Modal(document.getElementById('courseModal')).show();
}
function deleteCourse(id) {
    document.getElementById('deleteCourseId').value = id;
    new bootstrap.Modal(document.getElementById('deleteCourseModal')).show();
}
function confirmDeleteCourse() {
    // Stub — will POST delete later
    bootstrap.Modal.getInstance(document.getElementById('deleteCourseModal')).hide();
}
</script>
