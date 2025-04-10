<?php
// register.php (회원가입 페이지)
include_once __DIR__ . '/config/db.php';      // DB 연결
include_once __DIR__ . '/includes/header.php'; // 상단 메뉴, Bootstrap 로드

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) 폼 전송된 데이터 가져오기
    $username     = trim($_POST['username']);
    $passwordRaw  = $_POST['password'];
    $passwordConf = $_POST['password_confirm'] ?? '';
    $email        = trim($_POST['email']);

    // 2) 비밀번호 확인 로직
    if ($passwordRaw !== $passwordConf) {
        echo "<script>alert('비밀번호 확인이 일치하지 않습니다.'); history.back();</script>";
        exit;
    }

    // 3) 아이디 중복 체크
    $chkSql = "SELECT COUNT(*) FROM users WHERE username = ?";
    $chkStmt = $pdo->prepare($chkSql);
    $chkStmt->execute([$username]);
    $count = $chkStmt->fetchColumn();
    if ($count > 0) {
        echo "<script>alert('이미 존재하는 아이디입니다.'); history.back();</script>";
        exit;
    }

    // 4) 비밀번호 해싱
    $passwordHash = password_hash($passwordRaw, PASSWORD_DEFAULT);

    // 5) DB에 INSERT
    $regSql = "INSERT INTO users (username, password, email, created_at)
               VALUES (?, ?, ?, NOW())";
    $regStmt = $pdo->prepare($regSql);
    $regStmt->execute([$username, $passwordHash, $email]);

    // 6) 가입 완료 후 처리
    echo "<script>alert('회원가입이 완료되었습니다! 로그인 페이지로 이동합니다.'); location.href='login.php';</script>";
    exit;
}
?>

<div class="container mt-4" style="max-width:500px;">
  <h2>회원가입</h2>
  <form method="post">
    <div class="mb-3">
      <label for="username" class="form-label">아이디</label>
      <input type="text" name="username" id="username"
             class="form-control" required />
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">비밀번호</label>
      <input type="password" name="password" id="password"
             class="form-control" required />
    </div>
    <div class="mb-3">
      <label for="password_confirm" class="form-label">비밀번호 확인</label>
      <input type="password" name="password_confirm" id="password_confirm"
             class="form-control" required />
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">이메일</label>
      <input type="email" name="email" id="email"
             class="form-control" />
    </div>
    <button type="submit" class="btn btn-primary">회원가입</button>
  </form>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
