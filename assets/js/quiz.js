// ── Quiz Page JS ──────────────────────────────────────────────
// Handles answer selection, timer countdown, animations, and auto-submit.

(function () {
    'use strict';

    // Question BGM modal sounds
    const quizSounds = {
        question: document.getElementById('questionBgm'),
        finalAnswer: document.getElementById('finalAnswerBgm'),
        win: document.getElementById('winBgm'),
        lose: document.getElementById('loseBgm')
    };

    function playQuizSound(sound) {
        if (!sound) return;

        sound.pause();
        sound.currentTime = 0;

        const playRequest = sound.play();
        if (playRequest && typeof playRequest.catch === 'function') {
            playRequest.catch(function() {});
        }
    }

    // ── Theme ─────────────────────────────────────────────────
    const themeToggleBtn = document.getElementById('themeToggle');
    const savedTheme = localStorage.getItem('theme') || 'dark';
    applyTheme(savedTheme);
    updateThemeIcon(savedTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const current = localStorage.getItem('theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', next);
            applyTheme(next);
            updateThemeIcon(next);
        });
    }

    function updateThemeIcon(theme) {
        const icon = themeToggleBtn?.querySelector('.theme-icon');
        if (icon) icon.textContent = theme === 'dark' ? '🌙' : '☀️';
    }

    function applyTheme(theme) {
        if (theme === 'light') {
            document.body.classList.add('light-mode');
            document.documentElement.style.setProperty('--bg',      '#ffffff');
            document.documentElement.style.setProperty('--card-bg', '#ffffff');
            document.documentElement.style.setProperty('--border',  '#94a3b8');
            document.documentElement.style.setProperty('--text',    '#111827');
            document.documentElement.style.setProperty('--muted',   '#475569');
        } else {
            document.body.classList.remove('light-mode');
            document.documentElement.style.setProperty('--bg',      '#0f0e1a');
            document.documentElement.style.setProperty('--card-bg', '#1a1830');
            document.documentElement.style.setProperty('--border',  '#2e2b4a');
            document.documentElement.style.setProperty('--text',    '#f1f0ff');
            document.documentElement.style.setProperty('--muted',   '#9ca3af');
        }
    }

    // ── Timer ─────────────────────────────────────────────────
    const timerWrap   = document.getElementById('timerWrap');
    const timerText   = document.getElementById('timerText');
    const timerCircle = document.getElementById('timerCircle');
    const timeoutForm = document.getElementById('timeoutForm');

    const TIMES = { easy: 45, medium: 30, hard: 15 };
    const CIRCUMFERENCE = 2 * Math.PI * 18; // r = 18

    let timerInterval = null;
    let timeLeft      = 0;
    let totalTime     = 0;

    if (timerWrap && timerCircle) {
        timerCircle.style.strokeDasharray  = CIRCUMFERENCE;
        timerCircle.style.strokeDashoffset = 0;

        const difficulty = timerWrap.dataset.difficulty || 'medium';
        const isActive   = timerWrap.dataset.active === '1';

        totalTime = TIMES[difficulty] !== undefined ? TIMES[difficulty] : 30;
        timeLeft  = totalTime;

        timerText.textContent = timeLeft;

        if (isActive) {
            startTimer();
        } else {
            timerText.textContent = '-';
            timerCircle.style.strokeDashoffset = CIRCUMFERENCE;
            timerWrap.classList.add('timer-paused');
        }
    }

    function startTimer() {
        if (timerInterval || timeLeft <= 0) return;
        updateTimerUI();
        timerInterval = setInterval(function() {
            timeLeft--;
            updateTimerUI();
            if (timeLeft <= 0) {
                stopTimer();
                onTimeout();
            }
        }, 1000);
    }

    function stopTimer() {
        if (!timerInterval) return;
        clearInterval(timerInterval);
        timerInterval = null;
    }

    function updateTimerUI() {
        if (!timerText || !timerCircle) return;
        timerText.textContent = timeLeft;

        var ratio  = timeLeft / totalTime;
        var offset = CIRCUMFERENCE * (1 - ratio);
        timerCircle.style.strokeDashoffset = offset;

        timerWrap.classList.remove('timer-warning', 'timer-danger');
        if (ratio <= 0.25) {
            timerWrap.classList.add('timer-danger');
        } else if (ratio <= 0.5) {
            timerWrap.classList.add('timer-warning');
        }
    }

    function onTimeout() {
        document.querySelectorAll('.choice-btn').forEach(function(b) {
            b.disabled = true;
            b.style.cursor = 'default';
        });
        if (timerWrap) timerWrap.classList.add('timer-danger');
        if (timerText) timerText.textContent = '0';

        setTimeout(function() {
            if (timeoutForm) timeoutForm.submit();
        }, 700);
    }

    // ── Card animations ───────────────────────────────────────
    var card = document.getElementById('questionCard');
    if (card) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(16px)';
        requestAnimationFrame(function() {
            card.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        });
    }

    var feedbackBox = document.getElementById('feedbackBox');
    if (feedbackBox) {
        feedbackBox.style.opacity = '0';
        feedbackBox.style.transform = 'translateY(12px)';
        requestAnimationFrame(function() {
            feedbackBox.style.transition = 'opacity 0.3s ease 0.15s, transform 0.3s ease 0.15s';
            feedbackBox.style.opacity = '1';
            feedbackBox.style.transform = 'translateY(0)';
        });

        playQuizSound(
            feedbackBox.classList.contains('feedback-correct')
                ? quizSounds.win
                : quizSounds.lose
        );
    } else if (card) {
        playQuizSound(quizSounds.question);
    }

    // ── Answer selection ──────────────────────────────────────
    window.selectAnswer = function (btn) {
        stopTimer();
        playQuizSound(quizSounds.finalAnswer);

        document.querySelectorAll('.choice-btn').forEach(function(b) {
            b.disabled = true;
            b.style.cursor = 'default';
        });

        btn.classList.add('selected');
        var input = document.getElementById('choiceInput');
        if (input) input.value = btn.dataset.choice;

        setTimeout(function() {
            var form = document.getElementById('answerForm');
            if (form) form.submit();
        }, 700);
    };

    window.confirmQuizQuit = function (event, link) {
        if (event) event.preventDefault();

        var targetUrl = link && link.href ? link.href : '../views/select_quiz.php';
        var wasTimerRunning = !!timerInterval;
        stopTimer();

        function leaveQuiz() {
            window.location.href = targetUrl;
        }

        function resumeQuiz() {
            if (wasTimerRunning && timeLeft > 0) startTimer();
        }

        if (typeof Swal === 'undefined') {
            if (window.confirm('Quit the quiz? Progress will be lost.')) {
                leaveQuiz();
            } else {
                resumeQuiz();
            }
            return false;
        }

        var isLightMode = document.body.classList.contains('light-mode');

        Swal.fire({
            icon: 'warning',
            title: 'Quit the quiz?',
            text: 'Your current progress will be lost.',
            background: isLightMode ? '#ffffff' : '#1a1830',
            color: isLightMode ? '#111827' : '#f1f0ff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#2563eb',
            confirmButtonText: 'Yes, quit',
            cancelButtonText: 'Keep playing',
            reverseButtons: true
        }).then(function(result) {
            if (result.isConfirmed) {
                leaveQuiz();
            } else {
                resumeQuiz();
            }
        });

        return false;
    };

})();
