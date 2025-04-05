<?php
// post_edit.php
include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

$postId = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id=?");
$stmt->execute([$postId]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    echo "<script>alert('존재하지 않는 게시글'); history.back();</script>";
    exit;
}

// 작성자만 수정
if ($post['user_id'] != $_SESSION['user_id']) {
    echo "<script>alert('수정 권한이 없습니다.'); history.back();</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?? null;
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);

    $imagePath = $post['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        $fileName = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir.$fileName);
        $imagePath = 'uploads/'.$fileName;
    }

    $updSql = "UPDATE posts SET category_id=?, title=?, content=?, image_path=? WHERE id=?";
    $updStmt = $pdo->prepare($updSql);
    $updStmt->execute([$category_id, $title, $content, $imagePath, $postId]);

    echo "<script>alert('수정되었습니다.'); location.href='post_view.php?id={$postId}';</script>";
    exit;
}
?>

<h2>글 수정</h2>
<form method="post" enctype="multipart/form-data">
  <select name="category_id">
    <option value="">카테고리 선택</option>
    <?php
    $catStmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    $catList = $catStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($catList as $cat) {
        $selected = ($cat['id'] == $post['category_id']) ? 'selected' : '';
        echo "<option value='{$cat['id']}' $selected>".htmlspecialchars($cat['name'])."</option>";
    }
    ?>
  </select><br><br>
  제목: <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required><br><br>
  내용: <textarea name="content" rows="5" cols="50" required><?= htmlspecialchars($post['content']) ?></textarea><br><br>
  이미지: <input type="file" name="image"><br>
  <?php if ($post['image_path']): ?>
    <img src="<?= $post['image_path'] ?>" width="100" alt="이미지"><br>
  <?php endif; ?>
  <button type="submit">수정</button>
</form>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
