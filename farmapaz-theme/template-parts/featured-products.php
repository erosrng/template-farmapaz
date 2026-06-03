<section class="py-16 lg:py-24 bg-gray-50/50">
    <div class="container-farmapaz">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-12 animate-on-scroll">
            <div>
                <span class="inline-block px-4 py-1.5 bg-brand-yellow/20 text-brand-blue text-xs font-semibold rounded-full mb-4">Destacados</span>
                <h2 class="section-title">Productos Destacados</h2>
                <p class="section-subtitle">Los más vendidos y mejor valorados</p>
            </div>
            <a href="<?= get_permalink(wc_get_page_id('shop')); ?>" class="hidden sm:inline-flex items-center gap-2 text-brand-green font-medium hover:text-brand-green/80 transition-colors mt-4 sm:mt-0">
                Ver todos
                <?= farmapaz_icon('chevron-right'); ?>
            </a>
        </div>

        <?php
        $featured = new WP_Query([
            'post_type'      => 'product',
            'posts_per_page' => 8,
            'meta_query'     => [
                'relation' => 'AND',
                ['key' => '_featured', 'value' => 'yes'],
                ['key' => '_stock_status', 'value' => 'instock'],
            ],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        if ($featured->have_posts()):
        ?>
        <div class="products-carousel relative">
            <div class="flex gap-4 lg:gap-6 overflow-x-auto pb-4 scrollbar-hide snap-x snap-mandatory -mx-4 px-4" id="featured-carousel">
                <?php while ($featured->have_posts()): $featured->the_post();
                    global $product;
                    $image = get_the_post_thumbnail_url(get_the_ID(), 'full');
                    $stock_qty = $product->get_stock_quantity();
                    $managing = $product->managing_stock();
                    $low_stock = $managing && $stock_qty !== null && $stock_qty <= 5;
                ?>
                <div class="flex-none w-[220px] sm:w-[240px] lg:w-[260px] snap-start animate-on-scroll">
                    <div class="product-card group h-full flex flex-col">
                        <div class="relative aspect-square bg-gray-50 overflow-hidden">
                            <?php if ($product->is_on_sale()): ?>
                                <span class="sale-badge absolute top-3 left-3 z-10">
                                    -<?= $product->is_on_sale() && $product->get_regular_price() > 0 ? round((($product->get_regular_price() - $product->get_sale_price()) / $product->get_regular_price()) * 100) : 0; ?>%
                                </span>
                            <?php endif; ?>
                            <?php if ($low_stock): ?>
                                <span class="absolute top-3 right-3 z-10 bg-orange-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-full shadow" style="background: #F97316;">
                                    Solo por tienda
                                </span>
                            <?php endif; ?>
                            <?php if ($image): ?>
                                <img src="<?= $image; ?>" alt="<?php the_title(); ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors"></div>
                        </div>
                        <div class="p-4 flex flex-col flex-1">
                            <h3 class="woocommerce-loop-product__title text-sm font-medium text-gray-800 line-clamp-2 mb-1">
                                <a href="<?php the_permalink(); ?>" class="hover:text-brand-green transition-colors">
                                    <?php the_title(); ?>
                                </a>
                            </h3>
                            <div class="mt-auto pt-3">
                                <div class="price text-lg font-bold text-brand-green">
                                    <?= $product->get_price_html(); ?>
                                </div>
                                <?php if ($low_stock): ?>
                                    <a href="https://wa.me/584128798885?text=Consultar%20disponibilidad%3A%20<?= urlencode(get_the_title()); ?>"
                                       target="_blank"
                                       class="block mt-2 w-full text-center py-2.5 px-4 text-white text-sm font-medium rounded-xl transition-colors"
                                       style="background: #F97316;">
                                        Consultar disponible
                                    </a>
                                <?php else: ?>
                                    <a href="<?= $product->add_to_cart_url(); ?>"
                                       class="block mt-2 w-full text-center py-2.5 px-4 bg-brand-green text-white text-sm font-medium rounded-xl hover:bg-brand-green/90 transition-colors"
                                       data-product_id="<?= $product->get_id(); ?>">
                                        Añadir al carrito
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <!-- Carousel controls -->
            <button class="carousel-prev absolute -left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white shadow-lg flex items-center justify-center hover:bg-gray-50 transition-all hidden lg:flex" aria-label="Anterior">
                <?= farmapaz_icon('chevron-left'); ?>
            </button>
            <button class="carousel-next absolute -right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white shadow-lg flex items-center justify-center hover:bg-gray-50 transition-all hidden lg:flex" aria-label="Siguiente">
                <?= farmapaz_icon('chevron-right'); ?>
            </button>
        </div>
        <?php endif; ?>
    </div>
</section>
