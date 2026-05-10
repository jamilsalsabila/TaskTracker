<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#6366f1">
<title>Task Tracker</title>

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: { brand: '#6366f1' }
        }
    }
};
</script>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Frappe Gantt -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="h-full bg-slate-100" x-data="taskApp" x-cloak>

<!-- ══════════════════════════════════════════════════════════
     LAYOUT WRAPPER
═══════════════════════════════════════════════════════════ -->
<div class="flex h-full">

    <!-- ── Sidebar (desktop) ────────────────────────────── -->
    <aside class="sidebar hidden md:flex flex-col p-4 gap-1">
        <!-- Logo -->
        <div class="flex items-center gap-3 px-2 py-4 mb-4">
            <div class="w-9 h-9 bg-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <span class="text-white font-bold text-lg">Task Tracker</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-0.5">
            <div @click="switchView('dashboard')"
                :class="currentView === 'dashboard' ? 'active' : ''"
                class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </div>
            <div @click="switchView('list')"
                :class="currentView === 'list' ? 'active' : ''"
                class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Daftar Task
                <span x-show="stats.total > 0"
                    class="ml-auto bg-indigo-700 text-white text-xs font-bold px-2 py-0.5 rounded-full"
                    x-text="stats.total"></span>
            </div>
            <div @click="switchView('gantt')"
                :class="currentView === 'gantt' ? 'active' : ''"
                class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
                Timeline
            </div>
            <div @click="switchView('kanban')"
                :class="currentView === 'kanban' ? 'active' : ''"
                class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                Kanban
            </div>
        </nav>

        <!-- Export Button -->
        <div class="relative mt-4" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open"
                class="btn btn-primary w-full justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export
                <svg class="w-3.5 h-3.5 ml-auto transition-transform" :class="open ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open"
                class="absolute bottom-full left-0 right-0 mb-2 bg-white rounded-xl shadow-2xl border border-slate-100 overflow-hidden z-10"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2">
                <button @click="exportExcel(); open = false"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Excel (.xlsx)
                </button>
                <div class="h-px bg-slate-100"></div>
                <button @click="exportPDF(); open = false"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Export PDF
                </button>
            </div>
        </div>

        <!-- Stats footer -->
        <div class="mt-4 pt-4 border-t border-slate-700">
            <div class="text-xs text-slate-500 mb-2 px-2">Ringkasan</div>
            <div class="grid grid-cols-2 gap-1 text-xs">
                <div class="bg-slate-800 rounded-lg p-2 text-center">
                    <div class="text-amber-400 font-bold text-base" x-text="stats.in_progress"></div>
                    <div class="text-slate-500">Proses</div>
                </div>
                <div class="bg-slate-800 rounded-lg p-2 text-center">
                    <div class="text-red-400 font-bold text-base" x-text="stats.overdue"></div>
                    <div class="text-slate-500">Terlambat</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- ── Main content ──────────────────────────────────── -->
    <div class="main-content flex flex-col">

        <!-- ── Top header ─────────────────────────────────── -->
        <header class="bg-white border-b border-slate-200 px-4 md:px-6 py-3 flex items-center gap-3 sticky top-0 z-30">
            <!-- Mobile menu trigger -->
            <button @click="sidebarOpen = true" class="md:hidden text-slate-500 hover:text-slate-700 p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Logo mobile -->
            <span class="md:hidden font-bold text-slate-800 text-lg">Task Tracker</span>

            <!-- Page title (desktop) -->
            <div class="hidden md:block">
                <h1 class="text-lg font-bold text-slate-800" x-text="{
                    dashboard: 'Dashboard',
                    list: 'Daftar Task',
                    gantt: 'Timeline Gantt',
                    kanban: 'Papan Kanban'
                }[currentView]"></h1>
            </div>

            <div class="flex-1"></div>

            <!-- Overdue badge -->
            <div x-show="stats.overdue > 0"
                class="hidden sm:flex items-center gap-1.5 bg-red-50 text-red-600 text-xs font-semibold px-3 py-1.5 rounded-full">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span x-text="stats.overdue + ' terlambat'"></span>
            </div>

            <!-- Add button (header, mobile) -->
            <button @click="openAddModal()"
                class="md:hidden btn btn-primary py-2 px-3 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>

            <!-- Add button (header, desktop) -->
            <button @click="openAddModal()"
                class="hidden md:flex btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Task
            </button>

            <!-- User menu -->
            <div class="relative ml-1">
                <button @click.stop="userMenuOpen = !userMenuOpen"
                    class="flex items-center gap-2 hover:bg-slate-100 rounded-xl px-2 py-1.5 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        <?= htmlspecialchars(strtoupper(substr($_SESSION['username'], 0, 2))) ?>
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-sm font-semibold text-slate-800 leading-none"><?= htmlspecialchars($_SESSION['username']) ?></p>
                        <p class="text-xs text-slate-500"><?= $_SESSION['role'] === 'admin' ? 'Admin' : 'User' ?></p>
                    </div>
                    <svg class="hidden sm:block w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="userMenuOpen" @click.stop
                    class="absolute right-0 top-full mt-2 bg-white rounded-xl shadow-xl border border-slate-100 overflow-hidden z-50 w-52"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="px-4 py-3 border-b border-slate-100">
                        <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($_SESSION['username']) ?></p>
                        <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($_SESSION['email']) ?></p>
                    </div>
                    <button @click="showPasswordModal = true; userMenuOpen = false"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Ganti Password
                    </button>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Panel Admin
                    </a>
                    <?php endif; ?>
                    <div class="border-t border-slate-100"></div>
                    <button @click="logout()"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Keluar
                    </button>
                </div>
            </div>
        </header>

        <!-- ── Page content ────────────────────────────────── -->
        <main class="flex-1 p-4 md:p-6 pb-24 md:pb-6 overflow-x-hidden">

            <!-- Loading -->
            <div x-show="loading" class="flex justify-center py-20">
                <div class="spinner"></div>
            </div>

            <!-- ════════════ DASHBOARD ════════════ -->
            <div x-show="!loading && currentView === 'dashboard'">

                <!-- Stat cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                    <!-- Total -->
                    <div class="stat-card">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total</span>
                            <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-slate-800" x-text="stats.total"></div>
                        <div class="text-xs text-slate-500 mt-1">semua task</div>
                    </div>
                    <!-- In Progress -->
                    <div class="stat-card">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Proses</span>
                            <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-amber-600" x-text="stats.in_progress"></div>
                        <div class="text-xs text-slate-500 mt-1">sedang dikerjakan</div>
                    </div>
                    <!-- Review -->
                    <div class="stat-card">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-blue-600 uppercase tracking-wide">Review</span>
                            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-blue-600" x-text="stats.review"></div>
                        <div class="text-xs text-slate-500 mt-1">perlu review</div>
                    </div>
                    <!-- Done -->
                    <div class="stat-card">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-green-600 uppercase tracking-wide">Selesai</span>
                            <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-green-600" x-text="stats.done"></div>
                        <div class="text-xs text-slate-500 mt-1">task selesai</div>
                    </div>
                </div>

                <!-- Overall progress bar -->
                <div class="bg-white rounded-2xl p-5 mb-6 border border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-bold text-slate-700">Progress Keseluruhan</h2>
                        <span class="text-sm font-semibold text-indigo-600"
                            x-text="stats.total ? Math.round((stats.done / stats.total) * 100) + '%' : '0%'"></span>
                    </div>
                    <div class="progress-bar-track" style="height:10px">
                        <div class="progress-bar-fill bg-indigo-500"
                            :style="'width:' + (stats.total ? Math.round((stats.done/stats.total)*100) : 0) + '%'"></div>
                    </div>
                    <div class="flex justify-between text-xs text-slate-500 mt-2">
                        <span x-text="stats.done + ' selesai'"></span>
                        <span x-text="(stats.total - stats.done) + ' tersisa'"></span>
                    </div>
                </div>

                <!-- Two column: upcoming + recent done -->
                <div class="grid md:grid-cols-2 gap-4">
                    <!-- Upcoming -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-100">
                        <h2 class="font-bold text-slate-700 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Deadline Terdekat
                        </h2>
                        <div x-show="upcomingTasks.length === 0" class="text-center py-8 text-slate-400 text-sm">
                            Tidak ada deadline dalam 7 hari ke depan
                        </div>
                        <template x-for="task in upcomingTasks" :key="task.id">
                            <div @click="openEditModal(task)"
                                class="flex items-center gap-3 py-3 border-b border-slate-50 last:border-0 cursor-pointer hover:bg-slate-50 rounded-lg px-2 -mx-2 transition-colors">
                                <div class="w-1 self-stretch rounded-full flex-shrink-0"
                                    :class="{
                                        'bg-red-500': task.priority==='urgent',
                                        'bg-orange-400': task.priority==='high',
                                        'bg-indigo-400': task.priority==='medium',
                                        'bg-green-400': task.priority==='low'
                                    }"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 truncate" x-text="task.title"></p>
                                    <p class="text-xs text-slate-500" x-text="task.manager || 'Tanpa manager'"></p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-xs font-semibold" :class="daysLeftClass(task)" x-text="daysLeftText(task.due_date)"></p>
                                    <p class="text-xs text-slate-400" x-text="formatDateFull(task.due_date)"></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Status breakdown -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-100">
                        <h2 class="font-bold text-slate-700 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Status Breakdown
                        </h2>
                        <div class="space-y-3">
                            <template x-for="[key, label, color] in [
                                ['todo', 'To Do', 'bg-slate-200'],
                                ['in_progress', 'Sedang Dikerjakan', 'bg-amber-400'],
                                ['review', 'Review', 'bg-blue-400'],
                                ['done', 'Selesai', 'bg-green-400']
                            ]" :key="key">
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-slate-600 font-medium" x-text="label"></span>
                                        <span class="text-slate-700 font-semibold" x-text="stats[key] || 0"></span>
                                    </div>
                                    <div class="progress-bar-track">
                                        <div class="progress-bar-fill" :class="color"
                                            :style="'width:' + (stats.total ? ((stats[key]||0)/stats.total*100) : 0) + '%'"></div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div x-show="stats.overdue > 0"
                            class="mt-4 flex items-center gap-2 bg-red-50 text-red-700 text-sm font-semibold rounded-xl px-4 py-2.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span x-text="stats.overdue + ' task melewati deadline!'"></span>
                        </div>
                    </div>
                </div>
            </div><!-- /dashboard -->

            <!-- ════════════ LIST VIEW ════════════ -->
            <div x-show="!loading && currentView === 'list'">

                <!-- Filters bar -->
                <div class="bg-white rounded-2xl p-4 mb-4 border border-slate-100">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Search -->
                        <div class="relative flex-1">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" placeholder="Cari task..." x-model="filters.search"
                                class="form-input pl-9">
                        </div>
                        <!-- Status filter -->
                        <select x-model="filters.status" class="form-input sm:w-44">
                            <option value="">Semua Status</option>
                            <option value="todo">To Do</option>
                            <option value="in_progress">Sedang Dikerjakan</option>
                            <option value="review">Review</option>
                            <option value="done">Selesai</option>
                        </select>
                        <!-- Priority filter -->
                        <select x-model="filters.priority" class="form-input sm:w-40">
                            <option value="">Semua Prioritas</option>
                            <option value="urgent">Urgent</option>
                            <option value="high">Tinggi</option>
                            <option value="medium">Sedang</option>
                            <option value="low">Rendah</option>
                        </select>
                        <!-- Sort -->
                        <select x-model="filters.sort" class="form-input sm:w-40">
                            <option value="smart">Urutkan: Cerdas</option>
                            <option value="due_date">Urutkan: Deadline</option>
                            <option value="priority">Urutkan: Prioritas</option>
                            <option value="created">Urutkan: Terbaru</option>
                        </select>
                    </div>

                    <!-- Active filters pill -->
                    <div x-show="filters.status || filters.priority || filters.search"
                        class="flex items-center gap-2 mt-3 flex-wrap">
                        <span class="text-xs text-slate-500">Filter aktif:</span>
                        <button x-show="filters.search" @click="filters.search = ''"
                            class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full flex items-center gap-1 hover:bg-indigo-200">
                            "<span x-text="filters.search"></span>" ×
                        </button>
                        <button x-show="filters.status" @click="filters.status = ''"
                            class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full flex items-center gap-1 hover:bg-indigo-200"
                            x-text="statusLabel(filters.status) + ' ×'"></button>
                        <button x-show="filters.priority" @click="filters.priority = ''"
                            class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full flex items-center gap-1 hover:bg-indigo-200"
                            x-text="priorityLabel(filters.priority) + ' ×'"></button>
                        <button @click="filters = {search:'', status:'', priority:'', sort:'smart'}"
                            class="text-xs text-slate-500 hover:text-slate-700 underline ml-auto">
                            Reset semua
                        </button>
                    </div>
                </div>

                <!-- Result count -->
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="text-sm text-slate-500">
                        <span class="font-semibold text-slate-700" x-text="filteredTasks.length"></span> task ditemukan
                    </span>
                </div>

                <!-- Empty state -->
                <div x-show="filteredTasks.length === 0" class="empty-state bg-white rounded-2xl border border-slate-100">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="font-semibold text-slate-600">Belum ada task</p>
                    <p class="text-sm mt-1">Tambahkan task pertama kamu!</p>
                    <button @click="openAddModal()" class="btn btn-primary mt-4 mx-auto">+ Tambah Task</button>
                </div>

                <!-- Task list -->
                <div class="space-y-3">
                    <template x-for="task in filteredTasks" :key="task.id">
                        <div class="task-card flex gap-3">
                            <!-- Priority bar -->
                            <div class="priority-bar self-stretch flex-shrink-0"
                                :class="'priority-bar ' + task.priority"></div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start gap-2 mb-2">
                                    <!-- Status dropdown -->
                                    <div class="relative flex-shrink-0">
                                        <button @click.stop="statusMenuTaskId = statusMenuTaskId === task.id ? null : task.id"
                                            :class="'badge badge-' + task.status"
                                            class="cursor-pointer hover:opacity-80 transition-opacity flex items-center gap-1"
                                            title="Klik untuk ubah status">
                                            <span x-text="statusLabel(task.status)"></span>
                                            <svg class="w-2.5 h-2.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <div x-show="statusMenuTaskId === task.id" @click.stop
                                            class="absolute top-full left-0 mt-1 bg-white rounded-xl shadow-xl border border-slate-100 z-30 overflow-hidden"
                                            style="min-width:185px">
                                            <template x-for="[sv, sl] in [['todo','To Do'],['in_progress','Sedang Dikerjakan'],['review','Review'],['done','Selesai']]" :key="sv">
                                                <button @click.stop="patchStatus(task.id, sv); statusMenuTaskId = null"
                                                    class="w-full flex items-center gap-2 px-3 py-2.5 text-xs hover:bg-slate-50 transition-colors"
                                                    :class="task.status === sv ? 'bg-slate-50' : ''">
                                                    <span :class="'badge badge-' + sv" x-text="sl"></span>
                                                    <svg x-show="task.status === sv" class="w-3 h-3 text-indigo-600 ml-auto flex-shrink-0"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <span :class="'badge badge-' + task.priority" x-text="priorityLabel(task.priority)"></span>
                                </div>

                                <h3 class="font-semibold text-slate-800 mb-1 leading-snug" x-text="task.title"></h3>
                                <p x-show="task.description" class="text-sm text-slate-500 mb-2 line-clamp-2"
                                    x-text="task.description"></p>

                                <!-- Progress bar -->
                                <div x-show="task.progress > 0 || task.status === 'in_progress'" class="mb-2">
                                    <div class="flex justify-between text-xs text-slate-500 mb-1">
                                        <span>Progress</span>
                                        <span x-text="task.progress + '%'"></span>
                                    </div>
                                    <div class="progress-bar-track">
                                        <div class="progress-bar-fill"
                                            :class="progressBarColor(task.progress)"
                                            :style="'width:' + task.progress + '%'"></div>
                                    </div>
                                </div>

                                <!-- Meta -->
                                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                    <span x-show="task.manager" class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                        </svg>
                                        <span x-text="task.manager"></span>
                                    </span>
                                    <span x-show="task.start_date" class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3"/>
                                        </svg>
                                        <span x-text="'Mulai: ' + formatDateFull(task.start_date)"></span>
                                    </span>
                                    <span x-show="task.due_date" :class="daysLeftClass(task)" class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span x-text="formatDateFull(task.due_date) + ' · ' + daysLeftText(task.due_date)"></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col gap-1 flex-shrink-0">
                                <button @click="openEditModal(task)"
                                    class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                    title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button @click="askDelete(task)"
                                    class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div><!-- /list -->

            <!-- ════════════ GANTT / TIMELINE ════════════ -->
            <div x-show="!loading && currentView === 'gantt'">
                <!-- Row 1: Skala waktu + Navigasi -->
                <div class="bg-white rounded-2xl p-4 mb-3 border border-slate-100">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Skala:</span>
                        <template x-for="[mode, label] in [['Quarter Day','6 Jam'],['Day','Harian'],['Week','Mingguan'],['Month','Bulanan']]" :key="mode">
                            <button @click="setGanttView(mode)"
                                class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 transition-colors"
                                x-text="label"></button>
                        </template>

                        <div class="w-px h-5 bg-slate-200 mx-1"></div>

                        <!-- Navigasi scroll -->
                        <button @click="scrollGantt(-1)" title="Geser ke kiri"
                            class="flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Lebih Lama
                        </button>
                        <button @click="scrollGanttToToday()"
                            class="flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-600 hover:bg-indigo-100 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Hari Ini
                        </button>
                        <button @click="scrollGantt(1)" title="Geser ke kanan"
                            class="flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                            Lebih Baru
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <button @click="scrollGanttToFirst()" title="Lompat ke task pertama"
                            class="flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors ml-auto">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                            </svg>
                            Task Pertama
                        </button>
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center gap-4 text-xs text-slate-400 mt-3 pt-3 border-t border-slate-100">
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>Sedang Dikerjakan</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-400 inline-block"></span>Review</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-indigo-500 inline-block"></span>To Do</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span>Selesai</span>
                    </div>
                </div>

                <!-- Mobile tip: tap to edit -->
                <div class="md:hidden bg-blue-50 border border-blue-100 rounded-xl p-3 mb-3 flex items-start gap-2">
                    <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-blue-700"><strong>Ketuk bar</strong> untuk edit tanggal via form. Scroll horizontal untuk lihat timeline.</p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                    <div id="gantt-container" class="gantt-wrapper p-4" style="min-height:300px"></div>
                </div>

                <p class="hidden md:block text-xs text-slate-400 mt-3 text-center">
                    Klik bar untuk edit · Drag bar untuk ubah tanggal · Drag ujung kanan untuk ubah progress
                </p>
            </div><!-- /gantt -->

            <!-- ════════════ KANBAN ════════════ -->
            <div x-show="!loading && currentView === 'kanban'">
                <p class="hidden md:block text-xs text-slate-400 mb-3 text-center">Drag & drop untuk pindah kolom</p>
                <p class="md:hidden text-xs text-slate-400 mb-3 text-center">Drag kartu atau gunakan dropdown status untuk pindah kolom</p>
                <div class="kanban-board">
                    <!-- To Do -->
                    <div class="kanban-column">
                        <div class="kanban-column-header">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-slate-400"></div>
                                <span class="text-sm font-bold text-slate-700">To Do</span>
                            </div>
                            <span class="text-xs bg-slate-200 text-slate-600 font-bold px-2 py-0.5 rounded-full"
                                x-text="tasks.filter(t=>t.status==='todo').length"></span>
                        </div>
                        <div id="kanban-todo" class="kanban-cards" data-status="todo"></div>
                    </div>
                    <!-- In Progress -->
                    <div class="kanban-column" style="background:#fffbeb">
                        <div class="kanban-column-header">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div>
                                <span class="text-sm font-bold text-amber-700">Dikerjakan</span>
                            </div>
                            <span class="text-xs bg-amber-100 text-amber-700 font-bold px-2 py-0.5 rounded-full"
                                x-text="tasks.filter(t=>t.status==='in_progress').length"></span>
                        </div>
                        <div id="kanban-in_progress" class="kanban-cards" data-status="in_progress"></div>
                    </div>
                    <!-- Review -->
                    <div class="kanban-column" style="background:#eff6ff">
                        <div class="kanban-column-header">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-blue-400"></div>
                                <span class="text-sm font-bold text-blue-700">Review</span>
                            </div>
                            <span class="text-xs bg-blue-100 text-blue-700 font-bold px-2 py-0.5 rounded-full"
                                x-text="tasks.filter(t=>t.status==='review').length"></span>
                        </div>
                        <div id="kanban-review" class="kanban-cards" data-status="review"></div>
                    </div>
                    <!-- Done -->
                    <div class="kanban-column" style="background:#f0fdf4">
                        <div class="kanban-column-header">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                                <span class="text-sm font-bold text-green-700">Selesai</span>
                            </div>
                            <span class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full"
                                x-text="tasks.filter(t=>t.status==='done').length"></span>
                        </div>
                        <div id="kanban-done" class="kanban-cards" data-status="done"></div>
                    </div>
                </div>
            </div><!-- /kanban -->

        </main><!-- /main -->
    </div><!-- /main-content -->
