<?php
/**
 * WooCommerce Single Product
 */

get_header(); ?>

<div class="container-farmapaz py-8 lg:py-12">
    <?php
    while (have_posts()): the_post();
        global $product;
        $is_sale = $product->is_on_sale() && $product->get_regular_price() > 0;
        $regular = $product->get_regular_price();
        $sale = $product->get_sale_price();
        $percent = 0;
        if ($is_sale && $regular > 0) {
            $percent = round((($regular - $sale) / $regular) * 100);
        }
    ?>

    <!-- Breadcrumb -->
    <div class="text-sm text-gray-400 mb-6 fade-in-up">
        <a href="<?= home_url(); ?>" class="hover:text-brand-green transition-colors">Inicio</a>
        <span class="mx-2">/</span>
        <a href="<?= get_permalink(wc_get_page_id('shop')); ?>" class="hover:text-brand-green transition-colors">Tienda</a>
        <?php
        $cats = get_the_terms(get_the_ID(), 'product_cat');
        if ($cats):
            foreach (array_slice($cats, 0, 1) as $cat):
        ?>
        <span class="mx-2">/</span>
        <a href="<?= get_term_link($cat); ?>" class="hover:text-brand-green transition-colors"><?= $cat->name; ?></a>
        <?php endforeach; endif; ?>
        <span class="mx-2">/</span>
        <span class="text-gray-600"><?php the_title(); ?></span>
    </div>

    <div class="grid md:grid-cols-2 gap-8 lg:gap-14 fade-in-up">
        <!-- Gallery -->
        <div class="relative">
            <?php if ($is_sale): ?>
                <span class="sale-badge absolute top-4 left-4 z-10 text-base px-4 py-2">-<?= $percent; ?>%</span>
            <?php endif; ?>
            <div class="rounded-2xl overflow-hidden bg-gray-50 mb-4">
                <img src="<?= farmapaz_product_img_fallback(get_the_post_thumbnail_url(get_the_ID(), 'full')); ?>"
                     alt="<?php the_title(); ?>"
                     class="product-gallery-main w-full h-auto object-cover transition-opacity duration-300">
            </div>
            <?php
            $attachment_ids = $product->get_gallery_image_ids();
            if ($attachment_ids):
            ?>
            <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                <div class="product-gallery-thumb w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden border-2 border-brand-green cursor-pointer flex-shrink-0 transition-all hover:border-brand-green/70"
                     data-src="<?= farmapaz_product_img_fallback(get_the_post_thumbnail_url(get_the_ID(), 'full')); ?>">
                    <img src="<?= farmapaz_product_img_fallback(get_the_post_thumbnail_url(get_the_ID(), 'thumbnail')); ?>" alt="" class="w-full h-full object-cover">
                </div>
                <?php foreach ($attachment_ids as $attachment_id):
                    $thumb = wp_get_attachment_image_url($attachment_id, 'thumbnail');
                    $full = wp_get_attachment_image_url($attachment_id, 'full');
                ?>
                    <div class="product-gallery-thumb w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden border-2 border-transparent cursor-pointer flex-shrink-0 transition-all hover:border-brand-green/70"
                         data-src="<?= $full; ?>">
                        <img src="<?= $thumb; ?>" alt="" class="w-full h-full object-cover">
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="space-y-5 sm:space-y-6">
            <?php if ($cats): ?>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($cats as $cat): ?>
                    <a href="<?= get_term_link($cat); ?>" class="px-3 py-1 bg-brand-green/10 text-brand-green text-xs font-medium rounded-full hover:bg-brand-green/20 transition-colors">
                        <?= $cat->name; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 leading-tight"><?php the_title(); ?></h1>

            <div class="flex items-baseline gap-3">
                <?php if ($is_sale): ?>
                    <span class="text-3xl sm:text-4xl font-bold text-brand-red"><?= wc_price($sale); ?></span>
                    <span class="text-lg sm:text-xl text-gray-400 line-through"><?= wc_price($regular); ?></span>
                    <span class="px-3 py-1 bg-brand-red/10 text-brand-red text-xs font-bold rounded-full">-<?= $percent; ?>%</span>
                <?php else: ?>
                    <span class="text-3xl sm:text-4xl font-bold text-brand-green"><?= $product->get_price_html(); ?></span>
                <?php endif; ?>
            </div>

            <?php if ($product->get_short_description()): ?>
                <p class="text-gray-600 leading-relaxed text-sm sm:text-base">
                    <?= $product->get_short_description(); ?>
                </p>
            <?php endif; ?>

            <div class="space-y-3 sm:space-y-4 pt-2">
                <div class="flex items-center gap-4">
                    <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
                        <button class="qty-btn px-3 sm:px-4 py-2.5 sm:py-3 text-gray-500 hover:text-brand-green hover:bg-gray-50 transition-colors text-sm sm:text-base" data-action="minus">−</button>
                        <input type="number" min="1" value="1" class="qty-input w-14 sm:w-16 text-center border-x border-gray-200 py-2.5 sm:py-3 text-sm sm:text-base font-medium bg-white" readonly>
                        <button class="qty-btn px-3 sm:px-4 py-2.5 sm:py-3 text-gray-500 hover:text-brand-green hover:bg-gray-50 transition-colors text-sm sm:text-base" data-action="plus">+</button>
                    </div>
                </div>

                <a href="<?= $product->add_to_cart_url(); ?>"
                   class="block w-full text-center py-3.5 sm:py-4 px-8 bg-brand-green text-white font-semibold rounded-xl hover:bg-brand-green/90 hover:shadow-lg hover:shadow-brand-green/20 transition-all duration-300 text-sm sm:text-base"
                   data-product_id="<?= $product->get_id(); ?>">
                    Añadir al carrito
                </a>

                <a href="https://wa.me/584128798885?text=<?= urlencode('Hola, me interesa: ' . get_the_title() . ' - ' . get_permalink()); ?>"
                   target="_blank" rel="noopener"
                   class="flex items-center justify-center gap-2 w-full py-3.5 sm:py-4 px-8 bg-[#25D366] text-white font-semibold rounded-xl hover:bg-[#25D366]/90 hover:shadow-lg hover:shadow-[#25D366]/20 transition-all duration-300 text-sm sm:text-base">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Consultar por WhatsApp
                </a>
            </div>

            <div class="pt-6 border-t border-gray-100">
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-500">
                    <span class="flex items-center gap-2"><?= farmapaz_icon('shield'); ?> Producto original</span>
                    <span class="flex items-center gap-2"><?= farmapaz_icon('truck'); ?> Delivery disponible</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <?php if ($product->get_description()): ?>
    <div class="mt-16 lg:mt-24 max-w-3xl fade-in-up">
        <h2 class="text-xl sm:text-2xl font-bold text-brand-blue mb-6">Descripción</h2>
        <div class="text-gray-600 leading-relaxed text-sm sm:text-base space-y-4">
            <?php the_content(); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Related Products -->
    <?php
    $related_cats = get_the_terms(get_the_ID(), 'product_cat');
    if ($related_cats && !is_wp_error($related_cats)):
        $cat_slug = $related_cats[0]->slug;
        farmapaz_product_carousel([
            'title'       => 'Productos Relacionados',
            'subtitle'    => 'También te puede interesar',
            'badge'       => '',
            'category'    => $cat_slug,
            'limit'       => 10,
            'orderby'     => 'rand',
            'exclude'     => [get_the_ID()],
        ]);
    endif;
    ?>

    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
