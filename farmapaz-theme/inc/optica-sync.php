<?php
/**
 * Óptica — Sincronización del catálogo desde ProteoERP hacia WooCommerce
 *
 * [†] El catálogo de óptica vive en ProteoERP (BD "optica", endpoint api.php).
 * Este módulo:
 *  - Importa los productos VINCULADOS (mapa en inc/optica-map.json) con sus fotos
 *    (1.webp principal, 2.webp/3.webp/... galería de ángulos).
 *  - Los crea como productos WooCommerce "solo catálogo" (sin compra en línea,
 *    sin stock visible; precio en USD, el plugin multi-moneda convierte a VES).
 *  - Marca en la BD de óptica cuáles se enviaron (api/marcar_enviados).
 *
 * Ajustes en: Ajustes de Tienda > Óptica (URL de la API + Token + botón Importar).
 */

if (!defined('ABSPATH')) exit;

define('FARMA_OPTICA_URL_OPTION', 'farmapaz_optica_api_url');
define('FARMA_OPTICA_TOKEN_OPTION', 'farmapaz_optica_token');
define('FARMA_OPTICA_LAST_RUN_OPTION', 'farmapaz_optica_last_run');

function farmapaz_optica_api_url() {
    return get_option(FARMA_OPTICA_URL_OPTION, 'http://172.17.0.1/optica/index.php/api/');
}

function farmapaz_optica_token() {
    return get_option(FARMA_OPTICA_TOKEN_OPTION, 'e5d9d4f864f36e868aa0dbd0');
}

function farmapaz_optica_productos_dir() {
    return get_template_directory() . '/assets/optica-catalogo';
}

function farmapaz_optica_map() {
    static $cache = null;
    if ($cache !== null) return $cache;

    $file = get_template_directory() . '/inc/optica-map.json';
    if (!file_exists($file)) {
        $cache = [];
        return $cache;
    }
    $json = json_decode((string)file_get_contents($file), true);
    $cache = is_array($json) ? $json : [];
    return $cache;
}

/**
 * Limpia títulos importados de ProteoERP.
 * Algunos registros traen "NULL" como descripción secundaria y termina
 * pegado al nombre del producto (ej. "ALPI 1643 NULL").
 */
function farmapaz_optica_clean_title($title) {
    $title = preg_replace('/\s+NULL\s*$/i', '', (string) $title);
    $title = trim(preg_replace('/\s+/', ' ', (string) $title));
    return $title !== '' ? $title : trim((string) $title);
}

// Llamada a la API de óptica (ProteoERP). Siempre POST JSON con token.
function farmapaz_optica_api_call($endpoint, $data = []) {
    $url = rtrim(farmapaz_optica_api_url(), '/') . '/' . ltrim($endpoint, '/');
    $data['token'] = farmapaz_optica_token();
    $resp = wp_remote_post($url, [
        'timeout' => 60,
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => wp_json_encode($data),
    ]);
    if (is_wp_error($resp)) {
        return ['result' => false, 'message' => $resp->get_error_message(), 'data' => null];
    }
    $body = wp_remote_retrieve_body($resp);
    $json = json_decode($body, true);
    if (is_array($json)) return $json;
    return ['result' => false, 'message' => 'Respuesta inválida de la API: ' . substr(strip_tags($body), 0, 300), 'data' => null];
}

// Garantiza la categoría única "Lentes Óptica" y devuelve su ID
function farmapaz_optica_categoria_padre() {
    $slug = 'optica-tienda';
    $term = get_term_by('slug', $slug, 'product_cat');
    if ($term && !is_wp_error($term)) {
        if ($term->name !== 'Lentes Óptica') {
            wp_update_term($term->term_id, 'product_cat', ['name' => 'Lentes Óptica']);
        }
        return (int)$term->term_id;
    }
    $res = wp_insert_term('Lentes Óptica', 'product_cat', ['slug' => $slug]);
    if (is_wp_error($res)) return 0;
    return (int)$res['term_id'];
}

// ¿El producto es "solo catálogo" (óptica, sin compra en línea)?
function farmapaz_optica_es_catalogo($product_id) {
    return get_post_meta((int)$product_id, '_farmapaz_catalog_only', true) === '1';
}

