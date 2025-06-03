<?php 
require_once '../config/db.php';
require_once '../config/app.php';
include '../includes/header.php';

$current_user = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

$project_types = ['게임', '웹', '디스코드 봇', '마인크래프트', '디자인'];

$badge_classes = ['badge-red', 'badge-blue', 'badge-green', 'badge-purple', 'badge-orange', 'badge-gray'];

function get_badge_class($name, $badge_classes) {
    $hash = crc32($name);
    $index = $hash % count($badge_classes);
    return $badge_classes[$index];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
    $project_name = trim($_POST['project_name']);
    $status = $_POST['status'];
    $description = trim($_POST['description']);
    $project_type = $_POST['project_type'];
    $participants = trim($_POST['participants']);
    $status_message = trim($_POST['status_message']);
    $progress = intval($_POST['progress']);

    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['main_image']['tmp_name'];
        $file_name = $_FILES['main_image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $safe_project_name = preg_replace('/[^가-힣a-zA-Z0-9_-]/', '', $project_name);
            $new_file_name = uniqid('proj_', true) . '_' . $safe_project_name . '.' . $file_ext;
            $upload_dir = '../assets/img/project/';
            $dest_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $main_image = 'assets/img/project/' . $new_file_name;
            } else {
                $main_image = null;
            }
        } else {
            $main_image = null;
        }
    } else {
        $main_image = null;
    }

    if ($progress < 0 || $progress > 100) {
        $progress = 0;
    }

    $stmt = $pdo->prepare("INSERT INTO projects (project_name, status, description, main_image, project_type, participants, progress, status_message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$project_name, $status, $description, $main_image, $project_type, $participants, $progress, $status_message]);

    header("Location: projects.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_project'])) {
    $id = intval($_POST['id']);
    $project_name = trim($_POST['project_name']);
    $status = $_POST['status'];
    $description = trim($_POST['description']);
    $project_type = $_POST['project_type'];
    $participants = trim($_POST['participants']);
    $status_message = trim($_POST['status_message']);
    $progress = intval($_POST['progress']);

    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['main_image']['tmp_name'];
        $file_name = $_FILES['main_image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $safe_project_name = preg_replace('/[^가-힣a-zA-Z0-9_-]/', '', $project_name);
            $new_file_name = uniqid('proj_', true) . '_' . $safe_project_name . '.' . $file_ext;
            $upload_dir = '../assets/img/project/';
            $dest_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $main_image = 'assets/img/project/' . $new_file_name;

                $stmt = $pdo->prepare("SELECT main_image FROM projects WHERE id = ?");
                $stmt->execute([$id]);
                $existing_image = $stmt->fetchColumn();
                if ($existing_image && file_exists('../' . $existing_image)) {
                    unlink('../' . $existing_image);
                }
            }
        }
    }

    if ($progress < 0 || $progress > 100) {
        $progress = 0;
    }

    if (isset($main_image)) {
        $stmt = $pdo->prepare("UPDATE projects SET project_name = ?, status = ?, description = ?, main_image = ?, project_type = ?, participants = ?, progress = ?, status_message = ? WHERE id = ?");
        $stmt->execute([$project_name, $status, $description, $main_image, $project_type, $participants, $progress, $status_message, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE projects SET project_name = ?, status = ?, description = ?, project_type = ?, participants = ?, progress = ?, status_message = ? WHERE id = ?");
        $stmt->execute([$project_name, $status, $description, $project_type, $participants, $progress, $status_message, $id]);
    }

    header("Location: projects.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project'])) {
    $id = intval($_POST['id']);

    $stmt = $pdo->prepare("SELECT main_image FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $existing_image = $stmt->fetchColumn();
    if ($existing_image && file_exists('../' . $existing_image)) {
        unlink('../' . $existing_image);
    }

    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: projects.php");
    exit();
}

$filter_type = '';
if (isset($_GET['type']) && in_array($_GET['type'], $project_types)) {
    $filter_type = $_GET['type'];
}

if ($filter_type) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE status IN ('기획중', '개발중') AND project_type = ? ORDER BY id DESC");
    $stmt->execute([$filter_type]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE status IN ('기획중', '개발중') ORDER BY id DESC");
    $stmt->execute();
}
$projects = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT project_type, COUNT(*) as count FROM projects WHERE status IN ('기획중', '개발중') GROUP BY project_type");
$stmt->execute();
$type_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$is_table_view = isset($_GET['view']) && $_GET['view'] === 'table';

if ($is_table_view) {
    $stmt = $pdo->prepare("SELECT name FROM members");
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT project_name, project_type, description, status_message, participants FROM projects");
    $stmt->execute();
    $all_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/projects.css">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<body>
    <div class="container">
        <h1><i class='bx bxs-folder-open'></i> 프로젝트 관리</h1>
        <?php if ($is_table_view): ?>
            <table class="table-view">
                <thead>
                    <tr>
                        <th>멤버 이름</th>
                        <th>참여 프로젝트</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($member['name']); ?></strong></td>
                            <td>
                                <?php
                                    $member_projects = [];
                                    foreach ($all_projects as $project) {
                                        $participants_arr = array_map('trim', explode(',', $project['participants']));
                                        if (in_array(trim($member['name']), $participants_arr)) {
                                            $project_type_class = '';
                                            switch ($project['project_type']) {
                                                case '게임': $project_type_class = 'badge-game'; break;
                                                case '웹': $project_type_class = 'badge-web'; break;
                                                case '디스코드 봇': $project_type_class = 'badge-discord'; break;
                                                case '마인크래프트': $project_type_class = 'badge-minecraft'; break;
                                                case '디자인': $project_type_class = 'badge-design'; break;
                                            }
                                            $desc = htmlspecialchars($project['description'] ?? '설명 없음');
                                            $status_msg = htmlspecialchars($project['status_message'] ?? '상태 메시지 없음');
                                            $member_projects[] = '<span class="project-badge ' . $project_type_class . '" 
                                                data-project-name="' . htmlspecialchars($project['project_name']) . '" 
                                                data-project-description="' . $desc . '" 
                                                data-project-type="' . htmlspecialchars($project['project_type']) . '" 
                                                data-project-status-message="' . $status_msg . '" 
                                                data-participants="' . htmlspecialchars($project['participants']) . '">'
                                                . htmlspecialchars($project['project_name']) . '</span>';
                                        }
                                    }
                                    echo implode(' ', $member_projects);
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="filter-container">
                <span><strong>프로젝트 타입:</strong></span>
                <a href="<?php echo BASE_URL; ?>/members/projects.php" class="filter-btn <?php echo $filter_type === '' ? 'active' : ''; ?>">전체</a>
                <?php foreach ($project_types as $type): ?>
                    <a href="<?php echo BASE_URL; ?>/members/projects.php?type=<?php echo urlencode($type); ?>" class="filter-btn <?php echo $filter_type === $type ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($type); ?> (<?php echo isset($type_counts[$type]) ? $type_counts[$type] : 0; ?>)
                    </a>
                <?php endforeach; ?>
            </div>
            <button class="add-project-btn" id="addProjectBtn"><i class='bx bx-plus'></i> 프로젝트 추가</button>
            <div class="projects-container">
                <?php foreach ($projects as $project): ?>
                    <div class="project-card">
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($project['project_name']); ?></h3>
                            <?php if ($project['main_image'] && file_exists('../' . $project['main_image'])): ?>
                                <img src="<?php echo BASE_URL . '/' . htmlspecialchars($project['main_image']); ?>" alt="<?php echo htmlspecialchars($project['project_name']); ?>" class="project-image">
                            <?php else: ?>
                                <img src="<?php echo BASE_URL; ?>/assets/img/project/default-project.png" alt="No Image" class="project-image">
                            <?php endif; ?>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo intval($project['progress']); ?>%;"></div>
                            </div>
                            <p class="progress-text"><?php echo intval($project['progress']); ?>%</p>
                        </div>
                        <div class="overlay" >
                            <p><strong>설명:</strong> <?php echo htmlspecialchars($project['description']); ?></p>
                            <p><strong>타입:</strong> <?php echo htmlspecialchars($project['project_type']); ?></p>
                            <p><strong>상태:</strong> <?php echo htmlspecialchars($project['status']); ?></p>
                            <p><strong>상태 메시지:</strong> <?php echo htmlspecialchars($project['status_message']); ?></p>
                            <div class="participants">
                                <strong>참여자:</strong>
                                <?php
                                    $participant_names = array_map('trim', explode(',', $project['participants']));
                                    foreach ($participant_names as $participant) {
                                        if ($participant) {
                                            echo '<span class="badge ' . get_badge_class($participant, $badge_classes) . '">' . htmlspecialchars($participant) . '</span>';
                                        }
                                    }
                                ?>
                            </div>
                        </div>
                        <div class="project-actions">
                            <?php
                                $participant_names = array_map('trim', explode(',', $project['participants']));
                            ?>
                            <?php if (in_array(trim($current_user), $participant_names)): ?>
                                <button class="edit-btn" onclick="openEditForm(<?php echo $project['id']; ?>)"><i class='bx bx-edit'></i> 수정</button>
                                <form action="<?php echo BASE_URL; ?>/members/projects.php" method="POST" class="delete-form">
                                    <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                                    <button type="submit" name="delete_project" class="delete-btn" onclick="return confirm('정말 삭제하시겠습니까?');"><i class='bx bx-trash'></i> 삭제</button>
                                </form>
                            <?php else: ?>
                                <button class="disabled-btn" disabled><i class='bx bx-lock'></i> 수정 / 삭제 불가</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="modal" id="addProjectModal">
        <div class="modal-content">
            <span class="close" id="closeAddModal">&times;</span>
            <h2><i class='bx bx-plus'></i> 프로젝트 추가</h2>
            <form action="<?php echo BASE_URL; ?>/members/projects.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_project" value="1">
                <label for="project_name">프로젝트 이름:</label>
                <input type="text" id="project_name" name="project_name" required>
                <label for="project_type">프로젝트 타입:</label>
                <select id="project_type" name="project_type" required>
                    <option value="">선택</option>
                    <?php foreach ($project_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="status">상태:</label>
                <select id="status" name="status" required>
                    <option value="기획중">기획중</option>
                    <option value="개발중">개발중</option>
                </select>
                <label for="description">설명:</label>
                <textarea id="description" name="description" rows="4" required></textarea>
                <label for="participants">참여자 (쉼표로 구분):</label>
                <input type="text" id="participants" name="participants" placeholder="한상민, 강지훈, 이세희" required>
                <label for="status_message">상태 메시지:</label>
                <textarea id="status_message" name="status_message" rows="2" placeholder="개발이 진행 중입니다." required></textarea>
                <label for="progress">작업 진행도 (%):</label>
                <input type="number" id="progress" name="progress" min="0" max="100" value="0" required>
                <label for="main_image">대표 이미지:</label>
                <input type="file" id="main_image" name="main_image" accept="image/*">
                <button type="submit"><i class='bx bx-save'></i> 저장</button>
            </form>
        </div>
    </div>

    <div class="modal" id="editProjectModal">
        <div class="modal-content">
            <span class="close" id="closeEditModal">&times;</span>
            <h2><i class='bx bx-edit'></i> 프로젝트 수정</h2>
            <form action="<?php echo BASE_URL; ?>/members/projects.php" method="POST" enctype="multipart/form-data" id="editProjectForm">
                <input type="hidden" name="edit_project" value="1">
                <input type="hidden" name="id" id="edit_id" value="">
                <label for="edit_project_name">프로젝트 이름:</label>
                <input type="text" id="edit_project_name" name="project_name" required>
                <label for="edit_project_type">프로젝트 타입:</label>
                <select id="edit_project_type" name="project_type" required>
                    <option value="">선택</option>
                    <?php foreach ($project_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="edit_status">상태:</label>
                <select id="edit_status" name="status" required>
                    <option value="기획중">기획중</option>
                    <option value="개발중">개발중</option>
                    <option value="개발 완료">개발 완료</option>
                </select>
                <label for="edit_description">설명:</label>
                <textarea id="edit_description" name="description" rows="4" required></textarea>
                <label for="edit_participants">참여자 (쉼표로 구분):</label>
                <input type="text" id="edit_participants" name="participants" placeholder="한상민, 강지훈, 이세희" required>
                <label for="edit_status_message">상태 메시지:</label>
                <textarea id="edit_status_message" name="status_message" rows="2" placeholder="개발이 진행 중입니다." required></textarea>
                <label for="edit_progress">작업 진행도 (%):</label>
                <input type="number" id="edit_progress" name="progress" min="0" max="100" value="0" required>
                <label for="edit_main_image">대표 이미지:</label>
                <input type="file" id="edit_main_image" name="main_image" accept="image/*">
                <div id="currentImageContainer" style="display: none;">
                    <p>현재 이미지:</p>
                    <img id="current_image" src="" alt="현재 이미지" width="100">
                </div>
                <button type="submit"><i class='bx bx-save'></i> 업데이트</button>
            </form>
        </div>
    </div>

    <div id="projectModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeProjectModal">&times;</span>
            <h2 id="modalProjectTitle">프로젝트 제목</h2>
            <p><strong>설명:</strong> <span id="modalProjectDescription"></span></p>
            <p><strong>타입:</strong> <span id="modalProjectType"></span></p>
            <p><strong>상태 메시지:</strong> <span id="modalProjectStatusMessage"></span></p>
            <div id="modalParticipants">
                <strong>참여자:</strong>
            </div>
        </div>
    </div>

<script>
    const closeModal = document.getElementById('closeProjectModal');
    const projectModal = document.getElementById('projectModal');

    document.querySelectorAll('.project-badge').forEach(badge => {
        badge.addEventListener('click', function() {
            const projectName = badge.dataset.projectName || '프로젝트 이름 없음';
            const projectDescription = badge.dataset.projectDescription || '설명 없음';
            const projectType = badge.dataset.projectType || '타입 없음';
            const projectStatusMessage = badge.dataset.projectStatusMessage || '상태 메시지 없음';
            const participants = badge.dataset.participants || '참여자 없음';

            document.getElementById('modalProjectTitle').textContent = projectName;
            document.getElementById('modalProjectDescription').textContent = projectDescription;
            document.getElementById('modalProjectType').textContent = projectType;
            document.getElementById('modalProjectStatusMessage').textContent = projectStatusMessage;

            const modalParticipants = document.getElementById('modalParticipants');
            modalParticipants.innerHTML = '<strong>참여자:</strong>';
            participants.split(',').forEach(participant => {
                const badgeSpan = document.createElement('span');
                badgeSpan.classList.add('badge');
                badgeSpan.textContent = participant.trim();
                modalParticipants.appendChild(badgeSpan);
            });

            projectModal.style.display = 'block';
        });
    });

    closeModal.addEventListener('click', function() {
        projectModal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target === projectModal) {
            projectModal.style.display = 'none';
        }
    });

    const addModal = document.getElementById('addProjectModal');
    const addBtn = document.getElementById('addProjectBtn');
    const closeAddModal = document.getElementById('closeAddModal');

    addBtn.onclick = function() {
        addModal.style.display = 'block';
    }

    closeAddModal.onclick = function() {
        addModal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == addModal) {
            addModal.style.display = 'none';
        }
    }

    function openEditForm(id) {
        fetch(`<?php echo BASE_URL; ?>/members/get_project.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('edit_id').value = data.project.id;
                    document.getElementById('edit_project_name').value = data.project.project_name;
                    document.getElementById('edit_project_type').value = data.project.project_type;
                    document.getElementById('edit_status').value = data.project.status;
                    document.getElementById('edit_description').value = data.project.description;
                    document.getElementById('edit_participants').value = data.project.participants;
                    document.getElementById('edit_status_message').value = data.project.status_message;
                    document.getElementById('edit_progress').value = data.project.progress;

                    if (data.project.main_image) {
                        document.getElementById('current_image').src = "<?php echo BASE_URL; ?>/" + data.project.main_image;
                        document.getElementById('currentImageContainer').style.display = 'block';
                    } else {
                        document.getElementById('currentImageContainer').style.display = 'none';
                    }

                    document.getElementById('editProjectModal').style.display = 'block';
                } else {
                    alert('프로젝트 정보를 가져오는데 실패했습니다.');
                }
            })
            .catch(error => {
                console.error('Error fetching project data:', error);
                alert('프로젝트 정보를 가져오는데 실패했습니다.');
            });
    }

    document.getElementById('closeEditModal').onclick = function() {
        document.getElementById('editProjectModal').style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == document.getElementById('editProjectModal')) {
            document.getElementById('editProjectModal').style.display = 'none';
        }
    }
</script>
</body>
<?php include '../includes/footer.php'; ?>