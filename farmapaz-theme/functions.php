<?php
/**
 * Farmapaz Theme Functions
 *
 * CONVENCIÓN:
 *   [†] (dagger) marca modificaciones personalizadas hechas sobre el código base.
 *   Busca "[†]" para encontrar rápido todos los cambios custom.
 */

define('FARMA_VERSION', '1.1.2');
define('FARMA_PER_PAGE', 48);

// Include custom nav walker
require_once get_template_directory() . '/inc/nav-walker.php';

// [†] Óptica: sincronización del catálogo desde ProteoERP hacia WooCommerce
require_once get_template_directory() . '/inc/optica-sync.php';

// [†] Óptica: URL limpia /optica/catalogo/ para el catálogo independiente.
add_action('init', function () {
    add_rewrite_rule('^optica/catalogo/?$', 'index.php?pagename=optica&optica_view=catalogo', 'top');

    // Refrescar reglas una sola vez por versión del tema (el usuario sube archivos,
    // no reactiva el tema, así que no depende de after_switch_theme).
    if (get_option('farmapaz_rewrite_version') !== FARMA_VERSION) {
        flush_rewrite_rules(false);
        update_option('farmapaz_rewrite_version', FARMA_VERSION, false);
    }
}, 5);

add_filter('query_vars', function ($vars) {
    $vars[] = 'optica_view';
    return $vars;
});

add_filter('template_include', function ($template) {
    if (get_query_var('optica_view') === 'catalogo') {
        $catalog = locate_template('page-optica-catalogo.php');
        if ($catalog) return $catalog;
    }
    return $template;
}, 20);

add_filter('document_title_parts', function ($parts) {
    if (get_query_var('optica_view') === 'catalogo') {
        $parts['title'] = 'Catálogo de monturas';
    }
    return $parts;
}, 20);

// [†] Performance: desactivar emoji (el tema usa iconos SVG, no emojis)
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('wp_enqueue_scripts', 'wp_enqueue_emoji_styles');
add_filter('emoji_svg_url', '__return_false');

// [†] Performance: diferir scripts de terceros que no bloquean el render
// (analytics/widgets). No toca jQuery, WooCommerce, WPBakery ni revslider.
add_filter('script_loader_tag', function($tag, $handle) {
    $defer = [
        'dflip-script-js',
        'ht_ctc_app_js-js',
        'xoo-aff-js-js',
        'xoo-el-js-js',
        'wpzoom-wc-spi-script-js',
        'aws-script-js',
        'nelio-popups-public-js',
    ];
    if (in_array($handle, $defer, true) && strpos($tag, ' defer') === false) {
        return str_replace(' src=', ' defer src=', $tag);
    }
    return $tag;
}, 10, 2);

// [†] Contacto configurable desde Apariencia → Personalizar → Contacto
function farmapaz_contact_link() {
    return get_theme_mod('farmapaz_contact_link', 'https://linktr.ee/farmaciafarmapaz');
}
function farmapaz_contact_label() {
    return get_theme_mod('farmapaz_contact_label', 'Contáctanos');
}
function farmapaz_whatsapp_number() {
    return get_theme_mod('farmapaz_whatsapp_number', '584128798885');
}
function farmapaz_whatsapp_url($message = '') {
    $url = 'https://wa.me/' . farmapaz_whatsapp_number();
    if ($message !== '') {
        $url .= '?text=' . urlencode($message);
    }
    return $url;
}
// Número dedicado para pedidos por WhatsApp con "Retiro en tienda" (0424-9139434).
function farmapaz_pickup_whatsapp_number() {
    return '584249139434';
}
// Números de teléfono que se muestran en header y footer (editables desde el panel)
function farmapaz_phone_numbers() {
    $defaults = ['584249139434', '584220129206', '584128798885', '584220130448'];
    $phones   = [];
    foreach ($defaults as $i => $default) {
        $value = get_theme_mod('farmapaz_phone_' . ($i + 1), $default);
        $value = trim($value);
        if ($value !== '') {
            $phones[] = $value;
        }
    }
    return $phones;
}
function farmapaz_phone_display($number) {
    if (preg_match('/^58(\d{3})(\d{7})$/', $number, $m)) {
        return '0' . $m[1] . '-' . $m[2];
    }
    return $number;
}

