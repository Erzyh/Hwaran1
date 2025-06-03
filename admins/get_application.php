<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "유효하지 않은 요청입니다.";
    exit();
}

$app_id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
    $stmt->execute([$app_id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($application) {
        echo "<h3>지원자 정보</h3>";
        echo "<p><strong>학번:</strong> " . htmlspecialchars($application['student_id']) . "</p>";
        echo "<p><strong>이름:</strong> " . htmlspecialchars($application['name']) . "</p>";
        echo "<p><strong>전화번호:</strong> " . htmlspecialchars($application['phone_number']) . "</p>";
        echo "<p><strong>희망 포지션:</strong> " . htmlspecialchars($application['desired_position']) . "</p>";
        echo "<p><strong>화란을 알게 된 방법:</strong> " . htmlspecialchars($application['how_heard']) . "</p>";
        echo "<p><strong>동아리 지원 계기:</strong> " . nl2br(htmlspecialchars($application['motivation'])) . "</p>";
        echo "<p><strong>개발해 보고 싶은 프로젝트:</strong> " . htmlspecialchars($application['wants_project']);
        if ($application['wants_project'] === '있다') {
            echo " - " . htmlspecialchars($application['project_description']);
        }
        echo "</p>";
        echo "<p><strong>노트북/데스크탑 성능:</strong> " . nl2br(htmlspecialchars($application['laptop_specs'])) . "</p>";
        echo "<p><strong>사용해본 도구/언어:</strong> " . htmlspecialchars($application['tools_languages']) . "</p>";
        echo "<p><strong>진행한 프로젝트:</strong> " . htmlspecialchars($application['has_project']);
        if ($application['has_project'] === '있다') {
            echo " - " . htmlspecialchars($application['project_details']);
        }
        echo "</p>";
        echo "<p><strong>동아리에서 해보고 싶은 활동:</strong> " . nl2br(htmlspecialchars($application['desired_activities'])) . "</p>";
        echo "<p><strong>새롭게 배우고 싶은 언어나 도구:</strong> " . nl2br(htmlspecialchars($application['new_languages_tools'])) . "</p>";
        echo "<p><strong>선호하는 커뮤니케이션 방식:</strong> " . htmlspecialchars($application['preferred_communication']);
        if ($application['preferred_communication'] === '기타') {
            echo " - " . htmlspecialchars($application['communication_other']);
        }
        echo "</p>";
        echo "<p><strong>토요일 개인 업무:</strong> " . nl2br(htmlspecialchars($application['personal_activities'])) . "</p>";
        echo "<p><strong>한 줄 소개:</strong> " . htmlspecialchars($application['self_introduction']) . "</p>";
    } else {
        echo "지원서를 찾을 수 없습니다.";
    }
} catch (PDOException $e) {
    echo "데이터베이스 오류: " . htmlspecialchars($e->getMessage());
}
?>
