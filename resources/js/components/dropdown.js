document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-dropdown]').forEach(function(dropdown) {
        var trigger = dropdown.querySelector('.dropdown-trigger');
        var menu = dropdown.querySelector('.dropdown-menu');

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = !menu.hasAttribute('hidden');
            // Close all others
            document.querySelectorAll('.dropdown-menu').forEach(function(m) { m.setAttribute('hidden', ''); });
            document.querySelectorAll('.dropdown-trigger').forEach(function(t) { t.setAttribute('aria-expanded', 'false'); });
            if (!isOpen) { menu.removeAttribute('hidden'); trigger.setAttribute('aria-expanded', 'true'); }
        });
    });

    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(function(m) { m.setAttribute('hidden', ''); });
        document.querySelectorAll('.dropdown-trigger').forEach(function(t) { t.setAttribute('aria-expanded', 'false'); });
    });
});
