<?php
// seed_data.php
// 주의: 실행 전에 기존 DB 데이터를 백업하거나, 테스트 DB에서만 사용하는 게 안전합니다.

include_once __DIR__ . '/config/db.php';

// ----------------------------------------------------------------------
// (A) 유저 100명 만들기 (user_id=1..100) - 예시로 강제 삽입
// ----------------------------------------------------------------------
echo "<h3>유저 생성 중...</h3>";

for ($u = 1; $u <= 100; $u++) {
    $username = "user{$u}";
    // 단순 비밀번호 '0000'를 해시
    $password = password_hash('0000', PASSWORD_DEFAULT); 
    $email = "user{$u}@test.com";

    // 이미 존재하는지 체크 후 삽입 (INSERT IGNORE 사용 예시)
    // 유저 테이블 구조: (id, username, password, email, created_at)
    $sql = "INSERT IGNORE INTO users (id, username, password, email, created_at)
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$u, $username, $password, $email]);
}
echo "유저 100명 생성(또는 이미 존재하면 무시).<br>";

// ----------------------------------------------------------------------
// (B) 카테고리 3개 만들기
// ----------------------------------------------------------------------
echo "<h3>카테고리 생성 중...</h3>";
$cats = ['IT기기', '리빙', '패션'];
for ($i=0; $i<3; $i++) {
    $catId = $i+1;  // 1,2,3
    $catName = $cats[$i];
    // INSERT IGNORE 또는 중복 검사 후 삽입
    $catSql = "INSERT IGNORE INTO categories (id, name) VALUES (?, ?)";
    $catStmt = $pdo->prepare($catSql);
    $catStmt->execute([$catId, $catName]);
}
echo "카테고리 3개 생성(또는 이미 존재시 무시).<br>";

// ----------------------------------------------------------------------
// (C) 게시글 12,000건 생성
//  - user_id 1..100, category_id 1..3, 각각 40개씩
// ----------------------------------------------------------------------
echo "<h3>게시글 대량 생성 중...</h3>";

$postsPerCategory = 40;
$totalCount = 0;

for ($user = 1; $user <= 100; $user++) {
    for ($cat = 1; $cat <= 3; $cat++) {
        for ($i = 1; $i <= $postsPerCategory; $i++) {
            $title = "테스트 글 - 유저{$user}, 카테고리{$cat} ({$i})";
            $content = "이것은 유저#{$user}가 카테고리#{$cat}에 작성한 테스트 글입니다 (번호: {$i}).";

            $insSql = "INSERT INTO posts (user_id, category_id, title, content, created_at)
                       VALUES (?, ?, ?, ?, NOW())";
            $insStmt = $pdo->prepare($insSql);
            $insStmt->execute([$user, $cat, $title, $content]);

            $totalCount++;
        }
    }
}

echo "총 {$totalCount} 건의 게시글이 생성되었습니다.<br>";

echo "<hr><strong>데이터 입력이 완료되었습니다. 이제 post_list.php에서 12000건 데이터를 확인하세요.</strong>";
