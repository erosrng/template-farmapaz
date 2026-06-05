<?php

global $product;

if (empty($product) || !$product->is_visible()) {
    return;
}

$product_id = $product->get_id();
$stock_qty    = $product->get_stock_quantity();
$managing     = $product->managing_stock();
$in_stock     = $product->is_in_stock();

if (!$in_stock) return;

$image   = farmapaz_product_img_fallback(get_the_post_thumbnail_url(get_the_ID(), 'woocommerce_thumbnail'));
$fallback = get_template_directory_uri() . '/assets/images/producto.png';
$is_sale = $product->is_on_sale() && $product->get_regular_price() > 0;
$regular = $product->get_regular_price();
$sale    = $product->get_sale_price();
$percent = 0;
if ($is_sale && $regular > 0) {
    $percent = round((($regular - $sale) / $regular) * 100);
}
$low_stock = $managing && $stock_qty !== null && $stock_qty <= 5;
$is_controlled = farmapaz_is_controlled_product($product_id);
$brands = get_the_terms(get_the_ID(), 'product_brand');
$cats   = get_the_terms(get_the_ID(), 'product_cat');
$in_cart = false;
$cart_qty = 0;
if (function_exists('WC') && WC() && WC()->cart && !WC()->cart->is_empty()) {
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ((int)$cart_item['product_id'] === (int)$product_id) {
            $in_cart = true;
            $cart_qty = (int)$cart_item['quantity'];
            break;
        }
    }
}
?>
<div class="sp-card" data-tilt>
    <div class="sp-card-inner">
        <!-- Image -->
        <div class="sp-card-img-wrap">
            <?php if ($is_sale && $percent > 0): ?>
                <span class="sp-card-badge sp-card-badge-sale">-<?= $percent; ?>%</span>
            <?php endif; ?>
            <?php if ($low_stock): ?>
                <span class="sp-card-badge sp-card-badge-low">Solo por tienda</span>
            <?php endif; ?>
            <?php $prod_price = $product->get_price(); if ($prod_price && $prod_price >= 10): ?>
                <span class="sp-card-badge sp-card-badge-delivery">Delivery Gratis</span>
            <?php endif; ?>
            <a href="<?php the_permalink(); ?>">
                <img src="<?= $image; ?>" alt="<?php the_title(); ?>"
                     class="sp-card-img"
                     loading="lazy"
                     onerror="this.onerror=null;this.src='<?= $fallback; ?>'">
            </a>
            <div class="sp-card-img-overlay"></div>
        </div>

        <!-- Info -->
        <div class="sp-card-body">
            <div class="sp-card-cats">
                <?php if ($cats): ?>
                    <?php foreach (array_slice($cats, 0, 1) as $cat): ?>
                        <span class="sp-card-cat"><?= $cat->name; ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if ($brands): ?>
                    <?php foreach (array_slice($brands, 0, 1) as $brand): ?>
                        <span class="sp-card-brand"><?= $brand->name; ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <h3 class="sp-card-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>

            <div class="sp-card-price">
                <?php if ($is_sale): ?>
                    <span class="sp-card-price-sale"><?= wc_price($sale); ?></span>
                    <span class="sp-card-price-regular"><?= wc_price($regular); ?></span>
                <?php else: ?>
                    <span class="sp-card-price-normal"><?= $product->get_price_html(); ?></span>
                <?php endif; ?>
            </div>

            <div class="sp-card-actions cart-card-actions" data-product_id="<?= $product_id; ?>">
                <?php if ($is_controlled): ?>
                    <div class="sp-card-btn sp-card-btn-controlled">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Solo en tienda
                    </div>
                <?php elseif ($low_stock): ?>
                    <a href="https://wa.me/584128798885?text=Consultar%20disponibilidad%3A%20<?= urlencode(get_the_title()); ?>"
                       target="_blank"
                       class="sp-card-btn sp-card-btn-whatsapp">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Consultar
                    </a>
                <?php elseif ($in_cart): ?>
                    <div class="cart-card-stepper" data-product_id="<?= $product_id; ?>">
                        <button class="cart-card-stepper-btn cart-card-stepper-minus" data-product_id="<?= $product_id; ?>">−</button>
                        <span class="cart-card-stepper-value"><?= $cart_qty; ?></span>
                        <button class="cart-card-stepper-btn cart-card-stepper-plus" data-product_id="<?= $product_id; ?>">+</button>
                    </div>
                <?php else: ?>
                    <a href="<?= $product->add_to_cart_url(); ?>"
                       class="sp-card-btn sp-card-btn-cart"
                       data-product_id="<?= $product_id; ?>">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        Añadir
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
