<section class="relative overflow-hidden py-14 sm:py-16 lg:py-20 section-offscreen futuristic-section futuristic-brand-blue" style="background: linear-gradient(135deg, #09146E 0%, #0c1fa0 40%, #09146E 100%);">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8 sm:mb-10 fade-in-up">
            <div>
                <span class="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-full mb-3" style="background: rgba(255,255,255,0.12); color: #FEAB0D; backdrop-filter: blur(4px);">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Categorías
                </span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold" style="color: #FEAB0D;">Explora por categoria</h2>
                <p class="text-sm sm:text-base mt-1" style="color: rgba(255,255,255,0.6);">Encuentra todo lo que necesitas para tu salud y bienestar</p>
            </div>
            <a href="<?= get_permalink(wc_get_page_id('shop')); ?>" class="hidden sm:inline-flex items-center gap-1.5 font-medium text-sm hover:underline hover:text-white transition-colors" style="color: #FEAB0D;">
                Ver todas
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <?php
        $categories = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => 0,
            'number'     => 12,
            'orderby'    => 'count',
            'order'      => 'DESC',
        ]);

        if (!empty($categories) && !is_wp_error($categories)):
        ?>
        <div class="flex gap-5 sm:gap-6 overflow-x-auto pb-6 snap-x snap-mandatory scrollbar-hide scroll-smooth product-carousel-track stagger-children" data-carousel="categories">
            <?php foreach ($categories as $cat):
                $slug = $cat->slug;
            ?>
            <a href="<?= get_term_link($cat); ?>"
               class="flex-none snap-start group stagger-item" style="width: 170px;">
                <div class="flex flex-col items-center p-6 rounded-2xl card-glass-dark">
                    <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-xl flex items-center justify-center transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 mb-1" style="background: linear-gradient(135deg, rgba(254,171,13,0.12), rgba(255,255,255,0.06)); color: #fff; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 0 20px rgba(254,171,13,0.05);">
                        <?= farmapaz_cat_icon($slug); ?>
                    </div>
                    <span class="text-sm sm:text-base font-semibold mt-3 text-center leading-tight line-clamp-2" style="color: #FEAB0D;">
                        <?= $cat->name; ?>
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium mt-2 px-3 py-1 rounded-full" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.5); border: 1px solid rgba(255,255,255,0.06);">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <?= $cat->count; ?>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <button class="carousel-arrow carousel-arrow-prev carousel-arrow-brand absolute top-1/2 -translate-y-1/2 z-40 w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center cursor-pointer select-none"
            style="left: 8px;"
            data-carousel="categories" aria-label="Anterior">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <button class="carousel-arrow carousel-arrow-next carousel-arrow-brand absolute top-1/2 -translate-y-1/2 z-40 w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center cursor-pointer select-none"
            style="right: 8px;"
            data-carousel="categories" aria-label="Siguiente">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </button>
</section>