add_action('customize_register', function ($wp_customize) {
    $wp_customize->add_section('farmapaz_contacto', [
        'title'    => 'Contacto',
        'priority' => 30,
    ]);
    $wp_customize->add_setting('farmapaz_contact_link', [
        'default'           => 'https://linktr.ee/farmaciafarmapaz',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('farmapaz_contact_link', [
        'section' => 'farmapaz_contacto',
        'label'   => 'Enlace "Contáctanos" (linktree, redes, etc.)',
        'type'    => 'url',
    ]);
    $wp_customize->add_setting('farmapaz_contact_label', [
        'default'           => 'Contáctanos',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('farmapaz_contact_label', [
        'section' => 'farmapaz_contacto',
        'label'   => 'Texto del enlace',
        'type'    => 'text',
    ]);
    $wp_customize->add_setting('farmapaz_whatsapp_number', [
        'default'           => '584128798885',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('farmapaz_whatsapp_number', [
        'section' => 'farmapaz_contacto',
        'label'   => 'Número WhatsApp (con código de país, ej. 584128798885)',
        'type'    => 'text',
    ]);
    $phone_labels = ['Teléfono 1', 'Teléfono 2', 'Teléfono 3', 'Teléfono 4'];
    foreach ($phone_labels as $i => $label) {
        $default = ['584249139434', '584220129206', '584128798885', '584220130448'][$i];
        $wp_customize->add_setting('farmapaz_phone_' . ($i + 1), [
            'default'           => $default,
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        $wp_customize->add_control('farmapaz_phone_' . ($i + 1), [
            'section'     => 'farmapaz_contacto',
            'label'       => $label . ' (mostrado en header y footer)',
            'description' => 'Con código de país, ej. 584220130448. Vacío para ocultarlo.',
            'type'        => 'text',
        ]);
    }

    // [†] Banners del slide de portada (3 carruseles: 1 grande + 2 pequeños, sin límite de imágenes)
    $wp_customize->add_section('farmapaz_banners', [
        'title'       => 'Banners Slide',
        'priority'    => 31,
        'description' => 'Sube las imágenes del carrusel de publicidad de la portada. Hay 3 carruseles: uno grande (izquierda) y dos pequeños (derecha, arriba y abajo). Cada uno acepta todas las imágenes que quieras. Si dejas todo vacío, la sección no se muestra.',
    ]);
    $banner_slots = [
        'large'       => 'Carrusel GRANDE (izquierda)',
        'small_top'   => 'Carrusel pequeño SUPERIOR (derecha)',
        'small_bottom' => 'Carrusel pequeño INFERIOR (derecha)',
    ];
    foreach ($banner_slots as $slot => $label) {
        $wp_customize->add_setting('farmapaz_banners_' . $slot, [
            'default'           => '',
            'sanitize_callback' => 'farmapaz_sanitize_banner_ids',
        ]);
        $wp_customize->add_control(new Farmapaz_Multi_Image_Control($wp_customize, 'farmapaz_banners_' . $slot, [
            'label'   => $label,
            'section' => 'farmapaz_banners',
        ]));
    }
});

// [†] Control del Customizer para subir/quitar VARIAS imágenes (sin límite)
// La clase solo existe dentro del contexto del Customizer (WP_Customize_Control).
if (class_exists('WP_Customize_Control')) {
    class Farmapaz_Multi_Image_Control extends WP_Customize_Control {
        public $type = 'farmapaz_multi_image';

        public function render_content() {
            $value = (string) $this->value();
            ?>
            <label>
                <?php if (!empty($this->label)): ?>
                    <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
                <?php endif; ?>
                <?php if (!empty($this->description)): ?>
                    <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
                <?php endif; ?>
            </label>
            <div class="farmapaz-banners-thumbs" data-value="<?php echo esc_attr($value); ?>"></div>
            <button type="button" class="button farmapaz-banners-add">Seleccionar / subir imágenes</button>
            <p class="description" style="margin-top:6px;">Borra la X sobre una imagen para quitar ese banner.</p>
            <style>
                .farmapaz-banners-thumbs { display: flex; flex-wrap: wrap; gap: 6px; margin: 8px 0; }
                .farmapaz-banner-thumb { position: relative; width: 64px; height: 64px; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
                .farmapaz-banner-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
                .farmapaz-banner-thumb .remove { position: absolute; top: 0; right: 0; width: 18px; height: 18px; background: rgba(0,0,0,0.65); color: #fff; font-size: 12px; line-height: 18px; text-align: center; cursor: pointer; border-radius: 0 0 0 6px; }
                .farmapaz-banner-thumb .remove:hover { background: #d63638; }
            </style>
            <?php
        }
    }
}

// [†] Sanitización: solo IDs de adjuntos separados por coma
function farmapaz_sanitize_banner_ids($value) {
    $ids = array_filter(array_map('absint', explode(',', $value)));
    return implode(',', $ids);
}

// [†] Banners de un carrusel (large | small_top | small_bottom). Vacío = no mostrar nada.
function farmapaz_hero_banners($slot = 'large') {
    $value = get_theme_mod('farmapaz_banners_' . $slot, '');
    $ids = array_filter(array_map('absint', explode(',', $value)));
    $banners = [];
    foreach ($ids as $id) {
        $url = wp_get_attachment_image_url($id, 'full');
        if ($url) {
            $banners[] = ['id' => $id, 'url' => $url];
        }
    }
    return $banners;
}

// [†] JS del control multi-imagen en el Customizer
add_action('customize_controls_enqueue_scripts', function () {
    wp_enqueue_media();
    wp_enqueue_script(
        'farmapaz-customize-banners',
        get_template_directory_uri() . '/assets/js/customize-banners.js',
        ['customize-controls', 'media-editor', 'media-views'],
        FARMA_VERSION,
        true
    );
});

// Controlled medications (psychotropic drugs, in-store only)
function farmapaz_is_controlled_product($product_id) {
    return has_term('medicamentos-controlados', 'product_cat', $product_id);
}

add_filter('woocommerce_is_purchasable', function ($purchasable, $product) {
    if ($product && farmapaz_is_controlled_product($product->get_id())) {
        return false;
    }
    // [†] Productos con precio $0 no se pueden comprar
    if ($product && $product->get_price() !== '' && floatval($product->get_price()) === 0.0) {
        return false;
    }
    return $purchasable;
}, 10, 2);

add_filter('woocommerce_add_to_cart_validation', function ($passed, $product_id) {
    if (farmapaz_is_controlled_product($product_id)) {
        wc_add_notice('Este producto requiere receta médica y solo se vende en nuestras tiendas físicas.', 'error');
        return false;
    }
    $product = wc_get_product($product_id);
    // [†] Bloquear adición al carrito de productos con precio $0
    if ($product && $product->get_price() !== '' && floatval($product->get_price()) === 0.0) {
        wc_add_notice('Este producto no está disponible para la venta en línea.', 'error');
        return false;
    }
    return $passed;
}, 10, 2);

// Setup
add_action('after_setup_theme', 'farmapaz_setup');
function farmapaz_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);
    add_theme_support('custom-logo', [
        'height'      => 100,
        'width'       => 300,
        'flex-width'  => true,
        'flex-height' => true,
    ]);
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    // Tamaño optimizado para fotos de sucursales (vertical 3:4, recorte centrado).
    // Cubre hasta ~450px de ancho CSS en pantallas retina (2x = 900px). Las fotos
    // originales (9:16) se recortan uniformemente y se ven bien en todos los dispositivos.
    add_image_size('farmapaz-sucursal', 900, 1200, true);

    register_nav_menus([
        'primary' => __('Menú Principal', 'farmapaz'),
        'mobile'  => __('Menú Móvil', 'farmapaz'),
    ]);
}

// Enqueue assets
add_action('wp_enqueue_scripts', 'farmapaz_enqueue');
function farmapaz_enqueue() {
    $theme = wp_get_theme();

    wp_enqueue_style('farmapaz-tailwind', get_template_directory_uri() . '/assets/css/app.css', [], FARMA_VERSION);
    wp_enqueue_style('farmapaz-style', get_template_directory_uri() . '/assets/css/style.css', ['farmapaz-tailwind'], FARMA_VERSION);

    wp_enqueue_script('farmapaz-app', get_template_directory_uri() . '/assets/js/app.js', [], FARMA_VERSION, true);

    if (is_singular('product')) {
        wp_enqueue_script('farmapaz-product', get_template_directory_uri() . '/assets/js/product.js', ['farmapaz-app'], FARMA_VERSION, true);
    }

    if (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy()) {
        wp_enqueue_style('farmapaz-shop', get_template_directory_uri() . '/assets/css/shop-1.1.0.css', ['farmapaz-style'], FARMA_VERSION);
        wp_enqueue_script('farmapaz-shop', get_template_directory_uri() . '/assets/js/shop.js', ['farmapaz-app'], FARMA_VERSION, true);
    }

    if (is_search()) {
        wp_enqueue_style('farmapaz-shop', get_template_directory_uri() . '/assets/css/shop-1.1.0.css', ['farmapaz-style'], FARMA_VERSION);
        wp_enqueue_script('farmapaz-shop', get_template_directory_uri() . '/assets/js/shop.js', ['farmapaz-app'], FARMA_VERSION, true);
    }

    if (is_front_page() || is_home()) {
        wp_enqueue_style('farmapaz-shop', get_template_directory_uri() . '/assets/css/shop-1.1.0.css', ['farmapaz-style'], FARMA_VERSION);
    }

    // CapiBlue chatbot — always available
    wp_enqueue_style('farmapaz-optica-transition', get_template_directory_uri() . '/assets/css/optica-transition.css', [], FARMA_VERSION);
    wp_enqueue_script('farmapaz-optica-transition', get_template_directory_uri() . '/assets/js/optica-transition.js', [], FARMA_VERSION, true);

    // Óptica — estilos y animaciones exclusivos de la página de catálogo
    if (is_page('optica') || is_page_template('page-optica.php')) {
        $optica_css_path = get_template_directory() . '/assets/css/optica-page.css';
        $optica_js_path  = get_template_directory() . '/assets/js/optica-page.js';
        $optica_css_ver  = file_exists($optica_css_path) ? (string) filemtime($optica_css_path) : FARMA_VERSION;
        $optica_js_ver   = file_exists($optica_js_path) ? (string) filemtime($optica_js_path) : FARMA_VERSION;

        wp_enqueue_style('farmapaz-optica-page', get_template_directory_uri() . '/assets/css/optica-page.css', ['farmapaz-style'], $optica_css_ver);
        wp_enqueue_script('farmapaz-optica-page', get_template_directory_uri() . '/assets/js/optica-page.js', [], $optica_js_ver, true);
    }

    wp_enqueue_script('farmapaz-capiblue', get_template_directory_uri() . '/assets/js/capiblue.js', ['farmapaz-app'], FARMA_VERSION, true);
    // CapiBlue needs shop.css styles everywhere
    if (!is_shop() && !is_product_category() && !is_product_tag() && !is_product_taxonomy() && !is_front_page() && !is_home() && !is_cart() && !is_checkout() && !is_account_page()) {
        wp_enqueue_style('farmapaz-shop', get_template_directory_uri() . '/assets/css/shop-1.1.0.css', ['farmapaz-style'], FARMA_VERSION);
    }

    // Cart drawer lives on every page
    wp_enqueue_script('farmapaz-cart', get_template_directory_uri() . '/assets/js/cart-1.1.0.js', ['farmapaz-app', 'wc-cart'], FARMA_VERSION, true);

    if (is_cart() || is_checkout() || is_account_page()) {
        wp_enqueue_style('farmapaz-shop', get_template_directory_uri() . '/assets/css/shop-1.1.0.css', ['farmapaz-style'], FARMA_VERSION);
    }

    wp_localize_script('farmapaz-app', 'farmapazData', [
        'ajaxUrl'   => admin_url('admin-ajax.php'),
        'themeUri'  => get_template_directory_uri(),
        'homeUrl'   => home_url(),
        'shopUrl'   => get_permalink(wc_get_page_id('shop')),
        'cartUrl'   => wc_get_cart_url(),
        'nonce'     => wp_create_nonce('farmapaz_nonce'),
        'pickupWhatsapp' => farmapaz_pickup_whatsapp_number(),
    ]);
}

// Register widget areas
add_action('widgets_init', 'farmapaz_widgets');
function farmapaz_widgets() {
    register_sidebar([
        'name'          => __('Sidebar Tienda', 'farmapaz'),
        'id'            => 'shop-sidebar',
        'before_widget' => '<div class="mb-6">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="text-lg font-semibold text-brand-blue mb-4 pb-2 border-b border-gray-100">',
        'after_title'   => '</h3>',
    ]);
}

// WooCommerce support - remove default styles
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

// Spanish add-to-cart message
add_filter('wc_add_to_cart_message_html', function($message, $products) {
    if (!is_array($products)) return $message;
    $count = 0;
    foreach ($products as $product_id => $qty) {
        $count += $qty;
    }
    $texto = $count > 1 ? "$count productos añadidos al carrito" : "1 producto añadido al carrito";
    $cart_url = wc_get_cart_url();
    return '<div class="farmapaz-toast" role="alert">
        <svg width="18" height="18" fill="none" stroke="#16a34a" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>' . $texto . '</span>
        <a href="' . esc_url($cart_url) . '" class="farmapaz-toast-link">Ver carrito</a>
    </div>';
}, 10, 2);

// Cart fragment for badge count and totals
// Render cart map in footer for initial page load (also included as AJAX fragment)
add_action('wp_footer', 'farmapaz_render_cart_map');
function farmapaz_render_cart_map() {
    $cart_map = [];
    if (function_exists('WC') && WC() && WC()->cart) {
        foreach (WC()->cart->get_cart() as $key => $item) {
            $cart_map[$item['product_id']] = ['key' => $key, 'qty' => $item['quantity']];
        }
    }
    echo '<div class="farmapaz-cart-map" style="display:none;" aria-hidden="true">' . esc_attr(wp_json_encode($cart_map)) . '</div>';
}

// Mini cart drawer — rendered in footer on every page
add_action('wp_footer', 'farmapaz_render_mini_cart');
function farmapaz_render_mini_cart() {
    ?>
    <div id="farmapaz-mini-cart" class="farmapaz-mini-cart" aria-hidden="true">
        <div class="farmapaz-mini-cart-overlay" data-mini-cart-close></div>
        <aside class="farmapaz-mini-cart-panel" role="dialog" aria-modal="true" aria-label="Carrito de compras">
            <header class="farmapaz-mini-cart-header">
                <h3 class="farmapaz-mini-cart-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    Tu carrito
                </h3>
                <button class="farmapaz-mini-cart-close" data-mini-cart-close aria-label="Cerrar carrito">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </header>
            <div class="farmapaz-mini-cart-body">
                <?php
                $cart = WC()->cart;
                if ($cart && !$cart->is_empty()) {
                    echo '<div class="cart-items">' . farmapaz_render_cart_items() . '</div>';
                    echo farmapaz_render_cart_totals_rows();
                    echo farmapaz_render_cart_total();
                    echo farmapaz_render_mini_cart_actions();
                } else {
                    echo '<p class="cart-empty-msg">Tu carrito está vacío.</p>';
                    echo '<a class="farmapaz-mini-cart-shop" href="' . esc_url(get_permalink(wc_get_page_id('shop'))) . '">Ver la tienda</a>';
                }
                ?>
            </div>
        </aside>
    </div>
    <?php
}

function farmapaz_render_mini_cart_actions() {
    $wa_icon = '<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21 5.46 0 9.91-4.45 9.91-9.91C21.95 6.45 17.5 2 12.04 2zm5.83 14.15c-.25.7-1.45 1.33-2 1.38-.51.05-1.16.07-1.87-.12-.43-.16-.98-.32-1.69-.62-2.98-1.29-4.92-4.29-5.07-4.49-.15-.2-1.22-1.62-1.22-3.09 0-1.47.77-2.19 1.04-2.49.27-.3.6-.37.8-.37h.57c.18.01.43-.07.67.51.25.6.85 2.07.93 2.22.08.15.13.33.03.53-.1.2-.15.32-.3.5-.15.18-.31.4-.45.53-.15.15-.3.31-.13.61.17.3.75 1.24 1.61 2.01 1.11.99 2.04 1.3 2.33 1.45.29.15.46.13.63-.08.17-.2.73-.85.92-1.15.2-.3.4-.25.67-.15.27.1 1.71.81 2 .96.3.15.5.22.57.35.08.13.08.7-.17 1.4z"/></svg>';
    return '<a class="farmapaz-mini-cart-checkout" href="' . esc_url(wc_get_checkout_url()) . '">Ir a pagar</a>'
         . '<a href="#" class="farmapaz-wa-btn farmapaz-wa-btn-pickup" data-wa-order>' . $wa_icon . 'Retirar en tienda</a>'
         . '<a class="farmapaz-mini-cart-fullcart" href="' . esc_url(wc_get_cart_url()) . '">Ver carrito completo</a>';
}

function farmapaz_render_mini_cart_body() {
    ob_start();
    $cart = WC()->cart;
    if ($cart && !$cart->is_empty()) {
        echo '<div class="cart-items">' . farmapaz_render_cart_items() . '</div>';
        echo farmapaz_render_cart_totals_rows();
        echo farmapaz_render_cart_total();
        echo farmapaz_render_mini_cart_actions();
    } else {
        echo '<p class="cart-empty-msg">Tu carrito está vacío.</p>';
        echo '<a class="farmapaz-mini-cart-shop" href="' . esc_url(get_permalink(wc_get_page_id('shop'))) . '">Ver la tienda</a>';
    }
    return ob_get_clean();
}

add_filter('woocommerce_add_to_cart_fragments', 'farmapaz_cart_fragment');
function farmapaz_cart_fragment($fragments) {
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $fragments['#cart-count'] = '<span id="cart-count" class="absolute -top-1.5 -right-1.5 sm:-top-2 sm:-right-2 bg-brand-orange text-white text-xs font-bold rounded-full w-4 h-4 sm:w-5 sm:h-5 flex items-center justify-center shadow-sm">' . $count . '</span>';
    $fragments['div.cart-totals-rows'] = farmapaz_render_cart_totals_rows();
    $fragments['div.cart-totals-total'] = farmapaz_render_cart_total();
    $fragments['div.cart-items'] = '<div class="cart-items">' . farmapaz_render_cart_items() . '</div>';
    $fragments['div.farmapaz-mini-cart-body'] = '<div class="farmapaz-mini-cart-body">' . farmapaz_render_mini_cart_body() . '</div>';
    $cart_map = [];
    if (WC()->cart) {
        foreach (WC()->cart->get_cart() as $key => $item) {
            $cart_map[$item['product_id']] = ['key' => $key, 'qty' => $item['quantity']];
        }
    }
    $fragments['div.farmapaz-cart-map'] = '<div class="farmapaz-cart-map" style="display:none;" aria-hidden="true">' . esc_attr(wp_json_encode($cart_map)) . '</div>';
    return $fragments;
}

function farmapaz_render_cart_totals_rows() {
    ob_start();
    $cart = WC()->cart;
    ?>
    <div class="cart-totals-rows">
        <div class="cart-totals-row">
            <span>Subtotal</span>
            <span><?= wc_price($cart->get_subtotal()); ?></span>
        </div>
        <?php if ($cart->get_cart_discount_total() > 0): ?>
        <div class="cart-totals-row cart-totals-discount">
            <span>Descuento</span>
            <span>-<?= wc_price($cart->get_cart_discount_total()); ?></span>
        </div>
        <?php endif; ?>
        <?php foreach ($cart->get_fees() as $fee):
            $is_shipping = stripos($fee->name, 'envío') !== false;
            $is_free = stripos($fee->name, 'gratis') !== false || stripos($fee->name, 'retiro') !== false;
        ?>
        <div class="cart-totals-row">
            <?php if ($is_shipping): ?>
            <span class="farmapaz-shipping-indicator <?= $is_free ? 'free' : 'paid'; ?>">
                <?php if ($is_free): ?>
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <?php else: ?>
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2-1m4-1l-2 1m4-2l-2 1m6-5v5a1 1 0 001 1h3l2-4v-3a1 1 0 00-1-1h-4a1 1 0 00-1 1z"/></svg>
                <?php endif; ?>
                <span><?= esc_html($fee->name); ?></span>
            </span>
            <?php else: ?>
            <span><?= esc_html($fee->name); ?></span>
            <?php endif; ?>
            <span><?= $is_free ? 'Gratis' : wc_price($fee->amount); ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (wc_tax_enabled() && !$cart->display_prices_including_tax() && $cart->get_cart_tax() > 0): ?>
        <div class="cart-totals-row">
            <span>IVA</span>
            <span><?= wc_price($cart->get_cart_tax()); ?></span>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function farmapaz_render_cart_total() {
    ob_start();
    ?>
    <div class="cart-totals-total">
        <span>Total</span>
        <span><?php wc_cart_totals_order_total_html(); ?></span>
    </div>
    <?php
    return ob_get_clean();
}

function farmapaz_render_cart_items() {
    ob_start();
    $cart = WC()->cart;
    if ($cart->is_empty()): ?>
        <p class="cart-empty-msg">Tu carrito está vacío.</p>
    <?php else:
        foreach ($cart->get_cart() as $cart_item_key => $cart_item):
            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
            $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
            if (!$_product || !$_product->exists() || $cart_item['quantity'] < 1 || !apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) continue;
            $subtotal  = apply_filters('woocommerce_cart_item_subtotal', $cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key);
            $permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
            $img_url = farmapaz_product_img_fallback(get_the_post_thumbnail_url($product_id, 'thumbnail'));
    ?>
        <div class="cart-item" data-cart-key="<?= esc_attr($cart_item_key); ?>" data-product-id="<?= esc_attr($product_id); ?>" data-unit-price="<?= esc_attr($_product->get_price()); ?>" data-sku="<?= esc_attr($_product->get_sku() ?: $product_id); ?>" data-name="<?= esc_attr($product_name); ?>" data-link="<?= esc_url($permalink); ?>">
            <button class="cart-item-remove" title="Eliminar" onclick="farmapaz_remove_from_cart('<?= esc_js($cart_item_key); ?>')">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="cart-item-img-wrap">
                <?php if ($permalink): ?><a href="<?= esc_url($permalink); ?>"><?php endif; ?>
                    <?php farmapaz_picture($img_url, $product_name, 'cart-item-img'); ?>
                <?php if ($permalink): ?></a><?php endif; ?>
            </div>
            <div class="cart-item-body">
                <div class="cart-item-info">
                    <h3 class="cart-item-name">
                        <?php if ($permalink): ?><a href="<?= esc_url($permalink); ?>"><?php endif; ?>
                            <?= esc_html($product_name); ?>
                        <?php if ($permalink): ?></a><?php endif; ?>
                    </h3>
                    <div class="cart-item-meta"><?php echo wc_get_formatted_cart_item_data($cart_item); ?></div>
                    <div class="cart-item-price-mobile"><?= $subtotal; ?></div>
                </div>
                <div class="cart-item-qty">
                    <div class="cart-qty-selector">
                        <button type="button" class="cart-qty-btn cart-qty-minus" data-key="<?= esc_attr($cart_item_key); ?>">−</button>
                        <input type="number" class="cart-qty-input" name="cart[<?= esc_attr($cart_item_key); ?>][qty]" value="<?= esc_attr($cart_item['quantity']); ?>" min="0" data-key="<?= esc_attr($cart_item_key); ?>">
                        <button type="button" class="cart-qty-btn cart-qty-plus" data-key="<?= esc_attr($cart_item_key); ?>">+</button>
                    </div>
                </div>
                <div class="cart-item-price"><?= $subtotal; ?></div>
            </div>
        </div>
    <?php endforeach;
    endif;
    return ob_get_clean();
}

// Translate empty cart notice to Spanish
add_filter('woocommerce_cart_is_empty_message', function() {
    return 'Tu carrito est\u00e1 vac\u00edo.';
});

// Translate WooCommerce texts to Spanish
add_filter('gettext', function($translated, $original, $domain) {
    if ($domain === 'woocommerce') {
        $texts = [
            'Your cart is currently empty.' => 'Tu carrito está vacío.',
            'Return to shop' => 'Volver a la tienda',
            'View cart' => 'Ver carrito',
            'has been added to your cart.' => 'ha sido añadido al carrito.',
            'View Cart' => 'Ver carrito',
            'Checkout' => 'Pagar',
            'Proceed to checkout' => 'Ir a pagar',
            'Update cart' => 'Actualizar carrito',
            'Cart updated.' => 'Carrito actualizado.',
            'Remove' => 'Eliminar',
            'Remove this item' => 'Eliminar este artículo',
            'Quantity' => 'Cantidad',
            'Product' => 'Producto',
            'Price' => 'Precio',
            'Subtotal' => 'Subtotal',
            'Total' => 'Total',
            'Shipping' => 'Envío',
            'Coupon' => 'Cupón',
            'Apply coupon' => 'Aplicar cupón',
            'Coupon code' => 'Código de descuento',
            'Returning customer?' => '¿Eres un cliente habitual?',
            'Click here to login' => 'Haz clic aquí para iniciar sesión',
            'Have a coupon?' => '¿Tienes un cupón?',
            'Click here to enter your code' => 'Haz clic aquí para ingresar tu código',
            'Billing details' => 'Detalles de facturación',
            'Shipping details' => 'Detalles de envío',
            'Additional information' => 'Información adicional',
            'Order notes' => 'Notas del pedido',
            'Your order' => 'Tu pedido',
            'First name' => 'Nombre',
            'Last name' => 'Apellido',
            'Company' => 'Empresa',
            'Country / Region' => 'País',
            'Street address' => 'Dirección detallada',
            'Town / City' => 'Ciudad',
            'State / County' => 'Estado',
            'Postcode / ZIP' => 'Código postal',
            'Phone' => 'Teléfono',
            'Email address' => 'Correo electrónico',
            'Apartment, suite, unit, etc. (optional)' => 'Apartamento, casa, etc. (opcional)',
            'Create an account?' => '¿Crear una cuenta?',
            'Ship to a different address?' => '¿Enviar a una dirección diferente?',
            'Venezuela' => 'Venezuela',
            'United States (US)' => 'Estados Unidos (EE.UU.)',
            'United States' => 'Estados Unidos',
            'Our bank details' => 'Nuestros datos bancarios',
            'Bank' => 'Banco',
            'Account number' => 'Número de cuenta',
            'Sort code' => 'Código de ordenación',
        ];
        if (isset($texts[$original])) {
            return $texts[$original];
        }
    }
    return $translated;
}, 10, 3);

// The reference is unique per purchase: never prefill it (not from session nor account meta).
add_filter('woocommerce_checkout_get_value', function ($value, $input) {
    if ($input === 'billing_numero_referencia') return '';
    return $value;
}, 10, 2);

// Customize checkout fields to match real portal
add_filter('woocommerce_checkout_fields', 'farmapaz_checkout_fields');
function farmapaz_checkout_fields($fields) {
    // Billing fields
    $fields['billing'] = [
        'billing_first_name' => [
            'label'    => 'Nombre',
            'required' => true,
            'class'    => ['form-row-first'],
            'priority' => 10,
        ],
        'billing_last_name' => [
            'label'    => 'Apellido',
            'required' => true,
            'class'    => ['form-row-last'],
            'priority' => 20,
        ],
        'billing_country' => [
            'label'    => 'País',
            'required' => true,
            'class'    => ['form-row-wide'],
            'priority' => 50,
            'type'     => 'country',
        ],
        'billing_city' => [
            'label'    => 'Ciudad',
            'required' => true,
            'class'    => ['form-row-first'],
            'priority' => 60,
        ],
        'billing_state' => [
            'label'    => 'Estado',
            'required' => true,
            'class'    => ['form-row-last'],
            'priority' => 70,
            'type'     => 'state',
        ],
        'billing_address_1' => [
            'label'    => 'Dirección detallada',
            'required' => true,
            'class'    => ['form-row-wide'],
            'priority' => 80,
        ],
        'billing_email' => [
            'label'    => 'Dirección de correo electrónico',
            'required' => true,
            'class'    => ['form-row-wide'],
            'priority' => 90,
        ],
        // [†] billing_numero_referencia DISABLED: WOOCCM renders its own required
        // "Número de Referencia" field (billing_wooccm10) which saves to order meta
        // for the external API.
        /*
        'billing_numero_referencia' => [
            'label'    => 'Número de Referencia',
            'required' => true,
            'class'    => ['form-row-wide'],
            'priority' => 100,
        ],
        */
    ]; // capture_pago rendered separately via woocommerce_after_checkout_billing_form

    // Clean up shipping fields
    if (isset($fields['shipping'])) {
        unset($fields['shipping']['shipping_company']);
        unset($fields['shipping']['shipping_address_2']);
        unset($fields['shipping']['shipping_postcode']);
        $fields['shipping']['shipping_first_name']['label'] = 'Nombre';
        $fields['shipping']['shipping_last_name']['label'] = 'Apellido';
        $fields['shipping']['shipping_country']['label'] = 'País';
        $fields['shipping']['shipping_city']['label'] = 'Ciudad';
        $fields['shipping']['shipping_state']['label'] = 'Estado';
        $fields['shipping']['shipping_address_1']['label'] = 'Dirección detallada';
    }

    // Remove unused billing fields
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_phone']);

    // Order fields (notes)
    $fields['order']['order_comments']['label'] = 'Notas del pedido (opcional)';
    $fields['order']['order_comments']['placeholder'] = '';

    return $fields;
}

// [†] Save the Capture del pago file to WordPress media AND attach it to the order.
// The theme renders its own billing_capture_pago field; WOOCCM's billing_wooccm11
// field is removed from the checkout (see farmapaz_remove_wooccm_capture_field).
// The upload runs server-side on order creation (media_handle_upload), so the image
// always reaches the media library and is stored on the order meta.
add_action('woocommerce_checkout_update_order_meta', 'farmapaz_save_custom_checkout_fields');
function farmapaz_save_custom_checkout_fields($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    if (isset($_FILES['billing_capture_pago']) && is_array($_FILES['billing_capture_pago']) && $_FILES['billing_capture_pago']['error'] === UPLOAD_ERR_OK) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attach_id = media_handle_upload('billing_capture_pago', $order_id, [
            'post_title' => 'Comprobante de pago - Pedido #' . $order_id,
        ], [
            'test_form' => false,
            'mimes'     => [
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'webp' => 'image/webp',
            ],
        ]);

        if (!is_wp_error($attach_id)) {
            $url = wp_get_attachment_url($attach_id);
            $order->update_meta_data('_billing_capture_pago', $url);
            $order->update_meta_data('_billing_capture_pago_attachment_id', (int) $attach_id);
            // The external API reads WOOCCM's meta key; keep it populated with the URL.
            $order->update_meta_data('_billing_wooccm11', $url);
        }
    }

    $order->save();
}

// [†] Validate that the payment capture file is uploaded
add_action('woocommerce_checkout_process', 'farmapaz_validate_capture_pago');
function farmapaz_validate_capture_pago() {
    if (empty($_FILES['billing_capture_pago']) || $_FILES['billing_capture_pago']['error'] !== UPLOAD_ERR_OK) {
        wc_add_notice('El Capture del pago es obligatorio. Por favor sube la imagen de tu comprobante de pago.', 'error');
        return;
    }
    if ((int) $_FILES['billing_capture_pago']['size'] > 5 * 1024 * 1024) {
        wc_add_notice('La imagen del comprobante no puede superar los 5 MB. Toma una foto en menor resolución o comprime la imagen.', 'error');
    }
}

// Remove WOOCCM's duplicate "Capture del pago" file field (billing_wooccm11).
// WOOCCM keeps billing_wooccm10 (Número de Referencia) and billing_wooccm12
// (Cédula); the capture is handled by the theme's own billing_capture_pago field.
add_filter('woocommerce_checkout_fields', 'farmapaz_remove_wooccm_capture_field', 999);
function farmapaz_remove_wooccm_capture_field($fields) {
    if (isset($fields['billing']['billing_wooccm11'])) {
        unset($fields['billing']['billing_wooccm11']);
    }
    return $fields;
}

// Display custom fields in admin order details
add_action('woocommerce_admin_order_data_after_billing_address', 'farmapaz_admin_order_custom_fields');
function farmapaz_admin_order_custom_fields($order) {
    $fields = [
        '_billing_numero_referencia' => 'Número de Referencia',
    ];
    foreach ($fields as $meta_key => $label) {
        $value = $order->get_meta($meta_key);
        if ($value) {
            echo '<p><strong>' . $label . ':</strong> ' . esc_html($value) . '</p>';
        }
    }
    $capture = $order->get_meta('_billing_capture_pago');
    if ($capture) {
        echo '<p><strong>Capture del pago:</strong> <a href="' . esc_url($capture) . '" target="_blank">Ver imagen</a></p>';
    }
}

// Display custom checkout fields in order emails
add_action('woocommerce_email_order_meta', 'farmapaz_email_order_custom_fields', 10, 3);
function farmapaz_email_order_custom_fields($order, $sent_to_admin, $plain_text) {
    if (!$order) return;
    $fields = [
        '_billing_numero_referencia' => 'Número de Referencia',
    ];
    if ($plain_text) {
        echo "\n--- Datos de pago ---\n";
        foreach ($fields as $meta_key => $label) {
            $value = $order->get_meta($meta_key);
            if ($value) {
                echo $label . ': ' . $value . "\n";
            }
        }
        $capture = $order->get_meta('_billing_capture_pago');
        if ($capture) {
            echo "Comprobante de pago: " . $capture . "\n";
        }
        echo "\n";
    } else {
        echo '<table class="td font-family" cellspacing="0" cellpadding="6" style="width: 100%;" border="1"><tbody>';
        foreach ($fields as $meta_key => $label) {
            $value = $order->get_meta($meta_key);
            if ($value) {
                echo '<tr><th class="td text-align-left" scope="row" style="width: 40%;">' . $label . '</th><td class="td text-align-left">' . esc_html($value) . '</td></tr>';
            }
        }
        $capture = $order->get_meta('_billing_capture_pago');
        if ($capture) {
            echo '<tr><th class="td text-align-left" scope="row" style="width: 40%;">Comprobante de pago</th><td class="td text-align-left"><a href="' . esc_url($capture) . '" target="_blank">Ver imagen</a></td></tr>';
        }
        echo '</tbody></table>';
    }
}

// Translate order item totals labels to Spanish
add_filter('woocommerce_get_order_item_totals', 'farmapaz_translate_order_totals', 10, 3);
function farmapaz_translate_order_totals($total_rows, $order, $tax_display) {
    $translations = [
        'cart_subtotal'  => 'Subtotal:',
        'shipping'       => 'Envío:',
        'payment_method' => 'Método de pago:',
        'order_total'    => 'Total:',
    ];
    foreach ($total_rows as $key => &$row) {
        if (isset($translations[$key])) {
            $row['label'] = $translations[$key];
        }
    }
    return $total_rows;
}

// Translate WooCommerce email headings to Spanish
add_filter('woocommerce_email_heading_' . 'customer_processing_order', function($heading) {
    return 'Pedido recibido';
});
add_filter('woocommerce_email_heading_' . 'customer_on_hold_order', function($heading) {
    return 'Pedido recibido';
});
add_filter('woocommerce_email_heading_' . 'customer_completed_order', function($heading) {
    return 'Pedido completado';
});

// Validate custom checkout fields
// DISABLED: the theme's billing_numero_referencia field was removed and replaced
// by WOOCCM's billing_wooccm10, which is configured as required and validated by
// WooCommerce core. This stale validator checked a key that is never posted, so
// it always added "Número de Referencia es obligatorio." even when the field was filled.

// Translate WooCommerce search results title to Spanish
add_filter('woocommerce_page_title', function($title) {
    if (is_search()) {
        return 'Resultados de búsqueda: "' . get_search_query() . '"';
    }
    return $title;
});

// Translate My Account navigation menu labels to Spanish
add_filter('woocommerce_account_menu_items', function($items) {
    $translations = [
        'dashboard'       => 'Panel de Control',
        'orders'          => 'Pedidos',
        'downloads'       => 'Descargas',
        'edit-address'    => 'Direcciones',
        'payment-methods' => 'Métodos de Pago',
        'edit-account'    => 'Detalles de la Cuenta',
        'customer-logout' => 'Cerrar Sesión',
    ];
    foreach ($items as $key => &$label) {
        if (isset($translations[$key])) {
            $label = $translations[$key];
        }
    }
    return $items;
});

// [†] Render Capture del pago file upload after billing fields
add_action('woocommerce_after_checkout_billing_form', 'farmapaz_render_capture_pago_field');
function farmapaz_render_capture_pago_field($checkout) {
    ?>
    <div class="form-row form-row-wide farmapaz-capture-wrap">
        <label for="billing_capture_pago">
            Capture del pago <abbr class="required" title="obligatorio">*</abbr>
        </label>
        <div class="farmapaz-capture-input">
            <input type="file" name="billing_capture_pago" id="billing_capture_pago" required
                   accept="image/png,image/jpeg,image/jpg,image/webp,.png,.jpg,.jpeg,.webp">
            <div class="farmapaz-capture-btn">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Seleccionar imagen</span>
            </div>
            <span class="farmapaz-capture-name"></span>
            <div class="farmapaz-capture-preview"></div>
        </div>
        <span class="farmapaz-capture-hint">Formatos: PNG, JPG, WEBP — Máximo 5 MB</span>
        <span class="farmapaz-capture-error" style="display:none; color:#FF1A27; font-size:12px;">La imagen no puede superar los 5 MB.</span>
    </div>
    <script>
    document.getElementById('billing_capture_pago')?.addEventListener('change', function() {
        var wrap = this.closest('.farmapaz-capture-wrap');
        var nameEl = wrap.querySelector('.farmapaz-capture-name');
        var previewEl = wrap.querySelector('.farmapaz-capture-preview');
        var errorEl = wrap.querySelector('.farmapaz-capture-error');
        if (this.files && this.files[0]) {
            var f = this.files[0];
            if (f.size > 5 * 1024 * 1024) {
                this.value = '';
                nameEl.style.display = 'none';
                previewEl.style.display = 'none';
                if (errorEl) errorEl.style.display = 'inline';
                return;
            }
            if (errorEl) errorEl.style.display = 'none';
            nameEl.textContent = f.name;
            nameEl.style.display = 'inline';
            var reader = new FileReader();
            reader.onload = function(e) {
                previewEl.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                previewEl.style.display = 'block';
            };
            reader.readAsDataURL(f);
        } else {
            nameEl.style.display = 'none';
            previewEl.style.display = 'none';
        }
    });
    window.addEventListener('pageshow', function(e) {
        if (!e.persisted) return;
        var input = document.getElementById('billing_capture_pago');
        if (!input) return;
        input.value = '';
        var wrap = input.closest('.farmapaz-capture-wrap');
        var nameEl = wrap.querySelector('.farmapaz-capture-name');
        var previewEl = wrap.querySelector('.farmapaz-capture-preview');
        if (nameEl) nameEl.style.display = 'none';
        if (previewEl) previewEl.style.display = 'none';
    });
    </script>
    <?php
}

// "Crear cuenta" checkbox marcado por defecto en checkout
add_filter('woocommerce_create_account_default_checked', '__return_true');

// Remove default empty cart message (we have our own in cart-empty.php)
remove_action('woocommerce_cart_is_empty', 'wc_empty_cart_message', 10);

// Dynamic shipping fee: configurable flat fee below the free-shipping minimum. Free for pickup.
add_action('woocommerce_cart_calculate_fees', 'farmapaz_shipping_fee');
function farmapaz_shipping_fee($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    $subtotal = $cart->get_subtotal();
    $free_min = farmapaz_free_shipping_min();
    $fee_amount = farmapaz_shipping_fee_amount();
    $fee_name = 'Envío';

    // Remove any existing shipping fee
    $fees = $cart->get_fees();
    foreach ($fees as $key => $fee) {
        if ($fee->name === $fee_name || $fee->name === 'Envío gratis') {
            $cart->remove_fee($key);
        }
    }

    if (farmapaz_delivery_method() === 'pickup') {
        $cart->add_fee('Retiro en tienda', 0, false);
        return;
    }

    // [†] Multi-moneda (WooCommerce Multi Currency, VillaTheme): el subtotal del
    // carrito viene expresado en la moneda seleccionada (p.ej. VES), pero el mínimo
    // de envío gratis y el fee están en USD. Normalizamos el subtotal a la moneda
    // base para compararlo y convertimos el fee a la moneda actual. Si el plugin no
    // está presente, se trabaja directo en USD.
    if (function_exists('wmc_revert_price')) {
        $subtotal_base = (float) wmc_revert_price($subtotal);
    } else {
        $subtotal_base = (float) $subtotal;
    }

    if ($free_min > 0 && $subtotal_base >= $free_min) {
        $cart->add_fee('Envío gratis', 0, false);
    } else {
        if (function_exists('wmc_get_price')) {
            $fee_amount = (float) wmc_get_price($fee_amount);
        }
        $cart->add_fee($fee_name, $fee_amount, false);
    }
}

// Custom WooCommerce wrapper
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', 'farmapaz_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'farmapaz_wrapper_end', 10);

function farmapaz_wrapper_start() {
    echo '<main class="min-h-screen bg-white"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">';
}

function farmapaz_wrapper_end() {
    echo '</div></main>';
}

// Change number of products per page
add_filter('loop_shop_per_page', fn() => FARMA_PER_PAGE);

// Custom breadcrumb
add_filter('woocommerce_breadcrumb_defaults', fn() => [
    'delimiter'   => ' / ',
    'wrap_before' => '<nav class="text-sm text-gray-500 mb-6">',
    'wrap_after'  => '</nav>',
    'before'      => '',
    'after'       => '',
    'home'        => 'Inicio',
]);

// Product carousel renderer
function farmapaz_product_carousel($args = []) {
    $defaults = [
        'title'       => 'Productos',
        'subtitle'    => '',
        'badge'       => '',
        'limit'       => 8,
        'category'    => '',
        'on_sale'     => false,
        'featured'    => false,
        'orderby'     => 'date',
        'order'       => 'DESC',
        'style'       => 'default',
        'exclude'     => [],
        'force_sale'  => false,
        'layout'      => 'carousel',
        'bg_tone'     => 'light',
    ];
    $atts = wp_parse_args($args, $defaults);

    $meta_query = ['relation' => 'AND'];

    if ($atts['on_sale']) {
        $meta_query[] = [
            'relation' => 'OR',
            ['key' => '_sale_price', 'value' => '', 'compare' => '!='],
            ['key' => '_sale_price', 'value' => 0, 'compare' => '>'],
        ];
    }

    if ($atts['featured']) {
        $meta_query[] = ['key' => '_featured', 'value' => 'yes'];
    }

    // [†] Exclude products with price zero ($0 = sin precio asignado)
    $meta_query[] = ['key' => '_price', 'value' => 0, 'compare' => '>', 'type' => 'DECIMAL'];

    $meta_query[] = ['key' => '_stock_status', 'value' => 'instock'];

    // [†] Excluir el catálogo de óptica de los carruseles de farmacia
    if (function_exists('farmapaz_optica_meta_exclude')) {
        $meta_query[] = farmapaz_optica_meta_exclude();
    }

    $query_args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'meta_query'     => $meta_query,
        'posts_per_page' => $atts['limit'] * 3,
        'orderby'        => $atts['orderby'],
        'order'          => $atts['order'],
        'post__not_in'   => $atts['exclude'],
    ];

    if ($atts['category']) {
        $query_args['tax_query'][] = [
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $atts['category'],
        ];
    }

    $products = new WP_Query($query_args);
    if (!$products->have_posts()) return;

    $section_id = 'section-' . sanitize_title($atts['title']);
    $is_grid = $atts['layout'] === 'grid' || $atts['layout'] === 'compact';
    $is_compact = $atts['layout'] === 'compact';
    $bg_tone = $atts['bg_tone'];
    $bg_colors = [
        'light'      => 'linear-gradient(135deg, #f0f5ff 0%, #ffffff 30%, rgba(9,20,110,0.03) 70%, #e8f0ff 100%)',
        'lighter'    => 'linear-gradient(135deg, #f8faff 0%, #ffffff 40%, rgba(9,20,110,0.015) 60%, #f5f9ff 100%)',
        'white'      => 'linear-gradient(135deg, #ffffff 0%, #fafcff 100%)',
        'brand-blue' => 'linear-gradient(135deg, rgba(9,20,110,0.06) 0%, rgba(9,20,110,0.02) 50%, rgba(9,20,110,0.04) 100%)',
    ];
    $bg = $bg_colors[$bg_tone] ?? $bg_colors['light'];
    $card_width = $is_grid ? '' : 'min(46vw, 260px)';
    ?>
    <section class="py-14 sm:py-20 lg:py-24 overflow-hidden futuristic-section section-offscreen" style="background: <?= $bg; ?>;">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 lg:mb-10 fade-in-up">
                <div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-brand-blue">
                        <?= esc_html($atts['title']); ?>
                    </h2>
                    <?php if ($atts['subtitle']): ?>
                        <p class="text-gray-500 mt-1 text-sm sm:text-base"><?= esc_html($atts['subtitle']); ?></p>
                    <?php endif; ?>
                </div>
                <a href="<?= get_permalink(wc_get_page_id('shop')); ?>"
                   class="hidden sm:inline-flex items-center gap-1.5 text-brand-green font-medium text-sm hover:text-green-700 transition-colors mt-3 sm:mt-0 group">
                    Ver todos
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <div class="relative z-10">
            <div class="relative" id="<?= $section_id; ?>">
                <?php if ($is_grid): ?>
                <div class="grid <?= $is_compact ? 'grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 sm:gap-3' : 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4 lg:gap-5'; ?> stagger-children"
                     style="padding-left: calc((100vw - min(100vw - 2rem, 1280px)) / 2); padding-right: calc((100vw - min(100vw - 2rem, 1280px)) / 2);">
                <?php else: ?>
                <div class="flex gap-4 sm:gap-6 lg:gap-7 overflow-x-auto pb-6 snap-x snap-mandatory scrollbar-hide scroll-smooth cursor-grab product-carousel-track"
                     data-carousel="<?= $section_id; ?>"
                     style="padding-left: calc((100vw - min(100vw - 2rem, 1280px)) / 2 + 24px); padding-right: calc((100vw - min(100vw - 2rem, 1280px)) / 2 + 24px);">
                <?php endif; ?>
                    <?php
                    $shown = 0;
                    while ($products->have_posts() && $shown < $atts['limit']):
                        $products->the_post();
                        global $product;
                        if (!$product) continue;
                        $image_url = farmapaz_product_img_fallback(get_the_post_thumbnail_url(get_the_ID(), 'medium_large'));
                        if (farmapaz_is_low_stock($product)) continue;
                        $shown++;
                        $has_real_sale = $product->is_on_sale() && $product->get_regular_price() > 0;
                        $regular = $product->get_regular_price();
                        $sale = $product->get_sale_price();
                        $percent = 0;
                        if ($has_real_sale && $regular > 0) {
                            $percent = round((($regular - $sale) / $regular) * 100);
                        }
                        $cart_item_key = null;
                        $cart_qty = 0;
                        $in_cart = false;
                        if (function_exists('WC') && WC() && WC()->cart && !WC()->cart->is_empty()) {
                            foreach (WC()->cart->get_cart() as $ckey => $cart_item) {
                                if ((int)$cart_item['product_id'] === (int)$product->get_id()) {
                                    $cart_item_key = $ckey;
                                    $cart_qty = (int)$cart_item['quantity'];
                                    $in_cart = true;
                                    break;
                                }
                            }
                        }
                    ?>
                    <div class="<?= $is_grid ? '' : 'flex-none snap-start '; ?>product-card-group stagger-item" style="<?= $is_grid ? '' : 'width: min(46vw, 260px);'; ?>">
                        <div class="group relative h-full flex flex-col <?= $is_compact ? 'rounded-xl' : 'rounded-2xl'; ?> transition-all duration-500 overflow-hidden glass-card glow-card"
                             onmouseover="this.style.transform='translateY(-6px)'; this.style.borderColor='rgba(9,20,110,0.15)';"
                             onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='rgba(9,20,110,0.08)';">
                            <a href="<?php the_permalink(); ?>" class="relative overflow-hidden block cursor-pointer" style="aspect-ratio: 1/1;">
                                <?php if ($has_real_sale): ?>
                                    <span class="sale-badge absolute <?= $is_compact ? 'top-1.5 left-1.5' : 'top-3 left-3'; ?> z-10 inline-flex items-center justify-center font-bold" style="<?= $is_compact ? 'min-width:36px;height:36px;font-size:11px' : 'min-width:44px;height:44px;font-size:14px'; ?>;">
                                        -<?= $percent; ?>%
                                    </span>
                                <?php elseif ($atts['force_sale']): ?>
                                    <span class="absolute <?= $is_compact ? 'top-1.5 left-1.5' : 'top-3 left-3'; ?> z-10 inline-flex items-center justify-center font-bold" style="<?= $is_compact ? 'min-width:36px;height:36px;font-size:10px' : 'min-width:44px;height:44px;font-size:11px'; ?>; background: linear-gradient(135deg, #FEAB0D, #fbbf24); color: #09146E; border-radius: 8px; padding: 2px 8px; letter-spacing: 0.04em; box-shadow: 0 2px 8px rgba(254,171,13,0.3); animation: salePulse 2s ease-in-out infinite;">
                                        OFERTA
                                    </span>
                                <?php endif; ?>
                                <?php $prod_price = $product->get_price(); if ($prod_price && (float)$prod_price >= 20): ?>
                                    <span class="absolute <?= $is_compact ? 'bottom-1.5 left-1.5' : 'bottom-3 left-3'; ?> z-10 inline-flex items-center justify-center font-bold text-white" style="background: #5A7D43; padding: 2px 8px; border-radius: 9999px; font-size: 10px; letter-spacing: 0.03em; box-shadow: 0 2px 8px rgba(90,125,67,0.3); line-height: 1.4;">
                                        Delivery Gratis
                                    </span>
                                <?php endif; ?>
                                <img src="<?= $image_url; ?>" alt="<?php the_title(); ?>"
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out"
                                     loading="lazy"
                                     <?= $shown === 1 ? 'fetchpriority="high"' : ''; ?>>
                                <div class="absolute inset-0 bg-gradient-to-t from-white/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            </a>
                            <div class="<?= $is_compact ? 'p-2.5' : 'p-4 sm:p-5'; ?> flex flex-col flex-1" style="position: relative; z-index: 1;">
                                <?php if (!$is_compact):
                                $cats = get_the_terms(get_the_ID(), 'product_cat');
                                if ($cats):
                                ?>
                                <div class="mb-1.5">
                                    <?php foreach (array_slice($cats, 0, 1) as $cat): ?>
                                        <span class="category-tag"><?= $cat->name; ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; endif; ?>
                                <h3 class="<?= $is_compact ? 'text-xs' : 'text-sm sm:text-base'; ?> font-semibold text-gray-800 leading-snug" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: <?= $is_compact ? '2.2em' : '2.6em'; ?>;">
                                    <a href="<?php the_permalink(); ?>" class="hover:text-brand-green transition-colors">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                <div class="mt-auto <?= $is_compact ? 'pt-2' : 'pt-3'; ?>">
                                    <div class="flex items-baseline gap-2 <?= $is_compact ? 'mb-2' : 'mb-3'; ?>">
                                        <?php if ($has_real_sale): ?>
                                            <span class="<?= $is_compact ? 'text-sm' : 'text-lg sm:text-xl'; ?> price-futuristic" style="color: #FF1A27;"><?= wc_price($sale); ?></span>
                                            <span class="text-xs text-gray-400 line-through"><?= wc_price($regular); ?></span>
                                        <?php elseif ($atts['force_sale']): ?>
                                            <span class="<?= $is_compact ? 'text-sm' : 'text-lg sm:text-xl'; ?> price-futuristic" style="color: #09146E;"><?= wc_price($regular); ?></span>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background: rgba(254,171,13,0.15); color: #92400e;">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                                                Oferta
                                            </span>
                                        <?php else: ?>
                                            <span class="<?= $is_compact ? 'text-sm' : 'text-lg sm:text-xl'; ?> price-futuristic" style="color: #09146E;"><?= $product->get_price_html(); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cart-card-actions" data-product_id="<?= $product->get_id(); ?>">
                                    <?php if ($in_cart): ?>
                                    <div class="cart-card-stepper <?= $is_compact ? 'cart-card-stepper-sm' : ''; ?>" data-product_id="<?= $product->get_id(); ?>">
                                        <button class="cart-card-stepper-btn cart-card-stepper-minus" data-product_id="<?= $product->get_id(); ?>">−</button>
                                        <span class="cart-card-stepper-value"><?= $cart_qty; ?></span>
                                        <button class="cart-card-stepper-btn cart-card-stepper-plus" data-product_id="<?= $product->get_id(); ?>">+</button>
                                    </div>
                                    <?php else: ?>
                                    <a href="<?= $product->add_to_cart_url(); ?>"
                                       class="flex items-center justify-center gap-2 w-full <?= $is_compact ? 'py-1.5 text-xs' : 'py-2.5 text-sm'; ?> font-semibold rounded-xl transition-all duration-300 shadow-sm"
                                       style="background: linear-gradient(135deg, #09146E 0%, #0a1a7a 100%); color: white;"
                                       onmouseover="this.style.background='linear-gradient(135deg, #5A7D43 0%, #4a6a36 100%)'; this.style.boxShadow='0 4px 16px rgba(90,125,67,0.3)'"
                                       onmouseout="this.style.background='linear-gradient(135deg, #09146E 0%, #0a1a7a 100%)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'"
                                       data-product_id="<?= $product->get_id(); ?>">
                                        <svg class="<?= $is_compact ? 'w-3 h-3' : 'w-4 h-4'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                        Comprar
                                    </a>
                                    <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>

            </div>
        </div>
        <?php if (!$is_grid): ?>
        <button class="carousel-arrow carousel-arrow-prev absolute top-1/2 -translate-y-1/2 z-40 w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center cursor-pointer select-none"
                style="left: 8px; background: rgba(255,255,255,0.95); box-shadow: 0 4px 20px rgba(9,20,110,0.15); color: #09146E; border: 1px solid rgba(9,20,110,0.12);"
                data-carousel="<?= $section_id; ?>" aria-label="Anterior">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button class="carousel-arrow carousel-arrow-next absolute top-1/2 -translate-y-1/2 z-40 w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center cursor-pointer select-none"
                style="right: 8px; background: rgba(255,255,255,0.95); box-shadow: 0 4px 20px rgba(9,20,110,0.15); color: #09146E; border: 1px solid rgba(9,20,110,0.12);"
                data-carousel="<?= $section_id; ?>" aria-label="Siguiente">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
        <?php endif; ?>
    </section>
    <?php
}

// Contact float button - shortcode
add_shortcode('farmapaz_whatsapp', 'farmapaz_whatsapp_btn');
function farmapaz_whatsapp_btn() {
    $link = farmapaz_contact_link();
    return sprintf(
        '<a href="%s" target="_blank" rel="noopener" class="whatsapp-float" aria-label="Contáctanos">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        </a>',
        esc_url($link)
    );
}

// Proxy product images through local server (bypass Cloudflare hotlink protection)
$proxy_base = home_url('/wp-content/themes/farmapaz-theme/proxy-image.php?url=');

add_filter('wp_get_attachment_url', function($url) use ($proxy_base) {
    if (strpos($url, 'localhost') !== false) {
        $path = parse_url($url, PHP_URL_PATH);
        $url = $proxy_base . 'wp-content/uploads/' . ltrim(basename($path), '/');
    }
    return $url;
});
add_filter('wp_calculate_image_srcset', function($sources) use ($proxy_base) {
    foreach ($sources as &$source) {
        if (strpos($source['url'], 'localhost') !== false) {
            $path = parse_url($source['url'], PHP_URL_PATH);
            $source['url'] = $proxy_base . 'wp-content/uploads/' . ltrim(basename($path), '/');
        }
    }
    return $sources;
});
add_filter('woocommerce_get_product_image', function($html) use ($proxy_base) {
    return preg_replace_callback('/https?:\/\/localhost[^\s"\'<>]+/', function($m) use ($proxy_base) {
        $path = parse_url($m[0], PHP_URL_PATH);
        return $proxy_base . 'wp-content/uploads/' . ltrim(basename($path), '/');
    }, $html);
});

// Product image fallback — WebP primary, JPEG fallback, PNG last resort
add_filter('woocommerce_placeholder_img_src', 'farmapaz_placeholder_img');
function farmapaz_placeholder_img() {
    return farmapaz_fallback_img_url();
}
function farmapaz_fallback_img_url($ext = 'webp') {
    $base = get_template_directory_uri() . '/assets/images/producto';
    if ($ext === 'webp') return $base . '.webp';
    if ($ext === 'jpg') return $base . '.jpg';
    return $base . '.png';
}
function farmapaz_product_img_fallback($url) {
    if (!$url || strpos($url, 'Gemini_Generated') !== false) {
        return farmapaz_fallback_img_url();
    }
    // Already proxied — return as-is
    if (strpos($url, 'proxy-image.php') !== false) {
        return $url;
    }
    if (strpos($url, 'localhost') !== false) {
        $proxy = home_url('/wp-content/themes/farmapaz-theme/proxy-image.php?url=');
        $path = parse_url($url, PHP_URL_PATH);
        return $proxy . 'wp-content/uploads/' . ltrim(basename($path), '/');
    }
    return $url;
}
/**
 * Check whether the .webp sibling of an image URL exists on disk.
 * Only reliable for URLs that map to the local filesystem (uploads/theme assets).
 * Proxied or remote URLs return false so callers skip the WebP source.
 */
function farmapaz_has_webp($url) {
    if (strpos($url, 'proxy-image.php') !== false) {
        return false;
    }
    $path = parse_url($url, PHP_URL_PATH);
    if (!$path) {
        return false;
    }
    $uploads = wp_upload_dir();
    $base_url = untrailingslashit($uploads['baseurl']);
    $base_dir = untrailingslashit($uploads['basedir']);
    $theme_url = untrailingslashit(get_template_directory_uri());
    $theme_dir = untrailingslashit(get_template_directory());

    if (strpos($url, $base_url) === 0) {
        $local = $base_dir . substr($path, strlen(parse_url($base_url, PHP_URL_PATH)));
    } elseif (strpos($url, $theme_url) === 0) {
        $local = $theme_dir . substr($path, strlen(parse_url($theme_url, PHP_URL_PATH)));
    } else {
        return false;
    }
    $local = urldecode($local);
    $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $local);
    return $webp !== $local && file_exists($webp);
}

/**
 * Output a <picture> element with WebP/JPEG fallback.
 * If $url is the fallback image, use optimized versions.
 * Otherwise, try WebP first (if the .webp file actually exists) with the original as <img>.
 */
function farmapaz_picture($url, $alt = '', $class = '', $loading = 'lazy') {
    $fallback_webp = farmapaz_fallback_img_url('webp');
    $fallback_jpg  = farmapaz_fallback_img_url('jpg');
    $is_fallback = ($url === $fallback_webp || $url === $fallback_jpg || strpos($url, 'producto.png') !== false);

    if ($is_fallback) {
        echo '<picture>';
        echo '<source srcset="' . esc_url($fallback_webp) . '" type="image/webp">';
        echo '<img src="' . esc_url($fallback_jpg) . '" alt="' . esc_attr($alt) . '" class="' . esc_attr($class) . '" loading="' . $loading . '">';
        echo '</picture>';
        return;
    }

    // Regular image — only serve WebP when the .webp file actually exists.
    // Emitting a <source> to a non-existent .webp makes the browser select it
    // (WebP is preferred) and the image renders blank, ignoring the <img> fallback.
    $real_url = $url;
    if (strpos($url, 'proxy-image.php') !== false && preg_match('/url=(.+)/', $url, $qm)) {
        $real_url = $qm[1];
    }
    $webp_url = preg_replace('/\.(jpe?g|png)$/i', '.webp', $url);
    $ext = strtolower(pathinfo(parse_url($real_url, PHP_URL_PATH), PATHINFO_EXTENSION));

    if (in_array($ext, ['jpg', 'jpeg', 'png']) && farmapaz_has_webp($url)) {
        echo '<picture>';
        echo '<source srcset="' . esc_url($webp_url) . '" type="image/webp">';
        echo '<img src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" class="' . esc_attr($class) . '" loading="' . $loading . '">';
        echo '</picture>';
    } else {
        echo '<img src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" class="' . esc_attr($class) . '" loading="' . $loading . '">';
    }
}

// Category SVG icons map
function farmapaz_cat_icon($slug) {
    $icons = [
        'ahorra-con-farmapaz'              => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'alimentos-y-bebidas'              => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'bebes-y-maternidad'               => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
        'cuidado-facial'                   => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'cuidado-personal-y-dermocosmeticos' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>',
        'medicamentos'                     => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        'salud-y-bienestar'                => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
        'hogar-mascotas-y-otros'           => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
        'nuevos-ingresos'                  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>',
        'por-clasificar'                   => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>',
    ];
    return $icons[$slug] ?? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
}

function farmapaz_cat_display_name($slug) {
    $names = [
        'ahorra-con-farmapaz'              => 'Ahorra con Farmapaz',
        'alimentos-y-bebidas'              => 'Alimentos y Bebidas',
        'bebes-y-maternidad'               => 'Bebés y Maternidad',
        'cuidado-facial'                   => 'Skincare',
        'cuidado-personal-y-dermocosmeticos' => 'Dermocosméticos',
        'medicamentos'                     => 'Medicamentos',
        'salud-y-bienestar'                => 'Salud y Bienestar',
        'hogar-mascotas-y-otros'           => 'Hogar, Mascotas y Otros',
        'nuevos-ingresos'                  => 'Nuevos Ingresos',
    ];
    return $names[$slug] ?? ucwords(str_replace(['-', '_'], ' ', $slug));
}

// Homepage carousels — configurable from Ajustes de Tienda
define('FARMA_HOME_CAROUSELS_OPTION', 'farmapaz_home_carousels');

function farmapaz_home_carousel_defaults() {
    return [
        [
            'title'    => 'Ahorra con Farmapaz',
            'subtitle' => 'Ofertas exclusivas y descuentos especiales',
            'category' => 'ahorra-con-farmapaz',
            'limit'    => 15,
            'orderby'  => 'rand',
            'force_sale' => true,
            'layout'   => 'carousel',
            'bg_tone'  => 'brand-blue',
        ],
        [
            'title'    => 'Belleza y Cuidado Personal',
            'subtitle' => 'Dermocosmetica, maquillaje y cuidado diario',
            'category' => 'cuidado-personal-y-dermocosmeticos',
            'limit'    => 12,
            'orderby'  => 'rand',
            'force_sale' => false,
            'layout'   => 'grid',
            'bg_tone'  => 'lighter',
        ],
        [
            'title'    => 'Alimentos y Bebidas',
            'subtitle' => 'Snacks, nutricion y bebidas para toda la familia',
            'category' => 'alimentos-y-bebidas',
            'limit'    => 12,
            'orderby'  => 'date',
            'force_sale' => false,
            'layout'   => 'carousel',
            'bg_tone'  => 'brand-blue',
        ],
        [
            'title'    => 'Salud y Bienestar',
            'subtitle' => 'Vitaminas, suplementos y productos para tu salud',
            'category' => 'salud-y-bienestar',
            'limit'    => 15,
            'orderby'  => 'rand',
            'force_sale' => false,
            'layout'   => 'grid',
            'bg_tone'  => 'brand-blue',
        ],
        [
            'title'    => 'Productos para el hogar',
            'subtitle' => 'Productos destacados para el hogar mascotas y otros',
            'category' => 'hogar-mascotas-y-otros',
            'limit'    => 12,
            'orderby'  => 'date',
            'force_sale' => false,
            'layout'   => 'carousel',
            'bg_tone'  => 'white',
        ],
    ];
}

function farmapaz_home_carousels() {
    $saved = get_option(FARMA_HOME_CAROUSELS_OPTION, null);
    if ($saved === null || $saved === '' || !is_array($saved) || empty($saved)) {
        return farmapaz_home_carousel_defaults();
    }
    $rows = array_values(array_filter($saved, function($r) {
        return is_array($r) && !empty($r['category']);
    }));
    return $rows ? $rows : farmapaz_home_carousel_defaults();
}

// SVG icon helper
function farmapaz_icon($name) {
    $icons = [
        'phone' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>',
        'location' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'clock' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'search' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>',
        'cart' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>',
        'user' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
        'chevron-right' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>',
        'chevron-left' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>',
        'star' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>',
        'instagram' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>',
        'facebook' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        'truck' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>',
        'shield' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
    ];
    return $icons[$name] ?? '';
}

// ===================== SKU SEARCH =====================
// Extend WP_Query so "s" also matches the _sku post meta and uses a FULLTEXT
// index for fast text search on the huge catalog. Applies globally (header
// search, shop AJAX, suggest endpoint, CapiBlue).
add_filter('posts_join', 'farmapaz_search_sku_join', 10, 2);
add_filter('posts_search', 'farmapaz_search_sku_where', 10, 2);

function farmapaz_search_sku_join($join, $query) {
    global $wpdb;
    $s = $query->get('s');
    if (empty($s) || $query->get('post_type') === 'nav_menu_item') return $join;
    // Skip pure admin screen queries, but keep admin-ajax.php (shop filters).
    if (is_admin() && !wp_doing_ajax()) return $join;
    // Only extend queries that are searching products (directly or via post_type array).
    $pt = $query->get('post_type');
    $is_product = ($pt === 'product') || (is_array($pt) && in_array('product', $pt, true));
    if (!$is_product && !$query->is_search()) return $join;
    if (strpos($join, 'farmapaz_sku_meta') !== false) return $join;
    $join .= " LEFT JOIN {$wpdb->postmeta} farmapaz_sku_meta ON {$wpdb->posts}.ID = farmapaz_sku_meta.post_id AND farmapaz_sku_meta.meta_key = '_sku' ";
    return $join;
}

function farmapaz_search_sku_where($search, $query) {
    global $wpdb;
    $s = $query->get('s');
    if (empty($s)) return $search;
    if (is_admin() && !wp_doing_ajax()) return $search;
    $pt = $query->get('post_type');
    $is_product = ($pt === 'product') || (is_array($pt) && in_array('product', $pt, true));
    if (!$is_product && !$query->is_search()) return $search;

    $len = mb_strlen($s);
    $esc = $wpdb->esc_like($s);

    // SKU always matches via subquery (uses the post_id+meta_key index, does not
    // inflate the query plan like a plain JOIN on postmeta does).
    $sku = $wpdb->prepare(
        " EXISTS ( SELECT 1 FROM {$wpdb->postmeta} fm WHERE fm.post_id = {$wpdb->posts}.ID AND fm.meta_key = '_sku' AND fm.meta_value LIKE %s )",
        '%' . $esc . '%'
    );

    // Terms >= 3 chars go through the FULLTEXT index (fast on 10k+ products).
    // Shorter terms fall back to a title LIKE (FULLTEXT ignores sub-3-char words).
    if ($len >= 3) {
        $clean = addcslashes($s, '+-<>()~*" ');
        $ft = $wpdb->prepare(
            " MATCH({$wpdb->posts}.post_title, {$wpdb->posts}.post_content, {$wpdb->posts}.post_excerpt) AGAINST(%s IN BOOLEAN MODE)",
            '+' . $clean . '*'
        );
        return " AND ( " . $ft . " OR " . $sku . " )";
    }

    $title = $wpdb->prepare(
        " {$wpdb->posts}.post_title LIKE %s ",
        '%' . $esc . '%'
    );
    return " AND ( " . $title . " OR " . $sku . " )";
}

// Ensure the FULLTEXT index exists (runs once; safe to call on every request
// because SHOW INDEX + conditional ALTER is cheap when already present).
add_action('init', 'farmapaz_ensure_fulltext_index', 20);
function farmapaz_ensure_fulltext_index() {
    global $wpdb;
    if (get_option('farmapaz_ft_index_checked')) return;
    $exists = $wpdb->get_results("SHOW INDEX FROM {$wpdb->posts} WHERE Key_name = 'farmapaz_search_idx'");
    if (empty($exists)) {
        $wpdb->query("ALTER TABLE {$wpdb->posts} ADD FULLTEXT farmapaz_search_idx (post_title, post_content, post_excerpt)");
    }
    update_option('farmapaz_ft_index_checked', 1, false);
}

// Expose the delivery method as a public order field in the WooCommerce REST API
add_action('rest_api_init', function () {
    register_rest_field('shop_order', 'farmapaz_delivery_method', [
        'get_callback'    => function ($order) {
            return get_order_meta_compat($order['id'], '_farmapaz_delivery_method') ?: 'delivery';
        },
        'update_callback' => function ($value, $order) {
            $value = sanitize_key($value);
            if (!in_array($value, ['delivery', 'pickup'], true)) {
                return new WP_Error('invalid_delivery_method', 'Debe ser delivery o pickup.', ['status' => 400]);
            }
            $order->update_meta_data('_farmapaz_delivery_method', $value);
            return true;
        },
        'schema' => [
            'type'        => 'string',
            'enum'        => ['delivery', 'pickup'],
            'description' => 'Método de entrega seleccionado por el cliente.',
        ],
    ]);
});

// Read order meta for the REST callback (works with legacy posts or HPOS)
function get_order_meta_compat($order_id, $meta_key) {
    if (function_exists('wc_get_order') && class_exists('Automattic\WooCommerce\Utilities\OrderUtil')) {
        $order = wc_get_order($order_id);
        if ($order) {
            $value = $order->get_meta($meta_key);
            if ($value !== '') return $value;
        }
    }
    $value = get_post_meta($order_id, $meta_key, true);
    return $value !== '' ? $value : null;
}

// Search suggestions REST API
add_action('rest_api_init', function () {
    register_rest_route('farmapaz/v1', '/suggest', [
        'methods'  => 'GET',
        'callback' => 'farmapaz_search_suggest',
        'args'     => [
            's'     => ['required' => true],
            'scope' => ['required' => false],
        ],
        'permission_callback' => '__return_true',
    ]);
});

// Count published, priced, in-stock products in a term — used so suggestion
// dropdowns show the same numbers the search actually renders (no agotados).
function farmapaz_term_instock_count($term) {
    global $wpdb;
    if (empty($term->term_taxonomy_id)) return (int) $term->count;
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT tr.object_id)
           FROM {$wpdb->term_relationships} tr
           INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
           INNER JOIN {$wpdb->postmeta} stock ON stock.post_id = p.ID AND stock.meta_key = '_stock_status'
           INNER JOIN {$wpdb->postmeta} price ON price.post_id = p.ID AND price.meta_key = '_price'
          WHERE tr.term_taxonomy_id = %d
            AND p.post_type = 'product'
            AND p.post_status = 'publish'
            AND stock.meta_value = 'instock'
            AND CAST(price.meta_value AS DECIMAL(10,2)) > 0
            AND NOT EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} co
                 WHERE co.post_id = p.ID
                   AND co.meta_key = '_farmapaz_catalog_only'
                   AND co.meta_value = '1'
            )",
        (int) $term->term_taxonomy_id
    ));
}

function farmapaz_search_suggest(WP_REST_Request $request) {
    $s = trim(sanitize_text_field($request->get_param('s')));
    if (strlen($s) < 2) return ['results' => []];

    $scope = sanitize_text_field($request->get_param('scope'));

    // Buscador dentro de Óptica: solo monturas, marcas y enlaces de óptica.
    if ($scope === 'optica') {
        $results = farmapaz_optica_suggest_results($s, 5);

        if (farmapaz_optica_query_is_pharmacy($s)) {
            array_unshift($results, [
                'name'  => 'Buscar “' . $s . '” en Farmacia',
                'url'   => add_query_arg(['s' => $s, 'post_type' => 'product'], home_url('/')),
                'img'   => '',
                'price' => 'Medicamentos y cuidado personal',
                'type'  => 'farmacia_search',
            ]);
        }

        $results[] = [
            'name'  => 'Ver todo el catálogo de Óptica',
            'url'   => farmapaz_optica_catalogo_url(),
            'img'   => '',
            'price' => 'Monturas, lentes de sol y examen de la vista',
            'type'  => 'optica_catalog',
        ];
        $results[] = [
            'name'  => 'Farmacia Farmapaz',
            'url'   => home_url('/'),
            'img'   => '',
            'price' => 'Medicamentos y cuidado personal — Ir a Farmacia',
            'type'  => 'farmacia',
        ];

        return ['results' => $results];
    }

    $results = [];

    // Products — fetch more to account for Gemini image filtering
    $product_query = new WP_Query([
        'post_type'      => 'product',
        's'              => $s,
        'posts_per_page' => 20,
        'post_status'    => 'publish',
        'meta_query'     => [
            ['key' => '_price', 'value' => 0, 'compare' => '>', 'type' => 'DECIMAL'], // [†] $0 excluded
            ['key' => '_stock_status', 'value' => 'instock'],
            farmapaz_optica_meta_exclude(), // [†] óptica solo en /optica/
        ],
        'tax_query'      => [[
            'taxonomy' => 'product_visibility',
            'field'    => 'name',
            'terms'    => ['exclude-from-search', 'exclude-from-catalog'],
            'operator' => 'NOT IN',
        ]],
    ]);

    foreach ($product_query->posts as $post) {
        if (count($results) >= 5) break;
        $product = wc_get_product($post->ID);
        if (!$product) continue;
        $img = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
        if (!$img || strpos($img, 'Gemini_Generated') !== false) {
            $img = farmapaz_fallback_img_url();
        }
        $results[] = [
            'name'   => $product->get_name(),
            'sku'    => $product->get_sku(),
            'url'    => $product->get_permalink(),
            'img'    => $img,
            'price'  => wp_strip_all_tags($product->get_price_html()),
            'type'   => 'product',
        ];
    }

    // Categories
    $categories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'name__like' => $s,
        'number'     => 3,
    ]);
    if (!is_wp_error($categories)) {
        foreach ($categories as $cat) {
            if ($cat->slug === 'optica-tienda') continue;
            $results[] = [
                'name'  => $cat->name,
                'url'   => get_term_link($cat),
                'img'   => '',
                'price' => farmapaz_term_instock_count($cat) . ' productos',
                'type'  => 'category',
            ];
        }
    }

    // Brands
    $brands = get_terms([
        'taxonomy'   => 'product_brand',
        'hide_empty' => true,
        'name__like' => $s,
        'number'     => 3,
    ]);
    if (!is_wp_error($brands)) {
        foreach ($brands as $brand) {
            $results[] = [
                'name'  => $brand->name,
                'url'   => get_term_link($brand),
                'img'   => '',
                'price' => farmapaz_term_instock_count($brand) . ' productos',
                'type'  => 'brand',
            ];
        }
    }

    // [†] Si la búsqueda de farmacia hace referencia a óptica, ofrecer el salto.
    if (farmapaz_optica_query_is_optics($s)) {
        array_unshift($results, [
            'name'  => 'Buscar “' . $s . '” en Óptica',
            'url'   => add_query_arg('optica_s', $s, farmapaz_optica_catalogo_url()),
            'img'   => '',
            'price' => 'Monturas, lentes de sol y examen de la vista',
            'type'  => 'optica_search',
        ]);
    }

    // [†] Referencia fija a la sección de Óptica
    $results[] = [
        'name'  => 'Óptica Farmapaz',
        'url'   => home_url('/optica/'),
        'img'   => '',
        'price' => 'Monturas, lentes de sol y examen de la vista — Visitar sección',
        'type'  => 'optica',
    ];

    return ['results' => $results];
}

