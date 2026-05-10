<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Tidak terautentikasi']); exit;
}

$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$userId  = (int)$_SESSION['user_id'];

require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {
    case 'GET':    handleGet($isAdmin, $userId);          break;
    case 'POST':   handlePost($userId);                   break;
    case 'PUT':    handlePut($id, $isAdmin, $userId);     break;
    case 'DELETE': handleDelete($id, $isAdmin, $userId);  break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

function extractTaskData(array $data): array {
    return [
        'title'       => trim($data['title'] ?? ''),
        'description' => $data['description'] ?? null,
        'status'      => $data['status'] ?? 'todo',
        'priority'    => $data['priority'] ?? 'medium',
        'start_date'  => ($data['start_date'] ?? '') ?: null,
        'due_date'    => ($data['due_date'] ?? '') ?: null,
        'progress'    => (int)($data['progress'] ?? 0),
        'manager'     => ($data['manager'] ?? '') ?: null,
    ];
}

function handleGet(bool $isAdmin, int $userId): void {
    $conn     = getConnection();
    $where    = [];
    $params   = [];
    $types    = '';
    $adminAll = $isAdmin && !empty($_GET['admin_all']);

    if ($adminAll) {
        $base = 'SELECT t.*, COALESCE(u.username, "Tidak diketahui") as owner_name FROM tasks t LEFT JOIN users u ON t.user_id = u.id';
        if (!empty($_GET['user_id'])) {
            $where[]  = 't.user_id = ?';
            $params[] = (int)$_GET['user_id'];
            $types   .= 'i';
        }
    } else {
        $base     = 'SELECT t.* FROM tasks t';
        $where[]  = 't.user_id = ?';
        $params[] = $userId;
        $types   .= 'i';
    }

    if (!empty($_GET['status']))   { $where[] = 't.status = ?';   $params[] = $_GET['status'];   $types .= 's'; }
    if (!empty($_GET['priority'])) { $where[] = 't.priority = ?'; $params[] = $_GET['priority']; $types .= 's'; }
    if (!empty($_GET['search'])) {
        $term     = '%' . $_GET['search'] . '%';
        $where[]  = '(t.title LIKE ? OR t.description LIKE ? OR t.manager LIKE ?)';
        array_push($params, $term, $term, $term);
        $types   .= 'sss';
    }

    $sql = $base;
    if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
    $sql .= ' ORDER BY
        CASE t.status WHEN "in_progress" THEN 0 WHEN "review" THEN 1 WHEN "todo" THEN 2 ELSE 3 END,
        CASE t.priority WHEN "urgent" THEN 0 WHEN "high" THEN 1 WHEN "medium" THEN 2 ELSE 3 END,
        t.due_date ASC';

    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $row['progress'] = (int)$row['progress'];
        $tasks[] = $row;
    }
    echo json_encode(['success' => true, 'tasks' => $tasks]);
    $conn->close();
}

function handlePost(int $userId): void {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $t    = extractTaskData($data);

    if (!$t['title']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Judul task wajib diisi']); return;
    }

    $conn = getConnection();
    $stmt = $conn->prepare(
        'INSERT INTO tasks (user_id, title, description, status, priority, start_date, due_date, progress, manager)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('isssssiss',
        $userId, $t['title'], $t['description'], $t['status'], $t['priority'],
        $t['start_date'], $t['due_date'], $t['progress'], $t['manager']
    );

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        $task  = $conn->query("SELECT * FROM tasks WHERE id = $newId")->fetch_assoc();
        $task['progress'] = (int)$task['progress'];
        echo json_encode(['success' => true, 'task' => $task]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $conn->close();
}

function handlePut(?int $id, bool $isAdmin, int $userId): void {
    if (!$id) { http_response_code(400); echo json_encode(['success' => false, 'error' => 'ID diperlukan']); return; }

    $conn = getConnection();
    if (!$isAdmin) {
        $chk = $conn->prepare('SELECT id FROM tasks WHERE id = ? AND user_id = ?');
        $chk->bind_param('ii', $id, $userId);
        $chk->execute(); $chk->store_result();
        if ($chk->num_rows === 0) {
            $chk->close(); $conn->close();
            http_response_code(403); echo json_encode(['success' => false, 'error' => 'Akses ditolak']); return;
        }
        $chk->close();
    }

    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $t    = extractTaskData($data);

    if (!$t['title']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Judul task wajib diisi']);
        $conn->close(); return;
    }

    $stmt = $conn->prepare(
        'UPDATE tasks SET title=?, description=?, status=?, priority=?, start_date=?, due_date=?, progress=?, manager=? WHERE id=?'
    );
    $stmt->bind_param('ssssssisi',
        $t['title'], $t['description'], $t['status'], $t['priority'],
        $t['start_date'], $t['due_date'], $t['progress'], $t['manager'], $id
    );

    if ($stmt->execute()) {
        $task = $conn->query("SELECT * FROM tasks WHERE id = $id")->fetch_assoc();
        $task['progress'] = (int)$task['progress'];
        echo json_encode(['success' => true, 'task' => $task]);
    } else {
        http_response_code(500); echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $conn->close();
}

function handleDelete(?int $id, bool $isAdmin, int $userId): void {
    if (!$id) { http_response_code(400); echo json_encode(['success' => false, 'error' => 'ID diperlukan']); return; }

    $conn = getConnection();
    if (!$isAdmin) {
        $chk = $conn->prepare('SELECT id FROM tasks WHERE id = ? AND user_id = ?');
        $chk->bind_param('ii', $id, $userId);
        $chk->execute(); $chk->store_result();
        if ($chk->num_rows === 0) {
            $chk->close(); $conn->close();
            http_response_code(403); echo json_encode(['success' => false, 'error' => 'Akses ditolak']); return;
        }
        $chk->close();
    }

    $stmt = $conn->prepare('DELETE FROM tasks WHERE id = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(404); echo json_encode(['success' => false, 'error' => 'Task tidak ditemukan']);
    }
    $conn->close();
}
