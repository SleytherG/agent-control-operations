document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('global-confirm-modal');
    if (!modal) return;

    let pendingSubmitter = null;

    document.getElementById('global-confirm-yes').addEventListener('click', () => {
        if (pendingSubmitter) {
            const form = pendingSubmitter.closest('form');
            if (form) {
                form.dataset.confirmBypass = '1';
                pendingSubmitter.click();
            }
        }
        closeGlobalConfirm();
    });

    document.getElementById('global-confirm-no').addEventListener('click', closeGlobalConfirm);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeGlobalConfirm();
    });

    function closeGlobalConfirm() {
        modal.style.display = 'none';
        pendingSubmitter = null;
    }

    document.body.addEventListener('submit', (e) => {
        const form = e.target.closest('form');
        if (!form) return;

        if (form.dataset.confirmBypass === '1') {
            delete form.dataset.confirmBypass;
            return;
        }

        const message = form.dataset.confirm;
        if (message && !form.dataset.confirmBypass) {
            e.preventDefault();
            e.stopImmediatePropagation();
            document.getElementById('global-confirm-message').textContent = message;
            modal.style.display = 'flex';
            pendingSubmitter = e.submitter || form.querySelector('button[type=submit]') || form;
        }
    }, true);
});