// ===================== SHOP PAGE — AJAX FILTERS =====================

// Pre-get-posts: apply URL filters to initial shop query
add_action('pre_get_posts', 'farmapaz_pre_get_products');
function farmapaz_pre_get_products($q) {
    if (is_admin() || !$q->is_main_query()) return;

    if (is_search()) {
        // Force product-only search and the same visibility filters as the shop,
        // so counts match the cards actually rendered.
        $q->set('post_type', 'product');
        $q->set('posts_per_page', 24);
        $meta_q = $q->get('meta_query');
        if (!is_array($meta_q)) $meta_q = [];
        $meta_q[] = ['key' => '_price', 'value' => 0, 'compare' => '>', 'type' => 'DECIMAL'];
        $meta_q[] = ['key' => '_stock_status', 'value' => 'instock'];
        if (function_exists('farmapaz_optica_meta_exclude')) $meta_q[] = farmapaz_optica_meta_exclude(); // [†] óptica solo en /optica/
        $q->set('meta_query', $meta_q);
        return;
    }

    if (is_shop() || is_product_category() || is_product_tag()) {
        // Stock status filter
        $meta_q = $q->get('meta_query');
        if (!is_array($meta_q)) $meta_q = [];
        $meta_q[] = ['key' => '_price', 'value' => 0, 'compare' => '>', 'type' => 'DECIMAL']; // [†] $0 excluded
        $meta_q[] = ['key' => '_stock_status', 'value' => 'instock'];
        if (function_exists('farmapaz_optica_meta_exclude')) $meta_q[] = farmapaz_optica_meta_exclude(); // [†] óptica solo en /optica/
        $q->set('meta_query', $meta_q);

        // Category filter from URL
        $cats = isset($_GET['cat']) ? array_map('sanitize_title', (array)$_GET['cat']) : [];
        if (!empty($cats)) {
            $tax_q = $q->get('tax_query');
            if (!is_array($tax_q)) $tax_q = [];
            $tax_q[] = [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $cats,
                'operator' => 'IN',
            ];
            $q->set('tax_query', $tax_q);
        }

        // Brand filter from URL
        $brands = isset($_GET['brand']) ? array_map('sanitize_title', (array)$_GET['brand']) : [];
        if (!empty($brands)) {
            $tax_q = $q->get('tax_query');
            if (!is_array($tax_q)) $tax_q = [];
            $tax_q[] = [
                'taxonomy' => 'product_brand',
                'field'    => 'slug',
                'terms'    => $brands,
                'operator' => 'IN',
            ];
            $q->set('tax_query', $tax_q);
        }

        // Relation AND if both
        if (!empty($cats) && !empty($brands)) {
            $tax_q = $q->get('tax_query');
            if (is_array($tax_q)) {
                $tax_q['relation'] = 'AND';
                $q->set('tax_query', $tax_q);
            }
        }

        // Price range
        $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
        $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
        if ($min_price > 0 || $max_price > 0) {
            $price_q = ['key' => '_price', 'type' => 'DECIMAL'];
            if ($min_price > 0 && $max_price > 0) {
                $price_q['value'] = [$min_price, $max_price];
                $price_q['compare'] = 'BETWEEN';
            } elseif ($min_price > 0) {
                $price_q['value'] = $min_price;
                $price_q['compare'] = '>=';
            } else {
                $price_q['value'] = $max_price;
                $price_q['compare'] = '<=';
            }
            $meta_q = $q->get('meta_query');
            if (!is_array($meta_q)) $meta_q = [];
            $meta_q[] = $price_q;
            $q->set('meta_query', $meta_q);
        }

        // Search
        $s = isset($_GET['s']) ? trim(sanitize_text_field($_GET['s'])) : '';
        if ($s) {
            $q->set('s', $s);
        }

        // Orderby
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : '';
        // ID tiebreaker keeps pagination deterministic when many products share
        // the same post_date (otherwise MySQL picks an unstable subset per page).
        if ($orderby) {
            switch ($orderby) {
                case 'price':
                    $q->set('orderby', ['meta_value_num' => 'ASC', 'ID' => 'ASC']);
                    $q->set('meta_key', '_price');
                    $q->set('order', 'ASC');
                    break;
                case 'price-desc':
                    $q->set('orderby', ['meta_value_num' => 'DESC', 'ID' => 'DESC']);
                    $q->set('meta_key', '_price');
                    $q->set('order', 'DESC');
                    break;
                case 'popularity':
                    $q->set('orderby', ['meta_value_num' => 'DESC', 'ID' => 'DESC']);
                    $q->set('meta_key', 'total_sales');
                    $q->set('order', 'DESC');
                    break;
            }
        } else {
            $q->set('orderby', ['date' => 'DESC', 'ID' => 'DESC']);
            $q->set('order', 'DESC');
        }

        // Pagination from URL
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 0;
        if ($page > 1) {
            $q->set('paged', $page);
        }

        // Products per page
        $q->set('posts_per_page', FARMA_PER_PAGE);
    }
}

add_action('wp_ajax_farmapaz_shop_filter', 'farmapaz_shop_filter_ajax');
add_action('wp_ajax_nopriv_farmapaz_shop_filter', 'farmapaz_shop_filter_ajax');
function farmapaz_shop_filter_ajax() {
    check_ajax_referer('farmapaz_nonce', 'nonce');

    $paged = max(1, isset($_POST['page']) ? (int)$_POST['page'] : 1);
    $per_page = FARMA_PER_PAGE;

    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        // The total count is only needed once — skip SQL_CALC_FOUND_ROWS on
        // subsequent scroll pages to cut ~2x the query time.
        'no_found_rows'  => $paged > 1,
        'meta_query'     => [
            ['key' => '_price', 'value' => 0, 'compare' => '>', 'type' => 'DECIMAL'], // [†] $0 excluded
            ['key' => '_stock_status', 'value' => 'instock'],
            farmapaz_optica_meta_exclude(), // [†] óptica solo en /optica/
        ],
    ];

    // Categories
    $cats = !empty($_POST['cat']) ? array_map('sanitize_title', (array)$_POST['cat']) : [];
    if (!empty($cats)) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $cats,
            'operator' => 'IN',
        ];
    }

    // Brands
    $brands = !empty($_POST['brand']) ? array_map('sanitize_title', (array)$_POST['brand']) : [];
    if (!empty($brands)) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_brand',
            'field'    => 'slug',
            'terms'    => $brands,
            'operator' => 'IN',
        ];
    }

    // If both cats and brands are set, add relation AND
    if (!empty($cats) && !empty($brands)) {
        $args['tax_query']['relation'] = 'AND';
    }

    // Price range
    $min_price = isset($_POST['min_price']) ? floatval($_POST['min_price']) : 0;
    $max_price = isset($_POST['max_price']) ? floatval($_POST['max_price']) : 0;
    if ($min_price > 0 || $max_price > 0) {
        $meta_query_price = ['key' => '_price', 'type' => 'DECIMAL'];
        if ($min_price > 0) $meta_query_price['value'][] = $min_price;
        if ($max_price > 0) $meta_query_price['value'][] = $max_price;
        if ($min_price > 0 && $max_price > 0) {
            $meta_query_price['compare'] = 'BETWEEN';
        } elseif ($min_price > 0) {
            $meta_query_price['compare'] = '>=';
        } else {
            $meta_query_price['compare'] = '<=';
        }
        $args['meta_query'][] = $meta_query_price;
    }

    // Search
    $search = isset($_POST['s']) ? trim(sanitize_text_field($_POST['s'])) : '';
    if ($search) {
        $args['s'] = $search;
    }

    // Sorting
    $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : '';
    // ID tiebreaker: same as pre_get_posts so scroll pages line up with the
    // server-rendered first page and stay stable across ties.
    switch ($orderby) {
        case 'price':
            $args['orderby'] = ['meta_value_num' => 'ASC', 'ID' => 'ASC'];
            $args['meta_key'] = '_price';
            $args['order'] = 'ASC';
            break;
        case 'price-desc':
            $args['orderby'] = ['meta_value_num' => 'DESC', 'ID' => 'DESC'];
            $args['meta_key'] = '_price';
            $args['order'] = 'DESC';
            break;
        case 'popularity':
            $args['orderby'] = ['meta_value_num' => 'DESC', 'ID' => 'DESC'];
            $args['meta_key'] = 'total_sales';
            $args['order'] = 'DESC';
            break;
        case 'rating':
            $args['orderby'] = ['meta_value_num' => 'DESC', 'ID' => 'DESC'];
            $args['meta_key'] = '_wc_average_rating';
            $args['order'] = 'DESC';
            break;
        default:
            $args['orderby'] = ['date' => 'DESC', 'ID' => 'DESC'];
            $args['order'] = 'DESC';
    }

    $query = new WP_Query($args);

    ob_start();
    if ($query->have_posts()):
        while ($query->have_posts()): $query->the_post();
            wc_get_template_part('content', 'product');
        endwhile;
    else:
        echo farmapaz_empty_state_html($search, $orderby);
    endif;
    $products_html = ob_get_clean();

    ob_start();
    if ($paged > 1) {
        // With no_found_rows we can't know the real page count — derive it from
        // how many items came back (a full page means there's likely more).
        $total_pages = null;
        $has_more    = count($query->posts) >= $per_page;
    } else {
        $total_pages = $query->max_num_pages;
        $has_more    = $paged < $total_pages;
    }
    $current_page = $paged;
    if ($total_pages) farmapaz_shop_pagination_html($total_pages, $current_page);
    $pagination_html = ob_get_clean();

    wp_reset_postdata();

    ob_start();
    farmapaz_render_active_filters($cats, $brands, $min_price, $max_price, $search, $orderby);
    $active_filters_html = ob_get_clean();

    ob_start();
    farmapaz_render_hero_indicator($cats, $brands);
    $hero_indicator_html = ob_get_clean();

    wp_send_json([
        'products'        => $products_html,
        'count'           => $query->found_posts,
        'page'            => $current_page,
        'total_pages'     => $total_pages,
        'has_more'        => $has_more,
        'active_filters'  => $active_filters_html,
        'hero_indicator'  => $hero_indicator_html,
    ]);
}

// Empty state for shop/search grids — shared by AJAX, archive and search templates
function farmapaz_empty_state_html($search = '', $orderby = 'date') {
    $shop_url = get_permalink(wc_get_page_id('shop'));
    if ($search) {
        $message = sprintf('No encontramos nada que coincida con "%s". Verifica la ortografía, intenta con otro término o busca por el código del producto.', esc_html($search));
    } else {
        $message = 'No se encontraron productos con estos filtros.';
    }
    ?>
    <div class="shop-empty">
        <div class="shop-empty-icon">
            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <p class="shop-empty-text"><?= $message; ?></p>
        <a href="<?= esc_url($shop_url); ?>" class="shop-empty-link">Ver toda la tienda</a>
    </div>
    <?php if (farmapaz_optica_enabled()): ?>
    <div class="shop-optica-banner">
        <a href="<?= esc_url(home_url('/optica/')); ?>" class="shop-optica-banner-link">
            <span class="shop-optica-banner-icon">
                <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="6.5" cy="14" r="3.5"></circle><circle cx="17.5" cy="14" r="3.5"></circle><path d="M10 14h4M3 9l2-3h3M21 9l-2-3h-3"></path></svg>
            </span>
            <span class="shop-optica-banner-text">
                <strong>¿Buscas monturas o lentes de sol?</strong>
                Visita nuestra sección de Óptica Farmapaz: marcas originales, examen de la vista gratis y asesoría en tiendas.
            </span>
            <span class="shop-optica-banner-cta">Visitar Óptica
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </span>
        </a>
    </div>
    <?php endif;
}

// Shared pagination HTML renderer
function farmapaz_shop_pagination_html($total_pages, $current_page) {
    if ($total_pages <= 1) return; ?>
    <nav class="shop-pagination">
        <?php if ($current_page > 1): ?>
            <a href="#" class="page-numbers prev" data-page="<?= $current_page - 1; ?>">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
        <?php endif;
        $range = 2;
        $start = max(1, $current_page - $range);
        $end = min($total_pages, $current_page + $range);
        if ($start > 1): ?>
            <a href="#" class="page-numbers" data-page="1">1</a>
            <?php if ($start > 2): ?><span class="page-numbers dots">...</span><?php endif;
        endif;
        for ($i = $start; $i <= $end; $i++):
            $active = $i === $current_page ? ' current' : ''; ?>
            <a href="#" class="page-numbers<?= $active; ?>" data-page="<?= $i; ?>"><?= $i; ?></a>
        <?php endfor;
        if ($end < $total_pages):
            if ($end < $total_pages - 1): ?><span class="page-numbers dots">...</span><?php endif; ?>
            <a href="#" class="page-numbers" data-page="<?= $total_pages; ?>"><?= $total_pages; ?></a>
        <?php endif;
        if ($current_page < $total_pages): ?>
            <a href="#" class="page-numbers next" data-page="<?= $current_page + 1; ?>">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        <?php endif; ?>
    </nav>
    <?php
}

