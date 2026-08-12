<?php
/**
 * Cart page
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart'); ?>

<div class="cart-wrap">
    <div class="cart-layout">
        <!-- Cart Items -->
        <div class="cart-items-col">
            <form class="cart-form woocommerce-cart-form" action="<?= esc_url(wc_get_cart_url()); ?>" method="post">
                <div class="cart-items-header">
                    <h2 class="cart-items-title">Productos</h2>
                    <span class="cart-items-count"><?= WC()->cart->get_cart_contents_count(); ?> artículos</span>
                </div>

                <?php do_action('woocommerce_before_cart_table'); ?>

                <div class="cart-items">
                    <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item):
                        $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                        $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);

                        if (!$_product || !$_product->exists() || $cart_item['quantity'] < 1 || !apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                            continue;
                        }

                        $price     = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
                        $subtotal  = apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key);
                        $permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);

                        $img_url = farmapaz_product_img_fallback(get_the_post_thumbnail_url($product_id, 'thumbnail'));
                    ?>
                    <div class="cart-item" data-cart-key="<?= esc_attr($cart_item_key); ?>" data-product-id="<?= esc_attr($product_id); ?>" data-unit-price="<?= esc_attr($_product->get_price()); ?>" data-sku="<?= esc_attr($_product->get_sku() ?: $product_id); ?>" data-name="<?= esc_attr($product_name); ?>" data-link="<?= esc_url($permalink); ?>">
                        <button class="cart-item-remove" title="Eliminar"
                                onclick="farmapaz_remove_from_cart('<?= esc_js($cart_item_key); ?>')">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        <div class="cart-item-img-wrap">
                            <?php if ($permalink): ?><a href="<?= esc_url($permalink); ?>"><?php endif; ?>
                                <?php farmapaz_picture($img_url, $product_name, 'cart-item-img'); ?>
                            <?php if ($permalink): ?></a><?php endif; ?>
                        </div>
                        <div class="cart-item-body">
                            <div class="cart-item-info">
                                <h3 class="cart-item-name">
                                    <?php if ($permalink): ?><a href="<?= esc_url($permalink); ?>"><?php endif; ?>
                                        <?= esc_html($product_name); ?>
                                    <?php if ($permalink): ?></a><?php endif; ?>
                                </h3>
                                <div class="cart-item-meta">
                                    <?php echo wc_get_formatted_cart_item_data($cart_item); ?>
                                </div>
                                <div class="cart-item-price-mobile"><?= $subtotal; ?></div>
                            </div>
                            <div class="cart-item-qty">
                                <div class="cart-qty-selector">
                                    <button type="button" class="cart-qty-btn cart-qty-minus"
                                            data-key="<?= esc_attr($cart_item_key); ?>">−</button>
                                    <input type="number" class="cart-qty-input"
                                           name="cart[<?= esc_attr($cart_item_key); ?>][qty]"
                                           value="<?= esc_attr($cart_item['quantity']); ?>"
                                           min="0"
                                           data-key="<?= esc_attr($cart_item_key); ?>">
                                    <button type="button" class="cart-qty-btn cart-qty-plus"
                                            data-key="<?= esc_attr($cart_item_key); ?>">+</button>
                                </div>
                            </div>
                            <div class="cart-item-price"><?= $subtotal; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php do_action('woocommerce_after_cart_table'); ?>

                <!-- Coupon + Update -->
                <div class="cart-actions">
                    <div class="cart-coupon">
                        <input type="text" name="coupon_code" class="cart-coupon-input" placeholder="Código de descuento" id="coupon_code">
                        <button type="submit" class="cart-coupon-btn" name="apply_coupon">Aplicar</button>
                        <?php do_action('woocommerce_cart_coupon'); ?>
                    </div>
                    <button type="submit" class="cart-update-btn" name="update_cart" hidden>Actualizar</button>
                </div>

                <?php do_action('woocommerce_cart_actions'); ?>
                <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
            </form>

            <?php if (count(WC()->cart->get_cart()) > 0): ?>
            <div class="cart-cross-sells">
                <?php
                $cross_sells = WC()->cart->get_cross_sells();
                if ($cross_sells):
                    $args = [
                        'post_type'      => 'product',
                        'post__in'       => $cross_sells,
                        'posts_per_page' => 4,
                        'orderby'        => 'rand',
                        'meta_query'     => [
                            ['key' => '_price', 'value' => 0, 'compare' => '>', 'type' => 'DECIMAL'], // [†] $0 excluded
                            ['key' => '_stock_status', 'value' => 'instock'],
                        ],
                    ];
                    $cross_q = new WP_Query($args);
                    if ($cross_q->have_posts()):
                ?>
                <div class="cart-cross-title">También te puede interesar</div>
                <div class="cart-cross-grid">
                    <?php while ($cross_q->have_posts()): $cross_q->the_post();
                        global $product;
                        $img = farmapaz_product_img_fallback(get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'));
                    ?>
                    <a href="<?php the_permalink(); ?>" class="cart-cross-item">
                        <?php farmapaz_picture($img, get_the_title(), 'cart-cross-img'); ?>
                        <div class="cart-cross-name"><?php the_title(); ?></div>
                        <div class="cart-cross-price"><?= $product->get_price_html(); ?></div>
                    </a>
                    <?php endwhile; ?>
                </div>
                <?php
                    endif;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Cart Totals -->
        <div class="cart-totals-col">
            <div class="cart-totals-sticky">
                <h2 class="cart-totals-title">Resumen</h2>

                <?= farmapaz_render_cart_totals_rows(); ?>
                <?= farmapaz_render_cart_total(); ?>

                <div class="cart-checkout-btn-wrap">
                    <?php do_action('woocommerce_proceed_to_checkout'); ?>
                </div>

                <a href="#" class="farmapaz-wa-btn farmapaz-wa-btn-pickup farmapaz-wa-btn-cart" data-wa-order>
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21 5.46 0 9.91-4.45 9.91-9.91C21.95 6.45 17.5 2 12.04 2zm5.83 14.15c-.25.7-1.45 1.33-2 1.38-.51.05-1.16.07-1.87-.12-.43-.16-.98-.32-1.69-.62-2.98-1.29-4.92-4.29-5.07-4.49-.15-.2-1.22-1.62-1.22-3.09 0-1.47.77-2.19 1.04-2.49.27-.3.6-.37.8-.37h.57c.18.01.43-.07.67.51.25.6.85 2.07.93 2.22.08.15.13.33.03.53-.1.2-.15.32-.3.5-.15.18-.31.4-.45.53-.15.15-.3.31-.13.61.17.3.75 1.24 1.61 2.01 1.11.99 2.04 1.3 2.33 1.45.29.15.46.13.63-.08.17-.2.73-.85.92-1.15.2-.3.4-.25.67-.15.27.1 1.71.81 2 .96.3.15.5.22.57.35.08.13.08.7-.17 1.4z"/></svg>
                    Retirar en tienda
                </a>

                <?php do_action('woocommerce_cart_totals_after_order_total'); ?>
            </div>
        </div>
    </div>
</div>

<?php do_action('woocommerce_after_cart'); ?>
