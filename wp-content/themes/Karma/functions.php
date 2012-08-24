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

?>