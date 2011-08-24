<?php
/*
Plugin Name: JW Paypal Shortcodes
Plugin URI: http://wordpress.org/extend/plugins/jw-paypal-shortcodes/
Description: Shortcodes + PayPal Website Payments Standard - Add to Cart Buttons
Author: Jackson Whelan
Version: 0.3
Author URI: http://jacksonwhelan.com
*/

if ( ! defined( 'ABSPATH' ) )
	die( "Can't load this file directly" );

class JWPaypal
{
	function __construct() {
		add_action('admin_menu', array($this, 'jw_pp_create_menu'));
		add_action('admin_init', array($this, 'jw_pp_admin_init'));
		add_shortcode('paypal', array($this, 'jw_pp_shortcodes'));
	}
	
	function jw_pp_admin_init() {
		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
			add_filter('mce_buttons', array($this, 'filter_mce_button'));
			add_filter('mce_external_plugins', array($this, 'filter_mce_plugin'));
		}
	}
	
	function filter_mce_button($buttons) {
		array_push($buttons, '|', 'jwpaypal_button' );
		return $buttons;
	}
	
	function filter_mce_plugin($plugins) {
		$plugins['jwpaypal'] = plugin_dir_url( __FILE__ ) . 'jwpaypal_plugin.js';
		return $plugins;
	}
	
	function jw_pp_create_menu() {
		add_management_page('PayPal Settings', 'PayPal Settings', 'administrator', __FILE__, array($this, 'jw_pp_settings_page'));
		add_action('admin_init', array($this, 'jw_pp_register_settings'));
	}
	
	function jw_pp_register_settings() {
		register_setting('jw-pp-settings-group', 'jw-pp-email');
		register_setting('jw-pp-settings-group', 'jw-pp-curr');
		register_setting('jw-pp-settings-group', 'jw-pp-lc');
		register_setting('jw-pp-settings-group', 'jw-pp-acimg');
		register_setting('jw-pp-settings-group', 'jw-pp-coimg');
	}

	function jw_pp_settings_page() { ?>
		<div class="wrap">
			<h2>PayPal Shortcodes Settings</h2>
			<?php if($_GET['settings-updated'] == true) { ?>
			<div class="update"><p><strong>Settings Updated</strong></p></div>
			<?php } if( !get_option('jw-pp-email') ) { ?>
			<p><strong>WARNING: Plugin will not function properly without your merchant email address.</strong></p>
			<?php } ?>
			<form method="post" action="options.php">
			    <?php settings_fields('jw-pp-settings-group'); ?>
			    <table class="form-table">
			        <tr valign="top">
			        <th scope="row">PayPal Merchant Email</th>
			        <td><input type="text" name="jw-pp-email" value="<?php echo get_option('jw-pp-email'); ?>" /></td>
			        </tr>
			        <tr valign="top">
			        <th scope="row">Currency Code</th>
			        <td><input type="text" name="jw-pp-curr" value="<?php echo get_option('jw-pp-curr'); ?>" /><br/>
			        Default: USD, <a href="https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_currency_codes" target="_blank">available codes here</a>.</td>
			        </tr>
			        <tr valign="top">
			        <th scope="row">Language Code</th>
			        <td><input type="text" name="jw-pp-lc" value="<?php echo get_option('jw-pp-lc'); ?>" /><br/>
			        Default: US, <a href="https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_country_codes" target="_blank">available codes here</a>.</td>
			        </tr> 
			        <tr valign="top">
			        <th scope="row">Add to Cart Image</th>
			        <td><input type="text" name="jw-pp-acimg" value="<?php echo get_option('jw-pp-acimg'); ?>" /><br/>
			        Defaults to standard submit button with CSS class of 'pp-button'. </td>
			        </tr>
			        <tr valign="top">
			        <th scope="row">Check Out Image</th>
			        <td><input type="text" name="jw-pp-coimg" value="<?php echo get_option('jw-pp-coimg'); ?>" /><br/>
			        Defaults to standard submit button with CSS class of 'pp-button'.</td>
			        </tr>
			    </table>
			    <p class="submit">
			    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			    </p>
			</form>
		</div>
	<?php }
	
	function jw_pp_shortcodes($atts) {
	
		$email = get_option('jw-pp-email');
		$cc = ( get_option('jw-pp-curr') ? get_option('jw-pp-curr') : 'USD' ) ;
		$lc = ( get_option('jw-pp-lc') ? get_option('jw-pp-lc') : 'US' ) ;
		$acimg = ( get_option('jw-pp-acimg') ? get_option('jw-pp-acimg') : false ) ;
		$coimg = ( get_option('jw-pp-coimg') ? get_option('jw-pp-coimg') : false ) ;
		
		$acbutton = ( $acimg ? '<input type="image" src="'.$acimg.'" class="pp-img-button" value="Add to Cart">' : '<input type="submit" class="pp-button" value="Add to Cart" name="submit" alt="PayPal - The safer, easier way to pay online!">' ) ;
		
		$cobutton = ( $coimg ? '<input type="image" src="'.$coimg.'" class="pp-img-button" value="View Cart / Checkout">' : '<input type="submit" class="pp-button" value="View Cart / Checkout" border="0" name="submit" alt="View Cart">' );
		
		$shipadd = $atts['shipadd'];
		if(!is_numeric($shipadd)) $shipadd = '2';
		switch($atts['type']):
			case "add":
			$code = '	
			<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post" class="pp-form">
			<input type="hidden" name="cmd" value="_cart">
			<input type="hidden" name="business" value="'.$email.'">
			<input type="hidden" name="lc" value="'.$lc.'">
			<input type="hidden" name="item_name" value="'.$atts['productname'].'">
			<input type="hidden" name="item_number" value="'.$atts['sku'].'">
			<input type="hidden" name="amount" value="'.$atts['amount'].'">
			<input type="hidden" name="currency_code" value="'.$cc.'">
			<input type="hidden" name="button_subtype" value="products">
			<input type="hidden" name="no_note" value="1">
			<input type="hidden" name="add" value="1">
			<input type="hidden" name="no_shipping" value="'.$shipadd.'">
			<input type="hidden" name="bn" value="PP-ShopCartBF:btn_cart_SM.gif:NonHostedGuest">';
			if($atts['weight'] != '') {
				$code.= '<input type="hidden" name="weight" value="'.$atts['weight'].'">';
			}
			if($atts['shipcost'] != '') {
				$code.= '<input type="hidden" name="shipping" value="'.$atts['shipcost'].'">';
			}
			if($atts['shipcost2'] != '') {
				$code.= '<input type="hidden" name="shipping2" value="'.$atts['shipcost2'].'">';
			}
			if($atts['extra'] != '') {
				$code.='<table><tr>';
				$code.='<td><input type="hidden" name="on0" value="'.$atts['extra'].'">'.$atts['extra'].':</td><td><input type="text" name="os0" maxlength="60"></td>';
				$code.= '<td>'.$acbutton.'</td></tr>
			</table>';
			} else {
			$code.= $acbutton;
			}
			$code.= '</form>';
			break;
			case "view":
			$code = '
				<form name="_xclick" target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post" class="pp-form">
				<input type="hidden" name="cmd" value="_cart">
				<input type="hidden" name="business" value="'.$email.'">
				'.$cobutton.'
				<input type="hidden" name="display" value="1">
				</form>
			';
			break;	
		endswitch;
		return $code;	
	}
	
}

$jwpaypal = new JWPaypal();

?>