function showToast(title, message, variant, duration) {
    variant = variant || 'info';
    duration = duration || 4000;

    var container = document.getElementById('toast-container');
    if (!container) return;

    var icons = { success: '&#x2714;', error: '&#x2716;', warning: '&#x26A0;', info: '&#x2139;' };
    var toast = document.createElement('div');
    toast.className = 'toast toast--' + variant;
    toast.innerHTML =
        '<span class="toast-icon">' + (icons[variant] || '') + '</span>' +
        '<div class="toast-body">' +
        '<div class="toast-title">' + title + '</div>' +
        (message ? '<div class="toast-message">' + message + '</div>' : '') +
        '</div>' +
        '<button class="toast-close" aria-label="Cerrar">&times;</button>';

    toast.querySelector('.toast-close').addEventListener('click', function() { dismissToast(toast); });

    container.appendChild(toast);

    var timer = setTimeout(function() { dismissToast(toast); }, duration);
    toast._timer = timer;
}

function dismissToast(toast) {
    clearTimeout(toast._timer);
    toast.classList.add('toast-dismissing');
    setTimeout(function() {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
    }, 250);
}

export { showToast, dismissToast };
