(function () {
    'use strict';

    var input = document.getElementById('menu-search');
    if (!input) {
        return;
    }

    var cards = document.querySelectorAll('[data-menu-name]');
    if (cards.length === 0) {
        return;
    }

    function filter() {
        var q = input.value.trim().toLowerCase();
        cards.forEach(function (card) {
            var name = card.getAttribute('data-menu-name') || '';
            var show = q === '' || name.indexOf(q) !== -1;
            card.classList.toggle('hidden', !show);
        });
    }

    input.addEventListener('input', filter);
    input.addEventListener('search', filter);
})();
