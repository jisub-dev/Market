<?php
$host = 'localhost';     
$db   = 'market_db';     
$port = 3307;            // 3307로 포트 지정
$user = 'root';          // DBeaver에서 쓰는 계정
$pass = 'root@1234';     // 비밀번호

try {
    // 포트를 명시해야 함
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass);
    // 에러 모드 (개발용)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "DB 연결 실패: " . $e->getMessage();
    exit;
}

// 세션 스타트 (원하면 여기서 시작)
if (!session_id()) {
    session_start();
}
?>
