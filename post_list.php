<?php
// post_list.php

include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php';

// (1) 검색 키워드 / 카테고리 ID / 페이지 변수 정의
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// (2) 페이징 처리
$limit = 10;
$offset = ($page - 1) * $limit;

// (3) 기본 쿼리
$sql = "SELECT p.*, u.username, c.name AS category_name
        FROM posts p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1 ";

// (4) 검색/필터 조건
$bindParams = [];

if ($keyword !== '') {
    $sql .= " AND (p.title LIKE :kw OR p.content LIKE :kw) ";
    $bindParams[':kw'] = "%$keyword%";
}
if ($category_id !== '') {
    $sql .= " AND p.category_id = :cat ";
    $bindParams[':cat'] = $category_id;
}

// (5) 정렬 + LIMIT
$sql .= " ORDER BY p.id DESC LIMIT :offset, :lim";

// (6) 쿼리 준비 & 바인딩
$stmt = $pdo->prepare($sql);

foreach ($bindParams as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_STR);
}
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);

// (7) 실행 & 결과
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);   // $posts에 결과 배열이 들어감

?>

<h2>게시글 목록</h2>
<!-- 검색 폼 -->
<form class="row g-2 mb-3" method="get">
  <div class="col-auto">
    <input type="text" name="keyword" class="form-control"
           placeholder="검색어" value="<?= htmlspecialchars($keyword) ?>">
  </div>
  <div class="col-auto">
    <select name="category_id" class="form-select">
      <option value="">전체 카테고리</option>
      <?php
    // 미리 카테고리 목록 가져오기 (간단 예시)
    $catSql = "SELECT * FROM categories ORDER BY id ASC";
    $catStmt = $pdo->query($catSql);
    $catList = $catStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($catList as $cat) {
        $selected = ($cat['id'] == $category_id) ? 'selected' : '';
        echo "<option value='{$cat['id']}' {$selected}>" . htmlspecialchars($cat['name']) . "</option>";
    }
    ?>
      <?php foreach($catList as $cat): ?>
        <?php $selected = ($cat['id'] == $category_id) ? 'selected' : ''; ?>
        <option value="<?= $cat['id'] ?>" <?= $selected ?>>
          <?= htmlspecialchars($cat['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-auto">
    <button type="submit" class="btn btn-primary">검색</button>
  </div>
</form>

<!-- 목록 테이블 -->
<table class="table table-hover align-middle">
  <thead class="table-dark">
    <tr>
      <th>번호</th>
      <th>카테고리</th>
      <th>제목</th>
      <th>작성자</th>
      <th>조회수</th>
      <th>작성일</th>
    </tr>
  </thead>
  <tbody>
  <?php if (!empty($posts)): ?>
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
  <?php else: ?>
    <tr><td colspan="6">게시글이 없습니다.</td></tr>
  <?php endif; ?>
  </tbody>
</table>

<!-- 간단한 페이징 -->
<nav>
  <ul class="pagination">
    <?php if($page > 1): ?>
      <li class="page-item">
        <a class="page-link" href="?page=<?=($page-1)?>&keyword=<?=urlencode($keyword)?>&category_id=<?=$category_id?>">
          이전
        </a>
      </li>
    <?php endif; ?>
    <li class="page-item active"><span class="page-link"><?=$page?></span></li>
    <li class="page-item">
      <a class="page-link" href="?page=<?=($page+1)?>&keyword=<?=urlencode($keyword)?>&category_id=<?=$category_id?>">
        다음
      </a>
    </li>
  </ul>
</nav>

<?php include_once __DIR__ . '/includes/footer.php'; ?>