// Render filter sidebar
function farmapaz_render_filters($current_cats = [], $current_brands = [], $current_min = '', $current_max = '', $current_s = '') {
    global $wp;
    $base_url = home_url($wp->request);
    ?>
    <div class="shop-filters">
        <!-- Categories (hierarchical) -->
        <?php if (farmapaz_cat_filters_enabled()): ?>
        <div class="filter-block">
            <button class="filter-title filter-toggle" data-target="cat-list">
                Categorías
                <svg class="filter-chevron" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="filter-list" id="cat-list">
                <?php
                $parent_cats = get_terms([
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => true,
                    'parent'     => 0,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ]);
                if (!is_wp_error($parent_cats)):
                    foreach ($parent_cats as $parent):
                        $checked = in_array($parent->slug, $current_cats) ? 'checked' : '';
                        $children = get_terms([
                            'taxonomy'   => 'product_cat',
                            'hide_empty' => true,
                            'parent'     => $parent->term_id,
                            'orderby'    => 'name',
                            'order'      => 'ASC',
                        ]);
                ?>
                <label class="filter-option filter-option-parent">
                    <input type="checkbox" class="filter-check filter-cat" value="<?= esc_attr($parent->slug); ?>" <?= $checked; ?>>
                    <span class="filter-label-text" style="font-weight: 700;"><?= esc_html(farmapaz_cat_display_name($parent->slug)); ?></span>
                    <span class="filter-count"><?= $parent->count; ?></span>
                </label>
                <?php if (!empty($children) && !is_wp_error($children)): ?>
                <div class="filter-children">
                    <?php foreach ($children as $child):
                        $checked = in_array($child->slug, $current_cats) ? 'checked' : '';
                    ?>
                    <label class="filter-option filter-option-child">
                        <input type="checkbox" class="filter-check filter-cat" value="<?= esc_attr($child->slug); ?>" <?= $checked; ?>>
                        <span class="filter-label-text"><?= esc_html(farmapaz_cat_display_name($child->slug)); ?></span>
                        <span class="filter-count"><?= $child->count; ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Brands -->
        <?php if (farmapaz_brand_filters_enabled()): ?>
        <div class="filter-block">
            <button class="filter-title filter-toggle" data-target="brand-list">
                Marcas
                <svg class="filter-chevron" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="filter-list" id="brand-list">
                <div class="filter-brand-search-wrap">
                    <svg class="filter-brand-search-icon" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" class="filter-brand-search" placeholder="Buscar marca...">
                </div>
                <div class="filter-brand-list">
                <?php
                $brands_terms = get_terms([
                    'taxonomy'   => 'product_brand',
                    'hide_empty' => true,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ]);
                if (!is_wp_error($brands_terms)):
                    foreach ($brands_terms as $brand):
                        $checked = in_array($brand->slug, $current_brands) ? 'checked' : '';
                ?>
                <label class="filter-option" data-brand="<?= esc_attr(strtolower($brand->name)); ?>">
                    <input type="checkbox" class="filter-check filter-brand" value="<?= esc_attr($brand->slug); ?>" <?= $checked; ?>>
                    <span class="filter-label-text"><?= esc_html($brand->name); ?></span>
                    <span class="filter-count"><?= $brand->count; ?></span>
                </label>
                <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Price Range -->
        <div class="filter-block">
            <h3 class="filter-title">Precio</h3>
            <div class="filter-price-row">
                <input type="number" class="filter-input filter-price-min" placeholder="Desde" value="<?= esc_attr($current_min); ?>" min="0">
                <span class="filter-price-sep">—</span>
                <input type="number" class="filter-input filter-price-max" placeholder="Hasta" value="<?= esc_attr($current_max); ?>" min="0">
            </div>
        </div>
    </div>
    <?php
}

// Render hero category/brand indicator
function farmapaz_render_hero_indicator($current_cats = [], $current_brands = []) {
    $cat_obj = !empty($current_cats) ? get_term_by('slug', $current_cats[0], 'product_cat') : null;
    $brand_obj = !empty($current_brands) ? get_term_by('slug', $current_brands[0], 'product_brand') : null; ?>
    <?php if ($cat_obj): ?>
    <div class="shop-hero-indicator">
        <span class="shop-hero-indicator-icon" style="color: #5A7D43;">
            <?= farmapaz_cat_icon($cat_obj->slug); ?>
        </span>
        <span class="shop-hero-indicator-label">Categoría:</span>
        <span class="shop-hero-indicator-value"><?= esc_html(farmapaz_cat_display_name($cat_obj->slug)); ?></span>
    </div>
    <?php endif; ?>
    <?php if ($brand_obj): ?>
    <div class="shop-hero-indicator">
        <svg class="shop-hero-indicator-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4h6m0 0v6m0-6L14 14m-4-4l-4 4m8 0l-4 4"/></svg>
        <span class="shop-hero-indicator-label">Marca:</span>
        <span class="shop-hero-indicator-value"><?= esc_html($brand_obj->name); ?></span>
    </div>
    <?php endif; ?>
    <?php
}

// Render active filters
function farmapaz_render_active_filters($current_cats = [], $current_brands = [], $current_min = '', $current_max = '', $current_s = '', $current_orderby = 'date') {
    $has_filters = $current_cats || $current_brands || $current_min || $current_max || $current_s;
    ?>
    <div class="active-filters"<?= $has_filters ? '' : ' style="display:none"'; ?>>
        <?php if ($has_filters): ?>
        <span class="active-filters-label">Filtros activos:</span>
        <div class="active-filters-list">
            <?php foreach ($current_cats as $slug):
                $term = get_term_by('slug', $slug, 'product_cat');
                if ($term):
            ?>
            <button class="active-filter-tag" data-remove="cat" data-value="<?= esc_attr($slug); ?>">
                <?= esc_html(farmapaz_cat_display_name($term->slug)); ?>
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <?php endif; endforeach; ?>
            <?php foreach ($current_brands as $slug):
                $term = get_term_by('slug', $slug, 'product_brand');
                if ($term):
            ?>
            <button class="active-filter-tag" data-remove="brand" data-value="<?= esc_attr($slug); ?>">
                <?= esc_html($term->name); ?>
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <?php endif; endforeach; ?>
            <?php if ($current_min || $current_max): ?>
            <button class="active-filter-tag" data-remove="price">
                Precio: <?= $current_min ? '$' . $current_min : '$0'; ?> — <?= $current_max ? '$' . $current_max : '∞'; ?>
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <?php endif; ?>
            <?php if ($current_s): ?>
            <button class="active-filter-tag" data-remove="s">
                "<?= esc_html($current_s); ?>"
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <?php endif; ?>
            <a href="<?= get_permalink(wc_get_page_id('shop')); ?>" class="active-filter-clear-all">Limpiar todo</a>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

// ====== CAPIBLUE — CHATBOT IA DE FARMApAZ ======

define('FARMA_CAPIBLUE_OPTION', 'farmapaz_gemini_key');
define('FARMA_OPENROUTER_KEY_OPTION', 'farmapaz_openrouter_key');
define('FARMA_OPENROUTER_MODEL_OPTION', 'farmapaz_openrouter_model');
define('FARMA_GROQ_KEY_OPTION', 'farmapaz_groq_key');
define('FARMA_GROQ_MODEL_OPTION', 'farmapaz_groq_model');


// Admin page for AI providers
add_action('admin_menu', function () {
    add_options_page('CapiBlue AI — Configuración', 'CapiBlue AI', 'manage_options', 'capiblue', function () {
        if (!current_user_can('manage_options')) wp_die('Acceso denegado');
        $gemini_result = '';
        $or_result = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Save Gemini key
            if (isset($_POST['gemini_key'])) {
                update_option(FARMA_CAPIBLUE_OPTION, trim(sanitize_text_field($_POST['gemini_key'])));
                echo '<div class="updated"><p>Gemini API Key guardada.</p></div>';
            }
            // Save OpenRouter key + model
            if (isset($_POST['openrouter_key'])) {
                update_option(FARMA_OPENROUTER_KEY_OPTION, trim(sanitize_text_field($_POST['openrouter_key'])));
            }
            if (isset($_POST['openrouter_model'])) {
                update_option(FARMA_OPENROUTER_MODEL_OPTION, sanitize_text_field($_POST['openrouter_model']));
                echo '<div class="updated"><p>OpenRouter configuración guardada.</p></div>';
            }

            // Test Gemini
            if (isset($_POST['test_gemini'])) {
                $key = get_option(FARMA_CAPIBLUE_OPTION, '');
                if ($key) {
                    $test = wp_remote_post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $key, [
                        'headers' => ['Content-Type' => 'application/json'],
                        'body'    => json_encode(['contents' => [['role' => 'user', 'parts' => [['text' => 'Hola']]]]]),
                        'timeout' => 10,
                    ]);
                    if (is_wp_error($test)) {
                        $gemini_result = '<div class="notice notice-error"><p>Error: ' . $test->get_error_message() . '</p></div>';
                    } else {
                        $body = json_decode(wp_remote_retrieve_body($test), true);
                        if (isset($body['error'])) {
                            $gemini_result = '<div class="notice notice-error"><p>Error: ' . esc_html($body['error']['message']) . '</p></div>';
                        } elseif (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                            $gemini_result = '<div class="notice notice-success"><p>Gemini funciona! Respuesta: ' . esc_html($body['candidates'][0]['content']['parts'][0]['text']) . '</p></div>';
                        } else {
                            $gemini_result = '<div class="notice notice-warning"><p>Respuesta inesperada.</p></div>';
                        }
                    }
                } else {
                    $gemini_result = '<div class="notice notice-warning"><p>Guarda una Gemini key primero.</p></div>';
                }
            }

            // Test OpenRouter
            if (isset($_POST['test_openrouter'])) {
                $or_key = trim(get_option(FARMA_OPENROUTER_KEY_OPTION, ''));
                $or_model = trim(get_option(FARMA_OPENROUTER_MODEL_OPTION, 'openai/gpt-4o-mini'));
                if (!$or_key) {
                    $or_result = '<div class="notice notice-warning"><p>Guarda una OpenRouter key primero.</p></div>';
                } else {
                    // 1) Validate the key and read the account balance (permanent while funded)
                    $info = wp_remote_get('https://openrouter.ai/api/v1/auth/key', [
                        'headers' => ['Authorization' => 'Bearer ' . $or_key],
                        'timeout' => 15,
                    ]);
                    if (is_wp_error($info)) {
                        $or_result = '<div class="notice notice-error"><p>Error de conexión: ' . esc_html($info->get_error_message()) . '</p></div>';
                    } else {
                        $status = (int) wp_remote_retrieve_response_code($info);
                        $info_body = json_decode(wp_remote_retrieve_body($info), true);
                        if ($status === 200 && isset($info_body['data'])) {
                            $d = $info_body['data'];
                            $label = isset($d['label']) ? $d['label'] : 'cuenta';
                            $balance = '';
                            if (isset($d['credits']) && is_array($d['credits']) && isset($d['credits']['limit'])) {
                                $used  = isset($d['credits']['used']) ? (float) $d['credits']['used'] : 0;
                                $limit = (float) $d['credits']['limit'];
                                $balance = sprintf(' — Saldo disponible: $%.2f (usado $%.2f de $%.2f)', max(0, $limit - $used), $used, $limit);
                            } elseif (isset($d['limit'])) {
                                $used  = isset($d['usage']) ? (float) $d['usage'] : 0;
                                $limit = (float) $d['limit'];
                                $balance = sprintf(' — Saldo disponible: $%.2f (usado $%.2f de $%.2f)', max(0, $limit - $used), $used, $limit);
                            }
                            if (isset($d['is_free_tier']) && $d['is_free_tier']) {
                                $balance .= ' (plan gratuito: solo modelos :free)';
                            }
                            $or_result = '<div class="notice notice-success"><p>OpenRouter key VÁLIDA — cuenta: ' . esc_html($label) . '.' . esc_html($balance) . '</p></div>';

                            // 2) Live test with the selected model
                            $test = wp_remote_post('https://openrouter.ai/api/v1/chat/completions', [
                                'headers' => [
                                    'Authorization' => 'Bearer ' . $or_key,
                                    'Content-Type'  => 'application/json',
                                ],
                                'body' => json_encode([
                                    'model'    => $or_model,
                                    'messages' => [['role' => 'user', 'content' => 'Hola']],
                                    'max_tokens' => 50,
                                ]),
                                'timeout' => 15,
                            ]);
                            if (is_wp_error($test)) {
                                $or_result .= '<div class="notice notice-error"><p>Error probando el modelo: ' . esc_html($test->get_error_message()) . '</p></div>';
                            } else {
                                $tbody = json_decode(wp_remote_retrieve_body($test), true);
                                if (isset($tbody['error'])) {
                                    $or_result .= '<div class="notice notice-error"><p>Error del modelo: ' . esc_html($tbody['error']['message']) . '</p></div>';
                                } elseif (isset($tbody['choices'][0]['message']['content'])) {
                                    $or_result .= '<div class="notice notice-success"><p>Modelo ' . esc_html($or_model) . ' responde: ' . esc_html($tbody['choices'][0]['message']['content']) . '</p></div>';
                                } else {
                                    $or_result .= '<div class="notice notice-warning"><p>Respuesta inesperada del modelo.</p></div>';
                                }
                            }
                        } else {
                            $msg = isset($info_body['error']['message']) ? $info_body['error']['message'] : 'error desconocido';
                            $or_result = '<div class="notice notice-error"><p>OpenRouter rechazó la key: HTTP ' . $status . ' — ' . esc_html($msg) . ' <strong>La key es inválida</strong> (no es un problema de saldo; saldo insuficiente da HTTP 402). Genera una key nueva en <a href="https://openrouter.ai/keys" target="_blank">openrouter.ai/keys</a> y pégala completa.</p></div>';
                        }
                    }
                }
            }
        }

    $gemini_key = trim(get_option(FARMA_CAPIBLUE_OPTION, ''));
        $or_key = get_option(FARMA_OPENROUTER_KEY_OPTION, '');
        $or_model = get_option(FARMA_OPENROUTER_MODEL_OPTION, 'openai/gpt-4o-mini');
        ?>
        <div class="wrap">
            <h1>CapiBlue — Configuración de IA</h1>
            <p>Configura al menos un proveedor de IA. El sistema prueba Gemini primero, luego OpenRouter, y si ambos fallan usa el modo local.</p>

            <hr>
            <h2>Opción 1: Google Gemini</h2>
            <p>Funciona mejor en servidores USA/Europa. Obtén tu key en <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a>.</p>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th><label for="gemini_key">API Key</label></th>
                        <td>
                            <input type="text" id="gemini_key" name="gemini_key" value="<?= esc_attr($gemini_key); ?>" style="width:450px;max-width:100%;font-family:monospace;">
                        </td>
                    </tr>
                </table>
                <button type="submit" class="button button-primary">Guardar Gemini</button>
                <button type="submit" name="test_gemini" value="1" class="button">Probar Gemini</button>
            </form>
            <?= $gemini_result; ?>

            <hr>
            <h2>Opción 2: OpenRouter (recomendado para todas las regiones)</h2>
            <p>Funciona desde cualquier país. Modelos gratis disponibles (sin tarjeta de crédito).</p>
            <p><strong>Regístrate:</strong> <a href="https://openrouter.ai/keys" target="_blank">openrouter.ai/keys</a> → usa GitHub o Google → crea una key</p>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th><label for="openrouter_key">API Key</label></th>
                        <td>
                            <input type="text" id="openrouter_key" name="openrouter_key" value="<?= esc_attr($or_key); ?>" style="width:450px;max-width:100%;font-family:monospace;">
                            <p class="description">Comienza con <code>sk-or-v1-</code></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="openrouter_model">Modelo</label></th>
                        <td>
                            <select id="openrouter_model" name="openrouter_model">
                                <option value="openai/gpt-4o-mini" <?= $or_model === 'openai/gpt-4o-mini' ? 'selected' : ''; ?>>GPT-4o-mini (rápido, ~$0.00003/msg)</option>
                                <option value="openai/gpt-4o" <?= $or_model === 'openai/gpt-4o' ? 'selected' : ''; ?>>GPT-4o (más capaz, ~$0.0025/msg)</option>
                                <option value="meta-llama/llama-3.3-70b-instruct:free" <?= $or_model === 'meta-llama/llama-3.3-70b-instruct:free' ? 'selected' : ''; ?>>Llama 3.3 70B (gratis, a veces saturado)</option>
                                <option value="google/gemma-4-26b-a4b-it:free" <?= $or_model === 'google/gemma-4-26b-a4b-it:free' ? 'selected' : ''; ?>>Gemma 4 26B (gratis, a veces saturado)</option>
                            </select>
                            <p class="description">GPT-4o-mini y GPT-4o requieren saldo en OpenRouter. El botón "Probar" valida la key y muestra el saldo de tu cuenta. Si sale <strong>"Error: User not found"</strong> (HTTP 401) la API key es inválida — es un problema de la key, no del saldo (saldo agotado da HTTP 402). Cámbiala por una nueva en <a href="https://openrouter.ai/keys" target="_blank">openrouter.ai/keys</a> (debe comenzar con <code>sk-or-v1-</code>) y pégala completa, sin espacios ni saltos de línea.</p>
                        </td>
                    </tr>
                </table>
                <button type="submit" class="button button-primary">Guardar OpenRouter</button>
                <button type="submit" name="test_openrouter" value="1" class="button">Probar OpenRouter</button>
            </form>
            <?= $or_result; ?>

            <hr>
            <h2>Opción 3: Groq (recomendado — gratis y confiable)</h2>
            <p>Completamente gratis, 30 requests/minuto, funciona desde cualquier país. Usa Llama 3, Mixtral, Gemma.</p>
            <p><strong>Regístrate:</strong> <a href="https://console.groq.com/keys" target="_blank">console.groq.com/keys</a> → crea key gratis</p>
            <?php
            $groq_key = get_option(FARMA_GROQ_KEY_OPTION, '');
            $groq_model = get_option(FARMA_GROQ_MODEL_OPTION, 'llama-3.3-70b-versatile');

            // Save BEFORE test so test uses the fresh config
            if (isset($_POST['groq_key'])) {
                update_option(FARMA_GROQ_KEY_OPTION, trim(sanitize_text_field($_POST['groq_key'])));
            }
            if (isset($_POST['groq_model'])) {
                update_option(FARMA_GROQ_MODEL_OPTION, sanitize_text_field($_POST['groq_model']));
                echo '<div class="updated"><p>Groq configuración guardada.</p></div>';
            }
            $groq_key = get_option(FARMA_GROQ_KEY_OPTION, '');
            $groq_model = get_option(FARMA_GROQ_MODEL_OPTION, 'llama-3.3-70b-versatile');

            $groq_result = '';
            if (isset($_POST['test_groq'])) {
                if ($groq_key) {
                    $test = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $groq_key,
                            'Content-Type'  => 'application/json',
                        ],
                        'body' => json_encode([
                            'model'    => $groq_model,
                            'messages' => [['role' => 'user', 'content' => 'Hola']],
                            'max_tokens' => 30,
                        ]),
                        'timeout' => 10,
                    ]);
                    if (is_wp_error($test)) {
                        $groq_result = '<div class="notice notice-error"><p>Error: ' . $test->get_error_message() . '</p></div>';
                    } else {
                        $body = json_decode(wp_remote_retrieve_body($test), true);
                        if (isset($body['error'])) {
                            $groq_result = '<div class="notice notice-error"><p>Error: ' . esc_html($body['error']['message']) . '</p></div>';
                        } elseif (isset($body['choices'][0]['message']['content'])) {
                            $groq_result = '<div class="notice notice-success"><p>Groq funciona! Respuesta: ' . esc_html($body['choices'][0]['message']['content']) . '</p></div>';
                        } else {
                            $groq_result = '<div class="notice notice-warning"><p>Respuesta inesperada.</p></div>';
                        }
                    }
                } else {
                    $groq_result = '<div class="notice notice-warning"><p>Guarda una Groq key primero.</p></div>';
                }
            }
            ?>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th><label for="groq_key">API Key</label></th>
                        <td>
                            <input type="text" id="groq_key" name="groq_key" value="<?= esc_attr($groq_key); ?>" style="width:450px;max-width:100%;font-family:monospace;">
                            <p class="description"><code>gsk_...</code></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="groq_model">Modelo</label></th>
                        <td>
                            <select id="groq_model" name="groq_model">
                                <option value="llama-3.3-70b-versatile" <?= $groq_model === 'llama-3.3-70b-versatile' ? 'selected' : ''; ?>>Llama 3.3 70B (inteligente, gratis)</option>
                                <option value="llama-3.1-8b-instant" <?= $groq_model === 'llama-3.1-8b-instant' ? 'selected' : ''; ?>>Llama 3.1 8B (rápido, gratis)</option>
                                <option value="mixtral-8x7b-32768" <?= $groq_model === 'mixtral-8x7b-32768' ? 'selected' : ''; ?>>Mixtral 8x7B (gratis)</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <button type="submit" class="button button-primary">Guardar Groq</button>
                <button type="submit" name="test_groq" value="1" class="button">Probar Groq</button>
            </form>
            <?= $groq_result; ?>

            <hr>
            <p style="color:#666;"><strong>Sin ningún proveedor de IA</strong>, CapiBlue funciona en modo básico: responde con información de Farmapaz (horarios, sucursales, pagos, delivery) y busca productos en el catálogo. Es útil pero no conversacional.</p>
        </div>
        <?php
    });
});

// ===== Tienda: ajustes de filtros =====
define('FARMA_SHOW_CATS_OPTION', 'farmapaz_show_cat_filters');
define('FARMA_SHOW_BRANDS_OPTION', 'farmapaz_show_brand_filters');
define('FARMA_LOW_STOCK_OPTION', 'farmapaz_low_stock_threshold');
define('FARMA_STORE_ADDRESS_OPTION', 'farmapaz_store_address');
define('FARMA_FREE_SHIPPING_MIN_OPTION', 'farmapaz_free_shipping_min');
define('FARMA_SHIPPING_FEE_OPTION', 'farmapaz_shipping_fee');
define('FARMA_PICKUP_ENABLED_OPTION', 'farmapaz_pickup_enabled');
define('FARMA_COLOR_GREEN_OPTION', 'farmapaz_color_green');
define('FARMA_COLOR_BLUE_OPTION', 'farmapaz_color_blue');
define('FARMA_COLOR_YELLOW_OPTION', 'farmapaz_color_yellow');
define('FARMA_COLOR_RED_OPTION', 'farmapaz_color_red');
define('FARMA_COLOR_ORANGE_OPTION', 'farmapaz_color_orange');
define('FARMA_PAGE_SUCURSALES_OPTION', 'farmapaz_page_sucursales_enabled');
define('FARMA_PAGE_ENCARTE_OPTION', 'farmapaz_page_encarte_enabled');
define('FARMA_PAGE_GUIA_OPTION', 'farmapaz_page_guia_enabled');
define('FARMA_SUCURSALES_OPTION', 'farmapaz_sucursales');
define('FARMA_CAPIBLUE_ENABLED_OPTION', 'farmapaz_capiblue_enabled');
define('FARMA_DELIVERY_OPEN_OPTION', 'farmapaz_delivery_open');
define('FARMA_DELIVERY_CLOSE_OPTION', 'farmapaz_delivery_close');

// Páginas controlables desde "Ajustes de Tienda": activar/desactivar sin tocar código.
// La clave es el slug de la página; se compara con is_page() en el front-end.
function farmapaz_paginas_controlables() {
    return [
        'sucursales'         => [FARMA_PAGE_SUCURSALES_OPTION, 'Sucursales Farmapaz'],
        'encarte-farmapaz-2' => [FARMA_PAGE_ENCARTE_OPTION, 'Encarte Farmapaz'],
        'guiamedica'         => [FARMA_PAGE_GUIA_OPTION, 'Guía Médica'],
    ];
}
function farmapaz_pagina_activa($slug) {
    $pages = farmapaz_paginas_controlables();
    if (!isset($pages[$slug])) return true;
    return get_option($pages[$slug][0], '1') !== '0';
}
// Si una página está desactivada desde el admin, se redirige a la portada
add_action('template_redirect', function () {
    if (is_admin() || is_customize_preview()) return;
    foreach (farmapaz_paginas_controlables() as $slug => $cfg) {
        if (is_page($slug) && get_option($cfg[0], '1') === '0') {
            wp_redirect(home_url('/'), 302);
            exit;
        }
    }
}, 0);

// Ocultar del menú los enlaces a páginas desactivadas desde "Ajustes de Tienda"
add_filter('wp_get_nav_menu_items', function ($items, $menu, $args) {
    if (is_admin()) return $items;
    static $disabled_ids = null;
    if ($disabled_ids === null) {
        $disabled_ids = [];
        foreach (farmapaz_paginas_controlables() as $slug => $cfg) {
            if (get_option($cfg[0], '1') === '0') {
                $page = get_page_by_path($slug);
                if ($page) $disabled_ids[(int) $page->ID] = get_permalink($page->ID);
            }
        }
    }
    if (!$disabled_ids) return $items;
    return array_values(array_filter($items, function ($item) use ($disabled_ids) {
        if ($item->object === 'page' && isset($disabled_ids[(int) $item->object_id])) return false;
        if ($item->object === 'custom' && $item->url) {
            $item_url = untrailingslashit($item->url);
            foreach ($disabled_ids as $url) {
                if ($item_url === untrailingslashit($url)) return false;
            }
        }
        return true;
    }));
}, 10, 3);

// ---- Sucursales (gestor desde Ajustes de Tienda > Sucursales) ----
function farmapaz_sucursales_defaults() {
    return [
        ['nombre' => 'La Paz',         'direccion' => 'Av. La Paz Qta. Nro 50-1, Sector las Avenidas.', 'telefono' => '0412-8798885', 'horario' => '24 horas',    'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'Juncal',         'direccion' => 'Av. Juncal con calle Piar, Local 3, Sector Centro.', 'telefono' => '0412-8798885', 'horario' => '24 horas',    'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'Luis del Valle', 'direccion' => 'Av. Luis del Valle Garcia, Edif. Doña Maria, P/B Local 1 y 2, Sector las Avenidas.', 'telefono' => '0412-8798885', 'horario' => '24 horas', 'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'Hospital',       'direccion' => 'Av. Bicentenario, Edif. Torre Fema, P/B Local 01.', 'telefono' => '0412-8798885', 'horario' => '24 horas', 'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'Guaritos',       'direccion' => 'Av. Principal los Guaritos, Local Nro 12.', 'telefono' => '0412-8798885', 'horario' => '24 horas', 'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'Av. El Ejercito','direccion' => 'Av. El Ejercito, Edif. San Jose, P/B Local 2, Urb. Alto de los Godos.', 'telefono' => '0412-8798885', 'horario' => '24 horas', 'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'Tipuro',         'direccion' => 'Av. Los Proceres, P/B Local S/N y Local 4, Sector Tipuro, Parroquia Boqueron.', 'telefono' => '0412-8798885', 'horario' => '24 horas', 'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'La Cruz',        'direccion' => 'Av. Principal de la Cruz, Local 1 y 2, frente a la plaza.', 'telefono' => '0412-8798885', 'horario' => '24 horas', 'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'Sabana Grande',  'direccion' => 'Calle Principal, Local Nro A-1, Sector 3 de Sabana Grande.', 'telefono' => '0412-8798885', 'horario' => '24 horas', 'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'Santa Ines',     'direccion' => 'Av. Raul Leoni, Local 7-3. Frente al Comando de GN. Sector Santa Ines.', 'telefono' => '0412-8798885', 'horario' => '24 horas', 'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'La Puente',      'direccion' => 'Calle Principal, Casa Nro S/N. Sector La Puente.', 'telefono' => '0422-0130448', 'horario' => '24 horas', 'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'Azcue',          'direccion' => 'Calle Azcue con Carrera 9, Edif. Morve, P/B Local B, Sector Centro.', 'telefono' => '0422-0130448', 'horario' => '24 horas', 'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'City',           'direccion' => 'Av. Bolivar, Edif. Radio Maturin, P/B Local Nro 4, Sector Centro. (Antiguo City Lamp).', 'telefono' => '0422-0130448', 'horario' => '24 horas', 'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'Mercado',        'direccion' => 'Calle 7, Local Nro 26, Sector Mercado Viejo.', 'telefono' => '0422-0130448', 'horario' => '7:30 AM — 5:30 PM', 'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'Las Cocuizas',   'direccion' => 'CR Nro 7, Local Nro 133, Urb. Las Cocuizas.', 'telefono' => '0422-0130448', 'horario' => '24 horas', 'informacion' => '', 'imagen' => '', 'activa' => '1'],
        ['nombre' => 'Paramaconi',     'direccion' => 'Calle Principal, Local S/N, Sector Paramaconi.', 'telefono' => '0422-0129206', 'horario' => '24 horas', 'informacion' => '', 'imagen' => '', 'activa' => '1'],
    ];
}
// Devuelve las sucursales guardadas en el admin; si nunca se han guardado, usa el seed original.
function farmapaz_sucursales() {
    $s = get_option(FARMA_SUCURSALES_OPTION, null);
    if (!is_array($s) || empty($s)) {
        return farmapaz_sucursales_defaults();
    }
    return array_values($s);
}
// Solo las sucursales marcadas como activas en el gestor (para la página pública).
function farmapaz_sucursales_activas() {
    $out = [];
    foreach (farmapaz_sucursales() as $s) {
        $s = (array) $s;
        if (isset($s['activa']) && $s['activa'] === '0') continue;
        if (empty($s['nombre'])) continue;
        $out[] = $s;
    }
    return $out;
}
// Si la opción aún no existe, la siembra con las sucursales actuales (primera visita al gestor).
function farmapaz_sucursales_seed() {
    if (get_option(FARMA_SUCURSALES_OPTION, null) === null) {
        update_option(FARMA_SUCURSALES_OPTION, farmapaz_sucursales_defaults(), false);
    }
}

// Brand palette — configurable from "Ajustes de Tienda". Defaults match the original design.
function farmapaz_brand_colors() {
    return [
        'green'  => get_option(FARMA_COLOR_GREEN_OPTION, '#5A7D43'),
        'blue'   => get_option(FARMA_COLOR_BLUE_OPTION, '#09146E'),
        'yellow' => get_option(FARMA_COLOR_YELLOW_OPTION, '#FEAB0D'),
        'red'    => get_option(FARMA_COLOR_RED_OPTION, '#FF1A27'),
        'orange' => get_option(FARMA_COLOR_ORANGE_OPTION, '#F97316'),
    ];
}
function farmapaz_brand_color($key) {
    $colors = farmapaz_brand_colors();
    return isset($colors[$key]) ? $colors[$key] : '';
}

// Convert a hex color to the "R G B" channel triplet used by Tailwind opacity modifiers
function farmapaz_hex_to_rgb_string($hex) {
    $hex = ltrim((string) $hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
        return '0 0 0';
    }
    return hexdec(substr($hex, 0, 2)) . ' ' . hexdec(substr($hex, 2, 2)) . ' ' . hexdec(substr($hex, 4, 2));
}

// Expose the configurable palette as CSS custom properties (loaded after all stylesheets)
add_action('wp_head', function () {
    $c = farmapaz_brand_colors();
    $out = ':root{';
    foreach (['green', 'blue', 'yellow', 'red', 'orange'] as $k) {
        $out .= '--brand-' . $k . ':' . $c[$k] . ';'
              . '--brand-' . $k . '-rgb:' . farmapaz_hex_to_rgb_string($c[$k]) . ';';
    }
    echo '<style>' . $out . '}</style>' . "\n";
}, 999);

// Delivery fee helpers: free above the minimum subtotal, otherwise a flat fee
function farmapaz_free_shipping_min() {
    return max(0, (float) get_option(FARMA_FREE_SHIPPING_MIN_OPTION, 20));
}
function farmapaz_shipping_fee_amount() {
    return max(0, (float) get_option(FARMA_SHIPPING_FEE_OPTION, 2));
}

