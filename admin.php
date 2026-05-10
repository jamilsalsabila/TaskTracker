<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.php'); exit;
}
$adminName     = $_SESSION['username'];
$adminEmail    = $_SESSION['email'];
$adminInitials = strtoupper(substr($adminName, 0, 2));
$adminId       = (int)$_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel – Task Tracker</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<script>
const APP_USER = <?= json_encode(['id' => $adminId, 'username' => $adminName, 'email' => $adminEmail, 'role' => 'admin']) ?>;
</script>
</head>
<body class="bg-slate-100 min-h-screen" x-data="adminApp" x-cloak>

<div class="flex min-h-screen">

    <!-- ── Sidebar desktop (selalu tampil ≥md) ──────────────── -->
    <aside class="sidebar hidden md:flex flex-col p-4 w-60 flex-shrink-0 sticky top-0 h-screen">
        <div class="flex items-center gap-3 px-2 py-4 mb-2">
            <div class="w-9 h-9 bg-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <div class="text-white font-bold text-sm leading-tight">Task Tracker</div>
                <div class="text-indigo-300 text-xs font-semibold">Admin Panel</div>
            </div>
        </div>

        <nav class="flex-1 space-y-0.5">
            <div @click="switchSection('users')"
                :class="currentSection==='users' ? 'active' : ''" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Kelola Users
                <span x-show="users.length > 0"
                    class="ml-auto bg-indigo-700 text-white text-xs font-bold px-2 py-0.5 rounded-full"
                    x-text="users.length"></span>
            </div>
            <div @click="switchSection('tasks')"
                :class="currentSection==='tasks' ? 'active' : ''" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Semua Task
                <span x-show="tasks.length > 0"
                    class="ml-auto bg-indigo-700 text-white text-xs font-bold px-2 py-0.5 rounded-full"
                    x-text="tasks.length"></span>
            </div>
        </nav>

        <div class="mt-auto pt-4 border-t border-slate-700">
            <div class="flex items-center gap-2 px-2 mb-3">
                <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    <?= $adminInitials ?>
                </div>
                <div class="min-w-0">
                    <p class="text-white text-xs font-semibold truncate"><?= htmlspecialchars($adminName) ?></p>
                    <p class="text-indigo-300 text-xs">Admin</p>
                </div>
            </div>
            <a href="index.php" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke App
            </a>
        </div>
    </aside>

    <!-- ── Main ─────────────────────────────────────────────── -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- Header -->
        <header class="bg-white border-b border-slate-200 px-4 md:px-6 py-3 flex items-center gap-3 sticky top-0 z-30">
            <button @click="sidebarOpen = true" class="md:hidden text-slate-500 hover:text-slate-700 p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="text-base md:text-lg font-bold text-slate-800"
                x-text="currentSection === 'users' ? 'Kelola Users' : 'Semua Task'"></h1>
            <div class="flex-1"></div>
            <span class="hidden sm:inline-flex items-center text-xs bg-indigo-100 text-indigo-700 font-bold px-3 py-1.5 rounded-full">
                Admin: <?= htmlspecialchars($adminName) ?>
            </span>
        </header>

        <main class="flex-1 p-4 md:p-6">

            <div x-show="loading" class="flex justify-center py-20"><div class="spinner"></div></div>

            <!-- ══ USERS ══ -->
            <div x-show="!loading && currentSection === 'users'">

                <!-- Stats -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
                    <div class="stat-card">
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Total User</div>
                        <div class="text-3xl font-bold text-slate-800" x-text="stats.total"></div>
                    </div>
                    <div class="stat-card">
                        <div class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-2">Admin</div>
                        <div class="text-3xl font-bold text-indigo-600" x-text="stats.admins"></div>
                    </div>
                    <div class="stat-card">
                        <div class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-2">Aktif</div>
                        <div class="text-3xl font-bold text-green-600" x-text="stats.active"></div>
                    </div>
                    <div class="stat-card">
                        <div class="text-xs font-semibold text-amber-600 uppercase tracking-wide mb-2">Total Task</div>
                        <div class="text-3xl font-bold text-amber-600" x-text="stats.totalTasks"></div>
                    </div>
                </div>

                <!-- Mobile: card list -->
                <div class="space-y-3 md:hidden">
                    <template x-for="user in users" :key="user.id">
                        <div class="bg-white rounded-2xl border border-slate-100 p-4">
                            <!-- Top: avatar + info + badges -->
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-sm font-bold flex-shrink-0"
                                    x-text="initialsOf(user.username)"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-slate-800 text-sm leading-tight" x-text="user.username"></p>
                                    <p class="text-xs text-slate-500 truncate" x-text="user.email"></p>
                                    <p class="text-xs text-slate-400 mt-0.5"
                                        x-text="'Bergabung ' + (user.created_at ? user.created_at.slice(0,10) : '')"></p>
                                </div>
                            </div>
                            <!-- Badges + task count -->
                            <div class="flex items-center gap-2 mb-3">
                                <span :class="user.role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'"
                                    class="badge" x-text="user.role === 'admin' ? 'Admin' : 'User'"></span>
                                <span :class="parseInt(user.is_active) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                    class="badge" x-text="parseInt(user.is_active) ? 'Aktif' : 'Nonaktif'"></span>
                                <span class="text-xs text-slate-500 ml-auto">
                                    <span class="font-semibold text-slate-700" x-text="user.task_count"></span> task
                                </span>
                            </div>
                            <!-- Action buttons -->
                            <div class="grid grid-cols-4 gap-1.5 border-t border-slate-50 pt-3">
                                <button @click="toggleRole(user)"
                                    :disabled="user.id == <?= $adminId ?>"
                                    class="flex flex-col items-center gap-1 py-2 text-xs font-medium rounded-xl bg-slate-50 hover:bg-indigo-50 text-slate-500 hover:text-indigo-700 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    Role
                                </button>
                                <button @click="toggleActive(user)"
                                    :disabled="user.id == <?= $adminId ?>"
                                    :class="parseInt(user.is_active) ? 'hover:bg-amber-50 hover:text-amber-700' : 'hover:bg-green-50 hover:text-green-700'"
                                    class="flex flex-col items-center gap-1 py-2 text-xs font-medium rounded-xl bg-slate-50 text-slate-500 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                                    <svg x-show="parseInt(user.is_active)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    <svg x-show="!parseInt(user.is_active)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span x-text="parseInt(user.is_active) ? 'Nonaktif' : 'Aktifkan'"></span>
                                </button>
                                <button @click="resetPassword(user)"
                                    class="flex flex-col items-center gap-1 py-2 text-xs font-medium rounded-xl bg-slate-50 hover:bg-blue-50 text-slate-500 hover:text-blue-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                    Reset PW
                                </button>
                                <button @click="askDeleteUser(user)"
                                    :disabled="user.id == <?= $adminId ?>"
                                    class="flex flex-col items-center gap-1 py-2 text-xs font-medium rounded-xl bg-slate-50 hover:bg-red-50 text-slate-500 hover:text-red-600 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </template>
                    <div x-show="!loading && users.length === 0"
                        class="bg-white rounded-2xl border border-slate-100 p-12 text-center text-slate-400 text-sm">
                        Belum ada user terdaftar
                    </div>
                </div>

                <!-- Desktop: table -->
                <div class="hidden md:block bg-white rounded-2xl border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50">
                                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-5 py-3">User</th>
                                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Email</th>
                                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Role</th>
                                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Status</th>
                                    <th class="text-center text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Task</th>
                                    <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wide px-5 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="user in users" :key="user.id">
                                    <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition-colors">
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-sm font-bold flex-shrink-0"
                                                    x-text="initialsOf(user.username)"></div>
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-800" x-text="user.username"></p>
                                                    <p class="text-xs text-slate-400"
                                                        x-text="'Bergabung ' + (user.created_at ? user.created_at.slice(0,10) : '')"></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-500" x-text="user.email"></td>
                                        <td class="px-4 py-3">
                                            <span :class="user.role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'"
                                                class="badge" x-text="user.role === 'admin' ? 'Admin' : 'User'"></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span :class="parseInt(user.is_active) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                                class="badge" x-text="parseInt(user.is_active) ? 'Aktif' : 'Nonaktif'"></span>
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm font-semibold text-slate-700" x-text="user.task_count"></td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center justify-end gap-1">
                                                <button @click="toggleRole(user)"
                                                    :title="user.role === 'admin' ? 'Jadikan User' : 'Jadikan Admin'"
                                                    :disabled="user.id == <?= $adminId ?>"
                                                    class="p-1.5 rounded-lg hover:bg-indigo-50 text-slate-400 hover:text-indigo-600 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                    </svg>
                                                </button>
                                                <button @click="toggleActive(user)"
                                                    :title="parseInt(user.is_active) ? 'Nonaktifkan' : 'Aktifkan'"
                                                    :disabled="user.id == <?= $adminId ?>"
                                                    :class="parseInt(user.is_active) ? 'hover:text-amber-600 hover:bg-amber-50' : 'hover:text-green-600 hover:bg-green-50'"
                                                    class="p-1.5 rounded-lg text-slate-400 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                                                    <svg x-show="parseInt(user.is_active)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                    <svg x-show="!parseInt(user.is_active)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </button>
                                                <button @click="resetPassword(user)" title="Reset Password"
                                                    class="p-1.5 rounded-lg hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                                    </svg>
                                                </button>
                                                <button @click="askDeleteUser(user)"
                                                    :disabled="user.id == <?= $adminId ?>"
                                                    title="Hapus User"
                                                    class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-500 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="!loading && users.length === 0">
                                    <td colspan="6" class="text-center py-16 text-slate-400 text-sm">Belum ada user terdaftar</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- /users -->

            <!-- ══ TASKS ══ -->
            <div x-show="!loading && currentSection === 'tasks'">

                <!-- Filter -->
                <div class="bg-white rounded-2xl p-4 mb-4 border border-slate-100">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <span class="text-sm text-slate-600 font-medium flex-shrink-0">Filter User:</span>
                        <select x-model="taskFilterUser" class="form-input sm:w-auto sm:min-w-[180px]">
                            <option value="">Semua User</option>
                            <template x-for="user in users" :key="user.id">
                                <option :value="user.id" x-text="user.username"></option>
                            </template>
                        </select>
                        <span class="text-sm text-slate-500 sm:ml-auto">
                            <span class="font-semibold text-slate-700" x-text="filteredTasks.length"></span> task ditemukan
                        </span>
                    </div>
                </div>

                <!-- Mobile: card list -->
                <div class="space-y-3 md:hidden">
                    <template x-for="task in filteredTasks" :key="task.id">
                        <div class="bg-white rounded-2xl border border-slate-100 p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span :class="'badge badge-' + task.status" x-text="statusLabel(task.status)"></span>
                                <span :class="'badge badge-' + task.priority" x-text="priorityLabel(task.priority)"></span>
                            </div>
                            <p class="text-sm font-semibold text-slate-800 mb-0.5" x-text="task.title"></p>
                            <p x-show="task.manager" class="text-xs text-slate-400 mb-3" x-text="'Manager: ' + task.manager"></p>
                            <div class="flex items-center gap-2 mb-3">
                                <div class="progress-bar-track flex-1">
                                    <div class="progress-bar-fill bg-indigo-500" :style="'width:'+task.progress+'%'"></div>
                                </div>
                                <span class="text-xs text-slate-500 flex-shrink-0" x-text="task.progress+'%'"></span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500" x-text="task.due_date ? 'Deadline: '+task.due_date : 'Tanpa deadline'"></span>
                                <span class="font-semibold text-slate-700" x-text="task.owner_name || 'Tidak diketahui'"></span>
                            </div>
                        </div>
                    </template>
                    <div x-show="!loading && filteredTasks.length === 0"
                        class="bg-white rounded-2xl border border-slate-100 p-12 text-center text-slate-400 text-sm">
                        Tidak ada task
                    </div>
                </div>

                <!-- Desktop: table -->
                <div class="hidden md:block bg-white rounded-2xl border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50">
                                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-5 py-3">Judul Task</th>
                                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Status</th>
                                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Prioritas</th>
                                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Progress</th>
                                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Deadline</th>
                                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Pemilik</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="task in filteredTasks" :key="task.id">
                                    <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition-colors">
                                        <td class="px-5 py-3">
                                            <p class="text-sm font-semibold text-slate-800" x-text="task.title"></p>
                                            <p x-show="task.manager" class="text-xs text-slate-400 mt-0.5"
                                                x-text="'Manager: ' + task.manager"></p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span :class="'badge badge-' + task.status" x-text="statusLabel(task.status)"></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span :class="'badge badge-' + task.priority" x-text="priorityLabel(task.priority)"></span>
                                        </td>
                                        <td class="px-4 py-3 w-36">
                                            <div class="flex items-center gap-2">
                                                <div class="progress-bar-track flex-1">
                                                    <div class="progress-bar-fill bg-indigo-500" :style="'width:'+task.progress+'%'"></div>
                                                </div>
                                                <span class="text-xs text-slate-500 flex-shrink-0 w-8 text-right"
                                                    x-text="task.progress+'%'"></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-500" x-text="task.due_date || '—'"></td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm font-medium text-slate-700"
                                                x-text="task.owner_name || 'Tidak diketahui'"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="!loading && filteredTasks.length === 0">
                                    <td colspan="6" class="text-center py-16 text-slate-400 text-sm">Tidak ada task</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- /tasks -->

        </main>
    </div>
