// ── Global JS ─────────────────────────────────────────────────
// Loaded on every page. Handles page transitions and shared utilities.

document.addEventListener('DOMContentLoaded', () => {
    // Smooth fade-in on page load
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.3s ease';
    requestAnimationFrame(() => { document.body.style.opacity = '1'; });

    // ── Theme Toggle (Global) ───────────────────────────────────
    // Load saved theme or default to dark
    const savedTheme = localStorage.getItem('theme') || 'dark';
    setTheme(savedTheme);

    function setTheme(theme) {
        // Update body class
        if (theme === 'light') {
            document.body.classList.add('light-mode');
        } else {
            document.body.classList.remove('light-mode');
        }

        // Update CSS variables for light mode
        if (theme === 'light') {
            document.documentElement.style.setProperty('--bg-dark', '#f1f5f9');
            document.documentElement.style.setProperty('--bg-sidebar', '#ffffff');
            document.documentElement.style.setProperty('--bg-card', '#ffffff');
            document.documentElement.style.setProperty('--bg-input', '#f1f5f9');
            document.documentElement.style.setProperty('--text-main', '#1e293b');
            document.documentElement.style.setProperty('--text-muted', '#475569');
            document.documentElement.style.setProperty('--border', '#cbd5e1');
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

    // Smooth fade-out before navigating away
    document.querySelectorAll('a[href]').forEach(link => {
        const href = link.getAttribute('href');
        // Skip anchors, external links, javascript:, and empty
        if (!href || href.startsWith('#') || href.startsWith('javascript') ||
            href.startsWith('http') || href.startsWith('mailto')) return;

        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.style.opacity = '0';
            setTimeout(() => { window.location.href = href; }, 280);
        });
    });
});

// ── SweetAlert2 helper wrappers ──────────────────────────────
// Common Swal config for the dark theme used throughout the app.

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    background: '#1a1830',
    color: '#f1f0ff',
});

function showToast(icon, message) {
    Toast.fire({ icon, title: message });
}

function confirmAction(title, text, onConfirm) {
    Swal.fire({
        icon: 'warning',
        title,
        text,
        background: '#1a1830',
        color: '#f1f0ff',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#4f46e5',
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel',
    }).then(result => {
        if (result.isConfirmed) onConfirm();
    });
}
