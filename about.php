<?php
require_once 'config/db.php';
require_once 'config/app.php';
include 'includes/header.php';
?>

<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/about.css">

<div class="about-page">
  <div class="container">
    <section id="club-info">
        <h1>화란은</h1>
        <p>
        HWARAN은 창업과 IT, 게임, 웹 등 다양한 분야에서의 도전과 창의성을 추구하는 동아리입니다.
        다양한 IT 기술들을 학습하고, 프로젝트에 적용하여 지속적인 발전을 목표로 하고 있습니다.
        </p>
    </section>

    <section id="activities">
      <h2>이런 활동을 해요!</h2>
      <div class="slider-container" id="activitySlider">
        <div class="slider-wrapper">
          <div class="slide">
            <img src="assets/img/activities/creator_conference.jpg" alt="Creator Conference" onerror="this.src='assets/img/activities/placeholder.jpg';">
            <div class="slide-caption">
              <h3>Creator Conference</h3>
              <p>기획 및 개발사항 발표</p>
            </div>
          </div>
          <div class="slide">
            <img src="assets/img/activities/mentoring.jpg" alt="Mentoring" onerror="this.src='assets/img/activities/placeholder.jpg';">
            <div class="slide-caption">
              <h3>Mentoring</h3>
              <p>지도교수와의 피드백</p>
            </div>
          </div>
          <div class="slide">
            <img src="assets/img/activities/devsprint.jpg" alt="DevSprint" onerror="this.src='assets/img/activities/placeholder.jpg';">
            <div class="slide-caption">
              <h3>DevSprint</h3>
              <p>집중 개발 세션</p>
            </div>
          </div>
          <div class="slide">
            <img src="assets/img/activities/linc.jpg" alt="LINC" onerror="this.src='assets/img/activities/placeholder.jpg';">
            <div class="slide-caption">
              <h3>LINC</h3>
              <p>동명대학교 행사 참여</p>
            </div>
          </div>
        </div>
        <button class="slider-btn prev" id="activityPrevBtn">&#10094;</button>
        <button class="slider-btn next" id="activityNextBtn">&#10095;</button>
      </div>
    </section>

    <section id="history">
      <h2>주요 기록</h2>
      <div class="timeline">
        <?php
        try {
          $stmt = $pdo->query("SELECT * FROM club_history ORDER BY event_date ASC");
          $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
          if (count($history) > 0) {
            foreach ($history as $row) {
              echo '<div class="timeline-item">';
              echo '<div class="date">' . date('Y-m', strtotime($row['event_date'])) . '</div>';
              echo '<div class="desc">' . htmlspecialchars($row['event_description']) . '</div>';
              echo '</div>';
            }
          } else {
            echo '<p>연혁 정보가 없습니다.</p>';
          }
        } catch (PDOException $e) {
          echo '<p>DB 에러: ' . $e->getMessage() . '</p>';
        }
        ?>
      </div>
    </section>

    <section id="team">
      <h2>2025 운영진</h2>
      <div class="team-list">

        <div class="team-item">
          <img src="assets/img/teamLead/이름.jpg" alt="이름">
          <h4>이름</h4>
          <p>팀장<br>컴퓨터공학과 21학번</p>
          <p>2024.07 - 2025.06 화란 팀장<br>
          2025.07 - 2026.02 화란 부팀장</p>
        </div>

    </section>

    <section id="members">
      <h2>2025 멤버</h2>
      <div class="member-list">
        <?php
        try {
          $stmt = $pdo->query("SELECT * FROM members ORDER BY id ASC");
          $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
          if (count($members) > 0) {
            foreach ($members as $member) {
              echo '<div class="member-item">';
              echo '<h4>' . htmlspecialchars($member['name']) . '</h4>';
              if (!empty($member['aboutMe'])) {
                echo '<p>' . htmlspecialchars($member['aboutMe']) . '</p>';
              } else {
                echo '<p>' . htmlspecialchars('-') . '</p>';
              }
              echo '</div>';
            }
          } else {
            echo '<p>멤버 정보가 없습니다.</p>';
          }
        } catch (PDOException $e) {
          echo '<p>DB 에러: ' . $e->getMessage() . '</p>';
        }
        ?>
      </div>
    </section>

  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const sliderWrapper = document.querySelector("#activitySlider .slider-wrapper");
  const slides = document.querySelectorAll("#activitySlider .slide");
  const prevBtn = document.getElementById("activityPrevBtn");
  const nextBtn = document.getElementById("activityNextBtn");
  let currentIndex = 0;
  const totalSlides = slides.length;
  
  nextBtn.addEventListener("click", function() {
    currentIndex = (currentIndex + 1) % totalSlides;
    sliderWrapper.style.transform = "translateX(-" + (currentIndex * 100) + "%)";
  });
  
  prevBtn.addEventListener("click", function() {
    currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
    sliderWrapper.style.transform = "translateX(-" + (currentIndex * 100) + "%)";
  });
});
</script>

<?php include 'includes/footer.php'; ?>