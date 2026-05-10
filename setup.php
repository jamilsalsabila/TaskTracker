<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup – Task Tracker</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 to-violet-100 flex items-center justify-center p-4">
<div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Task Tracker Setup</h1>
        <p class="text-gray-500 mt-1">Inisialisasi database pertama kali</p>
    </div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['host'] ?? 'localhost');
    $user = trim($_POST['user'] ?? 'root');
    $pass = $_POST['pass'] ?? '';
    $name = trim($_POST['name'] ?? 'tasktracker');
    $sample = isset($_POST['sample']);

    $errors = [];

    // Test connection without database first
    $conn = @new mysqli($host, $user, $pass);
    if ($conn->connect_error) {
        $errors[] = 'Tidak bisa terhubung ke MySQL: ' . $conn->connect_error;
    } else {
        // Create database
        if (!$conn->query("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
            $errors[] = 'Gagal membuat database: ' . $conn->error;
        } else {
            $conn->select_db($name);

            // Create tables
            $createUsers = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin','user') NOT NULL DEFAULT 'user',
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            $createTasks = "CREATE TABLE IF NOT EXISTS tasks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                status ENUM('todo','in_progress','review','done') NOT NULL DEFAULT 'todo',
                priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
                start_date DATE,
                due_date DATE,
                progress TINYINT UNSIGNED DEFAULT 0,
                manager VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            if (!$conn->query($createUsers) || !$conn->query($createTasks)) {
                $errors[] = 'Gagal membuat tabel: ' . $conn->error;
            } elseif ($sample) {
                $conn->query("INSERT IGNORE INTO tasks (title, description, status, priority, start_date, due_date, progress, manager) VALUES
                    ('Desain mockup halaman utama', 'Buat mockup UI untuk halaman dashboard', 'done', 'high', '2026-04-28', '2026-05-03', 100, 'Budi Santoso'),
                    ('Integrasi API backend', 'Hubungkan frontend dengan REST API', 'in_progress', 'urgent', '2026-05-01', '2026-05-12', 60, 'Budi Santoso'),
                    ('Penulisan dokumentasi teknis', 'Buat dokumentasi API dan panduan penggunaan', 'todo', 'medium', '2026-05-10', '2026-05-20', 0, 'Rina Pratiwi'),
                    ('Testing dan QA', 'Lakukan pengujian unit dan integrasi', 'todo', 'high', '2026-05-13', '2026-05-22', 0, 'Rina Pratiwi'),
                    ('Deployment ke server staging', 'Deploy ke environment staging', 'todo', 'medium', '2026-05-23', '2026-05-27', 0, 'Budi Santoso'),
                    ('Review keamanan (security audit)', 'Periksa celah keamanan', 'review', 'urgent', '2026-05-05', '2026-05-11', 80, 'Deni Kurniawan')");
            }
        }
        $conn->close();
    }

    if (empty($errors)) {
        // Write config
        $configContent = "<?php\ndefine('DB_HOST', '$host');\ndefine('DB_USER', '$user');\ndefine('DB_PASS', '$pass');\ndefine('DB_NAME', '$name');\n\nfunction getConnection(): mysqli {\n    \$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);\n    if (\$conn->connect_error) {\n        http_response_code(500);\n        die(json_encode(['success' => false, 'error' => 'Koneksi database gagal: ' . \$conn->connect_error]));\n    }\n    \$conn->set_charset('utf8mb4');\n    return \$conn;\n}\n";
        file_put_contents(__DIR__ . '/api/db.php', $configContent);
        ?>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
            <div class="flex items-center gap-2 text-green-700 font-semibold mb-1">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Setup berhasil!
            </div>
            <p class="text-green-600 text-sm">Database <strong><?= htmlspecialchars($name) ?></strong> siap digunakan.</p>
        </div>
        <a href="index.php" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl text-center transition-colors">
            Buka Task Tracker →
        </a>
        <?php
    } else {
        foreach ($errors as $err) {
            echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4 text-red-700 text-sm">' . htmlspecialchars($err) . '</div>';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !empty($errors ?? [])):
?>
    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">MySQL Host</label>
            <input type="text" name="host" value="localhost"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input type="text" name="user" value="root"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="pass" placeholder="(kosong jika tidak ada)"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Database</label>
            <input type="text" name="name" value="tasktracker"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
        </div>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="sample" checked class="rounded text-indigo-600">
            <span class="text-sm text-gray-600">Tambahkan data contoh</span>
        </label>
        <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl transition-colors mt-2">
            Buat Database & Mulai
        </button>
    </form>
<?php endif; ?>
</div>
</body>
</html>
