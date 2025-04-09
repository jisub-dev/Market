<?php
// post_list.php

include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php';

// --------------------------------------
// (1) 검색 키워드 / 카테고리 / 현재 페이지
// --------------------------------------
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// --------------------------------------
// (2) 페이지당 게시글 수, 페이지 계산용
// --------------------------------------
$limit = 10; // 한 페이지에 보여줄 게시글 수

// --------------------------------------
// (3) 게시글 '총 개수' 구하기 (검색/필터 동일 적용)
// --------------------------------------
$countSql = "SELECT COUNT(*) FROM posts p
             JOIN users u ON p.user_id = u.id
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE 1=1 ";

// 검색/카테고리 조건을 동일하게
$bindParams = [];
if ($keyword !== '') {
    $countSql .= " AND (p.title LIKE :kw OR p.content LIKE :kw)";
    $bindParams[':kw'] = "%$keyword%";
}
if ($category_id !== '') {
    $countSql .= " AND p.category_id = :cat";
    $bindParams[':cat'] = $category_id;
}

$countStmt = $pdo->prepare($countSql);
foreach ($bindParams as $k => $v) {
    $countStmt->bindValue($k, $v, PDO::PARAM_STR);
}
$countStmt->execute();
$totalCount = $countStmt->fetchColumn(); // 총 게시글 수

// 총 페이지 수
$totalPages = ceil($totalCount / $limit);
if ($totalPages < 1) $totalPages = 1;
if ($page > $totalPages) $page = $totalPages;

// --------------------------------------
// (4) 실제 게시글 목록 SELECT (LIMIT)
// --------------------------------------
$offset = ($page - 1) * $limit;

$sql = "SELECT p.*, u.username, c.name AS category_name
        FROM posts p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1 ";

// 검색/필터 동일 적용
if ($keyword !== '') {
    $sql .= " AND (p.title LIKE :kw OR p.content LIKE :kw)";
}
if ($category_id !== '') {
    $sql .= " AND p.category_id = :cat";
}

$sql .= " ORDER BY p.id DESC
          LIMIT :offset, :lim";

$stmt = $pdo->prepare($sql);

// 바인딩
if ($keyword !== '') {
    $stmt->bindValue(':kw', "%$keyword%", PDO::PARAM_STR);
}
if ($category_id !== '') {
    $stmt->bindValue(':cat', $category_id, PDO::PARAM_STR);
}
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);

// 실행 & 결과
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --------------------------------------
// (5) 블록 계산 (페이지 링크 10개씩)
// --------------------------------------
$blockSize = 10;                  // 한 블록 당 페이지 수
$blockNum = floor(($page - 1) / $blockSize); 
$blockStart = $blockNum * $blockSize + 1; 
$blockEnd = $blockStart + $blockSize - 1;
if ($blockEnd > $totalPages) {
    $blockEnd = $totalPages;
}
?>

<h2>게시글 목록</h2>

<!-- 검색 폼 -->
<form class="row g-2 mb-3" method="get">
  <div class="col-auto">
    <input type="text" name="keyword" class="form-control"
           placeholder="검색어"
           value="<?= htmlspecialchars($keyword) ?>">
  </div>
  <div class="col-auto">
    <select name="category_id" class="form-select">
      <option value="">전체 카테고리</option>
      <?php
      // 카테고리 목록
      $catSql = "SELECT * FROM categories ORDER BY id ASC";
      $catStmt = $pdo->query($catSql);
      $catList = $catStmt->fetchAll(PDO::FETCH_ASSOC);
      foreach($catList as $cat) {
          $selected = ($cat['id'] == $category_id) ? 'selected' : '';
          echo "<option value='{$cat['id']}' {$selected}>"
               . htmlspecialchars($cat['name'])
               . "</option>";
      }
      ?>
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

<!-- (6) 페이징 -->
<?php
// 페이지가 0건이라면?
if ($totalCount == 0) {
    // 게시글이 없으면 페이지 링크도 표시 안 함
    include_once __DIR__ . '/includes/footer.php';
    exit;
}
?>
<nav>
  <ul class="pagination justify-content-center">
    <!-- 맨 처음 버튼 (<<) -->
    <?php if ($page > 1): ?>
      <li class="page-item">
        <a class="page-link"
           href="?page=1&keyword=<?=urlencode($keyword)?>&category_id=<?=$category_id?>">
          &laquo;&laquo;
        </a>
      </li>
    <?php endif; ?>

    <!-- 이전 블록 (이전 10페이지) -->
    <?php if($blockStart > 1): ?>
      <li class="page-item">
        <a class="page-link"
           href="?page=<?=($blockStart - 1)?>&keyword=<?=urlencode($keyword)?>&category_id=<?=$category_id?>">
          이전
        </a>
      </li>
    <?php endif; ?>

    <!-- 페이지 번호들 -->
    <?php for($i = $blockStart; $i <= $blockEnd; $i++): ?>
      <?php if($i == $page): ?>
        <li class="page-item active">
          <span class="page-link"><?= $i ?></span>
        </li>
      <?php else: ?>
        <li class="page-item">
          <a class="page-link"
             href="?page=<?=$i?>&keyword=<?=urlencode($keyword)?>&category_id=<?=$category_id?>">
            <?= $i ?>
          </a>
        </li>
      <?php endif; ?>
    <?php endfor; ?>

    <!-- 다음 블록 (다음 10페이지) -->
    <?php if($blockEnd < $totalPages): ?>
      <li class="page-item">
        <a class="page-link"
           href="?page=<?=($blockEnd + 1)?>&keyword=<?=urlencode($keyword)?>&category_id=<?=$category_id?>">
          다음
        </a>
      </li>
    <?php endif; ?>

    <!-- 맨 끝 버튼 (>>) -->
    <?php if($page < $totalPages): ?>
      <li class="page-item">
        <a class="page-link"
           href="?page=<?=$totalPages?>&keyword=<?=urlencode($keyword)?>&category_id=<?=$category_id?>">
          &raquo;&raquo;
        </a>
      </li>
    <?php endif; ?>
  </ul>
</nav>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
