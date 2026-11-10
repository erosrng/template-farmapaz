<?php
/**
 * Catálogo de Óptica — /optica/catalogo/
 *
 * Se carga desde page-optica.php mediante rewrite + template_include.
 * Grid paginado, búsqueda, filtro por marca y orden.
 */
if (!defined('ABSPATH')) exit;

get_header();

$theme_uri = get_template_directory_uri();
$catalog_url = farmapaz_optica_catalogo_url();
$brand_data = farmapaz_optica_brand_counts();
$opt_total = (int) $brand_data['total'];
$opt_brands = $brand_data['brands'];
$opt_mapa = farmapaz_optica_map();

$search = isset($_GET['optica_s']) ? sanitize_text_field(wp_unslash($_GET['optica_s'])) : '';
$brand = isset($_GET['marca']) ? sanitize_text_field(wp_unslash($_GET['marca'])) : '';
$orderby = isset($_GET['orderby']) ? sanitize_key(wp_unslash($_GET['orderby'])) : 'title';
$catalog_page = max(1, isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1);
$per_page = 16;

$args = [
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => $per_page,
    'paged'          => $catalog_page,
    'meta_query'     => [['key' => '_farmapaz_catalog_only', 'value' => '1']],
    'ignore_sticky_posts' => true,
];

if ($search !== '') {
    $args['s'] = $search;
}

if ($brand !== '') {
    $codes = [];
    foreach ($opt_mapa as $code => $info) {
        if (trim((string) ($info['marca'] ?? '')) === $brand) $codes[] = $code;
    }
    if (!empty($codes)) {
        $args['meta_query'][] = ['key' => '_optica_sinv', 'value' => $codes, 'compare' => 'IN'];
    } else {
        $args['meta_query'][] = ['key' => '_optica_sinv', 'value' => '__sin_marca__', 'compare' => '='];
    }
}

switch ($orderby) {
    case 'date':
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        break;
    case 'price':
        $args['meta_key'] = '_price';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'ASC';
        break;
    case 'price-desc':
        $args['meta_key'] = '_price';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;
    default:
        $orderby = 'title';
        $args['orderby'] = 'title';
        $args['order'] = 'ASC';
        break;
}

$catalog_query = new WP_Query($args);
$total = (int) $catalog_query->found_posts;
$max_pages = (int) $catalog_query->max_num_pages;

$items = [];
foreach ($catalog_query->posts as $catalog_post) {
    $item = farmapaz_optica_item_data($catalog_post->ID);
    if (!empty($item)) $items[] = $item;
}
wp_reset_postdata();

$sort_options = [
    'title'      => 'Nombre A–Z',
    'date'       => 'Nuevos primero',
    'price'      => 'Precio: menor a mayor',
    'price-desc' => 'Precio: mayor a menor',
];

$base_args = [];
if ($search !== '') $base_args['optica_s'] = $search;
if ($brand !== '') $base_args['marca'] = $brand;
if ($orderby !== 'title') $base_args['orderby'] = $orderby;
$base_url = $base_args ? add_query_arg($base_args, $catalog_url) : $catalog_url;

$pharmacy_search_url = add_query_arg([
    's' => $search,
    'post_type' => 'product',
], home_url('/'));

