<?php
/*
 * Decimal Product Quantity for WooCommerce
 * Blocks
 * ajax_processing.php
 */
 
	$WooDecimalProduct_Вebug_Process = 'blocks_ajax_processing';
	
	// $Mode 		= isset($_REQUEST['mode']) ? sanitize_text_field( wp_unslash( $_REQUEST['mode'] ) ) : null; // phpcs:ignore	
	// $Object_ID	= isset($_REQUEST['id']) ? sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) : 0; // phpcs:ignore
	$WooDecimalProduct_NonceRequest = isset($_REQUEST['wdpq_wpnonce']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_wpnonce'])) : 'none'; // phpcs:ignore	

	WooDecimalProduct_Debugger ($_REQUEST, '$_REQUEST', $WooDecimalProduct_Вebug_Process, __FUNCTION__, __LINE__); // phpcs:ignore
	
	$WooDecimalProduct_NonceKey = 'WooDecimalProduct_Blocks_Get_WDPQ-Cart';
	
	// if (!wp_verify_nonce( $WooDecimalProduct_NonceRequest, $WooDecimalProduct_NonceKey )) {
		// exit;
	// }	
	
	$WooDecimalProduct_Result = false;
	
$WooDecimalProduct_Cart = array();	

	// global $WooDecimalProduct_Cart;
	// WooDecimalProduct_Debugger ($WooDecimalProduct_Cart, '$WooDecimalProduct_Cart', $WooDecimalProduct_Вebug_Process, __FUNCTION__, __LINE__); // phpcs:ignore	
	
	foreach ($WooDecimalProduct_Cart as &$WooDecimalProduct_Item) {
		$WooDecimalProduct_Product_ID 	= $WooDecimalProduct_Item['product_id'];
		$WooDecimalProduct_Variation_ID 	= $WooDecimalProduct_Item['variation_id'];
		
		if ($WooDecimalProduct_Variation_ID) {
			//Вариативный Товар.
			$WooDecimalProduct_Product = wc_get_product( $WooDecimalProduct_Variation_ID );
			
		} else {
			//Простой Товар.
			$WooDecimalProduct_Product = wc_get_product( $WooDecimalProduct_Product_ID );
		}
		
		// WooDecimalProduct_Debugger ($WooDecimalProduct_Product, '$WooDecimalProduct_Product', $WooDecimalProduct_Вebug_Process, __FUNCTION__, __LINE__); // phpcs:ignore	
		
		$WooDecimalProduct_Slug = '';
		$WooDecimalProduct_QuantityData = '';
		
		if ($WooDecimalProduct_Product) {
			$WooDecimalProduct_Slug = $WooDecimalProduct_Product -> get_slug();	
			WooDecimalProduct_Debugger ($WooDecimalProduct_Slug, '$WooDecimalProduct_Slug', $WooDecimalProduct_Вebug_Process, __FUNCTION__, __LINE__); // phpcs:ignore	
			
			$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($WooDecimalProduct_Product_ID);
			WooDecimalProduct_Debugger ($WooDecimalProduct_QuantityData, '$WooDecimalProduct_QuantityData', $WooDecimalProduct_Вebug_Process, __FUNCTION__, __LINE__);			
		}
		
		$WooDecimalProduct_Item['slug'] = $WooDecimalProduct_Slug;
		$WooDecimalProduct_Item['quantity_data'] = $WooDecimalProduct_QuantityData;
	}
	
	// WooDecimalProduct_Action_before_cart_contents ();
	// wp_send_json_success();
	
	// $WooCart = WC() -> cart -> get_cart();
	// WooDecimalProduct_Debugger ($WooCart, '$WooCart', $WooDecimalProduct_Вebug_Process, __FUNCTION__, __LINE__);
	
	$WooDecimalProduct_Result = true;

	$WooDecimalProduct_Obj_Request = new stdClass();
	$WooDecimalProduct_Obj_Request -> status 	= 'OK';
	$WooDecimalProduct_Obj_Request -> answer 	= $WooDecimalProduct_Result;
	$WooDecimalProduct_Obj_Request -> wdpq_cart = $WooDecimalProduct_Cart;

	wp_send_json( $WooDecimalProduct_Obj_Request );    

	die; // Complete.	