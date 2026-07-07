<?php
require_once __DIR__ . '/../../config/db.php';

$stmt = $pdo->prepare("
    SELECT ea.*, un.unit_name, un.duration_minutes, c.course_name
    FROM exam_attempts ea
    JOIN units un ON un.unit_id = ea.unit_id
    JOIN courses c ON c.course_id = un.course_id
    WHERE ea.user_id = ?
    ORDER BY ea.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$attempts = $stmt->fetchAll();
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">My Results</h4>
        <p class="text-muted mb-0 small">View your past exam attempts and scores</p>
    </div>
</div>

<?php if (empty($attempts)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-trophy text-muted fs-1 d-block mb-3"></i>
            <h6>No Results Yet</h6>
            <p class="text-muted small mb-2">You haven't taken any exams yet.</p>
            <a href="?page=units" class="btn btn-primary btn-sm">Take an Exam</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card dash-stat">
                <div class="card-body">
                    <div class="dash-stat-icon bg-success-subtle text-success"><i class="bi bi-clipboard-check"></i></div>
                    <div class="dash-stat-info">
                        <h3><?= count($attempts) ?></h3>
                        <span>Exams Taken</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <?php
                $best = $pdo->prepare("SELECT MAX(score_percent) FROM exam_attempts WHERE user_id = ? AND status = 'completed'");
                $best->execute([$_SESSION['user_id']]);
                $bestScore = $best->fetchColumn();
            ?>
            <div class="card dash-stat">
                <div class="card-body">
                    <div class="dash-stat-icon bg-warning-subtle text-warning"><i class="bi bi-trophy"></i></div>
                    <div class="dash-stat-info">
                        <h3><?= $bestScore ?: '—' ?><?= $bestScore ? '%' : '' ?></h3>
                        <span>Best Score</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <?php
                $avg = $pdo->prepare("SELECT ROUND(AVG(score_percent), 1) FROM exam_attempts WHERE user_id = ? AND status = 'completed'");
                $avg->execute([$_SESSION['user_id']]);
                $avgScore = $avg->fetchColumn();
            ?>
            <div class="card dash-stat">
                <div class="card-body">
                    <div class="dash-stat-icon bg-info-subtle text-info"><i class="bi bi-graph-up"></i></div>
                    <div class="dash-stat-info">
                        <h3><?= $avgScore ?: '—' ?><?= $avgScore ? '%' : '' ?></h3>
                        <span>Average Score</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Unit</th>
                            <th style="width:100px">Score</th>
                            <th style="width:100px">Percentage</th>
                            <th style="width:90px">Status</th>
                            <th style="width:140px">Date</th>
                            <th style="width:100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attempts as $i => $a): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="small">
                                <div class="fw-medium"><?= htmlspecialchars($a['unit_name']) ?></div>
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
                                <button class="btn btn-sm btn-outline-info" title="View Details" onclick="viewResult(<?= $a['attempt_id'] ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- View Details Modal -->
<div class="modal fade" id="viewResultModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exam Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resultDetails">
                <div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewResult(id) {
    var modal = new bootstrap.Modal(document.getElementById('viewResultModal'));
    document.getElementById('resultDetails').innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...</div>';
    modal.show();

    fetch('../admin/attempt_detail.php?attempt_id=' + id)
        .then(function(r) { return r.text(); })
        .then(function(html) {
            document.getElementById('resultDetails').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('resultDetails').innerHTML = '<div class="alert alert-danger">Failed to load details.</div>';
        });
}
</script>