$page_title = 'Catálogo de monturas';
if ($search !== '') {
    $page_title = 'Resultados para “' . $search . '”';
} elseif ($brand !== '') {
    $page_title = 'Monturas ' . $brand;
}
?>
<div class="optica-page">
    <section class="op-catalog-hero">
        <div class="wrap">
            <a class="op-catalog-hero__back" href="<?= esc_url(home_url('/optica/')); ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M15 19l-7-7 7-7"/></svg>
                Volver a Óptica
            </a>
            <span class="op-eyebrow">Catálogo Óptica</span>
            <h1 class="op-catalog-hero__title"><?= esc_html($page_title); ?></h1>
            <p class="op-catalog-hero__sub">
                <?= $total; ?> modelo<?= $total === 1 ? '' : 's'; ?> disponible<?= $total === 1 ? '' : 's'; ?>
                <?php if ($search !== ''): ?> para tu búsqueda<?php endif; ?>
                <?php if ($brand !== ''): ?> en <?= esc_html($brand); ?><?php endif; ?>
            </p>
        </div>
    </section>

    <section class="op-catalog-page">
        <div class="wrap">
            <?php if ($search !== '' && farmapaz_optica_query_is_pharmacy($search)): ?>
                <div class="op-cross-search op-cross-search--farmacia">
                    <div>
                        <strong>¿Buscas productos de farmacia?</strong>
                        <span>Estás en el catálogo de Óptica. Podemos buscar “<?= esc_html($search); ?>” en la tienda de farmacia.</span>
                    </div>
                    <a class="op-btn op-btn--primary" href="<?= esc_url($pharmacy_search_url); ?>">Buscar en Farmacia</a>
                </div>
            <?php endif; ?>

            <form class="op-catalog-filters" method="get" action="<?= esc_url($catalog_url); ?>">
                <div class="op-catalog-filters__search">
                    <label class="screen-reader-text" for="optica-search">Buscar monturas</label>
                    <input type="search" id="optica-search" name="optica_s" value="<?= esc_attr($search); ?>" placeholder="Buscar montura, marca o modelo…" autocomplete="off">
                    <button type="submit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Buscar
                    </button>
                </div>
                <div class="op-catalog-filters__selects">
                    <label>
                        <span>Marca</span>
                        <select name="marca" onchange="this.form.submit()">
                            <option value="">Todas las marcas</option>
                            <?php foreach ($opt_brands as $brand_name => $brand_count): ?>
                                <option value="<?= esc_attr($brand_name); ?>" <?= selected($brand, $brand_name, false); ?>>
                                    <?= esc_html($brand_name); ?> (<?= (int) $brand_count; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>Ordenar</span>
                        <select name="orderby" onchange="this.form.submit()">
                            <?php foreach ($sort_options as $value => $label): ?>
                                <option value="<?= esc_attr($value); ?>" <?= selected($orderby, $value, false); ?>><?= esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            </form>

            <?php if ($search !== '' || $brand !== '' || $orderby !== 'title'): ?>
                <div class="op-active-filters">
                    <?php if ($search !== ''): ?>
                        <a class="op-filter-chip" href="<?= esc_url(remove_query_arg('optica_s', $base_url)); ?>">
                            Búsqueda: “<?= esc_html($search); ?>”
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if ($brand !== ''): ?>
                        <a class="op-filter-chip" href="<?= esc_url(remove_query_arg('marca', $base_url)); ?>">
                            Marca: <?= esc_html($brand); ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if ($orderby !== 'title'): ?>
                        <a class="op-filter-chip" href="<?= esc_url(remove_query_arg('orderby', $base_url)); ?>">
                            <?= esc_html($sort_options[$orderby] ?? 'Orden'); ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </a>
                    <?php endif; ?>
                    <a class="op-clear-filters" href="<?= esc_url($catalog_url); ?>">Limpiar filtros</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($items)): ?>
                <div class="op-grid-products op-grid-products--catalog">
                    <?php foreach ($items as $item): ?>
                        <?php get_template_part('template-parts/optica-card', null, ['item' => $item]); ?>
                    <?php endforeach; ?>
                </div>

                <?php if ($max_pages > 1): ?>
                    <?php
                    $pagination = paginate_links([
                        'base'      => add_query_arg('pagina', '%#%', $base_url),
                        'format'    => '',
                        'current'   => $catalog_page,
                        'total'     => $max_pages,
                        'mid_size'  => 1,
                        'end_size'  => 1,
                        'prev_text' => '‹',
                        'next_text' => '›',
                        'type'      => 'array',
                    ]);
                    if (!empty($pagination)):
                    ?>
                    <nav class="op-pagination" aria-label="Paginación del catálogo">
                        <?php foreach ($pagination as $link): ?>
                            <?= $link; ?>
                        <?php endforeach; ?>
                    </nav>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else: ?>
                <div class="op-empty-state">
                    <span class="op-empty-state__icon">
                        <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12Z"/><circle cx="12" cy="12" r="2.8"/></svg>
                    </span>
                    <h2>No encontramos monturas con esos filtros</h2>
                    <p>Prueba con otra marca, modelo o código, o mira todo el catálogo.</p>
                    <a class="op-btn op-btn--primary" href="<?= esc_url($catalog_url); ?>">Ver todo el catálogo</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="op-catalog-help">
        <div class="wrap">
            <div class="op-catalog-help__card">
                <div>
                    <span class="op-eyebrow">¿No encuentras tu modelo?</span>
                    <h2>Te ayudamos a conseguirlo</h2>
                    <p>Escríbenos por WhatsApp y consulta disponibilidad, precios y opciones de pago con Cashea.</p>
                </div>
                <a class="op-btn op-btn--whatsapp"
                   href="<?= esc_url(farmapaz_whatsapp_url('Hola, quiero consultar por una montura de Óptica Farmapaz.')); ?>"
                   target="_blank"
                   rel="noopener">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a9.9 9.9 0 0 0-8.5 15L2 22l5.2-1.4A9.9 9.9 0 1 0 12 2Zm4.6 12.1c-.3-.1-1.6-.8-1.8-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1-.2.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.2-.4 0-.5.2-.7l.4-.5c.1-.2.1-.3 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2s.9 2.5 1 2.7c.1.2 1.8 2.8 4.4 3.9 1.6.7 2.2.7 3 .6.5-.1 1.6-.6 1.8-1.3.2-.6.2-1.1.2-1.2-.1-.2-.2-.2-.4-.3Z"/></svg>
                    Consultar por WhatsApp
                </a>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>
