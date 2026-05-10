// ── Login Page JS ─────────────────────────────────────────────
// Handles real-time field validation and form submission guard.

document.addEventListener('DOMContentLoaded', () => {

    // Force-clear fields on load so browser autofill doesn't pre-populate
    // after logout or registration redirect
    const usernameEl = document.getElementById('username');
    const passwordEl = document.getElementById('password');
    if (usernameEl) usernameEl.value = '';
    if (passwordEl) passwordEl.value = '';

    const usernameHint    = document.getElementById('usernameHint');
    const passwordHint    = document.getElementById('passwordHint');
    const passwordStatus  = document.getElementById('passwordStatus');

    function setFieldState(input, hint, valid, msg) {
        input.classList.toggle('valid',   valid === true);
        input.classList.toggle('invalid', valid === false);
        if (hint) {
            hint.textContent = msg;
            hint.className   = 'field-hint ' + (valid === true ? 'ok' : valid === false ? 'fail' : 'info');
        }
    }

    usernameEl.addEventListener('input', () => {
        const v = usernameEl.value.trim();
        if (!v)           return setFieldState(usernameEl, usernameHint, null, '');
        if (v.length < 3) return setFieldState(usernameEl, usernameHint, false, 'At least 3 characters');
        setFieldState(usernameEl, usernameHint, true, '');
    });

    passwordEl.addEventListener('input', () => {
        const v = passwordEl.value;
        if (!v) {
            setFieldState(passwordEl, passwordHint, null, '');
            passwordStatus.textContent = '';
            passwordStatus.className   = 'input-status';
            return;
        }
        setFieldState(passwordEl, passwordHint, true, '');
        passwordStatus.textContent = '✓';
        passwordStatus.className   = 'input-status ok';
    });

    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const u = usernameEl.value.trim();
        const p = passwordEl.value;
        if (!u || !p) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning', title: 'Missing Fields',
                text: 'Please enter your username and password.',
                background: '#1a1830', color: '#f1f0ff', confirmButtonColor: '#4f46e5',
            });
        }
    });

    // Particle background
    const container = document.getElementById('particles');
    if (container) {
        for (let i = 0; i < 20; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.cssText = `left:${Math.random()*100}%;width:${2+Math.random()*4}px;height:${2+Math.random()*4}px;animation-duration:${8+Math.random()*12}s;animation-delay:${-Math.random()*10}s;`;
            container.appendChild(p);
        }
    }
});
