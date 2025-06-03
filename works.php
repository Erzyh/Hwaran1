<?php
require_once 'config/db.php';
require_once 'config/app.php';
include 'includes/header.php';

$stmt = $pdo->prepare("SELECT * FROM projects WHERE status = '개발 완료'");
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="assets/css/works.css">

<style>
  body {
      font-family: 'Montserrat', sans-serif;
      background-color: #f4f7fc;
      margin: 0;
      padding: 0;
  }
  .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
  }
  .intro-slider {
      position: relative;
      overflow: hidden;
      margin: 0 auto 40px;
      max-width: 800px;
      border-radius: 15px;
  }
  .intro-slider .slider-wrapper {
      display: flex;
      transition: transform 0.5s ease;
  }
  .intro-slider .slide {
      min-width: 100%;
      position: relative;
  }
  .intro-slider .slide img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      display: block;
  }
  .intro-slider .slide-caption {
      position: absolute;
      bottom: 30px;
      left: 40px;
      background: rgba(0, 0, 0, 0.5);
      padding: 15px 25px;
      border-radius: 8px;
      color: #fff;
  }
  .intro-slider .slide-caption h2 {
      font-size: 1.8rem;
  }
  .intro-slider .slide-caption p {
      font-size: 1.1rem;
  }
  .intro-slider .slider-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(255, 255, 255, 0);
      color: #fff;
      border: none;
      font-size: 2.5rem;
      padding: 5px 10px;
      cursor: pointer;
      z-index: 10;
  }
  .intro-slider .slider-btn.prev {
      left: 10px;
  }
  .intro-slider .slider-btn.next {
      right: 10px;
  }
  h1.page-title {
      font-size: 2.8rem;
      text-align: center;
      margin-bottom: 20px;
      color: #333;
  }
  .filter-container {
      text-align: center;
      margin-bottom: 30px;
  }
  .filter-btn {
      padding: 10px 20px;
      margin: 0 5px;
      border: none;
      background: #ddd;
      color: #333;
      cursor: pointer;
      transition: background 0.3s;
  }
  .filter-btn.active {
      background: #007BFF;
      color: #fff;
  }
  .project-list {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
  }
  .project-card {
      background: #fff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      width: 300px;
      display: flex;
      flex-direction: column;
  }
  .project-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 12px 32px rgba(0,0,0,0.15);
  }
  .project-card img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      display: block;
  }
  .project-info {
      position: relative;
      padding: 20px;
      text-align: center;
      min-height: 180px;
  }
  .description-wrapper {
      margin-bottom: 50px;
  }
  .project-info h3 {
      font-size: 1.6rem;
      color: #007BFF;
      margin-bottom: 10px;
      font-weight: 600;
  }
  .project-info p {
      font-size: 1rem;
      color: #666;
      line-height: 1.4;
      margin: 0;
  }
  .project-btn {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: inline-block;
      padding: 10px 20px;
      border-radius: 30px;
      font-size: 1rem;
      text-decoration: none;
      transition: background 0.3s ease;
  }
  .download-btn {
      background: linear-gradient(45deg, #28a745, #218838);
      color: #fff;
  }
  .download-btn:hover {
      background: linear-gradient(45deg, #218838, #1e7e34);
  }
  .visit-btn {
      background: linear-gradient(45deg, #007BFF, #0069d9);
      color: #fff;
  }
  .visit-btn:hover {
      background: linear-gradient(45deg, #0069d9, #0056b3);
  }
  .invite-btn {
      background: linear-gradient(45deg, #ffc107, #e0a800);
      color: #333;
  }
  .invite-btn:hover {
      background: linear-gradient(45deg, #e0a800, #d39e00);
  }
  @media (max-width: 600px) {
      h1.page-title {
          font-size: 2.5rem;
      }
  }
  @media screen and (max-width: 768px) {
  .intro-slider {
      max-width: 100%;
      margin: 0 auto 20px;
      border-radius: 10px;
  }
  .intro-slider .slide img {
      height: 200px;
  }
  .intro-slider .slide-caption {
      bottom: 15px;
      left: 15px;
      padding: 10px 15px;
      font-size: 1rem;
  }
  .intro-slider .slider-btn {
      font-size: 2rem;
      padding: 5px;
  }

  .filter-container {
      margin-bottom: 20px;
  }
  .filter-btn {
      padding: 8px 15px;
      font-size: 14px;
      margin: 0 3px;
  }
}

@media screen and (max-width: 480px) {
  .intro-slider .slide img {
      height: 180px;
  }
  .intro-slider .slide-caption {
      font-size: 0.9rem;
      bottom: 10px;
      left: 10px;
  }
  .intro-slider .slider-btn {
      font-size: 1.8rem;
      padding: 4px;
  }
  
  .filter-btn {
      padding: 6px 10px;
      font-size: 12px;
      margin: 2px;
  }
}
</style>

<br><br>
<div class="intro-slider">
  <div class="slider-wrapper">
      <div class="slide">
          <img src="assets/img/slide_show/3.png" alt="슬라이드 1" onerror="this.src='assets/img/slider/placeholder.png';">
      </div>
      <div class="slide">
          <img src="assets/img/slide_show/2.png" alt="슬라이드 2" onerror="this.src='assets/img/slider/placeholder.png';">
      </div>
      <div class="slide">
          <img src="assets/img/slide_show/1.png" alt="슬라이드 3" onerror="this.src='assets/img/slider/placeholder.png';">
      </div>
  </div>
  <button class="slider-btn prev" id="introPrevBtn">&#10094;</button>
  <button class="slider-btn next" id="introNextBtn">&#10095;</button>
</div>

<div class="container">
    <div class="filter-container">
        <button class="filter-btn active" data-type="게임">게임</button>
        <button class="filter-btn" data-type="웹">웹</button>
        <button class="filter-btn" data-type="디스코드 봇">디스코드 봇</button>
        <button class="filter-btn" data-type="디자인">디자인</button>
        <button class="filter-btn" data-type="마인크래프트">마인크래프트</button>
    </div>

    <!-- 프로젝트 목록 -->
    <div id="project-list" class="project-list">
        <?php if ($projects): ?>
            <?php foreach ($projects as $project): ?>
                <div class="project-card" data-type="<?php echo htmlspecialchars($project['project_type']); ?>">
                    <img src="<?php echo htmlspecialchars($project['main_image']); ?>" alt="<?php echo htmlspecialchars($project['project_name']); ?>">
                    <div class="project-info">
                        <h3><?php echo htmlspecialchars($project['project_name']); ?></h3>
                        <div class="description-wrapper">
                          <p><?php echo htmlspecialchars($project['description']); ?></p>
                        </div>
                        <?php
                        $type = $project['project_type'];
                        $link = htmlspecialchars($project['external_link']);
                        if ($type === '게임' || $type === '마인크래프트') {
                            echo '<a href="'.$link.'" target="_blank" class="project-btn download-btn">다운로드</a>';
                        } elseif ($type === '웹') {
                            echo '<a href="'.$link.'" target="_blank" class="project-btn visit-btn">방문하기</a>';
                        } elseif ($type === '디스코드 봇') {
                            echo '<a href="'.$link.'" target="_blank" class="project-btn invite-btn">초대하기</a>';
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>데이터베이스 점검중입니다.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    const buttons = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.project-card');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const selectedType = button.getAttribute('data-type');
            projectCards.forEach(card => {
                if (card.getAttribute('data-type') === selectedType) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            buttons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const defaultButton = document.querySelector('[data-type="게임"]');
        if(defaultButton) defaultButton.click();
    });
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const introSliderWrapper = document.querySelector(".intro-slider .slider-wrapper");
    const introSlides = document.querySelectorAll(".intro-slider .slide");
    const introPrevBtn = document.getElementById("introPrevBtn");
    const introNextBtn = document.getElementById("introNextBtn");
    let introIndex = 0;
    const totalIntroSlides = introSlides.length;
    
    introNextBtn.addEventListener("click", function() {
        introIndex = (introIndex + 1) % totalIntroSlides;
        introSliderWrapper.style.transform = "translateX(-" + (introIndex * 100) + "%)";
    });
    
    introPrevBtn.addEventListener("click", function() {
        introIndex = (introIndex - 1 + totalIntroSlides) % totalIntroSlides;
        introSliderWrapper.style.transform = "translateX(-" + (introIndex * 100) + "%)";
    });
});
</script>

<?php include 'includes/footer.php'; ?>
