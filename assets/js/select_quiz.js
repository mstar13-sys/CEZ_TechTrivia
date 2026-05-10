// ── Quiz Selection Screen JS ─────────────────────────────────
// Manages category → difficulty → launch flow with AJAX availability check.

(function () {
    'use strict';

    // ── State ────────────────────────────────────────────────
    let selectedCategory   = null;
    let selectedDifficulty = null;

    // ── DOM refs ─────────────────────────────────────────────
    const stepDifficulty  = document.getElementById('stepDifficulty');
    const stepLaunch      = document.getElementById('stepLaunch');
    const summaryCategory = document.getElementById('summaryCategory');
    const summaryDiff     = document.getElementById('summaryDifficulty');
    const summaryCount    = document.getElementById('summaryCount');
    const inputCategory   = document.getElementById('inputCategory');
    const inputDifficulty = document.getElementById('inputDifficulty');
    const diffUnavailable = document.getElementById('diffUnavailableMsg');

    // Date display
    const dateEl = document.getElementById('currentDate');
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

    // ── Category buttons ──────────────────────────────────────
    document.querySelectorAll('.cat-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            selectedCategory   = btn.dataset.category;
            selectedDifficulty = null;

            // Reset difficulty selection
            document.querySelectorAll('.diff-btn').forEach(b => {
                b.classList.remove('selected');
                b.disabled = false;
            });
            diffUnavailable.style.display = 'none';
            stepLaunch.style.display      = 'none';

            showStep(stepDifficulty);
            fetchAvailableDifficulties(selectedCategory);
        });
    });

    // ── Difficulty buttons ────────────────────────────────────
    document.querySelectorAll('.diff-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.disabled) return;
            document.querySelectorAll('.diff-btn').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            selectedDifficulty = btn.dataset.difficulty;

            // Fetch question count then show launch card
            fetchQuestionCount(selectedCategory, selectedDifficulty);
        });
    });

    // ── Fetch available difficulties for a category ───────────
    function fetchAvailableDifficulties(category) {
        fetch(`../controllers/QuizController.php?action=difficulties&category=${encodeURIComponent(category)}`)
            .then(r => r.json())
            .then(data => {
                const available = data.difficulties || [];
                document.querySelectorAll('.diff-btn').forEach(btn => {
                    const diff = btn.dataset.difficulty;
                    btn.disabled = !available.includes(diff);
                });
            })
            .catch(() => {
                // On network error just leave all enabled
                document.querySelectorAll('.diff-btn').forEach(b => b.disabled = false);
            });
    }

    // ── Fetch question count and show launch card ─────────────
    function fetchQuestionCount(category, difficulty) {
        fetch(`../controllers/QuizController.php?action=count&category=${encodeURIComponent(category)}&difficulty=${encodeURIComponent(difficulty)}`)
            .then(r => r.json())
            .then(data => {
                const count = data.count || 0;
                if (count === 0) {
                    diffUnavailable.style.display = 'block';
                    stepLaunch.style.display      = 'none';
                    return;
                }
                diffUnavailable.style.display = 'none';
                summaryCategory.textContent   = category;
                summaryDiff.textContent       = capitalize(difficulty);
                summaryCount.textContent      = Math.min(count, 10) + ' questions';
                inputCategory.value           = category;
                inputDifficulty.value         = difficulty;
                showStep(stepLaunch);
            })
            .catch(() => {
                // Fallback: just show launch with unknown count
                summaryCategory.textContent = category;
                summaryDiff.textContent     = capitalize(difficulty);
                summaryCount.textContent    = 'Up to 10 questions';
                inputCategory.value         = category;
                inputDifficulty.value       = difficulty;
                showStep(stepLaunch);
            });
    }

    // ── Show a step with animation ────────────────────────────
    function showStep(el) {
        el.style.display = 'block';
        // Trigger reflow for CSS animation restart
        el.style.animation = 'none';
        void el.offsetHeight;
        el.style.animation = '';
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // ── Reset everything ──────────────────────────────────────
    window.resetSelection = function () {
        selectedCategory   = null;
        selectedDifficulty = null;
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('selected'));
        document.querySelectorAll('.diff-btn').forEach(b => { b.classList.remove('selected'); b.disabled = false; });
        stepDifficulty.style.display  = 'none';
        stepLaunch.style.display      = 'none';
        diffUnavailable.style.display = 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // ── Helpers ───────────────────────────────────────────────
    function capitalize(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : str;
    }

})();
