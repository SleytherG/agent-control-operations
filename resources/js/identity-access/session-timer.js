let expiresAt = null;
let timerInterval = null;
let refreshInFlight = false;

function getExpiresAt() {
    const meta = document.querySelector('meta[name="session-expires-at"]');
    return meta ? new Date(meta.content).getTime() : null;
}

function updateTimer() {
    if (!expiresAt) return;
    const remaining = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
    const el = document.getElementById('session-timer');
    if (el) {
        const m = Math.floor(remaining / 60);
        const s = remaining % 60;
        el.textContent = `${m}:${String(s).padStart(2, '0')}`;
    }

    const modal = document.getElementById('session-expiry-modal');
    if (remaining <= 30 && remaining > 0 && modal && modal.hidden) {
        showModal();
    }
    if (remaining <= 0) {
        cleanupAndLogin();
    }
}

function showModal() {
    const modal = document.getElementById('session-expiry-modal');
    if (!modal) return;
    modal.hidden = false;
    document.getElementById('continue-session')?.focus();
}

function hideModal() {
    const modal = document.getElementById('session-expiry-modal');
    if (modal) modal.hidden = true;
}

async function continueSession() {
    if (refreshInFlight) return;
    refreshInFlight = true;
    const continueBtn = document.getElementById('continue-session');
    if (continueBtn) continueBtn.disabled = true;

    const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    try {
        const res = await fetch('/auth/refresh', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        });

        if (res.ok) {
            const data = await res.json();
            expiresAt = new Date(data.expiresAt).getTime();
            hideModal();
            updateTimer();
        } else {
            cleanupAndLogin();
        }
    } catch {
        // keep modal open until timer zero
    } finally {
        refreshInFlight = false;
        if (continueBtn) continueBtn.disabled = false;
    }
}

function endSession() {
    const form = document.getElementById('logout-form');
    if (form) form.submit();
}

function cleanupAndLogin() {
    clearInterval(timerInterval);
    hideModal();
    window.location.href = '/login';
}

document.addEventListener('DOMContentLoaded', () => {
    expiresAt = getExpiresAt();
    if (!expiresAt) return;

    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            expiresAt = getExpiresAt();
            updateTimer();
        }
    });

    document.getElementById('continue-session')?.addEventListener('click', continueSession);
    document.getElementById('end-session')?.addEventListener('click', endSession);
});
