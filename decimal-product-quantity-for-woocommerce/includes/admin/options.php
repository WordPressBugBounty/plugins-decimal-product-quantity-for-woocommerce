<?php
/*
 * WPGear. Decimal Product Quantity for WooCommerce
 * options.php
 */
	
    if (!current_user_can('edit_dashboard')) {
        return;
    }

	$WooDecimalProduct_Debug_Process = 'setup_options';
	WooDecimalProduct_Debugger ($_REQUEST, '$_REQUEST', $WooDecimalProduct_Debug_Process, __FUNCTION__, __LINE__); // phpcs:ignore	
	
	$WooDecimalProduct_Plugin_URL = plugin_dir_url ( __FILE__ ); // со слэшем на конце

	$WooDecimalProduct_Plugin_Data = get_plugin_data( __FILE__ );
	$WooDecimalProduct_Plugin_Version = $WooDecimalProduct_Plugin_Data['Version'];
	wp_enqueue_style ('wdpq_admin_style', $WooDecimalProduct_Plugin_URL .'admin-style.css', array(), $WooDecimalProduct_Plugin_Version); // phpcs:ignore 
	
	$WooDecimalProduct_NonceKey = 'Update_Options_DecimalProductQuantityForWooCommerce';
	$WooDecimalProduct_Nonce = wp_create_nonce ($WooDecimalProduct_NonceKey);	
	
	$WooDecimalProduct_Min_Quantity_Default    	= get_option ('woodecimalproduct_min_qnt_default', 1);  
	$WooDecimalProduct_Max_Quantity_Default    	= get_option ('woodecimalproduct_max_qnt_default', '');  
    $WooDecimalProduct_Step_Quantity_Default   	= get_option ('woodecimalproduct_step_qnt_default', 1); 
	$WooDecimalProduct_Item_Quantity_Default   	= get_option ('woodecimalproduct_item_qnt_default', 1);

$WooDecimalProduct_Min_Quantity_Default    	= WooDecimalProduct_DecimalValueFormatting( $WooDecimalProduct_Min_Quantity_Default );
$WooDecimalProduct_Max_Quantity_Default    	= WooDecimalProduct_DecimalValueFormatting( $WooDecimalProduct_Max_Quantity_Default );
$WooDecimalProduct_Step_Quantity_Default    = WooDecimalProduct_DecimalValueFormatting( $WooDecimalProduct_Step_Quantity_Default );
$WooDecimalProduct_Item_Quantity_Default    = WooDecimalProduct_DecimalValueFormatting( $WooDecimalProduct_Item_Quantity_Default );
	
	$WooDecimalProduct_ButtonsPM_Product_Enable	= get_option ('woodecimalproduct_buttonspm_product_enable', 0);
	$WooDecimalProduct_ButtonsPM_Cart_Enable	= get_option ('woodecimalproduct_buttonspm_cart_enable', 0);
	
	$WooDecimalProduct_Auto_Correction_Quantity	= get_option ('woodecimalproduct_auto_correction_qnt', 1);
	$WooDecimalProduct_AJAX_Cart_Update			= get_option ('woodecimalproduct_ajax_cart_update', 0);	
	$WooDecimalProduct_Price_Unit_Label			= get_option ('woodecimalproduct_price_unit_label', 0);	
	
	$WooDecimalProduct_ConsoleLog_Debuging		= get_option ('woodecimalproduct_debug_log', 0);
	$WooDecimalProduct_Uninstall_Del_MetaData 	= get_option ('woodecimalproduct_uninstall_del_metadata', 0);
	
	$WooDecimalProduct_StorageType 				= get_option ('woodecimalproduct_storage_type', 'system');
	
	$WooDecimalProduct_RSS_Feed_Link = get_site_url() ."?feed=products";
	
	$WooDecimalProduct_Action 		= isset($_REQUEST['action']) ? sanitize_text_field (wp_unslash($_REQUEST['action'])) : null;
	$WooDecimalProduct_NonceRequest = isset($_REQUEST['_wpnonce']) ? sanitize_text_field (wp_unslash($_REQUEST['_wpnonce'])) : 'none';
	
	$WooDecimalProduct_Errors_Msg = array();
	$WooDecimalProduct_RSS_Feed_Slug = 'products';
	
