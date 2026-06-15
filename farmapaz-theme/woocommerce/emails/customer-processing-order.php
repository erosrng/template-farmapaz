<?php
defined('ABSPATH') || exit;

$email_improvements_enabled = false;

do_action('woocommerce_email_header', $email_heading, $email);
?>

<p>
<?php if (!empty($order->get_billing_first_name())): ?>
    Hola <?= esc_html($order->get_billing_first_name()); ?>,
<?php else: ?>
    Hola,
<?php endif; ?>
</p>

<p>Te confirmamos que hemos recibido tu pedido #<?= esc_html($order->get_order_number()); ?> y está siendo procesado.</p>

<p>Te enviaremos una notificación cuando el pedido sea despachado.</p>

<?php

do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);
