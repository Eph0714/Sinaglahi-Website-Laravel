// Off-canvas sidebar toggle shared by the Admin and Artist dashboard layouts
// (see dashboard.css's max-width: 900px rules). Opens/closes #dashboardSidebar
// via the .sidebar-toggle button, closing again on backdrop click or when a
// nav link is followed.
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.querySelector('.sidebar-toggle');
        var sidebar = document.getElementById('dashboardSidebar');
        var backdrop = document.querySelector('[data-sidebar-backdrop]');
        if (!toggle || !sidebar || !backdrop) return;

        function close() {
            sidebar.classList.remove('is-open');
            backdrop.classList.remove('is-visible');
            toggle.setAttribute('aria-expanded', 'false');
        }
        function open() {
            sidebar.classList.add('is-open');
            backdrop.classList.add('is-visible');
            toggle.setAttribute('aria-expanded', 'true');
        }

        toggle.addEventListener('click', function () {
            sidebar.classList.contains('is-open') ? close() : open();
        });
        backdrop.addEventListener('click', close);
        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', close);
        });
    });
})();
