<?php
/**
 * WooCommerce Single Product — Alta Gama redesign
 */

get_header(); ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-12">
    <?php
    while (have_posts()): the_post();
        global $product;
        $product_id = $product->get_id();
        $is_sale = $product->is_on_sale() && $product->get_regular_price() > 0;
        $regular = $product->get_regular_price();
        $sale = $product->get_sale_price();
        $percent = 0;
        $is_controlled = farmapaz_is_controlled_product($product_id);
        if ($is_sale && $regular > 0) {
            $percent = round((($regular - $sale) / $regular) * 100);
        }
    ?>

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs sm:text-sm mb-6 sm:mb-8 text-gray-400" aria-label="Breadcrumb">
        <a href="<?= home_url(); ?>" class="hover:text-brand-blue transition-colors">Inicio</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="<?= get_permalink(wc_get_page_id('shop')); ?>" class="hover:text-brand-blue transition-colors">Tienda</a>
        <?php
        $cats = get_the_terms(get_the_ID(), 'product_cat');
        if ($cats):
            foreach (array_slice($cats, 0, 1) as $cat):
        ?>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="<?= get_term_link($cat); ?>" class="hover:text-brand-blue transition-colors"><?= $cat->name; ?></a>
        <?php endforeach; endif; ?>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 truncate max-w-[200px]"><?php the_title(); ?></span>
    </nav>

    <div class="sp-layout">
        <!-- Gallery -->
        <div class="sp-gallery">
            <div class="relative group">
                <?php if ($is_sale): ?>
                    <span class="absolute top-4 left-4 z-10 px-4 py-2 rounded-xl text-sm font-bold text-white"
                          style="background: linear-gradient(135deg, #FF1A27, #dc2626); box-shadow: 0 4px 16px rgba(255,26,39,0.3);">
                        -<?= $percent; ?>%
                    </span>
                <?php endif; ?>
                <div class="rounded-2xl overflow-hidden bg-gray-50/50" style="backdrop-filter: blur(4px);">
                    <img src="<?= farmapaz_product_img_fallback(get_the_post_thumbnail_url(get_the_ID(), 'full')); ?>"
                         alt="<?php the_title(); ?>"
                         class="product-gallery-main w-full h-auto object-cover transition-transform duration-500 group-hover:scale-[1.02]"
                         onerror="this.onerror=null;this.src='<?= get_template_directory_uri(); ?>/assets/images/producto.png'">
                </div>
            </div>
            <?php
            $attachment_ids = $product->get_gallery_image_ids();
            if ($attachment_ids):
            ?>
            <div class="flex gap-3 overflow-x-auto pb-1 scrollbar-hide">
                <?php
                $main_thumb = farmapaz_product_img_fallback(get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'));
                $main_full = farmapaz_product_img_fallback(get_the_post_thumbnail_url(get_the_ID(), 'full'));
                ?>
                <button class="product-gallery-thumb w-14 h-14 sm:w-16 sm:h-16 rounded-xl overflow-hidden flex-shrink-0 transition-all duration-200 hover:ring-2 hover:ring-brand-blue/30 active:scale-95"
                        style="border: 2px solid #09146E;"
                        data-src="<?= esc_attr($main_full); ?>">
                    <img src="<?= esc_attr($main_thumb); ?>" alt=""
                         class="w-full h-full object-cover bg-gray-50"
                         onerror="this.onerror=null;this.src='<?= get_template_directory_uri(); ?>/assets/images/producto.png'">
                </button>
                <?php foreach ($attachment_ids as $attachment_id):
                    $thumb = farmapaz_product_img_fallback(wp_get_attachment_image_url($attachment_id, 'thumbnail'));
                    $full = farmapaz_product_img_fallback(wp_get_attachment_image_url($attachment_id, 'full'));
                ?>
                    <button class="product-gallery-thumb w-14 h-14 sm:w-16 sm:h-16 rounded-xl overflow-hidden flex-shrink-0 transition-all duration-200 hover:ring-2 hover:ring-brand-blue/30 active:scale-95"
                            style="border: 2px solid transparent;"
                            data-src="<?= esc_attr($full); ?>">
                        <img src="<?= esc_attr($thumb); ?>" alt=""
                             class="w-full h-full object-cover bg-gray-50"
                             onerror="this.onerror=null;this.src='<?= get_template_directory_uri(); ?>/assets/images/producto.png'">
                    </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="sp-info">
            <?php if ($cats): ?>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($cats as $cat): ?>
                    <a href="<?= get_term_link($cat); ?>" class="px-3 py-1 text-xs font-semibold rounded-full transition-colors"
                       style="background: rgba(9,20,110,0.06); color: #09146E;">
                        <?= $cat->name; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Brand & SKU -->
            <div class="flex flex-wrap items-center gap-3 text-xs" style="color: rgba(9,20,110,0.45);">
                <?php
                $brands = get_the_terms(get_the_ID(), 'product_brand');
                if ($brands && !is_wp_error($brands)):
                ?>
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <?= esc_html($brands[0]->name); ?>
                </span>
                <?php endif; ?>
                <?php if ($product->get_sku()): ?>
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    SKU: <?= esc_html($product->get_sku()); ?>
                </span>
                <?php endif; ?>
            </div>

            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight" style="color: #09146E;"><?php the_title(); ?></h1>

            <div class="flex items-baseline gap-3 flex-wrap">
                <?php if ($is_sale): ?>
                    <span class="text-3xl sm:text-4xl font-bold" style="color: #FF1A27;"><?= wc_price($sale); ?></span>
                    <span class="text-lg sm:text-xl text-gray-400 line-through"><?= wc_price($regular); ?></span>
                    <span class="px-3 py-1 text-xs font-bold rounded-full text-white" style="background: linear-gradient(135deg, #FF1A27, #dc2626);">-<?= $percent; ?>%</span>
                <?php else: ?>
                    <span class="text-3xl sm:text-4xl font-bold" style="color: #09146E;"><?= $product->get_price_html(); ?></span>
                <?php endif; ?>
            </div>

            <?php if ($product->get_short_description()): ?>
                <p class="text-gray-500 leading-relaxed text-sm sm:text-base"><?= $product->get_short_description(); ?></p>
            <?php endif; ?>

            <?php if ($is_controlled): ?>
            <!-- Controlled medication - in-store only -->
            <div class="sp-controlled-notice">
                <div class="sp-controlled-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <strong>Medicamento controlado</strong>
                    <p>Este producto requiere receta médica y solo se vende en nuestras tiendas físicas. Preséntate en cualquiera de nuestras sucursales con tu receta para adquirirlo.</p>
                </div>
            </div>
            <?php else: ?>
            <!-- Add to Cart -->
            <div class="space-y-3 pt-2">
                <div class="flex items-center gap-3">
                    <div class="flex items-center rounded-xl overflow-hidden" style="border: 1.5px solid rgba(9,20,110,0.12);">
                        <button class="qty-btn px-3 sm:px-4 py-2.5 sm:py-3 text-gray-400 hover:text-brand-blue hover:bg-gray-50 transition-colors text-sm sm:text-base font-medium" data-action="minus">−</button>
                        <input type="number" min="1" value="1" class="qty-input w-14 sm:w-16 text-center py-2.5 sm:py-3 text-sm sm:text-base font-semibold bg-white" style="color: #09146E; border-left: 1.5px solid rgba(9,20,110,0.08); border-right: 1.5px solid rgba(9,20,110,0.08);" readonly>
                        <button class="qty-btn px-3 sm:px-4 py-2.5 sm:py-3 text-gray-400 hover:text-brand-blue hover:bg-gray-50 transition-colors text-sm sm:text-base font-medium" data-action="plus">+</button>
                    </div>
                </div>

                <a href="<?= $product->add_to_cart_url(); ?>"
                   class="block w-full text-center py-3.5 sm:py-4 px-8 font-semibold rounded-xl transition-all duration-300 text-sm sm:text-base text-white"
                   style="background: linear-gradient(135deg, #09146E, #0c1fa0); box-shadow: 0 4px 20px rgba(9,20,110,0.2);"
                   onmouseover="this.style.boxShadow='0 8px 32px rgba(9,20,110,0.3)'; this.style.transform='translateY(-1px)'"
                   onmouseout="this.style.boxShadow='0 4px 20px rgba(9,20,110,0.2)'; this.style.transform='translateY(0)'"
                   data-product_id="<?= $product_id; ?>">
                    Añadir al carrito
                </a>

                <a href="https://wa.me/584128798885?text=<?= urlencode('Hola, me interesa: ' . get_the_title() . ' - ' . get_permalink()); ?>"
                   target="_blank" rel="noopener"
                   class="flex items-center justify-center gap-2 w-full py-3.5 sm:py-4 px-8 font-semibold rounded-xl transition-all duration-300 text-sm sm:text-base text-white"
                   style="background: #25D366; box-shadow: 0 4px 20px rgba(37,211,102,0.2);"
                   onmouseover="this.style.boxShadow='0 8px 32px rgba(37,211,102,0.35)'; this.style.transform='translateY(-1px)'"
                   onmouseout="this.style.boxShadow='0 4px 20px rgba(37,211,102,0.2)'; this.style.transform='translateY(0)'">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Consultar por WhatsApp
                </a>
            </div>
            <?php endif; ?>

            <!-- Trust badges -->
            <div class="grid grid-cols-2 gap-3 pt-4" style="border-top: 1px solid rgba(9,20,110,0.06);">
                <div class="flex items-center gap-2.5 text-xs sm:text-sm" style="color: rgba(9,20,110,0.5);">
                    <svg class="w-4 h-4 flex-shrink-0" style="color: #09146E;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Producto original
                </div>
                <div class="flex items-center gap-2.5 text-xs sm:text-sm" style="color: rgba(9,20,110,0.5);">
                    <svg class="w-4 h-4 flex-shrink-0" style="color: #09146E;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    Delivery disponible
                </div>
            </div>
        </div>
    </div>

    <!-- Description / Stock Availability -->
    <?php if ($product->get_description()): ?>
    <div class="mt-16 lg:mt-24 max-w-4xl mx-auto">
        <button onclick="toggleStock(this)" class="w-full flex items-center gap-3 mb-4 text-left group cursor-pointer">
            <div class="w-1 h-8 rounded-full transition-all duration-300" style="background: linear-gradient(to bottom, #09146E, #FEAB0D);"></div>
            <div class="flex-1">
                <h2 class="text-xl sm:text-2xl font-bold transition-colors" style="color: #09146E;">Disponibilidad en sucursales</h2>
                <p class="text-xs sm:text-sm mt-0.5" style="color: rgba(9,20,110,0.4);">Stock en tiempo real</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all duration-300"
                  style="background: rgba(9,20,110,0.06); color: #09146E;"
                  id="stockHint">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                Toca para ver
            </span>
        </button>

        <div id="stockContent" class="overflow-hidden transition-all duration-500 ease-out" style="max-height: 0; opacity: 0;">
            <div class="rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #09146E 0%, #0a1a7a 50%, #09146E 100%); box-shadow: 0 20px 60px rgba(9,20,110,0.15);">
                <?php
                $desc = $product->get_description();
            // Use DOMDocument to parse and extract stock data
            $dom = new DOMDocument();
            @$dom->loadHTML(mb_convert_encoding($desc, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR);
            $xpath = new DOMXPath($dom);

            // Get summary data
            $total_nodes = $xpath->query("//span[contains(@class,'summary-count')]");
            $totals = [];
            foreach ($total_nodes as $node) {
                $totals[] = trim($node->textContent);
            }

            // Get branch items
            $branch_nodes = $xpath->query("//li[contains(@class,'branch-item')]");
            $branches = [];
            foreach ($branch_nodes as $li) {
                $name_node = $xpath->query(".//*[contains(@class,'branch-name')]", $li);
                $stock_node = $xpath->query(".//*[contains(@class,'stock-badge')]", $li);
                if ($name_node->length > 0) {
                    $name = trim($name_node->item(0)->textContent);
                    $stock_text = $stock_node->length > 0 ? trim($stock_node->item(0)->textContent) : '';
                    $is_out = strpos($stock_text, 'Agotado') !== false;
                    $is_low = strpos($stock_text, 'Últimas') !== false || strpos($stock_text, 'Stock bajo') !== false;
                    preg_match('/(\d+)/', $stock_text, $m);
                    $qty = isset($m[1]) ? (int)$m[1] : 0;
                    $branches[] = [
                        'name' => $name,
                        'qty' => $qty,
                        'status' => $is_out ? 'out' : ($qty <= 2 ? 'low' : 'available'),
                        'label' => $is_out ? 'Agotado' : ($qty <= 2 ? 'Últimas unidades' : 'Disponible'),
                    ];
                }
            }
            ?>

            <!-- Summary Stats -->
            <?php if (!empty($branches)): ?>
            <div class="grid grid-cols-3 gap-px" style="background: rgba(255,255,255,0.06);">
                <div class="p-4 sm:p-6 text-center" style="background: rgba(9,20,110,0.5);">
                    <div class="text-2xl sm:text-3xl font-bold text-white"><?= count($branches) ?></div>
                    <div class="text-xs sm:text-sm mt-1" style="color: rgba(255,255,255,0.5);">Sucursales</div>
                </div>
                <div class="p-4 sm:p-6 text-center" style="background: rgba(9,20,110,0.5);">
                    <?php
                    $available = count(array_filter($branches, fn($b) => $b['status'] !== 'out'));
                    ?>
                    <div class="text-2xl sm:text-3xl font-bold" style="color: #4ade80;"><?= $available ?></div>
                    <div class="text-xs sm:text-sm mt-1" style="color: rgba(255,255,255,0.5);">Con stock</div>
                </div>
                <div class="p-4 sm:p-6 text-center" style="background: rgba(9,20,110,0.5);">
                    <?php
                    $low = count(array_filter($branches, fn($b) => $b['status'] === 'low'));
                    ?>
                    <div class="text-2xl sm:text-3xl font-bold" style="color: #FEAB0D;"><?= $low ?></div>
                    <div class="text-xs sm:text-sm mt-1" style="color: rgba(255,255,255,0.5);">Stock bajo</div>
                </div>
            </div>

            <!-- Branch List -->
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-semibold" style="color: rgba(255,255,255,0.7);">Sucursales</span>
                    <span class="text-xs px-2 py-1 rounded-full" style="background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.4);"><?= count($branches); ?> ubicaciones</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[420px] overflow-y-auto pr-2 branch-scroll" style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.1) transparent;">
                    <?php foreach ($branches as $i => $b): ?>
                    <div class="flex items-center justify-between p-3 sm:p-4 rounded-xl transition-all duration-300 branch-item-reveal"
                         style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); animation-delay: <?= $i * 0.03; ?>s;"
                         onmouseover="this.style.background='rgba(255,255,255,0.08)'; this.style.borderColor='rgba(255,255,255,0.12)'"
                         onmouseout="this.style.background='rgba(255,255,255,0.03)'; this.style.borderColor='rgba(255,255,255,0.06)'">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-2 h-2 rounded-full flex-shrink-0"
                                 style="background: <?= $b['status'] === 'out' ? '#ef4444' : ($b['status'] === 'low' ? '#FEAB0D' : '#4ade80'); ?>; box-shadow: 0 0 6px <?= $b['status'] === 'out' ? 'rgba(239,68,68,0.4)' : ($b['status'] === 'low' ? 'rgba(254,171,13,0.4)' : 'rgba(74,222,128,0.4)'); ?>">
                            </div>
                            <span class="text-sm sm:text-base font-medium text-white truncate"><?= esc_html($b['name']); ?></span>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                            <span class="text-xs sm:text-sm font-semibold" style="color: <?= $b['status'] === 'out' ? 'rgba(239,68,68,0.6)' : ($b['status'] === 'low' ? 'rgba(254,171,13,0.8)' : 'rgba(74,222,128,0.8)'); ?>;">
                                <?= $b['qty'] > 0 ? $b['qty'] . ' und.' : '0'; ?>
                            </span>
                            <span class="text-[10px] sm:text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap"
                                  style="background: <?= $b['status'] === 'out' ? 'rgba(239,68,68,0.1)' : ($b['status'] === 'low' ? 'rgba(254,171,13,0.1)' : 'rgba(74,222,128,0.1)'); ?>; color: <?= $b['status'] === 'out' ? '#ef4444' : ($b['status'] === 'low' ? '#FEAB0D' : '#4ade80'); ?>;">
                                <?= $b['label']; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Related Products -->
    <?php
    $related_cats = get_the_terms(get_the_ID(), 'product_cat');
    if ($related_cats && !is_wp_error($related_cats)):
        $cat_slug = $related_cats[0]->slug;
        farmapaz_product_carousel([
            'title'       => 'También te puede interesar',
            'subtitle'    => 'Productos relacionados',
            'badge'       => '',
            'category'    => $cat_slug,
            'limit'       => 10,
            'orderby'     => 'rand',
            'exclude'     => [get_the_ID()],
        ]);
    endif;
    ?>

    <?php endwhile; ?>
</div>

<style>
.sp-layout {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}
.sp-gallery {
    width: 100%;
}
.sp-gallery > * + * {
    margin-top: 1rem;
}
.sp-info {
    width: 100%;
}
.sp-info > * + * {
    margin-top: 1.25rem;
}
@media (min-width: 640px) {
    .sp-info > * + * {
        margin-top: 1.5rem;
    }
}
@media (min-width: 1024px) {
    .sp-layout {
        flex-direction: row;
        gap: 4rem;
    }
    .sp-gallery {
        width: 50%;
    }
    .sp-info {
        width: 50%;
    }
}

.branch-scroll::-webkit-scrollbar { width: 4px; }
.branch-scroll::-webkit-scrollbar-track { background: transparent; }
.branch-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

@keyframes branchReveal {
    from { opacity: 0; transform: translateX(-12px); }
    to { opacity: 1; transform: translateX(0); }
}
.branch-item-reveal {
    animation: branchReveal 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
}
</style>

<script>
function toggleStock(btn) {
    const content = document.getElementById('stockContent');
    const hint = document.getElementById('stockHint');
    if (!content) return;
    const isOpen = content.style.maxHeight !== '0px' && content.style.maxHeight !== '';
    if (isOpen) {
        content.style.maxHeight = '0';
        content.style.opacity = '0';
        if (hint) hint.style.display = 'inline-flex';
    } else {
        content.style.maxHeight = content.scrollHeight + 'px';
        content.style.opacity = '1';
        if (hint) hint.style.display = 'none';
    }
}

// Gallery
document.querySelectorAll('.product-gallery-thumb').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var src = this.getAttribute('data-src');
        var main = document.querySelector('.product-gallery-main');
        if (!src || !main) return;
        main.src = src;
        document.querySelectorAll('.product-gallery-thumb').forEach(function(t) {
            t.style.borderColor = 'transparent';
        });
        this.style.borderColor = '#09146E';
    });
});
</script>

<?php get_footer(); ?>
