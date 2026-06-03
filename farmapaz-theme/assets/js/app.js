/**
 * Farmapaz - Main JavaScript v2
 */

(function() {
    'use strict';

    // ====== MOBILE MENU ======
    const menuBtn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');
    const menuClose = document.getElementById('mobile-menu-close');
    const menuOverlay = document.getElementById('mobile-menu-overlay');

    function openMenu() {
        menu.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeMenu() {
        menu.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    if (menuBtn && menu) {
        menuBtn.addEventListener('click', openMenu);
        if (menuClose) menuClose.addEventListener('click', closeMenu);
        if (menuOverlay) menuOverlay.addEventListener('click', closeMenu);
    }

    // ====== HERO SLIDERS (supports multiple instances) ======
    document.querySelectorAll('.hero-slider').forEach(heroSlider => {
        const slides = heroSlider.querySelectorAll('.hero-slide');
        const dots = heroSlider.querySelectorAll('.hero-dot');
        const prevBtn = heroSlider.querySelector('.hero-prev');
        const nextBtn = heroSlider.querySelector('.hero-next');
        let currentSlide = 0;
        let slideInterval;

        if (slides.length < 2) return;

        function showSlide(index) {
            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active')) 

            currentSlide = (index + slides.length) % slides.length;
            slides[currentSlide].classList.add('active');
            if (dots[currentSlide]) {
                dots[currentSlide].classList.add('active');
            }
        }

        function nextSlide() { showSlide(currentSlide + 1); }
        function prevSlide() { showSlide(currentSlide - 1); }

        function startAutoplay() {
            stopAutoplay();
            slideInterval = setInterval(nextSlide, 5000);
        }

        function stopAutoplay() {
            if (slideInterval) {
                clearInterval(slideInterval);
                slideInterval = null;
            }
        }

        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => { showSlide(i); startAutoplay(); });
        });

        if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); startAutoplay(); });
        if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); startAutoplay(); });

        heroSlider.addEventListener('mouseenter', stopAutoplay);
        heroSlider.addEventListener('mouseleave', startAutoplay);

        // Touch swipe
        let touchStartX = 0;
        heroSlider.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        heroSlider.addEventListener('touchend', e => {
            const diff = touchStartX - e.changedTouches[0].screenX;
            if (Math.abs(diff) > 50) {
                diff > 0 ? nextSlide() : prevSlide();
                startAutoplay();
            }
        }, { passive: true });

        startAutoplay();
    });

    // ====== PRODUCT CAROUSELS ======
    function initCarousels() {
        document.querySelectorAll('.product-carousel-track').forEach(track => {
            if (track.dataset.carouselInit === '1') return;
            track.dataset.carouselInit = '1';

            const sectionId = track.dataset.carousel;
            const prevBtn = document.querySelector(`.carousel-arrow-prev[data-carousel="${sectionId}"]`);
            const nextBtn = document.querySelector(`.carousel-arrow-next[data-carousel="${sectionId}"]`);

            const getScrollStep = () => {
                const first = track.querySelector('.flex-none, .snap-start');
                if (!first) return 300;
                const gap = parseInt(getComputedStyle(track).gap) || 16;
                return first.offsetWidth + gap;
            };

            const scrollToStep = (direction) => {
                const step = getScrollStep();
                const maxScroll = track.scrollWidth - track.clientWidth;
                let target = track.scrollLeft + (direction * step);
                if (target < 0) target = maxScroll;
                else if (target > maxScroll) target = 0;
                track.scrollTo({ left: target, behavior: 'smooth' });
                resetAutoScroll();
            };

            if (prevBtn) prevBtn.addEventListener('click', () => scrollToStep(-1));
            if (nextBtn) nextBtn.addEventListener('click', () => scrollToStep(1));

            // Touch swipe
            let touchStart = 0;
            track.addEventListener('touchstart', e => {
                touchStart = e.changedTouches[0].screenX;
            }, { passive: true });
            track.addEventListener('touchend', e => {
                const diff = touchStart - e.changedTouches[0].screenX;
                if (Math.abs(diff) > 40) {
                    scrollToStep(diff > 0 ? 1 : -1);
                }
            }, { passive: true });

            // Auto-scroll
            let autoScrollTimer;
            function startAutoScroll() {
                stopAutoScroll();
                const hasOverflow = track.scrollWidth > track.clientWidth + 5;
                if (!hasOverflow) return;
                autoScrollTimer = setInterval(() => {
                    const step = getScrollStep();
                    const maxScroll = track.scrollWidth - track.clientWidth;
                    let nextPos = track.scrollLeft + step;
                    if (nextPos > maxScroll) nextPos = 0;
                    track.scrollTo({ left: nextPos, behavior: 'smooth' });
                }, 4000);
            }
            function stopAutoScroll() {
                if (autoScrollTimer) {
                    clearInterval(autoScrollTimer);
                    autoScrollTimer = null;
                }
            }
            function resetAutoScroll() {
                stopAutoScroll();
                startAutoScroll();
            }
            track.addEventListener('mouseenter', stopAutoScroll);
            track.addEventListener('mouseleave', startAutoScroll);
            track.addEventListener('touchstart', stopAutoScroll, { passive: true });
            track.addEventListener('touchend', startAutoScroll, { passive: true });

            setTimeout(() => {
                startAutoScroll();
            }, 300);
        });
    }

    // Run on load, then again after images render
    initCarousels();
    setTimeout(initCarousels, 1500);
    window.addEventListener('load', initCarousels);

    // ====== QUANTITY BUTTONS ======
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.closest('.flex')?.querySelector('.qty-input');
            if (!input) return;
            let val = parseInt(input.value) || 1;
            if (this.dataset.action === 'plus') val++;
            if (this.dataset.action === 'minus' && val > 1) val--;
            input.value = val;
        });
    });

    // ====== SCROLL ANIMATIONS (Apple-style) ======
    function initScrollAnimations() {
        const selectors = [
            '.fade-in-up:not(.visible)',
            '.stagger-item:not(.visible)',
            '.reveal:not(.visible)',
            '.reveal-up:not(.visible)',
            '.reveal-clip:not(.visible)',
            '.stagger-children:not(.visible)',
        ];
        const elements = selectors.flatMap(sel => 
            Array.from(document.querySelectorAll(sel))
        );

        if (elements.length === 0) return;

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.08,
                rootMargin: '0px 0px -50px 0px'
            });

            elements.forEach(el => observer.observe(el));
        } else {
            elements.forEach(el => el.classList.add('visible'));
        }
    }

    // ====== PARALLAX ON SCROLL ======
    function initParallax() {
        const parallaxElements = document.querySelectorAll('[data-speed]');
        if (parallaxElements.length === 0) return;

        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    const scrollY = window.pageYOffset;
                    parallaxElements.forEach(el => {
                        const speed = parseFloat(el.dataset.speed) || 0.1;
                        const rect = el.getBoundingClientRect();
                        const centerOffset = (rect.top + rect.height / 2) / window.innerHeight;
                        const translateY = (centerOffset - 0.5) * speed * 100;
                        el.style.transform = `translateY(${translateY}px)`;
                    });
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }

    initScrollAnimations();
    initParallax();

    // ====== HEADER SHADOW ON SCROLL ======
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            header.classList.toggle('shadow-sm', currentScroll > 80);
        }, { passive: true });
    }

    // ====== AJAX ADD TO CART ======
    document.querySelectorAll('.add_to_cart_button, a[data-product_id]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.product_id;
            if (!productId) return;

            const originalText = this.textContent;
            this.textContent = 'Añadiendo...';
            this.style.opacity = '0.7';

            fetch(farmapazData.ajaxUrl + '?add-to-cart=' + productId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'add-to-cart=' + productId
            }).then(() => {
                this.textContent = '✓ Añadido';
                this.style.opacity = '1';
                this.style.background = '#16a34a';
                setTimeout(() => {
                    this.textContent = originalText;
                    this.style.background = '';
                }, 2000);
                if (typeof wc_add_to_cart_params !== 'undefined') {
                    jQuery(document.body).trigger('wc_fragment_refresh');
                }
            }).catch(() => {
                this.textContent = originalText;
                this.style.opacity = '1';
            });
        });
    });

    // ====== PRODUCT IMAGE GALLERY (single product) ======
    const galleryMain = document.querySelector('.product-gallery-main');
    const galleryThumbs = document.querySelectorAll('.product-gallery-thumb');
    if (galleryMain && galleryThumbs.length) {
        galleryThumbs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                const src = this.dataset.src;
                if (src) {
                    galleryMain.src = src;
                    galleryThumbs.forEach(t => t.classList.remove('border-brand-green'));
                    this.classList.add('border-brand-green');
                }
            });
        });
    }

    // ====== MOBILE CATEGORIES TOGGLE ======
    const catToggle = document.getElementById('mobile-cat-toggle');
    const catList = document.getElementById('mobile-cat-list');
    const catChevron = document.getElementById('mobile-cat-chevron');
    if (catToggle && catList && catChevron) {
        catToggle.addEventListener('click', () => {
            const isOpen = !catList.classList.contains('hidden');
            catList.classList.toggle('hidden');
            catChevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
        });
    }
})();
