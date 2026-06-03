<?php
/**
 * Search results template
 */

get_header(); ?>

<div class="container-farmapaz py-16">
    <div class="max-w-3xl mx-auto text-center mb-12">
        <h1 class="text-3xl lg:text-4xl font-bold text-brand-blue">
            <?php printf('Resultados para: %s', get_search_query()); ?>
        </h1>
        <p class="text-gray-500 mt-4"><?= $wp_query->found_posts; ?> producto(s) encontrado(s)</p>
    </div>

    <?php if (have_posts()): ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
            <?php while (have_posts()): the_post();
                global $product;
                $image = get_the_post_thumbnail_url(get_the_ID(), 'full');
                if ($product && !$product->is_in_stock()) continue;
            ?>
                <div class="product-card group">
                    <div class="relative aspect-square bg-gray-50 overflow-hidden">
                        <?php if ($product && $product->is_on_sale()): ?>
                            <span class="absolute top-3 left-3 z-10 bg-brand-red text-white text-[10px] font-bold px-2.5 py-1 rounded-full">Oferta</span>
                        <?php endif; ?>
                        <?php if ($image): ?>
                            <img src="<?= $image; ?>" alt="<?php the_title(); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <h3 class="text-sm font-medium text-gray-800 line-clamp-2 mb-1">
                            <a href="<?php the_permalink(); ?>" class="hover:text-brand-green transition-colors"><?php the_title(); ?></a>
                        </h3>
                        <?php if ($product): ?>
                            <div class="text-lg font-bold text-brand-green mt-2"><?= $product->get_price_html(); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16">
            <p class="text-gray-500">No se encontraron productos. Intenta con otros términos de búsqueda.</p>
            <a href="<?= esc_url(home_url('/')); ?>" class="btn-primary mt-6 inline-block">Volver al inicio</a>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
