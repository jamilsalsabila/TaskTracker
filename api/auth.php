<?php
session_start();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$data   = json_decode(file_get_contents('php://input'), true) ?? [];

require_once __DIR__ . '/db.php';

switch ($action) {
    case 'login':           handleLogin($data);          break;
    case 'register':        handleRegister($data);       break;
    case 'logout':          handleLogout();              break;
    case 'change_password': handleChangePassword($data); break;
    case 'me':              handleMe();                  break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

function handleLogin(array $d): void {
    $email    = trim($d['email'] ?? '');
    $password = $d['password'] ?? '';

    if (!$email || !$password) {
        echo json_encode(['success' => false, 'error' => 'Email dan password wajib diisi']); return;
    }

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id, username, email, password, role, is_active FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close(); $conn->close();

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'error' => 'Email atau password salah']); return;
    }
    if (!$user['is_active']) {
        echo json_encode(['success' => false, 'error' => 'Akun dinonaktifkan. Hubungi admin']); return;
    }

    session_regenerate_id(true);
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email']    = $user['email'];
    $_SESSION['role']     = $user['role'];

    echo json_encode(['success' => true, 'user' => [
        'id' => $user['id'], 'username' => $user['username'],
        'email' => $user['email'], 'role' => $user['role'],
    ]]);
}

function handleRegister(array $d): void {
    $username = trim($d['username'] ?? '');
    $email    = trim($d['email'] ?? '');
    $password = $d['password'] ?? '';

    if (!$username || !$email || !$password) {
        echo json_encode(['success' => false, 'error' => 'Semua field wajib diisi']); return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Format email tidak valid']); return;
    }
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'error' => 'Password minimal 6 karakter']); return;
    }

    $conn = getConnection();

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close(); $conn->close();
        echo json_encode(['success' => false, 'error' => 'Email sudah terdaftar']); return;
    }
    $stmt->close();

    $row     = $conn->query("SELECT COUNT(*) cnt FROM users")->fetch_assoc();
    $isFirst = ((int)$row['cnt'] === 0);
    $role    = $isFirst ? 'admin' : 'user';
    $hash    = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $username, $email, $hash, $role);

    if (!$stmt->execute()) {
        $stmt->close(); $conn->close();
        echo json_encode(['success' => false, 'error' => 'Gagal mendaftar']); return;
    }

    $userId = $conn->insert_id;
    $stmt->close();

    // First user claims all orphan tasks (from sample data / setup)
    if ($isFirst) {
        $conn->query("UPDATE tasks SET user_id = $userId WHERE user_id IS NULL");
    }
    $conn->close();

    session_regenerate_id(true);
    $_SESSION['user_id']  = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email']    = $email;
    $_SESSION['role']     = $role;

    echo json_encode(['success' => true, 'user' => [
        'id' => $userId, 'username' => $username,
        'email' => $email, 'role' => $role,
    ]]);
}

function handleLogout(): void {
    session_destroy();
    echo json_encode(['success' => true]);
}

function handleChangePassword(array $d): void {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Tidak terautentikasi']); return;
    }

    $currentPw = $d['current_password'] ?? '';
    $newPw     = $d['new_password'] ?? '';

    if (!$currentPw || !$newPw) {
        echo json_encode(['success' => false, 'error' => 'Semua field wajib diisi']); return;
    }
    if (strlen($newPw) < 6) {
        echo json_encode(['success' => false, 'error' => 'Password baru minimal 6 karakter']); return;
    }

    $conn   = getConnection();
    $userId = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($currentPw, $user['password'])) {
        $conn->close();
        echo json_encode(['success' => false, 'error' => 'Password saat ini salah']); return;
    }

    $hash = password_hash($newPw, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param('si', $hash, $userId);
    $stmt->execute();
    $stmt->close(); $conn->close();

    echo json_encode(['success' => true]);
}

function handleMe(): void {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'authenticated' => false]); return;
    }
    echo json_encode(['success' => true, 'authenticated' => true, 'user' => [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email'    => $_SESSION['email'],
        'role'     => $_SESSION['role'],
    ]]);
}