// Oculta el "NULL" heredado de ProteoERP también en la ficha del producto.
add_filter('the_title', function ($title, $post_id = 0) {
    if (!$post_id || get_post_type($post_id) !== 'product') return $title;
    if (!farmapaz_optica_es_catalogo($post_id)) return $title;
    return farmapaz_optica_clean_title($title);
}, 10, 2);

// Y en el <title> del navegador / resultados de búsqueda.
add_filter('document_title_parts', function ($parts) {
    if (!is_singular('product')) return $parts;
    $post_id = get_queried_object_id();
    if ($post_id && farmapaz_optica_es_catalogo($post_id) && isset($parts['title'])) {
        $parts['title'] = farmapaz_optica_clean_title($parts['title']);
    }
    return $parts;
});

// [†] ¿Está activo el módulo de Óptica? (existe la categoría del catálogo)
function farmapaz_optica_enabled() {
    return (bool) get_term_by('slug', 'optica-tienda', 'product_cat');
}

// [†] Cláusula meta para EXCLUIR el catálogo de óptica de las consultas de
// farmacia (tienda, búsqueda, carruseles del home). El catálogo de óptica
// solo se muestra en la sección /optica/.
function farmapaz_optica_meta_exclude() {
    return ['key' => '_farmapaz_catalog_only', 'compare' => 'NOT EXISTS'];
}

// Los productos solo-catálogo no se pueden comprar en línea
add_filter('woocommerce_is_purchasable', function ($purchasable, $product) {
    if ($product && farmapaz_optica_es_catalogo($product->get_id())) {
        return false;
    }
    return $purchasable;
}, 30, 2);

/**
 * Importación principal.
 * Devuelve ['ok'=>bool, 'message'=>string, 'detalle'=>array]
 */
