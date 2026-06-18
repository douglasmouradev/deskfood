/**
 * Menu mobile genérico (sem Alpine) — layouts público, cliente, operador, admin.
 */
(function () {
    'use strict';

    function findNavShell(button, navPanel) {
        var node = button;
        while (node && node !== document.body) {
            if (node.contains(navPanel)) {
                return node;
            }
            node = node.parentElement;
        }
        return document.body;
    }

    function initDfMobileNav() {
        document.querySelectorAll('[data-df-nav-toggle]').forEach(function (btn) {
            if (btn.dataset.dfNavBound === '1') {
                return;
            }
            btn.dataset.dfNavBound = '1';

            var panelId = btn.getAttribute('aria-controls');
            var panel = panelId ? document.getElementById(panelId) : null;
            if (!panel) {
                return;
            }

            var shell = findNavShell(btn, panel);
            var isOpen = false;

            function setOpen(open) {
                isOpen = open;
                panel.hidden = !open;
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                setOpen(!isOpen);
            });

            panel.querySelectorAll('a[href]').forEach(function (link) {
                link.addEventListener('click', function () {
                    setOpen(false);
                });
            });

            document.addEventListener('click', function (e) {
                if (!isOpen) {
                    return;
                }
                if (shell.contains(e.target)) {
                    return;
                }
                setOpen(false);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    setOpen(false);
                }
            });

            setOpen(false);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDfMobileNav);
    } else {
        initDfMobileNav();
    }
})();
