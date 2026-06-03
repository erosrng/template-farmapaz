<?php
/**
 * Page template
 */

get_header(); ?>

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