</div><!-- /layout -->

<!-- ══════════════════════════════════════════════════════════
     MOBILE SIDEBAR DRAWER
═══════════════════════════════════════════════════════════ -->
<div x-show="sidebarOpen" @click="sidebarOpen = false"
    class="fixed inset-0 bg-black/50 z-40 md:hidden"
    x-transition:enter="transition-opacity duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"></div>

<aside x-show="sidebarOpen" @click.stop
    class="fixed left-0 top-0 bottom-0 z-50 md:hidden sidebar w-64 flex flex-col p-4 shadow-2xl"
    x-transition:enter="transition-transform duration-250"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition-transform duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full">

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-indigo-500 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <span class="text-white font-bold text-lg">Task Tracker</span>
        </div>
        <button @click="sidebarOpen = false" class="text-slate-400 hover:text-white p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Export (mobile drawer) -->
    <div class="mt-2">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide px-1 mb-3">Ekspor Data</p>
        <div class="space-y-2">
            <button @click="exportExcel(); sidebarOpen = false"
                class="w-full flex items-center gap-3 bg-slate-800 hover:bg-slate-700 text-white rounded-xl px-4 py-3.5 text-sm font-semibold transition-colors">
                <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel (.xlsx)
            </button>
            <button @click="exportPDF(); sidebarOpen = false"
                class="w-full flex items-center gap-3 bg-slate-800 hover:bg-slate-700 text-white rounded-xl px-4 py-3.5 text-sm font-semibold transition-colors">
                <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export PDF
            </button>
        </div>
    </div>
