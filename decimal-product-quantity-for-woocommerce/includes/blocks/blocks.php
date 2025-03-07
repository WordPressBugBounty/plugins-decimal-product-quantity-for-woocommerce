<?php
/*
 * Decimal Product Quantity for WooCommerce
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
			
			$is_Page_BlockLayot = WC_Blocks_Utils::has_block_in_page( $Page_ID, $Block_Name );
			WDPQ_Debugger ($is_Page_BlockLayot, '$is_Page_BlockLayot', $debug_process, __FUNCTION__, __LINE__);
			
			if ($is_Page_BlockLayot) {
				$Result[] = ucfirst( $key );
			}			
		}

		WDPQ_Debugger ($Result, '$Result', $debug_process, __FUNCTION__, __LINE__);		
		return $Result;
	}
