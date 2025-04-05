<?php
// post_list.php
include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php';

// 검색/카테고리 필터
$keyword     = $_GET['keyword'] ?? '';
$category_id = $_GET['category_id'] ?? '';

// 페이징
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 10;  // 10개씩
$offset = ($page - 1) * $limit;

// 기본 쿼리
$sql = "SELECT p.*, u.username, c.name AS category_name
        FROM posts p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1";

// 동적 WHERE
$bindParams = [];
if ($keyword !== '') {
    $sql .= " AND (p.title LIKE :kw OR p.content LIKE :kw)";
    $bindParams[':kw'] = "%$keyword%";
}
if ($category_id !== '') {
    $sql .= " AND p.category_id = :cat";
    $bindParams[':cat'] = $category_id;
}

// 정렬 + LIMIT
$sql .= " ORDER BY p.id DESC LIMIT :offset, :limit";

$stmt = $pdo->prepare($sql);
// 바인딩
foreach ($bindParams as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_STR);
}
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>게시글 목록</h2>

<!-- 검색 폼 -->
<form method="get" style="margin-bottom:10px;">
  <input type="text" name="keyword" placeholder="검색어" value="<?= htmlspecialchars($keyword) ?>">
  <select name="category_id">
    <option value="">전체 카테고리</option>
    <?php
    // 카테고리 목록
    $catStmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    $catList = $catStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($catList as $c) {
        $selected = ($c['id'] == $category_id) ? 'selected' : '';
        echo "<option value='{$c['id']}' $selected>".htmlspecialchars($c['name'])."</option>";
    }
    ?>
  </select>
  <button type="submit">검색</button>
</form>

<table border="1" width="100%">
  <tr>
    <th>ID</th>
    <th>카테고리</th>
    <th>제목</th>
    <th>작성자</th>
    <th>조회수</th>
    <th>작성일</th>
  </tr>
  <?php foreach($posts as $post): ?>
  <tr>
    <td><?= $post['id'] ?></td>
    <td><?= htmlspecialchars($post['category_name']) ?></td>
    <td>
      <a href="post_view.php?id=<?= $post['id'] ?>">
        <?= htmlspecialchars($post['title']) ?>
      </a>
    </td>
    <td><?= htmlspecialchars($post['username']) ?></td>
    <td><?= $post['views'] ?></td>
    <td><?= $post['created_at'] ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<!-- 간단 페이징 -->
<div style="margin-top:10px;">
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page-1 ?>&keyword=<?= urlencode($keyword) ?>&category_id=<?= $category_id ?>">이전</a>
  <?php endif; ?>
  <span> [<?= $page ?>] </span>
  <a href="?page=<?= $page+1 ?>&keyword=<?= urlencode($keyword) ?>&category_id=<?= $category_id ?>">다음</a>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
