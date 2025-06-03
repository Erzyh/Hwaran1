<?php 
require_once 'config/db.php';
require_once 'config/app.php';
include 'includes/header.php';

// 현재 날짜
$current_date = date('Y-m-d');

// 모집 기간 체크
$is_recruiting = ($current_date >= RECRUITMENT_START && $current_date <= RECRUITMENT_END);

$stmt = $pdo->prepare("SELECT * FROM faq");
$stmt->execute();
$faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/recruiting.css">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<div class="container">
    <h3>모집 대상</h3>
    <div class="target_box">
        <div class="box">
            <i class='bx bxs-check-circle'></i>
            <p>배울 의지가 있고</p>
        </div>
        <div class="box">
            <i class='bx bxs-game'></i>
            <p>게임 혹은 웹에 관심이 있는</p>
        </div>
        <div class="box">
            <i class='bx bxs-heart-circle'></i>
            <p>열정적이신 분</p>
        </div>
    </div><br>

    <h3>모집 포지션</h3>
    <div class="positions_box">
        <div class="box">
            <i class='bx bx-joystick'></i>
            <p>게임 개발</p>
        </div>
        <div class="box">
            <i class='bx bx-code-alt'></i>
            <p>웹 개발</p>
        </div>
        <div class="box">
            <i class='bx bx-paint'></i>
            <p>그래픽</p>
        </div>
        <div class="box">
            <i class='bx bx-music'></i>
            <p>사운드</p>
        </div>
    </div><br>

    <h3>모집 일정</h3>
    <div class="schedule_box">
        <div class="schedule-item">
            <span class="activity">서류 접수</span>
            <span class="date">2025.01.20. - 2025.03.14.</span>
        </div>
        <div class="schedule-item">
            <span class="activity">결과 발표</span>
            <span class="date">2025.03.14</span>
        </div>
        <div class="schedule-item">
            <span class="activity">신입부원 OT</span>
            <span class="date">2025.03.14</span>
        </div>
        <div class="schedule-item">
            <span class="activity">개강총회</span>
            <span class="date">2025.03.14.</span>
        </div>
    </div><br>

    <div class="apply-button">
        <?php if ($is_recruiting): ?>
            <a href="<?php echo BASE_URL; ?>/apply.php" class="btn-apply">지원하기</a>
        <?php else: ?>
            <div class="no-recruitment">
                <p>현재 모집 기간이 아닙니다!</p>
            </div>
        <?php endif; ?>
    </div>
    <br><hr><br>

    <h3>FAQ</h3>
    <?php if ($faqs): ?>
        <ul class="faq-list">
            <?php foreach ($faqs as $faq): ?>
                <li class="faq-item">
                    <div class="faq-question">
                        <span><strong><?php echo htmlspecialchars($faq['question']); ?></strong></span>
                        <span class="faq-arrow">▼</span>
                    </div>
                    <div class="faq-answer">
                        <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>자주 묻는 질문이 없습니다.</p>
    <?php endif; ?>
</div>

<script>
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', () => {
            const answer = question.nextElementSibling;
            answer.style.display = answer.style.display === 'block' ? 'none' : 'block';
            const arrow = question.querySelector('.faq-arrow');
            arrow.textContent = arrow.textContent === '▼' ? '▲' : '▼';
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
