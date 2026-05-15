// ── Register Page JS ──────────────────────────────────────────
// Handles real-time validation, availability checks, password strength.

document.addEventListener('DOMContentLoaded', () => {

    const fUsername = document.getElementById('username');
    const fEmail = document.getElementById('email');
    const fPw = document.getElementById('password');
    const fConfirm = document.getElementById('confirmPassword');
    const btn = document.getElementById('registerBtn');

    const fieldValid = { username: false, email: false, password: false, confirm: false };

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

    function updateBtn() {
        btn.disabled = !Object.values(fieldValid).every(Boolean);
    }

    function setField(input, statusEl, hintEl, state, msg) {
        input.classList.toggle('valid', state === 'ok');
        input.classList.toggle('invalid', state === 'fail');
        if (statusEl) {
            statusEl.className = 'input-status ' + ({ ok: 'ok', fail: 'fail', loading: 'spin' }[state] || '');
            statusEl.textContent = ({ ok: '✓', fail: '✗', loading: '⟳' }[state] || '');
        }
        if (hintEl) {
            hintEl.textContent = msg || '';
            hintEl.className = 'field-hint ' + (state === 'ok' ? 'ok' : state === 'fail' ? 'fail' : 'info');
        }
    }

    // ── Username availability ─────────────────────────────────
    let userTimer;
    fUsername.addEventListener('input', () => {
        clearTimeout(userTimer);
        const v = fUsername.value.trim();
        const sE = document.getElementById('userStatus');
        const hE = document.getElementById('userHint');

        if (!v) { setField(fUsername, sE, hE, '', ''); fieldValid.username = false; updateBtn(); return; }
        if (v.length < 3) { setField(fUsername, sE, hE, 'fail', 'Min 3 characters'); fieldValid.username = false; updateBtn(); return; }
        if (v.length > 20) { setField(fUsername, sE, hE, 'fail', 'Max 20 characters'); fieldValid.username = false; updateBtn(); return; }
        if (!/^[a-zA-Z0-9_]+$/.test(v)) { setField(fUsername, sE, hE, 'fail', 'Letters, numbers, underscores only'); fieldValid.username = false; updateBtn(); return; }

        setField(fUsername, sE, hE, 'loading', 'Checking…');
        userTimer = setTimeout(async () => {
            try {
                const res = await fetch(`../controllers/AuthController.php?action=check_availability&field=username&value=${encodeURIComponent(v)}`);
                const data = await res.json();
                setField(fUsername, sE, hE, data.available ? 'ok' : 'fail', data.message);
                fieldValid.username = data.available;
            } catch { setField(fUsername, sE, hE, 'ok', ''); fieldValid.username = true; }
            updateBtn();
        }, 500);
    });

    // ── Email availability ────────────────────────────────────
    let emailTimer;
    fEmail.addEventListener('input', () => {
        clearTimeout(emailTimer);
        const v = fEmail.value.trim();
        const sE = document.getElementById('emailStatus');
        const hE = document.getElementById('emailHint');

        if (!v) { setField(fEmail, sE, hE, '', ''); fieldValid.email = false; updateBtn(); return; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) { setField(fEmail, sE, hE, 'fail', 'Enter a valid email'); fieldValid.email = false; updateBtn(); return; }

        setField(fEmail, sE, hE, 'loading', 'Checking…');
        emailTimer = setTimeout(async () => {
            try {
                const res = await fetch(`../controllers/AuthController.php?action=check_availability&field=email&value=${encodeURIComponent(v)}`);
                const data = await res.json();
                setField(fEmail, sE, hE, data.available ? 'ok' : 'fail', data.message);
                fieldValid.email = data.available;
            } catch { setField(fEmail, sE, hE, 'ok', ''); fieldValid.email = true; }
            updateBtn();
        }, 600);
    });

    // ── Password strength ─────────────────────────────────────
    function checkReq(id, met) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('met', met);
        el.querySelector('.check').textContent = met ? '✓' : '○';
    }

    fPw.addEventListener('input', () => {
        const v = fPw.value;
        const sE = document.getElementById('pwStatus');
        const fillEl = document.getElementById('strengthFill');
        const hasLen = v.length >= 8;
        const hasUp = /[A-Z]/.test(v);
        const hasNum = /[0-9]/.test(v);
        const hasSp = /[^a-zA-Z0-9]/.test(v);

        checkReq('req-length', hasLen);
        checkReq('req-upper', hasUp);
        checkReq('req-number', hasNum);
        checkReq('req-special', hasSp);

        const score = [hasLen, hasUp, hasNum, hasSp].filter(Boolean).length;
        const colors = ['#ef4444', '#f59e0b', '#eab308', '#10b981'];
        const widths = ['25%', '50%', '75%', '100%'];
        if (fillEl) {
            fillEl.style.width = v ? (widths[score - 1] || '10%') : '0%';
            fillEl.style.background = v ? (colors[score - 1] || '#ef4444') : 'transparent';
        }

        const valid = hasLen && hasUp && hasNum;
        fieldValid.password = valid;
        setField(fPw, sE, null, valid ? 'ok' : (v ? 'fail' : ''), '');
        updateBtn();
        if (fConfirm.value) fConfirm.dispatchEvent(new Event('input'));
    });

    // ── Confirm password ──────────────────────────────────────
    fConfirm.addEventListener('input', () => {
        const v = fConfirm.value;
        const sE = document.getElementById('confirmStatus');
        const hE = document.getElementById('confirmHint');
        if (!v) { setField(fConfirm, sE, hE, '', ''); fieldValid.confirm = false; updateBtn(); return; }
        const match = v === fPw.value;
        setField(fConfirm, sE, hE, match ? 'ok' : 'fail', match ? '✓ Passwords match' : 'Passwords do not match');
        fieldValid.confirm = match;
        updateBtn();
    });

    // ── Submit guard ──────────────────────────────────────────
    document.getElementById('registerForm').addEventListener('submit', function (e) {
        if (!Object.values(fieldValid).every(Boolean)) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Incomplete Form', text: 'Please fix the highlighted fields.', background: '#1a1830', color: '#f1f0ff', confirmButtonColor: '#4f46e5' });
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
