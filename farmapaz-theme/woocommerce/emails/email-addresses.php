<?php
defined('ABSPATH') || exit;

$address  = $order->get_formatted_billing_address();
$shipping = $order->get_formatted_shipping_address();
?>
<hr style="border: 0; border-top: 1px solid #1E1E1E; border-top-color: rgba(30, 30, 30, 0.2); margin: 20px 0;">
<table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;" border="0" role="presentation">
    <tr>
        <td class="font-family text-align-left" style="border:0; padding:0;" valign="top" width="50%">
            <h2>Dirección de facturación</h2>
            <address class="address">
                <?php echo wp_kses_post($address ? $address : esc_html__('N/A', 'woocommerce')); ?>
                <?php if ($order->get_billing_phone()): ?>
                    <br/><?php echo wc_make_phone_clickable($order->get_billing_phone()); ?>
                <?php endif; ?>
                <?php if ($order->get_billing_email()): ?>
                    <br/><?php echo esc_html($order->get_billing_email()); ?>
                <?php endif; ?>
            </address>
        </td>
        <?php if (!wc_ship_to_billing_address_only() && $order->needs_shipping_address() && $shipping): ?>
            <td class="font-family text-align-left" style="padding:0;" valign="top" width="50%">
                <h2>Dirección de envío</h2>
                <address class="address">
                    <?php echo wp_kses_post($shipping); ?>
                    <?php if ($order->get_shipping_phone()): ?>
                        <br/><?php echo wc_make_phone_clickable($order->get_shipping_phone()); ?>
                    <?php endif; ?>
                </address>
            </td>
        <?php endif; ?>
    </tr>
</table>
