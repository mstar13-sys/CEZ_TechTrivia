// ── Landing Page JS ───────────────────────────────────────────
// Fetches the leaderboard from the API and renders it on the page.

document.addEventListener('DOMContentLoaded', () => {

    loadLeaderboard();

    async function loadLeaderboard() {
        const container = document.getElementById('leaderboardBody');
        if (!container) return;

        container.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#888;padding:24px">Loading…</td></tr>';

        try {
            const res  = await fetch('index.php?action=leaderboard&limit=10');
            const data = await res.json();

            if (!data || data.length === 0) {
                container.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#888;padding:24px">No players yet. Be the first!</td></tr>';
                return;
            }

            const medals = ['🥇','🥈','🥉'];

            container.innerHTML = data.map((p, i) => {
                const username = String(p.username || 'Player');
                const avatar = username.charAt(0).toUpperCase() || '?';
                return `
                <tr class="lb-row ${i < 3 ? 'lb-top' : ''}">
                    <td class="lb-rank">${medals[i] || (i + 1)}</td>
                    <td class="lb-user">
                        <span class="lb-avatar">${escHtml(avatar)}</span>
                        ${escHtml(username)}
                    </td>
                    <td class="lb-xp">${parseInt(p.total_xp).toLocaleString()} XP</td>
                    <td class="lb-games">${parseInt(p.games_played)} games</td>
                </tr>
            `;
            }).join('');

        } catch (err) {
            container.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#888;padding:24px">Could not load leaderboard.</td></tr>';
        }
    }

    function escHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }
});
