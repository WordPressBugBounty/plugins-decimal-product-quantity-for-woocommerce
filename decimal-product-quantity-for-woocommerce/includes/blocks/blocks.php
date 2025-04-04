<?php
/*
 * Decimal Product Quantity for WooCommerce
 * Blocks
 * blocks.php
 */
 
	function WooDecimalProduct_Blocks_Check_BlockLayots($WC_PageName = '') {
		$debug_process = 'blocks_check_block_layots';
		
		WDPQ_Debugger ($WC_PageName, '$WC_PageName', $debug_process, __FUNCTION__, __LINE__);
		
		$Result = array ();
		
		$BlockLayots = array(
			'cart' => false,
			'checkout' => false,
			'order' => false,
		);
		
		foreach ($BlockLayots as $key => $value) {
			$Page_ID = wc_get_page_id( $key );
			WDPQ_Debugger ($key . ':' .$Page_ID, '$Page_ID', $debug_process, __FUNCTION__, __LINE__);

			$Block_Name = 'woocommerce/' .$key;

// Simulation			
// if ($key == 'cart') {
	// $Page_ID = 2130;
// }
			
			$is_Page_BlockLayot = WC_Blocks_Utils::has_block_in_page( $Page_ID, $Block_Name );
			WDPQ_Debugger ($is_Page_BlockLayot, '$is_Page_BlockLayot', $debug_process, __FUNCTION__, __LINE__);
			
			if ($is_Page_BlockLayot) {
				$Result[] = ucfirst( $key );
			}			
		}

		WDPQ_Debugger ($Result, '$Result', $debug_process, __FUNCTION__, __LINE__);		
		return $Result;
	}
	
	/* AJAX Processing
	----------------------------------------------------------------- */
    add_action ('wp_ajax_wdpq_blocks_ext_processing', 'WooDecimalProduct_Blocks_Ajax');
	add_action ('wp_ajax_nopriv_wdpq_blocks_ext_processing', 'WooDecimalProduct_Blocks_Ajax');
    function WooDecimalProduct_Blocks_Ajax() {
		include_once ('ajax_processing.php');
    }	
	
	