function farmapaz_optica_run_import() {
    if (!function_exists('WC')) {
        return ['ok' => false, 'message' => 'WooCommerce no está activo.'];
    }
    @set_time_limit(0);

    $map = farmapaz_optica_map();
    if (empty($map)) {
        return ['ok' => false, 'message' => 'No existe el mapa de productos (inc/optica-map.json).'];
    }

    $api = farmapaz_optica_api_call('productos');
    if (empty($api['result']) || !isset($api['productos']) || !is_array($api['productos'])) {
        return ['ok' => false, 'message' => 'No se pudo leer la API de óptica: ' . ($api['message'] ?? 'sin respuesta')];
    }

    $por_codigo = [];
    foreach ($api['productos'] as $p) {
        if (!empty($p['codigo'])) {
            $por_codigo[$p['codigo']] = $p;
        }
    }

    // Categoría base
    $cat_padre = farmapaz_optica_categoria_padre();
    if (!$cat_padre) {
        return ['ok' => false, 'message' => 'No se pudo crear la categoría Óptica.'];
    }

    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $dir_img  = farmapaz_optica_productos_dir();
    $nuevos = $actualizados = $fotos = 0;
    $errores = [];
    $importados = [];

    foreach ($map as $codigo => $info) {
        if (!isset($por_codigo[$codigo])) {
            $errores[] = "$codigo: no está en la API";
            continue;
        }
        $p = $por_codigo[$codigo];

        // Título: descrip + descrip2 (si aporta algo nuevo)
        $titulo = farmapaz_optica_clean_title($p['descrip'] ?? $info['descrip'] ?? $codigo);
        $desc2  = trim((string)($p['descrip2'] ?? $info['descrip2'] ?? ''));
        if (strcasecmp($desc2, 'NULL') === 0) $desc2 = '';
        if ($desc2 !== '' && stripos($titulo, $desc2) === false) {
            $titulo .= ' ' . $desc2;
        }
        $titulo = farmapaz_optica_clean_title($titulo);

        $precio = (float)($p['precio_usd'] ?? 0);

        // ¿Ya existe?
        $existente = get_posts([
            'post_type'      => 'product',
            'post_status'    => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_optica_sinv',
            'meta_value'     => $codigo,
        ]);
        $post_id = $existente ? (int)$existente[0] : 0;

        $post_data = [
            'post_title'  => $titulo,
            'post_status' => 'publish',
            'post_type'   => 'product',
        ];
        if ($post_id) {
            $post_data['ID'] = $post_id;
            $post_id = wp_update_post($post_data);
            if (is_wp_error($post_id)) {
                $errores[] = "$codigo: " . $post_id->get_error_message();
                continue;
            }
            $actualizados++;
        } else {
            $post_id = wp_insert_post($post_data);
            if (is_wp_error($post_id)) {
                $errores[] = "$codigo: " . $post_id->get_error_message();
                continue;
            }
            $nuevos++;
        }

        // Datos WooCommerce
        // SKU = código del modelo (el que viene en la descripción, ej. RB5286).
        // El código interno de SINV queda en _optica_sinv para trazabilidad.
        $sku = !empty($info['sku']) ? $info['sku'] : $codigo;
        update_post_meta($post_id, '_sku', $sku);
        update_post_meta($post_id, '_optica_sinv', $codigo);
        update_post_meta($post_id, '_farmapaz_catalog_only', '1');
        update_post_meta($post_id, '_manage_stock', 'no');
        update_post_meta($post_id, '_stock_status', 'instock');
        update_post_meta($post_id, '_visibility', 'visible');
        if ($precio > 0) {
            update_post_meta($post_id, '_regular_price', $precio);
            update_post_meta($post_id, '_price', $precio);
        } else {
            update_post_meta($post_id, '_regular_price', '');
            update_post_meta($post_id, '_price', '');
        }

        // Categoría única: Lentes Óptica (sin subcategorías de marca)
        wp_set_object_terms($post_id, [$cat_padre], 'product_cat', false);

        // Imágenes (solo si aún no tiene foto principal)
        if (!has_post_thumbnail($post_id) && !empty($info['fotos'])) {
            $galeria = [];
            foreach ($info['fotos'] as $i => $foto) {
                $ruta = $dir_img . '/' . $codigo . '/' . $foto;
                if (!file_exists($ruta)) continue;
                // media_handle_sideload MUEVE el archivo tmp_name: copiar a un
                // temporal para no consumir el original (permite re-importar).
                $tmp = wp_tempnam('optica-' . $codigo . '-');
                if (!$tmp) {
                    $errores[] = "$codigo: no se pudo crear archivo temporal";
                    continue;
                }
                copy($ruta, $tmp);
                $file_array = [
                    'name'     => sanitize_file_name($codigo . '-' . $foto),
                    'tmp_name' => $tmp,
                ];
                $att_id = media_handle_sideload($file_array, $post_id, $titulo);
                @unlink($tmp);
                if (is_wp_error($att_id)) {
                    $errores[] = "$codigo: foto $foto: " . $att_id->get_error_message();
                    continue;
                }
                if ($i === 0) {
                    set_post_thumbnail($post_id, $att_id);
                } else {
                    $galeria[] = $att_id;
                }
                $fotos++;
            }
            if (!empty($galeria)) {
                update_post_meta($post_id, '_product_image_gallery', implode(',', $galeria));
            }
        }

        $importados[] = $codigo;
        wc_delete_product_transients($post_id);
    }

    // Marcar como enviados en la BD de óptica
    $marcados = 0;
    if (!empty($importados)) {
        $resp = farmapaz_optica_api_call('marcar_enviados', ['codigos' => $importados]);
        if (!empty($resp['result'])) {
            $marcados = (int)($resp['marcados'] ?? count($importados));
        }
    }

    $resumen = [
        'ok'         => true,
        'message'    => sprintf(
            'Importación completada: %d nuevos, %d actualizados, %d fotos cargadas, %d marcados en Proteo%s.',
            $nuevos, $actualizados, $fotos, $marcados,
            $errores ? (' — ' . count($errores) . ' avisos.') : ''
        ),
        'detalle'    => [
            'nuevos'        => $nuevos,
            'actualizados'  => $actualizados,
            'fotos'         => $fotos,
            'marcados'      => $marcados,
            'errores'       => $errores,
        ],
    ];
    update_option(FARMA_OPTICA_LAST_RUN_OPTION, $resumen);
    return $resumen;
}

/* ------------------------------------------------------------------
 * Utilidades compartidas por la página principal y el catálogo
 * ------------------------------------------------------------------ */

// URL del catálogo independiente (rewrite /optica/catalogo/)
function farmapaz_optica_catalogo_url() {
    return home_url('/optica/catalogo/');
}

