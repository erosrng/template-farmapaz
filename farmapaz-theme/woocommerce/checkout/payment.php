<?php
defined('ABSPATH') || exit;

$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
$chosen_gateway = WC()->session->get('chosen_payment_method');
if (!$chosen_gateway) $chosen_gateway = key($available_gateways);
?>

<div class="checkout-payment-wrap">

    <?php do_action('woocommerce_review_order_before_payment'); ?>

    <div class="checkout-payment-actions">

        <h3 class="checkout-payment-title">Metodo de pago</h3>

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
                <p class="checkout-no-gateways">No hay metodos de pago disponibles.</p>
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

    </div>

    <!-- Retiro en tienda: la confirmación es solo por WhatsApp, no por la web -->
    <a href="#" class="farmapaz-wa-btn farmapaz-wa-btn-pickup" data-wa-order data-delivery-wa style="display:none;">
        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21 5.46 0 9.91-4.45 9.91-9.91C21.95 6.45 17.5 2 12.04 2zm5.83 14.15c-.25.7-1.45 1.33-2 1.38-.51.05-1.16.07-1.87-.12-.43-.16-.98-.32-1.69-.62-2.98-1.29-4.92-4.29-5.07-4.49-.15-.2-1.22-1.62-1.22-3.09 0-1.47.77-2.19 1.04-2.49.27-.3.6-.37.8-.37h.57c.18.01.43-.07.67.51.25.6.85 2.07.93 2.22.08.15.13.33.03.53-.1.2-.15.32-.3.5-.15.18-.31.4-.45.53-.15.15-.3.31-.13.61.17.3.75 1.24 1.61 2.01 1.11.99 2.04 1.3 2.33 1.45.29.15.46.13.63-.08.17-.2.73-.85.92-1.15.2-.3.4-.25.67-.15.27.1 1.71.81 2 .96.3.15.5.22.57.35.08.13.08.7-.17 1.4z"/></svg>
        Retirar en tienda
    </a>

    <?php do_action('woocommerce_review_order_after_payment'); ?>

</div>
