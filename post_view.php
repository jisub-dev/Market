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

<div class="card">
  <div class="card-header">
    <h4><?= htmlspecialchars($post['title']) ?></h4>
    <div class="small text-muted">
      카테고리: <?= htmlspecialchars($post['category_name']) ?>
      | 작성자: <?= htmlspecialchars($post['username']) ?>
      | 조회수: <?= $post['views'] ?>
    </div>
  </div>
  <div class="card-body">
    <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
    <?php if ($post['image_path']): ?>
      <img src="<?= $post['image_path'] ?>" class="img-fluid" alt="이미지">
    <?php endif; ?>
  </div>
  <div class="card-footer text-end">
    작성일: <?= $post['created_at'] ?>
    <?php if ($post['updated_at'] && $post['updated_at'] != $post['created_at']): ?>
      | 수정일: <?= $post['updated_at'] ?>
    <?php endif; ?>
  </div>
</div>

<div class="mt-3">
  <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
    <a href="post_edit.php?id=<?= $postId ?>" class="btn btn-warning btn-sm">수정</a>
    <a href="post_delete.php?id=<?= $postId ?>" onclick="return confirm('삭제?');"
       class="btn btn-danger btn-sm">삭제</a>
  <?php endif; ?>

  <?php if(isset($_SESSION['user_id'])): ?>
    <form method="post" action="favorite_process.php" style="display:inline;">
      <input type="hidden" name="post_id" value="<?= $postId ?>">
      <button type="submit" name="action" value="add" class="btn btn-outline-primary btn-sm">
        즐겨찾기 추가
      </button>
      <button type="submit" name="action" value="remove" class="btn btn-outline-secondary btn-sm">
        즐겨찾기 해제
      </button>
    </form>
  <?php endif; ?>
</div>

<!-- 댓글 목록 -->
<h5 class="mt-4">댓글</h5>
<div>
  <?php 
  $cmtSql = "SELECT c.*, u.username
  FROM comments c
  JOIN users u ON c.user_id = u.id
  WHERE c.post_id=?
  ORDER BY c.id ASC";
  $cmtStmt = $pdo->prepare($cmtSql);
  $cmtStmt->execute([$postId]);
  $comments = $cmtStmt->fetchAll(PDO::FETCH_ASSOC);
  
  foreach($comments as $cmt): ?>
  <div class="border p-2 mb-2">
    <strong><?= htmlspecialchars($cmt['username']) ?></strong><br>
    <div><?= nl2br(htmlspecialchars($cmt['content'])) ?></div>
    <small class="text-muted"><?= $cmt['created_at'] ?></small>
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $cmt['user_id']): ?>
      <form method="post" action="comment_process.php" class="d-inline">
        <input type="hidden" name="comment_id" value="<?= $cmt['id'] ?>">
        <input type="hidden" name="post_id" value="<?= $postId ?>">
        <button type="submit" name="action" value="delete"
                class="btn btn-sm btn-link text-danger">
          삭제
        </button>
      </form>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<!-- 댓글 작성 -->
<?php if(isset($_SESSION['user_id'])): ?>
<div class="mt-3">
  <form method="post" action="comment_process.php">
    <input type="hidden" name="post_id" value="<?= $postId ?>">
    <div class="mb-2">
      <textarea name="content" class="form-control" rows="2" required></textarea>
    </div>
    <button type="submit" name="action" value="add" class="btn btn-primary btn-sm">
      댓글 달기
    </button>
  </form>
</div>
<?php endif; ?>
