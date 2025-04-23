<?php
/*
 * Decimal Product Quantity for WooCommerce
 * Blocks
 * ajax_processing.php
 */
 
	$debug_process = 'blocks_ajax_processing';
	
	// $Mode 		= isset($_REQUEST['mode']) ? sanitize_text_field( wp_unslash( $_REQUEST['mode'] ) ) : null; // phpcs:ignore	
	// $Object_ID	= isset($_REQUEST['id']) ? sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) : 0; // phpcs:ignore
	$Nonce 	= isset($_REQUEST['wdpq_wpnonce']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_wpnonce'])) : 'none'; // phpcs:ignore	

	WDPQ_Debugger ($_REQUEST, '$_REQUEST', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore
	
	$WDPQ_Nonce = 'WooDecimalProduct_Blocks_Get_WDPQ-Cart';
	
	// if (!wp_verify_nonce( $Nonce, $WDPQ_Nonce )) {
		// exit;
	// }	
	
	$Result = false;
	
$WDPQ_Cart = array();	

	// global $WDPQ_Cart;
	// WDPQ_Debugger ($WDPQ_Cart, '$WDPQ_Cart', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore	
	
	foreach ($WDPQ_Cart as &$Item) {
		$Product_ID 	= $Item['product_id'];
		$Variation_ID 	= $Item['variation_id'];
		
		if ($Variation_ID) {
			//Вариативный Товар.
			$Product = wc_get_product( $Variation_ID );
			
		} else {
			//Простой Товар.
			$Product = wc_get_product( $Product_ID );
		}
		
		// WDPQ_Debugger ($Product, '$Product', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore	
		
		$Slug = '';
		$QuantityData = '';
		
		if ($Product) {
			$Slug = $Product -> get_slug();	
			WDPQ_Debugger ($Slug, '$Slug', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore	
			
			$QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID);
			WDPQ_Debugger ($QuantityData, '$QuantityData', $debug_process, __FUNCTION__, __LINE__);			
		}
		
		$Item['slug'] = $Slug;
		$Item['quantity_data'] = $QuantityData;
	}
	
	// WooDecimalProduct_Action_before_cart_contents ();
	// wp_send_json_success();
	
	// $WooCart = WC() -> cart -> get_cart();
	// WDPQ_Debugger ($WooCart, '$WooCart', $debug_process, __FUNCTION__, __LINE__);
	
	$Result = true;

	$Obj_Request = new stdClass();
	$Obj_Request -> status 	= 'OK';
	$Obj_Request -> answer 	= $Result;
	$Obj_Request -> wdpq_cart = $WDPQ_Cart;

	wp_send_json( $Obj_Request );    

	die; // Complete.	