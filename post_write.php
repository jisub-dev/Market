<?php
// post_write.php
include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $category_id = $_POST['category_id'] ?? null;
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);

    // 이미지 업로드
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/'; // 실제 물리 경로
        // 파일명 중복 방지를 위해 타임스탬프_원본이름 형태
        $fileName = time() . '_' . $_FILES['image']['name'];
        $tmpName = $_FILES['image']['tmp_name'];
        move_uploaded_file($tmpName, $uploadDir.$fileName);
        // DB에는 상대경로 저장 (브라우저에서 접근 가능하도록)
        $imagePath = 'uploads/'.$fileName;
    }

    $sql = "INSERT INTO posts (user_id, category_id, title, content, image_path)
            VALUES (?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $category_id, $title, $content, $imagePath]);

    echo "<script>alert('글이 등록되었습니다.'); location.href='post_list.php';</script>";
    exit;
}
?>

<h2>글 작성</h2>
<form method="post" enctype="multipart/form-data">
  <select name="category_id" required>
    <option value="">카테고리 선택</option>
    <?php
    $catStmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    $catList = $catStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($catList as $cat) {
      echo "<option value='{$cat['id']}'>".htmlspecialchars($cat['name'])."</option>";
    }
    ?>
  </select><br><br>
  제목: <input type="text" name="title" required><br><br>
  내용: <textarea name="content" rows="5" cols="50" required></textarea><br><br>
  이미지: <input type="file" name="image"><br><br>
  <button type="submit">등록</button>
</form>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
