document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tabs').forEach(function(tabs) {
        var buttons = tabs.querySelectorAll('[data-tab]');
        buttons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                buttons.forEach(function(b) { b.classList.remove('tabs-tab--active'); b.setAttribute('aria-selected', 'false'); });
                btn.classList.add('tabs-tab--active');
                btn.setAttribute('aria-selected', 'true');

                var key = btn.getAttribute('data-tab');
                var panels = tabs.querySelectorAll('.tabs-panel');
                panels.forEach(function(p) {
                    p.hidden = p.getAttribute('data-tab-panel') !== key;
                });
            });
        });
    });
});
