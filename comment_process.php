<?php
// comment_process.php
include_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); history.back();</script>";
    exit;
}

$action = $_POST['action'] ?? '';
$postId = $_POST['post_id'] ?? 0;

if ($action === 'add') {
    $content = trim($_POST['content']);
    $sql = "INSERT INTO comments (post_id, user_id, content) VALUES (?,?,?)";
    $stmt= $pdo->prepare($sql);
    $stmt->execute([$postId, $_SESSION['user_id'], $content]);

    // 새로 생성된 comment_id
    $commentId = $pdo->lastInsertId();

    // 게시글 작성자(user_id)를 찾는다.
    $postOwnerStmt = $pdo->prepare("SELECT user_id FROM posts WHERE id=?");
    $postOwnerStmt->execute([$postId]);
    $postOwner = $postOwnerStmt->fetchColumn();

    // 자기 자신 글에 단 댓글이라면 알림 안 만든다(원하면 만들어도 됨).
    if ($postOwner && $postOwner != $_SESSION['user_id']) {
        // 알림 메시지
        $msg = "당신의 글에 새로운 댓글이 달렸습니다.";
        $notiSql = "INSERT INTO notifications (user_id, post_id, comment_id, message)
                    VALUES (?,?,?,?)";
        $notiStmt= $pdo->prepare($notiSql);
        $notiStmt->execute([$postOwner, $postId, $commentId, $msg]);
    }
} elseif ($action === 'delete') {
    $commentId = $_POST['comment_id'] ?? 0;
    // 본인 댓글인지 확인
    $check = $pdo->prepare("SELECT user_id FROM comments WHERE id=?");
    $check->execute([$commentId]);
    $commentOwner = $check->fetchColumn();
    if ($commentOwner == $_SESSION['user_id']) {
        $del = $pdo->prepare("DELETE FROM comments WHERE id=?");
        $del->execute([$commentId]);
    }
}

header("Location: post_view.php?id={$postId}");
exit;
