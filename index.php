<?php
// index.php
include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php';

// 카테고리 목록 불러오기
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- 상단 인트로/배너 섹션 -->
<div class="bg-light p-5 rounded mb-4">
  <h1 class="display-5 fw-bold">Market 중고거래</h1>
  <p class="lead">원하는 카테고리를 선택해서 매물 게시글을 확인해보세요!</p>
  <hr>
  <a href="post_list.php" class="btn btn-primary btn-lg">전체 게시글 보러가기</a>
</div>

<!-- 카테고리 목록 섹션 -->
<h2 class="mb-3">카테고리 목록</h2>

<!-- 
  카드 형태로 여러 열(row-col)로 나열하는 예시 
     Bootstrap 5의 row-cols-? 클래스를 사용하면 자동으로 그리드로 배치됩니다.
-->
<div class="row row-cols-2 row-cols-md-4 g-4">
  <?php foreach ($categories as $cat): ?>
    <div class="col">
      <!-- 카테고리를 클릭 시 post_list.php?category_id=... 으로 이동 -->
      <a href="post_list.php?category_id=<?= $cat['id'] ?>" class="text-decoration-none text-dark">
        <div class="card h-100">
          <div class="card-body d-flex align-items-center justify-content-center">
            <h5 class="card-title m-0 text-center">
              <?= htmlspecialchars($cat['name']) ?>
            </h5>
          </div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>


<?php include_once __DIR__ . '/includes/footer.php'; ?>
