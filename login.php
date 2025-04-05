<?php
// login.php
include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $pwPlain  = $_POST['password'];

    // 유저 조회
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($pwPlain, $user['password'])) {
        // 세션 저장
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        echo "<script>alert('로그인 성공'); location.href='index.php';</script>";
    } else {
        echo "<script>alert('로그인 실패'); history.back();</script>";
    }
    exit;
}
?>

<h2>로그인</h2>
<form method="post">
  아이디: <input type="text" name="username" required><br><br>
  비밀번호: <input type="password" name="password" required><br><br>
  <button type="submit">로그인</button>
</form>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
