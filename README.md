# Task Tracker

Aplikasi web untuk melacak dan mengelola tugas dari manager. Dibangun dengan PHP, MySQL, Alpine.js, dan Tailwind CSS.

## Fitur

- Login & registrasi akun (user pertama yang daftar otomatis menjadi Admin)
- CRUD task: tambah, edit, hapus, update status & progress
- Filter task berdasarkan status dan prioritas
- Kanban board & tampilan list
- Ganti password sendiri
- **Admin Panel**: kelola semua user dan task, reset password user, aktifkan/nonaktifkan akun
- Responsif — mobile friendly (diuji di Safari iOS)

## Teknologi

| Layer | Stack |
|---|---|
| Backend | PHP 8+ |
| Database | MySQL / MariaDB |
| Frontend | Alpine.js 3, Tailwind CSS (CDN) |
| Server | Apache via XAMPP |

## Struktur Direktori

```
TaskTracker/
├── api/
│   ├── auth.php        # Login, register, logout, ganti password
│   ├── db.php          # Koneksi database (di-generate oleh setup.php)
│   ├── tasks.php       # CRUD task
│   └── users.php       # Manajemen user (admin only)
├── assets/
│   ├── css/style.css   # Custom styles
│   ├── js/app.js       # Alpine.js component (halaman utama)
│   └── js/admin.js     # Alpine.js component (admin panel)
├── db/
│   └── schema.sql      # Skema database
├── admin.php           # Halaman admin panel
├── auth.php            # Halaman login & registrasi
├── index.php           # Halaman utama (dashboard task)
└── setup.php           # Setup awal database
```

## Instalasi

### Prasyarat
- XAMPP (atau server dengan PHP 8+ dan MySQL/MariaDB)

### Langkah

1. Clone atau copy folder `TaskTracker` ke direktori htdocs:
   ```
   /Applications/XAMPP/xamppfiles/htdocs/TaskTracker/   # macOS
   C:\xampp\htdocs\TaskTracker\                          # Windows
   ```

2. Jalankan Apache dan MySQL dari XAMPP Control Panel.

3. Buka browser dan akses:
   ```
   http://localhost/TaskTracker/setup.php
   ```

4. Isi form setup (host, user, password, nama database) lalu klik **Buat Database & Mulai**. File `api/db.php` akan dibuat otomatis.

5. Akses aplikasi:
   ```
   http://localhost/TaskTracker/
   ```

6. Daftar akun — user pertama yang mendaftar otomatis menjadi Admin.

### Setup Manual (tanpa setup.php)

Jalankan `db/schema.sql` di MySQL, lalu buat `api/db.php` secara manual:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tasktracker');

function getConnection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode(['success' => false, 'error' => 'Koneksi database gagal: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
```

## Role & Hak Akses

| Fitur | User | Admin |
|---|:---:|:---:|
| Lihat & kelola task sendiri | ✓ | ✓ |
| Ganti password sendiri | ✓ | ✓ |
| Akses Admin Panel | — | ✓ |
| Lihat semua task semua user | — | ✓ |
| Kelola user (aktif/nonaktif, role, reset PW) | — | ✓ |
| Hapus user | — | ✓ |
