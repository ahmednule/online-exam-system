<?php
require_once __DIR__ . '/../../config/db.php';

$stmt = $pdo->prepare("
    SELECT u.*, c.course_name
    FROM users u
    LEFT JOIN student_courses sc ON sc.user_id = u.user_id
    LEFT JOIN courses c ON c.course_id = sc.course_id
    WHERE u.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">My Profile</h4>
        <p class="text-muted mb-0 small">Your account information</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-4">
                <div class="profile-avatar mx-auto mb-3">
                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                </div>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['full_name']) ?></h5>
                <span class="badge bg-primary-subtle text-primary mb-3"><?= ucfirst($user['role']) ?></span>
                <?php if ($user['course_name']): ?>
                    <p class="text-muted small mb-0"><i class="bi bi-book"></i> <?= htmlspecialchars($user['course_name']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">Account Details</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Full Name</label>
                        <div class="fw-medium"><?= htmlspecialchars($user['full_name']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Admission No.</label>
                        <div class="fw-medium"><?= htmlspecialchars($user['admission_no'] ?? '—') ?></div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Email</label>
                        <div class="fw-medium"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Role</label>
                        <div class="fw-medium"><?= ucfirst($user['role']) ?></div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Course</label>
                        <div class="fw-medium"><?= htmlspecialchars($user['course_name'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Member Since</label>
                        <div class="fw-medium"><?= date('F j, Y', strtotime($user['created_at'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
