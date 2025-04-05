<?php
// register.php
include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $pwPlain  = $_POST['password'];
    $email    = trim($_POST['email']);

    // 비번 해싱
    $hash = password_hash($pwPlain, PASSWORD_DEFAULT);

    // 중복 체크
    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username=?");
    $check->execute([$username]);
    if ($check->fetchColumn() > 0) {
        echo "<script>alert('이미 존재하는 아이디입니다.'); history.back();</script>";
        exit;
    }

    // INSERT
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?,?,?)");
    $stmt->execute([$username, $hash, $email]);

    echo "<script>alert('회원가입 완료!'); location.href='login.php';</script>";
    exit;
}
?>

<h2>회원가입</h2>
<form method="post">
    아이디: <input type="text" name="username" required><br><br>
    비밀번호: <input type="password" name="password" required><br><br>
    이메일: <input type="email" name="email"><br><br>
    <button type="submit">가입하기</button>
</form>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
