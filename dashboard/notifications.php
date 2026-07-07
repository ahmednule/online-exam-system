<?php
require_once __DIR__ . '/../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $stmt = $pdo->query("
        SELECT id, type, title, message, link, is_read, created_at
        FROM notifications
        WHERE user_id IS NULL OR user_id = {$_SESSION['user_id']}
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $items = $stmt->fetchAll();

    $unread = $pdo->query("
        SELECT COUNT(*) FROM notifications
        WHERE (user_id IS NULL OR user_id = {$_SESSION['user_id']}) AND is_read = 0
    ")->fetchColumn();

    echo json_encode(['notifications' => $items, 'unread' => (int)$unread]);
    exit;
}

if ($action === 'read') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) {
        $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?")->execute([$id]);
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'read_all') {
    $pdo->query("UPDATE notifications SET is_read = 1 WHERE (user_id IS NULL OR user_id = {$_SESSION['user_id']})");
    echo json_encode(['success' => true]);
    exit;
}
