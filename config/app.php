<?php
define('SITE_NAME', 'HWARAN');
define('BASE_URL', 'http://hwaran.kr');
define('ADMIN_EMAIL', 'email@edit.here');

define('RECRUITMENT_START', '2025-01-20');
define('RECRUITMENT_END', '2025-03-14');
date_default_timezone_set('Asia/Seoul');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
