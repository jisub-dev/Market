<?php
// includes/header.php
if (!isset($pdo)) {
    include_once __DIR__ . '/../config/db.php';
}

// (세션 정보 등을 기반으로 알림 카운트, 로그인 상태 UI 구성)
$unreadCount = 0;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    // 알림 개수 가져옴
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
    $countStmt->execute([$userId]);
    $unreadCount = $countStmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="utf-8"/>
  <title>Market - 중고거래</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <!-- 사이트 로고 or 이름 -->
    <a class="navbar-brand" href="/Market/index.php">Market</a>
    
    <!-- 모바일에서 토글되는 버튼 (원치 않으면 삭제 가능) -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
            data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
            aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- 실제 메뉴 목록 -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <!-- ms-auto: 오른쪽 정렬 -->
        <li class="nav-item">
          <a class="nav-link" href="/Market/post_list.php">게시글 목록</a>
        </li>

        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item">
            <a class="nav-link" href="/Market/post_write.php">글쓰기</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/Market/my_posts.php">내 글보기</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/Market/favorite_list.php">즐겨찾기</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/Market/notification_list.php">
              알림
              <?php if($unreadCount > 0): ?>
                <span class="badge bg-danger"><?= $unreadCount ?></span>
              <?php endif; ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/Market/logout.php">로그아웃 (<?= htmlspecialchars($_SESSION['username'] ?? '') ?>)</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="/Market/login.php">로그인</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/Market/register.php">회원가입</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<!-- 페이지 컨텐츠를 감쌀 컨테이너 -->
<div class="container py-4">