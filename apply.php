<?php  
session_start();
require_once 'config/db.php';
require_once 'config/app.php';
include 'includes/header.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$current_date = date('Y-m-d');

if ($current_date < RECRUITMENT_START || $current_date > RECRUITMENT_END) {
    echo "<div class='container'><br><br><p>현재 모집 기간이 아닙니다. 모집 기간은 " . RECRUITMENT_START . "부터 " . RECRUITMENT_END . "까지입니다.</p><br><br><br><</div>";
    include 'includes/footer.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "유효하지 않은 요청입니다.";
    } else {
        $errors = [];

        // 1. 학번
        if (empty($_POST['student_id']) || !ctype_digit($_POST['student_id'])) {
            $errors[] = "유효한 학번을 입력해주세요.";
        } else {
            $student_id = intval($_POST['student_id']);
        }

        // 2. 이름
        if (empty($_POST['name'])) {
            $errors[] = "이름을 입력해주세요.";
        } else {
            $name = trim($_POST['name']);
        }

        // 3. 전화번호
        if (empty($_POST['phone_number'])) {
            $errors[] = "전화번호를 입력해주세요.";
        } else {
            $phone_number = trim($_POST['phone_number']);
        }

        // 3. 희망 포지션
        $valid_positions = ['게임 개발', '웹 개발', '그래픽', '사운드'];
        if (empty($_POST['desired_position']) || !in_array($_POST['desired_position'], $valid_positions)) {
            $errors[] = "유효한 희망 포지션을 선택해주세요.";
        } else {
            $desired_position = $_POST['desired_position'];
        }

        // 4. 화란을 어떻게 알게 되셨나요?
        if (empty($_POST['how_heard'])) {
            $errors[] = "화란을 어떻게 알게 되었는지 작성해주세요.";
        } else {
            $how_heard = trim($_POST['how_heard']);
        }

        // 5. 동아리 지원 계기
        if (empty($_POST['motivation'])) {
            $errors[] = "동아리 지원 계기를 작성해주세요.";
        } else {
            $motivation = trim($_POST['motivation']);
        }

        // 6. 개발해 보고 싶은 프로젝트가 있나요?
        $valid_wants_project = ['있다', '없다'];
        if (empty($_POST['wants_project']) || !in_array($_POST['wants_project'], $valid_wants_project)) {
            $errors[] = "개발해 보고 싶은 프로젝트 여부를 선택해주세요.";
        } else {
            $wants_project = $_POST['wants_project'];
            if ($wants_project === '있다') {
                if (empty($_POST['project_description'])) {
                    $errors[] = "개발하고 싶은 프로젝트에 대해 작성해주세요.";
                } else {
                    $project_description = trim($_POST['project_description']);
                }
            } else {
                $project_description = null;
            }
        }

        // 7. 보유하신 노트북 / 데스크탑의 성능
        if (empty($_POST['laptop_specs'])) {
            $errors[] = "노트북/데스크탑의 성능을 작성해주세요.";
        } else {
            $laptop_specs = trim($_POST['laptop_specs']);
        }

        // 8. 사용해본 도구나 언어 (복수 선택)
        $valid_tools_languages = [
            'C', 'C++', 'C#', 'Python', 'JAVA', 'Kotlin', 'Go', 'HTML/CSS', 'JavaScript', 'PHP', 'R', 'SQL',
            'Unity', 'Unreal', 'Android Studio', 'godot', 'GDevelop',
            'Photoshop', 'Illustrator', 'AfterEffect', 'Premiere Pro', 'Vegas Pro', 'Aseprite', 'ClipStudio', 'Blender', 'Cubik Studio', 'BlockBench'
        ];
        if (empty($_POST['tools_languages'])) {
            $errors[] = "사용해본 도구나 언어를 선택해주세요.";
        } else {
            // 배열로 받기 때문에 배열 필터링
            $tools_languages = array_intersect($_POST['tools_languages'], $valid_tools_languages);
            if (empty($tools_languages)) {
                $errors[] = "유효한 도구나 언어를 선택해주세요.";
            } else {
                $tools_languages = implode(',', $tools_languages);
            }
        }

        // 9. 지금까지 진행한 프로젝트가 있으신가요?
        $valid_has_project = ['있다', '없다'];
        if (empty($_POST['has_project']) || !in_array($_POST['has_project'], $valid_has_project)) {
            $errors[] = "지금까지 진행한 프로젝트 여부를 선택해주세요.";
        } else {
            $has_project = $_POST['has_project'];
            if ($has_project === '있다') {
                if (empty($_POST['project_details'])) {
                    $errors[] = "진행한 프로젝트에 대해 작성해주세요.";
                } else {
                    $project_details = trim($_POST['project_details']);
                }
            } else {
                $project_details = null;
            }
        }

        // 10. 동아리에서 해보고 싶은 활동
        if (empty($_POST['desired_activities'])) {
            $errors[] = "동아리에서 해보고 싶은 활동을 작성해주세요.";
        } else {
            $desired_activities = trim($_POST['desired_activities']);
        }

        // 11. 새롭게 배우고 싶은 언어나 도구
        if (empty($_POST['new_languages_tools'])) {
            $errors[] = "새롭게 배우고 싶은 언어나 도구를 작성해주세요.";
        } else {
            $new_languages_tools = trim($_POST['new_languages_tools']);
        }

        // 12. 팀으로 일 할 때 선호하는 커뮤니케이션 방식
        $valid_communication = ['대면', '온라인 채팅', '음성 채팅', '기타'];
        if (empty($_POST['preferred_communication']) || !in_array($_POST['preferred_communication'], $valid_communication)) {
            $errors[] = "선호하는 커뮤니케이션 방식을 선택해주세요.";
        } else {
            $preferred_communication = $_POST['preferred_communication'];
            if ($preferred_communication === '기타') {
                if (empty($_POST['communication_other'])) {
                    $errors[] = "기타 커뮤니케이션 방식에 대해 작성해주세요.";
                } else {
                    $communication_other = trim($_POST['communication_other']);
                }
            } else {
                $communication_other = null;
            }
        }

        // 13. 동아리 정기 활동 참여 여부
        if (empty($_POST['personal_activities'])) {
            $errors[] = "토요일에 개인적으로 진행하는 업무 여부를 작성해주세요.";
        } else {
            $personal_activities = trim($_POST['personal_activities']);
        }

        // 14. 본인을 한 줄로 소개
        if (empty($_POST['self_introduction'])) {
            $errors[] = "본인을 한 줄로 소개해주세요.";
        } else {
            $self_introduction = trim($_POST['self_introduction']);
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO applications (student_id, name, phone_number, desired_position, how_heard, motivation, wants_project, project_description, laptop_specs, tools_languages, has_project, project_details, desired_activities, new_languages_tools, preferred_communication, communication_other, personal_activities, self_introduction) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->execute([
                    $student_id,
                    $name,
                    $phone_number,
                    $desired_position,
                    $how_heard,
                    $motivation,
                    $wants_project,
                    $project_description,
                    $laptop_specs,
                    $tools_languages,
                    $has_project,
                    $project_details,
                    $desired_activities,
                    $new_languages_tools,
                    $preferred_communication,
                    $communication_other,
                    $personal_activities,
                    $self_introduction
                ]);

                $success_message = "지원이 성공적으로 제출되었습니다. 감사합니다!";

                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } catch (PDOException $e) {
                $errors[] = "데이터베이스 오류: " . $e->getMessage();
            }
        }
    }
}
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/apply.css">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<div class="container">
    <h1>화란 동아리 공개 모집 지원서</h1><br>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="success-message">
            <p><?php echo htmlspecialchars($success_message); ?></p><br><br>
        </div>
    <?php else: ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="applicationForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <!-- 1. 학번 -->
            <div class="form-group">
                <label for="student_id">1. 학번 (숫자 입력):</label>
                <input type="number" id="student_id" name="student_id" required>
            </div>

            <!-- 2. 이름 -->
            <div class="form-group">
                <label for="name">2. 이름:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <!-- 3. 전화번호 -->
            <div class="form-group">
                <label for="phone_number">3. 전화번호:</label>
                <input type="text" id="phone_number" name="phone_number" required>
            </div>

            <!-- 3. 희망 포지션 -->
            <div class="form-group">
                <label for="desired_position">4. 희망 포지션:</label>
                <select id="desired_position" name="desired_position" required>
                    <option value="">선택</option>
                    <option value="게임 개발">게임 개발</option>
                    <option value="웹 개발">웹 개발</option>
                    <option value="그래픽">그래픽</option>
                    <option value="사운드">사운드</option>
                </select>
            </div>

            <!-- 4. 화란을 어떻게 알게 되셨나요? -->
            <div class="form-group">
                <label for="how_heard">5. 화란을 어떻게 알게 되셨나요?</label>
                <input type="text" id="how_heard" name="how_heard" required>
            </div>

            <!-- 5. 동아리 지원 계기 -->
            <div class="form-group">
                <label for="motivation">6. 동아리 지원 계기:</label>
                <textarea id="motivation" name="motivation" rows="4" required></textarea>
            </div>

            <!-- 6. 개발해 보고 싶은 프로젝트가 있나요? -->
            <div class="form-group">
                <label>7. 개발해 보고 싶은 프로젝트가 있나요?</label>
                <div class="radio-group">
                    <input type="radio" id="wants_project_yes" name="wants_project" value="있다" required>
                    <label for="wants_project_yes">있다</label>
                    
                    <input type="radio" id="wants_project_no" name="wants_project" value="없다">
                    <label for="wants_project_no">없다</label>
                </div>
            </div>

            <!-- 6-1. 프로젝트 설명 (있다 선택 시 표시) -->
            <div class="conditional-field" id="project_description_container">
                <div class="form-group">
                    <label for="project_description">7-1. 무슨 프로젝트인가요?</label>
                    <input type="text" id="project_description" name="project_description">
                </div>
            </div>

            <!-- 7. 노트북 / 데스크탑 성능 -->
            <div class="form-group">
                <label for="laptop_specs"></i>8. 보유하신 노트북 / 데스크탑의 성능을 작성해주세요:</label>
                <textarea id="laptop_specs" name="laptop_specs" rows="3" placeholder="CPU: Intel i7, RAM: 16GB, GPU: NVIDIA GTX 1660" required></textarea>
            </div>

            <!-- 8. 사용해본 도구나 언어 (커스텀 체크박스) -->
            <div class="form-group">
                <label><i class='bx bx-hardware-chip'></i>9. 사용해본 도구나 언어를 모두 선택해주세요:</label>
                <div class="checkbox-group">
                    <!-- 언어 -->
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_c" name="tools_languages[]" value="C">
                        <label for="tool_c">C</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_cpp" name="tools_languages[]" value="C++">
                        <label for="tool_cpp">C++</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_csharp" name="tools_languages[]" value="C#">
                        <label for="tool_csharp">C#</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_python" name="tools_languages[]" value="Python">
                        <label for="tool_python">Python</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_java" name="tools_languages[]" value="JAVA">
                        <label for="tool_java">JAVA</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_kotlin" name="tools_languages[]" value="Kotlin">
                        <label for="tool_kotlin">Kotlin</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_go" name="tools_languages[]" value="Go">
                        <label for="tool_go">Go</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_htmlcss" name="tools_languages[]" value="HTML/CSS">
                        <label for="tool_htmlcss">HTML/CSS</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_javascript" name="tools_languages[]" value="JavaScript">
                        <label for="tool_javascript">JavaScript</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_php" name="tools_languages[]" value="PHP">
                        <label for="tool_php">PHP</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_r" name="tools_languages[]" value="R">
                        <label for="tool_r">R</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_sql" name="tools_languages[]" value="SQL">
                        <label for="tool_sql">SQL</label>
                    </div>
                    <!-- 개발 도구 -->
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_unity" name="tools_languages[]" value="Unity">
                        <label for="tool_unity">Unity</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_unreal" name="tools_languages[]" value="Unreal">
                        <label for="tool_unreal">Unreal</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_androidstudio" name="tools_languages[]" value="Android Studio">
                        <label for="tool_androidstudio">Android Studio</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_godot" name="tools_languages[]" value="godot">
                        <label for="tool_godot">godot</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_gdevelop" name="tools_languages[]" value="GDevelop">
                        <label for="tool_gdevelop">GDevelop</label>
                    </div>
                    <!-- 디자인 도구 -->
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_photoshop" name="tools_languages[]" value="Photoshop">
                        <label for="tool_photoshop">Photoshop</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_illustrator" name="tools_languages[]" value="Illustrator">
                        <label for="tool_illustrator">Illustrator</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_aftereffect" name="tools_languages[]" value="AfterEffect">
                        <label for="tool_aftereffect">AfterEffect</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_premierepro" name="tools_languages[]" value="Premiere Pro">
                        <label for="tool_premierepro">Premiere Pro</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_vegaspro" name="tools_languages[]" value="Vegas Pro">
                        <label for="tool_vegaspro">Vegas Pro</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_aseprite" name="tools_languages[]" value="Aseprite">
                        <label for="tool_aseprite">Aseprite</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_clipstudio" name="tools_languages[]" value="ClipStudio">
                        <label for="tool_clipstudio">ClipStudio</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_blender" name="tools_languages[]" value="Blender">
                        <label for="tool_blender">Blender</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_cubikstudio" name="tools_languages[]" value="Cubik Studio">
                        <label for="tool_cubikstudio">Cubik Studio</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="tool_blockbench" name="tools_languages[]" value="BlockBench">
                        <label for="tool_blockbench">BlockBench</label>
                    </div>
                </div>
            </div>

            <!-- 9. 지금까지 진행한 프로젝트가 있으신가요? -->
            <div class="form-group">
                <label></i>10. 지금까지 진행한 프로젝트가 있으신가요?</label>
                <div class="radio-group">
                    <input type="radio" id="has_project_yes" name="has_project" value="있다" required>
                    <label for="has_project_yes">있다</label>
                    
                    <input type="radio" id="has_project_no" name="has_project" value="없다">
                    <label for="has_project_no">없다</label>
                </div>
            </div>

            <!-- 9-1. 프로젝트 설명 (있다 선택 시 표시) -->
            <div class="conditional-field" id="project_details_container">
                <div class="form-group">
                    <label for="project_details">10-1. 무슨 프로젝트인가요?</label>
                    <input type="text" id="project_details" name="project_details">
                </div>
            </div>

            <!-- 10. 동아리에서 해보고 싶은 활동 -->
            <div class="form-group">
                <label for="desired_activities">11. 동아리에서 해보고 싶은 활동이 있으신가요?</label>
                <textarea id="desired_activities" name="desired_activities" rows="3" required></textarea>
            </div>

            <!-- 11. 새롭게 배우고 싶은 언어나 도구 -->
            <div class="form-group">
                <label for="new_languages_tools">12. 새롭게 배우고 싶은 언어나 도구가 있으신가요?</label>
                <textarea id="new_languages_tools" name="new_languages_tools" rows="3" required></textarea>
            </div>

            <!-- 12. 선호하는 커뮤니케이션 방식 -->
            <div class="form-group">
                <label>13. 팀으로 일 할 때 선호하는 커뮤니케이션 방식은 무엇인가요?</label>
                <div class="radio-group">
                    <input type="radio" id="communication_face_to_face" name="preferred_communication" value="대면" required>
                    <label for="communication_face_to_face">대면</label>
                    
                    <input type="radio" id="communication_online_chat" name="preferred_communication" value="온라인 채팅">
                    <label for="communication_online_chat">온라인 채팅</label>
                    
                    <input type="radio" id="communication_voice_chat" name="preferred_communication" value="음성 채팅">
                    <label for="communication_voice_chat">음성 채팅</label>
                    
                    <input type="radio" id="communication_other" name="preferred_communication" value="기타">
                    <label for="communication_other">기타</label>
                </div>
            </div>

            <!-- 12-1. 기타 커뮤니케이션 방식 (기타 선택 시 표시) -->
            <div class="conditional-field" id="communication_other_container">
                <div class="form-group">
                    <label for="communication_other">기타 커뮤니케이션 방식:</label>
                    <input type="text" id="communication_other" name="communication_other">
                </div>
            </div>

            <!-- 13. 동아리 정기 활동 참여 여부 -->
            <div class="form-group">
                <label for="personal_activities">14. 동아리 정기 활동 (참여)의 경우 주로 토요일 오후 1시에 진행됩니다. 토요일에 개인적으로 진행하는 업무가 있으신가요?</label>
                <textarea id="personal_activities" name="personal_activities" rows="2" required></textarea>
            </div>

            <!-- 14. 본인을 한 줄로 소개 -->
            <div class="form-group">
                <label for="self_introduction">15. 마지막으로 본인을 한 줄로 소개한다면?</label>
                <input type="text" id="self_introduction" name="self_introduction" required>
            </div>

            <!-- 제출 버튼 -->
            <button type="submit" name="submit_application"><i class='bx bx-send'></i>지원 제출</button>
        </form>
    <?php endif; ?>
