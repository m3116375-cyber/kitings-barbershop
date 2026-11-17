/* ==========================================================
   ANIMASI SCROLL - Kiting's Barbershop
   Dipakai bersama public/css/animations.css

   Cara pakai di Blade:
     data-reveal="up|left|right|zoom|fade"   -> animasi saat elemen masuk layar
     data-reveal-delay="200"                 -> tunda 200 ms
     data-stagger  (di wadah/grid)           -> anak-anaknya muncul bergantian
   Class lama "reveal" tetap didukung (sama dengan data-reveal="up").
   ========================================================== */
(function () {
    'use strict';

    var doc = document.documentElement;
    var reduceMotion = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var STAGGER_STEP = 90;   // jeda antar kartu (ms)
    var STAGGER_MAX  = 6;    // batas jumlah kartu yang dijeda per baris/gelombang

    /* ------------------------------------------------------
       1. REVEAL SAAT SCROLL
       ------------------------------------------------------ */
    function initReveal() {

        // a) Wadah [data-stagger]: jadikan semua anaknya item reveal
        document.querySelectorAll('[data-stagger]').forEach(function (box) {
            var effect = box.getAttribute('data-stagger') || 'up';

            Array.prototype.forEach.call(box.children, function (child) {
                if (!child.hasAttribute('data-reveal')) {
                    child.setAttribute('data-reveal', effect);
                }
                child.setAttribute('data-staggered', '');
            });
        });

        var items = document.querySelectorAll('[data-reveal], .reveal');

        // b) Tanpa animasi: browser lama atau pengguna memilih "kurangi gerakan"
        if (reduceMotion || !('IntersectionObserver' in window)) {
            items.forEach(function (el) { el.classList.add('is-visible'); });
            return;
        }

        // c) Pantau elemen; saat masuk layar, tambahkan class .is-visible
        var observer = new IntersectionObserver(function (entries) {
            var n = 0;   // urutan elemen dalam satu gelombang

            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;

                var el    = entry.target;
                var base  = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
                var extra = el.hasAttribute('data-staggered')
                    ? Math.min(n++, STAGGER_MAX) * STAGGER_STEP
                    : 0;

                el.style.setProperty('--reveal-delay', (base + extra) + 'ms');
                el.classList.add('is-visible');
                observer.unobserve(el);   // animasi cukup sekali
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -8% 0px'  // mulai sedikit sebelum benar-benar terlihat
        });

        items.forEach(function (el) { observer.observe(el); });
    }

    /* ------------------------------------------------------
       2. NAVBAR + SCROLL PROGRESS
       ------------------------------------------------------ */
    function initScrollEffects() {
        var header = document.querySelector('[data-site-header]');

        var bar = document.createElement('div');
        bar.className = 'scroll-progress';
        bar.setAttribute('aria-hidden', 'true');
        document.body.appendChild(bar);

        var ticking = false;

        function update() {
            var y   = window.pageYOffset || doc.scrollTop || 0;
            var max = doc.scrollHeight - window.innerHeight;

            bar.style.transform = 'scaleX(' + (max > 0 ? Math.min(y / max, 1) : 0) + ')';

            if (header) {
                header.classList.toggle('is-scrolled', y > 12);
            }
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                ticking = true;
                window.requestAnimationFrame(update);   // hemat performa
            }
        }, { passive: true });

        window.addEventListener('resize', update);
        update();
    }

    /* ------------------------------------------------------
       Jalankan
       ------------------------------------------------------ */
    function start() {
        doc.setAttribute('data-anim-ready', '1');   // tanda JS berhasil dimuat
        initReveal();
        initScrollEffects();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();