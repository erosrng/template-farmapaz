<?php
defined('ABSPATH') || exit;
?>
<div class="checkout-review-table">
    <?php
    do_action('woocommerce_review_order_before_cart_contents');

    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item):
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

        if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)):
            $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
            $img_url = farmapaz_product_img_fallback(get_the_post_thumbnail_url($product_id, 'thumbnail'));
            $fallback = get_template_directory_uri() . '/assets/images/producto.png';
    ?>
    <div class="checkout-review-item">
        <div class="checkout-review-item-img">
            <?php if ($product_permalink): ?><a href="<?= esc_url($product_permalink); ?>"><?php endif; ?>
                <img src="<?= esc_url($img_url); ?>" alt="<?= esc_attr($_product->get_name()); ?>"
                     onerror="this.onerror=null;this.src='<?= $fallback; ?>'"
                     loading="lazy">
            <?php if ($product_permalink): ?></a><?php endif; ?>
        </div>
        <div class="checkout-review-item-info">
            <div class="checkout-review-item-name">
                <?php if ($product_permalink): ?><a href="<?= esc_url($product_permalink); ?>"><?php endif; ?>
                    <?= esc_html($_product->get_name()); ?>
                <?php if ($product_permalink): ?></a><?php endif; ?>
                <span class="checkout-review-item-qty">× <?= $cart_item['quantity']; ?></span>
            </div>
            <div class="checkout-review-item-meta"><?= wc_get_formatted_cart_item_data($cart_item); ?></div>
        </div>
        <div class="checkout-review-item-total"><?= apply_filters('woocommerce_checkout_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?></div>
    </div>
    <?php
        endif;
    endforeach;

    do_action('woocommerce_review_order_after_cart_contents');
    ?>
</div>

<div class="checkout-review-totals">
    <div class="checkout-review-total-row">
        <span>Subtotal</span>
        <span><?php wc_cart_totals_subtotal_html(); ?></span>
    </div>

    <?php foreach (WC()->cart->get_coupons() as $code => $coupon): ?>
    <div class="checkout-review-total-row checkout-review-coupon">
        <span><?php wc_cart_totals_coupon_label($coupon); ?></span>
        <span><?php wc_cart_totals_coupon_html($coupon); ?></span>
    </div>
    <?php endforeach; ?>

    <?php foreach (WC()->cart->get_fees() as $fee):
        $is_shipping = stripos($fee->name, 'envío') !== false;
        $is_free = stripos($fee->name, 'gratis') !== false;
    ?>
    <div class="checkout-review-total-row checkout-review-fee <?= $is_shipping ? 'checkout-review-shipping' : ''; ?>">
        <span class="checkout-shipping-label">
            <?php if ($is_shipping): ?>
                <?php if ($is_free): ?>
                <svg class="checkout-shipping-icon checkout-shipping-free-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <?php else: ?>
                <svg class="checkout-shipping-icon checkout-shipping-paid-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2-1m4-1l-2 1m4-2l-2 1m6-5v5a1 1 0 001 1h3l2-4v-3a1 1 0 00-1-1h-4a1 1 0 00-1 1z"/></svg>
                <?php endif; ?>
                <span class="checkout-shipping-text"><?= esc_html($fee->name); ?></span>
            <?php else: ?>
                <span><?= esc_html($fee->name); ?></span>
            <?php endif; ?>
        </span>
        <span class="checkout-shipping-amount"><?php wc_cart_totals_fee_html($fee); ?></span>
    </div>
    <?php endforeach; ?>

    <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()): ?>
        <?php if (WC()->cart->get_cart_tax() > 0): ?>
        <div class="checkout-review-total-row">
            <span>IVA</span>
            <span><?php wc_cart_totals_taxes_total_html(); ?></span>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="checkout-review-total-row checkout-review-grand-total">
        <span>Total</span>
        <span><?php wc_cart_totals_order_total_html(); ?></span>
    </div>
</div>

