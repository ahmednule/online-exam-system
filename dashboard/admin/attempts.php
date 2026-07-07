<?php
require_once __DIR__ . '/../../config/db.php';

$stmt = $pdo->query("
    SELECT ea.*, u.full_name, u.admission_no, un.unit_name, c.course_name
    FROM exam_attempts ea
    JOIN users u ON u.user_id = ea.user_id
    JOIN units un ON un.unit_id = ea.unit_id
    JOIN courses c ON c.course_id = un.course_id
    ORDER BY ea.created_at DESC
");
$attempts = $stmt->fetchAll();
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Student Attempts</h4>
        <p class="text-muted mb-0 small">View all exam attempts by students</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Student</th>
                        <th>Unit</th>
                        <th style="width:80px">Score</th>
                        <th style="width:80px">Percentage</th>
                        <th style="width:100px">Status</th>
                        <th style="width:140px">Date</th>
                        <th style="width:100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attempts)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No attempts recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($attempts as $i => $a): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div class="fw-medium small"><?= htmlspecialchars($a['full_name']) ?></div>
                                <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($a['admission_no'] ?? '') ?></div>
                            </td>
                            <td class="small">
                                <div><?= htmlspecialchars($a['unit_name']) ?></div>
                                <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($a['course_name']) ?></div>
                            </td>
                            <td class="fw-medium"><?= $a['score'] ?>/<?= $a['total_questions'] ?></td>
                            <td>
                                <span class="badge bg-<?= $a['score_percent'] >= 50 ? 'success' : 'danger' ?>-subtle text-<?= $a['score_percent'] >= 50 ? 'success' : 'danger' ?>">
                                    <?= $a['score_percent'] ?>%
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $a['status'] === 'completed' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($a['status']) ?>
                                </span>
                            </td>
                            <td class="small text-muted"><?= date('M j, Y g:i A', strtotime($a['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" title="View Details" onclick="viewAttempt(<?= $a['attempt_id'] ?>)">
                                    <i class="bi bi-eye"></i>
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

<!-- View Details Modal -->
<div class="modal fade" id="viewAttemptModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Attempt Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="attemptDetails">
                <div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewAttempt(id) {
    var modal = new bootstrap.Modal(document.getElementById('viewAttemptModal'));
    document.getElementById('attemptDetails').innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...</div>';
    modal.show();

    fetch('attempt_detail.php?attempt_id=' + id)
        .then(function(r) { return r.text(); })
        .then(function(html) {
            document.getElementById('attemptDetails').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('attemptDetails').innerHTML = '<div class="alert alert-danger">Failed to load details.</div>';
        });
}
</script>
