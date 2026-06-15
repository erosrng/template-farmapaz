<?php
defined('ABSPATH') || exit;
?>

<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
    <?php
    $message = apply_filters(
        'woocommerce_thankyou_order_received_text',
        'Gracias. Tu pedido ha sido recibido.',
        $order
    );
    echo $message;
    ?>
</p>
