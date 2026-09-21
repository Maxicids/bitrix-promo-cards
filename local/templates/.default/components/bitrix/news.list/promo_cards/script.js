/**
 * Шаблон promo_cards: появление карточек при прокрутке, обратный отсчёт и 3D-наклон.
 *
 * Отсчёт заодно исправляет «застывший» в кеше компонента IS_HOT: если акция вошла
 * в последние 3 суток уже после построения кеша, бейдж «Заканчивается!» включится здесь.
 */
(function () {
    'use strict';

    var HOT_PERIOD = 3 * 86400;
    var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    function pad(n) {
        return n < 10 ? '0' + n : String(n);
    }

    function formatLeft(seconds, labels) {
        var days = Math.floor(seconds / 86400);
        var h = Math.floor(seconds % 86400 / 3600);
        var m = Math.floor(seconds % 3600 / 60);
        var s = Math.floor(seconds % 60);

        return labels.left + ' ' + (days > 0 ? days + ' ' + labels.days + ' ' : '') + pad(h) + ':' + pad(m) + ':' + pad(s);
    }

    function initTimers(root) {
        var labels = {
            left: root.getAttribute('data-label-left') || '',
            days: root.getAttribute('data-label-days') || '',
            ended: root.getAttribute('data-label-ended') || ''
        };
        var cards = [].slice.call(root.querySelectorAll('[data-active-to]'));
        if (!cards.length) {
            return;
        }

        function tick() {
            var now = Date.now() / 1000;

            cards.forEach(function (card) {
                var left = parseInt(card.getAttribute('data-active-to'), 10) - now;
                var timer = card.querySelector('[data-timer]');
                var badge = card.querySelector('[data-hot-badge]');
                var isHot = left > 0 && left < HOT_PERIOD;

                card.classList.toggle('promo-card--hot', isHot);
                card.classList.toggle('promo-card--ended', left <= 0);
                if (badge) {
                    badge.hidden = !isHot;
                }
                if (timer && left <= 0) {
                    timer.textContent = labels.ended;
                } else if (timer && isHot) {
                    timer.textContent = formatLeft(left, labels);
                }
            });
        }

        tick();
        setInterval(tick, 1000);
    }

    function initReveal(root) {
        var cards = [].slice.call(root.querySelectorAll('.promo-card'));
        if (reducedMotion || !('IntersectionObserver' in window)) {
            return;
        }

        root.classList.add('promo-cards--animated');
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {threshold: 0.15});

        cards.forEach(function (card) {
            observer.observe(card);
        });
    }

    function initTilt(root) {
        if (reducedMotion || !finePointer) {
            return;
        }

        [].forEach.call(root.querySelectorAll('.promo-card'), function (card) {
            card.addEventListener('pointermove', function (event) {
                var rect = card.getBoundingClientRect();
                var x = (event.clientX - rect.left) / rect.width;
                var y = (event.clientY - rect.top) / rect.height;

                card.style.setProperty('--rx', ((0.5 - y) * 8).toFixed(2) + 'deg');
                card.style.setProperty('--ry', ((x - 0.5) * 10).toFixed(2) + 'deg');
                card.style.setProperty('--mx', (x * 100).toFixed(1) + '%');
                card.style.setProperty('--my', (y * 100).toFixed(1) + '%');
            });
            card.addEventListener('pointerleave', function () {
                card.style.removeProperty('--rx');
                card.style.removeProperty('--ry');
            });
        });
    }

    function init() {
        [].forEach.call(document.querySelectorAll('[data-promo-cards]'), function (root) {
            if (root.getAttribute('data-promo-ready')) {
                return;
            }
            root.setAttribute('data-promo-ready', 'Y');
            initTimers(root);
            initReveal(root);
            initTilt(root);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
