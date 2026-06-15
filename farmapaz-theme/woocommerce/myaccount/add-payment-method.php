<?php
defined('ABSPATH') || exit;
?>

<div class="farmapaz-payment-wrap">
    <div class="farmapaz-payment-header">
        <h1 class="farmapaz-payment-title">Agregar Método de Pago</h1>
        <p class="farmapaz-payment-subtitle">Añade un nuevo método de pago a tu cuenta.</p>
    </div>

    <?php wc_print_notices(); ?>

    <?php if (have_posts()): the_post(); ?>
        <?php the_content(); ?>
    <?php else: ?>
    <div class="farmapaz-payment-empty">
        <div class="farmapaz-payment-empty-icon">
            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: rgba(9,20,110,0.2);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>
        <h3 class="farmapaz-payment-empty-title">No disponible</h3>
        <p class="farmapaz-payment-empty-text">Los métodos de pago solo se pueden agregar durante el proceso de compra. Si necesitas ayuda, contáctanos por WhatsApp.</p>
        <a href="https://wa.me/584128798885" target="_blank"
           class="farmapaz-orders-shop-btn">
            Contactar por WhatsApp
        </a>
    </div>
    <?php endif; ?>
</div>
