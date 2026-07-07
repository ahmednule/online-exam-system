<?php
// 1. Establish strict typing and error tracking for a professional architecture
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Student Dashboard - Examination Portal Home
 * High-Fidelity Professional Frontend Tier
 */

// 2. Simulated Session Guarding (Prevents unauthorized direct access to this view)
// In production, this verifies the $_SESSION['user_role'] === 'student'
$studentName = "David Mugendi"; 
$studentRegNo = "BIT/3341/2024";
$currentSemester = "Year 2, Semester 2";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Available Examinations | Portal Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --brand-primary: #6f42c1;
            --brand-dark: #212529;
            --surface-bg: #f4f6f9;
            --panel-white: #ffffff;
            --text-muted: #6c757d;
            --border-radius-sm: 8px;
            --border-radius-lg: 16px;
            --transition-smooth: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background-color: var(--surface-bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--brand-dark);
            overflow-x: hidden;
        }

        /* Expert-Level Clean Glass Sidebar Real Estate Layout */
        .portal-layout {
            display: flex;
            min-height: 100vh;
        }

        .main-workspace {
            flex-grow: 1;
            padding: 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        /* High-End Component Elevations (Cards & Containers) */
        .stat-banner {
            background: linear-gradient(135deg, var(--brand-primary), #5a2e9e);
            border: none;
            border-radius: var(--border-radius-lg);
            color: white;
            box-shadow: 0 10px 20px rgba(111, 66, 193, 0.15);
        }

        .exam-card {
            background: var(--panel-white);
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: var(--border-radius-lg);
            transition: var(--transition-smooth);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.01);
        }

        .exam-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06);
            border-color: rgba(111, 66, 193, 0.2);
        }

        .data-table-card {
            background: var(--panel-white);
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            overflow: hidden;
        }

        .btn-action-primary {
            background-color: var(--brand-primary);
            color: white;
            border: none;
            border-radius: var(--border-radius-sm);
            padding: 0.6rem 1.2rem;
            font-weight: 600;
            transition: var(--transition-smooth);
        }

        .btn-action-primary:hover {
            background-color: #5a2e9e;
            color: white;
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.3);
        }

        .badge-custom {
            padding: 0.5em 0.8em;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>

<div class="portal-layout">
    <?php if(file_exists('sidebar.php')) { include_once 'sidebar.php'; } ?>

    <main class="main-workspace">
        
        <div class="card stat-banner p-4 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <div>
                    <span class="text-uppercase tracking-wider small opacity-75">Academic Assessment Portal</span>
                    <h2 class="fw-bold mb-1 mt-1">Welcome back, <?= htmlspecialchars($studentName); ?></h2>
                    <p class="mb-0 opacity-70"><i class="bi bi-mortarboard me-2"></i> Registration Number: <?= htmlspecialchars($studentRegNo); ?> | <i class="bi bi-calendar3 ms-2 me-1"></i> Current Cadence: <?= htmlspecialchars($currentSemester); ?></p>
                </div>
                <div class="mt-3 mt-md-0">
                    <span class="badge bg-white text-dark p-2 rounded-3 fs-6 font-monospace fw-normal">
                        <i class="bi bi-clock-fill text-success me-1"></i> System Online
                    </span>
                </div>
            </div>
        </div>

        <?php if(file_exists('notifications.php')) { include_once 'notifications.php'; } ?>

        <div class="row align-items-center mb-4">
            <div class="col-8">
                <h4 class="fw-bold m-0"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Active Examinations</h4>
                <p class="text-muted small mb-0">Authorized units currently open for computational assessment submissions.</p>
            </div>
            <div class="col-4 text-end">
                <button class="btn btn-outline-secondary btn-sm rounded-3" onclick="window.location.reload();">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Feed
                </button>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12 col-xl-6">
                <div class="card exam-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-purple-subtle text-purple mb-2 badge-custom" style="color: var(--brand-primary); background: #eee5f8;">CORE ASSESSMENT</span>
                            <h5 class="fw-bold mb-1">BIT 2204: Internet Programming</h5>
                            <p class="text-muted small mb-0">Scope: Client-side DOM manipulation, state validation paradigms, and PHP-MariaDB application integrations.</p>
                        </div>
                    </div>
                    <hr class="text-muted opacity-25 my-3">
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div class="d-flex gap-3 text-muted small">
                            <span><i class="bi bi-hourglass-split text-primary me-1"></i> <strong>120</strong> Minutes</span>
                            <span><i class="bi bi-list-task text-success me-1"></i> <strong>40</strong> Structured Qs</span>
                        </div>
                        <button class="btn btn-action-primary px-4">Initialize Test</button>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card exam-card p-4 h-100 opacity-75 bg-light">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-warning-subtle text-warning-emphasis mb-2 badge-custom">UPCOMING SESSION</span>
                            <h5 class="fw-bold mb-1 text-dark">BIT 2201: Object Oriented Analysis & Design</h5>
                            <p class="text-muted small mb-0">Scope: Comprehensive evaluation on unified modeling syntax (UML), object relationships, and platform design architectures.</p>
                        </div>
                    </div>
                    <hr class="text-muted opacity-25 my-3">
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div class="d-flex gap-3 text-muted small">
                            <span><i class="bi bi-hourglass-split me-1"></i> <strong>90</strong> Minutes</span>
                            <span><i class="bi bi-list-task me-1"></i> <strong>35</strong> Items</span>
                        </div>
                        <button class="btn btn-secondary px-4 rounded-3" disabled><i class="bi bi-lock-fill me-1"></i> Locked until 14:00 EAT</button>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="fw-bold mb-1"><i class="bi bi-clipboard2-check-fill text-primary me-2"></i>Historical Execution Summary</h4>
        <p class="text-muted small mb-3">Validated outcomes and performance metrics for previously closed examinations.</p>

        <div class="card data-table-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase font-monospace small">
                        <tr>
                            <th class="ps-4 py-3">Unit Reference</th>
                            <th class="py-3">Course Specification</th>
                            <th class="py-3">Completion Matrix Timestamp</th>
                            <th class="py-3 text-center">Score Metric</th>
                            <th class="pe-4 py-3 text-end">Verification State</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4 fw-bold font-monospace text-secondary">BIT 2102</td>
                            <td>Database Management Systems</td>
                            <td>July 02, 2026 @ 11:24 EAT</td>
                            <td class="text-center"><span class="badge bg-success-subtle text-success p-2 px-3 fs-6 rounded-3">78 / 100</span></td>
                            <td class="pe-4 text-end"><span class="text-success small fw-bold"><i class="bi bi-patch-check-fill me-1"></i> Verified & Released</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold font-monospace text-secondary">BIT 2105</td>
                            <td>Data Communications</td>
                            <td>June 28, 2026 @ 15:40 EAT</td>
                            <td class="text-center"><span class="badge bg-success-subtle text-success p-2 px-3 fs-6 rounded-3">64 / 100</span></td>
                            <td class="pe-4 text-end"><span class="text-success small fw-bold"><i class="bi bi-patch-check-fill me-1"></i> Verified & Released</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>