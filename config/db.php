<?php
$host = 'localhost';  // 호스트
$dbname = 'db_name';  // 데이터베이스 이름
$username = 'user_name';  // 데이터베이스 사용자
$password = '';  // 데이터베이스 비밀번호

//$dbname = 'db_name';  // 데이터베이스 이름
//$username = 'root';  // 데이터베이스 사용자
//$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
