<?php
/**
 * Page template
 */

// Cart & Checkout: full-width with WPBakery content decoding
if (is_cart() || is_checkout()):
    get_header(); ?>
    <div class="container-farmapaz py-8 lg:py-12">
        <?php if (have_posts()): while (have_posts()): the_post(); ?>
            <?php
            $content = get_the_content();
            if (preg_match('/\[vc_raw_html[^\]]*\](\S+?)\[\/vc_raw_html\]/', $content, $m)) {
                $decoded = base64_decode($m[1]);
                if ($decoded) {
                    $decoded = urldecode($decoded);
                    echo do_shortcode($decoded);
                }
            } else {
                echo do_shortcode($content);
            }
            ?>
        <?php endwhile; endif; ?>
    </div>
    <?php get_footer();
    return;
endif;

// My Account: strip WPBakery wrappers, process remaining shortcodes
if (is_account_page()):
    get_header(); ?>
    <div class="container-farmapaz py-8 lg:py-12">
        <?php if (have_posts()): while (have_posts()): the_post(); ?>
            <?php
            $content = get_the_content();
            // Remove all WPBakery shortcodes but keep inner content
            $content = preg_replace('/\[\/?vc_\w+[^\]]*\]/', '', $content);
            echo do_shortcode($content);
            ?>
        <?php endwhile; endif; ?>
    </div>
    <?php get_footer();
    return;
endif;
?>

<?php get_header(); ?>

<div class="container-farmapaz py-16 lg:py-24">
    <?php if (have_posts()): while (have_posts()): the_post(); ?>
        <div class="max-w-3xl mx-auto">
            <h1 class="text-4xl lg:text-5xl font-bold text-brand-blue mb-6"><?php the_title(); ?></h1>
            <div class="prose prose-lg max-w-none text-gray-600 leading-relaxed">
                <?php the_content(); ?>
            </div>
        </div>
    <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>
