<?php
/*
 * WPGear. Decimal Product Quantity for WooCommerce
 * options.php
 */
	
    if (!current_user_can('edit_dashboard')) {
        return;
    }	
	
	global $WooDecimalProduct_Plugin_URL;
	wp_enqueue_style ('wdpq_admin_style', $WooDecimalProduct_Plugin_URL .'includes/admin/admin-style.css'); // phpcs:ignore	
	
	$WooDecimalProduct_Nonce = 'Update_Options_DecimalProductQuantityForWooCommerce';
	$nonce = wp_create_nonce ($WooDecimalProduct_Nonce);	
	
	$WooDecimalProduct_Min_Quantity_Default    	= get_option ('woodecimalproduct_min_qnt_default', 1);  
	$WooDecimalProduct_Max_Quantity_Default    	= get_option ('woodecimalproduct_max_qnt_default', '');  
    $WooDecimalProduct_Step_Quantity_Default   	= get_option ('woodecimalproduct_step_qnt_default', 1); 
	$WooDecimalProduct_Item_Quantity_Default   	= get_option ('woodecimalproduct_item_qnt_default', 1);
	
	$WooDecimalProduct_ButtonsPM_Product_Enable	= get_option ('woodecimalproduct_buttonspm_product_enable', 0);
	$WooDecimalProduct_ButtonsPM_Cart_Enable	= get_option ('woodecimalproduct_buttonspm_cart_enable', 0);
	
	$WooDecimalProduct_Auto_Correction_Quantity	= get_option ('woodecimalproduct_auto_correction_qnt', 1);
	$WooDecimalProduct_AJAX_Cart_Update			= get_option ('woodecimalproduct_ajax_cart_update', 0);	
	$WooDecimalProduct_Price_Unit_Label			= get_option ('woodecimalproduct_price_unit_label', 0);	
	
	$WooDecimalProduct_ConsoleLog_Debuging		= get_option ('woodecimalproduct_debug_log', 0);
	$WooDecimalProduct_Uninstall_Del_MetaData 	= get_option ('woodecimalproduct_uninstall_del_metadata', 0);
	
	$WooDecimalProduct_RSS_Feed_Link = get_site_url() ."?feed=products";
	
	$Action 	= isset($_REQUEST['action']) ? sanitize_text_field (wp_unslash($_REQUEST['action'])) : null;
	$WP_Nonce 	= isset($_REQUEST['_wpnonce']) ? sanitize_text_field (wp_unslash($_REQUEST['_wpnonce'])) : 'none';
	
	$Errors_Msg = array();
	$WooDecimalProduct_RSS_Feed_Slug = 'products';
	
	if ($Action == 'Update') {
		if (!wp_verify_nonce($WP_Nonce, $WooDecimalProduct_Nonce)) {
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
		
		$New_WDPQ_Min	= isset($_REQUEST['wdpq_min_qnt_default']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_min_qnt_default'])) : 1;
		$New_WDPQ_Max	= isset($_REQUEST['wdpq_max_qnt_default']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_max_qnt_default'])) : '';
		$New_WDPQ_Step	= isset($_REQUEST['wdpq_step_qnt_default']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_step_qnt_default'])) : 1;
		$New_WDPQ_Set	= isset($_REQUEST['wdpq_set_qnt_default']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_set_qnt_default'])) : 1;
		
		$New_WDPQ_Auto_Correction 			= isset($_REQUEST['wdpq_auto_correction']) ? 1 : 0;
		$New_WDPQ_AJAX_Cart_Update 			= isset($_REQUEST['wdpq_ajax_cart_update']) ? 1 : 0;
		$New_WDPQ_Price_Unit_Label 			= isset($_REQUEST['wdpq_price_unit_label']) ? 1 : 0;
		$New_WDPQ_ButtonsPM_Product_Enable 	= isset($_REQUEST['wdpq_buttons_pm_product_enable']) ? 1 : 0;
		$New_WDPQ_ButtonsPM_Cart_Enable 	= isset($_REQUEST['wdpq_buttons_pm_cart_enable']) ? 1 : 0;
		
		$New_WDPQ_ConsoleLog_Debuging 		= isset($_REQUEST['wdpq_debug_log']) ? 1 : 0;
		$New_WDPQ_Uninstall_Del_MetaData	= isset($_REQUEST['wdpq_uninstall_del']) ? 1 : 0;

		$New_WDPQ_Min 	= WooDecimalProduct_Normalize_Number ($New_WDPQ_Min);
		$New_WDPQ_Max 	= WooDecimalProduct_Normalize_Number ($New_WDPQ_Max);
		$New_WDPQ_Step 	= WooDecimalProduct_Normalize_Number ($New_WDPQ_Step);
		$New_WDPQ_Set 	= WooDecimalProduct_Normalize_Number ($New_WDPQ_Set);	
		
		// Проверка Значений
		if (! is_numeric ($New_WDPQ_Min)) {
			$Error_Msg 	= __('Min Quantity', 'decimal-product-quantity-for-woocommerce');
			if ($New_WDPQ_Min) {
				$Error_Msg .= ' (' .$New_WDPQ_Min .') - ';
			} else {
				$Error_Msg .= ' - ';
			}
			$Error_Msg .= __('Not a Valid Number. Set = 1', 'decimal-product-quantity-for-woocommerce');
			$Errors_Msg[] = $Error_Msg;
			
			$New_WDPQ_Min = 1;
		}
		if (! is_numeric ($New_WDPQ_Max)) {
			if ($New_WDPQ_Max != '') {
				$Error_Msg 	= __('Max Quantity', 'decimal-product-quantity-for-woocommerce');
				if ($New_WDPQ_Max) {
					$Error_Msg .= ' (' .$New_WDPQ_Max .') - ';
				} else {
					$Error_Msg .= ' - ';
				}					
				$Error_Msg .= __('Not a Valid Number. Set = empty', 'decimal-product-quantity-for-woocommerce');
				$Errors_Msg[] = $Error_Msg;
				
				$New_WDPQ_Max = '';
			}
		}
		if (! is_numeric ($New_WDPQ_Step)) {
			$Error_Msg 	= __('Step Quantity', 'decimal-product-quantity-for-woocommerce');
			if ($New_WDPQ_Step) {
				$Error_Msg .= ' (' .$New_WDPQ_Step .') - ';
			} else {
				$Error_Msg .= ' - ';
			}				
			$Error_Msg .= __('Not a Valid Number. Set = 1', 'decimal-product-quantity-for-woocommerce');
			$Errors_Msg[] = $Error_Msg;
			
			$New_WDPQ_Step = 1;
		}
		if (! is_numeric ($New_WDPQ_Set)) {
			$Error_Msg 	= __('Default set Quantity', 'decimal-product-quantity-for-woocommerce');
			if ($New_WDPQ_Set) {
				$Error_Msg .= ' (' .$New_WDPQ_Set .') - ';
			} else {
				$Error_Msg .= ' - ';
			}				
			$Error_Msg .= __('Not a Valid Number. Set = 1', 'decimal-product-quantity-for-woocommerce');
			$Errors_Msg[] = $Error_Msg;
			
			$New_WDPQ_Set = 1;
		}	

		// Проверка взаимосвязей.
		if ($New_WDPQ_Set < $New_WDPQ_Min) {
			$Error_Msg  = __('Default set Quantity', 'decimal-product-quantity-for-woocommerce');
			$Error_Msg .= ' (' .$New_WDPQ_Set .') < ';
			$Error_Msg .= __('Min Quantity', 'decimal-product-quantity-for-woocommerce');
			$Error_Msg .= ' (' .$New_WDPQ_Min .') ';
			$Errors_Msg[] = $Error_Msg;
			
			$Error_Msg  = __('Set = Min', 'decimal-product-quantity-for-woocommerce');		
			$Errors_Msg[] = $Error_Msg;
			
			$New_WDPQ_Set = $New_WDPQ_Min;
		}
		
		if ($New_WDPQ_Max && $New_WDPQ_Set > $New_WDPQ_Max) {
			$Error_Msg 	= __('Default set Quantity', 'decimal-product-quantity-for-woocommerce');
			$Error_Msg .= ' (' .$New_WDPQ_Set .') > ';
			$Error_Msg .= __('Max Quantity', 'decimal-product-quantity-for-woocommerce');
			$Error_Msg .= ' (' .$New_WDPQ_Max .') ';
			$Errors_Msg[] = $Error_Msg;
			
			$Error_Msg  = __('Set = Max', 'decimal-product-quantity-for-woocommerce');
			$Errors_Msg[] = $Error_Msg;
			
			$New_WDPQ_Set = $New_WDPQ_Max;
		}	
		
		if ($New_WDPQ_Max && $New_WDPQ_Step > $New_WDPQ_Max) {
			$Error_Msg 	= __('Step change Quantity', 'decimal-product-quantity-for-woocommerce');
			$Error_Msg .= ' (' .$New_WDPQ_Step .') > ';
			$Error_Msg .= __('Max Quantity', 'decimal-product-quantity-for-woocommerce');
			$Error_Msg .= ' (' .$New_WDPQ_Max .') ';
			$Errors_Msg[] = $Error_Msg;
			
			$Error_Msg  = __('Set = Default', 'decimal-product-quantity-for-woocommerce');			
			$Errors_Msg[] = $Error_Msg;
			
			$New_WDPQ_Step = $New_WDPQ_Set;
		}		
		
		// Обновление Настроек.
		if ($New_WDPQ_Min != $WooDecimalProduct_Min_Quantity_Default) {
			$WooDecimalProduct_Min_Quantity_Default = $New_WDPQ_Min;
			update_option('woodecimalproduct_min_qnt_default', $WooDecimalProduct_Min_Quantity_Default);
		}		
		
		if ($New_WDPQ_Max != $WooDecimalProduct_Max_Quantity_Default) {
			$WooDecimalProduct_Max_Quantity_Default = $New_WDPQ_Max;
			update_option('woodecimalproduct_max_qnt_default', $WooDecimalProduct_Max_Quantity_Default);
		}	
			
		if ($New_WDPQ_Step != $WooDecimalProduct_Step_Quantity_Default) {
			$WooDecimalProduct_Step_Quantity_Default = $New_WDPQ_Step;
			update_option('woodecimalproduct_step_qnt_default', $WooDecimalProduct_Step_Quantity_Default);
		}
		
		if ($New_WDPQ_Set != $WooDecimalProduct_Item_Quantity_Default) {
			$WooDecimalProduct_Item_Quantity_Default = $New_WDPQ_Set;
			update_option('woodecimalproduct_item_qnt_default', $WooDecimalProduct_Item_Quantity_Default);
		}		
		
		if ($New_WDPQ_ButtonsPM_Product_Enable != $WooDecimalProduct_ButtonsPM_Product_Enable) {
			$WooDecimalProduct_ButtonsPM_Product_Enable = $New_WDPQ_ButtonsPM_Product_Enable;
			update_option('woodecimalproduct_buttonspm_product_enable', $WooDecimalProduct_ButtonsPM_Product_Enable);
		}
		
		if ($New_WDPQ_ButtonsPM_Cart_Enable != $WooDecimalProduct_ButtonsPM_Cart_Enable) {
			$WooDecimalProduct_ButtonsPM_Cart_Enable = $New_WDPQ_ButtonsPM_Cart_Enable;
			update_option('woodecimalproduct_buttonspm_cart_enable', $WooDecimalProduct_ButtonsPM_Cart_Enable);
		}		

		if ($New_WDPQ_Auto_Correction != $WooDecimalProduct_Auto_Correction_Quantity) {
			$WooDecimalProduct_Auto_Correction_Quantity = $New_WDPQ_Auto_Correction;
			update_option('woodecimalproduct_auto_correction_qnt', $WooDecimalProduct_Auto_Correction_Quantity);
		}
		
		if ($New_WDPQ_AJAX_Cart_Update != $WooDecimalProduct_AJAX_Cart_Update) {
			$WooDecimalProduct_AJAX_Cart_Update = $New_WDPQ_AJAX_Cart_Update;
			update_option('woodecimalproduct_ajax_cart_update', $WooDecimalProduct_AJAX_Cart_Update);
		}

		if ($New_WDPQ_Price_Unit_Label != $WooDecimalProduct_Price_Unit_Label) {
			$WooDecimalProduct_Price_Unit_Label = $New_WDPQ_Price_Unit_Label;
			update_option('woodecimalproduct_price_unit_label', $WooDecimalProduct_Price_Unit_Label);
		}

		if ($New_WDPQ_ConsoleLog_Debuging != $WooDecimalProduct_ConsoleLog_Debuging) {
			$WooDecimalProduct_ConsoleLog_Debuging = $New_WDPQ_ConsoleLog_Debuging;
			update_option('woodecimalproduct_debug_log', $WooDecimalProduct_ConsoleLog_Debuging);
		}

		if ($New_WDPQ_Uninstall_Del_MetaData != $WooDecimalProduct_Uninstall_Del_MetaData) {
			$WooDecimalProduct_Uninstall_Del_MetaData = $New_WDPQ_Uninstall_Del_MetaData;
			update_option('woodecimalproduct_uninstall_del_metadata', $WooDecimalProduct_Uninstall_Del_MetaData);
		}		
	}	
?>
	<div class="wrap">
		<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
		<hr>	
		
		<?php
		if ($Errors_Msg) {
			echo "<div style='margin-bottom: 20px; border-style: solid; border-width: 1px; border-radius: 5px; padding: 10px; background: white; border-color: grey;'>";
			echo esc_html( __('Warning!', 'decimal-product-quantity-for-woocommerce') );
			echo "<ul style='color: red; list-style-type: circle; margin-left: 20px;'>";
			 
			foreach ($Errors_Msg as $Error_Item) {
				echo "<li>" .esc_html ($Error_Item) ."</li>";
			}

			echo "</ul>";
			echo esc_html( __('Check this Settings.', 'decimal-product-quantity-for-woocommerce') );
			echo "</div>";
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

				<hr>				

				<div style="margin-top: 10px; margin-right: 10px; padding-bottom: 10px; text-align: right;">
					<input id="btn_options_save" type="submit" class="button button-primary" value="<?php echo esc_html( __('Save', 'decimal-product-quantity-for-woocommerce') ); ?>">
				</div>
				<input id="action" name="action" type="hidden" value="Update">
				<input id="_wpnonce" name="_wpnonce" type="hidden" value="<?php echo esc_attr($nonce); ?>">
			</form>
		</div>		
	</div>