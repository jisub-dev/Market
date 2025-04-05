<?php
// post_view.php
include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php';

$postId = $_GET['id'] ?? 0;

// 조회수 증가
$pdo->prepare("UPDATE posts SET views=views+1 WHERE id=?")->execute([$postId]);

// 게시글 조회
$sql = "SELECT p.*, u.username, c.name as category_name
        FROM posts p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$postId]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    echo "<script>alert('존재하지 않는 게시글'); history.back();</script>";
    exit;
}
?>

<h2><?= htmlspecialchars($post['title']) ?></h2>
<div>카테고리: <?= htmlspecialchars($post['category_name']) ?></div>
<div>작성자: <?= htmlspecialchars($post['username']) ?></div>
<div>조회수: <?= $post['views'] ?></div>
<div>작성일: <?= $post['created_at'] ?></div>
<?php if ($post['updated_at'] && $post['updated_at'] != $post['created_at']): ?>
<div>수정일: <?= $post['updated_at'] ?></div>
<?php endif; ?>
<hr>
<div><?= nl2br(htmlspecialchars($post['content'])) ?></div>

<?php if ($post['image_path']): ?>
  <p><img src="<?= $post['image_path'] ?>" width="300" alt="이미지"></p>
<?php endif; ?>

<!-- 수정/삭제 버튼 (작성자만) -->
<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
  <a href="post_edit.php?id=<?= $postId ?>">[수정]</a>
  <a href="post_delete.php?id=<?= $postId ?>" onclick="return confirm('정말 삭제?');">[삭제]</a>
<?php endif; ?>

<!-- 즐겨찾기 (로그인 상태) -->
<?php if (isset($_SESSION['user_id'])): ?>
  <form method="post" action="favorite_process.php" style="display:inline;">
    <input type="hidden" name="post_id" value="<?= $postId ?>">
    <button type="submit" name="action" value="add">즐겨찾기 추가</button>
    <button type="submit" name="action" value="remove">즐겨찾기 해제</button>
  </form>
<?php endif; ?>

<hr>
<!-- 댓글 목록 -->
<h3>댓글</h3>
<?php
$cmtSql = "SELECT c.*, u.username
           FROM comments c
           JOIN users u ON c.user_id = u.id
           WHERE c.post_id=?
           ORDER BY c.id ASC";
$cmtStmt = $pdo->prepare($cmtSql);
$cmtStmt->execute([$postId]);
$comments = $cmtStmt->fetchAll(PDO::FETCH_ASSOC);

foreach($comments as $cmt) {
    echo "<div style='border:1px solid #ccc; margin-bottom:5px; padding:5px;'>";
    echo "<strong>".htmlspecialchars($cmt['username'])."</strong><br>";
    echo nl2br(htmlspecialchars($cmt['content']))."<br>";
    echo "<small>".$cmt['created_at']."</small>";
    // 댓글 삭제 (댓글 작성자만)
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $cmt['user_id']) {
        echo "
        <form method='post' action='comment_process.php' style='display:inline;'>
          <input type='hidden' name='comment_id' value='{$cmt['id']}'/>
          <input type='hidden' name='post_id' value='{$postId}'/>
          <button type='submit' name='action' value='delete'>삭제</button>
        </form>
        ";
    }
    echo "</div>";
}
?>

<!-- 댓글 작성 -->
<?php if (isset($_SESSION['user_id'])): ?>
<form method="post" action="comment_process.php">
  <input type="hidden" name="post_id" value="<?= $postId ?>">
  <textarea name="content" rows="3" cols="50" required></textarea><br>
  <button type="submit" name="action" value="add">댓글달기</button>
</form>
<?php endif; ?>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
