(function () {
    'use strict';

    var HOT_PERIOD = 3 * 86400;
    var HERO_TRANSITION = 'promo-hero';

    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    function toArray(list) {
        return Array.prototype.slice.call(list);
    }

    function pad(n) {
        return n < 10 ? '0' + n : String(n);
    }

    function formatLeft(seconds, labels) {
        var days = Math.floor(seconds / 86400);
        var time = [
            Math.floor(seconds % 86400 / 3600),
            Math.floor(seconds % 3600 / 60),
            Math.floor(seconds % 60)
        ].map(pad).join(':');

        return labels.left + ' ' + (days > 0 ? days + ' ' + labels.days + ' ' : '') + time;
    }

    function withViewTransition(update) {
        if (reducedMotion || !document.startViewTransition) {
            update();
            return Promise.resolve();
        }

        return document.startViewTransition(update).finished;
    }

    /**
     * Пересчитывает IS_HOT на клиенте: значение из result_modifier.php живёт в кеше компонента
     * и к моменту показа может устареть.
     */
    function initTimers(root, cards) {
        var labels = {
            left: root.dataset.labelLeft,
            days: root.dataset.labelDays,
            ended: root.dataset.labelEnded
        };
        var timed = cards.filter(function (card) {
            return card.dataset.activeTo;
        });

        function tick() {
            var now = Date.now() / 1000;

            timed.forEach(function (card) {
                var left = Number(card.dataset.activeTo) - now;
                var isHot = left > 0 && left < HOT_PERIOD;
                var badge = card.querySelector('[data-hot-badge]');
                var timer = card.querySelector('[data-timer]');

                card.classList.toggle('promo-card--hot', isHot);
                card.classList.toggle('promo-card--ended', left <= 0);
                badge.hidden = !isHot;

                if (left <= 0) {
                    timer.textContent = labels.ended;
                } else if (isHot) {
                    timer.textContent = formatLeft(left, labels);
                }
            });

            root.dispatchEvent(new CustomEvent('promo:tick'));
        }

        if (timed.length) {
            tick();
            setInterval(tick, 1000);
        }
    }

    function initReveal(root, cards) {
        if (reducedMotion || !('IntersectionObserver' in window)) {
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {threshold: 0.15});

        root.classList.add('promo-cards--animated');
        cards.forEach(function (card) {
            observer.observe(card);
        });
    }

    function initTilt(cards) {
        if (reducedMotion || !finePointer) {
            return;
        }

        cards.forEach(function (card) {
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

    function initModal(root, cards) {
        var dialog = root.querySelector('[data-promo-modal]');
        if (!dialog || typeof dialog.showModal !== 'function') {
            return;
        }

        var ui = {
            panel: dialog.querySelector('[data-modal-panel]'),
            content: dialog.querySelector('[data-modal-content]'),
            image: dialog.querySelector('[data-modal-image]'),
            badges: dialog.querySelector('[data-modal-badges]'),
            discount: dialog.querySelector('[data-modal-discount]'),
            title: dialog.querySelector('[data-modal-title]'),
            timer: dialog.querySelector('[data-modal-timer]'),
            text: dialog.querySelector('[data-modal-text]'),
            cta: dialog.querySelector('[data-modal-cta]')
        };
        var current = 0;

        function cardImage(card) {
            return card.querySelector('.promo-card__image');
        }

        function syncTimer() {
            var timer = cards[current].querySelector('.promo-card__timer');

            ui.timer.hidden = !timer;
            ui.timer.innerHTML = timer ? timer.innerHTML : '';
            ui.panel.classList.toggle('promo-modal__panel--hot', cards[current].classList.contains('promo-card--hot'));
        }

        function render(index) {
            var card = cards[index];
            var image = cardImage(card);
            var discount = card.querySelector('.promo-card__discount');
            var url = card.dataset.detailUrl;

            current = index;
            ui.image.src = card.dataset.detailPicture || (image && image.currentSrc) || '';
            ui.image.alt = image ? image.alt : '';
            ui.image.hidden = !ui.image.getAttribute('src');
            ui.badges.innerHTML = card.querySelector('.promo-card__badges').innerHTML;
            ui.discount.innerHTML = discount ? discount.outerHTML : '';
            ui.title.textContent = card.querySelector('.promo-card__title').textContent.trim();
            ui.text.innerHTML = card.querySelector('[data-detail-text]').innerHTML;
            ui.cta.hidden = !url;
            ui.cta.href = url || '#';
            ui.content.scrollTop = 0;
            syncTimer();
        }

        function morph(from, to, update) {
            if (from) {
                from.style.viewTransitionName = HERO_TRANSITION;
            }

            withViewTransition(function () {
                if (from) {
                    from.style.viewTransitionName = '';
                }
                update();
                if (to) {
                    to.style.viewTransitionName = HERO_TRANSITION;
                }
            }).finally(function () {
                if (to) {
                    to.style.viewTransitionName = '';
                }
            });
        }

        function open(index) {
            render(index);
            morph(cardImage(cards[index]), ui.image, function () {
                dialog.showModal();
                document.documentElement.classList.add('promo-modal-open');
            });
        }

        function close() {
            morph(ui.image, cardImage(cards[current]), function () {
                dialog.close();
            });
        }

        function step(delta) {
            var next = (current + delta + cards.length) % cards.length;

            withViewTransition(function () {
                render(next);
            });
        }

        cards.forEach(function (card, index) {
            var link = card.querySelector('.promo-card__link');

            if (!link) {
                card.tabIndex = 0;
                card.setAttribute('role', 'button');
                card.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        open(index);
                    }
                });
            }

            card.addEventListener('click', function (event) {
                var newTab = event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey;
                if (!newTab) {
                    event.preventDefault();
                    open(index);
                }
            });
        });

        dialog.addEventListener('click', function (event) {
            if (event.target === dialog || event.target.closest('[data-modal-close]')) {
                close();
            } else if (event.target.closest('[data-modal-prev]')) {
                step(-1);
            } else if (event.target.closest('[data-modal-next]')) {
                step(1);
            }
        });

        dialog.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowLeft') {
                step(-1);
            } else if (event.key === 'ArrowRight') {
                step(1);
            }
        });

        dialog.addEventListener('cancel', function (event) {
            event.preventDefault();
            close();
        });

        dialog.addEventListener('close', function () {
            document.documentElement.classList.remove('promo-modal-open');
        });

        root.addEventListener('promo:tick', function () {
            if (dialog.open) {
                syncTimer();
            }
        });
    }

    function init(root) {
        var cards = toArray(root.querySelectorAll('[data-promo-card]'));

        initTimers(root, cards);
        initReveal(root, cards);
        initTilt(cards);
        initModal(root, cards);
    }

    function initAll() {
        toArray(document.querySelectorAll('[data-promo-cards]:not([data-promo-ready])')).forEach(function (root) {
            root.setAttribute('data-promo-ready', '');
            init(root);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