</aside>

<!-- ══════════════════════════════════════════════════════════
     BOTTOM NAV (mobile only)
═══════════════════════════════════════════════════════════ -->
<nav class="bottom-nav md:hidden">
    <div @click="switchView('dashboard')" :class="currentView==='dashboard'?'active':''" class="bottom-nav-item">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span>Home</span>
    </div>
    <div @click="switchView('list')" :class="currentView==='list'?'active':''" class="bottom-nav-item">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/></svg>
        <span>Tasks</span>
    </div>
    <div @click="switchView('gantt')" :class="currentView==='gantt'?'active':''" class="bottom-nav-item">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13v-1m4 1v-3m4 3V8M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
        <span>Timeline</span>
    </div>
    <div @click="switchView('kanban')" :class="currentView==='kanban'?'active':''" class="bottom-nav-item">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>
        <span>Kanban</span>
    </div>
</nav>

<!-- ══════════════════════════════════════════════════════════
     TASK MODAL (Add / Edit)
═══════════════════════════════════════════════════════════ -->
<div x-show="showModal" class="modal-overlay"
    x-transition:enter="transition-opacity duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="closeModal()">

    <div class="modal-box" @click.stop>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-slate-800"
                x-text="modalMode === 'add' ? 'Tambah Task Baru' : 'Edit Task'"></h2>
            <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 p-1.5 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form @submit.prevent="saveTask()" class="space-y-4">
            <!-- Title -->
            <div>
                <label class="form-label">Judul Task <span class="text-red-500">*</span></label>
                <input type="text" x-model="form.title" class="form-input"
                    placeholder="Contoh: Revisi desain halaman login" autofocus>
            </div>

            <!-- Description -->
            <div>
                <label class="form-label">Deskripsi</label>
                <textarea x-model="form.description" class="form-input"
                    placeholder="Detail task, catatan, atau instruksi dari manager..."></textarea>
            </div>

            <!-- Status & Priority row -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Status</label>
                    <select x-model="form.status" class="form-input">
                        <option value="todo">To Do</option>
                        <option value="in_progress">Sedang Dikerjakan</option>
                        <option value="review">Review</option>
                        <option value="done">Selesai</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Prioritas</label>
                    <select x-model="form.priority" class="form-input">
                        <option value="low">Rendah</option>
                        <option value="medium">Sedang</option>
                        <option value="high">Tinggi</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>

            <!-- Date row -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="text" id="fp-start" x-model="form.start_date"
                        class="form-input" placeholder="YYYY-MM-DD">
                </div>
                <div>
                    <label class="form-label">Deadline</label>
                    <input type="text" id="fp-due" x-model="form.due_date"
                        class="form-input" placeholder="YYYY-MM-DD">
                </div>
            </div>

            <!-- Progress slider -->
            <div>
                <label class="form-label flex items-center justify-between">
                    <span>Progress</span>
                    <span class="text-indigo-600 font-bold" x-text="form.progress + '%'"></span>
                </label>
                <input type="range" min="0" max="100" step="5" x-model="form.progress"
                    class="w-full">
                <div class="flex justify-between text-xs text-slate-400 mt-1">
                    <span>0%</span><span>50%</span><span>100%</span>
                </div>
            </div>

            <!-- Manager -->
            <div>
                <label class="form-label">Manager / Pemberi Tugas</label>
                <input type="text" x-model="form.manager" class="form-input"
                    placeholder="Nama manager atau PIC">
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-2">
                <button type="button" @click="closeModal()" class="btn btn-ghost flex-1 justify-center">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary flex-1 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span x-text="modalMode === 'add' ? 'Tambahkan' : 'Simpan Perubahan'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     DELETE CONFIRM MODAL
