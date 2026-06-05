/**
 * Farmapaz - Single Product JavaScript
 */

(function() {
    'use strict';

    // Quantity buttons
    document.querySelectorAll('.qty-btn').forEach(function(btn) {
        if (btn.dataset.farmapazBound) return;
        btn.dataset.farmapazBound = '1';
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var input = this.parentElement.querySelector('.qty-input');
            if (!input) return;
            var val = parseInt(input.value) || 1;
            if (this.dataset.action === 'plus') { val++; }
            if (this.dataset.action === 'minus' && val > 1) { val--; }
            input.value = val;
        });
    });

    // Gallery thumbnails
    var main = document.querySelector('.product-gallery-main');
    var thumbs = document.querySelectorAll('.product-gallery-thumb');
    if (main && thumbs.length) {
        thumbs.forEach(function(thumb) {
            thumb.addEventListener('click', function() {
                var src = this.dataset.src;
                if (src) {
                    main.style.opacity = '0';
                    setTimeout(function() {
                        main.src = src;
                        main.style.opacity = '1';
                    }, 150);
                    thumbs.forEach(function(t) { t.style.borderColor = 'transparent'; });
                    this.style.borderColor = '#09146E';
                }
            });
        });
    }

})();