// CapiBlue AI chat can be fully disabled from "Ajustes de Tienda"; when off, the
// widget stays but only answers with predetermined replies (no AI, no catalog search).
function farmapaz_capiblue_enabled() {
    return get_option(FARMA_CAPIBLUE_ENABLED_OPTION, '1') !== '0';
}

// Delivery hours (from "Ajustes de Tienda"). Outside this window the checkout warns
// that the order will be processed the next day.
function farmapaz_delivery_open() {
    return get_option(FARMA_DELIVERY_OPEN_OPTION, '08:00');
}
function farmapaz_delivery_close() {
    return get_option(FARMA_DELIVERY_CLOSE_OPTION, '22:00');
}
// Normalize an "H:MM" / "HH:MM" string to "HH:MM", or '' if invalid (hours 00–23).
function farmapaz_normalize_time($value) {
    if (preg_match('/^((?:[01]?\d|2[0-3])):([0-5]\d)$/', trim((string) $value), $m)) {
        return str_pad($m[1], 2, '0', STR_PAD_LEFT) . ':' . $m[2];
    }
    return '';
}
// Delivery currently open? Supports overnight windows (open > close, e.g. 22:00–08:00).
function farmapaz_delivery_is_open() {
    $open = farmapaz_normalize_time(farmapaz_delivery_open());
    $close = farmapaz_normalize_time(farmapaz_delivery_close());
    if ($open === '' || $close === '') return true; // invalid config → assume open
    $now = current_time('H:i');
    if ($open <= $close) {
        return $now >= $open && $now <= $close;
    }
    return $now >= $open || $now <= $close;
}
// "08:00" → "8:00 a.m." / "22:00" → "10:00 p.m."
function farmapaz_format_delivery_hour($value) {
    $t = farmapaz_normalize_time($value);
    if ($t === '') return '';
    [$h, $m] = array_map('intval', explode(':', $t));
    $suffix = $h >= 12 ? 'p.m.' : 'a.m.';
    $h12 = $h % 12;
    if ($h12 === 0) $h12 = 12;
    return sprintf('%d:%02d %s', $h12, $m, $suffix);
}
// Human-friendly window, e.g. "8:00 a.m. – 10:00 p.m."
function farmapaz_delivery_schedule() {
    return farmapaz_format_delivery_hour(farmapaz_delivery_open()) . ' – ' . farmapaz_format_delivery_hour(farmapaz_delivery_close());
}

// Delivery method: 'delivery' (home) or 'pickup' (retiro en tienda). Stored in WC session.
function farmapaz_pickup_enabled() {
    return get_option(FARMA_PICKUP_ENABLED_OPTION, '1') !== '0';
}
function farmapaz_delivery_method() {
    if (!farmapaz_pickup_enabled()) return 'delivery';
    $method = WC()->session ? WC()->session->get('farmapaz_delivery_method') : '';
    return in_array($method, ['delivery', 'pickup'], true) ? $method : 'delivery';
}
function farmapaz_store_address() {
    return get_option(FARMA_STORE_ADDRESS_OPTION, 'Av. Francisco de Miranda, Centro Comercial San Ignacio, Local PB-11, Caracas');
}
// Capture the chosen method when checkout totals are recalculated via AJAX
add_action('woocommerce_checkout_update_order_review', function () {
    $method = isset($_POST['farmapaz_delivery_method']) ? sanitize_key($_POST['farmapaz_delivery_method']) : '';
    if (!farmapaz_pickup_enabled()) $method = 'delivery';
    if (in_array($method, ['delivery', 'pickup'], true) && WC()->session) {
        WC()->session->set('farmapaz_delivery_method', $method);
    }
});
// Retiro en tienda se gestiona SOLO por WhatsApp: si se intenta completar el
// checkout web con ese método, se bloquea y se guía al cliente a WhatsApp.
add_action('woocommerce_checkout_process', function () {
    $method = isset($_POST['farmapaz_delivery_method']) ? sanitize_key($_POST['farmapaz_delivery_method']) : '';
    if ($method === 'pickup') {
        wc_add_notice('El retiro en tienda se cuadra y finiquita por WhatsApp. Usa el botón "Retirar en tienda por WhatsApp" para indicar la sucursal donde lo buscarás y los detalles del retiro.', 'error');
    }
});
// Our own AJAX endpoint: update delivery method + re-render review totals (works for guests)
add_action('wp_ajax_farmapaz_set_delivery_method', 'farmapaz_ajax_set_delivery_method');
add_action('wp_ajax_nopriv_farmapaz_set_delivery_method', 'farmapaz_ajax_set_delivery_method');
function farmapaz_ajax_set_delivery_method() {
    check_ajax_referer('farmapaz_nonce', 'nonce');
    $method = isset($_POST['method']) ? sanitize_key($_POST['method']) : '';
    if (!farmapaz_pickup_enabled()) $method = 'delivery';
    if (in_array($method, ['delivery', 'pickup'], true) && WC()->session) {
        WC()->session->set('farmapaz_delivery_method', $method);
        WC()->cart->calculate_totals();
    }
    ob_start();
    if (function_exists('farmapaz_render_checkout_review_totals')) {
        farmapaz_render_checkout_review_totals();
    }
    $totals = ob_get_clean();
    wp_send_json_success(['review_totals' => $totals]);
}
function farmapaz_render_checkout_review_totals() {
    ?>
    <div class="checkout-review-totals">
        <div class="checkout-review-total-row">
            <span>Subtotal</span>
            <span><?php wc_cart_totals_subtotal_html(); ?></span>
        </div>
        <?php foreach (WC()->cart->get_coupons() as $code => $coupon): ?>
        <div class="checkout-review-total-row checkout-review-coupon">
            <span><?php wc_cart_totals_coupon_label($coupon); ?></span>
            <span><?php wc_cart_totals_coupon_html($coupon); ?></span>
        </div>
        <?php endforeach; ?>
        <?php foreach (WC()->cart->get_fees() as $fee):
            $is_shipping = stripos($fee->name, 'envío') !== false;
            $is_free = stripos($fee->name, 'gratis') !== false;
            $is_pickup = stripos($fee->name, 'retiro') !== false;
        ?>
        <div class="checkout-review-total-row checkout-review-fee <?= $is_shipping || $is_pickup ? 'checkout-review-shipping' : ''; ?>">
            <span class="checkout-shipping-label">
                <?php if ($is_shipping || $is_pickup): ?>
                    <?php if ($is_pickup): ?>
                    <svg class="checkout-shipping-icon checkout-shipping-free-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14-13h2a2 2 0 012 2v9a2 2 0 01-2 2h-2m-14-9H3a2 2 0 00-2 2v9a2 2 0 002 2h2M9 7h2m-2 4h2m-2 4h2m4-4h2m-2 4h2"/></svg>
                    <?php elseif ($is_free): ?>
                    <svg class="checkout-shipping-icon checkout-shipping-free-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <?php else: ?>
                    <svg class="checkout-shipping-icon checkout-shipping-paid-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2-1m4-1l-2 1m4-2l-2 1m6-5v5a1 1 0 001 1h3l2-4v-3a1 1 0 00-1-1h-4a1 1 0 00-1 1z"/></svg>
                    <?php endif; ?>
                    <span class="checkout-shipping-text"><?= esc_html($fee->name); ?></span>
                <?php else: ?>
                    <span><?= esc_html($fee->name); ?></span>
                <?php endif; ?>
            </span>
            <span class="checkout-shipping-amount"><?php if ($is_pickup || $is_free): ?>Gratis<?php else: ?><?php wc_cart_totals_fee_html($fee); ?><?php endif; ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax() && WC()->cart->get_cart_tax() > 0): ?>
        <div class="checkout-review-total-row">
            <span>IVA</span>
            <span><?php wc_cart_totals_taxes_total_html(); ?></span>
        </div>
        <?php endif; ?>
        <div class="checkout-review-total-row checkout-review-grand-total">
            <span>Total</span>
            <span><?php wc_cart_totals_order_total_html(); ?></span>
        </div>
    </div>
    <?php
}
// Save the method on the order (HPOS-compatible: wc_orders_meta table)
add_action('woocommerce_checkout_create_order', function ($order) {
    $post_method = isset($_POST['farmapaz_delivery_method']) ? sanitize_key($_POST['farmapaz_delivery_method']) : '';
    if (!farmapaz_pickup_enabled()) $post_method = 'delivery';
    if (in_array($post_method, ['delivery', 'pickup'], true) && WC()->session) {
        WC()->session->set('farmapaz_delivery_method', $post_method);
    }
    $order->update_meta_data('_farmapaz_delivery_method', farmapaz_delivery_method());
});
// Pickup orders don't need a shipping address
add_filter('woocommerce_cart_needs_shipping_address', function ($needs) {
    return $needs && farmapaz_delivery_method() !== 'pickup';
});
// In pickup mode, shipping fields are not required
add_filter('woocommerce_checkout_fields', function ($fields) {
    if (farmapaz_delivery_method() === 'pickup' && !empty($fields['shipping'])) {
        foreach ($fields['shipping'] as $key => $field) {
            $fields['shipping'][$key]['required'] = false;
        }
    }
    return $fields;
});
// Show the delivery method on the admin order page
add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
    $method = $order->get_meta('_farmapaz_delivery_method');
    if (!$method) return;
    $label = $method === 'pickup' ? 'Retiro en tienda' : 'Envío a domicilio';
    echo '<p style="margin-top: 8px;"><strong>' . esc_html($label) . '</strong></p>';
    if ($method === 'pickup') {
        echo '<p style="color:#5A7D43;">' . esc_html(farmapaz_store_address()) . '</p>';
    }
});

// Store front filter visibility helpers
function farmapaz_cat_filters_enabled() {
    return get_option(FARMA_SHOW_CATS_OPTION, '1') !== '0';
}
function farmapaz_brand_filters_enabled() {
    return get_option(FARMA_SHOW_BRANDS_OPTION, '1') !== '0';
}
// Live product count for marketing copy (cached 1h). Rounded down to a "nice" number.
function farmapaz_product_count() {
    $cached = get_transient('farmapaz_product_count');
    if (is_numeric($cached) && $cached > 0) return (int) $cached;
    $total = wp_count_posts('product');
    $count = isset($total->publish) ? (int) $total->publish : 0;
    if ($count >= 10000) $count = floor($count / 1000) * 1000;
    set_transient('farmapaz_product_count', $count, HOUR_IN_SECONDS);
    return $count;
}
// Low stock threshold — products with managed stock at or below this value are handled by call center
function farmapaz_low_stock_threshold() {
    return max(0, (int) get_option(FARMA_LOW_STOCK_OPTION, 5));
}
function farmapaz_is_low_stock($product) {
    if (!$product || !$product->managing_stock()) return false;
    $qty = $product->get_stock_quantity();
    if ($qty === null) return false;
    return $qty <= farmapaz_low_stock_threshold();
}

add_action('admin_menu', function () {
    add_menu_page('Ajustes de Tienda', 'Ajustes de Tienda', 'manage_options', 'farmapaz-shop', 'farmapaz_shop_settings_page', 'dashicons-store', 56);
    add_submenu_page('farmapaz-shop', 'Sucursales', 'Sucursales', 'manage_options', 'farmapaz-sucursales', 'farmapaz_sucursales_page', 5);
});
function farmapaz_shop_settings_page() {
        if (!current_user_can('manage_options')) wp_die('Acceso denegado');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['farmapaz_shop_settings'])) {
            if (isset($_POST['farmapaz_shop_settings_nonce']) && wp_verify_nonce($_POST['farmapaz_shop_settings_nonce'], 'farmapaz_shop_settings')) {
                update_option(FARMA_SHOW_CATS_OPTION, isset($_POST['show_cat_filters']) ? '1' : '0');
                update_option(FARMA_SHOW_BRANDS_OPTION, isset($_POST['show_brand_filters']) ? '1' : '0');
                update_option(FARMA_LOW_STOCK_OPTION, max(0, (int) $_POST['low_stock_threshold']));
                update_option(FARMA_STORE_ADDRESS_OPTION, sanitize_text_field($_POST['store_address'] ?? ''));
                update_option(FARMA_FREE_SHIPPING_MIN_OPTION, max(0, (float) ($_POST['free_shipping_min'] ?? 20)));
                update_option(FARMA_SHIPPING_FEE_OPTION, max(0, (float) ($_POST['shipping_fee'] ?? 2)));
                update_option(FARMA_PICKUP_ENABLED_OPTION, isset($_POST['enable_pickup']) ? '1' : '0');
                update_option(FARMA_CAPIBLUE_ENABLED_OPTION, isset($_POST['enable_capiblue']) ? '1' : '0');
                update_option(FARMA_DELIVERY_OPEN_OPTION, farmapaz_normalize_time($_POST['delivery_open'] ?? '') ?: '08:00');
                update_option(FARMA_DELIVERY_CLOSE_OPTION, farmapaz_normalize_time($_POST['delivery_close'] ?? '') ?: '22:00');
                foreach (farmapaz_paginas_controlables() as $slug => $cfg) {
                    update_option($cfg[0], isset($_POST['page_enabled_' . $slug]) ? '1' : '0');
                }
                foreach (['green', 'blue', 'yellow', 'red', 'orange'] as $brand_key) {
                    $posted_color = isset($_POST['farmapaz_color_' . $brand_key]) ? sanitize_hex_color($_POST['farmapaz_color_' . $brand_key]) : '';
                    if ($posted_color) {
                        update_option(constant('FARMA_COLOR_' . strtoupper($brand_key) . '_OPTION'), $posted_color);
                    }
                }
                $home_carousel_rows = [];
                $hc_titles = isset($_POST['home_carousel_title']) ? (array) $_POST['home_carousel_title'] : [];
                $hc_subs = isset($_POST['home_carousel_subtitle']) ? (array) $_POST['home_carousel_subtitle'] : [];
                $hc_cats = isset($_POST['home_carousel_category']) ? (array) $_POST['home_carousel_category'] : [];
                $hc_limits = isset($_POST['home_carousel_limit']) ? (array) $_POST['home_carousel_limit'] : [];
                $hc_orderbys = isset($_POST['home_carousel_orderby']) ? (array) $_POST['home_carousel_orderby'] : [];
                $hc_layouts = isset($_POST['home_carousel_layout']) ? (array) $_POST['home_carousel_layout'] : [];
                $hc_positions = isset($_POST['home_carousel_position']) ? (array) $_POST['home_carousel_position'] : [];
                foreach ($hc_cats as $i => $cat) {
                    $cat = sanitize_text_field($cat);
                    if (empty($cat)) continue;
                    $home_carousel_rows[] = [
                        'position' => isset($hc_positions[$i]) ? max(1, min(50, (int) $hc_positions[$i])) : ($i + 1),
                        'title'    => isset($hc_titles[$i]) ? sanitize_text_field($hc_titles[$i]) : '',
                        'subtitle' => isset($hc_subs[$i]) ? sanitize_text_field($hc_subs[$i]) : '',
                        'category' => $cat,
                        'limit'    => isset($hc_limits[$i]) ? max(1, min(60, (int) $hc_limits[$i])) : 12,
                        'orderby'  => isset($hc_orderbys[$i]) && in_array($hc_orderbys[$i], ['rand', 'date', 'title', 'menu_order', 'popularity'], true) ? $hc_orderbys[$i] : 'date',
                        'layout'   => isset($hc_layouts[$i]) && in_array($hc_layouts[$i], ['carousel', 'grid'], true) ? $hc_layouts[$i] : 'carousel',
                    ];
                }
                usort($home_carousel_rows, function($a, $b) {
                    return $a['position'] <=> $b['position'];
                });
                update_option(FARMA_HOME_CAROUSELS_OPTION, $home_carousel_rows);
                echo '<div class="updated"><p>Ajustes de tienda guardados.</p></div>';
            }
        }

        $show_cats = farmapaz_cat_filters_enabled();
        $show_brands = farmapaz_brand_filters_enabled();
        $low_stock = farmapaz_low_stock_threshold();
        $store_address = farmapaz_store_address();
        $free_shipping_min = farmapaz_free_shipping_min();
        $shipping_fee = farmapaz_shipping_fee_amount();
        $pickup_enabled = farmapaz_pickup_enabled();
        $capiblue_enabled = farmapaz_capiblue_enabled();
        $delivery_open = farmapaz_delivery_open();
        $delivery_close = farmapaz_delivery_close();
        $brand_colors = farmapaz_brand_colors();
        $brand_color_labels = [
            'green'  => 'Verde (success, envío gratis, precios)',
            'blue'   => 'Azul (marca principal, botones, títulos)',
            'yellow' => 'Amarillo (acentos, ofertas)',
            'red'    => 'Rojo (precios de oferta, descuentos)',
            'orange' => 'Naranja (stock bajo / call center)',
        ];
        $all_cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0, 'orderby' => 'count', 'order' => 'DESC']);
        $home_carousel_rows = farmapaz_home_carousels();
        $carousel_orderbys = ['rand' => 'Aleatorio', 'date' => 'Más recientes', 'title' => 'Alfabético', 'menu_order' => 'Orden del menú', 'popularity' => 'Popularidad'];
        ?>
        <div class="wrap">
            <h1>Ajustes de Tienda</h1>
            <p>Controla qué filtros aparecen en la tienda (sidebar). Si la data de una marca o categoría está incompleta, puedes ocultar su filtro sin tocar código.</p>

            <form method="post">
                <?php wp_nonce_field('farmapaz_shop_settings', 'farmapaz_shop_settings_nonce'); ?>
                <input type="hidden" name="farmapaz_shop_settings" value="1">
                <table class="form-table">
                    <tr>
                        <th scope="row">Filtro de Categorías</th>
                        <td>
                            <label>
                                <input type="checkbox" name="show_cat_filters" value="1" <?= $show_cats ? 'checked' : ''; ?>>
                                Mostrar filtro de categorías en la tienda
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Filtro de Marcas</th>
                        <td>
                            <label>
                                <input type="checkbox" name="show_brand_filters" value="1" <?= $show_brands ? 'checked' : ''; ?>>
                                Mostrar filtro de marcas en la tienda
                            </label>
                            <p class="description">Si una marca tiene pocos productos o su data está incompleta, puedes ocultar el filtro completo desde aquí.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Umbral de stock bajo</th>
                        <td>
                            <input type="number" name="low_stock_threshold" value="<?= (int) $low_stock; ?>" min="0" max="1000" style="width:120px;">
                            <p class="description">Productos con stock gestionado en este valor o menor se marcan como <strong>"Disponibilidad por call center"</strong> en las tarjetas y se ocultan de los carruseles. Por defecto: 5.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Dirección de la tienda</th>
                        <td>
                            <input type="text" name="store_address" value="<?= esc_attr($store_address); ?>" class="regular-text" style="max-width: 460px;">
                            <p class="description">Se muestra en el checkout cuando el cliente elige <strong>"Retiro en tienda"</strong> (opción sin costo de envío).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Retiro en tienda</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_pickup" value="1" <?= $pickup_enabled ? 'checked' : ''; ?>>
                                Permitir "Retiro en tienda" en el checkout
                            </label>
                            <p class="description">Desmarca para ocultar la opción de retiro en tienda en el checkout y forzar envío a domicilio.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Chat CapiBlue</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_capiblue" value="1" <?= $capiblue_enabled ? 'checked' : ''; ?>>
                                Activar el chat de CapiBlue (asistente inteligente)
                            </label>
                            <p class="description">Si lo desactivas, el chat sigue visible en el sitio pero solo responde mensajes predeterminados: saludos, preguntas frecuentes, horarios y contacto. Sin inteligencia artificial ni búsqueda en el catálogo.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Horario de delivery</th>
                        <td>
                            <input type="time" name="delivery_open" value="<?= esc_attr($delivery_open); ?>" step="60">
                            <span style="margin: 0 6px;">a</span>
                            <input type="time" name="delivery_close" value="<?= esc_attr($delivery_close); ?>" step="60">
                            <p class="description">Fuera de este horario, el checkout avisa que el pedido se procesará al día siguiente. Por defecto: 08:00 a 22:00. También se aceptan horarios nocturnos (ej. 22:00 a 08:00).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Páginas del sitio</th>
                        <td>
                            <?php foreach (farmapaz_paginas_controlables() as $slug => $cfg): $page_active = get_option($cfg[0], '1') !== '0'; ?>
                            <label style="display:block; margin-bottom:6px;">
                                <input type="checkbox" name="page_enabled_<?= esc_attr($slug); ?>" value="1" <?= $page_active ? 'checked' : ''; ?>>
                                Activar página <strong><?= esc_html($cfg[1]); ?></strong>
                                <span style="color:#888;">(<?= esc_html($slug); ?>)</span>
                            </label>
                            <?php endforeach; ?>
                            <p class="description">Desmarca para ocultar la página (redirige a la portada). No se borra nada: puedes reactivarla cuando quieras. Las sucursales se gestionan en <strong>Ajustes de Tienda &gt; Sucursales</strong>.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Mínimo para envío gratis</th>
                        <td>
                            <input type="number" name="free_shipping_min" value="<?= esc_attr($free_shipping_min); ?>" min="0" step="0.01" style="width:120px;">
                            <p class="description">Subtotal a partir del cual el envío es gratis. Por defecto: 20. Usa 0 para nunca dar envío gratis.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Costo del envío</th>
                        <td>
                            <input type="number" name="shipping_fee" value="<?= esc_attr($shipping_fee); ?>" min="0" step="0.01" style="width:120px;">
                            <p class="description">Tarifa de envío para compras por debajo del mínimo. Por defecto: 2.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Colores de marca</th>
                        <td>
                            <p class="description">Los 5 colores globales del tema. Se aplican en todo el sitio (botones, precios, badges, header, footer). Guarda y recarga para ver los cambios.</p>
                            <table class="form-table" style="margin-top: 8px; max-width: 620px;">
                                <?php foreach ($brand_color_labels as $brand_key => $brand_label): ?>
                                <tr>
                                    <th style="width: 60px;"><?= esc_html(ucfirst($brand_key)); ?></th>
                                    <td>
                                        <input type="color" name="farmapaz_color_<?= $brand_key; ?>" value="<?= esc_attr($brand_colors[$brand_key]); ?>" style="vertical-align: middle; width: 48px; height: 30px; cursor: pointer; border: none; padding: 0; background: transparent;">
                                        <span style="margin-left: 8px; color: #444; vertical-align: middle;"><?= esc_html($brand_label); ?></span>
                                        <span style="margin-left: 6px; font-family: monospace; color: #777; vertical-align: middle;"><?= esc_html($brand_colors[$brand_key]); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Carruseles de la portada</th>
                        <td>
                            <p class="description">Configura los carruseles de la página de inicio. Puedes cambiar el título, subtítulo, la categoría de productos, cuántos se muestran, el orden de los productos y la posición de cada carrusel en la página (1 = primero). Deja filas vacías para añadir más.</p>
                            <?php foreach ($home_carousel_rows as $hc_i => $hc_row): ?>
                            <?php $hc_row = wp_parse_args((array) $hc_row, ['position' => $hc_i + 1, 'title' => '', 'subtitle' => '', 'category' => '', 'limit' => 12, 'orderby' => 'date', 'layout' => 'carousel']); ?>
                            <fieldset style="border: 1px solid #ccd0d4; border-radius: 6px; padding: 10px 12px; margin-bottom: 10px; background: #fff; max-width: 720px;">
                                <table class="form-table" style="margin: 0;">
                                    <tr>
                                        <th style="width: 130px;">Posición</th>
                                        <td><input type="number" name="home_carousel_position[]" value="<?= (int) $hc_row['position']; ?>" min="1" max="50" style="width: 70px;"></td>
                                    </tr>
                                    <tr>
                                        <th>Título</th>
                                        <td><input type="text" name="home_carousel_title[]" value="<?= esc_attr($hc_row['title']); ?>" class="regular-text" placeholder="Ej: Ahorra con Farmapaz"></td>
                                    </tr>
                                    <tr>
                                        <th>Subtítulo</th>
                                        <td><input type="text" name="home_carousel_subtitle[]" value="<?= esc_attr($hc_row['subtitle']); ?>" class="regular-text" placeholder="Ej: Ofertas exclusivas"></td>
                                    </tr>
                                    <tr>
                                        <th>Categoría</th>
                                        <td>
                                            <select name="home_carousel_category[]">
                                                <option value="">— Seleccionar —</option>
                                                <?php if (!empty($all_cats) && !is_wp_error($all_cats)): foreach ($all_cats as $cat): ?>
                                                <option value="<?= esc_attr($cat->slug); ?>" <?= selected($hc_row['category'], $cat->slug, false); ?>><?= esc_html($cat->name); ?> (<?= (int) $cat->count; ?>)</option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Productos</th>
                                        <td>
                                            <input type="number" name="home_carousel_limit[]" value="<?= (int) $hc_row['limit']; ?>" min="1" max="60" style="width: 70px;">
                                            <span style="margin-left: 12px;">Orden: </span>
                                            <select name="home_carousel_orderby[]">
                                                <?php foreach ($carousel_orderbys as $key => $label): ?>
                                                <option value="<?= $key; ?>" <?= selected($hc_row['orderby'], $key, false); ?>><?= $label; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Formato</th>
                                        <td>
                                            <select name="home_carousel_layout[]">
                                                <option value="carousel" <?= selected($hc_row['layout'], 'carousel', false); ?>>Carrusel (horizontal)</option>
                                                <option value="grid" <?= selected($hc_row['layout'], 'grid', false); ?>>Cuadrícula (grid)</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                            <?php endforeach; ?>
                            <fieldset style="border: 1px dashed #ccd0d4; border-radius: 6px; padding: 10px 12px; margin-bottom: 10px; background: #f9f9f9; max-width: 720px;">
                                <p class="description" style="margin: 0;"><strong>Añadir nuevo carrusel:</strong> llena una fila vacía con categoría y guarda.</p>
                                <table class="form-table" style="margin: 0;">
                                    <tr>
                                        <th style="width: 130px;">Posición</th>
                                        <td><input type="number" name="home_carousel_position[]" value="<?= count($home_carousel_rows) + 1; ?>" min="1" max="50" style="width: 70px;"></td>
                                    </tr>
                                    <tr>
                                        <th>Título</th>
                                        <td><input type="text" name="home_carousel_title[]" value="" class="regular-text" placeholder="Ej: Bebés y Maternidad"></td>
                                    </tr>
                                    <tr>
                                        <th>Subtítulo</th>
                                        <td><input type="text" name="home_carousel_subtitle[]" value="" class="regular-text" placeholder="Ej: Todo para tu bebé"></td>
                                    </tr>
                                    <tr>
                                        <th>Categoría</th>
                                        <td>
                                            <select name="home_carousel_category[]">
                                                <option value="">— Seleccionar —</option>
                                                <?php if (!empty($all_cats) && !is_wp_error($all_cats)): foreach ($all_cats as $cat): ?>
                                                <option value="<?= esc_attr($cat->slug); ?>"><?= esc_html($cat->name); ?> (<?= (int) $cat->count; ?>)</option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Productos</th>
                                        <td>
                                            <input type="number" name="home_carousel_limit[]" value="12" min="1" max="60" style="width: 70px;">
                                            <span style="margin-left: 12px;">Orden: </span>
                                            <select name="home_carousel_orderby[]">
                                                <?php foreach ($carousel_orderbys as $key => $label): ?>
                                                <option value="<?= $key; ?>"><?= $label; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Formato</th>
                                        <td>
                                            <select name="home_carousel_layout[]">
                                                <option value="carousel">Carrusel (horizontal)</option>
                                                <option value="grid">Cuadrícula (grid)</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Guardar ajustes'); ?>
            </form>
        </div>
        <?php
}

// Resuelve el attachment desde la URL de una imagen. Si la URL no coincide con el
// baseurl de uploads (p.ej. en local con dominio de producción), busca por _wp_attached_file.
function farmapaz_attachment_id_from_url($url) {
    $id = attachment_url_to_postid($url);
    if ($id) return (int) $id;
    $path = ltrim(parse_url($url, PHP_URL_PATH), '/');
    if ($path === '') return 0;
    $candidates = [];
    $basename = basename($path);
    if ($basename !== '') $candidates[] = $basename;
    $rel = preg_replace('#^(wp-content/uploads/)#', '', $path);
    if ($rel !== '' && $rel !== $basename) $candidates[] = $rel;
    foreach ($candidates as $rel_file) {
        $posts = get_posts([
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_wp_attached_file',
            'meta_value'     => $rel_file,
        ]);
        if ($posts) return (int) $posts[0];
    }
    return 0;
}

