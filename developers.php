<?php
require_once 'config/db.php';
require_once 'config/app.php';
include 'includes/header.php';
?>

<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/developers.css">

<div class="wrapper">
  <div class="content">
    <div class="container">
      <h2 class="page-title">Thanks For</h2>
      <div class="developer-grid">
          <div class="developer-card" data-role="개발">
              <h4 class="developer-name">이름</h4>
              <p class="developer-gerne">컴퓨터공학과 22</p>
              <span class="developer-role full-stack">Full Stack</span>
              <p class="developer-period">2024.10 ~</p>
              <span class="hover-role">개발</span>
          </div>
      </div>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>
</div>