$WooDecimalProduct_Options = array(
	'min' => $WooDecimalProduct_Min_Quantity_Default,
	'max' => $WooDecimalProduct_Max_Quantity_Default,
	'step' => $WooDecimalProduct_Step_Quantity_Default,
	'default' => $WooDecimalProduct_Item_Quantity_Default,
	'buttons_pm_product' => $WooDecimalProduct_ButtonsPM_Product_Enable,
	'buttons_pm_cart' => $WooDecimalProduct_ButtonsPM_Cart_Enable,
	'auto_correction' => $WooDecimalProduct_Auto_Correction_Quantity,
	'ajax_cart_update' => $WooDecimalProduct_AJAX_Cart_Update,
	'price_unit_labale' => $WooDecimalProduct_Price_Unit_Label,
	'console_log_debuging' => $WooDecimalProduct_ConsoleLog_Debuging,
	'uninstall_del_metadata' => $WooDecimalProduct_Uninstall_Del_MetaData,
	'storage_type' => $WooDecimalProduct_StorageType,
	'rss_feed_link' => $WooDecimalProduct_RSS_Feed_Link,
	'rss_feed_slug' => $WooDecimalProduct_RSS_Feed_Slug,
);
WooDecimalProduct_Debugger ($WooDecimalProduct_Options, '$WooDecimalProduct_Options', $WooDecimalProduct_Debug_Process, __FUNCTION__, __LINE__); // phpcs:ignore	
	
	if ($WooDecimalProduct_Action == 'Update') {
		if (!wp_verify_nonce($WooDecimalProduct_NonceRequest, $WooDecimalProduct_NonceKey)) {
			?>
				<div class="wrap">
					<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
					<hr>
					<div class="wdpq_options_box">						
						<?php echo esc_html( __('Warning! Data Incorrect. Update Disable.', 'decimal-product-quantity-for-woocommerce') ); ?>
					</div>
				</div>
			<?php
			
			exit;
		}
		
		$WooDecimalProduct_New_Min	= isset($_REQUEST['wdpq_min_qnt_default']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_min_qnt_default'])) : 1;
		$WooDecimalProduct_New_Max	= isset($_REQUEST['wdpq_max_qnt_default']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_max_qnt_default'])) : '';
		$WooDecimalProduct_New_Step	= isset($_REQUEST['wdpq_step_qnt_default']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_step_qnt_default'])) : 1;
		$WooDecimalProduct_New_Set	= isset($_REQUEST['wdpq_set_qnt_default']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_set_qnt_default'])) : 1;
		
		$WooDecimalProduct_New_AutoCorrection 			= isset($_REQUEST['wdpq_auto_correction']) ? 1 : 0;
		$WooDecimalProduct_New_AJAX_CartUpdate 			= isset($_REQUEST['wdpq_ajax_cart_update']) ? 1 : 0;
		$WooDecimalProduct_New_PriceUnitLabel 			= isset($_REQUEST['wdpq_price_unit_label']) ? 1 : 0;
		$WooDecimalProduct_New_ButtonsPM_ProductEnable 	= isset($_REQUEST['wdpq_buttons_pm_product_enable']) ? 1 : 0;
		$WooDecimalProduct_New_ButtonsPM_CartEnable 	= isset($_REQUEST['wdpq_buttons_pm_cart_enable']) ? 1 : 0;
		
		$WooDecimalProduct_New_ConsoleLogDebuging 		= isset($_REQUEST['wdpq_debug_log']) ? 1 : 0;
		$WooDecimalProduct_New_UninstallDel_MetaData	= isset($_REQUEST['wdpq_uninstall_del']) ? 1 : 0;
		
		$WooDecimalProduct_New_StorageType = isset($_REQUEST['wdpq_storage_type']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_storage_type'])) : 'local';

$WooDecimalProduct_New_Min 	= WooDecimalProduct_Normalize_Number ($WooDecimalProduct_New_Min);
$WooDecimalProduct_New_Max 	= WooDecimalProduct_Normalize_Number ($WooDecimalProduct_New_Max);
$WooDecimalProduct_New_Step 	= WooDecimalProduct_Normalize_Number ($WooDecimalProduct_New_Step);
$WooDecimalProduct_New_Set 	= WooDecimalProduct_Normalize_Number ($WooDecimalProduct_New_Set);	
		
$WooDecimalProduct_Options_New = array(
	'min' => $WooDecimalProduct_New_Min,
	'max' => $WooDecimalProduct_New_Max,
	'step' => $WooDecimalProduct_New_Step,
	'default' => $WooDecimalProduct_New_Set,
	'buttons_pm_product' => $WooDecimalProduct_New_ButtonsPM_ProductEnable,
	'buttons_pm_cart' => $WooDecimalProduct_New_ButtonsPM_CartEnable,
	'auto_correction' => $WooDecimalProduct_New_AutoCorrection,
	'ajax_cart_update' => $WooDecimalProduct_New_AJAX_CartUpdate,
	'price_unit_labale' => $WooDecimalProduct_New_PriceUnitLabel,
	'console_log_debuging' => $WooDecimalProduct_New_ConsoleLogDebuging,
	'uninstall_del_metadata' => $WooDecimalProduct_New_UninstallDel_MetaData,
	'storage_type' => $WooDecimalProduct_New_StorageType,
	'rss_feed_link' => $WooDecimalProduct_RSS_Feed_Link,
	'rss_feed_slug' => $WooDecimalProduct_RSS_Feed_Slug,
);
WooDecimalProduct_Debugger ($WooDecimalProduct_Options_New, '$WooDecimalProduct_Options_New', $WooDecimalProduct_Debug_Process, __FUNCTION__, __LINE__);

$WooDecimalProduct_Input_Parameters = array();

$WooDecimalProduct_Input_Parameters['new_min'] 	= $WooDecimalProduct_New_Min;
$WooDecimalProduct_Input_Parameters['new_max'] 	= $WooDecimalProduct_New_Max;
$WooDecimalProduct_Input_Parameters['new_step'] 	= $WooDecimalProduct_New_Step;
$WooDecimalProduct_Input_Parameters['new_set'] 	= $WooDecimalProduct_New_Set;

$WooDecimalProduct_Checked_Input_Parameters = WooDecimalProduct_Check_Input_Parameters ($WooDecimalProduct_Input_Parameters);
WooDecimalProduct_Debugger ($WooDecimalProduct_Checked_Input_Parameters, '$WooDecimalProduct_Checked_Input_Parameters', $WooDecimalProduct_Debug_Process, __FUNCTION__, __LINE__);

$WooDecimalProduct_New_Min 	= $WooDecimalProduct_Checked_Input_Parameters['new_min'];
$WooDecimalProduct_New_Max 	= $WooDecimalProduct_Checked_Input_Parameters['new_max'];
$WooDecimalProduct_New_Step = $WooDecimalProduct_Checked_Input_Parameters['new_step'];
$WooDecimalProduct_New_Set 	= $WooDecimalProduct_Checked_Input_Parameters['new_set'];

$WooDecimalProduct_Errors_Msg = $WooDecimalProduct_Checked_Input_Parameters['errors_msg'];
		
// Обновление Настроек.
if ($WooDecimalProduct_New_Min != WooDecimalProduct_Normalize_Number ( $WooDecimalProduct_Min_Quantity_Default )) {
	$WooDecimalProduct_Min_Quantity_Default = WooDecimalProduct_DecimalValueFormatting( $WooDecimalProduct_New_Min );
	update_option('woodecimalproduct_min_qnt_default', $WooDecimalProduct_New_Min);
}		

if ($WooDecimalProduct_New_Max != WooDecimalProduct_Normalize_Number ( $WooDecimalProduct_Max_Quantity_Default )) {
	$WooDecimalProduct_Max_Quantity_Default = WooDecimalProduct_DecimalValueFormatting( $WooDecimalProduct_New_Max );
	update_option('woodecimalproduct_max_qnt_default', $WooDecimalProduct_New_Max);
}	
	
if ($WooDecimalProduct_New_Step != WooDecimalProduct_Normalize_Number ( $WooDecimalProduct_Step_Quantity_Default )) {
	$WooDecimalProduct_Step_Quantity_Default = WooDecimalProduct_DecimalValueFormatting( $WooDecimalProduct_New_Step );
	update_option('woodecimalproduct_step_qnt_default', $WooDecimalProduct_New_Step);
}

if ($WooDecimalProduct_New_Set != WooDecimalProduct_Normalize_Number ( $WooDecimalProduct_Item_Quantity_Default )) {
	$WooDecimalProduct_Item_Quantity_Default = WooDecimalProduct_DecimalValueFormatting( $WooDecimalProduct_New_Set );
	update_option('woodecimalproduct_item_qnt_default', $WooDecimalProduct_New_Set);
}		
		
		if ($WooDecimalProduct_New_ButtonsPM_ProductEnable != $WooDecimalProduct_ButtonsPM_Product_Enable) {
			$WooDecimalProduct_ButtonsPM_Product_Enable = $WooDecimalProduct_New_ButtonsPM_ProductEnable;
			update_option('woodecimalproduct_buttonspm_product_enable', $WooDecimalProduct_ButtonsPM_Product_Enable);
		}
		
		if ($WooDecimalProduct_New_ButtonsPM_CartEnable != $WooDecimalProduct_ButtonsPM_Cart_Enable) {
			$WooDecimalProduct_ButtonsPM_Cart_Enable = $WooDecimalProduct_New_ButtonsPM_CartEnable;
			update_option('woodecimalproduct_buttonspm_cart_enable', $WooDecimalProduct_ButtonsPM_Cart_Enable);
		}		

		if ($WooDecimalProduct_New_AutoCorrection != $WooDecimalProduct_Auto_Correction_Quantity) {
			$WooDecimalProduct_Auto_Correction_Quantity = $WooDecimalProduct_New_AutoCorrection;
			update_option('woodecimalproduct_auto_correction_qnt', $WooDecimalProduct_Auto_Correction_Quantity);
		}
		
		if ($WooDecimalProduct_New_AJAX_CartUpdate != $WooDecimalProduct_AJAX_Cart_Update) {
			$WooDecimalProduct_AJAX_Cart_Update = $WooDecimalProduct_New_AJAX_CartUpdate;
			update_option('woodecimalproduct_ajax_cart_update', $WooDecimalProduct_AJAX_Cart_Update);
		}

		if ($WooDecimalProduct_New_PriceUnitLabel != $WooDecimalProduct_Price_Unit_Label) {
			$WooDecimalProduct_Price_Unit_Label = $WooDecimalProduct_New_PriceUnitLabel;
			update_option('woodecimalproduct_price_unit_label', $WooDecimalProduct_Price_Unit_Label);
		}

		if ($WooDecimalProduct_New_ConsoleLogDebuging != $WooDecimalProduct_ConsoleLog_Debuging) {
			$WooDecimalProduct_ConsoleLog_Debuging = $WooDecimalProduct_New_ConsoleLogDebuging;
			update_option('woodecimalproduct_debug_log', $WooDecimalProduct_ConsoleLog_Debuging);
		}

		if ($WooDecimalProduct_New_UninstallDel_MetaData != $WooDecimalProduct_Uninstall_Del_MetaData) {
			$WooDecimalProduct_Uninstall_Del_MetaData = $WooDecimalProduct_New_UninstallDel_MetaData;
			update_option('woodecimalproduct_uninstall_del_metadata', $WooDecimalProduct_Uninstall_Del_MetaData);
		}

		if ($WooDecimalProduct_New_StorageType != $WooDecimalProduct_StorageType) {
			$WooDecimalProduct_StorageType = $WooDecimalProduct_New_StorageType;
			update_option('woodecimalproduct_storage_type', $WooDecimalProduct_StorageType);
		}		
	}	
?>
	<div class="wrap">
		<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
		<hr>	
		
		<?php
if ($WooDecimalProduct_Errors_Msg) {
	WooDecimalProduct_Check_AdminNotice_AboutCorrection( $WooDecimalProduct_Screen = null, $WooDecimalProduct_Errors_Msg );
}
		?>
		
		<div class="wdpq_options_box">
			<div style="margin-top: 10px;">
				<?php echo esc_html( __('* Each Product and each Category can set a custom value.', 'decimal-product-quantity-for-woocommerce') ); ?>
			</div>	
			
			<form name="form_wdpq_Options" method="post" style="margin-top: 20px;">			
				<div style="margin-left: 20px; margin-bottom: 10px;">					
					<div style="margin-top: 10px;">
						<h3><?php echo esc_html( __('General:', 'decimal-product-quantity-for-woocommerce') ); ?></h3>
					</div>					
					
					<table class="form-table" style="margin-left: 20px;">
						<tbody>
							<tr>
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_min_qnt_default">
										<?php echo esc_html( __('Min Quantity', 'decimal-product-quantity-for-woocommerce') ); ?>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input id="wdpq_min_qnt_default" name="wdpq_min_qnt_default" type="text" style="width: 64px; text-align: center;" value="<?php echo esc_attr($WooDecimalProduct_Min_Quantity_Default); ?>">
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('How min-much quantity of product to cart', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
									<p class="wdpq_options_field_helptip">
										<?php echo esc_html( __('1 or 0.1 or 0.25 or 1.5 etc.', 'decimal-product-quantity-for-woocommerce') ); ?>
									</p>
								</td>
							</tr>
							
							<tr>
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_max_qnt_default">
										<?php echo esc_html( __('Max Quantity', 'decimal-product-quantity-for-woocommerce') ); ?>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input id="wdpq_max_qnt_default" name="wdpq_max_qnt_default" type="text" style="width: 64px; text-align: center;" value="<?php echo esc_attr($WooDecimalProduct_Max_Quantity_Default); ?>">
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('How max-much quantity of product to cart', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
									<p class="wdpq_options_field_helptip">
										<?php echo esc_html( __('1 or 0.1 or 0.25 or 1.5 etc. Or leave blank', 'decimal-product-quantity-for-woocommerce') ); ?>
									</p>
								</td>
							</tr>
							
							<tr>
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_step_qnt_default">
										<?php echo esc_html( __('Step change Quantity', 'decimal-product-quantity-for-woocommerce') ); ?>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input id="wdpq_step_qnt_default" name="wdpq_step_qnt_default" type="text" style="width: 64px; text-align: center;" value="<?php echo esc_attr($WooDecimalProduct_Step_Quantity_Default); ?>">
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('How much to increase or decrease the quantity of product to cart', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
									<p class="wdpq_options_field_helptip">
										<?php echo esc_html( __('1 or 0.1 or 0.25 or 1.5 etc.', 'decimal-product-quantity-for-woocommerce') ); ?>
									</p>
								</td>
							</tr>
							
							<tr>
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_set_qnt_default">
										<?php echo esc_html( __('Default set Quantity', 'decimal-product-quantity-for-woocommerce') ); ?>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input id="wdpq_set_qnt_default" name="wdpq_set_qnt_default" type="text" style="width: 64px; text-align: center;" value="<?php echo esc_attr($WooDecimalProduct_Item_Quantity_Default); ?>">
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('How much default quantity of product to cart', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
									<p class="wdpq_options_field_helptip">
										<?php echo esc_html( __('1 or 0.1 or 0.25 or 1.5 etc.', 'decimal-product-quantity-for-woocommerce') ); ?>
									</p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>	
					
				<div style="margin-top: 10px; margin-bottom: 20px;">	
					<hr>
					<div style="margin-top: 10px;">
						<h3><?php echo esc_html( __('Buttons [Plus / Minus]:', 'decimal-product-quantity-for-woocommerce') ); ?></h3>
					</div>
					
					<table class="form-table" style="margin-left: 20px;">
						<tbody>	
							<tr>
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_buttons_pm_product_enable">
										<?php echo esc_html( __('Product', 'decimal-product-quantity-for-woocommerce') ); ?>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input id="wdpq_buttons_pm_product_enable" name="wdpq_buttons_pm_product_enable" type="checkbox" <?php if($WooDecimalProduct_ButtonsPM_Product_Enable) {echo 'checked';} ?>>
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('Enable Buttons [ + / - ] for Product', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
								</td>									
								</th>
							</tr>

							<tr>
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_buttons_pm_cart_enable">
										<?php echo esc_html( __('Cart', 'decimal-product-quantity-for-woocommerce') ); ?>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input id="wdpq_buttons_pm_cart_enable" name="wdpq_buttons_pm_cart_enable" type="checkbox" <?php if($WooDecimalProduct_ButtonsPM_Cart_Enable) {echo 'checked';} ?>>
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('Enable Buttons [ + / - ] for Cart', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
								</td>									
								</th>
							</tr>							
						</tbody>
					</table>
				</div>						
				
				<div style="margin-top: 10px; margin-bottom: 20px;">				
					<div style="margin-top: 10px;">
						<hr>
						<h3><?php echo esc_html( __('Advanced:', 'decimal-product-quantity-for-woocommerce') ); ?></h3>
					</div>					

					<table class="form-table" style="margin-left: 20px;">
						<tbody>	
							<tr>
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_price_unit_label">
										<?php echo esc_html( __('Price Unit-Label', 'decimal-product-quantity-for-woocommerce') ); ?>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input id="wdpq_price_unit_label" name="wdpq_price_unit_label" type="checkbox" <?php if($WooDecimalProduct_Price_Unit_Label) {echo 'checked';} ?>>
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('Enable "Price Unit-Label" (Kg, Liter, Meter, Piece, etc.) On/Off', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
								</td>
							</tr>
							
							<tr>
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_auto_correction">
										<?php echo esc_html( __('Auto correction', 'decimal-product-quantity-for-woocommerce') ); ?>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input id="wdpq_auto_correction" name="wdpq_auto_correction" type="checkbox" <?php if($WooDecimalProduct_Auto_Correction_Quantity) {echo 'checked';} ?>>
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('Correction "No valid Value" customer enters to nearest valid value Quantity. On/Off', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
								</td>
							</tr>
							
							<tr>
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_ajax_cart_update">
										<?php echo esc_html( __('Auto update Cart', 'decimal-product-quantity-for-woocommerce') ); ?>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input id="wdpq_ajax_cart_update" name="wdpq_ajax_cart_update" type="checkbox" <?php if($WooDecimalProduct_AJAX_Cart_Update) {echo 'checked';} ?>>
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('Auto update Cart if change Quantity (AJAX Cart Update) On/Off', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
								</td>
							</tr>	
						</tbody>
					</table>
				</div>

				<div style="margin-top: 10px; margin-bottom: 20px;">				
					<div style="margin-top: 10px;">
						<hr>
						<h3><?php echo esc_html( __('RSS:', 'decimal-product-quantity-for-woocommerce') ); ?></h3>
					</div>
					
					<table class="form-table" style="margin-left: 20px;">
						<tbody>	
							<tr class="wdpq_options_field_vpro">
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_rss_feed_enable">
										<?php echo esc_html( __('WooCommerce RSS Feed', 'decimal-product-quantity-for-woocommerce') ); ?>
										<br>
										<span class="wdpq_options_field_vpro_about"><?php echo esc_html( __('* PRO Version only!', 'decimal-product-quantity-for-woocommerce') ); ?><span>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input disabled="true" id="wdpq_rss_feed_enable" name="wdpq_rss_feed_enable" type="checkbox" >
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('Enable WooCommerce RSS Feed. On/Off', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
									<p class="wdpq_options_field_helptip">
										<?php echo esc_html( __('Support: "Google Merchant Center" -> "Price_Unit_Label as [unit_pricing_measure]', 'decimal-product-quantity-for-woocommerce') ); ?>
									</p>									
								</td>
							</tr>

							<tr class="wdpq_options_field_vpro">
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_rss_feed_slug">
										<?php echo esc_html( __('Slug for WooCommerce RSS Feed', 'decimal-product-quantity-for-woocommerce') ); ?>
										<br>
										<span class="wdpq_options_field_vpro_about"><?php echo esc_html( __('* PRO Version only!', 'decimal-product-quantity-for-woocommerce') ); ?><span>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input disabled="true" id="wdpq_rss_feed_slug" name="wdpq_rss_feed_slug" type="text" style="width: 100px;" value="<?php echo esc_attr($WooDecimalProduct_RSS_Feed_Slug); ?>">
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('Link for RSS Feed. (Default: products)', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
									<p class="wdpq_options_field_helptip">
										<?php echo esc_html( __('RSS Feed:', 'decimal-product-quantity-for-woocommerce') ); ?>
										<?php echo '<a href="' .esc_url ($WooDecimalProduct_RSS_Feed_Link) .'" target="blank">' .esc_url ($WooDecimalProduct_RSS_Feed_Link) .'</a>'; ?>
									</p>
								</td>
							</tr>							
						</tbody>
					</table>
				</div>
				
				<div style="margin-top: 10px; margin-bottom: 20px;">
					<div style="margin-top: 10px;">
						<hr>
						<h3><?php echo esc_html( __('Debugging:', 'decimal-product-quantity-for-woocommerce') ); ?></h3>
					</div>	
					
					<table class="form-table" style="margin-left: 20px;">
						<tbody>								
							<tr>
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_debug_log">
										<?php echo esc_html( __('Debug info', 'decimal-product-quantity-for-woocommerce') ); ?>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input id="wdpq_debug_log" name="wdpq_debug_log" type="checkbox" <?php if($WooDecimalProduct_ConsoleLog_Debuging) {echo 'checked';} ?>>
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('View Debug info in Browser Console. On/Off', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
								</td>
							</tr>							
						</tbody>
					</table>
				</div>
				
				<div style="margin-top: 10px; margin-bottom: 20px;">
					<div style="margin-top: 10px;">
						<hr>
						<h3><?php echo esc_html( __('Cart Storage type:', 'decimal-product-quantity-for-woocommerce') ); ?></h3>
					</div>	
					
					<table class="form-table" style="margin-left: 20px;">
						<tbody>								
							<tr>
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_storage_type_local">
										<?php echo esc_html( __('System', 'decimal-product-quantity-for-woocommerce') ); ?>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input id="wdpq_storage_type_local" name="wdpq_storage_type" type="radio" value="system" <?php if($WooDecimalProduct_StorageType == 'system') {echo 'checked';} ?>>
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('Choose if Caching on Hosting is used. (Cart Data can be saved after the browser is closed).', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
								</td>
							</tr>
							
							<tr>							
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_storage_type_session">
										<?php echo esc_html( __('PHP Session', 'decimal-product-quantity-for-woocommerce') ); ?>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input id="wdpq_storage_type_session" name="wdpq_storage_type" type="radio" value="session" <?php if($WooDecimalProduct_StorageType == 'session') {echo 'checked';} ?>>
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('Choose if your users can use Public Terminals. (Cart Data deleted after the browser closed).', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
								</td>								
							</tr>	

							<tr>
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_storage_type_local">
										<?php echo esc_html( __('Local Storage', 'decimal-product-quantity-for-woocommerce') ); ?>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input disabled="true" id="wdpq_storage_type_local" name="wdpq_storage_type" type="radio" value="local" <?php if($WooDecimalProduct_StorageType == 'local') {echo 'checked';} ?>>
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('Experimental option. Testing. Inaccessible..', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
								</td>
							</tr>							
						</tbody>
					</table>
				</div>				
				
				<div style="margin-top: 10px; margin-bottom: 20px;">
					<div style="margin-top: 10px;">
						<hr>
						<h3><?php echo esc_html( __('Clearing:', 'decimal-product-quantity-for-woocommerce') ); ?></h3>
					</div>	
					
					<table class="form-table" style="margin-left: 20px;">
						<tbody>								
							<tr>
							<tr>
								<th scope="row" class="wdpq_options_field_label">
									<label for="wdpq_uninstall_del">
										<?php echo esc_html( __('Clearing', 'decimal-product-quantity-for-woocommerce') ); ?>
									</label>
								</th>
								<td class="wdpq_options_field_input">
									<input id="wdpq_uninstall_del" name="wdpq_uninstall_del" type="checkbox" <?php if($WooDecimalProduct_Uninstall_Del_MetaData) {echo 'checked';} ?>>
									<span class="wdpq_options_field_description">
										<?php echo esc_html( __('Delete Quantity-MetaData with Uninstall Plugin. On/Off', 'decimal-product-quantity-for-woocommerce') ); ?>
									</span>
								</td>
							</tr>							
							</tr>
						</tbody>
					</table>
				</div>
				
				<div style="margin-top: 10px; margin-bottom: 20px;">
					<div style="margin-top: 10px;">
						<hr>
						<h3 style="color: grey;"><?php echo esc_html( __('* Extentions:', 'decimal-product-quantity-for-woocommerce') ); ?></h3>
					</div>
					<div style="margin-top: 10px; margin-left: 20px;">
						<?php echo esc_html( __('With this plugin fully compatible Plugin for Composite Products: ', 'decimal-product-quantity-for-woocommerce') ); ?>
						"<a href='https://wpgear.xyz/wpgear-composite-products-woo' target="blank">WPGear Composite Products for WooCommerce</a>".
					</div>					
				</div>

				<hr>				

				<div style="margin-top: 10px; margin-right: 10px; padding-bottom: 10px; text-align: right;">
					<input id="btn_options_save" type="submit" class="button button-primary" value="<?php echo esc_html( __('Save', 'decimal-product-quantity-for-woocommerce') ); ?>">
				</div>
				<input id="action" name="action" type="hidden" value="Update">
				<input id="_wpnonce" name="_wpnonce" type="hidden" value="<?php echo esc_attr($WooDecimalProduct_Nonce); ?>">
			</form>
		</div>		
	</div>