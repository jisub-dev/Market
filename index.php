<?php
// index.php
include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php';

// 카테고리 목록 불러오기 (옵션)
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h2>카테고리 목록</h2>
<ul>
<?php foreach ($categories as $cat): ?>
  <li>
    <a href="post_list.php?category_id=<?= $cat['id'] ?>">
      <?= htmlspecialchars($cat['name']) ?>
    </a>
  </li>
<?php endforeach; ?>
</ul>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
