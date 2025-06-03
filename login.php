<?php
require_once 'config/db.php';
require_once 'config/app.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = $_POST['student_number'];
    $password = $_POST['password'];

    // 회원 정보 조회
    $stmt = $pdo->prepare("SELECT * FROM members WHERE student_number = :student_number");
    $stmt->execute(['student_number' => $student_number]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member && password_verify($password, $member['password'])) {
        if ($member['status'] === 'approved') {
            $_SESSION['user_id'] = $member['id'];
            $_SESSION['user_name'] = $member['name'];
            $_SESSION['user_role'] = $member['grade'];
            header('Location: ' . BASE_URL . '/index.php');
            exit();
        } else {
            $error = "관리자의 승인이 필요합니다.";
        }
    } else {
        $error = "학번 또는 비밀번호가 올바르지 않습니다.";
    }
}
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/login.css">

<div class="container">
    <h1>로그인</h1>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label>학번:</label>
        <input type="text" name="student_number" required>
        <label>비밀번호:</label>
        <input type="password" name="password" required>
        <button type="submit">로그인</button>
    </form>
    <p>계정이 없으신가요? <a href="register.php">회원가입</a></p>
</div>

<?php include 'includes/footer.php'; ?>
