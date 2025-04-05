<?php
// favorite_process.php
include_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); history.back();</script>";
    exit;
}

$userId = $_SESSION['user_id'];
$postId = $_POST['post_id'] ?? 0;
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    // 중복 방지를 위해 INSERT IGNORE
    $stmt = $pdo->prepare("INSERT IGNORE INTO favorites (user_id, post_id) VALUES (?, ?)");
    $stmt->execute([$userId, $postId]);
} elseif ($action === 'remove') {
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id=? AND post_id=?");
    $stmt->execute([$userId, $postId]);
}

header("Location: post_view.php?id={$postId}");
exit;