// Ajustes de Tienda > Sucursales: gestor visual de sucursales (imagen, título, ubicación, horario, info)
function farmapaz_sucursales_page() {
    if (!current_user_can('manage_options')) wp_die('Acceso denegado');
    farmapaz_sucursales_seed();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['farmapaz_sucursales_save'])) {
        if (isset($_POST['farmapaz_sucursales_nonce']) && wp_verify_nonce($_POST['farmapaz_sucursales_nonce'], 'farmapaz_sucursales')) {
            $rows = [];
            $nombres = isset($_POST['suc_nombre']) ? (array) $_POST['suc_nombre'] : [];
            foreach ($nombres as $i => $nombre) {
                $nombre = sanitize_text_field($nombre);
                if ($nombre === '') continue;
                $img_url = isset($_POST['suc_imagen'][$i]) ? esc_url_raw($_POST['suc_imagen'][$i]) : '';
                // Si la imagen viene de la biblioteca de medios, usa el tamaño optimizado 3:4.
                // En local las URLs pasan por proxy-image.php; extraemos la ruta real para resolver el adjunto.
                if ($img_url !== '') {
                    $resolve_url = $img_url;
                    if (strpos($img_url, 'proxy-image.php') !== false && preg_match('/url=([^&]+)/', $img_url, $qm)) {
                        $resolve_url = home_url('/') . rawurldecode($qm[1]);
                    }
                    $att_id = farmapaz_attachment_id_from_url($resolve_url);
                    if ($att_id) {
                        $sized = wp_get_attachment_image_url($att_id, 'farmapaz-sucursal');
                        if ($sized) $img_url = $sized;
                    }
                }
                $rows[] = [
                    'nombre'      => $nombre,
                    'direccion'   => isset($_POST['suc_direccion'][$i]) ? sanitize_text_field($_POST['suc_direccion'][$i]) : '',
                    'telefono'    => isset($_POST['suc_telefono'][$i]) ? sanitize_text_field($_POST['suc_telefono'][$i]) : '',
                    'horario'     => isset($_POST['suc_horario'][$i]) ? sanitize_text_field($_POST['suc_horario'][$i]) : '',
                    'informacion' => isset($_POST['suc_informacion'][$i]) ? sanitize_textarea_field($_POST['suc_informacion'][$i]) : '',
                    'imagen'      => $img_url,
                    'activa'      => isset($_POST['suc_activa'][$i]) && $_POST['suc_activa'][$i] === '1' ? '1' : '0',
                    'orden'       => isset($_POST['suc_orden'][$i]) ? max(0, (int) $_POST['suc_orden'][$i]) : ($i + 1),
                ];
            }
            usort($rows, function ($a, $b) { return $a['orden'] <=> $b['orden']; });
            update_option(FARMA_SUCURSALES_OPTION, $rows, false);
            echo '<div class="updated"><p>Sucursales guardadas.</p></div>';
        }
    }

    $sucursales = farmapaz_sucursales();
    wp_enqueue_media();
    $field_style = 'style="width:100%; max-width:520px;"';
    ?>
    <div class="wrap">
        <h1>Sucursales Farmapaz</h1>
        <p>Agrega, edita, ordena o elimina sucursales desde aquí. Se reflejan automáticamente en la página <strong>Sucursales</strong> del sitio, sin tocar código. Puedes subir la imagen de cada sucursal con el botón "Subir imagen".</p>

        <form method="post">
            <?php wp_nonce_field('farmapaz_sucursales', 'farmapaz_sucursales_nonce'); ?>
            <input type="hidden" name="farmapaz_sucursales_save" value="1">
            <div style="position: sticky; top: 32px; z-index: 10; background: #fff; padding: 10px 0; margin-bottom: 14px; border-bottom: 1px solid #dcdcde; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                <span style="color: #3c434a; font-weight: 600;">Este botón queda fijo arriba mientras editas.</span>
                <?php submit_button('Guardar sucursales'); ?>
            </div>

            <div id="sucursales-rows">
                <?php foreach ($sucursales as $i => $s): $s = wp_parse_args((array) $s, ['nombre' => '', 'direccion' => '', 'telefono' => '', 'horario' => '24 horas', 'informacion' => '', 'imagen' => '', 'activa' => '1', 'orden' => $i + 1]); ?>
                <fieldset style="border:1px solid #ccd0d4; border-radius:6px; padding:12px 14px; margin-bottom:14px; background:#fff; max-width:720px;">
                    <table class="form-table" style="margin:0;">
                        <tr>
                            <th style="width:140px;">Nombre</th>
                            <td><input type="text" name="suc_nombre[]" value="<?= esc_attr($s['nombre']); ?>" class="regular-text" required placeholder="Ej: La Paz"></td>
                        </tr>
                        <tr>
                            <th>Ubicación / Dirección</th>
                            <td><input type="text" name="suc_direccion[]" value="<?= esc_attr($s['direccion']); ?>" class="regular-text" placeholder="Dirección exacta"></td>
                        </tr>
                        <tr>
                            <th>Teléfono</th>
                            <td><input type="text" name="suc_telefono[]" value="<?= esc_attr($s['telefono']); ?>" class="regular-text" placeholder="Ej: 0412-8798885"></td>
                        </tr>
                        <tr>
                            <th>Horario</th>
                            <td><input type="text" name="suc_horario[]" value="<?= esc_attr($s['horario']); ?>" class="regular-text" placeholder="Ej: 24 horas o 8:00 AM — 6:00 PM"></td>
                        </tr>
                        <tr>
                            <th>Información</th>
                            <td><textarea name="suc_informacion[]" rows="2" class="large-text" placeholder="Notas opcionales: se muestran en la tarjeta (Ej: atención farmacéutica, entrega express...)"><?= esc_textarea($s['informacion']); ?></textarea></td>
                        </tr>
                        <tr>
                            <th>Imagen</th>
                            <td>
                                <input type="hidden" name="suc_imagen[]" class="suc-imagen" value="<?= esc_url($s['imagen']); ?>">
                                <div class="suc-imagen-box" style="position:relative; width:150px; height:200px; border:1px dashed #c3c4c7; border-radius:6px; background:#f9f9f9; margin-bottom:8px; overflow:hidden;">
                                    <span class="suc-imagen-empty" style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#8c8f94; font-size:12px; text-align:center;">Sin imagen<br>Vista previa</span>
                                    <img src="<?= esc_url($s['imagen']); ?>" class="suc-imagen-preview" style="width:100%; height:100%; object-fit:cover; <?= $s['imagen'] ? '' : 'display:none;'; ?>" alt="Vista previa">
                                </div>
                                <button type="button" class="button suc-imagen-btn">Subir imagen</button>
                                <button type="button" class="button suc-imagen-remove">Quitar</button>
                                <p class="description" style="margin-top:4px;">Foto vertical de la fachada. Se optimiza sola a 900×1200 (3:4) y se ve bien en todos los dispositivos.</p>
                            </td>
                        </tr>
                        <tr>
                            <th>Estado y orden</th>
                            <td>
                                <label style="margin-right:24px;"><input type="checkbox" name="suc_activa[]" value="1" <?= $s['activa'] === '1' ? 'checked' : ''; ?>> Activa (visible en la página)</label>
                                Orden: <input type="number" name="suc_orden[]" value="<?= (int) $s['orden']; ?>" min="0" style="width:70px;">
                                <button type="button" class="button-link-delete suc-row-remove" style="margin-left:24px;">Eliminar sucursal</button>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <?php endforeach; ?>
            </div>

            <p><button type="button" id="sucursal-add" class="button button-secondary">+ Añadir sucursal</button></p>
            <?php submit_button('Guardar sucursales'); ?>
        </form>
    </div>

    <template id="sucursal-row-template">
        <fieldset style="border:1px solid #ccd0d4; border-radius:6px; padding:12px 14px; margin-bottom:14px; background:#fff; max-width:720px;">
            <table class="form-table" style="margin:0;">
                <tr>
                    <th style="width:140px;">Nombre</th>
                    <td><input type="text" name="suc_nombre[]" class="regular-text" required placeholder="Ej: La Paz"></td>
                </tr>
                <tr>
                    <th>Ubicación / Dirección</th>
                    <td><input type="text" name="suc_direccion[]" class="regular-text" placeholder="Dirección exacta"></td>
                </tr>
                <tr>
                    <th>Teléfono</th>
                    <td><input type="text" name="suc_telefono[]" class="regular-text" placeholder="Ej: 0412-8798885"></td>
                </tr>
                <tr>
                    <th>Horario</th>
                    <td><input type="text" name="suc_horario[]" class="regular-text" value="24 horas" placeholder="Ej: 24 horas o 8:00 AM — 6:00 PM"></td>
                </tr>
                <tr>
                    <th>Información</th>
                    <td><textarea name="suc_informacion[]" rows="2" class="large-text" placeholder="Notas opcionales: se muestran en la tarjeta"></textarea></td>
                </tr>
                <tr>
                    <th>Imagen</th>
                    <td>
                        <input type="hidden" name="suc_imagen[]" class="suc-imagen" value="">
                        <div class="suc-imagen-box" style="position:relative; width:150px; height:200px; border:1px dashed #c3c4c7; border-radius:6px; background:#f9f9f9; margin-bottom:8px; overflow:hidden;">
                            <span class="suc-imagen-empty" style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#8c8f94; font-size:12px; text-align:center;">Sin imagen<br>Vista previa</span>
                            <img src="" class="suc-imagen-preview" style="width:100%; height:100%; object-fit:cover; display:none;" alt="Vista previa">
                        </div>
                        <button type="button" class="button suc-imagen-btn">Subir imagen</button>
                        <button type="button" class="button suc-imagen-remove">Quitar</button>
                        <p class="description" style="margin-top:4px;">Foto vertical de la fachada. Se optimiza sola a 900×1200 (3:4) y se ve bien en todos los dispositivos.</p>
                    </td>
                </tr>
                <tr>
                    <th>Estado y orden</th>
                    <td>
                        <label style="margin-right:24px;"><input type="checkbox" name="suc_activa[]" value="1" checked> Activa (visible en la página)</label>
                        Orden: <input type="number" name="suc_orden[]" value="0" min="0" style="width:70px;">
                        <button type="button" class="button-link-delete suc-row-remove" style="margin-left:24px;">Eliminar sucursal</button>
                    </td>
                </tr>
            </table>
        </fieldset>
    </template>

    <script>
    (function ($) {
        function bindRow(row) {
            row.find('.suc-imagen-btn').on('click', function (e) {
                e.preventDefault();
                var $row = $(this).closest('fieldset');
                var frame = wp.media({ title: 'Imagen de la sucursal', multiple: false, library: { type: 'image' } });
                frame.on('select', function () {
                    var url = frame.state().get('selection').first().toJSON().url;
                    $row.find('.suc-imagen').val(url);
                    $row.find('.suc-imagen-preview').attr('src', url).show();
                    $row.find('.suc-imagen-empty').hide();
                });
                frame.open();
            });
            row.find('.suc-imagen-remove').on('click', function (e) {
                e.preventDefault();
                var $row = $(this).closest('fieldset');
                $row.find('.suc-imagen').val('');
                $row.find('.suc-imagen-preview').attr('src', '').hide();
                $row.find('.suc-imagen-empty').show();
            });
            row.find('.suc-row-remove').on('click', function (e) {
                e.preventDefault();
                if (!confirm('¿Eliminar esta sucursal? Guarda el formulario para confirmar.')) return;
                $(this).closest('fieldset').remove();
            });
        }
        $('#sucursales-rows fieldset').each(function () { bindRow($(this)); });
        $('#sucursal-add').on('click', function (e) {
            e.preventDefault();
            var $tmpl = $('#sucursal-row-template').html();
            var $row = $($tmpl);
            $('#sucursales-rows').append($row);
            bindRow($row);
            $row.find('input[name="suc_nombre[]"]').trigger('focus');
        });
    })(jQuery);
    </script>
    <?php
}

// Build CapiBlue system prompt with live product data
function farmapaz_capiblue_system_prompt() {    $prompt = "Eres CapiBlue, el asistente virtual de Farmapaz Venezuela. ";
    $prompt .= "Eres amigable, entusiasta, usas español. ";
    $prompt .= "Tu personalidad: fresco, juvenil, futurista. ";
    $prompt .= "Usas frases cortas y claras.\n\n";
    $prompt .= "REGLAS ESTRICTAS:\n";
    $prompt .= "- SOLO respondes sobre Farmapaz Venezuela. ";
    $prompt .= "- Si te preguntan por Farmatodo, Farmadon, Farmatina, Locatel o cualquier otra farmacia, ";
    $prompt .= "  dices amablemente: 'Lo siento, solo conozco Farmapaz. ¿Quieres que te ayude con algo de Farmapaz?'\n";
    $prompt .= "- No inventes precios ni productos que no existan en Farmapaz.\n";
    $prompt .= "- Si no sabes algo, sugiere contactar por WhatsApp.\n";
    $prompt .= "- No des consejos médicos. Si te preguntan sobre síntomas, sugiere consultar a un médico.\n";
    $prompt .= "- Siempre mantén un tono positivo y servicial.\n\n";

    $prompt .= "INFORMACIÓN DE FARMApAZ:\n";
    $prompt .= "- Horarios: algunas sucursales son 24 horas y otras no. Para el horario exacto de cada sucursal, remite al usuario a la página de Sucursales: " . get_permalink(357) . "\n";
    $prompt .= "- Delivery disponible en Maturín, Caripito y Punta de Mata, Edo Monagas.\n";
    $prompt .= "- Delivery gratis en compras desde $" . farmapaz_free_shipping_min() . ".\n";
    $prompt .= "- En compras menores al mínimo, el delivery cuesta $" . farmapaz_shipping_fee_amount() . ".\n";
    $prompt .= "- Envío nacional disponible (consultar por WhatsApp).\n";
    $prompt .= "- WhatsApp: +58 412-8798885\n";
    $prompt .= "- Instagram: @farmapazofficial\n";
    $prompt .= "- Dirección: Maturín, Caripito y Punta de Mata, Estado Monagas, Venezuela.\n";
    $prompt .= "- Medicamentos controlados (psicotrópicos) solo se venden en tiendas físicas con receta médica.\n";
    $prompt .= "- Formas de pago: transferencia bancaria, pago móvil, efectivo en tienda.\n";
    $prompt .= "- Para pagos por transferencia se requiere: número de cédula, banco, teléfono y referencia.\n";
    $prompt .= "- Las sucursales están en Maturín, Caripito y Punta de Mata. Para dirección exacta y horario, remite a la página de Sucursales: " . get_permalink(357) . "\n\n";

    $prompt .= "ÓPTICA FARMAPAZ:\n";
    $prompt .= "- Página y catálogo: " . home_url('/optica/') . "\n";
    $prompt .= "- Catálogo directo: " . farmapaz_optica_catalogo_url() . "\n";
    $prompt .= "- Examen de la vista GRATIS en las sucursales de Óptica Farmapaz.\n";
    $prompt .= "- Se venden monturas y lentes de sol de marcas como Ray-Ban, Oakley, Prada, Versace, Tom Ford, Guess, Carolina Herrera, Michael Kors, Palazzo y más.\n";
    $prompt .= "- Los productos de óptica son SOLO CATÁLOGO: no se compran en línea. El precio se consulta y la disponibilidad se confirma por WhatsApp: " . farmapaz_whatsapp_url('Hola, quiero información de Óptica Farmapaz.') . "\n";
    $prompt .= "- Promoción Cashea: Montura + cristales + examen optométrico, con pago en cuotas sin intereses en tiendas físicas. Enlace de la promo: " . home_url('/optica/#cashea') . "\n";
    $prompt .= "- También hay Cashea para compras de farmacia en tiendas físicas.\n";
    $prompt .= "- Si preguntan por una marca o modelo de óptica, usa el catálogo y ofrece consultar disponibilidad por WhatsApp.\n\n";

    // [†] Categorías jerárquicas: padres → hijas (el AI conoce toda la estructura)
    $parent_cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0, 'orderby' => 'count', 'order' => 'DESC']);
    if (!empty($parent_cats) && !is_wp_error($parent_cats)) {
        $prompt .= "CATEGORÍAS DISPONIBLES (usar el enlace al mencionarlas):\n";
        foreach ($parent_cats as $pcat) {
            $pcat_url = get_term_link($pcat);
            $prompt .= "- [{$pcat->name}]({$pcat_url}) ({$pcat->count} productos)\n";
            $child_cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => $pcat->term_id, 'orderby' => 'count', 'order' => 'DESC']);
            if (!empty($child_cats) && !is_wp_error($child_cats)) {
                foreach ($child_cats as $ccat) {
                    $ccat_url = get_term_link($ccat);
                    $prompt .= "  - [{$ccat->name}]({$ccat_url}) ({$ccat->count} productos)\n";
                }
            }
        }
        $prompt .= "\n";
    }

    // [†] Marcas disponibles (todas, con enlace directo y conteo de productos)
    $brands_terms = get_terms(['taxonomy' => 'product_brand', 'hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC']);
    if (!empty($brands_terms) && !is_wp_error($brands_terms)) {
        $prompt .= "MARCAS DISPONIBLES (usar el enlace al mencionarlas):\n";
        foreach ($brands_terms as $brand) {
            $brand_url = get_term_link($brand);
            $prompt .= "- [{$brand->name}]({$brand_url}) ({$brand->count} productos)\n";
        }
        $prompt .= "\n";
    }

    $prompt .= "ENLACES:\n";
    $prompt .= "- Tienda: " . get_permalink(wc_get_page_id('shop')) . "\n";
    $prompt .= "- Carrito: " . wc_get_cart_url() . "\n";
    $prompt .= "- WhatsApp: " . farmapaz_whatsapp_url() . "\n";
    $prompt .= "- Instagram: @farmapazofficial\n\n";

    $prompt .= "FORMATO DE RESPUESTA:\n";
    $prompt .= "- Responde de forma natural y conversacional, como un asesor amable de farmacia.\n";
    $prompt .= "- Respuestas cortas (máximo 3-4 líneas).\n";
    $prompt .= "- NO uses simbolos de markdown como #, *, _, >, ni listas largas. Escribe en texto plano.\n";
    $prompt .= "- Si mencionas productos, usa exactamente el formato [nombre](url) que viene en el contexto (máximo 3 productos).\n";
    $prompt .= "- SOLO usa los enlaces exactos que recibes en el contexto — NO inventes URLs.\n";
    $prompt .= "- Si el usuario busca algo que no está en el contexto, no inventes productos: sugiere visitar la tienda o una categoria.\n";
    $prompt .= "- Al FINAL de cada respuesta, SIEMPRE agrega: Contactanos por WhatsApp: " . farmapaz_whatsapp_url() . "\n";
    $prompt .= "- Siempre finaliza ofreciendo ayuda adicional.\n";

    return $prompt;
}

// Search products for context
function farmapaz_capiblue_search_products($query) {
    global $wpdb;

    $query = trim($query);
    if (empty($query)) return [];

    $normalized = remove_accents(mb_strtolower($query));

    // Synonym map — every term maps to related search keywords
    $synonyms = [
        // Medicamentos
        'acetaminofen'  => 'acetaminofen paracetamol',
        'acetaminofén'  => 'acetaminofen paracetamol',
        'paracetamol'   => 'paracetamol acetaminofen',
        'ibuprofeno'    => 'ibuprofeno',
        'aspirina'      => 'aspirina acido acetilsalicilico',
        'amoxicilina'   => 'amoxicilina',
        'loratadina'    => 'loratadina',
        'omeprazol'     => 'omeprazol',
        'vitamina'      => 'vitamina',
        'vitaminas'     => 'vitamina complejo suplemento',
        'suplemento'    => 'suplemento vitaminas',
        'suplementos'   => 'suplemento vitaminas minerales',
        'alcohol'       => 'alcohol antiseptico',
        'antibiotico'   => 'antibiotico amoxicilina',
        'antialergico'  => 'antialergico loratadina cetirizina',
        'antigripal'    => 'antigripal resfriado gripe',
        'gripe'         => 'gripe resfriado antigripal',
        'dolor'         => 'dolor analgesico ibuprofeno acetaminofen',
        'analgesico'    => 'analgesico dolor ibuprofeno acetaminofen',
        'fiebre'        => 'fiebre acetaminofen ibuprofeno',
        'colicos'       => 'dolor menstrual colicos',
        'estomacal'     => 'estomago digestion antiacido',
        'dolores'       => 'dolor analgesico',
        'cuidado personal' => 'shampoo jabon crema desodorante',
        'higiene'       => 'jabon shampoo desodorante dental',
        // Protección
        'tapaboca'      => 'tapaboca mascarilla barbijo cubreboca',
        'mascarilla'    => 'tapaboca mascarilla barbijo cubreboca',
        'cubreboca'     => 'tapaboca mascarilla barbijo cubreboca',
        'barbijo'       => 'tapaboca mascarilla barbijo',
        // Skin care
        'skincare'      => 'skincare cuidado facial crema serum',
        'crema'         => 'crema hidratante facial cuerpo',
        'cremas'        => 'crema hidratante facial cuerpo',
        'facial'        => 'facial crema serum limpieza',
        'coreano'       => 'coreano korean skincare',
        'korean'        => 'coreano korean skincare',
        'protector solar' => 'protector solar bloqueador',
        'bloqueador'    => 'protector solar bloqueador',
        // Cabello
        'cabello'       => 'cabello pelo shampoo acondicionador capilar',
        'pelo'          => 'cabello pelo shampoo acondicionador capilar',
        'shampoo'       => 'shampoo champu cabello pelo',
        'champu'        => 'shampoo champu cabello pelo',
        'acondicionador' => 'acondicionador cabello pelo',
        'capilar'       => 'capilar cabello pelo shampoo',
        // Bebé
        'bebe'          => 'bebe bebe infantil pañal toallita',
        'bebé'          => 'bebe bebe infantil pañal toallita',
        'pañal'         => 'pañal pañales bebe infantil',
        'pañales'       => 'pañal pañales bebe infantil',
        'infantil'      => 'infantil bebe niño',
        'bebe'          => 'bebe infantil pañal toallita',
        // [†] Dulces / Comida (sinónimos expandidos para búsqueda de snacks/alimentos)
        'dulces'        => 'dulces caramelos chocolate chicle chupeta',
        'caramelos'     => 'dulces caramelos chocolate',
        'chocolate'     => 'chocolate dulces',
        'chupeta'       => 'chupeta dulce caramelo',
        'chicle'        => 'chicle goma de mascar',
        'galletas'      => 'galletas snack',
        'snacks'        => 'snacks galletas dulces papitas',
        'comer'         => 'comida dulces snack galletas papitas brownie chucheria',
        'comida'        => 'snacks dulces galletas alimentos',
        'almorzar'      => 'comida almuerzo snack',
        'hambre'        => 'comer comida snack galletas',
        'tentempie'     => 'snack galletas dulces papitas',
        'snack'         => 'snacks galletas dulces papitas brownie',
        'golosinas'     => 'dulces caramelos golosinas',
        // Cosméticos / Belleza
        'maquillaje'    => 'maquillaje labial rimel base',
        'labial'        => 'labial pintalabios',
        'rimel'         => 'rimel pestanas',
        'esmalte'       => 'esmalte unas',
        'belleza'       => 'belleza cosmeticos maquillaje',
        'cosmeticos'    => 'cosmeticos maquillaje belleza',
        // Jabones / Desodorantes
        'jabon'         => 'jabon baño corporal',
        'jabón'         => 'jabon baño corporal',
        'desodorante'   => 'desodorante antitranspirante',
        'colonia'       => 'colonia perfume fragancia',
        'perfume'       => 'colonia perfume fragancia',
        // Oral
        'dental'        => 'dental cepillo pasta hilo bucal',
        'cepillo'       => 'cepillo dental dientes',
        'pasta dental'  => 'crema dental pasta dental',
        // Aseo / Hogar
        'lavandina'     => 'lavandina cloro blanqueador',
        'cloro'         => 'cloro lavandina',
        'jabon liquido' => 'jabon liquido manos',
        'papel'         => 'papel higienico servilleta toalla',
        'higienico'     => 'papel higienico',
        'toallas'       => 'toalla femenina sanitaria',
        'femenina'      => 'toalla femenina sanitaria',
        'pañito'        => 'pañito humedo toallita',
        // Primeros auxilios
        'curita'        => 'curita bandita adhesivo',
        'vendas'        => 'venda gasa vendaje',
        'gasas'         => 'gasa vendas',
        // Categorías comunes
        'medicina'      => 'medicamento farmacia',
        'medicamento'   => 'medicamento farmacia',
        'farmacia'      => 'farmacia medicamento',
        'tienda'        => 'tienda online catalogo',
        'productos'     => 'productos catalogo',
        'catalogo'      => 'catalogo productos',
        // Óptica
        'optica'        => 'optica lentes monturas gafas anteojos',
        'montura'       => 'montura lentes gafas anteojos',
        'monturas'      => 'montura lentes gafas anteojos',
        'gafas'         => 'gafas lentes montura anteojos',
        'anteojos'      => 'anteojos lentes montura gafas',
        'lentes de sol' => 'lentes de sol gafas anteojos',
        'lentes'        => 'lentes gafas montura anteojos',
        // [†] Sintomas, partes del cuerpo y usos creativos
        'duele'         => 'dolor analgesico ibuprofeno acetaminofen',
        'dolor de cabeza' => 'dolor cabeza migraña acetaminofen',
        'dolor de muela' => 'dolor muela dental analgesico',
        'dolor muscular' => 'dolor muscular relajante gel',
        'dolor de espalda' => 'dolor espalda analgesico',
        'dolor de garganta' => 'garganta dolor spray pastilla',
        'dolor de oido' => 'oido dolor gotas',
        'dolor menstrual' => 'dolor menstrual colicos',
        'cabeza'        => 'cabeza dolor migraña acetaminofen',
        'muela'         => 'muela dental dolor',
        'garganta'      => 'garganta dolor spray pastilla',
        'espalda'       => 'espalda dolor analgesico',
        'musculo'       => 'musculo muscular relajante gel',
        'muscular'      => 'muscular relajante gel',
        'tos'           => 'tos jarabe expectorante',
        'alergia'       => 'alergia loratadina cetirizina',
        'mocos'         => 'mocos descongestionante resfriado',
        'nasal'         => 'nasal nariz spray',
        'nariz'         => 'nariz spray nasal',
        'sueno'         => 'sueno dormir melatonina',
        'dormir'        => 'dormir sueno melatonina',
        'insomnio'      => 'insomnio melatonina sueno',
        'estomago'      => 'estomago digestion antiacido',
        'estomacal'     => 'estomago digestion antiacido',
        'gastritis'     => 'gastritis acidez estomago',
        'acidez'        => 'acidez antiacido estomago',
        'digestion'     => 'digestion estomago probiotico',
        'estreñimiento' => 'estreñimiento laxante',
        'diarrea'       => 'diarrea suero probiotico',
        'nausea'        => 'nausea vomito mareo',
        'vomito'        => 'vomito nausea',
        'antiacido'     => 'antiacido estomago acidez',
        'probiotico'    => 'probiotico digestion flora',
        'laxante'       => 'laxante estreñimiento',
        'piel'          => 'piel crema dermatologica',
        'acne'          => 'acne crema acne',
        'quemadura'     => 'quemadura quemaduras crema',
        'herida'        => 'herida curita gasa cicatrizar',
        'cicatriz'      => 'cicatriz crema cicatrizante',
        'ojos'          => 'ojos lagrima colirio',
        'lagrima'       => 'lagrima ojos colirio',
        'oidos'         => 'oidos oido gotas',
        'oido'          => 'oido gotas',
        // [†] Salud sexual
        'sexual'        => 'salud sexual preservativo lubricante',
        'preservativo'  => 'preservativo condon profilactico',
        'condon'        => 'preservativo condon',
        'lubricante'    => 'lubricante salud sexual',
        'anticonceptivo'=> 'anticonceptivo pildora',
        // [†] Maternidad / bebe
        'maternidad'    => 'maternidad bebe lactancia',
        'embarazo'      => 'embarazo prueba materno prenatal',
        'prueba de embarazo' => 'prueba embarazo',
        'test de embarazo' => 'test embarazo',
        'prueba de ovulacion' => 'prueba ovulacion',
        'prueba'        => 'prueba embarazo ovulacion',
        'test'          => 'test embarazo',
        'ovulacion'     => 'ovulacion prueba embarazo',
        'lactancia'     => 'lactancia maternidad',
        // [†] Corazon / presion / diabetes
        'presion'       => 'presion tensiometro salud cardiovascular',
        'colesterol'    => 'colesterol salud cardiovascular',
        'diabetes'      => 'diabetes glucosa control',
        'azucar'        => 'azucar glucosa diabetes',
        'corazon'       => 'corazon salud cardiovascular',
        'cardiovascular'=> 'cardiovascular corazon presion',
        // [†] Inflamacion y analgesicos especificos
        'antiinflamatorio' => 'antiinflamatorio ibuprofeno diclofenaco',
        'diclofenaco'   => 'diclofenaco antiinflamatorio',
        'naproxeno'     => 'naproxeno antiinflamatorio',
        'ketorolaco'    => 'ketorolaco analgesico',
        'metamizol'     => 'metamizol analgesico',
        'cetirizina'    => 'cetirizina antialergico',
        // [†] Suplementos
        'proteina'      => 'proteina suplemento deporte',
        'omega'         => 'omega 3 suplemento',
        'colageno'      => 'colageno suplemento',
        'hierro'        => 'hierro suplemento',
        'calcio'        => 'calcio suplemento',
        'magnesio'      => 'magnesio suplemento',
        // [†] Alimentos / bebidas
        'alimento'      => 'alimento alimentos avena',
        'alimentos'     => 'alimentos avena cereal',
        'avena'         => 'avena alimentos cereal',
        'cereal'        => 'cereal alimentos',
        'miel'          => 'miel alimentos',
        'jugo'          => 'jugo bebida',
        'refresco'      => 'refresco bebida gaseosa',
        'gaseosa'       => 'gaseosa refresco bebida',
        'cafe'          => 'cafe bebida',
        'te'            => 'te infusion bebida',
        'agua'          => 'agua mineral bebida',
        'tomar'         => 'bebida refresco jugo gaseosa',
        'beber'         => 'bebida refresco jugo gaseosa',
        'bebida'        => 'bebida refresco jugo gaseosa',
        'bebidas'       => 'bebida refresco jugo gaseosa',
        'chucheria'     => 'chucheria snack galletas dulces',
        'papitas'       => 'papitas snack',
        'brownie'       => 'brownie snack dulce',
        'caramelo'      => 'caramelo dulces',
        'mermelada'     => 'mermelada alimentos',
        'enlatado'      => 'enlatado conserva alimentos',
        // [†] Hogar / mascotas / tecnologia
        'mascota'       => 'mascota perro gato',
        'mascotas'      => 'mascotas perro gato',
        'perro'         => 'perro mascota',
        'gato'          => 'gato mascota',
        'hogar'         => 'hogar articulos casa',
        'telefono'      => 'telefono tecnologia accesorio',
        'audifono'      => 'audifono tecnologia',
        'memoria'       => 'memoria usb tecnologia',
    ];

    // Stop words to exclude from individual word search
    $stop_words = ['de', 'la', 'el', 'los', 'las', 'del', 'en', 'un', 'una', 'para', 'por', 'con', 'sin', 'y', 'e', 'o', 'a', 'que', 'es', 'se', 'su', 'lo', 'le', 'da', 'al', 'como', 'mas', 'más', 'muy', 'todo', 'dame', 'quiero', 'necesito', 'busco', 'tiene', 'tienen', 'productos', 'producto', 'comprar'];

    // [†] Orden: primero la intencion (sinonimos), luego palabras, y el mensaje
    //     crudo de barrido al final. Asi 'quiero un snack' no se contamina con
    //     matches amplios tipo '%un%' ni '%tos%' dentro de 'antibioticos'.
    $words = explode(' ', $normalized);
    $search_queries = [];

    // Synonyms per-word and full-phrase (keywords mas especificos primero)
    foreach ($words as $w) {
        if (isset($synonyms[$w])) {
            $search_queries[] = $synonyms[$w];
        }
    }
    if (isset($synonyms[$normalized])) {
        $search_queries[] = $synonyms[$normalized];
    }

    // Check if $normalized CONTAINS any multi-word synonym key (substring match)
    foreach ($synonyms as $key => $value) {
        if (strpos($key, ' ') !== false && strpos($normalized, $key) !== false) {
            $search_queries[] = $value;
        }
    }

    // Also try "para el X" → just X
    foreach (['para el ', 'para la ', 'de ', 'del '] as $prefix) {
        if (strpos($normalized, $prefix) === 0) {
            $stripped = substr($normalized, strlen($prefix));
            if (strlen($stripped) >= 3) {
                $search_queries[] = $stripped;
            }
        }
    }

    // Individual meaningful words
    foreach ($words as $w) {
        if (strlen($w) >= 3 && !in_array($w, $stop_words)) {
            $search_queries[] = $w;
        }
    }

    // Look up category slugs that match any word — adds category-based results
    $cat_words = [];
    foreach ($words as $w) {
        if (strlen($w) >= 3) {
            $cat_words[] = $w;
        }
    }
    if (!empty($cat_words)) {
        $cat_slugs = $wpdb->get_col(
            "SELECT slug FROM {$wpdb->terms} t
             JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
             WHERE tt.taxonomy = 'product_cat'
             AND (" . implode(' OR ', array_map(function($w) use ($wpdb) {
                 return $wpdb->prepare("t.slug LIKE %s OR t.name LIKE %s", '%' . $wpdb->esc_like($w) . '%', '%' . $wpdb->esc_like($w) . '%');
             }, $cat_words)) . ")"
        );
        foreach ($cat_slugs as $slug) {
            $search_queries[] = $slug;
        }
    }

    // Raw message last (queda como barrido de seguridad)
    $search_queries[] = $query;
    $search_queries[] = $normalized;

    // [†] Category-aware search: si el usuario menciona ciertas palabras, busca directo en categorías relevantes
    //     Ej: "comer" → busca en "snacks", "dulces", "alimentos"
    //     Ej: "dolor" → busca en "alivio-del-dolor"
    // [†] Categorias reales del catalogo (slugs verificados en BD)
    $cat_intents = [
        // Comida
        'comer'    => ['snacks', 'alimentos', 'alimentos-y-bebidas'],
        'comida'   => ['alimentos', 'alimentos-y-bebidas', 'snacks'],
        'hambre'   => ['snacks', 'alimentos'],
        'snack'    => ['snacks'],
        'snacks'   => ['snacks'],
        'dulces'   => ['snacks'],
        'chocolate'=> ['snacks'],
        'galleta'  => ['snacks'],
        'galletas' => ['snacks'],
        'papitas'  => ['snacks'],
        'bebida'   => ['bebidas', 'alimentos-y-bebidas'],
        'bebidas'  => ['bebidas', 'alimentos-y-bebidas'],
        'alimento' => ['alimentos', 'alimentos-y-bebidas'],
        'alimentos'=> ['alimentos', 'alimentos-y-bebidas'],
        'avena'    => ['alimentos'],
        'cereal'   => ['alimentos'],
        'infantil' => ['alimentacion-infantil', 'bebes-y-maternidad'],
        // Salud
        'dolor'    => ['alivio-del-dolor'],
        'gripe'    => ['salud-respiratoria-y-alergias'],
        'tos'      => ['salud-respiratoria-y-alergias'],
        'fiebre'   => ['alivio-del-dolor'],
        'alergia'  => ['salud-respiratoria-y-alergias'],
        'respiratorio' => ['salud-respiratoria-y-alergias'],
        'digestion'=> ['salud-digestiva'],
        'estomago' => ['salud-digestiva'],
        'gastritis'=> ['salud-digestiva'],
        'acidez'   => ['salud-digestiva'],
        'cardio'   => ['salud-cardiovascular'],
        'presion'  => ['salud-cardiovascular'],
        'colesterol' => ['salud-cardiovascular'],
        'corazon'  => ['salud-cardiovascular'],
        'diabetes' => ['salud-cardiovascular'],
        'neurologico' => ['neurologicos'],
        'sueno'    => ['neurologicos', 'productos-naturales-y-vitaminas'],
        'dormir'   => ['neurologicos', 'productos-naturales-y-vitaminas'],
        'vitamina' => ['productos-naturales-y-vitaminas'],
        'vitaminas'=> ['productos-naturales-y-vitaminas'],
        'suplemento' => ['productos-naturales-y-vitaminas', 'nutricion-especializada'],
        'natural'  => ['productos-naturales-y-vitaminas'],
        'oncologia'=> ['oncologicos'],
        'antibiotico' => ['antibioticos'],
        // Salud sexual
        'sexual'   => ['salud-sexual'],
        'preservativo' => ['salud-sexual'],
        'condon'   => ['salud-sexual'],
        'anticonceptivo' => ['salud-sexual'],
        // Bebe / maternidad
        'bebe'     => ['bebes-y-maternidad', 'higiene-del-bebe', 'alimentacion-infantil'],
        'pañal'    => ['bebes-y-maternidad', 'higiene-del-bebe'],
        'maternidad' => ['bebes-y-maternidad', 'cuidado-materno'],
        // [†] Las pruebas de embarazo estan en Salud Sexual (y Salud y Bienestar),
        //     NO en bebes-y-maternidad ni cuidado-materno (verificado en BD).
        'embarazo' => ['salud-sexual', 'cuidado-materno'],
        'prueba'   => ['salud-sexual'],
        'ovulacion'=> ['salud-sexual'],
        'test'     => ['salud-sexual'],
        // Cuidado personal y dermocosmeticos
        'shampoo'  => ['cuidado-corporal-y-capilar'],
        'cabello'  => ['cuidado-corporal-y-capilar'],
        'pelo'     => ['cuidado-corporal-y-capilar'],
        'capilar'  => ['cuidado-corporal-y-capilar'],
        'facial'   => ['cuidado-facial', 'dermatologicos'],
        'cara'     => ['cuidado-facial'],
        'crema'    => ['dermatologicos', 'cuidado-personal-y-dermocosmeticos'],
        'piel'     => ['dermatologicos'],
        'acne'     => ['dermatologicos'],
        'maquillaje' => ['belleza-y-maquillaje'],
        'belleza'  => ['belleza-y-maquillaje'],
        'cosmetico'=> ['belleza-y-maquillaje', 'cuidado-personal-y-dermocosmeticos'],
        'higiene'  => ['higiene-diaria'],
        'dermo'    => ['cuidado-personal-y-dermocosmeticos'],
        'cuidado'  => ['cuidado-personal-y-dermocosmeticos'],
        // Primeros auxilios / hogar / otros
        'botiquin' => ['insumos-hospitalarios-y-botiquin-de-primeros-auxilios'],
        'curita'   => ['insumos-hospitalarios-y-botiquin-de-primeros-auxilios'],
        'gasa'     => ['insumos-hospitalarios-y-botiquin-de-primeros-auxilios'],
        'venda'    => ['insumos-hospitalarios-y-botiquin-de-primeros-auxilios'],
        'rehabilitacion' => ['rehabilitacion-y-equipos-medicos'],
        'mascota'  => ['mascotas'],
        'perro'    => ['mascotas'],
        'gato'     => ['mascotas'],
        'hogar'    => ['hogar-mascotas-y-otros', 'articulos-para-el-hogar'],
        'tecnologia' => ['tecnologia'],
        'escolar'  => ['escolar-y-oficina'],
        'entretenimiento' => ['entretenimiento'],
        // [†] Medicamentos genericos
        'medicamento' => ['medicamentos'],
        'medicina'    => ['medicamentos'],
        'farmacia'    => ['medicamentos'],
        // Óptica
        'optica'      => ['optica-tienda'],
        'montura'     => ['optica-tienda'],
        'monturas'    => ['optica-tienda'],
        'gafas'       => ['optica-tienda'],
        'anteojos'    => ['optica-tienda'],
        'lentes'      => ['optica-tienda'],
        // [†] Bebidas
        'jugo'     => ['bebidas'],
        'cafe'     => ['bebidas'],
        'te'       => ['bebidas'],
        'agua'     => ['bebidas'],
        'refresco' => ['bebidas'],
        'gaseosa'  => ['bebidas'],
        'tomar'    => ['bebidas', 'alimentos-y-bebidas'],
        'beber'    => ['bebidas', 'alimentos-y-bebidas'],
        // [†] Higiene
        'papel'    => ['higiene-diaria'],
        'higienico'=> ['higiene-diaria'],
        'dental'   => ['higiene-diaria'],
        'cepillo'  => ['higiene-diaria'],
        // [†] Suplementos
        'proteina' => ['productos-naturales-y-vitaminas', 'nutricion-especializada'],
        'omega'    => ['productos-naturales-y-vitaminas'],
        'colageno' => ['productos-naturales-y-vitaminas', 'dermatologicos'],
        'hierro'   => ['productos-naturales-y-vitaminas'],
        'calcio'   => ['productos-naturales-y-vitaminas'],
        'magnesio' => ['productos-naturales-y-vitaminas'],
    ];

    // Check which category intents match the user's words (single word or full phrase)
    $target_cat_slugs = [];
    foreach ($words as $w) {
        if (isset($cat_intents[$w])) {
            $target_cat_slugs = array_merge($target_cat_slugs, $cat_intents[$w]);
        }
    }
    foreach ($cat_intents as $key => $slugs) {
        if (strpos($key, ' ') !== false && strpos($normalized, $key) !== false) {
            $target_cat_slugs = array_merge($target_cat_slugs, $slugs);
        }
    }
    $target_cat_slugs = array_unique($target_cat_slugs);

    $search_queries = array_unique(array_filter($search_queries));
    $all_ids = [];
    $limit = 25;
    // [†] Cupo reservado para los matches por palabra (relevancia real). La
    //     categoria solo rellena el resto: evita que una categoria grande (ej.
    //     bebes-y-maternidad, 620 productos) llene todo el contexto y tape los
    //     resultados exactos (las pruebas de embarazo reales).
    $keyword_quota = 15;

    // [†] 0. Keyword queries PRIMERO con ranking por relevancia:
    //     palabra completa en titulo > prefijo/titulo > SKU > categoria > contenido.
    //     Evita que substrings cortos ('tos' dentro de 'antibioticos') ganen al match real.
    //     El cupo reservado ($keyword_quota) garantiza que los resultados exactos
    //     siempre entren al contexto antes que el relleno por categoria.
    foreach ($search_queries as $sq) {
        if (count($all_ids) >= $keyword_quota) break;

        // Remove stop words from EVERY search phrase (not just per-word):
        // 'quiero un snack' no debe buscar '%un%' (matchea todo el catalogo)
        $terms = array_values(array_filter(
            array_unique(explode(' ', $sq)),
            function ($t) use ($stop_words) {
                return strlen($t) >= 3 && !in_array($t, $stop_words);
            }
        ));
        if (empty($terms)) continue;

        $score_parts = [];
        $where_ors = [];
        foreach ($terms as $term) {
            $like = '%' . $wpdb->esc_like($term) . '%';
            $word = '[[:<:]]' . preg_quote($term, '/') . '[[:>:]]';

            $where_ors[] = $wpdb->prepare(
                "(p.post_title LIKE %s OR p.post_excerpt LIKE %s OR p.post_content LIKE %s
                  OR (tt.taxonomy IN ('product_cat','product_tag','product_brand') AND t.name LIKE %s)
                  OR EXISTS (SELECT 1 FROM {$wpdb->postmeta} pmS WHERE pmS.post_id = p.ID AND pmS.meta_key = '_sku' AND pmS.meta_value LIKE %s))", // [†] brand + SKU incluido
                $like, $like, $like, $like, $like
            );

            $score_parts[] = $wpdb->prepare(
                "CASE
                   WHEN p.post_title REGEXP %s THEN 90
                   WHEN p.post_title LIKE %s THEN 45
                   WHEN EXISTS (SELECT 1 FROM {$wpdb->postmeta} pmS2 WHERE pmS2.post_id = p.ID AND pmS2.meta_key = '_sku' AND pmS2.meta_value LIKE %s) THEN 50
                   WHEN (tt.taxonomy IN ('product_cat','product_tag','product_brand') AND t.name REGEXP %s) THEN 40
                   WHEN (tt.taxonomy IN ('product_cat','product_tag','product_brand') AND t.name LIKE %s) THEN 25
                   WHEN p.post_content REGEXP %s OR p.post_excerpt REGEXP %s THEN 15
                   WHEN p.post_content LIKE %s OR p.post_excerpt LIKE %s THEN 5
                   ELSE 0 END",
                $word, $like,
                $like,
                $word, $like,
                $word, $word,
                $like, $like
            );
        }
        $where_extra = implode(' OR ', $where_ors);
        $score_expr  = implode(' + ', $score_parts);

        $sql = "SELECT t.ID FROM (
                    SELECT DISTINCT p.ID, p.menu_order, p.post_title, ({$score_expr}) AS score
                    FROM {$wpdb->posts} p
                    LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                    LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                    WHERE p.post_type = 'product'
                      AND p.post_status = 'publish'
                      AND ({$where_extra})
                      AND EXISTS (
                          SELECT 1 FROM {$wpdb->postmeta} pmStock
                          WHERE pmStock.post_id = p.ID AND pmStock.meta_key = '_stock_status' AND pmStock.meta_value = 'instock'
                      )
                      AND EXISTS (
                          SELECT 1 FROM {$wpdb->postmeta} pmPrice
                          WHERE pmPrice.post_id = p.ID AND pmPrice.meta_key = '_price' AND pmPrice.meta_value+0 > 0
                      )
                ) t
                WHERE t.score > 0
                ORDER BY t.score DESC, t.menu_order ASC, t.post_title ASC
                LIMIT " . ($keyword_quota - count($all_ids));

        $ids = $wpdb->get_col($sql);
        if (!empty($ids)) {
            foreach ($ids as $id) {
                if (!in_array($id, $all_ids)) {
                    $all_ids[] = $id;
                }
            }
        }
    }

    // [†] 1. Category-intent query DESPUES: rellena lo que falte del limite.
    //     "dolor" → alivio-del-dolor, "snack" → snacks, "sexual" → salud-sexual...
    if (!empty($target_cat_slugs)) {
        $cat_placeholders = implode(',', array_fill(0, count($target_cat_slugs), '%s'));
        $cat_sql = $wpdb->prepare(
            "SELECT DISTINCT p.ID
             FROM {$wpdb->posts} p
             JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
             JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
             JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
             WHERE p.post_type = 'product'
               AND p.post_status = 'publish'
               AND tt.taxonomy = 'product_cat'
               AND (t.slug IN ({$cat_placeholders}) OR t.name IN ({$cat_placeholders}))
               AND EXISTS (
                   SELECT 1 FROM {$wpdb->postmeta} pmStock
                   WHERE pmStock.post_id = p.ID AND pmStock.meta_key = '_stock_status' AND pmStock.meta_value = 'instock'
               )
               AND EXISTS (
                   SELECT 1 FROM {$wpdb->postmeta} pmPrice
                   WHERE pmPrice.post_id = p.ID AND pmPrice.meta_key = '_price' AND pmPrice.meta_value+0 > 0
               )
             ORDER BY p.menu_order ASC, p.post_title ASC
             LIMIT %d",
            array_merge($target_cat_slugs, $target_cat_slugs, [max(0, $limit - count($all_ids))])
        );
        $cat_ids = $wpdb->get_col($cat_sql);
        if (!empty($cat_ids)) {
            foreach ($cat_ids as $cid) {
                if (!in_array($cid, $all_ids)) {
                    $all_ids[] = $cid;
                }
            }
        }
    }

    $products = [];
    foreach (array_slice($all_ids, 0, $limit) as $id) {
        $product = wc_get_product($id);
        if ($product) {
            $cats = wp_get_post_terms($id, 'product_cat', ['fields' => 'names']);
            $brands = wp_get_post_terms($id, 'product_brand', ['fields' => 'names']);
            $products[] = [
                'name'   => $product->get_name(),
                'price'  => html_entity_decode(strip_tags(wc_price($product->get_price()))),
                'cat'    => !empty($cats) ? $cats[0] : '',
                'brand'  => !empty($brands) ? $brands[0] : '',
                'url'    => strtok(get_permalink($id), '?'), // [†] strtok quita ?v= parámetros que causan 404
                'stock'  => $product->is_in_stock() ? 'Disponible' : 'Agotado',
            ];
        }
    }

    return $products;
}

