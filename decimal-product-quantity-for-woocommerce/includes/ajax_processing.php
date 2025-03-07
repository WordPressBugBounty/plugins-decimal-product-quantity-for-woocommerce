<?php
/*
 * Decimal Product Quantity for WooCommerce
 * JS Product Object.
 * ajax_processing.php
 */ 
	$debug_process = 'ajax_processing';
	
	$Mode 		= isset($_REQUEST['mode']) ? sanitize_text_field( wp_unslash( $_REQUEST['mode'] ) ) : null; // phpcs:ignore	
	$Object_ID	= isset($_REQUEST['id']) ? sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) : 0; // phpcs:ignore

	WDPQ_Debugger ($_REQUEST, '$_REQUEST', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore

	$Product_QNT_Options = array();
	
	$Result = false; 
		
	// get_product_quantity
	if ($Mode == 'get_product_quantity') {
		if ($Object_ID) {
			$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Object_ID);
			WDPQ_Debugger ($WooDecimalProduct_QuantityData, '$WooDecimalProduct_QuantityData', $debug_process, __FUNCTION__, __LINE__);
		}
		
		$Result = true;	
	}
	
	$Obj_Request = new stdClass();
	$Obj_Request -> status 	= 'OK';
	$Obj_Request -> answer 	= $Result;
	$Obj_Request -> qnt_data = $WooDecimalProduct_QuantityData;

	wp_send_json( $Obj_Request );    

	die; // Complete.