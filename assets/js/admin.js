// ── Admin Panel JS ────────────────────────────────────────────
// Tab switching, table search, confirm dialogs, edit modal.

document.addEventListener('DOMContentLoaded', () => {

    // ── Date display ──────────────────────────────────────────
    const dateEl = document.getElementById('adminDate');
    if (dateEl) {
        dateEl.textContent = new Date().toLocaleDateString('en-US', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    }

    // ── Theme Toggle Button ───────────────────────────────────
    const themeToggleBtn = document.getElementById('themeToggle');
    if (themeToggleBtn) {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        updateThemeButton(savedTheme);

        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = localStorage.getItem('theme') || 'dark';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', newTheme);
            setTheme(newTheme);
            updateThemeButton(newTheme);
        });
    }

    function updateThemeButton(theme) {
        const icon = themeToggleBtn?.querySelector('.theme-icon');
        if (icon) {
            icon.textContent = theme === 'dark' ? '🌙' : '☀️';
        }
    }

    function setTheme(theme) {
        if (theme === 'light') {
            document.body.classList.add('light-mode');
        } else {
            document.body.classList.remove('light-mode');
        }

        if (theme === 'light') {
            document.documentElement.style.setProperty('--bg-dark', '#f8fafc');
            document.documentElement.style.setProperty('--bg-sidebar', '#ffffff');
            document.documentElement.style.setProperty('--bg-card', '#ffffff');
            document.documentElement.style.setProperty('--bg-input', '#f1f5f9');
            document.documentElement.style.setProperty('--text-main', '#1e293b');
            document.documentElement.style.setProperty('--text-muted', '#64748b');
            document.documentElement.style.setProperty('--border', '#e2e8f0');
        } else {
            document.documentElement.style.setProperty('--bg-dark', '#0f0e1a');
            document.documentElement.style.setProperty('--bg-sidebar', '#13112b');
            document.documentElement.style.setProperty('--bg-card', '#1a1830');
            document.documentElement.style.setProperty('--bg-input', '#231f3a');
            document.documentElement.style.setProperty('--text-main', '#f1f0ff');
            document.documentElement.style.setProperty('--text-muted', '#9ca3af');
            document.documentElement.style.setProperty('--border', '#2e2b4a');
        }
    }

    // ── Tab switching ─────────────────────────────────────────
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-' + tab)?.classList.add('active');
            document.querySelectorAll('.nav-link[data-tab]').forEach(l => {
                l.classList.toggle('active', l.dataset.tab === tab);
            });
        });
    });

    document.querySelectorAll('.nav-link[data-tab]').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            document.querySelector(`.tab-btn[data-tab="${link.dataset.tab}"]`)?.click();
        });
    });
    const requestedTab = new URLSearchParams(window.location.search).get('tab') || window.location.hash.replace('#', '');
    if (requestedTab) {
        document.querySelector(`.tab-btn[data-tab="${requestedTab}"]`)?.click();
    }


    // ── Table search filter ───────────────────────────────────
    window.filterTable = function(tableId, query) {
        const q = query.toLowerCase();
        document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    };

    // ── Delete player ─────────────────────────────────────────
    window.confirmDelete = function(e, username) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            icon: 'warning', title: 'Delete Player?',
            html: `Delete <strong>${username}</strong>? This cannot be undone.`,
            background: '#1a1830', color: '#f1f0ff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', cancelButtonColor: '#4f46e5',
            confirmButtonText: 'Yes, delete', cancelButtonText: 'Cancel',
        }).then(r => { if (r.isConfirmed) form.submit(); });
        return false;
    };

    // ── Delete question ───────────────────────────────────────
    window.confirmDeleteQ = function(e) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            icon: 'warning', title: 'Delete Question?',
            text: 'This will also delete all its choices.',
            background: '#1a1830', color: '#f1f0ff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', cancelButtonColor: '#4f46e5',
            confirmButtonText: 'Delete', cancelButtonText: 'Cancel',
        }).then(r => { if (r.isConfirmed) form.submit(); });
        return false;
    };

    window.confirmDeleteAchievement = function(e) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            icon: 'warning', title: 'Delete Achievement?',
            text: 'Players will lose the unlocked record for this achievement.',
            background: '#1a1830', color: '#f1f0ff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', cancelButtonColor: '#4f46e5',
            confirmButtonText: 'Delete', cancelButtonText: 'Cancel',
        }).then(r => { if (r.isConfirmed) form.submit(); });
        return false;
    };

    window.confirmDeleteRank = function(e) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            icon: 'warning', title: 'Delete Rank?',
            text: 'Players in this XP range will fall back to the next matching rank.',
            background: '#1a1830', color: '#f1f0ff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', cancelButtonColor: '#4f46e5',
            confirmButtonText: 'Delete', cancelButtonText: 'Cancel',
        }).then(r => { if (r.isConfirmed) form.submit(); });
        return false;
    };

    // ── Logout confirm ────────────────────────────────────────
    window.confirmLogout = function(e) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            icon: 'question', title: 'Sign Out?',
            text: 'Are you sure you want to sign out?',
            background: '#1a1830', color: '#f1f0ff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', cancelButtonColor: '#4f46e5',
            confirmButtonText: 'Yes, sign out',
        }).then(r => { if (r.isConfirmed) form.submit(); });
        return false;
    };

    // ── Add question form validation ──────────────────────────
    const addForm = document.getElementById('addQuestionForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            const text    = this.question_text.value.trim();
            const choices = ['choice_0','choice_1','choice_2','choice_3'].map(n => this[n]?.value.trim());
            if (!text || choices.some(c => !c)) {
                e.preventDefault();
                Swal.fire({ icon:'warning', title:'Incomplete', text:'Fill in the question and all 4 choices.', background:'#1a1830', color:'#f1f0ff', confirmButtonColor:'#4f46e5' });
            }
        });
    }

    // ── Edit question modal ───────────────────────────────────
    window.openEditModal = function(questionId) {
        // Fetch question data via API
        fetch(`../controllers/QuizController.php?action=get_question&id=${questionId}`)
            .then(r => r.json())
            .then(data => {
                if (!data || data.error) {
                    Swal.fire({ icon:'error', title:'Error', text:'Could not load question data.', background:'#1a1830', color:'#f1f0ff', confirmButtonColor:'#4f46e5' });
                    return;
                }

                document.getElementById('edit_question_id').value        = data.question_id;
                document.getElementById('edit_question_text').value       = data.question_text;
                document.getElementById('edit_difficulty').value          = data.difficulty;
                document.getElementById('edit_category').value            = data.category;

                data.choices.forEach((choice, i) => {
                    const input = document.getElementById(`edit_choice_${i}`);
                    const radio = document.querySelector(`input[name="correct_index"][value="${i}"]`);
                    if (input) input.value = choice.choice_text;
                    if (radio) radio.checked = parseInt(choice.is_correct) === 1;
                });

                document.getElementById('editModal').classList.add('open');
            })
            .catch(() => {
                Swal.fire({ icon:'error', title:'Network Error', text:'Failed to fetch question.', background:'#1a1830', color:'#f1f0ff', confirmButtonColor:'#4f46e5' });
            });
    };

    window.closeEditModal = function() {
        document.getElementById('editModal')?.classList.remove('open');
    };

    // Close modal on backdrop click
    document.getElementById('editModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });

    window.openAchievementModal = function(achievement) {
        document.getElementById('edit_achievement_id').value = achievement.achievement_id || '';
        document.getElementById('edit_achievement_title').value = achievement.title || '';
        document.getElementById('edit_achievement_description').value = achievement.description || '';
        document.getElementById('edit_achievement_condition_type').value = achievement.condition_type || 'quiz_count';
        document.getElementById('edit_achievement_condition_value').value = achievement.condition_value || 1;
        document.getElementById('achievementModal')?.classList.add('open');
    };

    window.closeAchievementModal = function() {
        document.getElementById('achievementModal')?.classList.remove('open');
    };

    document.getElementById('achievementModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeAchievementModal();
    });

    window.openRankModal = function(rank) {
        document.getElementById('edit_rank_id').value = rank.rank_id || '';
        document.getElementById('edit_rank_name').value = rank.rank_name || '';
        document.getElementById('edit_rank_min_xp').value = rank.min_xp || 0;
        document.getElementById('edit_rank_max_xp').value = rank.max_xp === null ? '' : rank.max_xp;
        document.getElementById('edit_rank_medal').value = rank.medal || 'Bronze';
        document.getElementById('rankModal')?.classList.add('open');
    };

    window.closeRankModal = function() {
        document.getElementById('rankModal')?.classList.remove('open');
    };

    document.getElementById('rankModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeRankModal();
    });

    // Edit form validation
    const editForm = document.getElementById('editQuestionForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const text    = this.question_text.value.trim();
            const choices = ['choice_0','choice_1','choice_2','choice_3'].map(n => this[n]?.value.trim());
            if (!text || choices.some(c => !c)) {
                e.preventDefault();
                Swal.fire({ icon:'warning', title:'Incomplete', text:'Fill in the question and all 4 choices.', background:'#1a1830', color:'#f1f0ff', confirmButtonColor:'#4f46e5' });
            }
        });
    }
});

