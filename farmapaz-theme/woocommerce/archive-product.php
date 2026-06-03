<?php
/**
 * WooCommerce Archive - Shop page v2
 */

get_header(); ?>

<div class="container-farmapaz py-8 lg:py-12">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 fade-in-up">
        <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-brand-blue">
                <?php woocommerce_page_title(); ?>
            </h1>
            <?php if (have_posts()): ?>
                <p class="text-gray-500 text-sm sm:text-base mt-1">
                    <?php
                    global $wp_query;
                    printf('%d producto(s) encontrado(s)', $wp_query->found_posts);
                    ?>
                </p>
            <?php endif; ?>
        </div>
        <div class="mt-4 sm:mt-0">
            <?php woocommerce_catalog_ordering(); ?>
        </div>
    </div>

    <div class="grid lg:grid-cols-4 gap-8">
        <!-- Sidebar Filters -->
        <aside class="lg:col-span-1 fade-in-up">
            <div class="sticky top-24">
                <?php if (is_active_sidebar('shop-sidebar')): ?>
                    <?php dynamic_sidebar('shop-sidebar'); ?>
                <?php else: ?>
                    <div class="bg-gray-50 rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-brand-blue mb-4">Categorías</h3>
                        <?php woocommerce_output_product_categories(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Products -->
        <div class="lg:col-span-3 fade-in-up" style="transition-delay: 0.1s">
            <?php if (woocommerce_product_loop()): ?>

                <?php if (wc_get_loop_prop('total')): ?>
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
                    <?php while (have_posts()): the_post(); ?>
                        <?php wc_get_template_part('content', 'product'); ?>
                    <?php endwhile; ?>
                    </div>
                <?php endif; ?>

                <?php woocommerce_pagination(); ?>
            <?php else: ?>
                <div class="text-center py-16">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <p class="text-gray-500 text-lg">No se encontraron productos en esta categoría.</p>
                    <a href="<?= get_permalink(wc_get_page_id('shop')); ?>" class="btn-primary mt-6 inline-flex items-center gap-2">
                        Ver todos los productos
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