</div>

<script>
    document.querySelectorAll('input[name="wants_project"]').forEach((elem) => {
        elem.addEventListener('change', function(event) {
            const value = event.target.value;
            const projectDescContainer = document.getElementById('project_description_container');
            if (value === '있다') {
                projectDescContainer.style.display = 'block';
                document.getElementById('project_description').required = true;
            } else {
                projectDescContainer.style.display = 'none';
                document.getElementById('project_description').required = false;
                document.getElementById('project_description').value = '';
            }
        });
    });

    document.querySelectorAll('input[name="has_project"]').forEach((elem) => {
        elem.addEventListener('change', function(event) {
            const value = event.target.value;
            const projectDetailsContainer = document.getElementById('project_details_container');
            if (value === '있다') {
                projectDetailsContainer.style.display = 'block';
                document.getElementById('project_details').required = true;
            } else {
                projectDetailsContainer.style.display = 'none';
                document.getElementById('project_details').required = false;
                document.getElementById('project_details').value = '';
            }
        });
    });

    document.querySelectorAll('input[name="preferred_communication"]').forEach((elem) => {
        elem.addEventListener('change', function(event) {
            const value = event.target.value;
            const communicationOtherContainer = document.getElementById('communication_other_container');
            if (value === '기타') {
                communicationOtherContainer.style.display = 'block';
                document.getElementById('communication_other').required = true;
            } else {
                communicationOtherContainer.style.display = 'none';
                document.getElementById('communication_other').required = false;
                document.getElementById('communication_other').value = '';
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
