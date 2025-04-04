<?php
/*
 * Decimal Product Quantity for WooCommerce
 * JS Product Object.
 * ajax_processing.php
 */ 
	
// require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );	
	$debug_process = 'ajax_processing';
	
	$WooDecimalProduct_Nonce = 'wdpq_ajax_processing';
	$nonce = wp_create_nonce ($WooDecimalProduct_Nonce);	
	
	$Mode 		= isset($_REQUEST['mode']) ? sanitize_text_field( wp_unslash( $_REQUEST['mode'] ) ) : null; // phpcs:ignore	
	$Object_ID	= isset($_REQUEST['id']) ? sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) : 0; // phpcs:ignore
	$WDPQ_Cart	= isset($_REQUEST['cart']) ? stripslashes( $_REQUEST['cart'] ) : array(); // phpcs:ignore
	$Cart_Name	= isset($_REQUEST['cart_name']) ? $_REQUEST['cart_name'] : array(); // phpcs:ignore
	$WP_Nonce	= isset($_REQUEST['_wpnonce']) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : 'none'; // phpcs:ignore		

	WDPQ_Debugger ($_REQUEST, '$_REQUEST', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore

	$Product_QNT_Options = array();
	
	$Result = false; 
		
	// Get Product Quantity by ProductID
	if ($Mode == 'get_product_quantity') {
		if ($Object_ID) {
			$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Object_ID);
			WDPQ_Debugger ($WooDecimalProduct_QuantityData, '$WooDecimalProduct_QuantityData', $debug_process, __FUNCTION__, __LINE__);
		}
		
		$Result = true;	
		
		$Obj_Request = new stdClass();
		$Obj_Request -> status 	= 'OK';
		$Obj_Request -> answer 	= $Result;
		$Obj_Request -> qnt_data = $WooDecimalProduct_QuantityData;

		wp_send_json( $Obj_Request );    		
	}
	
	// Update Cart from Local Storage
	if ($Mode == 'update_wdpq_cart') {
		// WooCart может быть на этом этапе еще не сформирована.
		
die();
		
		WDPQ_Debugger ($WDPQ_Cart, '$WDPQ_Cart', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore	
		
		$WDPQ_Cart = json_decode( $WDPQ_Cart );
		WDPQ_Debugger ($WDPQ_Cart, '$WDPQ_Cart', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore
		
		if ($WDPQ_Cart) {
			$WooCart = WC() -> cart;	// WC()->cart->get_cart() return Empty !!!
			WDPQ_Debugger ($WooCart, '$WooCart', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore	
			
			
			if ($WooCart) {
				// Обновляем - Добавляем.
				
				$Cart_Contents = $WooCart -> cart_contents;
				WDPQ_Debugger ($Cart_Contents, '$Cart_Contents', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore
				
				if ($Cart_Contents) {
					
					foreach ($WDPQ_Cart as $Item) {
						$Item_ProductID 	= $Item -> product_id;
						$Item_VariationID 	= $Item -> variation_id;
						$Item_Quantity 		= $Item -> quantity;
						
						foreach ($Cart_Contents as $key => $Item) {
							
						}
						
						// WC() -> cart -> add_to_cart( $Item_ProductID, $Item_Quantity, $Item_VariationID );
						// WC() -> cart -> set_quantity( $Item_Key, $Item_Quantity, $refresh_totals = true );
					}				
					
				}
								
			} else {
				// Формируем Корзину Woo
				
				foreach ($WDPQ_Cart as $Item) {
					$Item_ProductID 	= $Item -> product_id;
					$Item_VariationID 	= $Item -> variation_id;
					$Item_Quantity 		= $Item -> quantity;
					
					// WC() -> cart -> add_to_cart( $Item_ProductID, $Item_Quantity, $Item_VariationID );
				}
			}
			
// $WooCart = WC() -> cart -> get_cart();
// WDPQ_Debugger ($WooCart, '$WooCart', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore
		}
		
		$Result = true;			
		
		$Obj_Request = new stdClass();
		$Obj_Request -> status 	= 'OK';
		$Obj_Request -> answer 	= $Result;
		$Obj_Request -> wdpq_cart = $WDPQ_Cart;

		wp_send_json( $Obj_Request );	
	}
	
	// что-то иное
	$Obj_Request = new stdClass();
	$Obj_Request -> status 	= 'OK';
	$Obj_Request -> answer 	= $Result;

	wp_send_json( $Obj_Request );  