</div>

<!-- ══ Mobile overlay ══ -->
<div x-show="sidebarOpen" @click="sidebarOpen = false"
    class="fixed inset-0 bg-black/50 z-40 md:hidden"
    x-transition:enter="transition-opacity duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"></div>

<!-- ══ Mobile sidebar drawer ══ -->
<aside x-show="sidebarOpen" @click.stop
    class="fixed left-0 top-0 bottom-0 z-50 md:hidden bg-slate-800 w-60 flex flex-col shadow-2xl overflow-hidden"
    x-transition:enter="transition-transform duration-200"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition-transform duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full">

    <!-- Header -->
    <div class="flex items-center justify-between p-4 pb-2 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <div class="text-white font-bold text-sm leading-tight">Task Tracker</div>
                <div class="text-indigo-300 text-xs font-semibold">Admin Panel</div>
            </div>
        </div>
        <button @click="sidebarOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Nav (scrollable) -->
    <nav class="flex-1 overflow-y-auto px-4 py-2 space-y-0.5">
        <div @click="switchSection('users'); sidebarOpen = false"
            :class="currentSection==='users' ? 'active' : ''" class="nav-item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Kelola Users
            <span x-show="users.length > 0"
                class="ml-auto bg-indigo-700 text-white text-xs font-bold px-2 py-0.5 rounded-full"
                x-text="users.length"></span>
        </div>
        <div @click="switchSection('tasks'); sidebarOpen = false"
            :class="currentSection==='tasks' ? 'active' : ''" class="nav-item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            Semua Task
            <span x-show="tasks.length > 0"
                class="ml-auto bg-indigo-700 text-white text-xs font-bold px-2 py-0.5 rounded-full"
                x-text="tasks.length"></span>
        </div>
    </nav>

    <!-- Footer (pinned) -->
    <div class="flex-shrink-0 px-4 pt-4 pb-4 border-t border-slate-700"
        style="padding-bottom: max(1rem, env(safe-area-inset-bottom))">
        <div class="flex items-center gap-2 px-2 mb-3">
            <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                <?= $adminInitials ?>
            </div>
            <div class="min-w-0">
                <p class="text-white text-xs font-semibold truncate"><?= htmlspecialchars($adminName) ?></p>
                <p class="text-indigo-300 text-xs">Admin</p>
            </div>
        </div>
        <a href="index.php" class="nav-item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke App
        </a>
    </div>
