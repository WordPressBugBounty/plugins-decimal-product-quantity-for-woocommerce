<?php
/*
 * Decimal Product Quantity for WooCommerce
 * JS Product Object.
 * ajax_processing.php
 */ 
	
	$Mode 		= isset($_REQUEST['mode']) ? sanitize_text_field( wp_unslash( $_REQUEST['mode'] ) ) : null; // phpcs:ignore	
	$Object_ID	= isset($_REQUEST['id']) ? sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) : 0; // phpcs:ignore

	WooDecimalProduct_Debugger ($_REQUEST, __FUNCTION__ .' $_REQUEST ' .__LINE__, 'ajax_processing', true); // phpcs:ignore	

	$Product_QNT_Options = array();
	
	$Result = false; 
		
	// get_product_quantity
	if ($Mode == 'get_product_quantity') {
		if ($Object_ID) {
			$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Object_ID);
			WooDecimalProduct_Debugger ($WooDecimalProduct_QuantityData, __FUNCTION__ .' $WooDecimalProduct_QuantityData ' .__LINE__, 'ajax_processing', true);
		}
		
		$Result = true;	
	}
	
	$Obj_Request = new stdClass();
	$Obj_Request -> status 	= 'OK';
	$Obj_Request -> answer 	= $Result;
	$Obj_Request -> qnt_data = $WooDecimalProduct_QuantityData;

	wp_send_json( $Obj_Request );    

	die; // Complete.