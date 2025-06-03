<?php
require_once 'config/db.php';
require_once 'config/app.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = $_POST['student_number'];
    $name = $_POST['name'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // 비밀번호 확인
    if ($password !== $password_confirm) {
        $error = "비밀번호가 일치하지 않습니다.";
    } else {
        // 이미 존재하는 학번인지 확인
        $stmt = $pdo->prepare("SELECT * FROM members WHERE student_number = :student_number");
        $stmt->execute(['student_number' => $student_number]);
        $existing_member = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_member) {
            $error = "이미 등록된 학번입니다.";
        } else {
            // 비밀번호 해싱
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO members (student_number, name, password, status) VALUES (:student_number, :name, :password, 'pending')");
            $stmt->execute([
                'student_number' => $student_number,
                'name' => $name,
                'password' => $hashed_password
            ]);

            $success = "회원가입이 완료되었습니다. 관리자의 승인을 기다려주세요.";
        }
    }
}
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/register.css">

<div class="container">
    <h1>회원가입</h1>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label>학번:</label>
        <input type="text" name="student_number" required>
        <label>이름:</label>
        <input type="text" name="name" required>
        <label>비밀번호:</label>
        <input type="password" name="password" required>
        <label>비밀번호 확인:</label>
        <input type="password" name="password_confirm" required>
        <button type="submit">회원가입</button>
    </form>
    <p>이미 계정이 있으신가요? <a href="login.php">로그인</a></p>
</div>

<?php include 'includes/footer.php'; ?>
