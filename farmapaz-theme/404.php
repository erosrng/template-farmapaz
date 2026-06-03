<?php
/**
 * 404 template
 */

get_header(); ?>

<div class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center px-4">
        <span class="text-8xl font-black text-brand-green/20">404</span>
        <h1 class="text-3xl lg:text-4xl font-bold text-brand-blue mt-4">Página no encontrada</h1>
        <p class="text-gray-500 mt-4 max-w-md mx-auto">La página que buscas no existe o ha sido movida.</p>
        <div class="mt-8 flex flex-wrap gap-4 justify-center">
            <a href="<?= esc_url(home_url('/')); ?>" class="btn-primary">Ir al inicio</a>
            <a href="<?= get_permalink(wc_get_page_id('shop')); ?>" class="btn-outline">Ver productos</a>
        </div>
    </div>
</div>

<?php get_footer(); ?>
