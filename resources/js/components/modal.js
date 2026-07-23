function initModal(modalId) {
    var overlay = document.getElementById(modalId + '-overlay');
    if (!overlay) return;

    function open() { overlay.removeAttribute('hidden'); trapFocus(overlay); }
    function close() { overlay.setAttribute('hidden', ''); }

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) close();
    });

    var closeBtn = overlay.querySelector('[data-modal-close]');
    if (closeBtn) closeBtn.addEventListener('click', close);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !overlay.hasAttribute('hidden')) close();
    });

    return { open: open, close: close };
}

function trapFocus(element) {
    var focusable = element.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (focusable.length === 0) return;
    var first = focusable[0];
    var last = focusable[focusable.length - 1];
    first.focus();

    element.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initModal('modal');
});

export { initModal };
