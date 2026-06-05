<?php
/**
 * Payment methods
 */

defined('ABSPATH') || exit;

$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
$chosen_gateway = WC()->session->get('chosen_payment_method');
if (!$chosen_gateway) $chosen_gateway = key($available_gateways);
?>

<div class="checkout-payment-wrap">
    <?php do_action('woocommerce_review_order_before_payment'); ?>

    <ul class="checkout-payment-methods">
        <?php if (!empty($available_gateways)): ?>
        <?php foreach ($available_gateways as $gateway):
            $checked = $gateway->id === $chosen_gateway ? 'checked' : '';
        ?>
        <li class="checkout-payment-method">
            <label class="checkout-payment-label">
                <input type="radio" name="payment_method" value="<?= esc_attr($gateway->id); ?>"
                       class="checkout-payment-radio" <?= $checked; ?>
                       data-order_button_text="<?= esc_attr($gateway->order_button_text ?? ''); ?>">
                <span class="checkout-payment-name"><?= $gateway->get_title(); ?></span>
            </label>
            <?php if ($gateway->has_fields() || $gateway->get_description()): ?>
            <div class="checkout-payment-box payment_box_<?= $gateway->id; ?>" <?php if (!$checked): ?>style="display:none;"<?php endif; ?>>
                <?php $gateway->payment_fields(); ?>
            </div>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
        <?php else: ?>
        <li class="checkout-payment-method">
            <p class="checkout-no-gateways">No hay métodos de pago disponibles.</p>
        </li>
        <?php endif; ?>
    </ul>

    <div class="checkout-place-order">
        <?php do_action('woocommerce_review_order_before_submit'); ?>
        <button type="submit" class="checkout-place-order-btn" name="woocommerce_checkout_place_order" id="place_order">
            Confirmar pedido
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </button>
        <?php do_action('woocommerce_review_order_after_submit'); ?>
        <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
    </div>

    <?php do_action('woocommerce_review_order_after_payment'); ?>
</div>