// Fallback products when search yields nothing — always give the AI something to work with
function farmapaz_capiblue_fallback_products($count = 8) {
    $products = [];
    // [†] Exclude $0 products (productos sin precio asignado)
    $ids = wc_get_products([
        'limit'        => $count,
        'status'       => 'publish',
        'stock_status' => 'instock',
        'meta_query'   => [
            ['key' => '_price', 'value' => 0, 'compare' => '>', 'type' => 'DECIMAL'],
            farmapaz_optica_meta_exclude(), // [†] óptica solo en /optica/
        ],
        'orderby'      => 'total_sales',
        'order'        => 'DESC',
        'return'       => 'ids',
    ]);
    foreach ($ids as $id) {
        $product = wc_get_product($id);
        if ($product) {
            $cats = wp_get_post_terms($id, 'product_cat', ['fields' => 'names']);
            $brands = wp_get_post_terms($id, 'product_brand', ['fields' => 'names']);
            $products[] = [
                'name'   => $product->get_name(),
                'price'  => html_entity_decode(strip_tags(wc_price($product->get_price()))),
                'cat'    => !empty($cats) ? $cats[0] : '',
                'brand'  => !empty($brands) ? $brands[0] : '',
                'url'    => strtok(get_permalink($id), '?'), // [†] strtok quita ?v= parámetros que causan 404
                'stock'  => 'Disponible',
            ];
        }
    }
    return $products;
}

/**
 * Local chatbot engine — handles everything without Gemini.
 * Returns a reply string, or null to continue to other handlers.
 */
/**
 * Match a keyword as a whole word (word boundaries). Prevents false positives
 * from substrings: 'hi' inside "chiste", 'hora' inside "ahora", 'ig' inside
 * "digital". $msg must already be lowercase + remove_accents().
 */
function farmapaz_capiblue_matches($msg, $keyword) {
    if ($keyword === '' || $keyword === '$') return false;
    if (!preg_match('/[a-z0-9áéíóúüñ]/', $keyword)) return false;
    return preg_match('/\b' . preg_quote($keyword, '/') . '\b/', $msg) === 1;
}

/**
 * Busca monturas de óptica por marca para CapiBlue.
 * Devuelve una lista corta con nombre, URL y precio.
 */
function farmapaz_capiblue_optics_products($message, $limit = 5) {
    if (!function_exists('farmapaz_optica_map') || !function_exists('wc_get_product')) return [];

    $msg = remove_accents(mb_strtolower(trim((string) $message)));
    $map = farmapaz_optica_map();
    if (empty($map)) return [];

    $matched_brand = '';
    $brands = [];
    foreach ($map as $info) {
        $brand = trim((string) ($info['marca'] ?? ''));
        if ($brand !== '') $brands[$brand] = true;
    }

    foreach (array_keys($brands) as $brand) {
        $norm = remove_accents(mb_strtolower($brand));
        $variants = [$norm];
        if (strpos($norm, '-') !== false) {
            $variants[] = str_replace('-', ' ', $norm);
        }
        foreach ($variants as $variant) {
            if (farmapaz_capiblue_matches($msg, $variant)) {
                $matched_brand = $brand;
                break 2;
            }
        }
    }

    if ($matched_brand === '') return [];

    $codes = [];
    foreach ($map as $code => $info) {
        if (trim((string) ($info['marca'] ?? '')) === $matched_brand) {
            $codes[] = $code;
        }
    }
    if (empty($codes)) return [];

    $query = new WP_Query([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'meta_query'     => [
            ['key' => '_farmapaz_catalog_only', 'value' => '1'],
            ['key' => '_optica_sinv', 'value' => $codes, 'compare' => 'IN'],
        ],
    ]);

    $items = [];
    foreach ($query->posts as $post) {
        $product = wc_get_product($post->ID);
        if (!$product) continue;

        $sinv = (string) get_post_meta($post->ID, '_optica_sinv', true);
        $brand = isset($map[$sinv]['marca']) ? trim((string) $map[$sinv]['marca']) : $matched_brand;
        $name = function_exists('farmapaz_optica_clean_title')
            ? farmapaz_optica_clean_title($post->post_title)
            : $post->post_title;

        $items[] = [
            'name'  => $name,
            'url'   => get_permalink($post->ID),
            'price' => html_entity_decode(strip_tags(wc_price($product->get_price()))),
            'brand' => $brand,
        ];
    }

    wp_reset_postdata();
    return $items;
}

function farmapaz_capiblue_local_reply($message) {
    $msg = mb_strtolower(trim($message));
    $msg = remove_accents($msg);

    // Marcas de óptica: si el usuario menciona una, respondemos con modelos concretos.
    $mentions_optics_brand = false;
    if (function_exists('farmapaz_optica_map')) {
        $optics_brand_names = [];
        foreach (farmapaz_optica_map() as $info) {
            $brand = trim((string) ($info['marca'] ?? ''));
            if ($brand !== '') $optics_brand_names[$brand] = true;
        }
        foreach (array_keys($optics_brand_names) as $ob) {
            $norm = remove_accents(mb_strtolower($ob));
            $variants = [$norm];
            if (strpos($norm, '-') !== false) {
                $variants[] = str_replace('-', ' ', $norm);
            }
            foreach ($variants as $variant) {
                if (farmapaz_capiblue_matches($msg, $variant)) {
                    $mentions_optics_brand = true;
                    break 2;
                }
            }
        }
    }

    // ========== 1. COMPETITOR DETECTION ==========
    $competitors = ['farmatodo', 'farmadon', 'farmatina', 'megafarma', 'locatel', 'farmashop'];
    foreach ($competitors as $c) {
        if (farmapaz_capiblue_matches($msg, $c)) {
            return "Lo siento, solo conozco Farmapaz. Quieres que te ayude con algo de Farmapaz?";
        }
    }

    // ========== 2. ÓPTICA POR MARCA ==========
    if ($mentions_optics_brand) {
        $optics_items = farmapaz_capiblue_optics_products($message, 5);
        if (!empty($optics_items)) {
            $lines = ["En Optica Farmapaz encontre estos modelos:"];
            foreach ($optics_items as $item) {
                $lines[] = "- [" . $item['name'] . "](" . $item['url'] . ") — " . $item['price'];
            }
            $lines[] = "Para disponibilidad y compra escribenos por WhatsApp: " . farmapaz_whatsapp_url('Hola, quiero consultar por una montura de Optica Farmapaz.');
            $lines[] = "Catalogo completo: " . farmapaz_optica_catalogo_url();
            return implode("\n", $lines);
        }
        $mentions_optics_brand = false;
    }

    // ========== 3. GREETINGS ==========
    $greetings = ['hola', 'buenas', 'buenos dias', 'buenas tardes', 'buenas noches', 'que tal', 'hello', 'hi', 'buen dia'];
    foreach ($greetings as $g) {
        if (farmapaz_capiblue_matches($msg, $g)) {
            return "Hola! Soy **CapiBlue**, tu asistente de Farmapaz. Puedo ayudarte a buscar productos, informarte sobre horarios, formas de pago, delivery y mas. En que puedo servirte?";
        }
    }

    // ========== 4. THANKS / FAREWELL ==========
    $farewells = ['gracias', 'adios', 'bye', 'chao', 'hasta luego', 'nos vemos', 'muchas gracias', 'graciass'];
    foreach ($farewells as $f) {
        if (farmapaz_capiblue_matches($msg, $f)) {
            return "De nada! Cuando necesites algo, aqui estoy. Si quieres ver nuestros productos, visita la tienda: " . get_permalink(wc_get_page_id('shop'));
        }
    }

    // ========== 5. HELP ==========
    $help = ['que puedes hacer', 'ayuda', 'funciones', 'comandos', 'menu', 'opciones', 'que haces', 'como funciona'];
    foreach ($help as $h) {
        if (farmapaz_capiblue_matches($msg, $h)) {
            return "Puedo ayudarte con:\n- Buscar productos y medicamentos\n- Optica: monturas, lentes de sol y examen de la vista gratis\n- Cashea: pago en cuotas sin intereses\n- Horarios de atencion (7am - 9:30pm)\n- Formas de pago (transferencia, pago movil, binance, efectivo)\n- Delivery gratis desde $" . farmapaz_free_shipping_min() . "\n- Sucursales en Maturin, Caripito y Punta de Mata\n- Contacto por WhatsApp\n\nQue deseas saber?";
        }
    }

    // ========== 5. BUSINESS INFO ==========
    $intents = [
        'horario' => ['horario', 'horarios', 'hora', 'horas', 'abierto', 'abren', 'cierran', 'atencion', 'atienden', 'cuando abren', 'cuando cierran'],
        'sucursal' => ['sucursal', 'sucursales', 'direccion', 'direcciones', 'ubicacion', 'ubicaciones', 'donde quedan', 'maturin', 'tienda fisica'],
        'optica' => ['optica', 'montura', 'monturas', 'gafas', 'anteojos', 'lentes de sol', 'lentes', 'catalogo de optica'],
        'examen_vista' => ['examen de la vista', 'examen optometrico', 'optometrista', 'consulta optometrica', 'graduacion', 'graduacion visual'],
        'cashea' => ['cashea', 'cuota', 'cuotas', 'financiamiento', 'pagar en cuotas', 'pago en cuotas', 'comprar en cuotas'],
        'pago' => ['pago', 'pagos', 'pagar', 'forma de pago', 'formas de pago', 'transferencia', 'pago movil', 'efectivo', 'binance', 'banco', 'cedula', 'tarjeta', 'divisas', 'dolares'],
        'delivery' => ['delivery', 'envio', 'envian', 'domicilio', 'llevan', 'despacho', 'gratis', 'costo envio', 'cuanto cobran por envio', 'envio nacional'],
        'whatsapp' => ['whatsapp', 'whats', 'wp', 'contacto', 'telefono', 'llamar', 'asesor', 'hablar con', 'contactar', 'atencion al cliente'],
        'instagram' => ['instagram', 'ig', 'redes', 'red social', 'facebook'],
        'como_comprar' => ['como comprar', 'como compro', 'quiero comprar', 'hacer un pedido', 'hacer pedido', 'proceso de compra', 'ordenar'],
    ];

    $responses = [
        'horario' => "En Farmapaz hay sucursales con distintos horarios: algunas son **24 horas** y otras no. Para ver el horario de la sucursal que te interesa (Maturin, Caripito o Punta de Mata), visita nuestra pagina de Sucursales: " . get_permalink(357),
        'sucursal' => "Nuestras sucursales estan en **Maturin, Caripito y Punta de Mata, Estado Monagas**. Para la direccion exacta contactanos por WhatsApp: " . farmapaz_whatsapp_url(),
        'optica' => "En **Optica Farmapaz** tenemos monturas y lentes de sol de marcas como Ray-Ban, Oakley, Prada, Versace, Tom Ford, Guess, Carolina Herrera, Michael Kors y mas. Mira el catalogo aqui: " . farmapaz_optica_catalogo_url() . "\n\nLos productos de optica son solo catalogo: para precio y disponibilidad escribenos por WhatsApp: " . farmapaz_whatsapp_url('Hola, quiero informacion de Optica Farmapaz.'),
        'examen_vista' => "El **examen de la vista es gratis** en nuestras sucursales de Optica Farmapaz. Agenda tu cita por WhatsApp y estrena montura el mismo dia: " . farmapaz_whatsapp_url('Hola, quiero agendar un examen de la vista gratis en Optica Farmapaz.'),
        'cashea' => "Con **Cashea** puedes pagar en cuotas sin intereses. En Optica Farmapaz tenemos la promo **Montura + cristales + examen optometrico**. Disponible en tiendas fisicas; consulta condiciones y disponibilidad por WhatsApp: " . farmapaz_whatsapp_url('Hola, quiero informacion de la promo Cashea en Optica Farmapaz.') . "\n\nTambien puedes pagar con Cashea tus compras de farmacia en tiendas fisicas.",
        'pago' => "Aceptamos **transferencia bancaria, pago movil y efectivo** en tienda. Para transferencias necesitamos: numero de cedula, banco, telefono y referencia. Contactanos por nuestro linktree para detalles: " . farmapaz_contact_link(),
        'delivery' => "El delivery en Maturin, Caripito y Punta de Mata es **GRATIS en compras desde $" . farmapaz_free_shipping_min() . "**. En compras menores el costo es de **$" . farmapaz_shipping_fee_amount() . "**. El horario de delivery es de **" . farmapaz_delivery_schedule() . "**. Para envios nacionales consulta por WhatsApp: " . farmapaz_whatsapp_url(),
        'whatsapp' => "Nuestro WhatsApp: **" . farmapaz_whatsapp_number() . "**. Escribenos directamente: " . farmapaz_whatsapp_url(),
        'instagram' => "Siguenos en Instagram: **@farmapazofficial**. Alli publicamos novedades, promociones y mas.",
        'como_comprar' => "Para comprar en Farmapaz:\n1. Busca el producto que necesitas en nuestra tienda online.\n2. Agregalo al carrito.\n3. Ve al carrito y da click en 'Finalizar compra'.\n4. Llena tus datos y selecciona el metodo de pago.\n5. Te contactaremos por WhatsApp para confirmar.\n\nTambien puedes pedir directo por WhatsApp: " . farmapaz_whatsapp_url(),
    ];

    foreach ($intents as $key => $keywords) {
        if ($key === 'optica' && $mentions_optics_brand) {
            continue;
        }
        foreach ($keywords as $kw) {
            if (farmapaz_capiblue_matches($msg, $kw)) {
                return $responses[$key];
            }
        }
    }

    return null; // not handled locally — pass to AI or search
}

// AJAX handler for CapiBlue
add_action('wp_ajax_farmapaz_capiblue', 'farmapaz_capiblue_ajax');
add_action('wp_ajax_nopriv_farmapaz_capiblue', 'farmapaz_capiblue_ajax');
function farmapaz_capiblue_ajax() {
    check_ajax_referer('farmapaz_nonce', 'nonce');

    $message = isset($_POST['message']) ? trim(sanitize_text_field($_POST['message'])) : '';
    if (!$message) {
        wp_send_json(['reply' => 'Hola! Soy CapiBlue, tu asistente de Farmapaz. En que puedo ayudarte?']);
    }

    // Build conversation history from client
    $history = [];
    if (!empty($_POST['history'])) {
        $decoded = json_decode(stripslashes($_POST['history']), true);
        if (is_array($decoded)) {
            $history = $decoded;
        }
    }
    $history_str = farmapaz_capiblue_build_history($history);

    // If CapiBlue is disabled from "Ajustes de Tienda": predetermined replies only.
    if (!farmapaz_capiblue_enabled()) {
        $local = farmapaz_capiblue_local_reply($message);
        if ($local) {
            wp_send_json(['reply' => $local]);
        }
        wp_send_json(['reply' => "Hola! Soy **CapiBlue**, tu asistente de Farmapaz. En este momento solo respondo preguntas frecuentes. Para ver todos nuestros productos visita la tienda: " . get_permalink(wc_get_page_id('shop'))]);
    }

    // 1. Local reply (only competitors, greetings, business info — not products)
    $local = farmapaz_capiblue_local_reply($message);
    if ($local) {
        wp_send_json(['reply' => $local]);
    }

    // 2. Search products using ONLY the current message. Enriching the query
    //    with the previous user message polluted the intent ("quiero tomar algo"
    //    heredaba el "comer" anterior y devolvia snacks). The conversation
    //    history still reaches the AI via $history_str for context.
    $search_query = $message;
    $products = farmapaz_capiblue_search_products($search_query);

    // 2b. If the direct search is weak, let the AI translate the request into
    //     better catalog keywords (covers creative / free-form ways of asking).
    if (count($products) < 3) {
        $ai_terms = farmapaz_capiblue_extract_terms($message);
        if ($ai_terms) {
            $ai_products = farmapaz_capiblue_search_products($ai_terms);
            $merged = [];
            foreach ([$products, $ai_products] as $list) {
                foreach ($list as $p) {
                    $merged[$p['url']] = $p;
                }
            }
            $products = array_slice(array_values($merged), 0, 20);
        }
    }

    // 3. Try AI with real matches as context. If nothing matched, the AI answers
    //    naturally using the store info (categories, brands) instead of unrelated products.
    $reply = farmapaz_capiblue_try_ai($message, $products, $history_str);
    if ($reply) {
        wp_send_json(['reply' => $reply]);
    }

    // 5. Fallback: show products if AI failed but we have them
    if (!empty($products)) {
        $reply = "Encontre estos productos en Farmapaz:\n\n";
        $count = 0;
        foreach ($products as $p) {
            if ($count >= 5) break;
            $reply .= "[{$p['name']}]({$p['url']}) — {$p['price']}";
            if ($p['brand']) $reply .= " ({$p['brand']})";
            if ($p['cat']) $reply .= " — _{$p['cat']}_";
            $reply .= "\n";
            $count++;
        }
        $reply .= "\n\nTe interesa alguno en particular? Contactanos por WhatsApp: " . farmapaz_whatsapp_url();
        wp_send_json(['reply' => $reply]);
    }

    // 6. Final fallback (should rarely reach here)
    wp_send_json(['reply' => "Bienvenido a Farmapaz! Tenemos una gran variedad de productos para ti. Puedes visitar nuestra tienda: " . get_permalink(wc_get_page_id('shop')) . " o consultarme por algun producto en especial. Tambien estamos en WhatsApp: " . farmapaz_whatsapp_url()]);
}