// Logos de marcas disponibles en /assets/images/optica/brands/
function farmapaz_optica_brand_logos() {
    return [
        'ALPI'             => 'alpi.webp',
        'CAROLINA HERRERA' => 'carolina-herrera.webp',
        'FERRAGAMO'        => 'ferragamo.webp',
        'FILA'             => 'fila.webp',
        'GANT'             => 'gant.webp',
        'GIO'              => 'gio.webp',
        'GUESS'            => 'guess.webp',
        'JUST CAVALLI'     => 'just-cavalli.webp',
        'LOUIS VUITTON'    => 'louis-vuitton.webp',
        'MAXMARA'          => 'maxmara.webp',
        'MICHAEL KORS'     => 'michael-kors.webp',
        'OAKLEY'           => 'oakley.webp',
        'PALAZZO'          => 'palazzo.webp',
        'PHILIPP PLEIN'    => 'philipp-plein.webp',
        'POLICE'           => 'police.webp',
        'PRADA'            => 'prada.webp',
        'RAY-BAN'          => 'ray-ban.webp',
        'ROBERTO CAVALLI'  => 'roberto-cavalli.webp',
        'TIMBERLAND'       => 'timberland.webp',
        'TOM FORD'         => 'tom-ford.webp',
        'VENETO'           => 'veneto.webp',
        'VERSACE'          => 'versace.webp',
        'ZEISS'            => 'zeiss.webp',
    ];
}

/**
 * IDs, total y conteo por marca de todo el catálogo de óptica.
 * Se cachea en memoria para no repetir la consulta.
 */
function farmapaz_optica_brand_counts() {
    static $cache = null;
    if ($cache !== null) return $cache;

    $query = new WP_Query([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => [['key' => '_farmapaz_catalog_only', 'value' => '1']],
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);

    $ids = array_map('intval', $query->posts);
    if (!empty($ids)) update_meta_cache('post', $ids);

    $map = farmapaz_optica_map();
    $brands = [];
    foreach ($ids as $id) {
        $sinv = (string) get_post_meta($id, '_optica_sinv', true);
        $brand = isset($map[$sinv]['marca']) ? trim((string) $map[$sinv]['marca']) : '';
        if ($brand === '') $brand = 'Óptica';
        if (!isset($brands[$brand])) $brands[$brand] = 0;
        $brands[$brand]++;
    }
    arsort($brands);

    $cache = [
        'ids'    => $ids,
        'total'  => count($ids),
        'brands' => $brands,
    ];
    return $cache;
}

/**
 * Datos de una montura para las tarjetas y carruseles.
 */
function farmapaz_optica_item_data($post_id) {
    $product = wc_get_product($post_id);
    if (!$product) return [];

    $map = farmapaz_optica_map();
    $sinv = (string) get_post_meta($post_id, '_optica_sinv', true);
    $brand = isset($map[$sinv]['marca']) ? trim((string) $map[$sinv]['marca']) : '';
    if ($brand === '') $brand = 'Óptica';

    $thumb = get_the_post_thumbnail_url($post_id, 'woocommerce_single');
    $gallery = $product->get_gallery_image_ids();
    $alt_img = !empty($gallery) ? wp_get_attachment_image_url($gallery[0], 'woocommerce_single') : '';

    return [
        'id'         => (int) $post_id,
        'sinv'       => $sinv,
        'title'      => farmapaz_optica_clean_title(get_the_title($post_id)),
        'brand'      => $brand,
        'permalink'  => get_permalink($post_id),
        'img'        => $thumb ?: farmapaz_fallback_img_url(),
        'alt_img'    => $alt_img ?: '',
        'has_thumb'  => (bool) $thumb,
        'price_html' => $product->get_price_html(),
    ];
}

/**
 * Detecta una marca de óptica dentro de un texto.
 */
function farmapaz_optica_detect_brand($query) {
    $q = remove_accents(mb_strtolower(trim((string) $query)));
    if ($q === '') return '';

    $brands = [];
    foreach (farmapaz_optica_map() as $info) {
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
            if (preg_match('/\b' . preg_quote($variant, '/') . '\b/', $q)) {
                return $brand;
            }
        }
    }
    return '';
}

// ¿La búsqueda hace referencia a óptica?
function farmapaz_optica_query_is_optics($query) {
    $q = remove_accents(mb_strtolower(trim((string) $query)));
    if ($q === '') return false;
    if (strpos($q, 'examen de la vista') !== false) return true;

    if (farmapaz_optica_detect_brand($q) !== '') return true;

    foreach (['optica', 'lente', 'lentes', 'montura', 'monturas', 'gafa', 'gafas', 'anteojo', 'anteojos', 'optometria', 'optometrico', 'optometrista'] as $word) {
        if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $q)) return true;
    }
    return false;
}

