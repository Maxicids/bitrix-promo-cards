(function () {
    'use strict';

    var HOT_PERIOD = 3 * 86400;
    var IMAGE_DECODE_TIMEOUT = 300;

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

        var transition = document.startViewTransition(update);
        transition.ready.catch(function () {});

        return transition.finished;
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

    /**
     * Свайпы для мобильной шторки: вниз — закрыть (шторка тянется за пальцем), влево/вправо — листать.
     */
    function bindSwipe(panel, handlers) {
        var SWIPE_X = 60;
        var SWIPE_DOWN = 100;
        var gesture = null;

        function delta(touch) {
            return {x: touch.clientX - gesture.x, y: touch.clientY - gesture.y};
        }

        function canPullDown() {
            return gesture.axis === 'y' && gesture.fromTop;
        }

        panel.addEventListener('touchstart', function (event) {
            var touch = event.touches[0];
            gesture = {x: touch.clientX, y: touch.clientY, axis: null, fromTop: panel.scrollTop <= 0};
        }, {passive: true});

        panel.addEventListener('touchmove', function (event) {
            if (!gesture) {
                return;
            }

            var d = delta(event.touches[0]);
            if (!gesture.axis && Math.abs(d.x) + Math.abs(d.y) > 10) {
                gesture.axis = Math.abs(d.x) > Math.abs(d.y) ? 'x' : 'y';
            }
            if (canPullDown() && d.y > 0) {
                panel.classList.add('is-dragging');
                panel.style.translate = '0 ' + d.y + 'px';
            }
        }, {passive: true});

        panel.addEventListener('touchend', function (event) {
            if (!gesture) {
                return;
            }

            var d = delta(event.changedTouches[0]);

            panel.classList.remove('is-dragging');

            if (canPullDown() && d.y > SWIPE_DOWN) {
                handlers.down();
            } else {
                panel.style.translate = '';
                if (gesture.axis === 'x' && Math.abs(d.x) > SWIPE_X) {
                    (d.x < 0 ? handlers.left : handlers.right)();
                }
            }
            gesture = null;
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

        if (!reducedMotion && document.startViewTransition) {
            dialog.setAttribute('data-morph', '');
        }

        // Элементы, которые «перелетают» между карточкой и окном: [в карточке, в окне, имя перехода]
        function sharedElements(card) {
            return [
                [card.querySelector('.promo-card__picture'), ui.image.hidden ? null : ui.image, 'promo-hero'],
                [card.querySelector('.promo-card__badges'), ui.badges, 'promo-badges'],
                [card.querySelector('.promo-card__discount'), ui.discount.firstElementChild, 'promo-discount']
            ];
        }

        function nameElements(pairs, side, clear) {
            pairs.forEach(function (pair) {
                if (pair[side]) {
                    pair[side].style.viewTransitionName = clear ? '' : pair[2];
                }
            });
        }

        function imageReady() {
            if (ui.image.hidden || !ui.image.decode) {
                return Promise.resolve();
            }

            return Promise.race([
                ui.image.decode().catch(function () {}),
                new Promise(function (resolve) {
                    setTimeout(resolve, IMAGE_DECODE_TIMEOUT);
                })
            ]);
        }

        // Сначала показываем уже загруженное превью карточки, крупную картинку подменяем после загрузки
        function renderImage(card) {
            var preview = card.querySelector('img.promo-card__image');
            var previewSrc = preview ? preview.currentSrc : '';
            var fullSrc = card.dataset.detailPicture || '';

            ui.image.src = previewSrc || fullSrc;
            ui.image.alt = preview ? preview.alt : '';
            ui.image.hidden = !ui.image.getAttribute('src');

            if (fullSrc && fullSrc !== previewSrc) {
                var loader = new Image();
                loader.onload = function () {
                    if (cards[current] === card) {
                        ui.image.src = fullSrc;
                    }
                };
                loader.src = fullSrc;
            }
        }

        function syncTimer() {
            var timer = cards[current].querySelector('.promo-card__timer');

            ui.timer.hidden = !timer;
            ui.timer.innerHTML = timer ? timer.innerHTML : '';
            ui.panel.classList.toggle('promo-modal__panel--hot', cards[current].classList.contains('promo-card--hot'));
        }

        function render(index) {
            var card = cards[index];
            var discount = card.querySelector('.promo-card__discount');
            var url = card.dataset.detailUrl;

            current = index;
            renderImage(card);
            ui.badges.innerHTML = card.querySelector('.promo-card__badges').innerHTML;
            ui.discount.innerHTML = discount ? discount.outerHTML : '';
            ui.title.textContent = card.querySelector('.promo-card__title').textContent.trim();
            ui.text.innerHTML = card.querySelector('[data-detail-text]').innerHTML;
            ui.cta.hidden = !url;
            ui.cta.href = url || '#';
            ui.content.scrollTop = 0;
            syncTimer();
        }

        function morph(pairs, from, update) {
            var to = 1 - from;

            nameElements(pairs, from);
            withViewTransition(function () {
                nameElements(pairs, from, true);
                update();
                nameElements(pairs, to);
            }).finally(function () {
                nameElements(pairs, to, true);
            });
        }

        function open(index) {
            render(index);
            imageReady().then(function () {
                morph(sharedElements(cards[index]), 0, function () {
                    dialog.showModal();
                    document.documentElement.classList.add('promo-modal-open');
                });
            });
        }

        function close() {
            morph(sharedElements(cards[current]), 1, function () {
                dialog.close();
                ui.panel.style.translate = '';
                document.documentElement.classList.remove('promo-modal-open');
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

        bindSwipe(ui.panel, {
            left: function () {
                step(1);
            },
            right: function () {
                step(-1);
            },
            down: close
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
