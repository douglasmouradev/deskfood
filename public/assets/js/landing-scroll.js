(function () {
    'use strict';

    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var scene = document.querySelector('.lp-scene');
    if (!scene || reduced) {
        document.querySelectorAll('.lp-panel').forEach(function (p) {
            p.classList.add('is-active', 'lp-panel--flat');
        });
        return;
    }

    var panels = Array.prototype.slice.call(document.querySelectorAll('.lp-panel'));
    var progressBar = document.querySelector('.lp-scroll-progress');
    var header = document.querySelector('.lp-header');
    var device = document.querySelector('.lp-device');
    var deviceWrap = document.querySelector('.lp-device-wrap');
    var ticking = false;

    function clamp(v, min, max) {
        return Math.min(max, Math.max(min, v));
    }

    function updatePanels() {
        var vh = window.innerHeight;
        var focus = vh * 0.42;

        panels.forEach(function (panel) {
            if (panel.classList.contains('lp-panel--flat')) {
                panel.style.transform = '';
                panel.style.opacity = '';
                return;
            }

            /* Só painéis marcados com --depth recebem 3D; demais ficam estáticos e legíveis */
            if (!panel.classList.contains('lp-panel--depth')) {
                panel.style.transform = '';
                panel.style.opacity = '';
                return;
            }

            var rect = panel.getBoundingClientRect();
            var center = rect.top + rect.height * 0.35;
            var dist = Math.abs(center - focus);
            var norm = clamp(1 - dist / (vh * 0.9), 0, 1);
            var rotateX = (1 - norm) * 10 - 3;
            var translateZ = norm * 36 - 18;
            var translateY = (1 - norm) * 18;
            var scale = 0.97 + norm * 0.03;

            panel.style.transform =
                'translate3d(0, ' + translateY.toFixed(2) + 'px, ' + translateZ.toFixed(2) + 'px) ' +
                'rotateX(' + rotateX.toFixed(2) + 'deg) scale(' + scale.toFixed(4) + ')';
            panel.style.opacity = '1';

            if (norm > 0.5) {
                panel.classList.add('is-active');
            } else {
                panel.classList.remove('is-active');
            }
        });
    }

    function updateProgress() {
        if (!progressBar) {
            return;
        }
        var scrollTop = window.scrollY;
        var docHeight = document.documentElement.scrollHeight - window.innerHeight;
        var p = docHeight > 0 ? scrollTop / docHeight : 0;
        progressBar.style.width = (p * 100).toFixed(2) + '%';
    }

    function updateHeader() {
        if (!header) {
            return;
        }
        if (window.scrollY > 48) {
            header.classList.add('is-scrolled');
        } else {
            header.classList.remove('is-scrolled');
        }
    }

    function updateDeviceParallax() {
        if (!device) {
            return;
        }
        var scrollY = window.scrollY;
        var parallaxY = scrollY * 0.06;
        var base = device.dataset.baseTransform || '';
        device.style.transform = base + ' translate3d(0, ' + (-parallaxY).toFixed(2) + 'px, 0)';
    }

    function onScroll() {
        if (!ticking) {
            window.requestAnimationFrame(function () {
                updatePanels();
                updateProgress();
                updateHeader();
                updateDeviceParallax();
                ticking = false;
            });
            ticking = true;
        }
    }

    if (deviceWrap && device) {
        var tiltX = 0;
        var tiltY = 0;

        deviceWrap.addEventListener('mousemove', function (e) {
            var rect = deviceWrap.getBoundingClientRect();
            var x = (e.clientX - rect.left) / rect.width - 0.5;
            var y = (e.clientY - rect.top) / rect.height - 0.5;
            tiltY = x * 10;
            tiltX = -y * 8;
            device.dataset.baseTransform =
                'rotateX(' + tiltX.toFixed(2) + 'deg) rotateY(' + tiltY.toFixed(2) + 'deg)';
            updateDeviceParallax();
        });

        deviceWrap.addEventListener('mouseleave', function () {
            tiltX = 0;
            tiltY = 0;
            device.dataset.baseTransform = 'rotateX(0deg) rotateY(0deg)';
            updateDeviceParallax();
        });
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    onScroll();
})();