// ¿La búsqueda hace referencia a farmacia?
function farmapaz_optica_query_is_pharmacy($query) {
    $q = remove_accents(mb_strtolower(trim((string) $query)));
    if ($q === '') return false;

    $words = [
        'medicamento', 'medicamentos', 'farmacia', 'pastilla', 'pastillas', 'tableta', 'tabletas',
        'capsula', 'capsulas', 'jarabe', 'crema', 'cremas', 'shampoo', 'champu', 'panal', 'panales',
        'vitamina', 'vitaminas', 'dolor', 'gripe', 'antibiotico', 'antibioticos', 'receta', 'dosis',
        'suero', 'alcohol', 'mascarilla', 'tapaboca', 'desodorante', 'cepillo', 'dental', 'colirio',
        'jeringa', 'tensiometro', 'glucosa', 'colesterol', 'probiotico', 'laxante', 'antialergico',
        'antiinflamatorio', 'analgesico', 'tratamiento', 'salud', 'curita', 'gasa', 'venda',
        'paracetamol', 'acetaminofen', 'ibuprofeno', 'aspirina', 'amoxicilina', 'loratadina',
        'omeprazol', 'diclofenaco', 'naproxeno', 'ketorolaco', 'metamizol', 'cetirizina',
        'losartan', 'metformina', 'insulina', 'salbutamol', 'prednisona', 'dexametasona',
        'clonazepam', 'gabapentin', 'sertralina', 'fluoxetina', 'levotiroxina', 'enalapril',
        'atorvastatina', 'simvastatina', 'warfarina', 'heparina', 'electrolitos', 'suero oral',
        'dermocosmetico', 'dermocosmeticos', 'belleza', 'maquillaje', 'perfume', 'higiene',
        'bebe', 'maternidad', 'toallita', 'toallitas', 'mascota', 'mascotas', 'hogar',
    ];
    foreach ($words as $word) {
        if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $q)) return true;
    }
    return false;
}

/**
 * Resultados de óptica para el autocompletado del buscador.
 */
function farmapaz_optica_suggest_results($search, $limit = 5) {
    $search = trim((string) $search);
    if ($search === '') return [];

    $map = farmapaz_optica_map();
    $matched_brand = farmapaz_optica_detect_brand($search);

    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'meta_query'     => [['key' => '_farmapaz_catalog_only', 'value' => '1']],
    ];

    if ($matched_brand !== '') {
        $codes = [];
        foreach ($map as $code => $info) {
            if (trim((string) ($info['marca'] ?? '')) === $matched_brand) $codes[] = $code;
        }
        if (!empty($codes)) {
            $args['meta_query'][] = ['key' => '_optica_sinv', 'value' => $codes, 'compare' => 'IN'];
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
        }
    } else {
        $args['s'] = $search;
    }

    $query = new WP_Query($args);

    // Si buscan "lentes", "monturas", etc. sin marca, mostrar destacadas.
    if (!$query->have_posts() && $matched_brand === '' && farmapaz_optica_query_is_optics($search)) {
        $codes = [];
        foreach (['00019C', '00006A', '00029A', '00007A'] as $code) {
            if (isset($map[$code])) $codes[] = $code;
        }
        if (!empty($codes)) {
            $query = new WP_Query([
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'meta_query'     => [
                    ['key' => '_farmapaz_catalog_only', 'value' => '1'],
                    ['key' => '_optica_sinv', 'value' => $codes, 'compare' => 'IN'],
                ],
            ]);
        }
    }

    $results = [];
    foreach ($query->posts as $post) {
        $product = wc_get_product($post->ID);
        if (!$product) continue;
        $img = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
        if (!$img) $img = farmapaz_fallback_img_url();
        $results[] = [
            'name'  => farmapaz_optica_clean_title($post->post_title),
            'url'   => get_permalink($post->ID),
            'img'   => $img,
            'price' => wp_strip_all_tags($product->get_price_html()),
            'type'  => 'optica_product',
        ];
    }
    wp_reset_postdata();
    return $results;
}

