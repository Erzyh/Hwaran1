<?php
include '../includes/auth.php';
include '../includes/header.php';
require_once '../config/db.php';

$survey_error = '';
$survey_success = '';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM survey_responses WHERE user_id = ?");
$stmt->execute([$user_id]);
$has_submitted = $stmt->fetchColumn();

if ($has_submitted > 0) {
    echo "<main class='survey-container'><h1>설문이 이미 제출되었습니다</h1><p>다시 설문에 참여할 수 없습니다.</p></main>";
    include '../includes/footer.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_survey'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('잘못된 요청입니다.');
    }

    $question1 = $_POST['question1'];
    $question2 = isset($_POST['question2']) ? implode(',', $_POST['question2']) : '';
    $question3 = isset($_POST['question3']) ? implode(',', $_POST['question3']) : '';
    $question3_2 = $_POST['question3_2'] ?? '';
    $question4 = isset($_POST['question4']) ? implode(',', $_POST['question4']) : '';
    $question5 = $_POST['question5'];
    $question6 = $_POST['question6'];
    $question7 = $_POST['question7'];
    $question9 = $_POST['question9'] ?? '';
    $question10 = $_POST['question10'] ?? '';

    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO survey_responses (user_id, question1, question2, question3, question3_2, question4, question5, question6, question7, question9, question10) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $question1, $question2, $question3, $question3_2, $question4, $question5, $question6, $question7, $question9, $question10])) {
        $survey_success = '설문이 성공적으로 제출되었습니다.';
    } else {
        $survey_error = '설문 제출에 실패했습니다. 다시 시도해주세요.';
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/survey.css">

<br><br>
<main class="survey-container">
    <h1><i class='bx bx-edit'></i> 화란 겨울 MT 설문</h1>
    <?php if ($survey_error): ?>
        <div class="error-message"><?php echo htmlspecialchars($survey_error); ?></div>
    <?php endif; ?>
    <?php if ($survey_success): ?>
        <div class="success-message"><?php echo htmlspecialchars($survey_success); ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <section>
            <h2><i class='bx bx-question-mark'></i> 1. 화란 겨울 MT에 참여하실 계획이신가요?</h2>
            <label><input type="radio" name="question1" value="예산만 적당하면" required> 예산만 적당하면 참여할 예정이다.</label><br>
            <label><input type="radio" name="question1" value="기간만 맞으면"> 기간만 맞으면 참여할 예정이다.</label><br>
            <label><input type="radio" name="question1" value="특별한 일 없으면"> 특별한 일만 없으면 참여할 예정이다.</label><br>
            <label><input type="radio" name="question1" value="참여 생각 없다"> 딱히 참여할 생각이 없다.</label><br>
        </section>

        <div id="additional-questions">
            <section>
                <h2><i class='bx bx-calendar'></i> 2. 특히 원하시는 날짜가 있으신가요?</h2>
                <label><input type="checkbox" name="question2[]" value="01월 07일"> 01월 07일 (화) - 01월 09일 (목)</label><br>
                <label><input type="checkbox" name="question2[]" value="01월 14일"> 01월 14일 (화) - 01월 16일 (목)</label><br>
                <label><input type="checkbox" name="question2[]" value="01월 21일"> 01월 21일 (화) - 01월 23일 (목)</label><br>
                <label><input type="checkbox" name="question2[]" value="그 외의 날짜"> 그 외의 날짜면 좋겠다.</label>
            </section>

            <section>
                <h2><i class='bx bx-map'></i> 3. 원하시는 지역이 있으신가요?</h2>
                <label><input type="checkbox" name="question3[]" value="경기도"> 경기도</label><br>
                <label><input type="checkbox" name="question3[]" value="강원도"> 강원도</label><br>
                <label><input type="checkbox" name="question3[]" value="제주도"> 제주도</label><br>
                <label><input type="checkbox" name="question3[]" value="경상남도"> 경상남도</label><br>
                <label><input type="checkbox" name="question3[]" value="경상북도"> 경상북도</label><br>
                <label><input type="checkbox" name="question3[]" value="충청남도"> 충청남도</label><br>
                <label><input type="checkbox" name="question3[]" value="충청북도"> 충청북도</label><br>
                <label><input type="checkbox" name="question3[]" value="전라남도"> 전라남도</label><br>
                <label><input type="checkbox" name="question3[]" value="전라북도"> 전라북도</label><br>
                <label><input type="checkbox" name="question3[]" value="부산광역시"> 부산광역시</label><br>
                <label><input type="checkbox" name="question3[]" value="대전광역시"> 대전광역시</label><br>
                <label><input type="checkbox" name="question3[]" value="남해"> 남해</label><br>
                <textarea name="question3_2" rows="3" placeholder="특별히 하고 싶은 활동이 있으신가요?"></textarea>
            </section>

            <section>
                <h2><i class='bx bx-star'></i> 4. 놀러가서 가장 하고 싶은 것은 무엇인가요?</h2>
                <label><input type="checkbox" name="question4[]" value="맛집 탐방"> 맛집 탐방</label><br>
                <label><input type="checkbox" name="question4[]" value="놀거리"> 놀거리 (놀이공원, 아쿠아리움, 스키장 등)</label><br>
                <label><input type="checkbox" name="question4[]" value="체험"> 체험 (동굴, 카트, 짚라인, 낚시 등)</label><br>
                <label><input type="checkbox" name="question4[]" value="힐링"> 힐링 (산책, 등산)</label><br>
                <textarea name="question4_2" rows="2" placeholder="그 외 하고 싶은 활동을 입력하세요"></textarea>
            </section>

            <section>
                <h2><i class='bx bx-home'></i> 5. 선호하는 숙소의 형태는 무엇인가요?</h2>
                <label><input type="radio" name="question5" value="상관 없음" required> 상관 없음</label><br>
                <label><input type="radio" name="question5" value="호텔"> 호텔</label><br>
                <label><input type="radio" name="question5" value="펜션"> 펜션</label><br>
                <label><input type="radio" name="question5" value="풀/홈 빌라"> 풀/홈 빌라</label>
            </section>

            <section>
                <h2><i class='bx bx-wallet'></i> 6. 가용 가능한 예산이 얼마인가요?</h2>
                <label><input type="radio" name="question6" value="상관 없음" required> 상관 없음</label><br>
                <label><input type="radio" name="question6" value="10만원 이내"> 10만원 이내</label><br>
                <label><input type="radio" name="question6" value="15만원 이내"> 15만원 이내</label><br>
                <label><input type="radio" name="question6" value="20만원 이내"> 20만원 이내</label><br>
                <label><input type="radio" name="question6" value="30만원 이내"> 30만원 이내</label><br>
                <label><input type="radio" name="question6" value="40만원 이내"> 40만원 이내</label>
            </section>

            <section>
                <h2><i class='bx bx-time-five'></i> 7. 여행 기간은 어느 정도가 적당하다고 생각하시나요?</h2>
                <label><input type="radio" name="question7" value="상관 없음" required> 상관 없음</label><br>
                <label><input type="radio" name="question7" value="1박 2일"> 1박 2일</label><br>
                <label><input type="radio" name="question7" value="무박 2일"> 무박 2일</label><br>
                <label><input type="radio" name="question7" value="2박 3일"> 2박 3일</label>
            </section>

            <section>
                <h2><i class='bx bx-food-menu'></i> 9. 알레르기 또는 못 먹는 음식이 있으신가요?</h2>
                <textarea name="question9" rows="3" placeholder="알레르기 또는 못 먹는 음식을 적어주세요"></textarea>
            </section>

            <section>
                <h2><i class='bx bx-comment-detail'></i> 10. 별도 여행 관련 건의 사항</h2>
                <textarea name="question10" rows="3" placeholder="기타 건의 사항이 있다면 적어주세요"></textarea>
            </section>
        </div>

        <button type="submit" name="submit_survey"><i class='bx bx-send'></i> 제출하기</button>
    </form>
</main>
<br><br>

<script>
    const question1Radios = document.querySelectorAll('input[name="question1"]');
    const additionalQuestions = document.getElementById('additional-questions');

    question1Radios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === '참여 생각 없다') {
                additionalQuestions.style.display = 'none';
            } else {
                additionalQuestions.style.display = 'block';
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
