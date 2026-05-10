/* Task Tracker – Alpine.js Application */

document.addEventListener('alpine:init', () => {
    Alpine.data('taskApp', () => ({

        /* ─── State ─────────────────────────────────────────── */
        currentView: 'dashboard',
        tasks: [],
        loading: false,

        /* Modal */
        showModal: false,
        modalMode: 'add',
        showDeleteConfirm: false,
        taskToDelete: null,

        /* Form */
        form: {
            id: null, title: '', description: '',
            status: 'todo', priority: 'medium',
            start_date: '', due_date: '',
            progress: 0, manager: ''
        },

        /* Filters */
        filters: { search: '', status: '', priority: '', sort: 'smart' },

        /* Toast */
        toast: { show: false, message: '', type: 'success' },

        /* Mobile sidebar */
        sidebarOpen: false,

        /* User menu & password modal */
        userMenuOpen: false,
        showPasswordModal: false,
        passwordForm: { current_password: '', new_password: '', confirm: '' },
        pwShow: { current: false, new: false, confirm: false },
        currentUser: typeof APP_USER !== 'undefined' ? APP_USER : null,

        /* Status dropdown (list view) */
        statusMenuTaskId: null,

        /* ─── Computed ───────────────────────────────────────── */
        get stats() {
            const now = dayjs().startOf('day');
            return {
                total:       this.tasks.length,
                todo:        this.tasks.filter(t => t.status === 'todo').length,
                in_progress: this.tasks.filter(t => t.status === 'in_progress').length,
                review:      this.tasks.filter(t => t.status === 'review').length,
                done:        this.tasks.filter(t => t.status === 'done').length,
                overdue:     this.tasks.filter(t =>
                    t.due_date && dayjs(t.due_date).isBefore(now) && t.status !== 'done'
                ).length,
            };
        },

        get filteredTasks() {
            let list = [...this.tasks];
            const s = this.filters.search.toLowerCase();
            if (s) list = list.filter(t =>
                t.title.toLowerCase().includes(s) ||
                (t.description || '').toLowerCase().includes(s) ||
                (t.manager || '').toLowerCase().includes(s)
            );
            if (this.filters.status)   list = list.filter(t => t.status === this.filters.status);
            if (this.filters.priority) list = list.filter(t => t.priority === this.filters.priority);

            const priorityOrder = { urgent: 0, high: 1, medium: 2, low: 3 };
            const statusOrder   = { in_progress: 0, review: 1, todo: 2, done: 3 };
            const sort = this.filters.sort;

            list.sort((a, b) => {
                if (sort === 'priority') return priorityOrder[a.priority] - priorityOrder[b.priority];
                if (sort === 'due_date') {
                    if (!a.due_date) return 1;
                    if (!b.due_date) return -1;
                    return dayjs(a.due_date).diff(dayjs(b.due_date));
                }
                if (sort === 'created') return dayjs(b.created_at).diff(dayjs(a.created_at));
                /* smart default */
                const sa = statusOrder[a.status], sb = statusOrder[b.status];
                if (sa !== sb) return sa - sb;
                const pa = priorityOrder[a.priority], pb = priorityOrder[b.priority];
                if (pa !== pb) return pa - pb;
                if (a.due_date && b.due_date) return dayjs(a.due_date).diff(dayjs(b.due_date));
                return 0;
            });
            return list;
        },

        get upcomingTasks() {
            const now = dayjs();
            return this.tasks
                .filter(t => t.due_date && t.status !== 'done')
                .filter(t => dayjs(t.due_date).diff(now, 'day') <= 7)
                .sort((a, b) => dayjs(a.due_date).diff(dayjs(b.due_date)))
                .slice(0, 5);
        },

        /* ─── Init ───────────────────────────────────────────── */
        async init() {
            window.taskApp = this; // expose for kanban card callbacks
            await this.loadTasks();
            document.addEventListener('click', () => { this.statusMenuTaskId = null; this.userMenuOpen = false; });
            this.$watch('currentView', view => {
                this.sidebarOpen = false;
                this.$nextTick(() => {
                    if (view === 'gantt')  this.initGantt();
                    if (view === 'kanban') this.initKanban();
                });
            });
        },

        /* ─── API ────────────────────────────────────────────── */
        async loadTasks() {
            this.loading = true;
            try {
                const res = await fetch('api/tasks.php');
                const data = await res.json();
                if (data.success) this.tasks = data.tasks;
                else this.showToast('Gagal memuat data', 'error');
            } catch {
                this.showToast('Tidak bisa terhubung ke server', 'error');
            }
            this.loading = false;
        },

        async saveTask() {
            if (!this.form.title.trim()) {
                this.showToast('Judul task wajib diisi', 'error'); return;
            }
            try {
                const isEdit  = !!this.form.id;
                const url     = isEdit ? `api/tasks.php?id=${this.form.id}` : 'api/tasks.php';
                const method  = isEdit ? 'PUT' : 'POST';
                const res     = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (data.success) {
                    await this.loadTasks();
                    this.closeModal();
                    this.showToast(isEdit ? 'Task diperbarui!' : 'Task ditambahkan!', 'success');
                    if (this.currentView === 'gantt')  this.$nextTick(() => this.initGantt());
                    if (this.currentView === 'kanban') this.$nextTick(() => this.initKanban());
                } else {
                    this.showToast(data.error || 'Gagal menyimpan task', 'error');
                }
            } catch {
                this.showToast('Gagal menyimpan task', 'error');
            }
        },

        async confirmDelete() {
            if (!this.taskToDelete) return;
            try {
                const res  = await fetch(`api/tasks.php?id=${this.taskToDelete.id}`, { method: 'DELETE' });
                const data = await res.json();
                if (data.success) {
                    await this.loadTasks();
                    this.showDeleteConfirm = false;
                    this.taskToDelete = null;
                    this.showToast('Task dihapus', 'success');
                    if (this.currentView === 'gantt')  this.$nextTick(() => this.initGantt());
                    if (this.currentView === 'kanban') this.$nextTick(() => this.initKanban());
                } else {
                    this.showToast(data.error || 'Gagal menghapus task', 'error');
                }
            } catch {
                this.showToast('Gagal menghapus task', 'error');
            }
        },

        async patchStatus(taskId, newStatus) {
            const task = this.tasks.find(t => t.id == taskId);
            if (!task) return;
            const prev = task.status;
            task.status = newStatus; // optimistic
            try {
                const res  = await fetch(`api/tasks.php?id=${taskId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ...task, status: newStatus })
                });
                const data = await res.json();
                if (!data.success) { task.status = prev; this.showToast('Gagal update status', 'error'); }
                else this.showToast('Status diperbarui', 'success');
            } catch {
                task.status = prev;
                this.showToast('Gagal update status', 'error');
            }
        },

        /* ─── Modal ──────────────────────────────────────────── */
        openAddModal() {
            this.form = {
                id: null, title: '', description: '',
                status: 'todo', priority: 'medium',
                start_date: dayjs().format('YYYY-MM-DD'),
                due_date: dayjs().add(7, 'day').format('YYYY-MM-DD'),
                progress: 0, manager: ''
            };
            this.modalMode = 'add';
            this.showModal = true;
            this.$nextTick(() => this.initDatepickers());
        },

        openEditModal(task) {
            this.form = {
                id: task.id,
                title: task.title,
                description: task.description || '',
                status: task.status,
                priority: task.priority,
                start_date: task.start_date || '',
                due_date: task.due_date || '',
                progress: task.progress || 0,
                manager: task.manager || ''
            };
            this.modalMode = 'edit';
            this.showModal = true;
            this.$nextTick(() => this.initDatepickers());
        },

        closeModal() {
            this.showModal = false;
        },

        askDelete(task) {
            this.taskToDelete = task;
            this.showDeleteConfirm = true;
        },

        /* ─── Flatpickr ──────────────────────────────────────── */
        initDatepickers() {
            const opts = { dateFormat: 'Y-m-d', allowInput: true };
            const startEl = document.getElementById('fp-start');
            const dueEl   = document.getElementById('fp-due');
            if (startEl && typeof flatpickr !== 'undefined') {
                if (startEl._flatpickr) startEl._flatpickr.destroy();
                flatpickr(startEl, {
                    ...opts,
                    defaultDate: this.form.start_date || null,
                    onChange: ([d]) => { this.form.start_date = d ? dayjs(d).format('YYYY-MM-DD') : ''; }
                });
            }
            if (dueEl && typeof flatpickr !== 'undefined') {
                if (dueEl._flatpickr) dueEl._flatpickr.destroy();
                flatpickr(dueEl, {
                    ...opts,
                    defaultDate: this.form.due_date || null,
                    onChange: ([d]) => { this.form.due_date = d ? dayjs(d).format('YYYY-MM-DD') : ''; }
                });
            }
        },

        /* ─── Gantt Chart ────────────────────────────────────── */
        ganttInstance: null,

        initGantt() {
            if (typeof Gantt === 'undefined') {
                this.showToast('Library Gantt gagal dimuat, cek koneksi internet', 'error');
                return;
            }
            // setTimeout memberi waktu browser menyelesaikan layout setelah x-show
            setTimeout(() => this._renderGantt(), 80);
        },

        _renderGantt() {
            const el = document.getElementById('gantt-container');
            if (!el) return;

            const ganttTasks = this.tasks
                .filter(t => t.start_date && t.due_date)
                .map(t => ({
                    id: String(t.id),
                    name: t.title,
                    start: t.start_date,
                    end: t.due_date,
                    progress: t.progress || 0,
                    custom_class: `gantt-${t.status}`
                }));

            el.innerHTML = '';

            if (!ganttTasks.length) {
                el.innerHTML = `
                    <div class="empty-state">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p>Belum ada task dengan tanggal mulai & selesai</p>
                        <p class="text-sm mt-1">Tambahkan task dengan tanggal untuk melihat Gantt chart</p>
                    </div>`;
                return;
            }

            this.ganttInstance = new Gantt('#gantt-container', ganttTasks, {
                view_mode: window.innerWidth < 640 ? 'Day' : 'Week',
                language: 'en',
                on_click: task => {
                    const full = this.tasks.find(t => String(t.id) === task.id);
                    if (full) this.openEditModal(full);
                },
                on_date_change: async (task, start, end) => {
                    const full = this.tasks.find(t => String(t.id) === task.id);
                    if (!full) return;
                    full.start_date = dayjs(start).format('YYYY-MM-DD');
                    full.due_date   = dayjs(end).format('YYYY-MM-DD');
                    await fetch(`api/tasks.php?id=${full.id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(full)
                    });
                    this.showToast('Tanggal diperbarui', 'info');
                },
                on_progress_change: async (task, progress) => {
                    const full = this.tasks.find(t => String(t.id) === task.id);
                    if (!full) return;
                    full.progress = progress;
                    await fetch(`api/tasks.php?id=${full.id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(full)
                    });
                    this.showToast('Progress diperbarui', 'info');
                },
            });
        },

        setGanttView(mode) {
            if (this.ganttInstance) this.ganttInstance.change_view_mode(mode);
        },

        scrollGantt(direction) {
            const container = document.querySelector('.gantt-container');
            if (container) container.scrollBy({ left: direction * 500, behavior: 'smooth' });
        },

        scrollGanttToToday() {
            const container = document.querySelector('.gantt-container');
            if (!container) return;
            // Cari elemen garis "hari ini" yang dirender Frappe Gantt
            const todayEl = container.querySelector('.today-highlight');
            if (todayEl) {
                const x = parseFloat(todayEl.getAttribute('x') || 0);
                container.scrollTo({ left: Math.max(0, x - container.clientWidth / 2), behavior: 'smooth' });
            } else {
                // Fallback: scroll ke tengah chart
                container.scrollTo({ left: container.scrollWidth / 2, behavior: 'smooth' });
            }
        },

        scrollGanttToFirst() {
            const container = document.querySelector('.gantt-container');
            if (container) container.scrollTo({ left: 0, behavior: 'smooth' });
        },

        /* ─── Kanban Board ───────────────────────────────────── */
        kanbanSortables: [],

        initKanban() {
            this.kanbanSortables.forEach(s => s.destroy());
            this.kanbanSortables = [];

            const statuses = ['todo', 'in_progress', 'review', 'done'];

            statuses.forEach(status => {
                const col = document.getElementById(`kanban-${status}`);
                if (!col) return;

                // Render cards
                col.innerHTML = '';
                this.tasks.filter(t => t.status === status).forEach(task => {
                    col.appendChild(this.buildKanbanCard(task));
                });

                // Init SortableJS
                const sortable = Sortable.create(col, {
                    group: 'tasks',
                    animation: 180,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    delay: 150,
                    delayOnTouchOnly: true,
                    onEnd: async evt => {
                        const id        = evt.item.dataset.id;
                        const newStatus = evt.to.dataset.status;
                        evt.item.dataset.status = newStatus;
                        await this.patchStatus(id, newStatus);
                        // Update badge in card
                        const badge = evt.item.querySelector('[data-badge]');
                        if (badge) {
                            badge.className = `badge badge-${newStatus}`;
                            badge.textContent = this.statusLabel(newStatus);
                        }
                    }
                });
                this.kanbanSortables.push(sortable);
            });
        },

        buildKanbanCard(task) {
            const card = document.createElement('div');
            card.className = 'kanban-card';
            card.dataset.id = task.id;
            card.dataset.status = task.status;

            const daysLeft  = task.due_date ? dayjs(task.due_date).diff(dayjs().startOf('day'), 'day') : null;
            const isOverdue = daysLeft !== null && daysLeft < 0 && task.status !== 'done';
            const dueText   = daysLeft === null ? '' :
                              daysLeft < 0  ? `${Math.abs(daysLeft)}h overdue` :
                              daysLeft === 0 ? 'Hari ini' :
                              daysLeft === 1 ? 'Besok' : `${daysLeft}h lagi`;

            card.innerHTML = `
                <div class="flex items-start justify-between gap-2 mb-2">
                    <span class="badge badge-${task.priority}" style="font-size:10px">${this.priorityLabel(task.priority)}</span>
                    <div class="flex gap-1 flex-shrink-0">
                        <button onclick="taskApp.openEditModal(taskApp.tasks.find(t=>t.id==${task.id}))"
                            class="text-slate-400 hover:text-indigo-600 p-1 rounded-md hover:bg-indigo-50 transition-colors" title="Edit">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button onclick="taskApp.askDelete(taskApp.tasks.find(t=>t.id==${task.id}))"
                            class="text-slate-400 hover:text-red-500 p-1 rounded-md hover:bg-red-50 transition-colors" title="Hapus">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <p class="text-sm font-semibold text-slate-800 mb-2 leading-snug">${this.escHtml(task.title)}</p>
                ${task.description ? `<p class="text-xs text-slate-500 mb-2 line-clamp-2">${this.escHtml(task.description)}</p>` : ''}
                ${task.progress > 0 ? `
                    <div class="mb-2">
                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                            <span>Progress</span><span>${task.progress}%</span>
                        </div>
                        <div class="progress-bar-track">
                            <div class="progress-bar-fill bg-indigo-500" style="width:${task.progress}%"></div>
                        </div>
                    </div>` : ''}
                <div class="flex items-center justify-between text-xs mt-2">
                    ${task.manager ? `<span class="text-slate-500 truncate max-w-[80px]" title="${this.escHtml(task.manager)}">
                        <svg class="w-3 h-3 inline mr-0.5 -mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>${this.escHtml(task.manager)}</span>` : '<span></span>'}
                    ${task.due_date ? `<span class="${isOverdue ? 'text-red-500 font-semibold' : 'text-slate-500'}">
                        <svg class="w-3 h-3 inline mr-0.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>${dueText}</span>` : ''}
                </div>
                <select class="kanban-status-select"
                    onchange="(async()=>{await taskApp.patchStatus(${task.id},this.value);taskApp.initKanban();})()"
                >
                    <option value="todo"${task.status==='todo'?' selected':''}>To Do</option>
                    <option value="in_progress"${task.status==='in_progress'?' selected':''}>Sedang Dikerjakan</option>
                    <option value="review"${task.status==='review'?' selected':''}>Review</option>
                    <option value="done"${task.status==='done'?' selected':''}>Selesai</option>
                </select>`;
            return card;
        },

        /* ─── Auth ───────────────────────────────────────────── */
        async logout() {
            await fetch('api/auth.php?action=logout', { method: 'POST' });
            window.location.href = 'auth.php';
        },

        async changePassword() {
            const f = this.passwordForm;
            if (!f.current_password || !f.new_password || !f.confirm) {
                this.showToast('Semua field wajib diisi', 'error'); return;
            }
            if (f.new_password.length < 6) {
                this.showToast('Password baru minimal 6 karakter', 'error'); return;
            }
            if (f.new_password !== f.confirm) {
                this.showToast('Konfirmasi password tidak cocok', 'error'); return;
            }
            try {
                const res  = await fetch('api/auth.php?action=change_password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ current_password: f.current_password, new_password: f.new_password })
                });
                const data = await res.json();
                if (data.success) {
                    this.showPasswordModal = false;
                    this.passwordForm = { current_password: '', new_password: '', confirm: '' };
                    this.pwShow = { current: false, new: false, confirm: false };
                    this.showToast('Password berhasil diubah', 'success');
                } else {
                    this.showToast(data.error || 'Gagal mengubah password', 'error');
                }
            } catch {
                this.showToast('Tidak bisa terhubung ke server', 'error');
            }
        },

        /* ─── Export ─────────────────────────────────────────── */
        exportExcel() {
            if (typeof XLSX === 'undefined') {
                this.showToast('Library Excel gagal dimuat', 'error'); return;
            }
            const rows = this.tasks.map(t => ({
                'Judul':        t.title,
                'Deskripsi':    t.description || '',
                'Status':       this.statusLabel(t.status),
                'Prioritas':    this.priorityLabel(t.priority),
                'Progress (%)': t.progress || 0,
                'Mulai':        t.start_date || '',
                'Deadline':     t.due_date || '',
                'Manager':      t.manager || '',
                'Dibuat':       t.created_at ? dayjs(t.created_at).format('YYYY-MM-DD') : '',
            }));
            const ws = XLSX.utils.json_to_sheet(rows);
            ws['!cols'] = [20,30,18,12,12,12,12,18,12].map(w => ({ wch: w }));
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Tasks');
            XLSX.writeFile(wb, `task-tracker-${dayjs().format('YYYY-MM-DD')}.xlsx`);
            this.showToast('File Excel berhasil diunduh', 'success');
        },

        exportPDF() {
            if (typeof window.jspdf === 'undefined') {
                this.showToast('Library PDF gagal dimuat', 'error'); return;
            }
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'landscape' });

            doc.setFontSize(16);
            doc.setTextColor(30, 41, 59);
            doc.text('Task Tracker Report', 14, 16);

            doc.setFontSize(9);
            doc.setTextColor(100, 116, 139);
            doc.text(
                `Diekspor: ${dayjs().format('D MMMM YYYY')}  ·  Total: ${this.tasks.length} task`,
                14, 23
            );

            doc.autoTable({
                startY: 28,
                head: [['Judul', 'Status', 'Prioritas', 'Progress', 'Mulai', 'Deadline', 'Manager']],
                body: this.tasks.map(t => [
                    t.title,
                    this.statusLabel(t.status),
                    this.priorityLabel(t.priority),
                    (t.progress || 0) + '%',
                    t.start_date || '—',
                    t.due_date   || '—',
                    t.manager    || '—',
                ]),
                styles:           { fontSize: 9, cellPadding: 3 },
                headStyles:       { fillColor: [99, 102, 241], textColor: 255, fontStyle: 'bold' },
                alternateRowStyles: { fillColor: [248, 250, 252] },
                columnStyles:     { 0: { cellWidth: 75 } },
            });

            doc.save(`task-tracker-${dayjs().format('YYYY-MM-DD')}.pdf`);
            this.showToast('File PDF berhasil diunduh', 'success');
        },

        /* ─── Toast ──────────────────────────────────────────── */
        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 3000);
        },

        /* ─── Helpers ────────────────────────────────────────── */
        formatDate(date) {
            if (!date) return '—';
            return dayjs(date).format('D MMM');
        },

        formatDateFull(date) {
            if (!date) return 'Tidak ada tanggal';
            return dayjs(date).format('D MMMM YYYY');
        },

        daysLeftText(date) {
            if (!date) return '';
            const days = dayjs(date).startOf('day').diff(dayjs().startOf('day'), 'day');
            if (days < 0)  return `${Math.abs(days)} hari terlambat`;
            if (days === 0) return 'Hari ini';
            if (days === 1) return 'Besok';
            return `${days} hari lagi`;
        },

        daysLeftClass(task) {
            if (task.status === 'done') return 'text-green-600';
            if (!task.due_date) return 'text-slate-400';
            const days = dayjs(task.due_date).startOf('day').diff(dayjs().startOf('day'), 'day');
            if (days < 0)  return 'text-red-500 font-semibold';
            if (days <= 2) return 'text-orange-500 font-semibold';
            return 'text-slate-500';
        },

        isOverdue(task) {
            return task.due_date &&
                   dayjs(task.due_date).isBefore(dayjs().startOf('day')) &&
                   task.status !== 'done';
        },

        progressBarColor(p) {
            if (p >= 100) return 'bg-green-500';
            if (p >= 75)  return 'bg-blue-500';
            if (p >= 50)  return 'bg-indigo-500';
            if (p >= 25)  return 'bg-amber-500';
            return 'bg-red-400';
        },

        statusLabel(s) {
            return { todo: 'To Do', in_progress: 'Sedang Dikerjakan', review: 'Review', done: 'Selesai' }[s] || s;
        },
        priorityLabel(p) {
            return { urgent: 'Urgent', high: 'Tinggi', medium: 'Sedang', low: 'Rendah' }[p] || p;
        },

        escHtml(str) {
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        },

        switchView(view) {
            this.currentView = view;
        },
    }));
});
