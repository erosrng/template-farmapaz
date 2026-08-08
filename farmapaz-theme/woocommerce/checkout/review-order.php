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
    ?>
    <div class="checkout-review-item">
        <div class="checkout-review-item-img">
            <?php if ($product_permalink): ?><a href="<?= esc_url($product_permalink); ?>"><?php endif; ?>
                <?php farmapaz_picture($img_url, $_product->get_name(), '', 'lazy'); ?>
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

<?php farmapaz_render_checkout_review_totals(); ?>

