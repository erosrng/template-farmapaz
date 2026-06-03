<section class="relative overflow-hidden w-full" style="background: #293683;">
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0" data-speed="0.15">
        <div class="absolute -top-20 -right-20 w-64 h-64 rounded-full" style="background: radial-gradient(circle, rgba(254,171,13,0.15), transparent);"></div>
        <div class="absolute -bottom-10 -left-10 w-48 h-48 rounded-full" style="background: radial-gradient(circle, rgba(90,125,67,0.15), transparent);"></div>
        <div class="absolute top-1/3 left-1/2 w-32 h-32 rounded-full" style="background: radial-gradient(circle, rgba(255,255,255,0.05), transparent);"></div>
    </div>

    <div class="relative z-10 w-full">
        <?php
        $banner_files = glob(get_template_directory() . '/assets/images/banner*.*');
        $total = count($banner_files);
        ?>

        <!-- ===== MOBILE: Single merged carousel with ALL banners ===== -->
        <div class="hero-slider lg:hidden relative w-full overflow-hidden" style="min-height: 280px; height: 65vw; max-height: 450px;">
            <?php
            $all_banners = $banner_files;
            shuffle($all_banners);
            $mi = 0;
            foreach ($all_banners as $path):
                $url = get_template_directory_uri() . '/assets/images/' . basename($path);
                $mi++;
            ?>
            <div class="hero-slide absolute inset-0 w-full h-full <?= $mi === 1 ? 'active' : ''; ?>">
                <img src="<?= $url; ?>" alt="" class="w-full h-full object-contain" style="background: #09146E;" <?= $mi === 1 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
            </div>
            <?php endforeach; ?>
            <?php if ($mi > 1): ?>
            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20 flex items-center gap-1.5">
                <?php for ($i = 0; $i < $mi; $i++): ?>
                <button class="hero-dot <?= $i === 0 ? 'active' : ''; ?>"
                        data-slide="<?= $i; ?>" aria-label="Slide <?= $i + 1; ?>"></button>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- ===== DESKTOP: 3-column grid with separate sliders ===== -->
        <div class="hidden lg:grid lg:grid-cols-3 gap-3 p-3">

            <!-- LEFT: Banner Carousel (66%) -->
            <div class="hero-slider col-span-2 relative rounded-2xl overflow-hidden group"
                 style="min-height: 300px; box-shadow: 0 8px 40px rgba(9,20,110,0.4);">

                <?php
                $slide_index = 0;
                foreach ($banner_files as $path):
                    $url = get_template_directory_uri() . '/assets/images/' . basename($path);
                    $slide_index++;
                ?>
                <div class="hero-slide absolute inset-0 w-full h-full <?= $slide_index === 1 ? 'active' : ''; ?>">
                    <img src="<?= $url; ?>" alt="" class="w-full h-full object-cover object-center" <?= $slide_index === 1 ? 'fetchpriority="high"' : ''; ?>>
                </div>
                <?php endforeach; ?>

                <?php if ($slide_index > 1): ?>
                <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
                    <?php for ($i = 0; $i < $slide_index; $i++): ?>
                    <button class="hero-dot <?= $i === 0 ? 'active' : ''; ?>"
                            data-slide="<?= $i; ?>" aria-label="Slide <?= $i + 1; ?>"></button>
                    <?php endfor; ?>
                </div>
                <button class="hero-prev absolute left-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/20 hover:bg-white/40 backdrop-blur-sm flex items-center justify-center transition-all opacity-0 group-hover:opacity-100" aria-label="Anterior">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button class="hero-next absolute right-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/20 hover:bg-white/40 backdrop-blur-sm flex items-center justify-center transition-all opacity-0 group-hover:opacity-100" aria-label="Siguiente">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <?php endif; ?>
            </div>

            <!-- RIGHT: 2 Banner Sliders stacked -->
            <div class="flex flex-col gap-3" style="min-height: 300px;">
                <?php
                $right_banners = $banner_files;
                shuffle($right_banners);
                $half = max(1, ceil($total / 2));
                $top_banners = array_slice($right_banners, 0, $half);
                $bottom_banners = array_slice($right_banners, $half);
                if (count($top_banners) < 2) $top_banners = array_merge($top_banners, $banner_files);
                if (count($bottom_banners) < 2) $bottom_banners = array_merge($bottom_banners, $banner_files);
                ?>

                <!-- Right Top -->
                <div class="hero-slider flex-1 relative rounded-xl overflow-hidden group" style="box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                    <?php $ti = 0; foreach ($top_banners as $path):
                        $url = get_template_directory_uri() . '/assets/images/' . basename($path); $ti++;
                    ?>
                    <div class="hero-slide absolute inset-0 w-full h-full <?= $ti === 1 ? 'active' : ''; ?>">
                        <img src="<?= $url; ?>" alt="" class="w-full h-full object-cover object-center" loading="lazy">
                    </div>
                    <?php endforeach; ?>
                    <?php if ($ti > 1): ?>
                    <div class="absolute bottom-2 left-1/2 -translate-x-1/2 z-20 flex items-center gap-1.5">
                        <?php for ($i = 0; $i < $ti; $i++): ?>
                        <button class="hero-dot <?= $i === 0 ? 'active' : ''; ?>" data-slide="<?= $i; ?>" aria-label="Slide <?= $i + 1; ?>"></button>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right Bottom -->
                <div class="hero-slider flex-1 relative rounded-xl overflow-hidden group" style="box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                    <?php $bi = 0; foreach ($bottom_banners as $path):
                        $url = get_template_directory_uri() . '/assets/images/' . basename($path); $bi++;
                    ?>
                    <div class="hero-slide absolute inset-0 w-full h-full <?= $bi === 1 ? 'active' : ''; ?>">
                        <img src="<?= $url; ?>" alt="" class="w-full h-full object-cover object-center" loading="lazy">
                    </div>
                    <?php endforeach; ?>
                    <?php if ($bi > 1): ?>
                    <div class="absolute bottom-2 left-1/2 -translate-x-1/2 z-20 flex items-center gap-1.5">
                        <?php for ($i = 0; $i < $bi; $i++): ?>
                        <button class="hero-dot <?= $i === 0 ? 'active' : ''; ?>" data-slide="<?= $i; ?>" aria-label="Slide <?= $i + 1; ?>"></button>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>
