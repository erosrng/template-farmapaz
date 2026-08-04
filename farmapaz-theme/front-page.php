<?php
/**
 * Front Page - Homepage
 * Las secciones, banners y bloques se configuran en
 * Ajustes > Portada (sin tocar código).
 */

get_header();

get_template_part('template-parts/hero');

$home_cfg  = farmapaz_home_config();
$sections  = farmapaz_home_sections();

foreach ($sections as $sec):
    $args = [
        'title'      => $sec['title'],
        'subtitle'   => $sec['subtitle'],
        'category'   => $sec['category'],
        'limit'      => (int)$sec['limit'],
        'orderby'    => $sec['orderby'],
        'order'      => $sec['order'],
        'layout'     => $sec['layout'],
        'bg_tone'    => $sec['bg_tone'],
        'badge'      => $sec['badge'],
        'force_sale' => !empty($sec['force_sale']),
        'on_sale'    => !empty($sec['on_sale']),
    ];
?>
<section class="section-offscreen">
<?php farmapaz_product_carousel($args); ?>
</section>
<?php endforeach; ?>

<?php if (!empty($home_cfg['show_categories'])): ?>
    <?php get_template_part('template-parts/categories-carousel'); ?>
<?php endif; ?>

<?php if (!empty($home_cfg['show_trust'])): ?>
    <?php get_template_part('template-parts/trust-section'); ?>
<?php endif; ?>

<?php if (!empty($home_cfg['show_promo'])): ?>
    <?php get_template_part('template-parts/promo-banners'); ?>
<?php endif; ?>

<?php get_footer(); ?>
