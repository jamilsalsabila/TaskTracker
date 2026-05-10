/* Task Tracker – Admin Panel */

document.addEventListener('alpine:init', () => {
    Alpine.data('adminApp', () => ({
        currentSection: 'users',
        sidebarOpen: false,
        users: [],
        tasks: [],
        loading: false,

        showResetModal: false,
        resetResult: { username: '', newPassword: '' },

        showDeleteUserConfirm: false,
        userToDelete: null,

        taskFilterUser: '',
        toast: { show: false, message: '', type: 'success' },

        async init() {
            await this.loadUsers();
        },

        async loadUsers() {
            this.loading = true;
            try {
                const res  = await fetch('api/users.php');
                const data = await res.json();
                if (data.success) this.users = data.users;
                else this.showToast('Gagal memuat data user', 'error');
            } catch { this.showToast('Tidak bisa terhubung ke server', 'error'); }
            this.loading = false;
        },

        async loadTasks() {
            this.loading = true;
            try {
                const res  = await fetch('api/tasks.php?admin_all=1');
                const data = await res.json();
                if (data.success) this.tasks = data.tasks;
                else this.showToast('Gagal memuat tasks', 'error');
            } catch { this.showToast('Tidak bisa terhubung ke server', 'error'); }
            this.loading = false;
        },

        async switchSection(section) {
            this.currentSection = section;
            if (section === 'users') await this.loadUsers();
            if (section === 'tasks') await this.loadTasks();
        },

        async toggleActive(user) {
            const val = user.is_active ? 0 : 1;
            const res = await fetch(`api/users.php?id=${user.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ is_active: val })
            });
            const data = await res.json();
            if (data.success) {
                user.is_active = val;
                this.showToast(val ? 'User diaktifkan' : 'User dinonaktifkan', 'success');
            } else { this.showToast('Gagal mengubah status', 'error'); }
        },

        async toggleRole(user) {
            if (user.id == APP_USER.id) { this.showToast('Tidak bisa mengubah role akun sendiri', 'error'); return; }
            const newRole = user.role === 'admin' ? 'user' : 'admin';
            const res = await fetch(`api/users.php?id=${user.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ role: newRole })
            });
            const data = await res.json();
            if (data.success) {
                user.role = newRole;
                this.showToast(`Role diubah ke ${newRole === 'admin' ? 'Admin' : 'User'}`, 'success');
            } else { this.showToast('Gagal mengubah role', 'error'); }
        },

        async resetPassword(user) {
            const res  = await fetch(`api/users.php?id=${user.id}&action=reset_password`, { method: 'POST' });
            const data = await res.json();
            if (data.success) {
                this.resetResult = { username: user.username, newPassword: data.new_password };
                this.showResetModal = true;
            } else { this.showToast('Gagal reset password', 'error'); }
        },

        askDeleteUser(user) {
            this.userToDelete = user;
            this.showDeleteUserConfirm = true;
        },

        async confirmDeleteUser() {
            if (!this.userToDelete) return;
            const res  = await fetch(`api/users.php?id=${this.userToDelete.id}`, { method: 'DELETE' });
            const data = await res.json();
            if (data.success) {
                this.users = this.users.filter(u => u.id != this.userToDelete.id);
                this.showDeleteUserConfirm = false;
                this.userToDelete = null;
                this.showToast('User dihapus', 'success');
            } else { this.showToast(data.error || 'Gagal menghapus user', 'error'); }
        },

        get filteredTasks() {
            if (!this.taskFilterUser) return this.tasks;
            return this.tasks.filter(t => String(t.user_id) === String(this.taskFilterUser));
        },

        get stats() {
            return {
                total:      this.users.length,
                admins:     this.users.filter(u => u.role === 'admin').length,
                active:     this.users.filter(u => parseInt(u.is_active) === 1).length,
                totalTasks: this.users.reduce((s, u) => s + parseInt(u.task_count || 0), 0),
            };
        },

        statusLabel(s) {
            return { todo: 'To Do', in_progress: 'Dikerjakan', review: 'Review', done: 'Selesai' }[s] || s;
        },
        priorityLabel(p) {
            return { urgent: 'Urgent', high: 'Tinggi', medium: 'Sedang', low: 'Rendah' }[p] || p;
        },
        initialsOf(name) {
            return (name || '?').split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 3000);
        },
    }));
});