/**
 * Build conversation history string from messages array
 */
function farmapaz_capiblue_build_history($history) {
    if (empty($history)) return '';
    $lines = [];
    foreach ($history as $msg) {
        $role = $msg['role'] === 'user' ? 'Usuario' : 'CapiBlue';
        $text = isset($msg['text']) ? trim($msg['text']) : '';
        if ($text) {
            $lines[] = "$role: $text";
        }
    }
    return implode("\n", $lines);
}

/**
 * Try AI providers in order: Gemini → OpenRouter
 * Passes product context when available for conversational answers.
 */
function farmapaz_capiblue_try_ai($message, $products = [], $history_str = '') {
    $context = '';

    // Prepnd conversation history so AI remembers context
    if ($history_str) {
        $context .= "Historial de la conversacion:\n{$history_str}\n\n";
    }

    if (!empty($products)) {
        $context .= "Productos de Farmapaz disponibles relacionados con la consulta (usa estos enlaces exactos al mencionarlos):\n";
        foreach ($products as $p) {
            $context .= "- [{$p['name']}]({$p['url']}) — {$p['price']}";
            if ($p['cat']) {
                $cat_url = get_term_link($p['cat'], 'product_cat');
                if (!is_wp_error($cat_url)) {
                    $context .= " — Categoría: [{$p['cat']}]({$cat_url})";
                } else {
                    $context .= " — Categoría: {$p['cat']}";
                }
            }
            if ($p['brand']) {
                $brand_url = get_term_link($p['brand'], 'product_brand');
                if (!is_wp_error($brand_url)) {
                    $context .= " — Marca: [{$p['brand']}]({$brand_url})";
                } else {
                    $context .= " — Marca: {$p['brand']}";
                }
            }
            $context .= "\n";
        }
        $context .= "\n";
    }

    $system_info = farmapaz_capiblue_system_prompt();
    $system_content = "Eres CapiBlue, asistente de Farmapaz Venezuela. Responde breve y amable, sin emojis.\n\n";
    $system_content .= $system_info . "\n\n";
    if ($context) {
        $system_content .= $context . "\n";
    }

    return farmapaz_capiblue_ask($system_content, $message, 400);
}

/**
 * Ask the configured AI provider (Gemini → OpenRouter → Groq) a single prompt.
 * Returns the model's text reply, or null if all providers fail.
 */
function farmapaz_capiblue_ask($system_content, $user_message, $max_tokens = 400) {

    // Log de errores de cada provider para diagnosticar fallos en producción
    $last_errors = [];

    // ----- Gemini (gratis, 60 req/min, 1000 req/día) -----
    $gemini_key = get_option(FARMA_CAPIBLUE_OPTION, '');
    if ($gemini_key) {
        $response = wp_remote_post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $gemini_key, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode([
                'system_instruction' => ['parts' => [['text' => $system_content]]],
                'contents' => [['role' => 'user', 'parts' => [['text' => $user_message]]]],
            ]),
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            $last_errors[] = 'Gemini: ' . $response->get_error_message();
        } else {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['error'])) {
                $last_errors[] = 'Gemini HTTP ' . wp_remote_retrieve_response_code($response) . ': ' . (isset($body['error']['message']) ? $body['error']['message'] : wp_remote_retrieve_body($response));
            } elseif (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                $reply = trim($body['candidates'][0]['content']['parts'][0]['text']);
                if ($reply) return $reply;
                $last_errors[] = 'Gemini: respuesta vacia';
            } else {
                $last_errors[] = 'Gemini: respuesta inesperada';
            }
        }
    }

    // ----- OpenRouter (fallback, solo si hay key configurada) -----
    $or_key = trim(get_option(FARMA_OPENROUTER_KEY_OPTION, ''));
    $or_model = trim(get_option(FARMA_OPENROUTER_MODEL_OPTION, 'openai/gpt-4o-mini'));
    if ($or_key) {
        $response = wp_remote_post('https://openrouter.ai/api/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $or_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => json_encode([
                'model'    => $or_model,
                'messages' => [
                    ['role' => 'system', 'content' => $system_content],
                    ['role' => 'user',   'content' => $user_message],
                ],
                'max_tokens' => $max_tokens,
            ]),
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            $last_errors[] = 'OpenRouter: ' . $response->get_error_message();
        } else {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['error'])) {
                $last_errors[] = 'OpenRouter HTTP ' . wp_remote_retrieve_response_code($response) . ': ' . (isset($body['error']['message']) ? $body['error']['message'] : wp_remote_retrieve_body($response));
            } elseif (isset($body['choices'][0]['message']['content'])) {
                $reply = trim($body['choices'][0]['message']['content']);
                if ($reply) return $reply;
                $last_errors[] = 'OpenRouter: respuesta vacia';
            } else {
                $last_errors[] = 'OpenRouter: respuesta inesperada';
            }
        }
    }

    // ----- Groq (fallback final — recomendado, gratis y confiable) -----
    $groq_key = trim(get_option(FARMA_GROQ_KEY_OPTION, ''));
    $groq_model = trim(get_option(FARMA_GROQ_MODEL_OPTION, 'llama-3.3-70b-versatile'));
    if ($groq_key) {
        $response = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $groq_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => json_encode([
                'model'    => $groq_model,
                'messages' => [
                    ['role' => 'system', 'content' => $system_content],
                    ['role' => 'user',   'content' => $user_message],
                ],
                'max_tokens' => $max_tokens,
            ]),
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            $last_errors[] = 'Groq: ' . $response->get_error_message();
        } else {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['error'])) {
                $last_errors[] = 'Groq HTTP ' . wp_remote_retrieve_response_code($response) . ': ' . (isset($body['error']['message']) ? $body['error']['message'] : wp_remote_retrieve_body($response));
            } elseif (isset($body['choices'][0]['message']['content'])) {
                $reply = trim($body['choices'][0]['message']['content']);
                if ($reply) return $reply;
                $last_errors[] = 'Groq: respuesta vacia';
            } else {
                $last_errors[] = 'Groq: respuesta inesperada';
            }
        }
    }

    if (!empty($last_errors)) {
        error_log('CapiBlue AI fallo: ' . implode(' | ', $last_errors));
    }

    return null;
}

/**
 * [†] Ask the AI to translate a creative / free-form user request into concrete
 * catalog search keywords. Returns a search string, or '' on failure.
 */
function farmapaz_capiblue_extract_terms($message) {
    $system = "Eres un buscador del catalogo de la farmacia Farmapaz (Venezuela). El usuario pide productos de forma libre y creativa. Traduce su pedido a 2-5 palabras clave de busqueda en espanol (generico del medicamento, tipo de producto o categoria) que encuentren el producto en el catalogo. Ejemplos:\n"
        . "- 'quiero algo para el dolor de cabeza' -> 'paracetamol dolor cabeza'\n"
        . "- 'tengo tos y mocos' -> 'tos jarabe antigripal'\n"
        . "- 'quiero un snack dulce' -> 'chocolate dulce snack'\n"
        . "- 'salud sexual' -> 'preservativo lubricante'\n"
        . "- 'algo para dormir' -> 'melatonina sueno'\n"
        . "- 'me duele el estomago' -> 'antiacido digestion'\n"
        . "Responde SOLO con las palabras clave separadas por espacios, sin puntuacion ni explicaciones.";
    $reply = farmapaz_capiblue_ask($system, $message, 60);
    if (!$reply) return '';
    $reply = mb_strtolower($reply);
    $reply = preg_replace('/[^a-z0-9áéíóúüñ ]+/i', ' ', $reply);
    $reply = preg_replace('/\s+/', ' ', trim($reply));
    return $reply;
}

/* ============================================================
   [†] SEO — Farmacia + Óptica Farmapaz
   - Títulos con keywords locales
   - Descripciones limpias (compatibles con Yoast SEO)
   - JSON-LD: Pharmacy + sucursales + FAQ + WebSite (+ Optician en /optica/)
   - og:image por defecto 1200x630
   - robots.txt: permite explícitamente a los rastreadores de IA
   - llms.txt enriquecido para asistentes de IA (gana a la versión de Yoast)
   ============================================================ */

// Coordenadas aproximadas por ciudad (para el geo del schema)
function farmapaz_seo_geo($texto) {
    $t = mb_strtolower($texto);
    if (strpos($t, 'caripito') !== false) return ['10.1207', '-63.0868'];
    if (strpos($t, 'punta de mata') !== false) return ['9.6628', '-63.6000'];
    if (strpos($t, 'jusepín') !== false || strpos($t, 'jusepin') !== false) return ['9.7527', '-63.3699'];
    return ['10.0654', '-63.1474']; // Maturín
}

// Convierte el horario de una sucursal a formato de apertura/cierre para el schema
function farmapaz_seo_hours($horario) {
    $t = mb_strtolower(trim((string) $horario));
    if (strpos($t, '24') !== false) {
        return ['opens' => '00:00', 'closes' => '23:59'];
    }
    if (preg_match('/(\d{1,2})(?::(\d{2}))?\s*(am|pm)?\s*[-–—]\s*(\d{1,2})(?::(\d{2}))?\s*(am|pm)?/', $t, $m)) {
        $h1 = (int) $m[1]; $min1 = (int) ($m[2] !== '' ? $m[2] : 0); $ap1 = isset($m[3]) ? $m[3] : '';
        $h2 = (int) $m[4]; $min2 = (int) ($m[5] !== '' ? $m[5] : 0); $ap2 = isset($m[6]) ? $m[6] : '';
        if ($ap1 === 'pm' && $h1 < 12) $h1 += 12;
        if ($ap1 === 'am' && $h1 === 12) $h1 = 0;
        if ($ap2 === 'pm' && $h2 < 12) $h2 += 12;
        if ($ap2 === 'am' && $h2 === 12) $h2 = 0;
        return ['opens' => sprintf('%02d:%02d', $h1, $min1), 'closes' => sprintf('%02d:%02d', $h2, $min2)];
    }
    return ['opens' => '08:00', 'closes' => '20:00'];
}

/**
 * Limpia texto de shortcodes, etiquetas y entidades HTML.
 * (WPBakery/WooCommerce inyectan [vc_row]... en las descripciones de Yoast).
 */
function farmapaz_seo_clean_text($texto) {
    $texto = strip_shortcodes((string) $texto);
    $texto = wp_strip_all_tags($texto);
    $texto = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $texto = preg_replace('/\s+/u', ' ', $texto);
    return trim($texto);
}

/**
 * Descripción útil según el contexto de la página.
 */
function farmapaz_seo_context_desc() {
    if (is_page('optica') || is_page_template('page-optica.php')) {
        return 'Óptica Farmapaz en Maturín: examen de la vista gratis, lentes de contacto y monturas originales (Ray-Ban, Oakley, Gucci, Prada y más). Agenda por WhatsApp y estrena montura el mismo día.';
    }
    if (is_front_page()) {
        return 'Farmapaz, la cadena de farmacias líder del Estado Monagas: 20 sucursales 24 horas en Maturín, Punta de Mata y Caripito. Medicamentos, dermocosmética y delivery por WhatsApp.';
    }
    if (is_page('sucursales')) {
        return 'Sucursales Farmapaz: farmacias 24 horas en Maturín, Punta de Mata y Caripito, Estado Monagas. Direcciones, teléfonos y delivery por WhatsApp al 0412-8798885.';
    }
    if (is_shop()) {
        return 'Tienda online de Farmapaz: medicamentos, dermocosmética, vitaminas y cuidado personal con delivery en Maturín, Punta de Mata y Caripito, Estado Monagas.';
    }
    if (is_product_category()) {
        $term = get_queried_object();
        if (!empty($term->description)) {
            return farmapaz_seo_clean_text($term->description);
        }
        $nombre = is_object($term) && !empty($term->name) ? $term->name : 'salud';
        return 'Productos de ' . $nombre . ' en Farmapaz, farmacia 24 horas en Maturín, Punta de Mata y Caripito, Estado Monagas, con delivery por WhatsApp.';
    }
    if (is_singular()) {
        $post = get_queried_object();
        if (!empty($post->post_excerpt)) {
            return farmapaz_seo_clean_text($post->post_excerpt);
        }
        $texto = farmapaz_seo_clean_text(!empty($post->post_content) ? $post->post_content : '');
        if (mb_strlen($texto) > 40) {
            return mb_substr($texto, 0, 300);
        }
    }
    return 'Farmapaz: farmacia 24 horas en Maturín, Punta de Mata y Caripito, Estado Monagas, Venezuela. Medicamentos, dermocosmética y delivery por WhatsApp.';
}

/**
 * Imagen por defecto 1200x630 para compartir en redes y asistentes de IA.
 */
function farmapaz_seo_og_image() {
    return get_template_directory_uri() . '/assets/images/og-default.webp';
}

// Schema JSON-LD: Pharmacy + sucursales + FAQ (+ Optician en /optica/) + WebSite
add_action('wp_head', 'farmapaz_seo_jsonld', 1);
function farmapaz_seo_jsonld() {
    if (is_admin() || (function_exists('is_checkout') && is_checkout())) return;
    $home = home_url('/');
    $brand = 'Farmapaz';
    $es_optica = is_page('optica') || is_page_template('page-optica.php');

    $sucursales = function_exists('farmapaz_sucursales_activas') ? farmapaz_sucursales_activas() : [];
    $departments = [];
    foreach ($sucursales as $s) {
        $nombre = !empty($s['nombre']) ? (stripos($s['nombre'], 'farmapaz') === 0 ? $s['nombre'] : $brand . ' ' . $s['nombre']) : $brand;
        $dir    = !empty($s['direccion']) ? $s['direccion'] : 'Maturín, Monagas, Venezuela';
        $tel    = !empty($s['telefono']) ? preg_replace('/[^0-9]/', '', $s['telefono']) : '';
        $horas  = farmapaz_seo_hours(!empty($s['horario']) ? $s['horario'] : '24 horas');
        $geo    = farmapaz_seo_geo($nombre . ' ' . $dir);
        $departments[] = [
            '@type'    => 'Pharmacy',
            'name'     => $nombre,
            'image'    => !empty($s['imagen']) ? $s['imagen'] : get_template_directory_uri() . '/assets/images/logo.webp',
            'telephone'=> $tel ? '+58 ' . substr($tel, 0, 3) . ' ' . substr($tel, 3, 3) . ' ' . substr($tel, 6) : '',
            'address'  => ['@type' => 'PostalAddress', 'streetAddress' => $dir, 'addressLocality' => 'Maturín', 'addressRegion' => 'Monagas', 'addressCountry' => 'VE'],
            'geo'      => ['@type' => 'GeoCoordinates', 'latitude' => $geo[0], 'longitude' => $geo[1]],
            'openingHoursSpecification' => [[
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
                'opens' => $horas['opens'],
                'closes' => $horas['closes'],
            ]],
        ];
    }

    $graph = [[
        '@type' => ['Pharmacy', 'DrugStore'],
        '@id'   => $home . '#farmacia',
        'name'  => $brand,
        'url'   => $home,
        'logo'  => get_template_directory_uri() . '/assets/images/logorif.webp',
        'image' => get_template_directory_uri() . '/assets/images/logorif.webp',
        'telephone' => '+58 412 879 8885',
        'priceRange' => '$$',
        'currenciesAccepted' => 'USD, VES',
        'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Maturín', 'addressRegion' => 'Monagas', 'addressCountry' => 'VE'],
        'geo' => ['@type' => 'GeoCoordinates', 'latitude' => '10.0654', 'longitude' => '-63.1474'],
        'areaServed' => ['Maturín', 'Punta de Mata', 'Caripito', 'Monagas', 'Venezuela'],
        'sameAs' => [
            'https://www.instagram.com/Farmapazofficial',
            'https://www.tiktok.com/@farmaciafarmapaz',
            'https://www.facebook.com/farmapaz.lapaz',
            'https://t.me/Farmapazcallcenter',
            'https://linktr.ee/farmaciafarmapaz',
        ],
        'department' => $departments,
    ]];

    // Nodo Optician para la página de óptica (entidad propia en Google/AI)
    if ($es_optica) {
        $graph[] = [
            '@type' => 'Optician',
            '@id' => $home . '/optica/#optica',
            'name' => 'Óptica Farmapaz',
            'url' => $home . '/optica/',
            'image' => get_template_directory_uri() . '/assets/images/optica/optica-logo.png',
            'logo' => get_template_directory_uri() . '/assets/images/optica/optica-logo.png',
            'telephone' => '+58 424 913 9434',
            'priceRange' => '$$',
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Maturín', 'addressRegion' => 'Monagas', 'addressCountry' => 'VE'],
            'geo' => ['@type' => 'GeoCoordinates', 'latitude' => '10.0654', 'longitude' => '-63.1474'],
            'areaServed' => ['Maturín', 'Monagas', 'Venezuela'],
            'parentOrganization' => ['@id' => $home . '#farmacia'],
            'openingHoursSpecification' => [[
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
                'opens' => '00:00',
                'closes' => '23:59',
            ]],
        ];
    }

    // FAQ local (preguntas reales de búsquedas)
    $whatsapp = function_exists('farmapaz_whatsapp_url') ? farmapaz_whatsapp_url() : '#';
    if ($es_optica) {
        $faqs = [
            ['¿Dónde hacerse un examen de la vista en Maturín?', 'En Óptica Farmapaz el examen de la vista es gratis y se agenda por WhatsApp en cualquiera de las sucursales de Maturín, Estado Monagas.'],
            ['¿Cuánto cuesta un examen de la vista en Maturín?', 'En Óptica Farmapaz el examen optométrico no tiene costo. Solo agenda tu cita por WhatsApp y el mismo día puedes estrenar montura.'],
            ['¿Dónde comprar lentes de contacto en Maturín?', 'Óptica Farmapaz tiene lentes de contacto y monturas graduadas en Maturín, Monagas, con atención de optometristas certificados.'],
            ['¿Qué marcas de lentes vende Óptica Farmapaz?', 'Ray-Ban, Oakley, Gucci, Prada, Carolina Herrera, Police, Guess, Alpi, Zeiss y más, originales y con garantía.'],
            ['¿Venden lentes de sol originales en Maturín?', 'Sí. Óptica Farmapaz vende lentes de sol originales Ray-Ban, Oakley, Prada y otras marcas en sus sucursales de Maturín.'],
            ['¿La óptica de Farmapaz atiende 24 horas?', 'Sí, Óptica Farmapaz está disponible en sucursales con atención extendida. Agenda tu examen por WhatsApp al 0424-9139434.'],
        ];
    } else {
        $faqs = [
            ['¿Dónde hay una farmacia en Maturín?', 'Farmapaz tiene múltiples sucursales en Maturín, Monagas, incluidas las de Av. La Paz, Juncal, Luis del Valle, Los Guaritos y Tipuro, muchas con atención 24 horas.'],
            ['¿Hay farmacia 24 horas en Caripito?', 'Sí. Farmapaz Caripito Mercado, en el Mercado Municipal de Caripito, Monagas, atiende las 24 horas.'],
            ['¿Hay farmacia en Punta de Mata?', 'Sí. Farmapaz Punta de Mata está en Av. Bolívar, diagonal al Seniat, Municipio Ezequiel Zamora, Monagas, con atención 24 horas.'],
            ['¿Farmapaz vende medicamentos en línea en Monagas?', 'Sí. Puedes pedir por WhatsApp con delivery gratis desde $' . (function_exists('farmapaz_free_shipping_min') ? farmapaz_free_shipping_min() : 20) . ' en Maturín, Caripito y Punta de Mata: ' . $whatsapp],
            ['¿Cuál es el teléfono del call center de Farmapaz?', 'El call center de Farmapaz está disponible en el 0412-8798885 por WhatsApp y Telegram.'],
            ['¿Dónde comprar cremas vaginales en Venezuela?', 'Farmapaz vende cremas y óvulos vaginales (clotrimazol, miconazol, nistatina, imazol y más) en sus sucursales de Maturín, Caripito y Punta de Mata, con delivery por WhatsApp. Consulta disponibilidad en el 0412-8798885.'],
            ['¿Qué crema sirve para los hongos vaginales?', 'Las cremas vaginales con clotrimazol (Imazol, CanderM, Kimiceg) y miconazol son las más usadas para la candidiasis. En Farmapaz las encuentras en varias presentaciones y concentraciones.'],
            ['¿Hay farmacia 24 horas cerca de mí en Monagas?', 'Farmapaz tiene más de 20 sucursales 24 horas en Maturín, Punta de Mata y Caripito, Estado Monagas. Revisa nuestra página de sucursales para ver la más cercana.'],
        ];
    }
    $graph[] = [
        '@type' => 'FAQPage',
        '@id' => $home . ($es_optica ? '/optica/' : '') . '#faq',
        'mainEntity' => array_map(function ($f) {
            return ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]];
        }, $faqs),
    ];

    // WebSite + SearchAction: refuerza la entidad de marca en Google y asistentes de IA
    $graph[] = [
        '@type' => 'WebSite',
        '@id' => $home . '#website',
        'url' => $home,
        'name' => $brand,
        'inLanguage' => 'es-VE',
        'publisher' => ['@id' => $home . '#farmacia'],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => $home . '?s={search_term_string}&post_type=product',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];

    echo '<script type="application/ld+json">' . wp_json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}

// Títulos con keywords de ubicación
add_filter('pre_get_document_title', function ($title) {
    if (get_query_var('optica_view') === 'catalogo') {
        return 'Catálogo de monturas — Óptica Farmapaz';
    }
    if (is_page('optica') || is_page_template('page-optica.php')) {
        return 'Óptica Farmapaz en Maturín | Examen de la vista gratis, lentes y monturas';
    }
    if (is_front_page()) {
        return 'Farmapaz | Farmacia 24 horas en Maturín, Punta de Mata, Caripito y Monagas';
    }
    if (is_page('sucursales')) {
        return 'Sucursales Farmapaz — Farmacias en Maturín, Punta de Mata y Caripito, Monagas';
    }
    return $title;
}, 20);

// Geo tags + descripción propia (solo si Yoast NO está activo; con Yoast la genera wpseo_metadesc)
add_action('wp_head', function () {
    if (is_front_page() || is_page('sucursales') || is_page('optica') || is_page_template('page-optica.php') || is_shop() || is_product_category()) {
        echo '<meta name="geo.region" content="VE-M">' . "\n";
        echo '<meta name="geo.placename" content="Maturín, Monagas, Venezuela">' . "\n";
    }
    if (!defined('WPSEO_VERSION')) {
        echo '<meta name="description" content="' . esc_attr(farmapaz_seo_context_desc()) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr(wp_get_document_title()) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr(farmapaz_seo_context_desc()) . '">' . "\n";
        echo '<meta property="og:image" content="' . esc_url(farmapaz_seo_og_image()) . '">' . "\n";
        echo '<meta property="og:image:width" content="1200">' . "\n";
        echo '<meta property="og:image:height" content="630">' . "\n";
        echo '<meta property="og:site_name" content="Farmapaz">' . "\n";
        echo '<meta property="og:locale" content="es_VE">' . "\n";
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr(wp_get_document_title()) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr(farmapaz_seo_context_desc()) . '">' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url(farmapaz_seo_og_image()) . '">' . "\n";
    }
}, 2);

// ====== Yoast SEO: repara las descripciones sucias ======
// Cuando la página está construida con WPBakery, Yoast toma el contenido crudo
// ([vc_row]...) como descripción. Aquí lo limpiamos y damos texto útil.

add_filter('wpseo_metadesc', function ($metadesc, $presentation = null) {
    $limpio = farmapaz_seo_clean_text($metadesc);
    return $limpio !== '' ? $limpio : farmapaz_seo_context_desc();
}, 20, 2);

add_filter('wpseo_opengraph_desc', function ($desc, $presentation = null) {
    $limpio = farmapaz_seo_clean_text($desc);
    return $limpio !== '' ? $limpio : farmapaz_seo_context_desc();
}, 20, 2);

add_filter('wpseo_twitter_description', function ($desc, $presentation = null) {
    $limpio = farmapaz_seo_clean_text($desc);
    return $limpio !== '' ? $limpio : farmapaz_seo_context_desc();
}, 20, 2);

add_filter('wpseo_opengraph_image', function ($image, $presentation = null) {
    return $image ? $image : farmapaz_seo_og_image();
}, 20, 2);

add_filter('wpseo_twitter_image', function ($image, $presentation = null) {
    return $image ? $image : farmapaz_seo_og_image();
}, 20, 2);

add_filter('wpseo_opengraph_site_name', function ($name) {
    return 'Farmapaz';
}, 20);

add_filter('wpseo_og_locale', function ($locale) {
    return 'es_VE';
}, 20);

// ====== robots.txt: permite explícitamente a los rastreadores de IA ======
// Se envuelve do_robots para conservar lo que Yoast genera y solo anexar.
add_action('do_robots', function () {
    ob_start();
}, 1);

add_action('do_robots', function () {
    $out = ob_get_clean();
    $ai = "\n# Asistentes y buscadores de IA (permitidos)\n";
    foreach ([
        'GPTBot', 'ChatGPT-User', 'OAI-SearchBot',
        'ClaudeBot', 'Claude-Web', 'anthropic-ai',
        'PerplexityBot', 'Perplexity-User',
        'Google-Extended', 'Applebot-Extended',
        'CCBot', 'meta-externalagent', 'meta-externalfetcher',
        'cohere-ai', 'Amazonbot', 'Bytespider',
    ] as $bot) {
        $ai .= "User-agent: {$bot}\nAllow: /\n\n";
    }
    echo rtrim($out, "\n") . "\n" . rtrim($ai, "\n");
}, 99999);

// llms.txt — estándar para asistentes de IA (ChatGPT, Gemini, Claude, DeepSeek...)
// Se registra en init para ganarle a la versión genérica de Yoast SEO.
add_action('init', function () {
    if (isset($_SERVER['REQUEST_URI']) && rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') === '/llms.txt') {
        nocache_headers();
        header('Content-Type: text/plain; charset=utf-8');
        echo farmapaz_llms_txt();
        exit;
    }
}, 1);

function farmapaz_llms_txt() {
    $home = home_url('/');
    $sucursales = function_exists('farmapaz_sucursales_activas') ? farmapaz_sucursales_activas() : [];
    $out = [];
    $out[] = '# Farmapaz';
    $out[] = '';
    $out[] = '> Farmapaz es la cadena de farmacias líder del Estado Monagas (Venezuela), con ' . count($sucursales) . ' sucursales 24 horas en Maturín, Punta de Mata y Caripito. Vende medicamentos, dermocosmética, vitaminas y productos de cuidado personal, con delivery por WhatsApp y pedidos en línea. También opera Óptica Farmapaz: examen de la vista gratis, lentes de contacto y monturas originales.';
    $out[] = '';
    $out[] = '## Datos clave';
    $out[] = '- Nombre legal/marca: Farmapaz';
    $out[] = '- Tipo: cadena de farmacias (retail farmacéutico)';
    $out[] = '- Área de servicio: Maturín, Punta de Mata, Caripito y el Estado Monagas (Venezuela)';
    $out[] = '- Teléfono / WhatsApp / Telegram (call center): 0412-8798885';
    $out[] = '- Horario: la mayoría de las sucursales atienden las 24 horas';
    $out[] = '- Delivery: gratis desde $' . (function_exists('farmapaz_free_shipping_min') ? farmapaz_free_shipping_min() : 20) . ' de compra en Maturín, Caripito y Punta de Mata';
    $out[] = '- Monedas aceptadas: USD y VES';
    $out[] = '- Medicamentos controlados: se despachan en tienda con récipe médico (no se venden en línea)';
    $out[] = '- Redes: Instagram @Farmapazofficial, TikTok @farmaciafarmapaz, Facebook farmapaz.lapaz, Telegram t.me/Farmapazcallcenter';
    $out[] = '';
    $out[] = '## Óptica Farmapaz';
    $out[] = '- Página: ' . home_url('/optica/');
    $out[] = '- Servicios: examen de la vista gratis, fórmula de lentes, lentes de contacto, monturas graduadas y de sol';
    $out[] = '- Marcas: Ray-Ban, Oakley, Gucci, Prada, Carolina Herrera, Police, Guess, Alpi, Zeiss y más';
    $out[] = '- WhatsApp: 0424-9139434';
    $out[] = '';
    $out[] = '## Sucursales';
    foreach ($sucursales as $s) {
        $out[] = '- ' . trim($s['nombre'] ?: 'Farmapaz') . ': ' . ($s['direccion'] ?: 'Maturín, Monagas') . ' — ' . ($s['horario'] ?: '24 horas') . ' — Tel. ' . $s['telefono'];
    }
    $out[] = '';
    $out[] = '## Páginas';
    $out[] = '- Inicio: ' . $home . ' (información general y ofertas)';
    $out[] = '- Tienda: ' . home_url('/tienda/') . ' (catálogo de productos con precios)';
    $out[] = '- Sucursales: ' . home_url('/sucursales/') . ' (direcciones y horarios de todas las farmacias)';
    $out[] = '- Óptica: ' . home_url('/optica/') . ' (examen de la vista gratis y monturas)';
    if (function_exists('farmapaz_contact_link')) {
        $out[] = '- Contacto: ' . farmapaz_contact_link();
    }
    $out[] = '';
    $out[] = '## Preguntas frecuentes';
    $out[] = 'P: ¿Dónde hay farmacia en Maturín? R: Farmapaz tiene sucursales 24 horas en Av. La Paz, Juncal, Luis del Valle, Los Guaritos, Tipuro y más.';
    $out[] = 'P: ¿Hay farmacia 24 horas en Caripito? R: Sí, Farmapaz Caripito Mercado, en el Mercado Municipal de Caripito.';
    $out[] = 'P: ¿Hay farmacia en Punta de Mata? R: Sí, Farmapaz Punta de Mata en Av. Bolívar, diagonal al Seniat.';
    $out[] = 'P: ¿Hacen delivery? R: Sí, por WhatsApp al 0412-8798885, gratis desde $' . (function_exists('farmapaz_free_shipping_min') ? farmapaz_free_shipping_min() : 20) . ' en Maturín, Caripito y Punta de Mata.';
    $out[] = 'P: ¿Venden medicamentos controlados? R: Solo en tienda con récipe médico vigente.';
    $out[] = 'P: ¿Dónde hacerse un examen de la vista en Maturín? R: En Óptica Farmapaz el examen es gratis; se agenda por WhatsApp al 0424-9139434.';
    $out[] = 'P: ¿Qué marcas de lentes vende Óptica Farmapaz? R: Ray-Ban, Oakley, Gucci, Prada, Carolina Herrera, Police, Guess, Alpi, Zeiss y más.';
    $out[] = '';
    $out[] = 'Sitio: ' . $home;
    return implode("\n", $out);
}
