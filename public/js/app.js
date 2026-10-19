(function () {
    'use strict';

    var sidebar          = document.getElementById('sidebar');
    var appMain          = document.getElementById('app-main');
    var toggleBtn        = document.getElementById('sidebar-toggle');
    var sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
    var overlay          = document.getElementById('sidebar-overlay');
    var COLLAPSED_KEY    = 'hris_sidebar_collapsed';
    var isCollapsed      = localStorage.getItem(COLLAPSED_KEY) === 'true';

    function applySidebarState() {
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            appMain.classList.add('sidebar-collapsed');
            if (toggleBtn) toggleBtn.classList.add('active');
        } else {
            sidebar.classList.remove('collapsed');
            appMain.classList.remove('sidebar-collapsed');
            if (toggleBtn) toggleBtn.classList.remove('active');
        }
    }

    function toggleSidebar() {
        isCollapsed = !isCollapsed;
        localStorage.setItem(COLLAPSED_KEY, isCollapsed);
        applySidebarState();
    }

    if (sidebar) {
        applySidebarState();
        if (toggleBtn)        toggleBtn.addEventListener('click', toggleSidebar);
        if (sidebarToggleBtn) sidebarToggleBtn.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', function () {
            isCollapsed = true;
            localStorage.setItem(COLLAPSED_KEY, true);
            applySidebarState();
            overlay.classList.remove('show');
        });
    }

    // Submenu
    document.querySelectorAll('.submenu-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            var parent = this.closest('.has-submenu');
            if (!parent) return;
            document.querySelectorAll('.has-submenu.open').forEach(function (el) {
                if (el !== parent) el.classList.remove('open');
            });
            parent.classList.toggle('open');
        });
    });

    // Dropdowns
    function setupDropdown(id) {
        var dropdown = document.getElementById(id);
        if (!dropdown) return;
        dropdown.querySelectorAll('button').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var isOpen = dropdown.classList.contains('open');
                document.querySelectorAll('.topbar-dropdown.open').forEach(function (d) { d.classList.remove('open'); });
                if (!isOpen) dropdown.classList.add('open');
            });
        });
    }
    setupDropdown('notif-dropdown');
    setupDropdown('user-dropdown');

    document.addEventListener('click', function () {
        document.querySelectorAll('.topbar-dropdown.open').forEach(function (d) { d.classList.remove('open'); });
    });
    document.querySelectorAll('.dropdown-panel').forEach(function (p) {
        p.addEventListener('click', function (e) { e.stopPropagation(); });
    });

    // Ctrl+K search
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            var s = document.getElementById('globalSearch');
            if (s) s.focus();
        }
        if (e.key === 'Escape') {
            document.querySelectorAll('.topbar-dropdown.open').forEach(function (d) { d.classList.remove('open'); });
        }
    });

    // Scroll to top
    var scrollBtn     = document.getElementById('scroll-to-top');
    var contentWrap   = document.getElementById('content-wrapper');
    if (contentWrap && scrollBtn) {
        contentWrap.addEventListener('scroll', function () {
            scrollBtn.classList.toggle('show', this.scrollTop > 300);
        });
        scrollBtn.addEventListener('click', function () {
            contentWrap.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Tooltip data-attribute
    document.querySelectorAll('.nav-link').forEach(function (link) {
        var text = link.querySelector('.nav-text');
        if (text) link.setAttribute('data-tooltip', text.textContent.trim());
    });

    // ==========================================
    // AUTO FORMAT RUPIAH / CURRENCY INPUTS
    // ==========================================
    window.formatRupiahString = function(value) {
        if (!value && value !== 0) return '';
        var clean = value.toString().replace(/[^\d]/g, '');
        if (!clean) return '';
        return clean.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    window.cleanRupiahString = function(value) {
        if (!value && value !== 0) return '0';
        return value.toString().replace(/\./g, '');
    };

    function attachCurrencyMask(input) {
        if (!input || input.dataset.masked === 'true') return;
        input.dataset.masked = 'true';

        // Format initial value if exists
        if (input.value) {
            input.value = window.formatRupiahString(input.value);
        }

        input.addEventListener('input', function() {
            var clean = this.value.replace(/[^\d]/g, '');
            if (!clean) {
                this.value = '';
                return;
            }
            this.value = window.formatRupiahString(clean);
        });
    }

    // Auto-init existing currency inputs
    document.querySelectorAll('.currency-input').forEach(attachCurrencyMask);

    // Auto-attach when dynamic input gets focus
    document.addEventListener('focusin', function(e) {
        if (e.target && e.target.classList && e.target.classList.contains('currency-input')) {
            attachCurrencyMask(e.target);
        }
    });

    // Auto-unmask all currency inputs before form submission
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (form && form.querySelectorAll) {
            form.querySelectorAll('.currency-input').forEach(function(input) {
                input.value = window.cleanRupiahString(input.value);
            });
        }
    }, true);

})();
