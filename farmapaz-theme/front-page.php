<?php
/**
 * Front Page - Homepage v3
 */

get_header(); ?>

<?php get_template_part('template-parts/hero'); ?>

<section class="section-offscreen">
<?php farmapaz_product_carousel([
    'title'       => 'Ahorra con Farmapaz',
    'subtitle'    => 'Ofertas exclusivas y descuentos especiales',
    'category'    => 'ahorra-con-farmapaz',
    'limit'       => 20,
    'orderby'     => 'rand',
    'force_sale'  => true,
    'layout'      => 'carousel',
    'bg_tone'     => 'brand-blue',
]); ?>
</section>

<section class="section-offscreen">
<?php farmapaz_product_carousel([
    'title'       => 'Belleza y Cuidado Personal',
    'subtitle'    => 'Dermocosmetica, maquillaje y cuidado diario',
    'category'    => 'cuidado-personal-y-dermocosmeticos',
    'limit'       => 12,
    'orderby'     => 'rand',
    'layout'      => 'grid',
    'bg_tone'     => 'lighter',
]); ?>
</section>

<?php get_template_part('template-parts/categories-carousel'); ?>

<section class="section-offscreen">
<?php farmapaz_product_carousel([
    'title'       => 'Alimentos y Bebidas',
    'subtitle'    => 'Snacks, nutricion y bebidas para toda la familia',
    'category'    => 'alimentos-y-bebidas',
    'limit'       => 15,
    'orderby'     => 'date',
    'layout'      => 'carousel',
    'bg_tone'     => 'light',
]); ?>
</section>

<section class="section-offscreen">
<?php farmapaz_product_carousel([
    'title'       => 'Salud y Bienestar',
    'subtitle'    => 'Vitaminas, suplementos y productos para tu salud',
    'category'    => 'salud-y-bienestar',
    'limit'       => 20,
    'orderby'     => 'rand',
    'layout'      => 'grid',
    'bg_tone'     => 'brand-blue',
]); ?>
</section>



<?php get_template_part('template-parts/trust-section'); ?>

<section class="section-offscreen">
<?php farmapaz_product_carousel([
    'title'       => 'Productos para el hogar',
    'subtitle'    => 'Productos destacados para el hogar mascotas y otros',
    'category'    => 'hogar-mascotas-y-otros',
    'limit'       => 15,
    'orderby'     => 'date',
    'order'       => 'DESC',
    'bg_tone'     => 'white',
]); ?>
</section>

<?php get_template_part('template-parts/promo-banners'); ?>

<?php get_footer(); ?>
