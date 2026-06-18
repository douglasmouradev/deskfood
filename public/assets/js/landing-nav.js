/**
 * Menu mobile da landing (tema escuro).
 */
(function () {
    'use strict';

    function initLandingMobileNav() {
        var header = document.querySelector('.lp-header');
        var btn = document.querySelector('[data-lp-nav-toggle]');
        var panel = document.querySelector('[data-lp-nav-panel]');
        if (!header || !btn || !panel) {
            return;
        }

        var iconOpen = btn.querySelector('.lp-nav-toggle__open');
        var iconClose = btn.querySelector('.lp-nav-toggle__close');
        var sr = btn.querySelector('.sr-only');
        var isOpen = false;

        function setOpen(open) {
            isOpen = open;
            panel.hidden = !open;
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            header.classList.toggle('lp-header--nav-open', open);
            if (iconOpen && iconClose) {
                iconOpen.classList.toggle('hidden', open);
                iconClose.classList.toggle('hidden', !open);
            }
            if (sr) {
                sr.textContent = open ? 'Fechar menu' : 'Abrir menu';
            }
            document.body.classList.toggle('lp-nav-open', open);
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
            if (header.contains(e.target)) {
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
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLandingMobileNav);
    } else {
        initLandingMobileNav();
    }
})();
