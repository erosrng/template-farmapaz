<?php
/**
 * Mini-cart
 *
 * Override del tema Farmapaz para el widget de carrito. Evita el template
 * customizado (heredado del tema original) que causa un error crítico al
 * añadir productos al carrito, y reutiliza el render del drawer propio.
 *
 * @package Farmapaz\Templates
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' );
?>

<div class="widget_shopping_cart_content-body farmapaz-mini-cart-body">
	<?php
	do_action( 'woocommerce_before_mini_cart_contents' );

	if ( function_exists( 'farmapaz_render_mini_cart_body' ) ) {
		echo farmapaz_render_mini_cart_body();
	}

	do_action( 'woocommerce_mini_cart_contents' );
	?>
</div>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
