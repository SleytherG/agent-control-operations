document.addEventListener('DOMContentLoaded', function() {
    var sidebar = document.getElementById('sidebar');
    var hamburgerBtn = document.getElementById('hamburger-btn');
    var mobileOverlay = document.getElementById('mobile-nav-overlay');
    var mobilePanel = document.getElementById('mobile-nav-panel');
    var mobileClose = document.getElementById('mobile-nav-close');
    var filterToggle = document.getElementById('filter-toggle');
    var filterOffcanvas = document.getElementById('filter-offcanvas');
    var filterClose = document.getElementById('filter-offcanvas-close');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('is-open');
        if (hamburgerBtn) hamburgerBtn.setAttribute('aria-expanded', 'true');
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('is-open');
        if (hamburgerBtn) hamburgerBtn.setAttribute('aria-expanded', 'false');
    }

    function openMobileNav() {
        if (mobileOverlay) mobileOverlay.classList.add('is-open');
        if (mobilePanel) mobilePanel.classList.add('is-open');
    }

    function closeMobileNav() {
        if (mobileOverlay) mobileOverlay.classList.remove('is-open');
        if (mobilePanel) mobilePanel.classList.remove('is-open');
    }

    function openFilterOffcanvas() {
        if (filterOffcanvas) filterOffcanvas.classList.add('is-open');
    }

    function closeFilterOffcanvas() {
        if (filterOffcanvas) filterOffcanvas.classList.remove('is-open');
    }

    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', function() {
            if (mobilePanel) {
                openMobileNav();
            } else {
                if (sidebar && sidebar.classList.contains('is-open')) closeSidebar();
                else openSidebar();
            }
        });
    }

    if (mobileClose) mobileClose.addEventListener('click', closeMobileNav);
    if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobileNav);
    if (filterToggle) filterToggle.addEventListener('click', openFilterOffcanvas);
    if (filterClose) filterClose.addEventListener('click', closeFilterOffcanvas);

    // Close mobile nav on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMobileNav();
            closeFilterOffcanvas();
        }
    });
});
