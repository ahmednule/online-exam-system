<?php
require_once __DIR__ . '/auth/session_check.php';

$role = $_SESSION['role'];
$page = $_GET['page'] ?? 'home';

// Route to the correct content file based on role
if ($role === 'admin') {
    $contentPath = __DIR__ . "/dashboard/admin/{$page}.php";
    if (!file_exists($contentPath)) {
        $contentPath = __DIR__ . '/dashboard/admin/home.php';
    }
} else {
    $contentPath = __DIR__ . "/dashboard/student/{$page}.php";
    if (!file_exists($contentPath)) {
        $contentPath = __DIR__ . '/dashboard/student/home.php';
    }
}

require_once __DIR__ . '/dashboard/layout.php';
