<?php
/**
 * Template Name: Óptica
 *
 * Portada de Óptica: hero 3D, marcas, Cashea, carruseles y examen.
 * El catálogo completo vive en /optica/catalogo/ (page-optica-catalogo.php).
 */
if (!defined('ABSPATH')) exit;

get_header();

$theme_uri = get_template_directory_uri();
$opt_catalog_url = farmapaz_optica_catalogo_url();

/* ------------------------------------------------------------------
 * Datos base: IDs, marcas y logos (sin cargar las 338 tarjetas)
 * ------------------------------------------------------------------ */
$opt_brand_data = farmapaz_optica_brand_counts();
$opt_total = (int) $opt_brand_data['total'];
$opt_ids = $opt_brand_data['ids'];
$opt_brands = $opt_brand_data['brands'];
$opt_mapa = farmapaz_optica_map();
$optica_brand_logos = farmapaz_optica_brand_logos();

$opt_brand_list = [];
foreach ($opt_brands as $brand_name => $brand_count) {
    $logo_file = $optica_brand_logos[$brand_name] ?? '';
    $opt_brand_list[] = [
        'name'  => $brand_name,
        'count' => $brand_count,
        'logo'  => $logo_file ? $theme_uri . '/assets/images/optica/brands/' . $logo_file : '',
        'url'   => add_query_arg('marca', $brand_name, $opt_catalog_url),
    ];
}
usort($opt_brand_list, function ($a, $b) {
    $a_has = $a['logo'] !== '';
    $b_has = $b['logo'] !== '';
    if ($a_has !== $b_has) return $a_has ? -1 : 1;
    return $b['count'] <=> $a['count'];
});

// SINV → ID para elegir las monturas del hero.
$by_sinv = [];
if (!empty($opt_ids)) update_meta_cache('post', $opt_ids);
foreach ($opt_ids as $opt_id) {
    $sinv = (string) get_post_meta($opt_id, '_optica_sinv', true);
    if ($sinv !== '' && !isset($by_sinv[$sinv])) {
        $by_sinv[$sinv] = (int) $opt_id;
    }
}

/* ------------------------------------------------------------------
 * Hero: solo 3 monturas destacadas
 * ------------------------------------------------------------------ */
$hero_codes = ['00019C', '00006A', '00029A'];
$hero_items = [];
$hero_used = [];

foreach ($hero_codes as $code) {
    if (!isset($by_sinv[$code])) continue;
    $item = farmapaz_optica_item_data($by_sinv[$code]);
    if (empty($item['has_thumb'])) continue;
    $hero_items[] = $item;
    $hero_used[$item['id']] = true;
}

if (count($hero_items) < 3) {
    foreach ($opt_ids as $opt_id) {
        if (count($hero_items) >= 3) break;
        if (isset($hero_used[$opt_id])) continue;
        $item = farmapaz_optica_item_data($opt_id);
        if (empty($item['has_thumb'])) continue;
        $hero_items[] = $item;
        $hero_used[$item['id']] = true;
    }
}

// Fallback visual si el catálogo aún no está importado.
if (!$hero_items) {
    $demo_images = [
        ['brand' => 'Óptica Farmapaz', 'title' => 'Colección de monturas', 'file' => 'demo-frame-1.webp', 'alt' => 'demo-frame-2.webp'],
        ['brand' => 'Óptica Farmapaz', 'title' => 'Lentes de sol', 'file' => 'demo-frame-2.webp', 'alt' => 'demo-frame-3.webp'],
        ['brand' => 'Óptica Farmapaz', 'title' => 'Estilo y precisión', 'file' => 'demo-frame-3.webp', 'alt' => 'demo-frame-1.webp'],
    ];
    foreach ($demo_images as $i => $demo) {
        $hero_items[] = [
            'id'         => -1 - $i,
            'sinv'       => 'demo-' . $i,
            'title'      => $demo['title'],
            'brand'      => $demo['brand'],
            'permalink'  => $opt_catalog_url,
            'img'        => $theme_uri . '/assets/images/optica/' . $demo['file'],
            'alt_img'    => $theme_uri . '/assets/images/optica/' . $demo['alt'],
            'has_thumb'  => true,
            'price_html' => '',
        ];
    }
}

