(function() {
    'use strict';

    var updating = {};

    function cartBounce() {
        var badge = document.getElementById('cart-count');
        if (badge) {
            badge.classList.remove('cart-bounce');
            void badge.offsetWidth;
            badge.classList.add('cart-bounce');
        }
    }

    function updateBadge(data) {
        if (data && data.fragments && data.fragments['#cart-count']) {
            var tmp = document.createElement('div');
            tmp.innerHTML = data.fragments['#cart-count'];
            var nb = tmp.querySelector('#cart-count');
            if (nb) {
                var badge = document.getElementById('cart-count');
                if (badge) badge.textContent = nb.textContent;
            }
            cartBounce();
        }
    }

    function getProductIdFromKey(key) {
        var input = document.querySelector('[name="cart[' + key + '][qty]"]');
        if (!input) return null;
        var item = input.closest('[data-product-id]');
        return item ? item.dataset.productId : null;
    }

    function updateHeaderCount(data) {
        if (!data || !data.fragments || !data.fragments['#cart-count']) return;
        var tmp = document.createElement('div');
        tmp.innerHTML = data.fragments['#cart-count'];
        var nb = tmp.querySelector('#cart-count');
        if (!nb) return;
        var countEl = document.querySelector('.cart-items-count');
        if (countEl) countEl.textContent = nb.textContent + ' artículos';
    }

    function replaceFragments(data) {
        if (!data || !data.fragments) return;
        var selectors = ['div.cart-totals-rows', 'div.cart-totals-total'];
        selectors.forEach(function(sel) {
            var html = data.fragments[sel];
            if (!html) return;
            var el = document.querySelector(sel);
            if (el) el.outerHTML = html;
        });
    }

    // ====== REMOVE FROM CART (AJAX) ======
    window.farmapaz_remove_from_cart = function(key) {
        updating[key] = true;
        var fd = new FormData();
        fd.append('cart_item_key', key);
        fetch(farmapazData.homeUrl + '/?wc-ajax=remove_from_cart', {
            method: 'POST', body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            updateBadge(data);
            updateHeaderCount(data);
            replaceFragments(data);
            var item = document.querySelector('[data-cart-key="' + key + '"]');
            if (item) {
                item.style.transform = 'translateX(100%)';
                item.style.opacity = '0';
                item.style.transition = 'all 0.3s ease';
                setTimeout(function() { item.remove(); }, 300);
            }
            delete updating[key];
        })
        .catch(function() {
            delete updating[key];
            location.reload();
        });
    };

    // ====== UPDATE QUANTITY (via remove + add) ======
    window.farmapaz_update_cart_qty = function(key, newQty) {
        newQty = parseInt(newQty);
        if (newQty < 0) newQty = 0;
        if (newQty === 0) {
            window.farmapaz_remove_from_cart(key);
            return;
        }
        if (updating[key]) return;
        updating[key] = true;

        var productId = getProductIdFromKey(key);
        if (!productId) { location.reload(); return; }

        // Update input visually
        var input = document.querySelector('[name="cart[' + key + '][qty]"]');
        if (input) input.value = newQty;

        // Step 1: Remove old item
        var fd1 = new FormData();
        fd1.append('cart_item_key', key);
        fetch(farmapazData.homeUrl + '/?wc-ajax=remove_from_cart', {
            method: 'POST', body: fd1
        })
        .then(function(r) { return r.json(); })
        .then(function() {
            // Step 2: Add back with new quantity
            var fd2 = new FormData();
            fd2.append('product_id', productId);
            fd2.append('quantity', newQty);
            return fetch(farmapazData.homeUrl + '/?wc-ajax=add_to_cart', {
                method: 'POST', body: fd2
            });
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            updateBadge(data);
            updateHeaderCount(data);
            replaceFragments(data);
            // Replace cart items container (fresh render with new keys)
            var itemsHtml = data.fragments['div.cart-items'];
            if (itemsHtml) {
                var itemsContainer = document.querySelector('div.cart-items');
                if (itemsContainer) itemsContainer.outerHTML = itemsHtml;
            }
            // Re-bind qty buttons on the new items
            bindCartQty();
            delete updating[key];
        })
        .catch(function() {
            delete updating[key];
            location.reload();
        });
    };

    // ====== QTY BUTTONS ======
    function bindCartQty() {
        document.querySelectorAll('.cart-qty-btn').forEach(function(btn) {
            if (btn.dataset.cartBound) return;
            btn.dataset.cartBound = '1';
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var key = this.dataset.key;
                if (!key || updating[key]) return;
                var input = document.querySelector('[name="cart[' + key + '][qty]"]');
                if (!input) return;
                var val = parseInt(input.value) || 1;
                if (this.classList.contains('cart-qty-plus')) val++;
                if (this.classList.contains('cart-qty-minus') && val > 1) val--;
                window.farmapaz_update_cart_qty(key, val);
            });
        });
    }

    bindCartQty();

})();
