<?php
require_once '../config/db.php';
require_once '../config/app.php';

header('Content-Type: application/json');

$response = ['success' => false];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($project) {
        $response['success'] = true;
        $response['project'] = $project;
    }
}

echo json_encode($response);
?>