$hero_count = count($hero_items);
$hero_pos = function ($index, $count) {
    if ($index === 0) return 'active';
    $half = (int) floor($count / 2);
    if ($index <= $half) return $index === 1 ? 'next' : 'next2';
    return $index === $count - 1 ? 'prev' : 'prev2';
};

/* ------------------------------------------------------------------
 * Carrusel de destacadas
 * ------------------------------------------------------------------ */
$featured_codes = ['00019C', '00006A', '00029A', '00007A', '000023', '00008B'];
$featured_items = [];
$featured_used = [];

foreach ($featured_codes as $code) {
    if (!isset($by_sinv[$code])) continue;
    $item = farmapaz_optica_item_data($by_sinv[$code]);
    if (empty($item['has_thumb'])) continue;
    $featured_items[] = $item;
    $featured_used[$item['id']] = true;
}

if (count($featured_items) < 8) {
    foreach ($opt_ids as $opt_id) {
        if (count($featured_items) >= 8) break;
        if (isset($featured_used[$opt_id])) continue;
        $item = farmapaz_optica_item_data($opt_id);
        if (empty($item['has_thumb'])) continue;
        $featured_items[] = $item;
        $featured_used[$item['id']] = true;
    }
}

/* ------------------------------------------------------------------
 * Recién llegadas
 * ------------------------------------------------------------------ */
$new_query = new WP_Query([
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => 8,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post__not_in'   => array_keys($featured_used),
    'meta_query'     => [
        ['key' => '_farmapaz_catalog_only', 'value' => '1'],
        ['key' => '_thumbnail_id', 'compare' => 'EXISTS'],
    ],
]);
$new_items = [];
foreach ($new_query->posts as $new_post) {
    $item = farmapaz_optica_item_data($new_post->ID);
    if (!empty($item['has_thumb'])) $new_items[] = $item;
}
wp_reset_postdata();

$exam_image = !empty($featured_items[0]['img']) ? $featured_items[0]['img'] : $theme_uri . '/assets/images/optica/demo-frame-1.webp';
$exam_title = !empty($featured_items[0]['title']) ? $featured_items[0]['title'] : 'Montura Óptica Farmapaz';
?>

