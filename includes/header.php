<?php
// includes/header.php
// 혹시 직접 접근 시 대비, db.php 로드
if (!isset($pdo)) {
    include_once __DIR__ . '/../config/db.php';
}
if (isset($_SESSION['user_id'])) {
  $currentUserId = $_SESSION['user_id'];

  // 읽지 않은 알림 개수
  $notiCountStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
  $notiCountStmt->execute([$currentUserId]);
  $unreadCount = $notiCountStmt->fetchColumn();
} else {
  $unreadCount = 0;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="utf-8"/>
  <title>Market</title>
</head>
<body>
  <div style="background:#eee; padding:10px;">
    <a href="/market/index.php">홈</a> |
    <a href="/market/post_list.php">게시글 목록</a> |
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="/market/post_write.php">글쓰기</a> |
      <a href="/market/favorite_list.php">즐겨찾기</a> |
      <a href="/market/my_posts.php">내 글보기</a> |
      <a href="notification_list.php">
      알림 <span style="color:red">(<?= $unreadCount ?>)</span>
    </a> |
      <a href="/market/logout.php">로그아웃</a>
      (<?= htmlspecialchars($_SESSION['username'] ?? '') ?> 님)
    <?php else: ?>
      <a href="/market/login.php">로그인</a> |
      <a href="/market/register.php">회원가입</a>
    <?php endif; ?>
  </div>
  <hr>