═══════════════════════════════════════════════════════════ -->
<div x-show="showDeleteConfirm" class="modal-overlay"
    x-transition:enter="transition-opacity duration-150"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    @keydown.escape.window="showDeleteConfirm = false">

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
        <h3 class="text-lg font-bold text-slate-800 text-center mb-2">Hapus Task?</h3>
        <p class="text-slate-500 text-sm text-center mb-6">
            "<span class="font-semibold text-slate-700" x-text="taskToDelete?.title"></span>"
            akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.
        </p>
        <div class="flex gap-3">
            <button @click="showDeleteConfirm = false; taskToDelete = null"
                class="btn btn-ghost flex-1 justify-center">Batal</button>
            <button @click="confirmDelete()"
                class="btn btn-danger flex-1 justify-center">Hapus</button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     TOAST NOTIFICATION
═══════════════════════════════════════════════════════════ -->
<div x-show="toast.show"
    class="toast"
    :class="{
        'toast-success': toast.type === 'success',
        'toast-error': toast.type === 'error',
        'toast-info': toast.type === 'info'
    }"
    x-transition:enter="transition duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    x-text="toast.message">
</div>

<!-- ══════════════════════════════════════════════════════════
     PASSWORD CHANGE MODAL
═══════════════════════════════════════════════════════════ -->
<div x-show="showPasswordModal" class="modal-overlay"
    x-transition:enter="transition-opacity duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="showPasswordModal = false">

    <div class="modal-box" @click.stop style="max-width:400px">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-slate-800">Ganti Password</h2>
            <button @click="showPasswordModal = false" class="text-slate-400 hover:text-slate-600 p-1.5 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form @submit.prevent="changePassword()" class="space-y-4">
            <div>
                <label class="form-label">Password Saat Ini</label>
                <div class="relative">
                    <input :type="pwShow.current ? 'text' : 'password'" x-model="passwordForm.current_password"
                        class="form-input pr-10" placeholder="••••••••" autocomplete="current-password">
                    <button type="button" @click="pwShow.current = !pwShow.current"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg x-show="!pwShow.current" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="pwShow.current" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="form-label">Password Baru</label>
                <div class="relative">
                    <input :type="pwShow.new ? 'text' : 'password'" x-model="passwordForm.new_password"
                        class="form-input pr-10" placeholder="Min. 6 karakter" autocomplete="new-password">
                    <button type="button" @click="pwShow.new = !pwShow.new"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg x-show="!pwShow.new" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="pwShow.new" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="form-label">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input :type="pwShow.confirm ? 'text' : 'password'" x-model="passwordForm.confirm"
                        class="form-input pr-10" placeholder="Ulangi password baru" autocomplete="new-password">
                    <button type="button" @click="pwShow.confirm = !pwShow.confirm"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg x-show="!pwShow.confirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="pwShow.confirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="showPasswordModal = false" class="btn btn-ghost flex-1 justify-center">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary flex-1 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════════════ -->
<!-- Day.js -->
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/isBefore.js"></script>
<script>dayjs.extend(window.dayjs_plugin_isBefore);</script>

<!-- SheetJS (Excel export) -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- jsPDF + autoTable (PDF export) -->
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>

<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>

<!-- Frappe Gantt (pinned to 0.6.1 – stable UMD build) -->
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<!-- Alpine.js (must be last before app.js) -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- Current user (injected by PHP for JS use) -->
<script>
const APP_USER = <?= json_encode([
    'id'       => (int)$_SESSION['user_id'],
    'username' => $_SESSION['username'],
    'email'    => $_SESSION['email'],
    'role'     => $_SESSION['role'],
]) ?>;
</script>

<!-- App -->
<script src="assets/js/app.js"></script>
</body>
</html>
