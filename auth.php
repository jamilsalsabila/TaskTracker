<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Task Tracker – Masuk</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-100 min-h-screen flex flex-col items-center justify-center py-10 px-4" x-data="authApp" x-cloak>

<div class="w-full max-w-sm">
    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-indigo-200">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">Task Tracker</h1>
        <p class="text-slate-500 text-sm mt-1">Kelola tugas dari manager dengan mudah</p>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 p-6 border border-slate-100">

        <!-- Tabs -->
        <div class="flex border border-slate-200 rounded-xl overflow-hidden mb-6 p-1 bg-slate-50">
            <button @click="tab = 'login'; error = ''"
                :class="tab === 'login' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 py-2 text-sm font-semibold rounded-lg transition-all">
                Masuk
            </button>
            <button @click="tab = 'register'; error = ''"
                :class="tab === 'register' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 py-2 text-sm font-semibold rounded-lg transition-all">
                Daftar
            </button>
        </div>

        <!-- Login Form -->
        <form x-show="tab === 'login'" @submit.prevent="login()" class="space-y-4">
            <div>
                <label class="form-label">Email</label>
                <input type="email" x-model="form.email" class="form-input" placeholder="email@contoh.com" autocomplete="email">
            </div>
            <div>
                <label class="form-label">Password</label>
                <div class="relative">
                    <input :type="show.loginPass ? 'text' : 'password'" x-model="form.password"
                        class="form-input pr-10" placeholder="••••••••" autocomplete="current-password">
                    <button type="button" @click="show.loginPass = !show.loginPass"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg x-show="!show.loginPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show.loginPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div x-show="error" x-transition class="text-xs text-red-600 bg-red-50 border border-red-100 rounded-lg p-3" x-text="error"></div>
            <button type="submit" class="btn btn-primary w-full justify-center" :disabled="loading">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span x-text="loading ? 'Memproses...' : 'Masuk'"></span>
            </button>
        </form>

        <!-- Register Form -->
        <form x-show="tab === 'register'" @submit.prevent="register()" class="space-y-4">
            <div>
                <label class="form-label">Nama</label>
                <input type="text" x-model="form.username" class="form-input" placeholder="Nama lengkap kamu" autocomplete="name">
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" x-model="form.email" class="form-input" placeholder="email@contoh.com" autocomplete="email">
            </div>
            <div>
                <label class="form-label">Password</label>
                <div class="relative">
                    <input :type="show.regPass ? 'text' : 'password'" x-model="form.password"
                        class="form-input pr-10" placeholder="Min. 6 karakter" autocomplete="new-password">
                    <button type="button" @click="show.regPass = !show.regPass"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg x-show="!show.regPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show.regPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="form-label">Konfirmasi Password</label>
                <div class="relative">
                    <input :type="show.regConfirm ? 'text' : 'password'" x-model="form.confirm"
                        class="form-input pr-10" placeholder="Ulangi password" autocomplete="new-password">
                    <button type="button" @click="show.regConfirm = !show.regConfirm"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg x-show="!show.regConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show.regConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div x-show="error" x-transition class="text-xs text-red-600 bg-red-50 border border-red-100 rounded-lg p-3" x-text="error"></div>
            <button type="submit" class="btn btn-primary w-full justify-center" :disabled="loading">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span x-text="loading ? 'Memproses...' : 'Daftar Akun'"></span>
            </button>
            <p class="text-xs text-slate-400 text-center">User pertama yang daftar otomatis menjadi Admin</p>
        </form>
    </div>

    <p class="text-center text-xs text-slate-400 mt-6">Task Tracker &copy; <?= date('Y') ?></p>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('authApp', () => ({
        tab: 'login',
        form: { username: '', email: '', password: '', confirm: '' },
        error: '',
        loading: false,
        show: { loginPass: false, regPass: false, regConfirm: false },

        async login() {
            this.error = '';
            if (!this.form.email || !this.form.password) { this.error = 'Email dan password wajib diisi'; return; }
            this.loading = true;
            try {
                const res  = await fetch('api/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: this.form.email, password: this.form.password })
                });
                const data = await res.json();
                if (data.success) { window.location.href = 'index.php'; }
                else { this.error = data.error || 'Gagal masuk'; }
            } catch { this.error = 'Tidak bisa terhubung ke server'; }
            this.loading = false;
        },

        async register() {
            this.error = '';
            if (this.form.password !== this.form.confirm) { this.error = 'Password tidak cocok'; return; }
            this.loading = true;
            try {
                const res  = await fetch('api/auth.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: this.form.username, email: this.form.email, password: this.form.password })
                });
                const data = await res.json();
                if (data.success) { window.location.href = 'index.php'; }
                else { this.error = data.error || 'Gagal mendaftar'; }
            } catch { this.error = 'Tidak bisa terhubung ke server'; }
            this.loading = false;
        },
    }));
});
</script>
</body>
</html>
