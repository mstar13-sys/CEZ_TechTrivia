// ── Global JS ─────────────────────────────────────────────────
// Loaded on every page. Handles page transitions and shared utilities.

document.addEventListener('DOMContentLoaded', () => {
    // Smooth fade-in on page load
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.3s ease';
    requestAnimationFrame(() => { document.body.style.opacity = '1'; });

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
