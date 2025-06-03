<?php
require_once 'config/app.php';
session_destroy();
header('Location: ' . BASE_URL . '/index.php');
exit();
?>
