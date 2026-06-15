<?php
/**
 * Plugin Name: Farmapaz Fidelidad
 * Description: Reporte de clientes fieles — muestra que productos compran recurrentemente mes a mes
 * Version:     1.1.0
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) exit;

define('FARMA_FID_PLUGIN_URL', plugin_dir_url(__FILE__));

// ── Detect HPOS ──
function farmapaz_fid_is_hpos() {
    return get_option('woocommerce_custom_orders_table_enabled') === 'yes';
}

// ── Build FROM/JOIN for orders (HPOS or legacy) ──
function farmapaz_fid_orders_from(&$wpdb, &$prefix, &$order_alias, &$join_email, &$date_col, &$id_col) {
    if (farmapaz_fid_is_hpos()) {
        $order_alias = 'o';
        $join_email  = ''; // billing_email is directly on wc_orders
        $date_col    = "{$order_alias}.date_created_gmt";
        $id_col      = "{$order_alias}.id";
        return "{$prefix}wc_orders {$order_alias}";
    } else {
        $order_alias = 'p';
        $join_email  = "INNER JOIN {$wpdb->postmeta} email ON {$order_alias}.ID = email.post_id AND email.meta_key = '_billing_email'";
        $date_col    = "{$order_alias}.post_date";
        $id_col      = "{$order_alias}.ID";
        return "{$wpdb->posts} {$order_alias}";
    }
}

function farmapaz_fid_orders_where($order_alias) {
    if (farmapaz_fid_is_hpos()) {
        return "{$order_alias}.type = 'shop_order' AND {$order_alias}.status IN ('wc-completed','wc-processing') AND {$order_alias}.billing_email != ''";
    } else {
        return "{$order_alias}.post_type = 'shop_order' AND {$order_alias}.post_status IN ('wc-completed','wc-processing') AND email.meta_value != ''";
    }
}

function farmapaz_fid_email_col($order_alias) {
    return farmapaz_fid_is_hpos() ? "{$order_alias}.billing_email" : 'email.meta_value';
}

// ── Admin menu ──
add_action('admin_menu', function () {
    add_submenu_page(
        'woocommerce',
        'Fidelidad de Clientes',
        'Fidelidad',
        'manage_woocommerce',
        'farmapaz-fidelidad',
        'farmapaz_fidelidad_render'
    );
});

function farmapaz_fidelidad_render() {
    global $wpdb;

    $min_months = isset($_GET['min_months']) ? max(1, (int) $_GET['min_months']) : 2;
    $search     = isset($_GET['s']) ? trim(sanitize_text_field($_GET['s'])) : '';
    $orderby    = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'months_active';
    $order      = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';

    $page     = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
    $per_page = 25;
    $offset   = ($page - 1) * $per_page;

    $prefix = $wpdb->prefix;

    // Build query parts
    list($o_from, $o_alias, $o_join_email, $o_date, $o_id) = ['', '', '', '', ''];
    $o_from = farmapaz_fid_orders_from($wpdb, $prefix, $o_alias, $o_join_email, $o_date, $o_id);
    $o_where = farmapaz_fid_orders_where($o_alias);
    $o_email = farmapaz_fid_email_col($o_alias);

    $where = $o_where;
    if ($search) {
        $like  = '%' . $wpdb->esc_like($search) . '%';
        if (farmapaz_fid_is_hpos()) {
            $where .= $wpdb->prepare(" AND ({$o_alias}.billing_email LIKE %s OR oi.order_item_name LIKE %s)", $like, $like);
        } else {
            $where .= $wpdb->prepare(" AND (email.meta_value LIKE %s OR oi.order_item_name LIKE %s)", $like, $like);
        }
    }

    $allowed_order = ['months_active', 'customer_email', 'product_name', 'first_purchase', 'last_purchase', 'total_orders'];
    $order_col     = in_array($orderby, $allowed_order) ? $orderby : 'months_active';
    $order_sql     = $order_col === 'customer_email' ? "customer_email $order" : "$order_col $order";

    // ── Total rows ──
    $total = $wpdb->get_var("
        SELECT COUNT(*) FROM (
            SELECT {$o_email} AS customer_email
            FROM {$o_from}
            INNER JOIN {$prefix}woocommerce_order_items oi ON {$o_id} = oi.order_id AND oi.order_item_type = 'line_item'
            INNER JOIN {$prefix}woocommerce_order_itemmeta pid ON oi.order_item_id = pid.order_item_id AND pid.meta_key = '_product_id'
            {$o_join_email}
            WHERE {$where}
            GROUP BY {$o_email}, pid.meta_value
            HAVING COUNT(DISTINCT DATE_FORMAT({$o_date}, '%Y-%m')) >= {$min_months}
        ) t
    ");

    // ── Page results ──
    $rows = $wpdb->get_results("
        SELECT
            {$o_email} AS customer_email,
            pid.meta_value AS product_id,
            MAX(oi.order_item_name) AS product_name,
            COUNT(DISTINCT DATE_FORMAT({$o_date}, '%Y-%m')) AS months_active,
            COUNT(DISTINCT {$o_id}) AS total_orders,
            MIN({$o_date}) AS first_purchase,
            MAX({$o_date}) AS last_purchase
        FROM {$o_from}
        INNER JOIN {$prefix}woocommerce_order_items oi ON {$o_id} = oi.order_id AND oi.order_item_type = 'line_item'
        INNER JOIN {$prefix}woocommerce_order_itemmeta pid ON oi.order_item_id = pid.order_item_id AND pid.meta_key = '_product_id'
        {$o_join_email}
        WHERE {$where}
        GROUP BY customer_email, product_id
        HAVING months_active >= {$min_months}
        ORDER BY {$order_sql}
        LIMIT {$per_page} OFFSET {$offset}
    ");

    // ── Fetch purchase months for streak calculation ──
    $streak_data = [];
    if (!empty($rows)) {
        $conditions = [];
        foreach ($rows as $r) {
            $email_esc = $wpdb->prepare('%s', $r->customer_email);
            $pid_esc   = (int) $r->product_id;
            $cond = farmapaz_fid_is_hpos()
                ? "({$o_alias}.billing_email = {$email_esc} AND pid.meta_value = {$pid_esc})"
                : "(email.meta_value = {$email_esc} AND pid.meta_value = {$pid_esc})";
            $conditions[] = $cond;
        }
        $streak_rows = $wpdb->get_results("
            SELECT
                {$o_email} AS customer_email,
                pid.meta_value AS product_id,
                DATE_FORMAT({$o_date}, '%Y-%m') AS purchase_month
            FROM {$o_from}
            INNER JOIN {$prefix}woocommerce_order_items oi ON {$o_id} = oi.order_id AND oi.order_item_type = 'line_item'
            INNER JOIN {$prefix}woocommerce_order_itemmeta pid ON oi.order_item_id = pid.order_item_id AND pid.meta_key = '_product_id'
            {$o_join_email}
            WHERE (" . implode(' OR ', $conditions) . ")
            ORDER BY {$o_email}, pid.meta_value, {$o_date}
        ");
        foreach ($streak_rows as $sr) {
            $key = $sr->customer_email . '|' . $sr->product_id;
            $streak_data[$key][] = $sr->purchase_month;
        }
    }

    $total_pages = ceil($total / $per_page);

    // ── Sorting helpers ──
    $order_url = function ($col) use ($orderby, $order) {
        $dir = ($orderby === $col && $order === 'DESC') ? 'ASC' : 'DESC';
        return add_query_arg(['orderby' => $col, 'order' => $dir]);
    };
    $arrow = function ($col) use ($orderby, $order) {
        if ($orderby !== $col) return '';
        return $order === 'DESC' ? ' &#9660;' : ' &#9650;';
    };

    ?>
    <div class="wrap">
        <h1>Fidelidad de Clientes</h1>
        <p>Clientes que han comprado el mismo producto en m&uacute;ltiples meses.</p>

        <form method="get" style="margin:1em 0;display:flex;gap:1em;align-items:center;flex-wrap:wrap;">
            <input type="hidden" name="page" value="farmapaz-fidelidad">
            <label>
                M&iacute;n. meses activos:
                <input type="number" name="min_months" value="<?= esc_attr($min_months) ?>" min="1" style="width:70px;">
            </label>
            <label>
                Buscar:
                <input type="text" name="s" value="<?= esc_attr($search) ?>" placeholder="Email o producto">
            </label>
            <button type="submit" class="button">Filtrar</button>
            <a href="?page=farmapaz-fidelidad&min_months=<?= $min_months ?>&s=<?= urlencode($search) ?>&export=csv" class="button button-secondary">Exportar CSV</a>
        </form>

        <p style="color:#666;">Mostrando <?= count($rows) ?> de <?= number_format($total) ?> registros.</p>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><a href="<?= $order_url('customer_email') ?>">Cliente<?= $arrow('customer_email') ?></a></th>
                    <th><a href="<?= $order_url('product_name') ?>">Producto<?= $arrow('product_name') ?></a></th>
                    <th><a href="<?= $order_url('months_active') ?>">Meses Activo<?= $arrow('months_active') ?></a></th>
                    <th>Racha (meses)</th>
                    <th><a href="<?= $order_url('total_orders') ?>">Pedidos<?= $arrow('total_orders') ?></a></th>
                    <th><a href="<?= $order_url('first_purchase') ?>">1ra Compra<?= $arrow('first_purchase') ?></a></th>
                    <th><a href="<?= $order_url('last_purchase') ?>">Ultima Compra<?= $arrow('last_purchase') ?></a></th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="8">No se encontraron clientes fieles con estos filtros.</td></tr>
                <?php endif; ?>

                <?php foreach ($rows as $r):
                    $key = $r->customer_email . '|' . $r->product_id;
                    $months = isset($streak_data[$key]) ? $streak_data[$key] : [];
                    sort($months);
                    $streak = farmapaz_fid_calc_streak($months);
                ?>
                <tr>
                    <td><?= esc_html($r->customer_email) ?></td>
                    <td>
                        <a href="<?= esc_url(get_permalink($r->product_id)) ?>" target="_blank">
                            <?= esc_html(wp_trim_words($r->product_name, 8)) ?>
                        </a>
                    </td>
                    <td><?= (int) $r->months_active ?></td>
                    <td>
                        <span style="font-weight:600;color:<?= $streak >= 3 ? '#2e7d32' : ($streak >= 2 ? '#f57f17' : '#666') ?>">
                            <?= $streak ?>
                        </span>
                        <?php if ($streak >= 3): ?>
                            <span style="color:#2e7d32;">&#9733;</span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $r->total_orders ?></td>
                    <td><?= esc_html(date_i18n(get_option('date_format'), strtotime($r->first_purchase))) ?></td>
                    <td><?= esc_html(date_i18n(get_option('date_format'), strtotime($r->last_purchase))) ?></td>
                    <td>
                        <button type="button" class="button button-small farmapaz-fid-detail"
                            data-email="<?= esc_attr($r->customer_email) ?>"
                            data-product="<?= (int) $r->product_id ?>">Ver</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="tablenav bottom" style="margin-top:1em;">
            <div class="tablenav-pages">
                <?php
                echo paginate_links([
                    'base'      => add_query_arg('paged', '%#%'),
                    'format'    => '',
                    'current'   => $page,
                    'total'     => $total_pages,
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                ]);
                ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div id="farmapaz-fid-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#fff;max-width:500px;width:90%;border-radius:8px;padding:24px;max-height:80vh;overflow-y:auto;position:relative;">
            <button type="button" id="farmapaz-fid-modal-close" style="position:absolute;top:12px;right:16px;border:none;background:none;font-size:24px;cursor:pointer;">&times;</button>
            <h3 id="farmapaz-fid-modal-title" style="margin-top:0;">Detalle</h3>
            <div id="farmapaz-fid-modal-body"></div>
        </div>
    </div>

    <script>
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.farmapaz-fid-detail');
        if (!btn) return;
        var email = btn.dataset.email;
        var product = btn.dataset.product;
        var modal = document.getElementById('farmapaz-fid-modal');
        var body = document.getElementById('farmapaz-fid-modal-body');
        var title = document.getElementById('farmapaz-fid-modal-title');
        title.textContent = 'Cargando...';
        body.innerHTML = '';
        modal.style.display = 'flex';
        var fd = new FormData();
        fd.append('action', 'farmapaz_fidelidad_detail');
        fd.append('email', email);
        fd.append('product', product);
        fetch(ajaxurl, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                title.textContent = email + ' — ' + data.product_name;
                body.innerHTML = data.html;
            });
    });
    document.getElementById('farmapaz-fid-modal-close').addEventListener('click', function() {
        document.getElementById('farmapaz-fid-modal').style.display = 'none';
    });
    document.getElementById('farmapaz-fid-modal').addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
    </script>
    <?php
}

function farmapaz_fid_calc_streak($months) {
    if (empty($months)) return 0;
    $months = array_unique($months);
    sort($months);
    $current  = date('Y-m');
    $streak   = 0;
    $expected = $current;
    for ($i = count($months) - 1; $i >= 0; $i--) {
        if ($months[$i] === $expected) {
            $streak++;
            $expected = date('Y-m', strtotime($expected . ' -1 month'));
        } elseif ($months[$i] < $expected) {
            break;
        }
    }
    return $streak;
}

// ── AJAX detail modal ──
add_action('wp_ajax_farmapaz_fidelidad_detail', function () {
    global $wpdb;
    $email   = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $product = isset($_POST['product']) ? (int) $_POST['product'] : 0;
    if (!$email || !$product) wp_send_json_error();

    $prefix = $wpdb->prefix;

    if (farmapaz_fid_is_hpos()) {
        $orders = $wpdb->get_results($wpdb->prepare("
            SELECT o.id, o.date_created_gmt AS order_date, oi.order_item_name, qty.meta_value AS qty, total.meta_value AS total
            FROM {$prefix}wc_orders o
            INNER JOIN {$prefix}woocommerce_order_items oi ON o.id = oi.order_id AND oi.order_item_type = 'line_item'
            INNER JOIN {$prefix}woocommerce_order_itemmeta pid ON oi.order_item_id = pid.order_item_id AND pid.meta_key = '_product_id' AND pid.meta_value = %d
            LEFT JOIN {$prefix}woocommerce_order_itemmeta qty ON oi.order_item_id = qty.order_item_id AND qty.meta_key = '_qty'
            LEFT JOIN {$prefix}woocommerce_order_itemmeta total ON oi.order_item_id = total.order_item_id AND total.meta_key = '_line_total'
            WHERE o.type = 'shop_order' AND o.status IN ('wc-completed','wc-processing') AND o.billing_email = %s
            ORDER BY o.date_created_gmt DESC
        ", $product, $email));
    } else {
        $orders = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_date AS order_date, oi.order_item_name, qty.meta_value AS qty, total.meta_value AS total
            FROM {$wpdb->posts} p
            INNER JOIN {$prefix}woocommerce_order_items oi ON p.ID = oi.order_id AND oi.order_item_type = 'line_item'
            INNER JOIN {$prefix}woocommerce_order_itemmeta pid ON oi.order_item_id = pid.order_item_id AND pid.meta_key = '_product_id' AND pid.meta_value = %d
            INNER JOIN {$wpdb->postmeta} email ON p.ID = email.post_id AND email.meta_key = '_billing_email' AND email.meta_value = %s
            LEFT JOIN {$prefix}woocommerce_order_itemmeta qty ON oi.order_item_id = qty.order_item_id AND qty.meta_key = '_qty'
            LEFT JOIN {$prefix}woocommerce_order_itemmeta total ON oi.order_item_id = total.order_item_id AND total.meta_key = '_line_total'
            WHERE p.post_type = 'shop_order' AND p.post_status IN ('wc-completed','wc-processing')
            ORDER BY p.post_date DESC
        ", $product, $email));
    }

    $product_name = $orders ? $orders[0]->order_item_name : '';

    $html = '<table class="widefat striped" style="margin-top:12px;">
        <thead><tr><th># Pedido</th><th>Fecha</th><th>Cant.</th><th>Total</th></tr></thead><tbody>';
    foreach ($orders as $o) {
        $order_id = farmapaz_fid_is_hpos() ? $o->id : $o->ID;
        $html .= '<tr>';
        $html .= '<td><a href="' . esc_url(admin_url('admin.php?page=wc-orders&action=edit&id=' . $order_id)) . '" target="_blank">#' . $order_id . '</a></td>';
        $html .= '<td>' . esc_html(date_i18n(get_option('date_format'), strtotime($o->order_date))) . '</td>';
        $html .= '<td>' . (int) $o->qty . '</td>';
        $html .= '<td>$' . number_format((float) $o->total, 2) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';

    wp_send_json(['html' => $html, 'product_name' => esc_html($product_name)]);
});

// ── Export CSV ──
add_action('admin_init', function () {
    if (!isset($_GET['export']) || $_GET['export'] !== 'csv' || !isset($_GET['page']) || $_GET['page'] !== 'farmapaz-fidelidad') return;
    if (!current_user_can('manage_woocommerce')) return;

    global $wpdb;
    $min_months = isset($_GET['min_months']) ? max(1, (int) $_GET['min_months']) : 2;
    $search     = isset($_GET['s']) ? trim(sanitize_text_field($_GET['s'])) : '';
    $prefix = $wpdb->prefix;

    list($o_from, $o_alias, $o_join_email, $o_date, $o_id) = ['', '', '', '', ''];
    $o_from = farmapaz_fid_orders_from($wpdb, $prefix, $o_alias, $o_join_email, $o_date, $o_id);
    $o_where = farmapaz_fid_orders_where($o_alias);
    $o_email = farmapaz_fid_email_col($o_alias);

    $where = $o_where;
    if ($search) {
        $like  = '%' . $wpdb->esc_like($search) . '%';
        $where .= farmapaz_fid_is_hpos()
            ? $wpdb->prepare(" AND ({$o_alias}.billing_email LIKE %s OR oi.order_item_name LIKE %s)", $like, $like)
            : $wpdb->prepare(" AND (email.meta_value LIKE %s OR oi.order_item_name LIKE %s)", $like, $like);
    }

    $rows = $wpdb->get_results("
        SELECT
            {$o_email} AS customer_email,
            pid.meta_value AS product_id,
            MAX(oi.order_item_name) AS product_name,
            COUNT(DISTINCT DATE_FORMAT({$o_date}, '%Y-%m')) AS months_active,
            COUNT(DISTINCT {$o_id}) AS total_orders,
            MIN({$o_date}) AS first_purchase,
            MAX({$o_date}) AS last_purchase
        FROM {$o_from}
        INNER JOIN {$prefix}woocommerce_order_items oi ON {$o_id} = oi.order_id AND oi.order_item_type = 'line_item'
        INNER JOIN {$prefix}woocommerce_order_itemmeta pid ON oi.order_item_id = pid.order_item_id AND pid.meta_key = '_product_id'
        {$o_join_email}
        WHERE {$where}
        GROUP BY customer_email, product_id
        HAVING months_active >= {$min_months}
        ORDER BY months_active DESC
    ");

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=fidelidad-' . date('Y-m-d') . '.csv');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Cliente', 'Producto', 'Meses Activo', 'Pedidos', 'Primera Compra', 'Ultima Compra']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r->customer_email,
            $r->product_name,
            (int) $r->months_active,
            (int) $r->total_orders,
            $r->first_purchase,
            $r->last_purchase,
        ]);
    }
    fclose($out);
    exit;
});
