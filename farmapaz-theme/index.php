<?php
/**
 * Index - Default template
 */

get_header(); ?>

<div class="container-farmapaz py-16">
    <div class="grid lg:grid-cols-3 gap-12">
        <div class="lg:col-span-2">
            <?php if (have_posts()): while (have_posts()): the_post(); ?>
                <article <?php post_class('mb-12 pb-12 border-b border-gray-100 last:border-0'); ?>>
                    <?php if (has_post_thumbnail()): ?>
                        <div class="mb-6 rounded-2xl overflow-hidden">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('large', ['class' => 'w-full h-auto object-cover']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <h2 class="text-2xl font-bold text-brand-blue mb-3">
                        <a href="<?php the_permalink(); ?>" class="hover:text-brand-green transition-colors"><?php the_title(); ?></a>
                    </h2>
                    <div class="text-sm text-gray-400 mb-4">
                        <span><?= get_the_date(); ?></span>
                    </div>
                    <div class="text-gray-600 leading-relaxed">
                        <?php the_excerpt(); ?>
                    </div>
                    <a href="<?php the_permalink(); ?>" class="inline-flex items-center gap-2 mt-4 text-brand-green font-medium hover:text-brand-green/80 transition-colors text-sm">
                        Leer más <?= farmapaz_icon('chevron-right'); ?>
                    </a>
                </article>
            <?php endwhile; endif; ?>
        </div>
        <aside class="lg:col-span-1">
            <?php get_sidebar(); ?>
        </aside>
    </div>
</div>

<?php get_footer(); ?>
