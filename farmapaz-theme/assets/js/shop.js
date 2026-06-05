/**
 * Farmapaz — Shop Page (AJAX Filters, Infinite Scroll, 3D Tilt)
 */
(function() {
    'use strict';

    var grid = document.getElementById('shopGrid');
    var count = document.getElementById('shopCount');
    var loading = document.getElementById('shopLoading');
    var sentinel = document.getElementById('shopSentinel');
    var loader = document.getElementById('shopLoader');
    var sidebar = document.getElementById('shopSidebar');
    var overlay = document.getElementById('shopOverlay');
    var toggleBtn = document.getElementById('toggleSidebar');
    var closeBtn = document.getElementById('closeSidebar');

    if (!grid) return;

    var loadingMore = false;
    var hasMore = true;

    // Detect if we're on a product taxonomy page (category/brand archive)
    var isTaxPage = document.body.classList.contains('tax-product_cat') || document.body.classList.contains('tax-product_brand') || document.body.classList.contains('tax-product_tag');

    // Helper: reset URL to shop page when filtering from a taxonomy page
    function resetToShopUrl() {
        if (isTaxPage && typeof farmapazData !== 'undefined' && farmapazData.shopUrl) {
            window.history.replaceState({}, '', farmapazData.shopUrl);
            isTaxPage = false;
        }
    }

    // ====== FILTER STATE ======
    var state = {
        cat: getUrlParams('cat'),
        brand: getUrlParams('brand'),
        min_price: getUrlParam('min_price') || '',
        max_price: getUrlParam('max_price') || '',
        s: getUrlParam('s') || '',
        orderby: document.getElementById('shopOrderby') ? document.getElementById('shopOrderby').value : 'date',
        page: 1
    };

    // If on a category archive page, seed state with the URL's category slug
    if (state.cat.length === 0 && document.body.classList.contains('tax-product_cat')) {
        var termClass = Array.from(document.body.classList).find(function(c) { return c.indexOf('term-') === 0; });
        if (termClass) {
            state.cat = [termClass.replace('term-', '')];
        }
    }

    function getUrlParam(name) {
        var params = new URLSearchParams(window.location.search);
        return params.get(name) || '';
    }

    function getUrlParams(name) {
        var params = new URLSearchParams(window.location.search);
        var vals = params.getAll(name);
        return vals.length ? vals : [];
    }

    // ====== FETCH PRODUCTS ======
    function fetchProducts(append, cb, resetUrl) {
        if (resetUrl) resetToShopUrl();
        if (loading) loading.classList.add('active');
        if (append && loader) loader.style.display = 'flex';

        var formData = new FormData();
        formData.append('action', 'farmapaz_shop_filter');
        formData.append('nonce', typeof farmapazData !== 'undefined' ? farmapazData.nonce : '');
        formData.append('page', state.page);
        formData.append('orderby', state.orderby);

        if (state.cat.length) {
            state.cat.forEach(function(c) { formData.append('cat[]', c); });
        }
        if (state.brand.length) {
            state.brand.forEach(function(b) { formData.append('brand[]', b); });
        }
        if (state.min_price) formData.append('min_price', state.min_price);
        if (state.max_price) formData.append('max_price', state.max_price);
        if (state.s) formData.append('s', state.s);

        fetch(typeof farmapazData !== 'undefined' ? farmapazData.ajaxUrl : '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (loading) loading.classList.remove('active');
            if (loader) loader.style.display = 'none';
            if (!data.products || data.products.trim() === '') {
                hasMore = false;
                if (sentinel) sentinel.style.display = 'none';
                if (cb) cb();
                return;
            }
            if (append) {
                grid.insertAdjacentHTML('beforeend', data.products);
            } else {
                grid.innerHTML = data.products;
                state.page = 1;
            }
            if (count) count.textContent = data.count + ' productos';
            hasMore = data.has_more;
            if (!hasMore && sentinel) sentinel.style.display = 'none';
            else if (sentinel) sentinel.style.display = '';
            loadingMore = false;
            bindTilt();
            bindImageFallback();
            if (typeof window.farmapazBindAddToCart === 'function') window.farmapazBindAddToCart(grid);
            updateCheckboxes();
            var activeFiltersEl = document.querySelector('.active-filters');
            if (activeFiltersEl) {
                activeFiltersEl.outerHTML = data.active_filters || '<div class="active-filters" style="display:none"></div>';
            }
            var heroIndicatorEl = document.getElementById('shopHeroIndicator');
            if (heroIndicatorEl) {
                heroIndicatorEl.innerHTML = data.hero_indicator || '';
            }
            if (typeof cb === 'function') cb();
        })
        .catch(function() {
            if (loading) loading.classList.remove('active');
            if (loader) loader.style.display = 'none';
            loadingMore = false;
        });
    }

    // ====== INFINITE SCROLL ======
    if (sentinel && 'IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting && hasMore && !loadingMore) {
                    loadingMore = true;
                    state.page = (state.page || 1) + 1;
                    fetchProducts(true);
                }
            });
        }, { rootMargin: '200px' });
        observer.observe(sentinel);
    }

    // ====== BIND FILTERS ======
    function bindFilters() {
        document.querySelectorAll('.filter-cat').forEach(function(cb) {
            cb.addEventListener('change', function() {
                state.cat = getCheckedValues('.filter-cat');
                state.page = 1;
                hasMore = true;
                if (sentinel) sentinel.style.display = '';
                fetchProducts(false, null, true);
            });
        });

        document.querySelectorAll('.filter-brand').forEach(function(cb) {
            cb.addEventListener('change', function() {
                state.brand = getCheckedValues('.filter-brand');
                state.page = 1;
                hasMore = true;
                if (sentinel) sentinel.style.display = '';
                fetchProducts(false, null, true);
            });
        });

        var priceMin = document.querySelector('.filter-price-min');
        var priceMax = document.querySelector('.filter-price-max');
        function onPriceChange() {
            var min = priceMin ? priceMin.value : '';
            var max = priceMax ? priceMax.value : '';
            if (min === state.min_price && max === state.max_price) return;
            state.min_price = min;
            state.max_price = max;
            state.page = 1;
            hasMore = true;
            if (sentinel) sentinel.style.display = '';
            fetchProducts(false, null, true);
        }
        var priceTimer;
        if (priceMin) {
            priceMin.addEventListener('input', function() {
                clearTimeout(priceTimer);
                priceTimer = setTimeout(onPriceChange, 500);
            });
        }
        if (priceMax) {
            priceMax.addEventListener('input', function() {
                clearTimeout(priceTimer);
                priceTimer = setTimeout(onPriceChange, 500);
            });
        }

        var searchInput = document.querySelector('.filter-search');
        var searchTimer;
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    state.s = searchInput.value;
                    state.page = 1;
                    hasMore = true;
                    if (sentinel) sentinel.style.display = '';
                    fetchProducts(false, null, true);
                }, 400);
            });
        }

        var orderby = document.getElementById('shopOrderby');
        if (orderby) {
            orderby.addEventListener('change', function() {
                state.orderby = this.value;
                state.page = 1;
                hasMore = true;
                if (sentinel) sentinel.style.display = '';
                fetchProducts(false, null, true);
            });
        }

        document.querySelectorAll('.filter-toggle').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var target = document.getElementById(this.dataset.target);
                if (target) {
                    target.classList.toggle('open');
                    this.classList.toggle('open');
                }
            });
        });

        document.addEventListener('click', function(e) {
            var tag = e.target.closest('.active-filter-tag');
            if (!tag) return;
            var remove = tag.dataset.remove;
            var value = tag.dataset.value;
            if (remove === 'cat') {
                state.cat = state.cat.filter(function(c) { return c !== value; });
            } else if (remove === 'brand') {
                state.brand = state.brand.filter(function(b) { return b !== value; });
            } else if (remove === 'price') {
                state.min_price = '';
                state.max_price = '';
                if (document.querySelector('.filter-price-min')) document.querySelector('.filter-price-min').value = '';
                if (document.querySelector('.filter-price-max')) document.querySelector('.filter-price-max').value = '';
            } else if (remove === 's') {
                state.s = '';
                if (document.querySelector('.filter-search')) document.querySelector('.filter-search').value = '';
            }
            state.page = 1;
            hasMore = true;
            if (sentinel) sentinel.style.display = '';
            fetchProducts(false, null, true);
        });

        document.querySelectorAll('.filter-clear-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var field = this.dataset.clear;
                if (field === 's') {
                    state.s = '';
                    if (document.querySelector('.filter-search')) document.querySelector('.filter-search').value = '';
                    state.page = 1;
                    hasMore = true;
                    if (sentinel) sentinel.style.display = '';
                    fetchProducts(false, null, true);
                }
            });
        });
    }

    function getCheckedValues(selector) {
        var vals = [];
        document.querySelectorAll(selector + ':checked').forEach(function(cb) {
            vals.push(cb.value);
        });
        return vals;
    }

    function updateCheckboxes() {
        document.querySelectorAll('.filter-cat').forEach(function(cb) {
            cb.checked = state.cat.indexOf(cb.value) !== -1;
        });
        document.querySelectorAll('.filter-brand').forEach(function(cb) {
            cb.checked = state.brand.indexOf(cb.value) !== -1;
        });
    }

    // ====== 3D TILT ======
    function bindTilt() {
        document.querySelectorAll('[data-tilt]').forEach(function(card) {
            if (card.dataset.tiltBound) return;
            card.dataset.tiltBound = '1';
            card.addEventListener('mousemove', function(e) {
                var rect = this.getBoundingClientRect();
                var x = e.clientX - rect.left;
                var y = e.clientY - rect.top;
                var centerX = rect.width / 2;
                var centerY = rect.height / 2;
                var rotateX = ((y - centerY) / centerY) * -6;
                var rotateY = ((x - centerX) / centerX) * 6;
                this.style.transform = 'perspective(1000px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) translateZ(10px)';
                var inner = this.querySelector('.sp-card-inner');
                if (inner) {
                    var px = (x / rect.width) * 100;
                    var py = (y / rect.height) * 100;
                    inner.style.setProperty('--glow-x', px + '%');
                    inner.style.setProperty('--glow-y', py + '%');
                }
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateZ(0)';
                var inner = this.querySelector('.sp-card-inner');
                if (inner) {
                    inner.style.setProperty('--glow-x', '50%');
                    inner.style.setProperty('--glow-y', '50%');
                }
            });
        });
    }

    // ====== SIDEBAR TOGGLE (mobile) ======
    if (toggleBtn && sidebar && overlay) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.add('open');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);
    }

    // ====== IMAGE FALLBACK ======
    function bindImageFallback() {
        var fallbackSrc = typeof farmapazData !== 'undefined' ? farmapazData.themeUri + '/assets/images/producto.png' : '/wp-content/themes/farmapaz-theme/assets/images/producto.png';
        document.querySelectorAll('.sp-card-img').forEach(function(img) {
            if (img.dataset.fallbackBound) return;
            img.dataset.fallbackBound = '1';
            img.addEventListener('error', function() {
                this.onerror = null;
                this.src = fallbackSrc;
            });
        });
    }

    document.addEventListener('error', function(e) {
        var target = e.target;
        if (target.tagName === 'IMG' && target.classList.contains('sp-card-img') && !target.dataset.fallbackTried) {
            target.dataset.fallbackTried = '1';
            var fallbackSrc = typeof farmapazData !== 'undefined' ? farmapazData.themeUri + '/assets/images/producto.png' : '/wp-content/themes/farmapaz-theme/assets/images/producto.png';
            target.src = fallbackSrc;
        }
    }, true);

    // ====== BRAND SEARCH ======
    function bindBrandSearch() {
        var searchInput = document.querySelector('.filter-brand-search');
        if (!searchInput) return;
        searchInput.addEventListener('input', function() {
            var query = this.value.toLowerCase().trim();
            document.querySelectorAll('.filter-brand-list .filter-option').forEach(function(opt) {
                var brandName = (opt.dataset.brand || '').toLowerCase();
                opt.style.display = (!query || brandName.indexOf(query) !== -1) ? '' : 'none';
            });
        });
    }

    // ====== INIT ======
    // Compute initial page from URL param
    state.page = parseInt(getUrlParam('page')) || 1;
    // If we have filter params on load, hasMore stays true
    hasMore = true;

    bindFilters();
    bindTilt();
    bindImageFallback();
    bindBrandSearch();
    updateCheckboxes();

    if (state.cat.length) {
        var catList = document.getElementById('cat-list');
        var catToggle = document.querySelector('[data-target="cat-list"]');
        if (catList) catList.classList.add('open');
        if (catToggle) catToggle.classList.add('open');
    }
    if (state.brand.length) {
        var brandList = document.getElementById('brand-list');
        var brandToggle = document.querySelector('[data-target="brand-list"]');
        if (brandList) brandList.classList.add('open');
        if (brandToggle) brandToggle.classList.add('open');
    }

    // Clean URL on load — remove any leftover filter query params
    if (window.location.search) {
        window.history.replaceState({}, '', window.location.pathname);
    }

})();
