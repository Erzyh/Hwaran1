<?php 
include '../includes/header.php';
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/app.php';

$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // FaQ 추가
        if ($action === 'add_faq') {
            $question = trim($_POST['question']);
            $answer = trim($_POST['answer']);
            if (!empty($question) && !empty($answer)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO faq (question, answer) VALUES (?, ?)");
                    $stmt->execute([$question, $answer]);
                    $messages[] = ['type' => 'success', 'text' => 'FAQ가 성공적으로 추가되었습니다.'];
                } catch (PDOException $e) {
                    $messages[] = ['type' => 'error', 'text' => '데이터베이스 오류: ' . htmlspecialchars($e->getMessage())];
                }
            } else {
                $messages[] = ['type' => 'error', 'text' => '모든 필드를 입력해주세요.'];
            }
        }

        // FAQ 삭제
        if ($action === 'delete_faq') {
            $faq_id = intval($_POST['faq_id']);
            try {
                $stmt = $pdo->prepare("DELETE FROM faq WHERE id = ?");
                $stmt->execute([$faq_id]);
                $messages[] = ['type' => 'success', 'text' => 'FAQ가 성공적으로 삭제되었습니다.'];
            } catch (PDOException $e) {
                $messages[] = ['type' => 'error', 'text' => '데이터베이스 오류: ' . htmlspecialchars($e->getMessage())];
            }
        }

        // FAQ 수정 (업데이트)
        if ($action === 'update_faq') {
            $faq_id = intval($_POST['faq_id']);
            $question = trim($_POST['question']);
            $answer = trim($_POST['answer']);
            if (!empty($question) && !empty($answer)) {
                try {
                    $stmt = $pdo->prepare("UPDATE faq SET question = ?, answer = ? WHERE id = ?");
                    $stmt->execute([$question, $answer, $faq_id]);
                    $messages[] = ['type' => 'success', 'text' => 'FAQ가 성공적으로 수정되었습니다.'];
                } catch (PDOException $e) {
                    $messages[] = ['type' => 'error', 'text' => '데이터베이스 오류: ' . htmlspecialchars($e->getMessage())];
                }
            } else {
                $messages[] = ['type' => 'error', 'text' => '모든 필드를 입력해주세요.'];
            }
        }

        // 지원서 승인
        if ($action === 'approve_application') {
            $app_id = intval($_POST['application_id']);
            try {
                $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
                $stmt->execute([$app_id]);
                $application = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($application) {
                    $hashed_password = password_hash('1234', PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO members (student_number, name, password, status, grade) VALUES (?, ?, ?, 'approved', '정회원')");
                    $stmt->execute([
                        $application['student_id'],
                        $application['name'],
                        $hashed_password
                    ]);
                    $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
                    $stmt->execute([$app_id]);
                    // AUTO_INCREMENT 리셋
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications");
                    $stmt->execute();
                    $count = $stmt->fetchColumn();
                    if ($count == 0) {
                        $resetStmt = $pdo->prepare("ALTER TABLE applications AUTO_INCREMENT = 1");
                        $resetStmt->execute();
                    }
                    $messages[] = ['type' => 'success', 'text' => '지원자가 성공적으로 승인되었습니다.'];
                } else {
                    $messages[] = ['type' => 'error', 'text' => '해당 지원서를 찾을 수 없습니다.'];
                }
            } catch (PDOException $e) {
                $messages[] = ['type' => 'error', 'text' => '데이터베이스 오류: ' . htmlspecialchars($e->getMessage())];
            }
        }

        // 지원서 거절
        if ($action === 'reject_application') {
            $app_id = intval($_POST['application_id']);
            try {
                $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
                $stmt->execute([$app_id]);
                // AUTO_INCREMENT 리셋
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications");
                $stmt->execute();
                $count = $stmt->fetchColumn();
                if ($count == 0) {
                    $resetStmt = $pdo->prepare("ALTER TABLE applications AUTO_INCREMENT = 1");
                    $resetStmt->execute();
                }
                $messages[] = ['type' => 'success', 'text' => '지원자가 성공적으로 거절되었습니다.'];
            } catch (PDOException $e) {
                $messages[] = ['type' => 'error', 'text' => '데이터베이스 오류: ' . htmlspecialchars($e->getMessage())];
            }
        }

        // 회원 정보 수정: 오직 회원의 등급(grade)과 상태(status)만 변경
        if ($action === 'update_member') {
            $member_id = intval($_POST['member_id']);
            $status = $_POST['status'];
            $grade = $_POST['grade'];
            $valid_statuses = ['pending', 'approved'];
            $valid_grades = ['정회원', '명예 회원', '관리자'];
            if (!in_array($status, $valid_statuses)) {
                $messages[] = ['type' => 'error', 'text' => '유효하지 않은 상태입니다.'];
            }
            if (!in_array($grade, $valid_grades)) {
                $messages[] = ['type' => 'error', 'text' => '유효하지 않은 등급입니다.'];
            }
            if (in_array($status, $valid_statuses) && in_array($grade, $valid_grades)) {
                try {
                    $stmt = $pdo->prepare("UPDATE members SET status = ?, grade = ? WHERE id = ?");
                    $stmt->execute([$status, $grade, $member_id]);
                    $messages[] = ['type' => 'success', 'text' => '회원 정보가 성공적으로 업데이트되었습니다.'];
                } catch (PDOException $e) {
                    $messages[] = ['type' => 'error', 'text' => '데이터베이스 오류: ' . htmlspecialchars($e->getMessage())];
                }
            }
        }

        // 연혁 추가 (새로운 메뉴)
        if ($action === 'add_history') {
            $event_date = trim($_POST['event_date']);
            if (strlen($event_date) === 7) {
                $event_date .= "-01";
            }
            $event_description = trim($_POST['event_description']);
            if (!empty($event_date) && !empty($event_description)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO club_history (event_date, event_description) VALUES (?, ?)");
                    $stmt->execute([$event_date, $event_description]);
                    $messages[] = ['type' => 'success', 'text' => '연혁이 성공적으로 추가되었습니다.'];
                } catch (PDOException $e) {
                    $messages[] = ['type' => 'error', 'text' => '데이터베이스 오류: ' . htmlspecialchars($e->getMessage())];
                }
            } else {
                $messages[] = ['type' => 'error', 'text' => '모든 필드를 입력해주세요.'];
            }
        }
        

        // 연혁 삭제 (추가 요청 시)
        if ($action === 'delete_history') {
            $history_id = intval($_POST['history_id']);
            try {
                $stmt = $pdo->prepare("DELETE FROM club_history WHERE id = ?");
                $stmt->execute([$history_id]);
                $messages[] = ['type' => 'success', 'text' => '연혁이 성공적으로 삭제되었습니다.'];
            } catch (PDOException $e) {
                $messages[] = ['type' => 'error', 'text' => '데이터베이스 오류: ' . htmlspecialchars($e->getMessage())];
            }
        }
    }
}
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/hwaran.css">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<body>
    <div class="admin-container">
        <h1>관리자 대시보드</h1><br>
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="message <?php echo $msg['type'] === 'success' ? 'success-message' : 'error-message'; ?>">
                    <?php echo htmlspecialchars($msg['text']); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="admin-buttons">
            <button class="admin-button" type="button" onclick="showSection('supportResults')">
                <i class='bx bx-check-circle'></i> 지원 결과
            </button>
            <button class="admin-button" type="button" onclick="showSection('faqManagement')">
                <i class='bx bx-help-circle'></i> FaQ 관리
            </button>
            <button class="admin-button" type="button" onclick="showSection('memberManagement')">
                <i class='bx bx-user-plus'></i> 회원 관리
            </button>
            <button class="admin-button" type="button" onclick="showSection('historyManagement')">
                <i class='bx bx-history'></i> 연혁 관리
            </button>
        </div>

        <!-- 지원 결과 섹션 -->
        <div id="supportResults" class="admin-section" style="display: none;">
            <h2>지원 결과 확인</h2>
            <?php
            try {
                $stmt = $pdo->prepare("SELECT * FROM applications ORDER BY id DESC");
                $stmt->execute();
                $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
                <?php if (!empty($applications)): ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>학번</th>
                                    <th>이름</th>
                                    <th>희망 포지션</th>
                                    <th>지원 날짜</th>
                                    <th>상세 보기</th>
                                    <th>승인</th>
                                    <th>거절</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($app['id']); ?></td>
                                        <td><?php echo htmlspecialchars($app['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($app['name']); ?></td>
                                        <td><?php echo htmlspecialchars($app['desired_position']); ?></td>
                                        <td><?php echo htmlspecialchars($app['submission_date']); ?></td>
                                        <td><button type="button" onclick="viewApplication(<?php echo $app['id']; ?>)">상세 보기</button></td>
                                        <td>
                                            <form action="hwaran.php" method="POST" onsubmit="return confirm('정말 승인하시겠습니까?');">
                                                <input type="hidden" name="action" value="approve_application">
                                                <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                                <button type="submit">승인</button>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="hwaran.php" method="POST" onsubmit="return confirm('정말 거절하시겠습니까?');">
                                                <input type="hidden" name="action" value="reject_application">
                                                <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                                <button type="submit">거절</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>등록된 지원서가 없습니다.</p>
                <?php endif; ?>
            <?php
            } catch (PDOException $e) {
                echo "<div class='error-message'>" . htmlspecialchars($e->getMessage()) . "</div>";
            }
            ?>
            <div id="applicationModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <div id="applicationDetails"></div>
                </div>
            </div>
        </div>

        <!-- FaQ 관리 섹션 -->
        <div id="faqManagement" class="admin-section" style="display: none;">
            <h2>FAQ 관리</h2>
            <div class="faq-form">
                <h3>새로운 FAQ 추가</h3>
                <form action="hwaran.php" method="POST">
                    <input type="hidden" name="action" value="add_faq">
                    <div class="form-group">
                        <label for="question">질문:</label>
                        <input type="text" id="question" name="question" required>
                    </div>
                    <div class="form-group">
                        <label for="answer">답변:</label>
                        <textarea id="answer" name="answer" rows="4" required></textarea>
                    </div>
                    <div class="button-group">
                        <button type="submit">추가</button>
                    </div>
                </form>
            </div>
            <div class="faq-list">
                <h3>FAQ 목록</h3>
                <?php
                try {
                    $stmt = $pdo->prepare("SELECT * FROM faq ORDER BY id DESC");
                    $stmt->execute();
                    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                    <?php if (!empty($faqs)): ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>질문</th>
                                        <th>답변</th>
                                        <th>수정</th>
                                        <th>삭제</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($faqs as $faq): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($faq['id']); ?></td>
                                            <td><?php echo htmlspecialchars($faq['question']); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></td>
                                            <td>
                                                <button type="button" onclick="openEditFaqModal('<?php echo $faq['id']; ?>', '<?php echo htmlspecialchars($faq['question']); ?>', '<?php echo htmlspecialchars($faq['answer']); ?>')">수정</button>
                                            </td>
                                            <td>
                                                <form action="hwaran.php" method="POST" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                                    <input type="hidden" name="action" value="delete_faq">
                                                    <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
                                                    <button type="submit">삭제</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>등록된 FAQ가 없습니다.</p>
                    <?php endif; ?>
                <?php
                } catch (PDOException $e) {
                    echo "<div class='error-message'>" . htmlspecialchars($e->getMessage()) . "</div>";
                }
                ?>
            </div>
            <!-- FAQ 수정 모달 -->
            <div id="editFaqModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeEditFaqModal()">&times;</span>
                    <h3>FAQ 수정</h3>
                    <form action="hwaran.php" method="POST">
                        <input type="hidden" name="action" value="update_faq">
                        <input type="hidden" name="faq_id" id="edit_faq_id" value="">
                        <div class="form-group">
                            <label for="edit_question">질문:</label>
                            <input type="text" id="edit_question" name="question" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_answer">답변:</label>
                            <textarea id="edit_answer" name="answer" rows="4" required></textarea>
                        </div>
                        <div class="button-group">
                            <button type="submit">수정</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 회원 관리 섹션 -->
        <div id="memberManagement" class="admin-section" style="display: none;">
            <h2>회원 관리</h2>
            <?php
            try {
                $stmt = $pdo->prepare("SELECT * FROM members ORDER BY id DESC");
                $stmt->execute();
                $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
                <?php if (!empty($members)): ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>학번</th>
                                    <th>이름</th>
                                    <th>등급</th>
                                    <th>상태</th>
                                    <th>가입 승인/수정</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($member['id']); ?></td>
                                        <td><?php echo htmlspecialchars($member['student_number']); ?></td>
                                        <td><?php echo htmlspecialchars($member['name']); ?></td>
                                        <td><?php echo htmlspecialchars($member['grade']); ?></td>
                                        <td><?php echo htmlspecialchars($member['status']); ?></td>
                                        <td>
                                            <button type="button" onclick="openUpdateMemberModal('<?php echo $member['id']; ?>', '<?php echo htmlspecialchars($member['status']); ?>', '<?php echo htmlspecialchars($member['grade']); ?>')">수정</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>등록된 회원이 없습니다.</p>
                <?php endif; ?>
            <?php
            } catch (PDOException $e) {
                echo "<div class='error-message'>" . htmlspecialchars($e->getMessage()) . "</div>";
            }
            ?>
            <!-- 회원 정보 수정 모달 -->
            <div id="updateMemberModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeUpdateMemberModal()">&times;</span>
                    <h3>회원 정보 수정</h3>
                    <form action="hwaran.php" method="POST">
                        <input type="hidden" name="action" value="update_member">
                        <input type="hidden" name="member_id" id="modal_member_id" value="">
                        <div class="form-group">
                            <label for="status">상태:</label>
                            <select id="status" name="status" required>
                                <option value="">선택</option>
                                <option value="pending">대기 중</option>
                                <option value="approved">승인됨</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="grade">등급:</label>
                            <select id="grade" name="grade" required>
                                <option value="">선택</option>
                                <option value="정회원">정회원</option>
                                <option value="명예 회원">명예 회원</option>
                                <option value="관리자">관리자</option>
                            </select>
                        </div>
                        <div class="button-group">
                            <button type="submit">수정</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 연혁 관리 섹션 -->
        <div id="historyManagement" class="admin-section" style="display: none;">
            <h2>연혁 관리</h2>
            <div class="history-form">
                <h3>새로운 연혁 추가</h3>
                <form action="hwaran.php" method="POST">
                    <input type="hidden" name="action" value="add_history">
                    <div class="form-group">
                        <label for="event_date">연월 (YYYY-MM):</label>
                        <input type="month" id="event_date" name="event_date" required>
                    </div>
                    <div class="form-group">
                        <label for="event_description">설명:</label>
                        <textarea id="event_description" name="event_description" rows="4" required></textarea>
                    </div>
                    <div class="button-group">
                        <button type="submit">추가</button>
                    </div>
                </form>
            </div>
            <div class="history-list">
                <h3>연혁 목록</h3>
                <?php
                try {
                    $stmt = $pdo->prepare("SELECT * FROM club_history ORDER BY event_date DESC");
                    $stmt->execute();
                    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                    <?php if (!empty($history)): ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>연월</th>
                                        <th>설명</th>
                                        <th>삭제</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['id']); ?></td>
                                            <td><?php echo date('Y-m', strtotime($item['event_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($item['event_description']); ?></td>
                                            <td>
                                                <form action="hwaran.php" method="POST" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                                    <input type="hidden" name="action" value="delete_history">
                                                    <input type="hidden" name="history_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit">삭제</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>등록된 연혁이 없습니다.</p>
                    <?php endif; ?>
                <?php
                } catch (PDOException $e) {
                    echo "<div class='error-message'>" . htmlspecialchars($e->getMessage()) . "</div>";
                }
                ?>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script>
    function showSection(sectionId) {
        const sections = document.querySelectorAll('.admin-section');
        sections.forEach(section => {
            section.style.display = section.id === sectionId ? 'block' : 'none';
        });
    }

    function viewApplication(appId) {
        fetch(`get_application.php?id=${appId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('applicationDetails').innerHTML = data;
                document.getElementById('applicationModal').style.display = 'block';
            })
            .catch(error => {
                alert('지원 상세 정보를 불러오는 데 실패했습니다.');
                console.error('Error fetching application details:', error);
            });
    }

    function closeModal() {
        document.getElementById('applicationModal').style.display = 'none';
        document.getElementById('applicationDetails').innerHTML = '';
    }

    function openEditFaqModal(faqId, question, answer) {
        document.getElementById('edit_faq_id').value = faqId;
        document.getElementById('edit_question').value = question;
        document.getElementById('edit_answer').value = answer;
        document.getElementById('editFaqModal').style.display = 'block';
    }

    function closeEditFaqModal() {
        document.getElementById('editFaqModal').style.display = 'none';
    }

    function openUpdateMemberModal(memberId, status, grade) {
        document.getElementById('modal_member_id').value = memberId;
        document.getElementById('status').value = status;
        document.getElementById('grade').value = grade;
        document.getElementById('updateMemberModal').style.display = 'block';
    }

    function closeUpdateMemberModal() {
        document.getElementById('updateMemberModal').style.display = 'none';
    }

    window.onclick = function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    }
    </script>
</body>
</html>
