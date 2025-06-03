<?php
include '../includes/header.php';
require_once '../includes/auth.php';
require_once '../config/db.php';

$messages = [];
$selected_user = isset($_GET['user']) ? $_GET['user'] : '';

try {
    $stmt = $pdo->prepare("SELECT sr.*, m.name FROM survey_responses sr JOIN members m ON sr.user_id = m.id ORDER BY sr.id DESC");
    $stmt->execute();
    $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT DISTINCT m.name FROM survey_responses sr JOIN members m ON sr.user_id = m.id ORDER BY m.name ASC");
    $stmt->execute();
    $user_names = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($selected_user) {
        $stmt = $pdo->prepare("SELECT sr.*, m.name FROM survey_responses sr JOIN members m ON sr.user_id = m.id WHERE m.name = ? ORDER BY sr.id DESC");
        $stmt->execute([$selected_user]);
        $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $messages[] = ['type' => 'error', 'text' => '데이터베이스 오류: ' . htmlspecialchars($e->getMessage())];
}
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/survey_results.css">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<br><br>
<main class="survey-results-container">
    <h1><i class='bx bx-poll'></i> 설문 결과 보기</h1>

    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $msg): ?>
            <div class="message <?php echo $msg['type'] === 'success' ? 'success-message' : 'error-message'; ?>">
                <?php echo htmlspecialchars($msg['text']); ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form action="" method="GET" class="user-filter">
        <label for="user">사용자 선택:</label>
        <select name="user" id="user" onchange="this.form.submit()">
            <option value="">모든 사용자</option>
            <?php foreach ($user_names as $user): ?>
                <option value="<?php echo htmlspecialchars($user['name']); ?>" <?php echo ($selected_user == $user['name']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($user['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (!empty($responses)): ?>
        <div class="responses">
            <?php foreach ($responses as $response): ?>
                <div class="response-card">
                    <h3><i class='bx bx-user'></i> <?php echo htmlspecialchars($response['name']); ?>님의 응답</h3>
                    <div class="response-section">
                        <p><strong>MT 참여 계획:</strong> <?php echo htmlspecialchars($response['question1']); ?></p>
                        <p><strong>희망 날짜:</strong> <?php echo htmlspecialchars($response['question2']); ?></p>
                    </div>
                    <div class="response-section">
                        <p><strong>희망 지역:</strong> <?php echo htmlspecialchars($response['question3']); ?></p>
                        <p><strong>하고 싶은 활동:</strong> <?php echo htmlspecialchars($response['question4']); ?></p>
                    </div>
                    <div class="response-section">
                        <p><strong>선호 숙소:</strong> <?php echo htmlspecialchars($response['question5']); ?></p>
                        <p><strong>가용 예산:</strong> <?php echo htmlspecialchars($response['question6']); ?></p>
                    </div>
                    <div class="response-section">
                        <p><strong>여행 기간:</strong> <?php echo htmlspecialchars($response['question7']); ?></p>
                        <p><strong>알레르기 또는 못 먹는 음식:</strong> <?php echo nl2br(htmlspecialchars($response['question9'])); ?></p>
                    </div>
                    <div class="response-section">
                        <p><strong>기타 건의 사항:</strong> <?php echo nl2br(htmlspecialchars($response['question10'])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <br><br><p>현재 등록된 설문 응답이 없습니다.</p><br><br>
    <?php endif; ?>
</main>
<br><br>

<?php include '../includes/footer.php'; ?>
