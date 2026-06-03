<section id="categories" class="py-16 lg:py-24 bg-white overflow-hidden">
    <div class="container-farmapaz">
        <div class="text-center mb-10 lg:mb-14 fade-in-up">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-brand-green/10 text-brand-green text-xs font-semibold rounded-full mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-green"></span>
                Categorías
            </span>
            <h2 class="section-title">Explora nuestras categorías</h2>
            <p class="section-subtitle mx-auto">Encuentra todo lo que necesitas para tu salud y bienestar</p>
        </div>

        <?php
        $categories = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => 0,
            'number'     => 8,
            'orderby'    => 'count',
            'order'      => 'DESC',
        ]);

        if (!empty($categories) && !is_wp_error($categories)):
        ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
            <?php foreach ($categories as $i => $cat):
                $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
                $image = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '';
                $delay = $i * 0.08;
            ?>
            <a href="<?= get_term_link($cat); ?>"
               class="group relative bg-white rounded-2xl border border-gray-100 p-4 sm:p-5 lg:p-6 hover:shadow-xl hover:border-brand-green/20 hover:-translate-y-1 transition-all duration-500 overflow-hidden stagger-item"
               style="transition-delay: <?= $delay; ?>s">
                <div class="relative z-10">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 rounded-xl sm:rounded-2xl bg-brand-green/10 flex items-center justify-center mb-3 sm:mb-4 group-hover:bg-brand-green/20 group-hover:scale-110 transition-all duration-500">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7 lg:w-8 lg:h-8 text-brand-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-base font-semibold text-gray-900 group-hover:text-brand-green transition-colors">
                        <?= $cat->name; ?>
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-400 mt-0.5 sm:mt-1"><?= $cat->count; ?> productos</p>
                </div>
                <div class="absolute -bottom-8 -right-8 w-28 h-28 rounded-full bg-brand-green/5 group-hover:bg-brand-green/10 group-hover:scale-150 transition-all duration-700"></div>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-8 sm:mt-10 lg:mt-12 fade-in-up">
            <a href="<?= get_permalink(wc_get_page_id('shop')); ?>"
               class="inline-flex items-center gap-2 px-6 sm:px-8 py-3 sm:py-4 bg-brand-yellow text-brand-blue font-semibold rounded-xl hover:bg-brand-yellow/90 hover:shadow-lg hover:shadow-brand-yellow/20 transition-all duration-300 text-sm sm:text-base group">
                Ver todas las categorías
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>
