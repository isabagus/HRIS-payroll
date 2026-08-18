(function () {
    'use strict';

    // ── Sidebar Toggle ──────────────────────────────────────────
    const sidebar          = document.getElementById('sidebar');
    const appMain          = document.getElementById('app-main');
    const toggleBtn        = document.getElementById('sidebar-toggle');
    const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
    const overlay          = document.getElementById('sidebar-overlay');
    const COLLAPSED_KEY    = 'hris_sidebar_collapsed';

    let isCollapsed = localStorage.getItem(COLLAPSED_KEY) === 'true';

    function applySidebarState() {
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            appMain.classList.add('sidebar-collapsed');
            toggleBtn && toggleBtn.classList.add('active');
        } else {
            sidebar.classList.remove('collapsed');
            appMain.classList.remove('sidebar-collapsed');
            toggleBtn && toggleBtn.classList.remove('active');
        }
    }

    function toggleSidebar() {
        isCollapsed = !isCollapsed;
        localStorage.setItem(COLLAPSED_KEY, isCollapsed);
        applySidebarState();
    }

    applySidebarState();
    toggleBtn        && toggleBtn.addEventListener('click', toggleSidebar);
    sidebarToggleBtn && sidebarToggleBtn.addEventListener('click', toggleSidebar);
    overlay && overlay.addEventListener('click', function () {
        isCollapsed = true;
        localStorage.setItem(COLLAPSED_KEY, true);
        applySidebarState();
        overlay.classList.remove('show');
    });

    // ── Submenu Toggle ──────────────────────────────────────────
    document.querySelectorAll('.submenu-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const parent = this.closest('.has-submenu');
            if (!parent) return;
            document.querySelectorAll('.has-submenu.open').forEach(function (el) {
                if (el !== parent) el.classList.remove('open');
            });
            parent.classList.toggle('open');
        });
    });

    // ── Topbar Dropdowns ────────────────────────────────────────
    function setupDropdown(dropdownId) {
        const dropdown = document.getElementById(dropdownId);
        if (!dropdown) return;
        dropdown.querySelectorAll('button').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = dropdown.classList.contains('open');
                document.querySelectorAll('.topbar-dropdown.open').forEach(function (d) {
                    d.classList.remove('open');
                });
                if (!isOpen) dropdown.classList.add('open');
            });
        });
    }

    setupDropdown('notif-dropdown');
    setupDropdown('user-dropdown');

    document.addEventListener('click', function () {
        document.querySelectorAll('.topbar-dropdown.open').forEach(function (d) {
            d.classList.remove('open');
        });
    });
    document.querySelectorAll('.dropdown-panel').forEach(function (panel) {
        panel.addEventListener('click', function (e) { e.stopPropagation(); });
    });

    // ── Keyboard Shortcut: Ctrl+K ───────────────────────────────
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.getElementById('globalSearch');
            if (searchInput) searchInput.focus();
        }
        if (e.key === 'Escape') {
            document.querySelectorAll('.topbar-dropdown.open').forEach(function (d) {
                d.classList.remove('open');
            });
        }
    });

    // ── Scroll To Top ───────────────────────────────────────────
    const scrollBtn      = document.getElementById('scroll-to-top');
    const contentWrapper = document.getElementById('content-wrapper');

    if (contentWrapper && scrollBtn) {
        contentWrapper.addEventListener('scroll', function () {
            scrollBtn.classList.toggle('show', this.scrollTop > 300);
        });
        scrollBtn.addEventListener('click', function () {
            contentWrapper.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ── Tooltip data-attribute untuk nav-link collapsed ─────────
    document.querySelectorAll('.nav-link').forEach(function (link) {
        const text = link.querySelector('.nav-text');
        if (text) link.setAttribute('data-tooltip', text.textContent.trim());
    });

})();
