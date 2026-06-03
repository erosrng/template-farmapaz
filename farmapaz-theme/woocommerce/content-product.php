<?php

global $product;

if (empty($product) || !$product->is_visible()) {
    return;
}

// Stock handling
$stock_qty    = $product->get_stock_quantity();
$managing     = $product->managing_stock();
$in_stock     = $product->is_in_stock();

// Hide out-of-stock
if (!$in_stock) return;

$image   = farmapaz_product_img_fallback(get_the_post_thumbnail_url(get_the_ID(), 'full'));
$is_sale = $product->is_on_sale() && $product->get_regular_price() > 0;
$regular = $product->get_regular_price();
$sale    = $product->get_sale_price();
$percent = 0;
if ($is_sale && $regular > 0) {
    $percent = round((($regular - $sale) / $regular) * 100);
}
$low_stock = $managing && $stock_qty !== null && $stock_qty <= 5;
?>
<div class="product-card group bg-white rounded-2xl border border-gray-100 hover:border-gray-200 hover:shadow-xl transition-all duration-500 overflow-hidden flex flex-col hover:-translate-y-1">
    <div class="relative aspect-square bg-gray-50 overflow-hidden">
        <?php if ($is_sale && $percent > 0): ?>
            <span class="sale-badge absolute top-3 left-3 z-10">
                -<?= $percent; ?>%
            </span>
        <?php endif; ?>
        <?php if ($low_stock): ?>
            <span class="absolute top-3 right-3 z-10 bg-brand-orange text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow" style="background: #F97316;">
                Solo por tienda
            </span>
        <?php endif; ?>
        <img src="<?= $image; ?>" alt="<?php the_title(); ?>"
             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out"
             loading="lazy">
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-500"></div>
    </div>
    <div class="p-3 sm:p-4 flex flex-col flex-1">
        <?php
        $cats = get_the_terms(get_the_ID(), 'product_cat');
        if ($cats):
        ?>
        <div class="flex flex-wrap gap-1 mb-1.5">
            <?php foreach (array_slice($cats, 0, 2) as $cat): ?>
                <span class="text-[10px] text-gray-400 uppercase tracking-wider"><?= $cat->name; ?></span>
                <?php if ($cat !== end(array_slice($cats, 0, 2))): ?><span class="text-gray-300 mx-0.5">·</span><?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <h3 class="text-xs sm:text-sm font-medium text-gray-800 line-clamp-2 leading-snug mb-1">
            <a href="<?php the_permalink(); ?>" class="hover:text-brand-green transition-colors">
                <?php the_title(); ?>
            </a>
        </h3>
        <div class="mt-auto pt-2 sm:pt-3">
            <div class="flex items-baseline gap-1.5 flex-wrap">
                <?php if ($is_sale): ?>
                    <span class="text-base sm:text-lg font-bold" style="color: #FF1A27;"><?= wc_price($sale); ?></span>
                    <span class="text-xs sm:text-sm text-gray-400 line-through"><?= wc_price($regular); ?></span>
                <?php else: ?>
                    <span class="text-base sm:text-lg font-bold" style="color: #5A7D43;"><?= $product->get_price_html(); ?></span>
                <?php endif; ?>
            </div>
            <?php if ($low_stock): ?>
                <a href="https://wa.me/584128798885?text=Consultar%20disponibilidad%3A%20<?= urlencode(get_the_title()); ?>"
                   target="_blank"
                   class="block mt-2 sm:mt-3 w-full text-center py-2.5 px-4 text-xs sm:text-sm font-medium rounded-xl transition-all duration-300"
                   style="background: #F97316; color: white;"
                   onmouseover="this.style.background='#ea580c'"
                   onmouseout="this.style.background='#F97316'">
                    Consultar disponible
                </a>
            <?php else: ?>
                <a href="<?= $product->add_to_cart_url(); ?>"
                   class="block mt-2 sm:mt-3 w-full text-center py-2.5 px-4 text-xs sm:text-sm font-medium rounded-xl transition-all duration-300"
                   style="background: #5A7D43; color: white;"
                   onmouseover="this.style.background='#4a6a36'"
                   onmouseout="this.style.background='#5A7D43'"
                   data-product_id="<?= $product->get_id(); ?>">
                    Añadir al carrito
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