</aside>

<!-- ══ Reset Password Modal ══ -->
<div x-show="showResetModal" class="modal-overlay" @keydown.escape.window="showResetModal = false"
    x-transition:enter="transition-opacity duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm mx-4 shadow-2xl" @click.stop
        x-transition:enter="transition-transform duration-200"
        x-transition:enter-start="scale-90 opacity-0"
        x-transition:enter-end="scale-100 opacity-100">
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-slate-800 text-center mb-1">Password Direset</h3>
        <p class="text-slate-500 text-sm text-center mb-4">
            Password <strong x-text="resetResult.username"></strong> berhasil direset
        </p>
        <div class="bg-slate-50 rounded-xl p-4 text-center mb-5 border border-slate-100">
            <p class="text-xs text-slate-500 mb-2">Password Baru Sementara</p>
            <p class="text-2xl font-bold text-indigo-600 tracking-widest font-mono" x-text="resetResult.newPassword"></p>
            <p class="text-xs text-slate-400 mt-2">Sampaikan ke user dan minta segera ganti password</p>
        </div>
        <button @click="showResetModal = false" class="btn btn-primary w-full justify-center">Tutup</button>
    </div>
</div>

<!-- ══ Delete User Confirm ══ -->
<div x-show="showDeleteUserConfirm" class="modal-overlay" @keydown.escape.window="showDeleteUserConfirm = false"
    x-transition:enter="transition-opacity duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm mx-4 shadow-2xl" @click.stop
        x-transition:enter="transition-transform duration-200"
        x-transition:enter-start="scale-90 opacity-0"
        x-transition:enter-end="scale-100 opacity-100">
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-slate-800 text-center mb-2">Hapus User?</h3>
        <p class="text-slate-500 text-sm text-center mb-6">
            "<span class="font-semibold text-slate-700" x-text="userToDelete?.username"></span>"
            dan semua task miliknya akan dihapus permanen.
        </p>
        <div class="flex gap-3">
            <button @click="showDeleteUserConfirm = false; userToDelete = null"
                class="btn btn-ghost flex-1 justify-center">Batal</button>
            <button @click="confirmDeleteUser()"
                class="btn btn-danger flex-1 justify-center">Hapus</button>
        </div>
    </div>
</div>

<!-- ══ Toast ══ -->
<div x-show="toast.show" class="toast"
    :class="{'toast-success': toast.type==='success', 'toast-error': toast.type==='error', 'toast-info': toast.type==='info'}"
    x-text="toast.message"
    x-transition:enter="transition duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"></div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="assets/js/admin.js"></script>
</body>
</html>
