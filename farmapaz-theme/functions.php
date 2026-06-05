<?php
/**
 * Farmapaz Theme Functions
 */

define('FARMA_VERSION', '1.0.3');
define('FARMA_GEMINI_KEY', 'AIzaSyDf4t4swmo2UpWaEpAwYUZ44a0IsNpvkGw');

// Include custom nav walker
require_once get_template_directory() . '/inc/nav-walker.php';

// Controlled medications (psychotropic drugs, in-store only)
function farmapaz_is_controlled_product($product_id) {
    return has_term('medicamentos-controlados', 'product_cat', $product_id);
}

add_filter('woocommerce_is_purchasable', function ($purchasable, $product) {
    if ($product && farmapaz_is_controlled_product($product->get_id())) {
        return false;
    }
    return $purchasable;
}, 10, 2);

add_filter('woocommerce_add_to_cart_validation', function ($passed, $product_id) {
    if (farmapaz_is_controlled_product($product_id)) {
        wc_add_notice('Este producto requiere receta médica y solo se vende en nuestras tiendas físicas.', 'error');
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
        wp_enqueue_style('farmapaz-shop', get_template_directory_uri() . '/assets/css/shop.css', ['farmapaz-style'], FARMA_VERSION);
        wp_enqueue_script('farmapaz-shop', get_template_directory_uri() . '/assets/js/shop.js', ['farmapaz-app'], FARMA_VERSION, true);
    }

    if (is_front_page() || is_home()) {
        wp_enqueue_style('farmapaz-shop', get_template_directory_uri() . '/assets/css/shop.css', ['farmapaz-style'], FARMA_VERSION);
    }

    // Pastillín chatbot — always available
    wp_enqueue_script('farmapaz-pastillin', get_template_directory_uri() . '/assets/js/pastillin.js', ['farmapaz-app'], FARMA_VERSION, true);
    // Pastillín needs shop.css styles everywhere
    if (!is_shop() && !is_product_category() && !is_product_tag() && !is_product_taxonomy() && !is_front_page() && !is_home() && !is_cart() && !is_checkout() && !is_account_page()) {
        wp_enqueue_style('farmapaz-shop', get_template_directory_uri() . '/assets/css/shop.css', ['farmapaz-style'], FARMA_VERSION);
    }

    if (is_cart() || is_checkout() || is_account_page()) {
        wp_enqueue_style('farmapaz-shop', get_template_directory_uri() . '/assets/css/shop.css', ['farmapaz-style'], FARMA_VERSION);
        wp_enqueue_script('farmapaz-cart', get_template_directory_uri() . '/assets/js/cart.js', ['farmapaz-app', 'wc-cart'], FARMA_VERSION, true);
    }

    wp_localize_script('farmapaz-app', 'farmapazData', [
        'ajaxUrl'   => admin_url('admin-ajax.php'),
        'themeUri'  => get_template_directory_uri(),
        'homeUrl'   => home_url(),
        'shopUrl'   => get_permalink(wc_get_page_id('shop')),
        'cartUrl'   => wc_get_cart_url(),
        'nonce'     => wp_create_nonce('farmapaz_nonce'),
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
        'before_title'  => '<h3 class="text-lg font-semibold text-[#09146E] mb-4 pb-2 border-b border-gray-100">',
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

add_filter('woocommerce_add_to_cart_fragments', 'farmapaz_cart_fragment');
function farmapaz_cart_fragment($fragments) {
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $fragments['#cart-count'] = '<span id="cart-count" class="absolute -top-1.5 -right-1.5 sm:-top-2 sm:-right-2 bg-brand-orange text-white text-xs font-bold rounded-full w-4 h-4 sm:w-5 sm:h-5 flex items-center justify-center shadow-sm">' . $count . '</span>';
    $fragments['div.cart-totals-rows'] = farmapaz_render_cart_totals_rows();
    $fragments['div.cart-totals-total'] = farmapaz_render_cart_total();
    $fragments['div.cart-items'] = '<div class="cart-items">' . farmapaz_render_cart_items() . '</div>';
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
            $is_free = stripos($fee->name, 'gratis') !== false;
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
            <span><?= wc_price($fee->amount); ?></span>
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
        $fallback = get_template_directory_uri() . '/assets/images/producto.png';
        foreach ($cart->get_cart() as $cart_item_key => $cart_item):
            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
            $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
            if (!$_product || !$_product->exists() || $cart_item['quantity'] < 1 || !apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) continue;
            $subtotal  = apply_filters('woocommerce_cart_item_subtotal', $cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key);
            $permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
            $img_url = farmapaz_product_img_fallback(get_the_post_thumbnail_url($product_id, 'thumbnail'));
    ?>
        <div class="cart-item" data-cart-key="<?= esc_attr($cart_item_key); ?>" data-product-id="<?= esc_attr($product_id); ?>" data-unit-price="<?= esc_attr($_product->get_price()); ?>">
            <button class="cart-item-remove" title="Eliminar" onclick="farmapaz_remove_from_cart('<?= esc_js($cart_item_key); ?>')">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="cart-item-img-wrap">
                <?php if ($permalink): ?><a href="<?= esc_url($permalink); ?>"><?php endif; ?>
                    <img src="<?= esc_url($img_url); ?>" alt="<?= esc_attr($product_name); ?>" class="cart-item-img" onerror="this.onerror=null;this.src='<?= $fallback; ?>'">
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
        ];
        if (isset($texts[$original])) {
            return $texts[$original];
        }
    }
    return $translated;
}, 10, 3);

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
        'billing_cedula' => [
            'label'    => 'Número de cédula',
            'required' => true,
            'class'    => ['form-row-wide'],
            'priority' => 30,
        ],
        'billing_phone_banco' => [
            'label'    => 'Teléfono afiliado al banco',
            'required' => true,
            'class'    => ['form-row-wide'],
            'priority' => 40,
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
        'billing_numero_referencia' => [
            'label'    => 'Número de Referencia',
            'required' => true,
            'class'    => ['form-row-wide'],
            'priority' => 100,
        ],
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

// Save custom checkout fields to order meta
add_action('woocommerce_checkout_update_order_meta', 'farmapaz_save_custom_checkout_fields');
function farmapaz_save_custom_checkout_fields($order_id) {
    $fields = ['billing_cedula', 'billing_phone_banco', 'billing_numero_referencia'];
    foreach ($fields as $field) {
        if (!empty($_POST[$field])) {
            update_post_meta($order_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
    // Handle file upload for capture
    if (isset($_FILES['billing_capture_pago']) && $_FILES['billing_capture_pago']['error'] === UPLOAD_ERR_OK) {
        $upload = wp_handle_upload($_FILES['billing_capture_pago'], ['test_form' => false]);
        if (!isset($upload['error'])) {
            update_post_meta($order_id, '_billing_capture_pago', esc_url($upload['url']));
        }
    }
}

// Display custom fields in admin order details
add_action('woocommerce_admin_order_data_after_billing_address', 'farmapaz_admin_order_custom_fields');
function farmapaz_admin_order_custom_fields($order) {
    $fields = [
        '_billing_cedula'          => 'Cédula',
        '_billing_phone_banco'     => 'Teléfono afiliado al banco',
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

// Validate custom checkout fields
add_action('woocommerce_checkout_process', 'farmapaz_validate_checkout_fields');
function farmapaz_validate_checkout_fields() {
    $required = [
        'billing_cedula'          => 'Número de cédula',
        'billing_phone_banco'     => 'Teléfono afiliado al banco',
        'billing_numero_referencia' => 'Número de Referencia',
    ];
    foreach ($required as $field => $label) {
        if (empty($_POST[$field])) {
            wc_add_notice(sprintf('El campo "%s" es obligatorio.', $label), 'error');
        }
    }
}

// Render Capture del pago file upload after billing fields
add_action('woocommerce_after_checkout_billing_form', 'farmapaz_render_capture_pago_field');
function farmapaz_render_capture_pago_field($checkout) {
    ?>
    <div class="form-row form-row-wide farmapaz-capture-wrap">
        <label for="billing_capture_pago">
            Capture del pago <span class="optional">(opcional)</span>
        </label>
        <div class="farmapaz-capture-input">
            <input type="file" name="billing_capture_pago" id="billing_capture_pago"
                   accept="image/png,image/jpeg,image/jpg,image/webp,.png,.jpg,.jpeg,.webp">
            <div class="farmapaz-capture-btn">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Seleccionar imagen</span>
            </div>
            <span class="farmapaz-capture-name"></span>
        </div>
        <span class="farmapaz-capture-hint">Formatos: PNG, JPG, WEBP</span>
    </div>
    <script>
    document.getElementById('billing_capture_pago')?.addEventListener('change', function() {
        var nameEl = this.closest('.farmapaz-capture-input').querySelector('.farmapaz-capture-name');
        if (this.files && this.files[0]) {
            nameEl.textContent = this.files[0].name;
            nameEl.style.display = 'inline';
        } else {
            nameEl.style.display = 'none';
        }
    });
    </script>
    <?php
}

// "Crear cuenta" checkbox marcado por defecto en checkout
add_filter('woocommerce_create_account_default_checked', '__return_true');

// Remove default empty cart message (we have our own in cart-empty.php)
remove_action('woocommerce_cart_is_empty', 'wc_empty_cart_message', 10);

// Dynamic shipping fee: $2 for orders < $10, free for orders >= $10
add_action('woocommerce_cart_calculate_fees', 'farmapaz_shipping_fee');
function farmapaz_shipping_fee($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    $subtotal = $cart->get_subtotal();
    $fee_name = 'Envío';

    // Remove any existing shipping fee
    $fees = $cart->get_fees();
    foreach ($fees as $key => $fee) {
        if ($fee->name === $fee_name || $fee->name === 'Envío gratis') {
            $cart->remove_fee($key);
        }
    }

    if ($subtotal >= 10) {
        $cart->add_fee('Envío gratis', 0, false);
    } else {
        $cart->add_fee($fee_name, 2, false);
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
add_filter('loop_shop_per_page', fn() => 24);

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

    $meta_query[] = ['key' => '_stock_status', 'value' => 'instock'];
    $meta_query[] = ['key' => '_thumbnail_id', 'compare' => 'EXISTS'];

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
    $card_width = $is_grid ? '' : '230px';
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
                <div class="flex gap-4 sm:gap-5 lg:gap-6 overflow-x-auto pb-6 snap-x snap-mandatory scrollbar-hide scroll-smooth cursor-grab product-carousel-track"
                     data-carousel="<?= $section_id; ?>"
                     style="padding-left: calc((100vw - min(100vw - 2rem, 1280px)) / 2 + 24px); padding-right: calc((100vw - min(100vw - 2rem, 1280px)) / 2 + 24px);">
                <?php endif; ?>
                    <?php
                    $shown = 0;
                    while ($products->have_posts() && $shown < $atts['limit']):
                        $products->the_post();
                        global $product;
                        if (!$product) continue;
                        $image_url = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                        if (!$image_url || strpos($image_url, 'Gemini_Generated') !== false) continue;
                        $stock_qty = $product->get_stock_quantity();
                        $managing = $product->managing_stock();
                        if ($managing && $stock_qty !== null && $stock_qty <= 5) continue;
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
                    <div class="<?= $is_grid ? '' : 'flex-none snap-start '; ?>product-card-group stagger-item" style="<?= $is_grid ? '' : 'width: 230px;'; ?>">
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
                                <?php $prod_price = $product->get_price(); if ($prod_price && (float)$prod_price >= 10): ?>
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
                            <div class="<?= $is_compact ? 'p-2.5' : 'p-4'; ?> flex flex-col flex-1" style="position: relative; z-index: 1;">
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
                                <h3 class="<?= $is_compact ? 'text-xs' : 'text-sm'; ?> font-semibold text-gray-800 leading-snug" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: <?= $is_compact ? '2.2em' : '2.6em'; ?>;">
                                    <a href="<?php the_permalink(); ?>" class="hover:text-brand-green transition-colors">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                <div class="mt-auto <?= $is_compact ? 'pt-2' : 'pt-3'; ?>">
                                    <div class="flex items-baseline gap-2 <?= $is_compact ? 'mb-2' : 'mb-3'; ?>">
                                        <?php if ($has_real_sale): ?>
                                            <span class="<?= $is_compact ? 'text-sm' : 'text-lg'; ?> price-futuristic" style="color: #FF1A27;"><?= wc_price($sale); ?></span>
                                            <span class="text-xs text-gray-400 line-through"><?= wc_price($regular); ?></span>
                                        <?php elseif ($atts['force_sale']): ?>
                                            <span class="<?= $is_compact ? 'text-sm' : 'text-lg'; ?> price-futuristic" style="color: #09146E;"><?= wc_price($regular); ?></span>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background: rgba(254,171,13,0.15); color: #92400e;">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                                                Oferta
                                            </span>
                                        <?php else: ?>
                                            <span class="<?= $is_compact ? 'text-sm' : 'text-lg'; ?> price-futuristic" style="color: #09146E;"><?= $product->get_price_html(); ?></span>
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

// WhatsApp button - shortcode
add_shortcode('farmapaz_whatsapp', 'farmapaz_whatsapp_btn');
function farmapaz_whatsapp_btn() {
    $phone = '584128798885';
    $msg   = urlencode('¡Hola! Solicito información sobre productos de Farmapaz.');
    return sprintf(
        '<a href="https://wa.me/%s?text=%s" target="_blank" rel="noopener" class="whatsapp-float" aria-label="WhatsApp">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        </a>',
        $phone, $msg
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

// Product image fallback
add_filter('woocommerce_placeholder_img_src', 'farmapaz_placeholder_img');
function farmapaz_placeholder_img() {
    return get_template_directory_uri() . '/assets/images/producto.png';
}
function farmapaz_product_img_fallback($url) {
    if (!$url || strpos($url, 'Gemini_Generated') !== false) {
        return get_template_directory_uri() . '/assets/images/producto.png';
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

// Search suggestions REST API
add_action('rest_api_init', function () {
    register_rest_route('farmapaz/v1', '/suggest', [
        'methods'  => 'GET',
        'callback' => 'farmapaz_search_suggest',
        'args'     => ['s' => ['required' => true]],
        'permission_callback' => '__return_true',
    ]);
});

function farmapaz_search_suggest(WP_REST_Request $request) {
    $s = trim(sanitize_text_field($request->get_param('s')));
    if (strlen($s) < 2) return ['results' => []];

    $results = [];

    // Products — fetch more to account for Gemini image filtering
    $product_query = new WP_Query([
        'post_type'      => 'product',
        's'              => $s,
        'posts_per_page' => 20,
        'post_status'    => 'publish',
        'meta_query'     => [
            ['key' => '_stock_status', 'value' => 'instock'],
            ['key' => '_thumbnail_id', 'compare' => 'EXISTS'],
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
        if (!$img || strpos($img, 'Gemini_Generated') !== false) continue;
        $results[] = [
            'name'   => $product->get_name(),
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
            $results[] = [
                'name'  => $cat->name,
                'url'   => get_term_link($cat),
                'img'   => '',
                'price' => $cat->count . ' productos',
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
                'price' => $brand->count . ' productos',
                'type'  => 'brand',
            ];
        }
    }

    return ['results' => $results];
}

// ===================== SHOP PAGE — AJAX FILTERS =====================

// Pre-get-posts: apply URL filters to initial shop query
add_action('pre_get_posts', 'farmapaz_pre_get_products');
function farmapaz_pre_get_products($q) {
    if (!is_admin() && $q->is_main_query() && (is_shop() || is_product_category() || is_product_tag())) {
        // Stock status filter
        $meta_q = $q->get('meta_query');
        if (!is_array($meta_q)) $meta_q = [];
        $meta_q[] = ['key' => '_stock_status', 'value' => 'instock'];
        $meta_q[] = ['key' => '_thumbnail_id', 'compare' => 'EXISTS'];
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
        if ($orderby) {
            switch ($orderby) {
                case 'price':
                    $q->set('orderby', 'meta_value_num');
                    $q->set('meta_key', '_price');
                    $q->set('order', 'ASC');
                    break;
                case 'price-desc':
                    $q->set('orderby', 'meta_value_num');
                    $q->set('meta_key', '_price');
                    $q->set('order', 'DESC');
                    break;
                case 'popularity':
                    $q->set('orderby', 'meta_value_num');
                    $q->set('meta_key', 'total_sales');
                    $q->set('order', 'DESC');
                    break;
            }
        }

        // Pagination from URL
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 0;
        if ($page > 1) {
            $q->set('paged', $page);
        }

        // Products per page
        $q->set('posts_per_page', 24);
    }
}

add_action('wp_ajax_farmapaz_shop_filter', 'farmapaz_shop_filter_ajax');
add_action('wp_ajax_nopriv_farmapaz_shop_filter', 'farmapaz_shop_filter_ajax');
function farmapaz_shop_filter_ajax() {
    check_ajax_referer('farmapaz_nonce', 'nonce');

    $paged = max(1, isset($_POST['page']) ? (int)$_POST['page'] : 1);
    $per_page = 24;

    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        'meta_query'     => [
            ['key' => '_stock_status', 'value' => 'instock'],
            ['key' => '_thumbnail_id', 'compare' => 'EXISTS'],
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
    $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'date';
    switch ($orderby) {
        case 'price':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order'] = 'ASC';
            break;
        case 'price-desc':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order'] = 'DESC';
            break;
        case 'popularity':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'total_sales';
            $args['order'] = 'DESC';
            break;
        case 'rating':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_wc_average_rating';
            $args['order'] = 'DESC';
            break;
        default:
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
    }

    $query = new WP_Query($args);

    ob_start();
    if ($query->have_posts()):
        while ($query->have_posts()): $query->the_post();
            wc_get_template_part('content', 'product');
        endwhile;
    endif;
    $products_html = ob_get_clean();

    ob_start();
    $total_pages = $query->max_num_pages;
    $current_page = $paged;
    farmapaz_shop_pagination_html($total_pages, $current_page);
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
        'has_more'        => $current_page < $total_pages,
        'active_filters'  => $active_filters_html,
        'hero_indicator'  => $hero_indicator_html,
    ]);
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
        <!-- Search -->
        <div class="filter-block">
            <h3 class="filter-title">Buscar</h3>
            <div class="filter-search-wrap">
                <svg class="filter-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" class="filter-input filter-search" placeholder="Buscar en resultados..." value="<?= esc_attr($current_s); ?>">
                <?php if ($current_s): ?>
                    <button class="filter-clear-btn" data-clear="s">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Categories -->
        <div class="filter-block">
            <button class="filter-title filter-toggle" data-target="cat-list">
                Categorías
                <svg class="filter-chevron" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="filter-list" id="cat-list">
                <?php
                $categories = get_terms([
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => true,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ]);
                if (!is_wp_error($categories)):
                    foreach ($categories as $cat):
                        $checked = in_array($cat->slug, $current_cats) ? 'checked' : '';
                ?>
                <label class="filter-option">
                    <input type="checkbox" class="filter-check filter-cat" value="<?= esc_attr($cat->slug); ?>" <?= $checked; ?>>
                    <span class="filter-label-text"><?= esc_html(farmapaz_cat_display_name($cat->slug)); ?></span>
                    <span class="filter-count"><?= $cat->count; ?></span>
                </label>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- Brands -->
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

// ====== PASTILLÍN — CHATBOT IA DE FARMApAZ ======

define('FARMA_PASTILLIN_OPTION', 'farmapaz_gemini_key');

// Admin page for Gemini API key
add_action('admin_menu', function () {
    add_options_page('Pastillín API Key', 'Pastillín AI', 'manage_options', 'pastillin', function () {
        if (!current_user_can('manage_options')) wp_die('Acceso denegado');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gemini_key'])) {
            update_option(FARMA_PASTILLIN_OPTION, sanitize_text_field($_POST['gemini_key']));
            echo '<div class="updated"><p>API Key guardada.</p></div>';
        }
        $key = get_option(FARMA_PASTILLIN_OPTION, '');
        ?>
        <div class="wrap">
            <h1>Pastillín — Gemini API Key</h1>
            <p>Obtén tu API Key gratis en <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a>.</p>
            <form method="post">
                <input type="text" name="gemini_key" value="<?= esc_attr($key); ?>" style="width:400px;max-width:100%;">
                <button type="submit" class="button button-primary">Guardar</button>
            </form>
            <p style="margin-top:20px;color:#666;">Sin API Key, Pastillín funciona en modo básico (búsqueda local de productos).</p>
        </div>
        <?php
    });
});

// Build Pastillín system prompt with live product data
function farmapaz_pastillin_system_prompt() {
    $prompt = "Eres Pastillín, el asistente virtual IA de Farmapaz Venezuela. ";
    $prompt .= "Eres amigable, entusiasta, usas español y hablas en primera persona. ";
    $prompt .= "Tu personalidad: fresco, juvenil, futurista, con estilo Frutiger Aero. ";
    $prompt .= "Usas emojis ocasionales (💊✨🔬🌿) y frases cortas y claras.\n\n";
    $prompt .= "REGLAS ESTRICTAS:\n";
    $prompt .= "- SOLO respondes sobre Farmapaz Venezuela. ";
    $prompt .= "- Si te preguntan por Farmatodo, Farmadon, Farmatina, Locatel o cualquier otra farmacia, ";
    $prompt .= "  dices amablemente: 'Lo siento, solo conozco Farmapaz 🙏 ¿Quieres que te ayude con algo de Farmapaz?'\n";
    $prompt .= "- No inventes precios ni productos que no existan en Farmapaz.\n";
    $prompt .= "- Si no sabes algo, di que lo consultarás y sugiere contactar por WhatsApp.\n";
    $prompt .= "- No des consejos médicos. Si te preguntan sobre síntomas, sugiere consultar a un médico.\n";
    $prompt .= "- Siempre mantén un tono positivo, servicial y futurista.\n\n";

    $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true]);
    if (!empty($cats) && !is_wp_error($cats)) {
        $prompt .= "CATEGORÍAS DISPONIBLES:\n";
        foreach ($cats as $cat) {
            $prompt .= "- {$cat->name} ({$cat->count} productos)\n";
        }
        $prompt .= "\n";
    }

    $prompt .= "ENLACES ÚTILES:\n";
    $prompt .= "- Tienda online: " . get_permalink(wc_get_page_id('shop')) . "\n";
    $prompt .= "- Carrito: " . wc_get_cart_url() . "\n";
    $prompt .= "- WhatsApp: https://wa.me/584128798885\n";
    $prompt .= "- Instagram: @farmapazvzla\n";
    $prompt .= "- Envío gratis en compras desde \$10.\n";
    $prompt .= "- Medicamentos controlados solo en tiendas físicas con receta.\n\n";
    $prompt .= "Sucursales: Pide al usuario que visite la página o contacte por WhatsApp para conocer las sucursales.\n\n";
    $prompt .= "FORMATO DE RESPUESTA:\n";
    $prompt .= "- Respuestas cortas y directas (máximo 3-4 líneas).\n";
    $prompt .= "- Si el usuario pide productos, muéstrale los que tenemos disponibles.\n";
    $prompt .= "- Siempre finaliza ofreciendo ayuda adicional.\n";

    return $prompt;
}

// Search products for context
function farmapaz_pastillin_search_products($query) {
    $products = [];
    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        's'              => $query,
        'meta_query'     => [
            ['key' => '_stock_status', 'value' => 'instock'],
            ['key' => '_thumbnail_id', 'compare' => 'EXISTS'],
        ],
    ];
    $loop = new WP_Query($args);
    if ($loop->have_posts()) {
        while ($loop->have_posts()) {
            $loop->the_post();
            $product = wc_get_product(get_the_ID());
            if ($product) {
                $cats = wp_get_post_terms(get_the_ID(), 'product_cat', ['fields' => 'names']);
                $products[] = [
                    'name'   => $product->get_name(),
                    'price'  => wc_price($product->get_price()),
                    'cat'    => !empty($cats) ? $cats[0] : '',
                    'url'    => get_permalink(),
                    'stock'  => $product->is_in_stock() ? 'Disponible' : 'Agotado',
                ];
            }
        }
    }
    wp_reset_postdata();
    return $products;
}

// AJAX handler for Pastillín
add_action('wp_ajax_farmapaz_pastillin', 'farmapaz_pastillin_ajax');
add_action('wp_ajax_nopriv_farmapaz_pastillin', 'farmapaz_pastillin_ajax');
function farmapaz_pastillin_ajax() {
    $message = isset($_POST['message']) ? trim(sanitize_text_field($_POST['message'])) : '';
    if (!$message) {
        wp_send_json(['reply' => '¡Hola! Soy Pastillín 💊 ¿En qué puedo ayudarte hoy?']);
    }

    $api_key = get_option(FARMA_PASTILLIN_OPTION, '');
    if (!$api_key && defined('FARMA_GEMINI_KEY')) {
        $api_key = FARMA_GEMINI_KEY;
    }
    $products = farmapaz_pastillin_search_products($message);

    if ($api_key) {
        $system_prompt = farmapaz_pastillin_system_prompt();
        $context = '';
        if (!empty($products)) {
            $context = "Productos de Farmapaz relacionados:\n";
            foreach ($products as $p) {
                $context .= "- {$p['name']} — {$p['price']} — {$p['cat']} — {$p['stock']}\n";
            }
            $context .= "\n";
        }

        $contents = [
            ['role' => 'user', 'parts' => [['text' => $system_prompt . "\n\n" . $context . "Usuario: " . $message]]],
        ];

        $response = wp_remote_post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $api_key, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode(['contents' => $contents]),
            'timeout' => 15,
        ]);

        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $reply = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if ($reply) {
                wp_send_json(['reply' => $reply]);
            }
        }
    }

    // Fallback: local response
    if (!empty($products)) {
        $reply = "¡Claro! En Farmapaz tenemos:\n";
        $count = 0;
        foreach ($products as $p) {
            if ($count >= 5) break;
            $reply .= "🔹 {$p['name']} — {$p['price']}\n";
            $count++;
        }
        $reply .= "\n¿Quieres ver más detalles de alguno? 😊";
        wp_send_json(['reply' => $reply]);
    }

    wp_send_json(['reply' => "No encontré \"{$message}\" en Farmapaz. ¿Pruebas con otra palabra o prefieres hablar con un asesor por WhatsApp? 🙏"]);
}
