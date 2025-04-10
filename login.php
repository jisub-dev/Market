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

    // 비밀번호 검증
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

<div class="container mt-4" style="max-width:500px;">
  <h2>로그인</h2>
  <form method="post">
    <div class="mb-3">
      <label class="form-label">아이디</label>
      <input type="text" name="username" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">비밀번호</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">로그인</button>
      <!-- 회원가입 버튼: register.php로 이동 -->
      <a href="register.php" class="btn btn-outline-secondary">회원가입</a>
    </div>
  </form>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
