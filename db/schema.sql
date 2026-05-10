/*CREATE DATABASE IF NOT EXISTS tasktracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tasktracker;*/

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('todo', 'in_progress', 'review', 'done') NOT NULL DEFAULT 'todo',
    priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    start_date DATE,
    due_date DATE,
    progress TINYINT UNSIGNED DEFAULT 0,
    manager VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO tasks (title, description, status, priority, start_date, due_date, progress, manager) VALUES
('Desain mockup halaman utama', 'Buat mockup UI untuk halaman dashboard dan task list', 'done', 'high', '2026-04-28', '2026-05-03', 100, 'Budi Santoso'),
('Integrasi API backend', 'Hubungkan frontend dengan REST API untuk fitur CRUD task', 'in_progress', 'urgent', '2026-05-01', '2026-05-12', 60, 'Budi Santoso'),
('Penulisan dokumentasi teknis', 'Buat dokumentasi API dan panduan penggunaan sistem', 'todo', 'medium', '2026-05-10', '2026-05-20', 0, 'Rina Pratiwi'),
('Testing dan QA', 'Lakukan pengujian unit, integrasi, dan UAT', 'todo', 'high', '2026-05-13', '2026-05-22', 0, 'Rina Pratiwi'),
('Deployment ke server staging', 'Deploy aplikasi ke environment staging untuk review akhir', 'todo', 'medium', '2026-05-23', '2026-05-27', 0, 'Budi Santoso'),
('Review keamanan (security audit)', 'Periksa celah keamanan dan lakukan penetration testing dasar', 'review', 'urgent', '2026-05-05', '2026-05-11', 80, 'Deni Kurniawan');
