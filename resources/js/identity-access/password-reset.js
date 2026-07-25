function initPasswordReset() {
    const root = document.querySelector('[data-password-reset]');
    if (!root) return;

    const openButton = root.querySelector('[data-password-reset-open]');
    const overlay = root.querySelector('[data-password-reset-modal]');
    const form = root.querySelector('[data-password-reset-form]');
    const closeButtons = root.querySelectorAll('[data-password-reset-close]');
    const formPanel = root.querySelector('[data-password-reset-form-panel]');
    const resultPanel = root.querySelector('[data-password-reset-result]');
    const secretNode = root.querySelector('[data-password-reset-secret]');
    const errorNode = root.querySelector('[data-password-reset-error]');
    const copyButton = root.querySelector('[data-password-reset-copy]');
    const announcement = root.querySelector('[data-password-reset-announcement]');

    const clearSecret = () => {
        if (secretNode) secretNode.textContent = '';
        if (resultPanel) resultPanel.hidden = true;
        if (formPanel) formPanel.hidden = false;
        if (form) form.reset();
        if (errorNode) errorNode.textContent = '';
        if (announcement) announcement.textContent = '';
    };

    const close = () => {
        clearSecret();
        overlay.hidden = true;
        openButton?.focus();
    };

    openButton?.addEventListener('click', () => {
        clearSecret();
        overlay.hidden = false;
        overlay.querySelector('input')?.focus();
    });
    closeButtons.forEach((button) => button.addEventListener('click', close));
    overlay?.addEventListener('click', (event) => {
        if (event.target === overlay) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && overlay && !overlay.hidden) close();
    });

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorNode.textContent = '';
        const submit = form.querySelector('[type="submit"]');
        submit.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                },
                body: new FormData(form),
                credentials: 'same-origin',
                cache: 'no-store',
            });
            const data = await response.json();

            if (!response.ok) {
                const validation = data.errors ? Object.values(data.errors).flat()[0] : null;
                throw new Error(validation || data.message || 'No se pudo restablecer la contraseña.');
            }

            secretNode.textContent = data.temporaryPassword;
            root.querySelector('[data-password-reset-expiry]').textContent = data.expiresAt;
            root.querySelector('[data-password-reset-warning]').textContent = data.deliveryWarning;
            formPanel.hidden = true;
            resultPanel.hidden = false;
            copyButton?.focus();
        } catch (error) {
            errorNode.textContent = error.message;
        } finally {
            submit.disabled = false;
        }
    });

    copyButton?.addEventListener('click', async () => {
        const secret = secretNode.textContent;
        if (!secret) return;
        await navigator.clipboard.writeText(secret);
        announcement.textContent = 'Contraseña temporal copiada. Compártala solo por un canal privado.';
    });
}

document.addEventListener('DOMContentLoaded', initPasswordReset);

export { initPasswordReset };
