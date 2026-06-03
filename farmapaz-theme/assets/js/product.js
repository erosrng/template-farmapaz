/**
 * Farmapaz - Single Product JavaScript
 */

(function() {
    'use strict';

    // Quantity buttons
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.closest('.flex')?.querySelector('.qty-input');
            if (!input) return;
            let val = parseInt(input.value) || 1;
            if (this.dataset.action === 'plus') { val++; }
            if (this.dataset.action === 'minus' && val > 1) { val--; }
            input.value = val;
        });
    });

    // Gallery thumbnails
    const main = document.querySelector('.product-gallery-main');
    const thumbs = document.querySelectorAll('.product-gallery-thumb');
    if (main && thumbs.length) {
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                const src = this.dataset.src;
                if (src) {
                    main.style.opacity = '0';
                    setTimeout(() => {
                        main.src = src;
                        main.style.opacity = '1';
                    }, 150);
                    thumbs.forEach(t => t.classList.remove('border-brand-green'));
                    this.classList.add('border-brand-green');
                }
            });
        });
    }

})();
