<?php
/**
 * Search results template — Futurista, same product grid as the shop.
 */

global $wp_query;
$found    = (int) $wp_query->found_posts;
$query    = get_search_query();
$max_pages = (int) $wp_query->max_num_pages;
$current  = max(1, (int) get_query_var('paged'));

get_header(); ?>

<div class="shop-wrap">
    <div class="shop-hero">
        <div class="shop-hero-bg"></div>
        <div class="shop-hero-orb shop-hero-orb-1"></div>
        <div class="shop-hero-orb shop-hero-orb-2"></div>
        <div class="shop-hero-orb shop-hero-orb-3"></div>
        <div class="container-farmapaz shop-hero-content">
            <div class="shop-hero-text">
                <span class="shop-hero-pill">Búsqueda</span>
                <h1 class="shop-hero-title"><?php printf('Resultados para: "%s"', esc_html($query)); ?></h1>
                <p class="shop-hero-sub">
                    <?php
                    if ($found > 0) {
                        printf('%d producto%s encontrado%s', $found, $found === 1 ? '' : 's', $found === 1 ? '' : 's');
                    } else {
                        echo 'No se encontraron productos';
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>

    <?php if (farmapaz_optica_query_is_optics($query)): ?>
    <div class="container-farmapaz" style="padding-top:18px;">
        <div class="search-cross search-cross--optica">
            <div>
                <strong>¿Buscas lentes o monturas?</strong>
                <span>Estás en Farmacia. En Óptica Farmapaz tenemos monturas, lentes de sol, examen de la vista gratis y pago con Cashea.</span>
            </div>
            <a href="<?= esc_url(add_query_arg('optica_s', $query, farmapaz_optica_catalogo_url())); ?>">
                Buscar en Óptica
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <div class="container-farmapaz shop-body">
        <div class="shop-layout shop-layout-search">
            <div class="shop-products-col">
                <div class="shop-toolbar">
                    <span class="shop-count"><?= $found; ?> producto<?= $found === 1 ? '' : 's'; ?></span>
                </div>

                <div class="shop-loading" id="shopLoading">
                    <div class="shop-spinner"></div>
                </div>

                <div class="shop-grid" id="shopGrid">
                    <?php if ($found > 0 && have_posts()): ?>
                        <?php while (have_posts()): the_post(); ?>
                            <?php wc_get_template_part('content', 'product'); ?>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="shop-empty">
                            <div class="shop-empty-icon">
                                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <p class="shop-empty-text">No se encontraron productos con "<?= esc_html($query); ?>". Prueba con otro término o busca por código.</p>
                            <a href="<?= esc_url(home_url('/')); ?>" class="shop-empty-link">Volver al inicio</a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($max_pages > 1): ?>
                    <nav class="shop-pagination search-pagination">
                        <?php
                        for ($i = 1; $i <= $max_pages; $i++):
                            $active = $i === $current ? ' current' : '';
                            $link = add_query_arg(['s' => $query, 'paged' => $i], home_url('/'));
                            ?>
                            <a class="page-numbers<?= $active; ?>" href="<?= esc_url($link); ?>"><?= $i; ?></a>
                        <?php endfor; ?>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
