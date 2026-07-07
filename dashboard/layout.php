<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Online Exam System</title>
    <?php
    require_once __DIR__ . '/../config/db.php';

    $appRoot = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($appRoot === '/') {
        $appRoot = '';
    }

    $assetUrl = $appRoot . '/assets/css/style.css';
    $dashboardUrl = $appRoot . '/dashboard.php';
    $logoutUrl = $appRoot . '/auth/logout.php';

    // Fetch unread notification count
    $unreadCount = 0;
    try {
        $stmt = $pdo->query("
            SELECT COUNT(*) FROM notifications
            WHERE (user_id IS NULL OR user_id = {$_SESSION['user_id']}) AND is_read = 0
        ");
        $unreadCount = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        $unreadCount = 0;
    }
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
                    <div class="position-relative">
                        <button id="notificationBell" class="btn btn-link text-muted p-0 position-relative" title="Notifications">
                            <i class="bi bi-bell fs-5"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;"><?= min($unreadCount, 99) ?></span>
                            <?php endif; ?>
                        </button>
                        <div id="notificationDropdown" class="notification-dropdown d-none">
                            <div class="notification-header d-flex align-items-center justify-content-between py-2 px-3">
                                <h6 class="mb-0 fw-semibold">Notifications</h6>
                                <button class="btn btn-sm btn-link text-muted p-0" id="markAllRead" title="Mark all as read">
                                    <i class="bi bi-check-all"></i>
                                </button>
                            </div>
                            <div id="notificationList" class="notification-list" style="max-height: 400px; overflow-y: auto;"></div>
                        </div>
                    </div>
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

    // Notifications
    (function() {
        var bell = document.getElementById('notificationBell');
        var dropdown = document.getElementById('notificationDropdown');
        var notificationList = document.getElementById('notificationList');
        var markAllReadBtn = document.getElementById('markAllRead');

        if (!bell || !dropdown) return;

        function loadNotifications() {
            fetch('<?= $appRoot ?>/dashboard/notifications.php?action=list')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    renderNotifications(data.notifications || []);
                })
                .catch(function(err) {
                    console.error('Failed to load notifications:', err);
                });
        }

        function renderNotifications(notifications) {
            if (notifications.length === 0) {
                notificationList.innerHTML = '<div class="p-3 text-center text-muted small">No notifications</div>';
                return;
            }

            var html = '';
            for (var i = 0; i < notifications.length; i++) {
                var n = notifications[i];
                var readClass = n.is_read ? '' : ' notification-item-unread';
                var link = n.link ? ' onclick="window.location.href=\'' + escapeHtml(n.link) + '\'; markRead(' + n.id + ');"' : '';
                html += '<div class="notification-item' + readClass + '" data-id="' + n.id + '"' + link + '>';
                html += '<div class="notification-item-title fw-semibold small">' + escapeHtml(n.title) + '</div>';
                html += '<div class="notification-item-message text-muted small">' + escapeHtml(n.message) + '</div>';
                html += '<div class="notification-item-time text-muted" style="font-size: 0.7rem;">' + formatTime(n.created_at) + '</div>';
                html += '</div>';
            }
            notificationList.innerHTML = html;
        }

        function markRead(id) {
            fetch('<?= $appRoot ?>/dashboard/notifications.php?action=read&id=' + id)
                .then(function() { loadNotifications(); })
                .catch(function(err) { console.error('Failed to mark as read:', err); });
        }

        function markAllRead() {
            fetch('<?= $appRoot ?>/dashboard/notifications.php?action=read_all')
                .then(function() { loadNotifications(); })
                .catch(function(err) { console.error('Failed to mark all as read:', err); });
        }

        function formatTime(dateStr) {
            var date = new Date(dateStr);
            var now = new Date();
            var diff = Math.floor((now - date) / 1000);

            if (diff < 60) return 'Just now';
            if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
            return Math.floor(diff / 86400) + 'd ago';
        }

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }

        bell.addEventListener('click', function(e) {
            e.preventDefault();
            dropdown.classList.toggle('d-none');
            if (!dropdown.classList.contains('d-none')) {
                loadNotifications();
            }
        });

        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                markAllRead();
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('d-none');
            }
        });
    })();
    </script>
</body>
</html>
