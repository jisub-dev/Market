<?php
// notification_list.php
include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

$userId = $_SESSION['user_id'];

// 알림 목록 조회 (최신 순)
$sql = "
  SELECT n.*, c.content AS comment_content, p.title AS post_title
  FROM notifications n
  JOIN comments c ON n.comment_id = c.id
  JOIN posts p ON n.post_id = p.id
  WHERE n.user_id = ?
  ORDER BY n.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 읽음 처리(모두 읽음) - 예: 페이지 접속시 전체 읽음 처리
$pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$userId]);
?>

<h2>알림 목록</h2>
<ul>
<?php foreach($notifications as $noti): ?>
  <li style="<?= $noti['is_read'] == 0 ? 'font-weight:bold;' : '' ?>">
    [<?= $noti['created_at'] ?>]
    <?= htmlspecialchars($noti['message']) ?>  
    | 댓글 내용: <?= htmlspecialchars($noti['comment_content']) ?>
    | <a href="post_view.php?id=<?= $noti['post_id'] ?>">바로가기</a>
  </li>
<?php endforeach; ?>
</ul>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
