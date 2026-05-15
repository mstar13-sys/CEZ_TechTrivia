document.addEventListener('DOMContentLoaded', () => {
    const usernameEl = document.getElementById('username');
    const emailEl = document.getElementById('email');
    const passwordEl = document.getElementById('password');
    const confirmEl = document.getElementById('confirmPassword');

    function setHint(input, hint, valid, msg) {
        input.classList.toggle('valid', valid === true);
        input.classList.toggle('invalid', valid === false);
        if (hint) {
            hint.textContent = msg || '';
            hint.className = 'field-hint ' + (valid === true ? 'ok' : valid === false ? 'fail' : 'info');
        }
    }

    function setStatus(id, valid) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = valid ? '✓' : '✕';
        el.className = 'input-status ' + (valid ? 'ok' : 'fail');
    }

    function validatePassword() {
        const value = passwordEl.value;
        const valid = value.length >= 8 && /[A-Z]/.test(value) && /[0-9]/.test(value);
        setHint(passwordEl, document.getElementById('passwordHint'), value ? valid : null, value && !valid ? 'Use 8+ characters with an uppercase letter and a number.' : '');
        if (value) setStatus('pwStatus', valid);
        return valid;
    }

    function validateConfirm() {
        const value = confirmEl.value;
        const valid = value !== '' && value === passwordEl.value;
        setHint(confirmEl, document.getElementById('confirmHint'), value ? valid : null, value && !valid ? 'Passwords do not match.' : '');
        if (value) setStatus('confirmStatus', valid);
        return valid;
    }

    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.togglePassword);
            if (!input) return;
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.innerHTML = showing ? '&#128065;' : '&#128584;';
            button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            button.title = showing ? 'Show password' : 'Hide password';
        });
    });

    passwordEl.addEventListener('input', () => {
        validatePassword();
        if (confirmEl.value) validateConfirm();
    });
    confirmEl.addEventListener('input', validateConfirm);

    document.getElementById('forgotPasswordForm').addEventListener('submit', (e) => {
        const username = usernameEl.value.trim();
        const email = emailEl.value.trim();
        const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

        setHint(usernameEl, document.getElementById('userHint'), username ? true : false, username ? '' : 'Username is required.');
        setHint(emailEl, document.getElementById('emailHint'), validEmail, validEmail ? '' : 'Enter a valid email.');

        if (!username || !validEmail || !validatePassword() || !validateConfirm()) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Check Your Details',
                text: 'Please complete all fields correctly.',
                background: '#1a1830',
                color: '#f1f0ff',
                confirmButtonColor: '#4f46e5',
            });
        }
    });

    const container = document.getElementById('particles2');
    if (container) {
        for (let i = 0; i < 20; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.cssText = `left:${Math.random() * 100}%;width:${2 + Math.random() * 4}px;height:${2 + Math.random() * 4}px;animation-duration:${8 + Math.random() * 12}s;animation-delay:${-Math.random() * 10}s;`;
            container.appendChild(p);
        }
    }
});
