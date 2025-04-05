<?php
// post_delete.php
include_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

$postId = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id=?");
$stmt->execute([$postId]);
$postOwner = $stmt->fetchColumn();

if (!$postOwner) {
    echo "<script>alert('존재하지 않는 게시글'); history.back();</script>";
    exit;
}

// 작성자만 삭제
if ($postOwner != $_SESSION['user_id']) {
    echo "<script>alert('삭제 권한이 없습니다.'); history.back();</script>";
    exit;
}

// 삭제
$delStmt = $pdo->prepare("DELETE FROM posts WHERE id=?");
$delStmt->execute([$postId]);

echo "<script>alert('삭제되었습니다.'); location.href='post_list.php';</script>";
exit;
