<?php
// favorite_list.php
include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

$userId = $_SESSION['user_id'];
$sql = "SELECT f.*, p.title, p.id as post_id
        FROM favorites f
        JOIN posts p ON f.post_id = p.id
        WHERE f.user_id=?
        ORDER BY f.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>내 즐겨찾기</h2>
<ul>
<?php foreach($favorites as $fav): ?>
  <li>
    <a href="post_view.php?id=<?= $fav['post_id'] ?>">
      <?= htmlspecialchars($fav['title']) ?>
    </a>
    (찜한 날짜: <?= $fav['created_at'] ?>)
  </li>
<?php endforeach; ?>
</ul>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
