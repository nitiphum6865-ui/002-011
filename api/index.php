<?php
ini_set('memory_limit', '256M');
require_once __DIR__ . '/db.php';

$endpoint = isset($_GET['endpoint']) ? strtolower(trim($_GET['endpoint'])) : 'info';
$method = $_SERVER['REQUEST_METHOD'];

if (!$pdo) {
    echo json_encode([
        'status' => 'warning',
        'message' => 'Database connecting or initializing...',
        'timestamp' => date('c'),
        'service' => 'api',
        'container' => 'api'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

try {
    switch ($endpoint) {
        case 'info':
            $stmt = $pdo->query("SELECT * FROM department_info LIMIT 1");
            $data = $stmt->fetch();
            echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
            break;

        case 'courses':
            $stmt = $pdo->query("SELECT * FROM courses ORDER BY id ASC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
            break;

        case 'teachers':
            $stmt = $pdo->query("SELECT * FROM teachers ORDER BY id ASC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
            break;

        case 'portfolios':
            $stmt = $pdo->query("SELECT * FROM portfolios ORDER BY id DESC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
            break;

        case 'news':
            $stmt = $pdo->query("SELECT * FROM news ORDER BY post_date DESC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
            break;

        case 'contacts':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $name = $input['name'] ?? '';
                $email = $input['email'] ?? '';
                $subject = $input['subject'] ?? '';
                $message = $input['message'] ?? '';

                if ($name && $email && $message) {
                    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $subject, $message]);
                    echo json_encode(['status' => 'success', 'message' => 'ส่งข้อความสำเร็จแล้ว'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'], JSON_UNESCAPED_UNICODE);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC");
                $data = $stmt->fetchAll();
                echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'register':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $username = trim($input['username'] ?? '');
                $password = trim($input['password'] ?? '');
                $fullname = trim($input['fullname'] ?? '');
                $email = trim($input['email'] ?? '');
                $phone = trim($input['phone'] ?? '');

                if (!$username || !$password || !$fullname || !$email) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน (ชื่อผู้ใช้, รหัสผ่าน, ชื่อ-นามสกุล, อีเมล)'], JSON_UNESCAPED_UNICODE);
                    break;
                }

                $chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $chk->execute([$username, $email]);
                if ($chk->fetch()) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'ชื่อผู้ใช้หรืออีเมลนี้มีในระบบแล้ว'], JSON_UNESCAPED_UNICODE);
                    break;
                }

                $avatar = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=400&auto=format&fit=crop&q=80';
                $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, email, phone, avatar) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $password, $fullname, $email, $phone, $avatar]);
                $newId = $pdo->lastInsertId();

                $uStmt = $pdo->prepare("SELECT id, username, fullname, email, phone, bio, avatar, role FROM users WHERE id = ?");
                $uStmt->execute([$newId]);
                $user = $uStmt->fetch();

                echo json_encode(['status' => 'success', 'message' => 'ลงทะเบียนสำเร็จเข้าสู่ระบบอัตโนมัติ', 'user' => $user], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'login':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $username = trim($input['username'] ?? '');
                $password = trim($input['password'] ?? '');

                if (!$username || !$password) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน'], JSON_UNESCAPED_UNICODE);
                    break;
                }

                $stmt = $pdo->prepare("SELECT id, username, fullname, email, phone, bio, avatar, role, password FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();

                if ($user && $user['password'] === $password) {
                    unset($user['password']);
                    echo json_encode(['status' => 'success', 'message' => 'เข้าสู่ระบบสำเร็จ', 'user' => $user], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(401);
                    echo json_encode(['status' => 'error', 'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'], JSON_UNESCAPED_UNICODE);
                }
            }
            break;

        case 'profile':
            $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
            if ($userId > 0) {
                $stmt = $pdo->prepare("SELECT id, username, fullname, email, phone, bio, avatar, role, created_at FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
                if ($user) {
                    echo json_encode(['status' => 'success', 'data' => $user], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลผู้ใช้'], JSON_UNESCAPED_UNICODE);
                }
            }
            break;

        case 'update_profile':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $userId = (int)($input['user_id'] ?? 0);
                $fullname = trim($input['fullname'] ?? '');
                $email = trim($input['email'] ?? '');
                $phone = trim($input['phone'] ?? '');
                $bio = trim($input['bio'] ?? '');
                $avatar = trim($input['avatar'] ?? '');
                $newPassword = trim($input['password'] ?? '');

                if ($userId <= 0) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'ระบุ User ID ไม่ถูกต้อง'], JSON_UNESCAPED_UNICODE);
                    break;
                }

                if ($newPassword !== '') {
                    $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, phone = ?, bio = ?, avatar = ?, password = ? WHERE id = ?");
                    $stmt->execute([$fullname, $email, $phone, $bio, $avatar, $newPassword, $userId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, phone = ?, bio = ?, avatar = ? WHERE id = ?");
                    $stmt->execute([$fullname, $email, $phone, $bio, $avatar, $userId]);
                }

                $uStmt = $pdo->prepare("SELECT id, username, fullname, email, phone, bio, avatar, role FROM users WHERE id = ?");
                $uStmt->execute([$userId]);
                $updatedUser = $uStmt->fetch();

                echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลโปรไฟล์เรียบร้อยแล้ว', 'user' => $updatedUser], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'users':
            $stmt = $pdo->query("SELECT id, username, fullname, email, phone, role, created_at FROM users ORDER BY id DESC");
            $users = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $users], JSON_UNESCAPED_UNICODE);
            break;

        case 'courses_add':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $level = trim($input['level'] ?? 'ปวช.');
                $title = trim($input['title'] ?? '');
                $duration = trim($input['duration'] ?? '');
                $description = trim($input['description'] ?? '');
                $credits = (int)($input['credits'] ?? 0);

                if ($title && $duration) {
                    $stmt = $pdo->prepare("INSERT INTO courses (level, title, duration, description, credits) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$level, $title, $duration, $description, $credits]);
                    echo json_encode(['status' => 'success', 'message' => 'เพิ่มหลักสูตรเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกชื่อหลักสูตรและระยะเวลา'], JSON_UNESCAPED_UNICODE);
                }
            }
            break;

        case 'courses_update':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $id = (int)($input['id'] ?? 0);
                $level = trim($input['level'] ?? 'ปวช.');
                $title = trim($input['title'] ?? '');
                $duration = trim($input['duration'] ?? '');
                $description = trim($input['description'] ?? '');
                $credits = (int)($input['credits'] ?? 0);

                if ($id > 0 && $title && $duration) {
                    $stmt = $pdo->prepare("UPDATE courses SET level = ?, title = ?, duration = ?, description = ?, credits = ? WHERE id = ?");
                    $stmt->execute([$level, $title, $duration, $description, $credits, $id]);
                    echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลหลักสูตรเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วนหรือไม่ถูกต้อง'], JSON_UNESCAPED_UNICODE);
                }
            }
            break;

        case 'courses_delete':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $id = (int)($input['id'] ?? ($_POST['id'] ?? ($_GET['id'] ?? ($_REQUEST['id'] ?? 0))));
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['status' => 'success', 'message' => 'ลบหลักสูตรเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID หลักสูตรที่ต้องการลบ'], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'teachers_add':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $name = trim($input['name'] ?? '');
                $position = trim($input['position'] ?? '');
                $degree = trim($input['degree'] ?? '');
                $expertise = trim($input['expertise'] ?? '');
                $email = trim($input['email'] ?? '');
                $image = trim($input['image'] ?? 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&auto=format&fit=crop&q=80');

                if ($name && $position) {
                    $stmt = $pdo->prepare("INSERT INTO teachers (name, position, degree, expertise, email, image) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $position, $degree, $expertise, $email, $image]);
                    echo json_encode(['status' => 'success', 'message' => 'เพิ่มรายชื่ออาจารย์เรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกชื่อและตำแหน่งอาจารย์'], JSON_UNESCAPED_UNICODE);
                }
            }
            break;

        case 'teachers_update':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $id = (int)($input['id'] ?? 0);
                $name = trim($input['name'] ?? '');
                $position = trim($input['position'] ?? '');
                $degree = trim($input['degree'] ?? '');
                $expertise = trim($input['expertise'] ?? '');
                $email = trim($input['email'] ?? '');
                $image = trim($input['image'] ?? '');

                if ($id > 0 && $name && $position) {
                    $stmt = $pdo->prepare("UPDATE teachers SET name = ?, position = ?, degree = ?, expertise = ?, email = ?, image = ? WHERE id = ?");
                    $stmt->execute([$name, $position, $degree, $expertise, $email, $image, $id]);
                    echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลอาจารย์เรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลอาจารย์ไม่ครบถ้วน'], JSON_UNESCAPED_UNICODE);
                }
            }
            break;

        case 'teachers_delete':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $id = (int)($input['id'] ?? ($_POST['id'] ?? ($_GET['id'] ?? ($_REQUEST['id'] ?? 0))));
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['status' => 'success', 'message' => 'ลบอาจารย์เรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID อาจารย์ที่ต้องการลบ'], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'portfolios_add':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $title = trim($input['title'] ?? '');
                $student_name = trim($input['student_name'] ?? '');
                $level = trim($input['level'] ?? 'ปวส.2');
                $description = trim($input['description'] ?? '');
                $category = trim($input['category'] ?? 'ผลงานออกแบบ');
                $image_url = trim($input['image_url'] ?? 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&auto=format&fit=crop&q=80');

                if ($title && $student_name) {
                    $stmt = $pdo->prepare("INSERT INTO portfolios (title, student_name, level, description, category, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $student_name, $level, $description, $category, $image_url]);
                    echo json_encode(['status' => 'success', 'message' => 'เพิ่มผลงานนักศึกษาเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกชื่อผลงานและชื่อนักศึกษา'], JSON_UNESCAPED_UNICODE);
                }
            }
            break;

        case 'portfolios_update':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $id = (int)($input['id'] ?? 0);
                $title = trim($input['title'] ?? '');
                $student_name = trim($input['student_name'] ?? '');
                $level = trim($input['level'] ?? 'ปวส.2');
                $description = trim($input['description'] ?? '');
                $category = trim($input['category'] ?? 'ผลงานออกแบบ');
                $image_url = trim($input['image_url'] ?? '');

                if ($id > 0 && $title && $student_name) {
                    $stmt = $pdo->prepare("UPDATE portfolios SET title = ?, student_name = ?, level = ?, description = ?, category = ?, image_url = ? WHERE id = ?");
                    $stmt->execute([$title, $student_name, $level, $description, $category, $image_url, $id]);
                    echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลผลงานเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลผลงานไม่ครบถ้วน'], JSON_UNESCAPED_UNICODE);
                }
            }
            break;

        case 'portfolios_delete':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $id = (int)($input['id'] ?? ($_POST['id'] ?? ($_GET['id'] ?? ($_REQUEST['id'] ?? 0))));
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM portfolios WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['status' => 'success', 'message' => 'ลบผลงานนักศึกษาเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID ผลงานที่ต้องการลบ'], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'news_add':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $title = trim($input['title'] ?? '');
                $content = trim($input['content'] ?? '');
                $author = trim($input['author'] ?? 'ฝ่ายประชาสัมพันธ์');
                $category = trim($input['category'] ?? 'ข่าวประชาสัมพันธ์');
                $post_date = trim($input['post_date'] ?? date('Y-m-d'));
                $image_url = trim($input['image_url'] ?? 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800&auto=format&fit=crop&q=80');

                if ($title && $content) {
                    $stmt = $pdo->prepare("INSERT INTO news (title, content, author, category, post_date, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $content, $author, $category, $post_date, $image_url]);
                    echo json_encode(['status' => 'success', 'message' => 'เพิ่มข่าวสารเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกหัวข้อข่าวและรายละเอียด'], JSON_UNESCAPED_UNICODE);
                }
            }
            break;

        case 'news_delete':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $id = (int)($input['id'] ?? ($_POST['id'] ?? ($_GET['id'] ?? ($_REQUEST['id'] ?? 0))));
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['status' => 'success', 'message' => 'ลบข่าวสารเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID ข่าวสารที่ต้องการลบ'], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'contacts_delete':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $id = (int)($input['id'] ?? ($_POST['id'] ?? ($_GET['id'] ?? ($_REQUEST['id'] ?? 0))));
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['status' => 'success', 'message' => 'ลบข้อความสอบถามเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID ข้อความที่ต้องการลบ'], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'info_update':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $name_th = trim($input['name_th'] ?? '');
                $name_en = trim($input['name_en'] ?? '');
                $college_th = trim($input['college_th'] ?? '');
                $college_en = trim($input['college_en'] ?? '');
                $slogan = trim($input['slogan'] ?? '');
                $history = trim($input['history'] ?? '');
                $vision = trim($input['vision'] ?? '');
                $mission = trim($input['mission'] ?? '');
                $established_year = (int)($input['established_year'] ?? 2537);

                $stmt = $pdo->prepare("UPDATE department_info SET name_th = ?, name_en = ?, college_th = ?, college_en = ?, slogan = ?, history = ?, vision = ?, mission = ?, established_year = ? WHERE id = 1");
                $stmt->execute([$name_th, $name_en, $college_th, $college_en, $slogan, $history, $vision, $mission, $established_year]);
                echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลแผนกวิชาเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'news_update':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $id = (int)($input['id'] ?? 0);
                $title = trim($input['title'] ?? '');
                $content = trim($input['content'] ?? '');
                $author = trim($input['author'] ?? 'ฝ่ายประชาสัมพันธ์');
                $category = trim($input['category'] ?? 'ข่าวประชาสัมพันธ์');
                $post_date = trim($input['post_date'] ?? date('Y-m-d'));
                $image_url = trim($input['image_url'] ?? '');

                if ($id > 0 && $title && $content) {
                    $stmt = $pdo->prepare("UPDATE news SET title = ?, content = ?, author = ?, category = ?, post_date = ?, image_url = ? WHERE id = ?");
                    $stmt->execute([$title, $content, $author, $category, $post_date, $image_url, $id]);
                    echo json_encode(['status' => 'success', 'message' => 'อัปเดตข่าวสารเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลข่าวสารไม่ครบถ้วน'], JSON_UNESCAPED_UNICODE);
                }
            }
            break;

        case 'users_add':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $username = trim($input['username'] ?? '');
                $password = trim($input['password'] ?? '123456');
                $fullname = trim($input['fullname'] ?? '');
                $email = trim($input['email'] ?? '');
                $phone = trim($input['phone'] ?? '');
                $role = trim($input['role'] ?? 'user');
                $avatar = trim($input['avatar'] ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=400&auto=format&fit=crop&q=80');

                if ($username && $password && $fullname && $email) {
                    $chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                    $chk->execute([$username, $email]);
                    if ($chk->fetch()) {
                        http_response_code(400);
                        echo json_encode(['status' => 'error', 'message' => 'Username หรือ อีเมลนี้ถูกใช้งานแล้ว'], JSON_UNESCAPED_UNICODE);
                        break;
                    }

                    $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, email, phone, role, avatar) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $password, $fullname, $email, $phone, $role, $avatar]);
                    echo json_encode(['status' => 'success', 'message' => 'เพิ่มสมาชิกใหม่เรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอก Username, Password, ชื่อ-นามสกุล และ อีเมล'], JSON_UNESCAPED_UNICODE);
                }
            }
            break;

        case 'users_update':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $id = (int)($input['id'] ?? 0);
                $fullname = trim($input['fullname'] ?? '');
                $email = trim($input['email'] ?? '');
                $phone = trim($input['phone'] ?? '');
                $role = trim($input['role'] ?? 'user');
                $newPassword = trim($input['password'] ?? '');

                if ($id > 0 && $fullname && $email) {
                    if ($newPassword !== '') {
                        $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, phone = ?, role = ?, password = ? WHERE id = ?");
                        $stmt->execute([$fullname, $email, $phone, $role, $newPassword, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, phone = ?, role = ? WHERE id = ?");
                        $stmt->execute([$fullname, $email, $phone, $role, $id]);
                    }
                    echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลสมาชิกเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลสมาชิกไม่ครบถ้วน'], JSON_UNESCAPED_UNICODE);
                }
            }
            break;

        case 'users_delete':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $id = (int)($input['id'] ?? ($_POST['id'] ?? ($_GET['id'] ?? ($_REQUEST['id'] ?? 0))));
            if ($id > 0) {
                $chk = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                $chk->execute([$id]);
                $usr = $chk->fetch();
                if ($usr && $usr['username'] === 'admin') {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถลบผู้ดูแลระบบหลัก (Admin) ได้'], JSON_UNESCAPED_UNICODE);
                    break;
                }
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['status' => 'success', 'message' => 'ลบผู้ใช้งานเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID ผู้ใช้งานที่ต้องการลบ'], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'stats':
            $c_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
            $c_teachers = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
            $c_portfolios = $pdo->query("SELECT COUNT(*) FROM portfolios")->fetchColumn();
            $c_news = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
            $c_contacts = $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
            $c_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $c_admins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'courses_count' => (int)$c_courses,
                    'teachers_count' => (int)$c_teachers,
                    'portfolios_count' => (int)$c_portfolios,
                    'news_count' => (int)$c_news,
                    'contacts_count' => (int)$c_contacts,
                    'users_count' => (int)$c_users,
                    'admins_count' => (int)$c_admins,
                    'container_name' => 'api',
                    'internal_port' => 80,
                    'external_port' => 'Not Exposed (Internal Only)'
                ]
            ], JSON_UNESCAPED_UNICODE);
            break;

        default:
            echo json_encode([
                'status' => 'success',
                'service' => 'KPT Architecture REST API',
                'container' => 'api',
                'available_endpoints' => ['info', 'courses', 'teachers', 'portfolios', 'news', 'contacts', 'stats', 'register', 'login', 'profile', 'update_profile', 'users']
            ], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
