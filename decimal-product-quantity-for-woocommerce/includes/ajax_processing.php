<?php
/*
 * Decimal Product Quantity for WooCommerce
 * JS Product Object.
 * ajax_processing.php
 */ 
	
// require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );	
	$WooDecimalProduct_Вebug_Process = 'ajax_processing';
	
	$WooDecimalProduct_NonceKey = 'wdpq_ajax_processing';
	$WooDecimalProduct_Nonce = wp_create_nonce ($WooDecimalProduct_NonceKey);	
	
	$WooDecimalProduct_Mode 		= isset($_REQUEST['mode']) ? sanitize_text_field( wp_unslash( $_REQUEST['mode'] ) ) : null; // phpcs:ignore	
	$WooDecimalProduct_Object_ID	= isset($_REQUEST['id']) ? sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) : 0; // phpcs:ignore
	$WooDecimalProduct_Cart			= isset($_REQUEST['cart']) ? stripslashes( $_REQUEST['cart'] ) : array(); // phpcs:ignore
	$WooDecimalProduct_Cart_Name	= isset($_REQUEST['cart_name']) ? $_REQUEST['cart_name'] : array(); // phpcs:ignore
	$WooDecimalProduct_NonceRequest	= isset($_REQUEST['_wpnonce']) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : 'none'; // phpcs:ignore		

	WooDecimalProduct_Debugger ($_REQUEST, '$_REQUEST', $WooDecimalProduct_Вebug_Process, __FUNCTION__, __LINE__); // phpcs:ignore
	
	$WooDecimalProduct_Result = false; 
		
	// Get Product Quantity by ProductID
	if ($WooDecimalProduct_Mode == 'get_product_quantity') {
		if ($WooDecimalProduct_Object_ID) {
			$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($WooDecimalProduct_Object_ID);
			WooDecimalProduct_Debugger ($WooDecimalProduct_QuantityData, '$WooDecimalProduct_QuantityData', $WooDecimalProduct_Вebug_Process, __FUNCTION__, __LINE__);
		}
		
		$WooDecimalProduct_Result = true;	
		
		$WooDecimalProduct_Obj_Request = new stdClass();
		$WooDecimalProduct_Obj_Request -> status 	= 'OK';
		$WooDecimalProduct_Obj_Request -> answer 	= $WooDecimalProduct_Result;
		$WooDecimalProduct_Obj_Request -> qnt_data 	= $WooDecimalProduct_QuantityData;

		wp_send_json( $WooDecimalProduct_Obj_Request );    		
	}
	
	// Update Cart from Local Storage
	if ($WooDecimalProduct_Mode == 'update_wdpq_cart') {
		// WooCart может быть на этом этапе еще не сформирована.
		
die();
		
		WooDecimalProduct_Debugger ($WooDecimalProduct_Cart, '$WooDecimalProduct_Cart', $WooDecimalProduct_Вebug_Process, __FUNCTION__, __LINE__); // phpcs:ignore	
		
		$WooDecimalProduct_Cart = json_decode( $WooDecimalProduct_Cart );
		WooDecimalProduct_Debugger ($WooDecimalProduct_Cart, '$WooDecimalProduct_Cart', $WooDecimalProduct_Вebug_Process, __FUNCTION__, __LINE__); // phpcs:ignore
		
		if ($WooDecimalProduct_Cart) {
			$WooDecimalProduct_WooCart = WC() -> cart;	// WC()->cart->get_cart() return Empty !!!
			WooDecimalProduct_Debugger ($WooDecimalProduct_WooCart, '$WooDecimalProduct_WooCart', $WooDecimalProduct_Вebug_Process, __FUNCTION__, __LINE__); // phpcs:ignore	
			
			
			if ($WooDecimalProduct_WooCart) {
				// Обновляем - Добавляем.
				
				$WooDecimalProduct_WooCart_Contents = $WooDecimalProduct_WooCart -> cart_contents;
				WooDecimalProduct_Debugger ($WooDecimalProduct_WooCart_Contents, '$WooDecimalProduct_WooCart_Contents', $WooDecimalProduct_Вebug_Process, __FUNCTION__, __LINE__); // phpcs:ignore
				
				if ($WooDecimalProduct_WooCart_Contents) {
					
					foreach ($WooDecimalProduct_Cart as $WooDecimalProduct_Item) {
						$WooDecimalProduct_Item_ProductID 	= $WooDecimalProduct_Item -> product_id;
						$WooDecimalProduct_Item_VariationID = $WooDecimalProduct_Item -> variation_id;
						$WooDecimalProduct_Item_Quantity 	= $WooDecimalProduct_Item -> quantity;
						
						foreach ($WooDecimalProduct_WooCart_Contents as $WooDecimalProduct_Key => $WooDecimalProduct_Item) {
							
						}
						
						// WC() -> cart -> add_to_cart( $WooDecimalProduct_Item_ProductID, $WooDecimalProduct_Item_Quantity, $WooDecimalProduct_Item_VariationID );
						// WC() -> cart -> set_quantity( $Item_Key, $WooDecimalProduct_Item_Quantity, $refresh_totals = true );
					}				
					
				}
								
			} else {
				// Формируем Корзину Woo
				
				foreach ($WooDecimalProduct_Cart as $WooDecimalProduct_Item) {
					$WooDecimalProduct_Item_ProductID 	= $WooDecimalProduct_Item -> product_id;
					$WooDecimalProduct_Item_VariationID = $WooDecimalProduct_Item -> variation_id;
					$WooDecimalProduct_Item_Quantity 	= $WooDecimalProduct_Item -> quantity;
					
					// WC() -> cart -> add_to_cart( $WooDecimalProduct_Item_ProductID, $WooDecimalProduct_Item_Quantity, $WooDecimalProduct_Item_VariationID );
				}
			}
			
// $WooDecimalProduct_WooCart = WC() -> cart -> get_cart();
// WooDecimalProduct_Debugger ($WooDecimalProduct_WooCart, '$WooDecimalProduct_WooCart', $WooDecimalProduct_Вebug_Process, __FUNCTION__, __LINE__); // phpcs:ignore
		}
		
		$WooDecimalProduct_Result = true;			
		
		$WooDecimalProduct_Obj_Request = new stdClass();
		$WooDecimalProduct_Obj_Request -> status 	= 'OK';
		$WooDecimalProduct_Obj_Request -> answer 	= $WooDecimalProduct_Result;
		$WooDecimalProduct_Obj_Request -> wdpq_cart = $WooDecimalProduct_Cart;

		wp_send_json( $WooDecimalProduct_Obj_Request );	
	}
	
	// что-то иное
	$WooDecimalProduct_Obj_Request = new stdClass();
	$WooDecimalProduct_Obj_Request -> status 	= 'OK';
	$WooDecimalProduct_Obj_Request -> answer 	= $WooDecimalProduct_Result;

	wp_send_json( $WooDecimalProduct_Obj_Request );  