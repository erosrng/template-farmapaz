<?php
/**
 * Front Page - Homepage v3
 * Carousels are configurable from Ajustes de Tienda > Carruseles de la portada
 */

get_header(); ?>

<?php get_template_part('template-parts/hero'); ?>

<?php $farmapaz_home_carousels = farmapaz_home_carousels(); ?>

<?php if (!empty($farmapaz_home_carousels)):
    $farmapaz_carousel_index = 0;
    $farmapaz_total_carousels = count($farmapaz_home_carousels);
    foreach ($farmapaz_home_carousels as $farmapaz_carousel):
        $farmapaz_carousel_index++;
        $farmapaz_carousel = wp_parse_args($farmapaz_carousel, [
            'title'    => 'Productos',
            'subtitle' => '',
            'category' => '',
            'limit'    => 12,
            'orderby'  => 'date',
            'order'    => 'DESC',
            'force_sale' => false,
            'layout'   => 'carousel',
            'bg_tone'  => 'light',
        ]);
        ?>
        <section class="section-offscreen">
        <?php farmapaz_product_carousel([
            'title'      => $farmapaz_carousel['title'],
            'subtitle'   => $farmapaz_carousel['subtitle'],
            'category'   => $farmapaz_carousel['category'],
            'limit'      => max(1, (int) $farmapaz_carousel['limit']),
            'orderby'    => $farmapaz_carousel['orderby'],
            'order'      => $farmapaz_carousel['order'],
            'force_sale' => !empty($farmapaz_carousel['force_sale']),
            'layout'     => $farmapaz_carousel['layout'],
            'bg_tone'    => $farmapaz_carousel['bg_tone'],
        ]); ?>
        </section>
        <?php
        if ($farmapaz_carousel_index === 1 && $farmapaz_total_carousels === 1) {
            get_template_part('template-parts/categories-carousel');
        }
        if ($farmapaz_carousel_index === 2) {
            get_template_part('template-parts/categories-carousel');
        }
    endforeach;
else:
    get_template_part('template-parts/categories-carousel');
endif;
?>

<?php get_template_part('template-parts/trust-section'); ?>

<?php get_template_part('template-parts/promo-banners'); ?>

<?php get_footer(); ?>
