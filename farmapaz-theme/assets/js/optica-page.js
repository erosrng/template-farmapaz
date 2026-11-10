/**
 * Óptica Farmapaz — Hero 3D, tilt suave y filtros de marca.
 * Solo se carga en la plantilla page-optica.php.
 */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    /* ============================================================
       HERO — carrusel 3D
       ============================================================ */
    var stage = document.querySelector('[data-op-stage]');
    if (stage) {
        var cards = Array.prototype.slice.call(stage.querySelectorAll('[data-op-card]'));
        var dots = Array.prototype.slice.call(stage.querySelectorAll('[data-op-dot]'));
        var prevBtn = stage.querySelector('[data-op-prev]');
        var nextBtn = stage.querySelector('[data-op-next]');
        var tiltEl = stage.querySelector('[data-op-tilt]');
        var active = 0;
        var timer = null;
        var paused = false;
        var pageHidden = false;

        function renderHero() {
            var count = cards.length;
            if (!count) return;

            var half = Math.floor(count / 2);
            cards.forEach(function (card, index) {
                var offset = index - active;
                if (offset > half) offset -= count;
                if (offset < -half) offset += count;

                var position = 'active';
                if (offset === 1) position = 'next';
                else if (offset === -1) position = 'prev';
                else if (offset === 2) position = 'next2';
                else if (offset === -2) position = 'prev2';

                card.setAttribute('data-pos', position);
            });

            dots.forEach(function (dot, index) {
                dot.classList.toggle('is-active', index === active);
                dot.setAttribute('aria-current', index === active ? 'true' : 'false');
            });
        }

        function goTo(index) {
            if (!cards.length) return;
            active = (index + cards.length) % cards.length;
            renderHero();
        }

        function stopAuto() {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        }

        function startAuto() {
            stopAuto();
            if (reduceMotion || pageHidden || cards.length < 2) return;
            timer = window.setInterval(function () {
                if (!paused) goTo(active + 1);
            }, 5400);
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                goTo(active - 1);
                startAuto();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                goTo(active + 1);
                startAuto();
            });
        }

        dots.forEach(function (dot, index) {
            dot.addEventListener('click', function () {
                goTo(index);
                startAuto();
            });
        });

        if (finePointer) {
            stage.addEventListener('mouseenter', function () {
                paused = true;
            });

            stage.addEventListener('mouseleave', function () {
                paused = false;
                resetTilt(tiltEl);
            });
        }

        stage.addEventListener('focusin', function () {
            paused = true;
        });

        stage.addEventListener('focusout', function (event) {
            if (!stage.contains(event.relatedTarget)) {
                paused = false;
            }
        });

        document.addEventListener('visibilitychange', function () {
            pageHidden = document.hidden;
            if (pageHidden) stopAuto();
            else startAuto();
        });

        if (finePointer && tiltEl && !reduceMotion) {
            bindTilt(stage, tiltEl, 7);
        }

        renderHero();
        startAuto();
    }

    /* ============================================================
       TILT — helper reutilizable
       ============================================================ */
    function bindTilt(zone, target, max) {
        var raf = null;
        var rect = null;

        function update(clientX, clientY) {
            if (!rect) return;
            var x = (clientX - rect.left) / rect.width - 0.5;
            var y = (clientY - rect.top) / rect.height - 0.5;
            var ry = clamp(x * max * 2, -max, max);
            var rx = clamp(-y * max * 2, -max, max);
            target.style.setProperty('--op-ry', ry.toFixed(2) + 'deg');
            target.style.setProperty('--op-rx', rx.toFixed(2) + 'deg');
            target.style.transform = 'perspective(1400px) rotateX(' + rx.toFixed(2) + 'deg) rotateY(' + ry.toFixed(2) + 'deg)';
        }

        zone.addEventListener('pointerenter', function () {
            rect = zone.getBoundingClientRect();
        });

        zone.addEventListener('pointermove', function (event) {
            if (!finePointer || reduceMotion) return;
            var cx = event.clientX;
            var cy = event.clientY;
            if (raf) return;
            raf = window.requestAnimationFrame(function () {
                raf = null;
                update(cx, cy);
            });
        });

        zone.addEventListener('pointerleave', function () {
            rect = null;
            resetTilt(target);
        });
    }

    function resetTilt(target) {
        if (!target) return;
        target.style.setProperty('--op-rx', '0deg');
        target.style.setProperty('--op-ry', '0deg');
        target.style.transform = '';
    }

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    /* ============================================================
       TARJETAS DE PRODUCTO — tilt 3D sutil
       ============================================================ */
    if (finePointer && !reduceMotion) {
        document.querySelectorAll('[data-op-product]').forEach(function (card) {
            var raf = null;
            var rect = null;

            card.addEventListener('pointerenter', function () {
                rect = card.getBoundingClientRect();
            });

            card.addEventListener('pointermove', function (event) {
                var cx = event.clientX;
                var cy = event.clientY;
                if (raf) return;
                raf = window.requestAnimationFrame(function () {
                    raf = null;
                    if (!rect) return;
                    var x = (cx - rect.left) / rect.width - 0.5;
                    var y = (cy - rect.top) / rect.height - 0.5;
                    card.style.setProperty('--op-ry', (x * 6).toFixed(2) + 'deg');
                    card.style.setProperty('--op-rx', (-y * 5).toFixed(2) + 'deg');
                });
            });

            card.addEventListener('pointerleave', function () {
                rect = null;
                card.style.setProperty('--op-rx', '0deg');
                card.style.setProperty('--op-ry', '0deg');
            });
        });
    }

    /* ============================================================
       CARRUSELES DE LA PORTADA (destacadas / recién llegadas)
       ============================================================ */
    document.querySelectorAll('[data-op-rail]').forEach(function (rail) {
        var track = rail.querySelector('[data-op-rail-track]');
        var prev = rail.querySelector('[data-op-rail-prev]');
        var next = rail.querySelector('[data-op-rail-next]');
        if (!track || !prev || !next) return;

        function step() {
            var first = track.querySelector('.op-rail__item');
            var gap = 16;
            if (!first) return 250;
            var styles = window.getComputedStyle(track);
            gap = parseFloat(styles.columnGap || styles.gap || 16) || 16;
            return first.getBoundingClientRect().width + gap;
        }

        function update() {
            var maxScroll = track.scrollWidth - track.clientWidth;
            var overflowing = maxScroll > 8;
            prev.style.display = overflowing ? '' : 'none';
            next.style.display = overflowing ? '' : 'none';
            prev.disabled = track.scrollLeft <= 4;
            next.disabled = track.scrollLeft >= maxScroll - 4;
        }

        prev.addEventListener('click', function () {
            track.scrollBy({ left: -step(), behavior: reduceMotion ? 'auto' : 'smooth' });
        });

        next.addEventListener('click', function () {
            track.scrollBy({ left: step(), behavior: reduceMotion ? 'auto' : 'smooth' });
        });

        track.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    });

})();
