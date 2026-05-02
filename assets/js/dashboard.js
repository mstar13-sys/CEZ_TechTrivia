// ── Dashboard JS ──────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {

    // Date display
    const dateEl = document.getElementById('currentDate');
    if (dateEl) {
        dateEl.textContent = new Date().toLocaleDateString('en-US', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    }

    // Logout confirm
    window.confirmLogout = function(e) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            icon: 'question', title: 'Sign Out?',
            text: 'Are you sure you want to sign out?',
            background: '#1a1830', color: '#f1f0ff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', cancelButtonColor: '#4f46e5',
            confirmButtonText: 'Yes, sign out', cancelButtonText: 'Stay',
        }).then(r => { if (r.isConfirmed) form.submit(); });
        return false;
    };
});
