// ── Quiz Page JS ──────────────────────────────────────────────
// Handles answer selection, animations, and auto-submit.

(function () {
    'use strict';

    // Animate question card in on load
    const card = document.getElementById('questionCard');
    if (card) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(16px)';
        requestAnimationFrame(() => {
            card.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        });
    }

    // Animate feedback box in
    const feedbackBox = document.getElementById('feedbackBox');
    if (feedbackBox) {
        feedbackBox.style.opacity = '0';
        feedbackBox.style.transform = 'translateY(12px)';
        requestAnimationFrame(() => {
            feedbackBox.style.transition = 'opacity 0.3s ease 0.15s, transform 0.3s ease 0.15s';
            feedbackBox.style.opacity = '1';
            feedbackBox.style.transform = 'translateY(0)';
        });
    }

    /**
     * Called when a choice button is clicked.
     * Marks the button, fills the hidden input, and submits.
     */
    window.selectAnswer = function (btn) {
        // Prevent double-click
        const allBtns = document.querySelectorAll('.choice-btn');
        allBtns.forEach(b => { b.disabled = true; b.style.cursor = 'default'; });

        btn.classList.add('selected');

        const input = document.getElementById('choiceInput');
        if (input) input.value = btn.dataset.choice;

        // Short delay for visual feedback before submit
        setTimeout(() => {
            const form = document.getElementById('answerForm');
            if (form) form.submit();
        }, 250);
    };

})();
