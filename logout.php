<?php
// logout.php
include_once __DIR__ . '/config/db.php';

// 세션 종료
session_destroy();
echo "<script>alert('로그아웃되었습니다.'); location.href='index.php';</script>";
