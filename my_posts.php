<?php
// my_posts.php
include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

$currentUserId = $_SESSION['user_id'];

$sql = "
   SELECT p.*, c.name AS category_name
   FROM posts p
   LEFT JOIN categories c ON p.category_id = c.id
   WHERE p.user_id = ?
   ORDER BY p.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$currentUserId]);
$myPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>내가 작성한 글</h2>
<table class="table table-bordered">
  <tr>
    <th>ID</th><th>카테고리</th><th>제목</th><th>작성일</th>
  </tr>
  <?php foreach($myPosts as $post): ?>
  <tr>
    <td><?= $post['id'] ?></td>
    <td><?= htmlspecialchars($post['category_name']) ?></td>
    <td><a href="post_view.php?id=<?= $post['id'] ?>">
      <?= htmlspecialchars($post['title']) ?>
    </a></td>
    <td><?= $post['created_at'] ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
