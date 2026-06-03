<?php
/**
 * Single post template
 */

get_header(); ?>

<div class="container-farmapaz py-16 lg:py-24">
    <?php if (have_posts()): while (have_posts()): the_post(); ?>
        <article class="max-w-3xl mx-auto fade-in-up">
            <?php if (has_post_thumbnail()): ?>
                <div class="mb-8 rounded-2xl overflow-hidden">
                    <?php the_post_thumbnail('large', ['class' => 'w-full h-auto object-cover']); ?>
                </div>
            <?php endif; ?>
            <div class="text-sm text-gray-400 mb-4">
                <span><?= get_the_date(); ?></span>
                <span class="mx-2">·</span>
                <span><?php the_category(', '); ?></span>
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-brand-blue mb-6 leading-tight">
                <?php the_title(); ?>
            </h1>
            <div class="text-gray-600 leading-relaxed text-base sm:text-lg space-y-4">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>