<div class="optica-page">
    <section class="op-hero" aria-labelledby="optica-hero-title">
        <span class="op-hero__beam" aria-hidden="true"></span>

        <div class="wrap op-hero__in">
            <div class="op-hero__copy">
                <span class="op-hero__tag">
                    <span class="op-hero__tag-dot" aria-hidden="true"></span>
                    Examen de la vista gratis
                </span>

                <h1 id="optica-hero-title">
                    EXAMEN<br>
                    <em>De La Vista</em><br>
                    <span class="op-hero__free">Gratis</span>
                    <small>Agenda tu examen optométrico sin costo en cualquiera de nuestras sucursales y estrena montura el mismo día.</small>
                </h1>

                <div class="op-hero__actions">
                    <a class="op-btn op-btn--primary" href="<?= esc_url($opt_catalog_url); ?>">
                        Ver catálogo
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <a class="op-btn op-btn--ghost" href="<?= esc_url(get_permalink(357)); ?>">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="2.8"/></svg>
                        Sucursales
                    </a>
                </div>

                <div class="op-hero__meta">
                    <div>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3 4.5 6v5.3c0 4.4 3.1 8.2 7.5 9.7 4.4-1.5 7.5-5.3 7.5-9.7V6L12 3Z"/><path d="m9.2 12.2 1.9 1.9 3.8-4"/></svg>
                        <?= $opt_total > 0 ? esc_html($opt_total) . ' monturas' : 'Monturas originales'; ?>
                    </div>
                    <div>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12Z"/><circle cx="12" cy="12" r="2.8"/></svg>
                        Asesoría personalizada
                    </div>
                    <div>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="2.8"/></svg>
                        Disponible en sucursales
                    </div>
                </div>
            </div>

            <div class="op-stage" data-op-stage role="region" aria-label="Monturas destacadas de Óptica Farmapaz">
                <span class="op-stage__aura" aria-hidden="true"></span>
                <span class="op-stage__ring" aria-hidden="true"></span>
                <span class="op-stage__floor" aria-hidden="true"></span>

                <div class="op-stage__levitate">
                    <div class="op-stage__tilt" data-op-tilt>
                        <?php foreach ($hero_items as $i => $item): ?>
                            <a class="op-hcard"
                               href="<?= esc_url($item['permalink']); ?>"
                               data-op-card
                               data-pos="<?= esc_attr($hero_pos($i, $hero_count)); ?>"
                               aria-label="<?= esc_attr($item['title'] . ' — ' . $item['brand']); ?>">
                                <span class="op-hcard__img">
                                    <img class="op-hcard__photo op-hcard__photo--main"
                                         src="<?= esc_url($item['img']); ?>"
                                         alt="<?= esc_attr($item['title']); ?>"
                                         <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
                                         decoding="async">
                                    <?php if (!empty($item['alt_img'])): ?>
                                        <img class="op-hcard__photo op-hcard__photo--alt"
                                             src="<?= esc_url($item['alt_img']); ?>"
                                             alt=""
                                             loading="lazy"
                                             decoding="async">
                                    <?php endif; ?>
                                </span>
                                <span class="op-hcard__meta">
                                    <span>
                                        <span class="op-hcard__brand"><?= esc_html($item['brand']); ?></span>
                                        <span class="op-hcard__name"><?= esc_html($item['title']); ?></span>
                                    </span>
                                    <span class="op-hcard__go" aria-hidden="true">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </span>
                                </span>
                                <span class="op-hcard__shine" aria-hidden="true"></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <span class="op-bubble op-bubble--1" aria-hidden="true"></span>
                <span class="op-bubble op-bubble--2" aria-hidden="true"></span>
                <span class="op-bubble op-bubble--3" aria-hidden="true"></span>
                <span class="op-bubble op-bubble--4" aria-hidden="true"></span>
                <span class="op-bubble op-bubble--5" aria-hidden="true"></span>
                <span class="op-bubble op-bubble--6" aria-hidden="true"></span>

                <?php if ($hero_count > 1): ?>
                    <div class="op-stage__ui">
                        <button type="button" class="op-stage__arrow" data-op-prev aria-label="Montura anterior">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <div class="op-stage__dots" aria-label="Seleccionar montura destacada">
                            <?php for ($i = 0; $i < $hero_count; $i++): ?>
                                <button type="button"
                                        class="op-stage__dot<?= $i === 0 ? ' is-active' : ''; ?>"
                                        data-op-dot="<?= $i; ?>"
                                        aria-label="Ver montura <?= $i + 1; ?>"
                                        aria-current="<?= $i === 0 ? 'true' : 'false'; ?>"></button>
                            <?php endfor; ?>
                        </div>
                        <button type="button" class="op-stage__arrow" data-op-next aria-label="Siguiente montura">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="op-brands" aria-label="Ver monturas por marca">
        <div class="wrap">
            <div class="op-brands__head">
                <span class="op-brands__label">Marcas destacadas</span>
                <span class="op-brands__hint">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    Toca una marca para ver sus modelos
                </span>
            </div>
            <div class="op-brands__rail" id="optica-brands" role="group" aria-label="Marcas de monturas">
                <a class="op-brand is-active" href="<?= esc_url($opt_catalog_url); ?>" aria-label="Ver todo el catálogo">
                    <span class="op-brand__mark">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="6.5" cy="14" r="3.5"/><circle cx="17.5" cy="14" r="3.5"/><path d="M10 14h4M3 9l2-3h3M21 9l-2-3h-3"/></svg>
                    </span>
                    <span class="op-brand__name">Ver todas</span>
                    <span class="n"><?= $opt_total; ?></span>
                </a>
                <?php foreach ($opt_brand_list as $opt_brand): ?>
                    <a class="op-brand<?= $opt_brand['logo'] ? ' has-logo' : ''; ?>"
                       href="<?= esc_url($opt_brand['url']); ?>"
                       title="<?= esc_attr($opt_brand['name']); ?>"
                       aria-label="<?= esc_attr('Ver monturas ' . $opt_brand['name']); ?>">
                        <span class="op-brand__mark">
                            <?php if ($opt_brand['logo']): ?>
                                <img src="<?= esc_url($opt_brand['logo']); ?>"
                                     alt=""
                                     loading="lazy"
                                     decoding="async">
                            <?php else: ?>
                                <span class="op-brand__initial" aria-hidden="true"><?= esc_html(mb_substr($opt_brand['name'], 0, 1)); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="op-brand__name"><?= esc_html($opt_brand['name']); ?></span>
                        <span class="n"><?= (int) $opt_brand['count']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="op-cashea" id="cashea" aria-labelledby="optica-cashea-title">
        <div class="wrap">
            <div class="op-cashea__card">
                <div class="op-cashea__copy">
                    <span class="op-cashea__eyebrow">Paga a tu ritmo</span>
                    <h2 id="optica-cashea-title">Montura + cristales + examen optométrico</h2>
                    <p>Todo incluido. Compra hoy y paga en cuotas sin intereses con Cashea.</p>
                    <div class="op-cashea__actions">
                        <a class="op-btn op-btn--whatsapp"
                           href="<?= esc_url(farmapaz_whatsapp_url('Hola, quiero información de la promo Cashea: montura + cristales + examen optométrico en Óptica Farmapaz.')); ?>"
                           target="_blank"
                           rel="noopener">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a9.9 9.9 0 0 0-8.5 15L2 22l5.2-1.4A9.9 9.9 0 1 0 12 2Zm4.6 12.1c-.3-.1-1.6-.8-1.8-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1-.2.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.2-.4 0-.5.2-.7l.4-.5c.1-.2.1-.3 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2s.9 2.5 1 2.7c.1.2 1.8 2.8 4.4 3.9 1.6.7 2.2.7 3 .6.5-.1 1.6-.6 1.8-1.3.2-.6.2-1.1.2-1.2-.1-.2-.2-.2-.4-.3Z"/></svg>
                            Consultar por WhatsApp
                        </a>
                        <a class="op-btn op-btn--glass" href="<?= esc_url($opt_catalog_url); ?>">
                            Ver catálogo
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                    <small>Disponible en tiendas físicas. Consulta condiciones en sucursal.</small>
                </div>
                <div class="op-cashea__brand">
                    <img src="<?= esc_url($theme_uri); ?>/assets/images/cashea-logo-transparent.webp" alt="Cashea">
                    <span class="op-cashea__note">Compra hoy, paga en cuotas</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($featured_items)): ?>
    <section class="op-featured" aria-labelledby="optica-featured-title">
        <div class="wrap">
            <div class="op-section-head">
                <div>
                    <span class="op-eyebrow">Selección Óptica</span>
                    <h2 class="op-section-title" id="optica-featured-title">Monturas destacadas</h2>
                    <p class="op-section-sub">Una muestra de lo que tenemos en nuestras tiendas. El catálogo completo está a un clic.</p>
                </div>
                <a class="op-link-all" href="<?= esc_url($opt_catalog_url); ?>">
                    Ver catálogo
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>

            <div class="op-rail" data-op-rail>
                <button type="button" class="op-rail__arrow op-rail__arrow--prev" data-op-rail-prev aria-label="Anterior">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M15 19l-7-7 7-7"/></svg>
                </button>
                <div class="op-rail__track" data-op-rail-track>
                    <?php foreach ($featured_items as $item): ?>
                        <div class="op-rail__item">
                            <?php get_template_part('template-parts/optica-card', null, ['item' => $item]); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="op-rail__arrow op-rail__arrow--next" data-op-rail-next aria-label="Siguiente">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="op-exam" aria-labelledby="optica-exam-title">
        <div class="wrap">
            <div class="op-exam__card">
                <div class="op-exam__copy">
                    <span class="op-eyebrow">Examen optométrico</span>
                    <h2 class="op-section-title" id="optica-exam-title">Tu examen de la vista, gratis</h2>
                    <p>Ven a cualquiera de nuestras sucursales, realiza tu examen sin costo y estrena montura el mismo día. Nuestros optometristas te ayudan a elegir la montura y los cristales que mejor se adapten a ti.</p>
                    <ul class="op-exam__list">
                        <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg> Examen de la vista sin costo</li>
                        <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg> Asesoría personalizada</li>
                        <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg> Pago en cuotas con Cashea</li>
                    </ul>
                    <div class="op-exam__actions">
                        <a class="op-btn op-btn--whatsapp"
                           href="<?= esc_url(farmapaz_whatsapp_url('Hola, quiero agendar un examen de la vista gratis en Óptica Farmapaz.')); ?>"
                           target="_blank"
                           rel="noopener">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a9.9 9.9 0 0 0-8.5 15L2 22l5.2-1.4A9.9 9.9 0 1 0 12 2Zm4.6 12.1c-.3-.1-1.6-.8-1.8-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1-.2.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.2-.4 0-.5.2-.7l.4-.5c.1-.2.1-.3 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2s.9 2.5 1 2.7c.1.2 1.8 2.8 4.4 3.9 1.6.7 2.2.7 3 .6.5-.1 1.6-.6 1.8-1.3.2-.6.2-1.1.2-1.2-.1-.2-.2-.2-.4-.3Z"/></svg>
                            Agendar por WhatsApp
                        </a>
                        <a class="op-btn op-btn--glass" href="<?= esc_url($opt_catalog_url); ?>">Ver monturas</a>
                    </div>
                </div>
                <div class="op-exam__visual">
                    <span class="op-exam__ring" aria-hidden="true"></span>
                    <img src="<?= esc_url($exam_image); ?>" alt="<?= esc_attr($exam_title); ?>" loading="lazy" decoding="async">
                    <span class="op-exam__badge">Examen gratis</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($new_items)): ?>
    <section class="op-featured op-featured--new" aria-labelledby="optica-new-title">
        <div class="wrap">
            <div class="op-section-head">
                <div>
                    <span class="op-eyebrow">Recién llegadas</span>
                    <h2 class="op-section-title" id="optica-new-title">Nuevos modelos</h2>
                    <p class="op-section-sub">Las últimas monturas que llegaron a nuestras ópticas.</p>
                </div>
                <a class="op-link-all" href="<?= esc_url(add_query_arg('orderby', 'date', $opt_catalog_url)); ?>">
                    Ver catálogo
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>

            <div class="op-rail" data-op-rail>
                <button type="button" class="op-rail__arrow op-rail__arrow--prev" data-op-rail-prev aria-label="Anterior">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M15 19l-7-7 7-7"/></svg>
                </button>
                <div class="op-rail__track" data-op-rail-track>
                    <?php foreach ($new_items as $item): ?>
                        <div class="op-rail__item">
                            <?php get_template_part('template-parts/optica-card', null, ['item' => $item]); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="op-rail__arrow op-rail__arrow--next" data-op-rail-next aria-label="Siguiente">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="op-cta-wrap">
        <div class="wrap">
            <div class="op-cta">
                <div class="op-cta__copy">
                    <span class="op-eyebrow op-eyebrow--yellow">Examen optométrico</span>
                    <h2>Agenda tu cita y estrena montura el mismo día</h2>
                    <p>Atención personalizada por optometristas certificados en nuestras sucursales Farmapaz.</p>
                </div>
                <a class="op-btn op-btn--yellow"
                   href="https://wa.me/584249139434?text=<?= rawurlencode('Hola, quiero agendar un examen de la vista gratis en Óptica Farmapaz.'); ?>"
                   target="_blank"
                   rel="noopener">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a9.9 9.9 0 0 0-8.5 15L2 22l5.2-1.4A9.9 9.9 0 1 0 12 2Zm4.6 12.1c-.3-.1-1.6-.8-1.8-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1-.2.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.2-.4 0-.5.2-.7l.4-.5c.1-.2.1-.3 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2s.9 2.5 1 2.7c.1.2 1.8 2.8 4.4 3.9 1.6.7 2.2.7 3 .6.5-.1 1.6-.6 1.8-1.3.2-.6.2-1.1.2-1.2-.1-.2-.2-.2-.4-.3Z"/></svg>
                    Agendar por WhatsApp
                </a>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>
