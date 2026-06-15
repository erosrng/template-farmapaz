<?php
defined('ABSPATH') || exit;

$downloads = WC()->customer->get_downloadable_products();
?>

<div class="farmapaz-downloads-wrap">
    <div class="farmapaz-downloads-header">
        <h1 class="farmapaz-downloads-title">Descargas</h1>
        <p class="farmapaz-downloads-subtitle">Archivos digitales disponibles para descargar.</p>
    </div>

    <?php if (!empty($downloads)): ?>
    <div class="farmapaz-downloads-table-wrap">
        <table class="farmapaz-downloads-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Descargas restantes</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($downloads as $download): ?>
                <tr>
                    <td>
                        <div class="farmapaz-downloads-product">
                            <div class="farmapaz-downloads-product-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <span class="farmapaz-downloads-product-name"><?= esc_html($download['product_name']); ?></span>
                        </div>
                    </td>
                    <td class="farmapaz-downloads-count">
                        <?= is_numeric($download['downloads_remaining']) ? esc_html($download['downloads_remaining']) : '&infin;'; ?>
                    </td>
                    <td class="farmapaz-downloads-actions">
                        <a href="<?= esc_url($download['download_url']); ?>"
                           class="farmapaz-downloads-btn"
                           rel="nofollow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Descargar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="farmapaz-downloads-empty">
        <div class="farmapaz-downloads-empty-icon">
            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: rgba(9,20,110,0.2);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h3 class="farmapaz-downloads-empty-title">Sin descargas</h3>
        <p class="farmapaz-downloads-empty-text">Aún no tienes productos digitales disponibles para descargar.</p>
        <a href="<?= esc_url(get_permalink(wc_get_page_id('shop'))); ?>"
           class="farmapaz-downloads-shop-btn">
            Ir a la Tienda
        </a>
    </div>
    <?php endif; ?>
</div>
