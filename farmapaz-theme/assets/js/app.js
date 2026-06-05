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

            // Mouse drag
            let isDragging = false;
            let dragStartX = 0;
            let dragScrollLeft = 0;

            track.addEventListener('mousedown', (e) => {
                isDragging = true;
                dragStartX = e.pageX;
                dragScrollLeft = track.scrollLeft;
                track.style.cursor = 'grabbing';
                track.style.userSelect = 'none';
            });

            window.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                e.preventDefault();
                const walked = (e.pageX - dragStartX) * 1.2;
                track.scrollLeft = dragScrollLeft - walked;
            });

            window.addEventListener('mouseup', () => {
                if (!isDragging) return;
                isDragging = false;
                track.style.cursor = '';
                track.style.userSelect = '';
            });

            track.addEventListener('mouseleave', () => {
                if (!isDragging) return;
                isDragging = false;
                track.style.cursor = '';
                track.style.userSelect = '';
            });

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
    function bindAddToCart(container) {
        const scope = container || document;
        scope.querySelectorAll('.add_to_cart_button, a[data-product_id]').forEach(btn => {
            if (btn.dataset.farmapazCartBound) return;
            btn.dataset.farmapazCartBound = '1';

            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.dataset.product_id;
                if (!productId) return;

                const originalText = this.textContent;
                this.textContent = 'Añadiendo...';
                this.style.opacity = '0.7';

                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', 1);

                fetch(farmapazData.homeUrl + '/?wc-ajax=add_to_cart', {
                    method: 'POST',
                    body: formData
                }).then(r => r.json()).then(data => {
                    var btn = this;
                    btn.textContent = '✓ Añadido';
                    btn.style.opacity = '1';
                    btn.style.background = '#16a34a';

                    if (data.fragments) {
                        updateBadgeFromFragments(data.fragments);
                        updateCartMapFromFragments(data.fragments);
                    }

                    cartBounce();
                    showAddToast(productId);

                    setTimeout(function() {
                        var container = btn.closest('.cart-card-actions') || btn.parentNode;
                        if (container && container.dataset.product_id) {
                            var qty = 1;
                            var cm = getCartMap();
                            if (cm[productId]) qty = cm[productId].qty;
                            container.innerHTML = '<div class="cart-card-stepper" data-product_id="' + productId + '">'
                                + '<button class="cart-card-stepper-btn cart-card-stepper-minus" data-product_id="' + productId + '">\u2212</button>'
                                + '<span class="cart-card-stepper-value">' + qty + '</span>'
                                + '<button class="cart-card-stepper-btn cart-card-stepper-plus" data-product_id="' + productId + '">+</button>'
                                + '</div>';
                            bindCardSteppers(container);
                        }
                    }, 800);
                }).catch(() => {
                    this.textContent = originalText;
                    this.style.opacity = '1';
                });
            });
        });
        bindCardSteppers(scope);
    }

    function cartBounce() {
        const badge = document.getElementById('cart-count');
        if (badge) {
            badge.classList.remove('cart-bounce');
            void badge.offsetWidth;
            badge.classList.add('cart-bounce');
        }
    }

    function showAddToast(productId) {
        const existing = document.querySelector('.farmapaz-toast');
        if (existing) existing.remove();

        const nameEl = document.querySelector(`a[data-product_id="${productId}"]`);
        let productName = 'Producto';
        if (nameEl) {
            const card = nameEl.closest('.sp-card, .product-card');
            if (card) {
                const titleEl = card.querySelector('.sp-card-title a, .product-card-title');
                if (titleEl) productName = titleEl.textContent.trim().substring(0, 60);
            }
        }

        const toast = document.createElement('div');
        toast.className = 'farmapaz-toast';
        toast.setAttribute('role', 'alert');
        toast.innerHTML = '<svg width="18" height="18" fill="none" stroke="#16a34a" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
            + '<span>' + productName.substring(0, 50) + ' añadido</span>'
            + '<a href="' + farmapazData.cartUrl + '" class="farmapaz-toast-link">Ver carrito</a>';
        document.body.appendChild(toast);

        setTimeout(() => { if (toast.parentNode) toast.remove(); }, 4000);
    }

    // Initial bind
    bindAddToCart();
    window.farmapazBindAddToCart = bindAddToCart;

    // ====== CARD QUANTITY STEPPER ======

    function getCartMap() {
        var el = document.querySelector('.farmapaz-cart-map');
        if (!el) return {};
        try { return JSON.parse(el.textContent || el.innerText); } catch(e) { return {}; }
    }

    function updateBadgeFromFragments(fragments) {
        if (!fragments['#cart-count']) return;
        var tmp = document.createElement('div');
        tmp.innerHTML = fragments['#cart-count'];
        var nb = tmp.querySelector('#cart-count');
        if (nb) {
            var badge = document.getElementById('cart-count');
            if (badge) badge.textContent = nb.textContent;
        }
    }

    function updateCartMapFromFragments(fragments) {
        if (!fragments['div.farmapaz-cart-map']) return;
        var tmp = document.createElement('div');
        tmp.innerHTML = fragments['div.farmapaz-cart-map'];
        var nm = tmp.querySelector('.farmapaz-cart-map');
        var om = document.querySelector('.farmapaz-cart-map');
        if (nm && om) om.outerHTML = nm.outerHTML;
        else if (nm) document.body.appendChild(nm);
    }

    function bindCardSteppers(container) {
        var scope = container || document;
        scope.querySelectorAll('.cart-card-stepper-plus, .cart-card-stepper-minus').forEach(function(btn) {
            if (btn.dataset.farmapazStepperBound) return;
            btn.dataset.farmapazStepperBound = '1';
            btn.addEventListener('click', function() {
                var productId = this.dataset.product_id;
                var stepper = this.closest('.cart-card-stepper');
                if (!stepper) return;
                var valueEl = stepper.querySelector('.cart-card-stepper-value');
                if (!valueEl) return;
                var currentQty = parseInt(valueEl.textContent) || 1;
                var isPlus = this.classList.contains('cart-card-stepper-plus');
                var newQty = isPlus ? currentQty + 1 : currentQty - 1;

                if (newQty < 1) {
                    removeFromCardCart(productId, stepper);
                    return;
                }

                valueEl.textContent = newQty;

                if (isPlus) {
                    cardAddQty(productId, stepper, valueEl, currentQty);
                } else {
                    cardRemoveAddQty(productId, newQty, stepper, valueEl, currentQty);
                }
            });
        });
    }

    function cardAddQty(productId, stepper, valueEl, oldQty) {
        var fd = new FormData();
        fd.append('product_id', productId);
        fd.append('quantity', 1);

        fetch(farmapazData.homeUrl + '/?wc-ajax=add_to_cart', {
            method: 'POST', body: fd
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.fragments) {
                updateBadgeFromFragments(data.fragments);
                updateCartMapFromFragments(data.fragments);
            }
            var cm = getCartMap();
            if (cm[productId]) valueEl.textContent = cm[productId].qty;
            cartBounce();
        }).catch(function() {
            valueEl.textContent = oldQty;
        });
    }

    function cardRemoveAddQty(productId, newQty, stepper, valueEl, oldQty) {
        var cm = getCartMap();
        var cartKey = cm[productId] ? cm[productId].key : null;
        if (!cartKey) return;

        var fd = new FormData();
        fd.append('cart_item_key', cartKey);

        fetch(farmapazData.homeUrl + '/?wc-ajax=remove_from_cart', {
            method: 'POST', body: fd
        }).then(function(r) { return r.json(); }).then(function(data) {
            var fd2 = new FormData();
            fd2.append('product_id', productId);
            fd2.append('quantity', newQty);
            return fetch(farmapazData.homeUrl + '/?wc-ajax=add_to_cart', {
                method: 'POST', body: fd2
            }).then(function(r) { return r.json(); });
        }).then(function(data) {
            if (data.fragments) {
                updateBadgeFromFragments(data.fragments);
                updateCartMapFromFragments(data.fragments);
            }
            var cm = getCartMap();
            if (cm[productId]) valueEl.textContent = cm[productId].qty;
            cartBounce();
        }).catch(function() {
            valueEl.textContent = oldQty;
        });
    }

    function removeFromCardCart(productId, stepper) {
        var cm = getCartMap();
        var cartKey = cm[productId] ? cm[productId].key : null;
        if (!cartKey) return;

        var fd = new FormData();
        fd.append('cart_item_key', cartKey);

        fetch(farmapazData.homeUrl + '/?wc-ajax=remove_from_cart', {
            method: 'POST', body: fd
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.fragments) {
                updateBadgeFromFragments(data.fragments);
                updateCartMapFromFragments(data.fragments);
            }
            cartBounce();
            restoreAddBtn(productId, stepper);
        }).catch(function() {
            location.reload();
        });
    }

    function restoreAddBtn(productId, stepper) {
        var container = stepper.closest('.cart-card-actions') || stepper.parentNode;
        if (!container) return;

        var isShop = container.classList.contains('sp-card-actions');
        var btn = document.createElement('a');

        if (isShop) {
            btn.href = '?add-to-cart=' + productId;
            btn.className = 'sp-card-btn sp-card-btn-cart';
            btn.dataset.product_id = productId;
            btn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg> Añadir';
        } else {
            btn.href = '?add-to-cart=' + productId;
            btn.className = 'flex items-center justify-center gap-2 w-full py-2.5 text-sm font-semibold rounded-xl transition-all duration-300 shadow-sm';
            btn.style.background = 'linear-gradient(135deg, #09146E 0%, #0a1a7a 100%)';
            btn.style.color = 'white';
            btn.dataset.product_id = productId;
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg> Comprar';
        }

        container.innerHTML = '';
        container.appendChild(btn);
        window.farmapazBindAddToCart(container);
    }

    // Bind initial steppers (for products already in cart on page load)
    bindCardSteppers();

    // Suppress default WooCommerce notices
    jQuery(document.body).on('wc_fragment_refresh added_to_cart', function() {
        cartBounce();
        // Remove any default WooCommerce notices that appear
        document.querySelectorAll('.woocommerce-message, .woocommerce-notice, .woocommerce-info').forEach(function(el) {
            if (el.closest('.farmapaz-toast')) return;
            el.remove();
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

    // ====== SEARCH SUGGESTIONS ======
    document.querySelectorAll('.farmapaz-search-input').forEach(input => {
        const container = input.closest('[data-search-container]');
        const dropdown = container && container.querySelector('.farmapaz-suggestions');
        const spinner = container && container.querySelector('.farmapaz-spinner');
        if (!dropdown || !spinner) {
            console.warn('Farmapaz search: container, dropdown or spinner not found');
            return;
        }
        let debounceTimer;

        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const q = input.value.trim();
            if (q.length < 2) { dropdown.classList.add('hidden'); return; }
            debounceTimer = setTimeout(() => fetchSuggestions(q), 350);
        });

        input.addEventListener('focus', () => {
            if (input.value.trim().length >= 2) dropdown.classList.remove('hidden');
        });

        input.addEventListener('blur', () => {
            setTimeout(() => dropdown.classList.add('hidden'), 200);
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') dropdown.classList.add('hidden');
        });

        function fetchSuggestions(q) {
            spinner.classList.remove('hidden');
            dropdown.classList.add('hidden');
            const apiUrl = (typeof farmapazData !== 'undefined' ? farmapazData.homeUrl : '') + '/wp-json/farmapaz/v1/suggest?s=' + encodeURIComponent(q);
            fetch(apiUrl)
                .then(r => r.json())
                .then(data => {
                    spinner.classList.add('hidden');
                    if (!data.results || !data.results.length) {
                        dropdown.classList.add('hidden');
                        return;
                    }
                    dropdown.innerHTML = data.results.map(r => `
                        <a href="${r.url}" class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0">
                            ${r.type === 'category'
                                ? '<span class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(9,20,110,0.06); color: #09146E;"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg></span>'
                                : '<img src="' + r.img + '" alt="" class="w-10 h-10 rounded-lg object-cover flex-shrink-0 bg-gray-50">'
                            }
                            <div class="min-w-0 flex-1">
                                <span class="text-sm font-medium text-gray-800 line-clamp-1">${escapeHtml(r.name)}</span>
                                <span class="text-xs font-semibold" style="color: ${r.type === 'category' ? 'rgba(9,20,110,0.5)' : '#09146E'};">${r.price}</span>
                            </div>
                        </a>
                    `).join('');
                    dropdown.classList.remove('hidden');
                })
                .catch(() => { spinner.classList.add('hidden'); });
        }

        function escapeHtml(str) {
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }
    });
})();