// ---- Página de administración: Ajustes de Tienda > Óptica ----

add_action('admin_menu', function () {
    add_submenu_page('farmapaz-shop', 'Óptica', 'Óptica', 'manage_options', 'farmapaz-optica', 'farmapaz_optica_admin_page', 30);
});

function farmapaz_optica_admin_page() {
    if (!current_user_can('manage_options')) wp_die('Acceso denegado');

    $aviso = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['farmapaz_optica_form'])) {
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'farmapaz_optica_form')) {
            wp_die('Nonce inválido');
        }
        update_option(FARMA_OPTICA_URL_OPTION, esc_url_raw(trim($_POST['optica_api_url'] ?? '')));
        update_option(FARMA_OPTICA_TOKEN_OPTION, sanitize_text_field(trim($_POST['optica_token'] ?? '')));

        if (isset($_POST['optica_importar'])) {
            $res = farmapaz_optica_run_import();
            $aviso = $res['ok']
                ? '<div class="notice notice-success"><p>' . esc_html($res['message']) . '</p>'
                  . (!empty($res['detalle']['errores'])
                      ? '<p><strong>Avisos:</strong> ' . esc_html(implode(' | ', array_slice($res['detalle']['errores'], 0, 20))) . '</p>'
                      : '')
                  . '</div>'
                : '<div class="notice notice-error"><p>' . esc_html($res['message']) . '</p></div>';
        }

        if (isset($_POST['optica_nuevo_token'])) {
            $res = farmapaz_optica_api_call('nuevo_token');
            if (!empty($res['result']) && !empty($res['token'])) {
                update_option(FARMA_OPTICA_TOKEN_OPTION, sanitize_text_field($res['token']));
                $aviso = '<div class="notice notice-success"><p>Token regenerado y guardado: <code>' . esc_html($res['token']) . '</code></p></div>';
            } else {
                $aviso = '<div class="notice notice-error"><p>No se pudo regenerar el token: ' . esc_html($res['message'] ?? 'sin respuesta') . '</p></div>';
            }
        }
    }

    $last = get_option(FARMA_OPTICA_LAST_RUN_OPTION, null);
    ?>
    <div class="wrap">
        <h1>Óptica — Catálogo desde ProteoERP</h1>
        <?= $aviso; ?>
        <p>Importa a WooCommerce los productos de óptica vinculados (mapa <code>inc/optica-map.json</code>) con su foto principal y galería de ángulos. Los productos quedan como <strong>solo catálogo</strong>: precio en USD (tu plugin multi-moneda lo convierte a VES), sin stock visible y sin compra en línea.</p>

        <?php if (is_array($last) && isset($last['message'])): ?>
        <div class="notice notice-info"><p><strong>Última importación:</strong> <?= esc_html($last['message']); ?></p></div>
        <?php endif; ?>

        <form method="post" style="max-width:720px; margin-top:16px;">
            <?php wp_nonce_field('farmapaz_optica_form'); ?>
            <input type="hidden" name="farmapaz_optica_form" value="1">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="optica_api_url">URL de la API (ProteoERP)</label></th>
                    <td>
                        <input type="url" id="optica_api_url" name="optica_api_url" class="regular-text"
                               value="<?= esc_attr(farmapaz_optica_api_url()); ?>">
                        <p class="description">Ej. local: <code>http://172.17.0.1/optica/index.php/api/</code> — VPS: la URL del servidor de óptica.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="optica_token">Token (APITIENDA)</label></th>
                    <td>
                        <input type="text" id="optica_token" name="optica_token" class="regular-text"
                               value="<?= esc_attr(farmapaz_optica_token()); ?>">
                        <p class="description">El token vive en la BD de óptica (tabla <code>valores</code>, nombre <code>APITIENDA</code>). Si no lo sabes, pégalo aquí y listo.</p>
                    </td>
                </tr>
            </table>
            <p>
                <button type="submit" name="optica_importar" class="button button-primary button-hero">Importar catálogo ahora</button>
                <button type="submit" name="optica_nuevo_token" class="button button-hero">Regenerar token en Proteo</button>
            </p>
            <p class="description">Al importar se llama a <code>api/marcar_enviados</code> y queda la bandera en la BD de óptica (tabla <code>sincrowoo</code>).</p>
        </form>
    </div>
    <?php
}
