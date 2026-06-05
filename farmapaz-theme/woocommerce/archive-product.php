<?php
/**
 * WooCommerce Archive — Tienda Futurista v3
 */

global $wp_query;

// Read filter params from URL
$current_cats    = isset($_GET['cat']) ? array_map('sanitize_title', (array)$_GET['cat']) : [];
$current_brands  = isset($_GET['brand']) ? array_map('sanitize_title', (array)$_GET['brand']) : [];
$current_min     = isset($_GET['min_price']) ? floatval($_GET['min_price']) : '';
$current_max     = isset($_GET['max_price']) ? floatval($_GET['max_price']) : '';
$current_s       = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$current_orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date';

get_header(); ?>

<div class="shop-wrap">
    <!-- Hero Section -->
    <div class="shop-hero">
        <div class="shop-hero-bg"></div>
        <div class="shop-hero-orb shop-hero-orb-1"></div>
        <div class="shop-hero-orb shop-hero-orb-2"></div>
        <div class="shop-hero-orb shop-hero-orb-3"></div>
        <div class="container-farmapaz shop-hero-content">
            <div class="shop-hero-text">
                <span class="shop-hero-pill">Tienda Farmapaz</span>
                <h1 class="shop-hero-title"><?php woocommerce_page_title(); ?></h1>
                <div id="shopHeroIndicator">
                <?php
                $current_cat_obj = null;
                $current_brand_obj = null;
                if (is_product_category()) {
                    $current_cat_obj = get_queried_object();
                } elseif (!empty($current_cats)) {
                    $current_cat_obj = get_term_by('slug', $current_cats[0], 'product_cat');
                }
                if (!empty($current_brands)) {
                    $current_brand_obj = get_term_by('slug', $current_brands[0], 'product_brand');
                }
                ?>
                <?php if ($current_cat_obj): ?>
                <div class="shop-hero-indicator">
                    <span class="shop-hero-indicator-icon" style="color: #5A7D43;">
                        <?= farmapaz_cat_icon($current_cat_obj->slug); ?>
                    </span>
                    <span class="shop-hero-indicator-label">Categoría:</span>
                    <span class="shop-hero-indicator-value"><?= esc_html(farmapaz_cat_display_name($current_cat_obj->slug)); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($current_brand_obj): ?>
                <div class="shop-hero-indicator">
                    <svg class="shop-hero-indicator-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4h6m0 0v6m0-6L14 14m-4-4l-4 4m8 0l-4 4"/></svg>
                    <span class="shop-hero-indicator-label">Marca:</span>
                    <span class="shop-hero-indicator-value"><?= esc_html($current_brand_obj->name); ?></span>
                </div>
                <?php endif; ?>
                </div>
                <p class="shop-hero-sub">
                    <?php printf('%d productos encontrados', $wp_query->found_posts); ?>
                </p>
            </div>
        </div>
    </div>

    <div class="container-farmapaz shop-body">
        <!-- Active Filters -->
        <?php farmapaz_render_active_filters($current_cats, $current_brands, $current_min, $current_max, $current_s, $current_orderby); ?>

        <div class="shop-layout">
            <!-- Sidebar Filters -->
            <aside class="shop-sidebar" id="shopSidebar">
                <div class="shop-sidebar-inner">
                    <div class="shop-sidebar-header">
                        <h2 class="shop-sidebar-title">Filtros</h2>
                        <button class="shop-sidebar-close" id="closeSidebar" aria-label="Cerrar filtros">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <?php farmapaz_render_filters($current_cats, $current_brands, $current_min, $current_max, $current_s); ?>
                </div>
            </aside>

            <!-- Products Column -->
            <div class="shop-products-col">
                <!-- Toolbar -->
                <div class="shop-toolbar">
                    <button class="shop-filter-toggle" id="toggleSidebar">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filtros
                    </button>
                    <span class="shop-count" id="shopCount"><?= $wp_query->found_posts; ?> productos</span>
                    <div class="shop-sorting">
                        <label for="shopOrderby" class="shop-sorting-label">Ordenar</label>
                        <select id="shopOrderby" class="shop-sorting-select">
                            <option value="date" <?= $current_orderby === 'date' ? 'selected' : ''; ?>>Nuevos primero</option>
                            <option value="price" <?= $current_orderby === 'price' ? 'selected' : ''; ?>>Menor precio</option>
                            <option value="price-desc" <?= $current_orderby === 'price-desc' ? 'selected' : ''; ?>>Mayor precio</option>
                            <option value="popularity" <?= $current_orderby === 'popularity' ? 'selected' : ''; ?>>Más populares</option>
                        </select>
                    </div>
                </div>

                <!-- Loading Overlay -->
                <div class="shop-loading" id="shopLoading">
                    <div class="shop-spinner"></div>
                </div>

                <!-- Products Grid -->
                <div class="shop-grid" id="shopGrid">
                    <?php if (woocommerce_product_loop() && wc_get_loop_prop('total')): ?>
                        <?php while (have_posts()): the_post(); ?>
                            <?php wc_get_template_part('content', 'product'); ?>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="shop-empty">
                            <div class="shop-empty-icon">
                                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <p class="shop-empty-text">No se encontraron productos con estos filtros.</p>
                            <a href="<?= get_permalink(wc_get_page_id('shop')); ?>" class="shop-empty-link">Limpiar filtros</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Infinite Scroll Sentinel -->
                <div id="shopSentinel" style="height:1px;"></div>
                <div id="shopLoader" class="shop-loader" style="display:none;">
                    <div class="shop-spinner"></div>
                    <span>Cargando más productos...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sidebar overlay for mobile -->
<div class="shop-overlay" id="shopOverlay"></div>

<?php get_footer(); ?>
