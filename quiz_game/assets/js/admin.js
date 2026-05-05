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
        fetch(`../api/get_question.php?id=${questionId}`)
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
                    const radio = document.querySelector(`input[name="edit_correct_index"][value="${i}"]`);
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
