<?php
include '../includes/auth.php';
include '../includes/header.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

$current_date = date('Y-m-d');

$stmt = $pdo->prepare("SELECT student_number, name, aboutMe, grade FROM members WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo '<div class="error-message">사용자 정보를 불러오는데 실패했습니다.</div>';
    include '../includes/footer.php';
    exit();
}

$password_error = '';
$password_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('잘못된 요청입니다.');
    }

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $pdo->prepare("SELECT password FROM members WHERE id = ?");
    $stmt->execute([$user_id]);
    $stored_password = $stmt->fetchColumn();

    if (!password_verify($current_password, $stored_password)) {
        $password_error = '현재 비밀번호가 일치하지 않습니다.';
    } elseif ($new_password !== $confirm_password) {
        $password_error = '새 비밀번호와 확인 비밀번호가 일치하지 않습니다.';
    } elseif (strlen($new_password) < 6) {
        $password_error = '새 비밀번호는 최소 6자 이상이어야 합니다.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{6,}$/', $new_password)) {
        $password_error = '새 비밀번호는 대문자, 소문자, 숫자, 특수문자를 포함해야 합니다.';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE members SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $user_id])) {
            $password_success = '비밀번호가 성공적으로 변경되었습니다.';
        } else {
            $password_error = '비밀번호 변경에 실패했습니다. 다시 시도해주세요.';
        }
    }
}

$intro_error = '';
$intro_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_intro'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('잘못된 요청입니다.');
    }

    $new_intro = trim($_POST['aboutMe']);

    if (strlen($new_intro) > 255) {
        $intro_error = '한 줄 소개는 최대 255자까지 입력할 수 있습니다.';
    } else {
        $stmt = $pdo->prepare("UPDATE members SET aboutMe = ? WHERE id = ?");
        if ($stmt->execute([$new_intro, $user_id])) {
            $intro_success = '한 줄 소개가 성공적으로 변경되었습니다.';
            $user['aboutMe'] = $new_intro;
        } else {
            $intro_error = '한 줄 소개 변경에 실패했습니다. 다시 시도해주세요.';
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

?>

<main>
    <h1><i class='bx bxs-user'></i> 내 프로필</h1>

    <section class="card profile-info">
        <h2><i class='bx bx-info-circle'></i> 사용자 정보</h2>
        <p><strong>학번:</strong> <?php echo htmlspecialchars($user['student_number']); ?></p>
        <p><strong>이름:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>한 줄 소개:</strong> <?php echo htmlspecialchars($user['aboutMe']); ?></p>
        <p><strong>등급:</strong> <?php echo htmlspecialchars($user['grade']); ?></p>
    </section>

    <section class="card change-intro">
        <h2><i class='bx bx-edit-alt'></i> 한 줄 소개 변경</h2>
        <?php if ($intro_error): ?>
            <div class="error-message"><?php echo htmlspecialchars($intro_error); ?></div>
        <?php endif; ?>
        <?php if ($intro_success): ?>
            <div class="success-message"><?php echo htmlspecialchars($intro_success); ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <textarea name="aboutMe" rows="3" maxlength="255" placeholder="한 줄 소개를 입력하세요 (최대 255자)"><?php echo htmlspecialchars($user['aboutMe']); ?></textarea>
            <button type="submit" name="change_intro"><i class='bx bx-save'></i> 변경</button>
        </form>
    </section>

    <section class="card change-password">
        <h2><i class='bx bx-lock-alt'></i> 비밀번호 변경</h2>
        <?php if ($password_error): ?>
            <div class="error-message"><?php echo htmlspecialchars($password_error); ?></div>
        <?php endif; ?>
        <?php if ($password_success): ?>
            <div class="success-message"><?php echo htmlspecialchars($password_success); ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="password" name="current_password" placeholder="현재 비밀번호" required>
            <input type="password" name="new_password" placeholder="새 비밀번호" required>
            <input type="password" name="confirm_password" placeholder="새 비밀번호 확인" required>
            <button type="submit" name="change_password"><i class='bx bx-save'></i> 변경</button>
        </form>
    </section>

</main>

<?php include '../includes/footer.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/profile.css">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
