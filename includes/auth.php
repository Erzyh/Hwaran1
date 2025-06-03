<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/app.php';

function getUserRole() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '비회원';
}

function isAdmin() {
    return getUserRole() === '관리자';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin($redirect = true) {
    if (!isLoggedIn()) {
        if ($redirect) {
            header('Location: ' . BASE_URL . '/login.php');
            exit();
        }
        return false;
    }
    return true;
}

function requireAdmin($redirect = true) {
    if (!isAdmin()) {
        if ($redirect) {
            header('Location: ' . BASE_URL . '/login.php');
            exit();
        }
        return false;
    }
    return true;
}

$current_url = $_SERVER['REQUEST_URI'];

$protected_paths = ['/members', '/admin'];

foreach ($protected_paths as $path) {
    if (strpos($current_url, $path) !== false) {
        requireLogin();
        break;
    }
}
?>
