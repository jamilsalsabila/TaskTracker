<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Akses ditolak']); exit;
}

require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':    handleGet();              break;
    case 'PUT':    handlePut($id);           break;
    case 'DELETE': handleDelete($id);        break;
    case 'POST':   handlePost($action, $id); break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

function handleGet(): void {
    $conn = getConnection();
    $res  = $conn->query("
        SELECT u.id, u.username, u.email, u.role, u.is_active, u.created_at,
               COUNT(t.id) as task_count
        FROM users u
        LEFT JOIN tasks t ON t.user_id = u.id
        GROUP BY u.id
        ORDER BY u.created_at ASC
    ");
    $users = $res->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    echo json_encode(['success' => true, 'users' => $users]);
}

function handlePut(?int $id): void {
    $d       = json_decode(file_get_contents('php://input'), true) ?? [];
    $sets    = []; $types = ''; $params = [];

    if (isset($d['role']))      { $sets[] = 'role = ?';      $types .= 's'; $params[] = $d['role']; }
    if (isset($d['is_active'])) { $sets[] = 'is_active = ?'; $types .= 'i'; $params[] = (int)$d['is_active']; }

    if (!$sets) { echo json_encode(['success' => false, 'error' => 'Tidak ada field yang diubah']); return; }

    $types   .= 'i';
    $params[] = $id;
    $conn     = getConnection();
    $stmt     = $conn->prepare("UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close(); $conn->close();
    echo json_encode(['success' => true]);
}

function handleDelete(?int $id): void {
    if ($id === (int)$_SESSION['user_id']) {
        echo json_encode(['success' => false, 'error' => 'Tidak bisa menghapus akun sendiri']); return;
    }
    $conn = getConnection();
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close(); $conn->close();

    if ($affected > 0) echo json_encode(['success' => true]);
    else { http_response_code(404); echo json_encode(['success' => false, 'error' => 'User tidak ditemukan']); }
}

function handlePost(string $action, ?int $id): void {
    if ($action !== 'reset_password') {
        echo json_encode(['success' => false, 'error' => 'Invalid action']); return;
    }
    $newPw = 'reset' . rand(1000, 9999);
    $hash  = password_hash($newPw, PASSWORD_BCRYPT);
    $conn  = getConnection();
    $stmt  = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param('si', $hash, $id);
    $stmt->execute();
    $stmt->close(); $conn->close();
    echo json_encode(['success' => true, 'new_password' => $newPw]);
}
