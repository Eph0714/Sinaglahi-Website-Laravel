// Applies the standard action-button color + icon convention (redesign
// Sections 2-3) to every button/link across the Admin and Artist dashboards,
// based purely on each control's own visible label. This is what keeps
// "Delete" red, "Edit" blue, "Add" green, etc. consistent across every
// module without hand-editing dozens of views - add a new page, and any
// button using one of these standard words is styled automatically.
//
// Escape hatch: add data-no-auto-style to a button to leave it untouched.
(function () {
    'use strict';

    // Ordered most-specific-first; the first match wins. Colors follow the
    // fixed function->color convention: Add/Save/Approve = green, Edit/Manage
    // = blue, Delete/Reject/Disable = red, Cancel = gray, Refresh/Search/View
    // = cyan, Print = purple, Settings = orange/gray, Export = blue, Import = orange.
    var RULES = [
        { test: /force delete|delete permanently|permanently delete/, cls: 'btn-delete-solid', icon: 'bi-trash3-fill' },
        { test: /remove from featured/, cls: 'btn-cancel', icon: 'bi-star' },
        { test: /^(delete|remove|reject|deny|disable)\b/, cls: 'btn-delete', icon: 'bi-trash3' },
        { test: /^confirm reverse$/, cls: 'btn-delete-solid', icon: 'bi-arrow-counterclockwise' },
        { test: /^(edit|update)\b/, cls: 'btn-edit', icon: 'bi-pencil-square' },
        { test: /^manage\b/, cls: 'btn-edit', icon: 'bi-sliders' },
        { test: /^(settings|configure|configuration)\b/, cls: 'btn-settings', icon: 'bi-gear' },
        { test: /reverse( transaction)?$/, cls: 'btn-edit', icon: 'bi-arrow-counterclockwise' },
        { test: /^export\b|^download\b/, cls: 'btn-export', icon: 'bi-download' },
        { test: /^import\b/, cls: 'btn-import', icon: 'bi-upload' },
        { test: /^upload/, cls: 'btn-upload', icon: 'bi-upload' },
        { test: /^(view|details|preview)\b/, cls: 'btn-view', icon: 'bi-eye' },
        { test: /^(\+\s*)?(add|create|new)\b/, cls: 'btn-add', icon: 'bi-plus-lg' },
        { test: /^(approve|activate|enable|publish|verify|unhide|restore|unfeature\s*$)/, cls: 'btn-add', icon: 'bi-check-circle' },
        { test: /^feature$/, cls: 'btn-add', icon: 'bi-star-fill' },
        { test: /^(save|confirm|submit)\b/, cls: 'btn-save', icon: 'bi-check-lg' },
        { test: /^(cancel|close|discard)\b/, cls: 'btn-cancel', icon: 'bi-x-lg' },
        { test: /^(deactivate|unpublish|hide|archive)\b/, cls: 'btn-cancel', icon: 'bi-slash-circle' },
        { test: /^(refresh|reload)\b/, cls: 'btn-refresh', icon: 'bi-arrow-clockwise' },
        { test: /^print\b/, cls: 'btn-print', icon: 'bi-printer' },
        { test: /^(search|filter)\b/, cls: 'btn-search', icon: 'bi-search' },
        { test: /^log ?out$/, cls: '', icon: 'bi-box-arrow-right' }
    ];

    // Controls with their own established styling that must not be re-colored.
    var SKIP_SELECTOR = [
        '.dashboard-nav-link', '.filter-pill', '.page-link', '.btn-close',
        '.photo-manage-cover-btn', '.quick-action-card', '[data-no-auto-style]',
        '.dashboard-topbar .btn'
    ].join(',');

    function enhance(root) {
        var controls = root.querySelectorAll('.btn, button[type="submit"], button[type="button"]');
        controls.forEach(function (el) {
            if (el.matches(SKIP_SELECTOR) || el.closest(SKIP_SELECTOR)) return;
            if (el.dataset.autoStyled === 'true') return;

            var label = (el.textContent || '').trim().toLowerCase().replace(/\s+/g, ' ');
            if (!label) return;

            for (var i = 0; i < RULES.length; i++) {
                if (RULES[i].test.test(label)) {
                    if (RULES[i].cls) el.classList.add(RULES[i].cls);
                    if (RULES[i].icon && !el.querySelector('.bi')) {
                        var icon = document.createElement('i');
                        icon.className = 'bi ' + RULES[i].icon;
                        icon.setAttribute('aria-hidden', 'true');
                        el.insertBefore(icon, el.firstChild);
                    }
                    // Section 4: every action carries a descriptive tooltip,
                    // even when its label is already visible text.
                    if (!el.hasAttribute('title')) {
                        el.setAttribute('title', (el.textContent || '').trim());
                    }
                    el.dataset.autoStyled = 'true';
                    break;
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        enhance(document);

        // Re-run for content swapped in dynamically (e.g. a Bootstrap modal
        // whose body is populated just before it opens).
        document.addEventListener('shown.bs.modal', function (e) { enhance(e.target); });
    });
})();
