<?php
/**
 * Archive template
 */

get_header(); ?>

<div class="container-farmapaz py-16">
    <div class="max-w-3xl mx-auto text-center mb-12">
        <h1 class="text-4xl lg:text-5xl font-bold text-brand-blue">
            <?php the_archive_title(); ?>
        </h1>
        <?php the_archive_description('<p class="text-gray-500 mt-4">', '</p>'); ?>
    </div>

    <div class="grid lg:grid-cols-3 gap-12">
        <div class="lg:col-span-2">
            <?php if (have_posts()): while (have_posts()): the_post(); ?>
                <article <?php post_class('mb-10 pb-10 border-b border-gray-100 last:border-0'); ?>>
                    <?php if (has_post_thumbnail()): ?>
                        <div class="mb-6 rounded-2xl overflow-hidden">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('large', ['class' => 'w-full h-auto object-cover']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <h2 class="text-xl font-bold text-brand-blue mb-3">
                        <a href="<?php the_permalink(); ?>" class="hover:text-brand-green transition-colors"><?php the_title(); ?></a>
                    </h2>
                    <div class="text-gray-600 leading-relaxed"><?php the_excerpt(); ?></div>
                    <a href="<?php the_permalink(); ?>" class="inline-flex items-center gap-2 mt-4 text-brand-green font-medium text-sm hover:text-brand-green/80 transition-colors">
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
