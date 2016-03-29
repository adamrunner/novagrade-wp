<?php  /**
 * Layers Theme Filters & Hooks
 *
 * This file is used to modify any Layers HTML and or CSS classes.
 *
 * @package Layers
 * @since Layers 1.0
 */

global $wp_customize;

/**
* Register WooCommerce Widgets
*/
if (!function_exists('layers_woocommerce_cart_button')) {
		function layers_woocommerce_cart_button(){
				global $woocommerce;

				if( !$woocommerce ) return;

				if( !layers_get_theme_mod( 'woocommerce-show-site-cart-heading') ) return;

				if( !layers_get_theme_mod( 'woocommerce-show-menu-cart-icon' ) && !layers_get_theme_mod( 'woocommerce-show-menu-cart-amount' ) && !layers_get_theme_mod( 'woocommerce-show-menu-cart-products' ) ) return; ?>
				<div class="header-cart">
					<a href="<?php echo $woocommerce->cart->get_cart_url(); ?>" class="cart">
						<?php if( TRUE === (bool) layers_get_theme_mod( 'woocommerce-show-menu-cart-icon' ) ) { ?>
							<span class="l-shopping-cart"></span>
						<?php } ?>
						<?php if( TRUE === (bool) layers_get_theme_mod( 'woocommerce-show-menu-cart-products' ) ) { ?>
							<span class="cart-count"><?php echo $woocommerce->cart->cart_contents_count; ?></span>
						<?php } ?>
						<?php if( TRUE === (bool) layers_get_theme_mod( 'woocommerce-show-menu-cart-amount' ) ) { ?>
							<span class="cart-total">
								<?php echo $woocommerce->cart->get_cart_subtotal(); ?>
							</span>
						<?php } ?>
					</a>

					<?php if( TRUE === (bool) layers_get_theme_mod( 'woocommerce-show-menu-cart-mini-cart' ) ) { ?>
						<div class="header-mini-cart">
							<?php woocommerce_mini_cart(); ?>
						</div>
					<?php } ?>
				</div>
		<?php };
}

add_action( 'layers_after_header_nav' , 'layers_woocommerce_cart_button' , 30 );


if (!function_exists('layers_woocommerce_cart_fragments')) {
	function layers_woocommerce_cart_fragments( $fragments ){
		 ob_start();

		layers_woocommerce_cart_button();

		$fragments[ 'div.header-cart' ] = ob_get_clean();
		$fragments[ 'ul.cart_list-cart' ] = ob_get_clean();

		return $fragments;
	}

	if( !$wp_customize )
		add_filter( 'add_to_cart_fragments', 'layers_woocommerce_cart_fragments' );
}

add_filter( 'layers_get_page_title', 'layers_woocommerce_page_title' );
function layers_woocommerce_page_title( $title ){

	if( !class_exists( 'WooCommerce' ) ) return $title;

	if( is_product_category() ) {

		$term = get_term_by( 'slug', get_query_var('term' ), get_query_var( 'taxonomy' ) );

		if( is_wp_error( $term ) ) return $title;

		$title['title'] = $term->name;
		if( TRUE == layers_get_theme_mod( 'woocommerce-header-category-excerpt' ) ){
			$title['excerpt'] = $term->description;
		}
	}

	 return $title;
}
