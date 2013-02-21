<?php
/*
Plugin Name: CipherKey-Plugin
Description: Wordpress plugin to create URLs provided by Coupons Inc Algorithm (which is provided in C#)
Version: 1.0
Author: Benjamin Ashby
Author Email: ben.ashby2@gmail.com
License:

  Copyright 2013 Benjamin Ashby (ben.ashby2@gmail.com)
  
*/


class CouponCipherKey {
   

	 
	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {
		//Hooks into Admin
		add_action('admin_menu', array( &$this, 'cypher_key_submenu') );

		//If 
		if ( ! is_admin() ) {
			add_action( 'init', array( &$this, 'register_coupon_shortcode' ) ) ;
		}
	} 
	//*************************************************************************//
	//Calling of the shortcode from the coupon_function function as [coupon]**//
	//***********************************************************************//
	public function register_coupon_shortcode() {
		add_shortcode('coupon', array( &$this, 'coupon_url_creation_function' ) );
	}
	
	//******************************************************************************************************************************//
	//Generation of the URL from the provided offer code, check code, generated pin code, and Coupons, Inc. algorithm generated cpt//
	//****************************************************************************************************************************//
	function coupon_url_creation_function() {
		
		$data = array();
		
		$data['o'] = get_option('offer_code'); // offer code from input
		$data['c'] = get_option('check_code'); // check code from input
		$data['p'] = strval( time() ) . strval( rand(1,99) ); // pin code generation
		$data['cpt'] = $this->encode_cpt( $data['p'], $data['o'], get_option('short_key'), get_option('long_key') ); // 
		
		$url = "http://bricks.coupons.com/enable.asp?eb=1&" . http_build_query($data, '', "&"); //Build URL
		
		return $url;
	}
	//**************************************************************************************************************//
	//Algorithm provided by Coupons, Inc. that has been converted from the provided C# to php by pdenya on github**//
	//Source: https://github.com/pdenya/Coupons-Inc-CipherKey-in-PHP											**//
	//***********************************************************************************************************//
	function encode_cpt($pinCode, $offerCode, $shortKey, $longKey){
		$decodeX = " abcdefghijklmnopqrstuvwxyz0123456789!$%()*+,-.@;<=>?[]^_{|}~";
	
		if(strlen($offerCode) == 5) {
			$ocode = $offerCode % 10000;
		} else {
			$ocode = $offerCode;
		}

		$vob = array();
		$vob[0] = $ocode % 100;
		$vob[1] = ($ocode - $vob[0]) / 100;

		$encodeModulo = array();
		for($i = 0; $i < 61; $i++) {
			$encodeModulo[ord(substr($decodeX, $i, 1))] = $i;
		}

		$pinCode = strtolower($pinCode) . $offerCode;

		if(strlen($pinCode) < 20) {
			$pinCode .= " couponsincproduction";
			$pinCode = substr($pinCode, 0, 20);
		}

		$q = 0;	
		$j = strlen($pinCode);
		$k = strlen($shortKey);
		$cpt = "";

		for($i = 0; $i < $j; $i++) {
			$s1 = $encodeModulo[ord(substr($pinCode, $i, 1))];
			$s2 = 2 * $encodeModulo[ord(substr($shortKey, $i % $k, 1))];
			$s3 = $vob[$i % 2];
			$q = ($q + $s1 + $s2 + $s3) % 61;
			$cpt .= substr($longKey, $q, 1);
		}

	return $cpt;
	}	
	

	//Generation of the submenu under the top level menu item Settings. 
	function cypher_key_submenu() {
		add_options_page( 'My Plugin Options', 'Cypher-Key Elements', 'manage_options', 'cypher-key', array( &$this, 'my_plugin_options') );
	}
	
	//Generation of the form under settings
  	function my_plugin_options() {
		?>
		<div id="icon-themes" class="icon32"></div>
		<div>
		<h1>CipherKey Elements</h1>
		<tr valign="left">
		<h3>Update / Enter the fields provided by Coupons, Inc.</h3>
		<tr>

		<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>

		
		<table width="510">
		<tr valign="top">
		<th width="300" scope="row">Enter Check Code:</th>
		<td width="406">
		<input name="check_code" type="text" id="check_code"
		value="<?php echo get_option('check_code'); ?>" />
		</td>
		
		</tr>
		</table>
		
		<table width="510">
		<tr valign="top">
		<th width="300" scope="row">Enter Offer Code:</th>
		<td width="406">
		<input name="offer_code" type="text" id="offer_code"
		value="<?php echo get_option('offer_code'); ?>" />
		</td>
		
		</tr>
		</table>
		
		<table width="510">
		<tr valign="top">
		<th width="300" scope="row">Enter Short Key:</th>
		<td width="406">
		<input name="short_key" type="text" id="short_key"
		value="<?php echo get_option('short_key'); ?>" />
		</td>
		
		</tr>
		</table>
		
		<table width="510">
		<tr valign="top">
		<th width="300" scope="row">Enter Long Key:</th>
		<td width="406">
		<input name="long_key" type="text" id="long_key"
		value="<?php echo get_option('long_key'); ?>" />
		</td>
		
		<input type="hidden" name="page_options" value="long_key, offer_code, check_code, short_key" />
		</tr>
		</table>

		<input type="hidden" name="action" value="update" />
			

		<p>
		<input type="submit" value="<?php _e('Save Changes') ?>" />
		</p>

		</form>
		<tr valign='center'>
		*The pin code is generated by concantating the two strings: time and a random number between 1-99. This feature is hard-coded into plugin.php
		</tr>
		</div>
		<?php
	}
} //end class


new CouponCipherKey();
?>
