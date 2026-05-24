// ── Settings Page JS ────────────────────────────────────────────
// Theme toggle, form handling, and interactions.

document.addEventListener('DOMContentLoaded', () => {

    // ── Date display ──────────────────────────────────────────
    const dateEl = document.getElementById('currentDate');
    if (dateEl) {
        dateEl.textContent = new Date().toLocaleDateString('en-US', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    }

    const adminDateEl = document.getElementById('adminDate');
    if (adminDateEl) {
        adminDateEl.textContent = new Date().toLocaleDateString('en-US', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    }

    // ── Theme Toggle ─────────────────────────────────────────
    const themeBtns = document.querySelectorAll('.theme-btn');

    // Load saved theme or default to dark
    const savedTheme = localStorage.getItem('theme') || 'dark';
    setTheme(savedTheme);

    themeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const theme = btn.dataset.theme;
            setTheme(theme);
            localStorage.setItem('theme', theme);
        });
    });

    function setTheme(theme) {
        // Update body class
        if (theme === 'light') {
            document.body.classList.add('light-mode');
        } else {
            document.body.classList.remove('light-mode');
        }

        // Update button states
        themeBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.theme === theme);
        });

        // Update CSS variables for light mode
        if (theme === 'light') {
            document.documentElement.style.setProperty('--bg-dark', '#ffffff');
            document.documentElement.style.setProperty('--bg-sidebar', '#ffffff');
            document.documentElement.style.setProperty('--bg-card', '#ffffff');
            document.documentElement.style.setProperty('--bg-input', '#f1f5f9');
            document.documentElement.style.setProperty('--text-main', '#1e293b');
            document.documentElement.style.setProperty('--text-muted', '#64748b');
            document.documentElement.style.setProperty('--border', '#94a3b8');
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

    // ── Password Form Validation ───────────────────────────────
    const passwordForm = document.querySelector('input[name="settings_action"][value="update_password"]')?.form;
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const currentPassword = this.current_password.value;
            const newPassword = this.new_password.value;
            const confirmPassword = this.confirm_password.value;

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Passwords Do Not Match',
                    text: 'Please make sure your new passwords match.',
                    background: '#1a1830',
                    color: '#f1f0ff',
                    confirmButtonColor: '#4f46e5'
                });
                return;
            }

            if (newPassword.length < 6) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Password Too Short',
                    text: 'Your new password must be at least 6 characters long.',
                    background: '#1a1830',
                    color: '#f1f0ff',
                    confirmButtonColor: '#4f46e5'
                });
                return;
            }

            if (currentPassword === newPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Same Password',
                    text: 'Your new password must be different from your current password.',
                    background: '#1a1830',
                    color: '#f1f0ff',
                    confirmButtonColor: '#4f46e5'
                });
                return;
            }
        });
    }

    // ── Username Form Validation ────────────────────────────────
    const usernameForm = document.querySelector('input[name="settings_action"][value="update_username"]')?.form;
    if (usernameForm) {
        usernameForm.addEventListener('submit', function(e) {
            const newUsername = this.new_username.value.trim();

            if (newUsername.length < 3 || newUsername.length > 20) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Username',
                    text: 'Your username must be 3-20 characters long.',
                    background: '#1a1830',
                    color: '#f1f0ff',
                    confirmButtonColor: '#4f46e5'
                });
                return;
            }

            // Check for special characters (only allow letters, numbers, underscores)
            const usernameRegex = /^[a-zA-Z0-9_]+$/;
            if (!usernameRegex.test(newUsername)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Username',
                    text: 'Username can only contain letters, numbers, and underscores.',
                    background: '#1a1830',
                    color: '#f1f0ff',
                    confirmButtonColor: '#4f46e5'
                });
                return;
            }
        });
    }

    // ── Logout confirm ────────────────────────────────────────
    const deleteAccountForm = document.getElementById('deleteAccountForm');
    if (deleteAccountForm) {
        deleteAccountForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                icon: 'warning',
                title: 'Delete Account?',
                text: 'Your account will be hidden until an admin restores it.',
                input: 'textarea',
                inputLabel: 'Reason',
                inputPlaceholder: 'Enter your reason for deleting this account...',
                inputAttributes: { maxlength: 500 },
                inputValidator: value => !value || !value.trim() ? 'Please enter a reason.' : undefined,
                background: '#1a1830',
                color: '#f1f0ff',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#4f46e5',
                confirmButtonText: 'Delete account',
                cancelButtonText: 'Cancel',
            }).then(result => {
                if (result.isConfirmed) {
                    const reasonInput = form.querySelector('input[name="delete_reason"]');
                    if (reasonInput) reasonInput.value = result.value.trim();
                    form.submit();
                }
            });
        });
    }

    window.confirmLogout = function(e) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            icon: 'question',
            title: 'Sign Out?',
            text: 'Are you sure you want to sign out?',
            background: '#1a1830',
            color: '#f1f0ff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#4f46e5',
            confirmButtonText: 'Yes, sign out',
        }).then(r => { if (r.isConfirmed) form.submit(); });
        return false;
    };
});
