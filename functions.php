<?php
// Required TrueThemes Framework - do not edit
require_once(TEMPLATEPATH . '/truethemes_framework/truethemes_framework_init.php');

// Load translation text domain
load_theme_textdomain ('truethemes_localize');



// **** Add your custom codes below this line ****
// Schedule our action after the Shopp actions get added
add_action('wp','disable_shopp_css',100);
 
// Callback to remove Shopp CSS
function disable_shopp_css() {
	global $Shopp;
	remove_action('wp_head',array(&$Shopp,'header'));
}

/**
 * Added by Jonathon McDonald
 */
function bootstrap_form()
{
	wp_register_style( 'boostrap_form', get_template_directory_uri() . '/css/bootstrap.css');
	wp_enqueue_style('bootstrap_form');
}
add_action('wp_enqueue_scripts', 'bootstrap_form');

/**
 * Added by Jonathon McDonald
 */
function go_back_button()
{
	echo'<p style="float: left; width:300px; font-size: 10px; line-height: 25px;"><a class="button" style="font-size: 10px; margin-bottom: 10px;" href="http://drnonprofit.com/coaching/">Continue Shopping</a>
	<br />
	Only one subscription needs to be purchased at a time, additional purchases may be made after this one is completed</p>';
}
add_action('woocommerce_review_order_before_submit', 'go_back_button');

/**
 * Having a subscription and then trying to add another item
 * does not redirect to the cart properly.  This will now cause
 * an add to cart link to properly redirect.  
 *
 * @author Jonathon McDonald <jon@onewebcentric.com>
 */
function jm_sub_fix_filter( $url )
{
	return '/cart/';
}

/**
 * This ensures that a subscription is in the cart so the above fix
 * will only work if there is a bus in the cart.  
 *
 * @author Jonathon McDonald <jon@onewebcentric.com>
 */
function jm_sub_fix_action( $valid, $product_id, $quantity )
{
	global $woocommerce;

	if ( !WC_Subscriptions_Product::is_subscription( $product_id ) && WC_Subscriptions_Cart::cart_contains_subscription() ) 
		add_filter( 'add_to_cart_redirect', 'jm_sub_fix_filter', 10, 1 );


	return $valid;
}
add_action( 'woocommerce_add_to_cart_validation', 'jm_sub_fix_action', 9, 3);
?>