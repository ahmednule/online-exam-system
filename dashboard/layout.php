<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Online Exam System</title>
    <?php
    $appRoot = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($appRoot === '/') {
        $appRoot = '';
    }

    $assetUrl = $appRoot . '/assets/css/style.css';
    $dashboardUrl = $appRoot . '/dashboard.php';
    $logoutUrl = $appRoot . '/auth/logout.php';
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetUrl) ?>">
</head>
<body>

    <div class="dash-wrapper">
        <aside class="dash-sidebar" id="sidebar">
            <div class="dash-sidebar-header">
                <a href="<?= htmlspecialchars($dashboardUrl) ?>" class="dash-brand">
                    <i class="bi bi-laptop"></i>
                    <span>OES</span>
                </a>
                <button class="dash-toggle btn btn-sm btn-outline-light d-lg-none" data-toggle-sidebar>
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <nav class="dash-nav">
                <?php require __DIR__ . '/sidebar.php'; ?>
            </nav>
            <div class="dash-sidebar-footer">
                <div class="d-flex align-items-center gap-2 px-3 py-2">
                    <div class="dash-avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
                    <div class="small text-truncate">
                        <div class="text-white fw-medium"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                        <div class="text-white-50" style="font-size: 0.75rem;"><?= $_SESSION['role'] ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="dash-main">
            <header class="dash-topbar">
                <button class="btn btn-sm btn-outline-secondary d-lg-none me-2" data-toggle-sidebar>
                    <i class="bi bi-list"></i>
                </button>
                <div class="d-flex align-items-center gap-3 ms-auto">
                    <a href="#" class="text-decoration-none text-muted position-relative" title="Notifications">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">3</span>
                    </a>
                    <a href="<?= htmlspecialchars($logoutUrl) ?>" class="btn btn-outline-danger btn-sm" title="Logout">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </header>

            <main class="dash-content">
                <?php require $contentPath; ?>
            </main>
        </div>
    </div>

    <div class="dash-sidebar-overlay" id="sidebarOverlay"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function() {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        var toggles = document.querySelectorAll('[data-toggle-sidebar]');

        function show() { sidebar.classList.add('show'); overlay.classList.add('show'); }
        function hide() { sidebar.classList.remove('show'); overlay.classList.remove('show'); }

        for (var i = 0; i < toggles.length; i++) {
            toggles[i].addEventListener('click', function(e) {
                if (sidebar.classList.contains('show')) { hide(); } else { show(); }
            });
        }
        overlay.addEventListener('click', hide);

        // Auto-close sidebar on nav click (mobile)
        var links = sidebar.querySelectorAll('.dash-nav-link');
        for (var j = 0; j < links.length; j++) {
            links[j].addEventListener('click', function() {
                if (window.innerWidth < 992) hide();
            });
        }
    })();
    </script>
</body>
</html>
