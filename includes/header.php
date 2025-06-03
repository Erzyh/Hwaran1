<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php'; 
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/header.css">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<header>
    <div class="header-container">
        <div class="logo-img">
            <a href="<?php echo BASE_URL; ?>/index.php">
                <img src="<?php echo BASE_URL; ?>/assets/img/hwaran_logo.png" alt="Hwaran Logo" class="logo">
            </a>
        </div>

        <nav id="nav">
            <div class="menu-close" id="mobile-menu-close">
                <i class='bx bx-x'></i>
            </div>
            <ul id="nav-list">
                <li><a href="<?php echo BASE_URL; ?>/about.php">ABOUT</a></li>
                <li><a href="<?php echo BASE_URL; ?>/works.php">WORKS</a></li>
                <li><a href="<?php echo BASE_URL; ?>/recruiting.php">RECRUITING</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?php echo BASE_URL; ?>/members/profile.php">PROFILE</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/members/projects.php">PROJECT</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="<?php echo BASE_URL; ?>/admins/hwaran.php">HWARAN</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo BASE_URL; ?>/logout.php">LOGOUT</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>/login.php">LOGIN</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="menu-toggle" id="mobile-menu">
            <i class='bx bx-menu'></i>
        </div>
    </div>
</header>

<script>
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuClose = document.getElementById('mobile-menu-close');
    const nav = document.getElementById('nav');

    mobileMenu.addEventListener('click', () => {
        nav.classList.add('show');
    });

    mobileMenuClose.addEventListener('click', () => {
        nav.classList.remove('show');
    });
</script>
</body>
</html>