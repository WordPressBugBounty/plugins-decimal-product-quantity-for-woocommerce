<?php
/*
 * Decimal Product Quantity for WooCommerce
 * Admin Product-Category Setup Page.
 * admin_setup_category.php
 */
 
	/* DashBoard. WooCommerce. List Product-Categories. 
	 * Добавляем новые Колонки в Списке Категорий Товаров.
	----------------------------------------------------------------- */	
	add_filter ('manage_edit-product_cat_columns', 'WooDecimalProduct_filter_manage_edit_product_cat_columns');
	function WooDecimalProduct_filter_manage_edit_product_cat_columns ($Columns) {
			$New_Columns = array();
			
			foreach ($Columns as $column_name => $column_info) {
				$New_Columns [$column_name] = $column_info;
				
				if ($column_name == 'name' ) {
					$New_Columns['quantity'] = __('Quantity', 'decimal-product-quantity-for-woocommerce');
				}			
			}		
			
			return $New_Columns;	
		
		return $Columns;
	}
	
	/* DashBoard. WooCommerce. List Product-Categories.
	 * Заполняем новые Колонки в Списке Категорий Товаров.
	----------------------------------------------------------------- */
	add_action ('manage_product_cat_custom_column', 'WooDecimalProduct_action_manage_product_cat_custom_column', 10, 3);
	function WooDecimalProduct_action_manage_product_cat_custom_column ($columns, $column, $Term_ID) {
		if ($column == 'quantity') {
			$No_MaxEmpty = '---';

			$Term_QuantityData = WooDecimalProduct_Get_Term_QuantityData_by_TermID ($Term_ID, $No_MaxEmpty);
			
$Min_Qnt = WooDecimalProduct_DecimalValueFormatting( $Term_QuantityData['min_qnt'] );
$Max_Qnt = WooDecimalProduct_DecimalValueFormatting( $Term_QuantityData['max_qnt'] );
$Stp_Qnt = WooDecimalProduct_DecimalValueFormatting( $Term_QuantityData['stp_qnt'] );
$Def_Qnt = WooDecimalProduct_DecimalValueFormatting( $Term_QuantityData['def_qnt'] );			

			if ($Term_QuantityData) {
				echo esc_html( __('Min:', 'decimal-product-quantity-for-woocommerce') ) .' ' .esc_html ($Min_Qnt) ."<br>";
				echo esc_html( __('Max:', 'decimal-product-quantity-for-woocommerce') ) .' ' .esc_html ($Max_Qnt) ."<br>";
				echo esc_html( __('Step:', 'decimal-product-quantity-for-woocommerce') ) .' ' .esc_html ($Stp_Qnt) ."<br>";
				echo esc_html( __('Set:', 'decimal-product-quantity-for-woocommerce') ) .' ' .esc_html ($Def_Qnt) ."<br>";
			}
		}
	}	

	/* DashBoard. Products-Categories Setup Page. Read.
	 * Опция "Price Unit-Label"
	----------------------------------------------------------------- */
	add_action ('product_cat_add_form_fields', 'WooDecimalProduct_action_product_cat_edit_form_fields', 9999);
	add_action ('product_cat_edit_form_fields', 'WooDecimalProduct_action_product_cat_edit_form_fields', 9999);	
	function WooDecimalProduct_action_product_cat_edit_form_fields ($term) {
		$debug_process = 'product_cat_edit_form_fields';
		
		WooDecimalProduct_Debugger ($term, '$term', $debug_process, __FUNCTION__, __LINE__);
		WooDecimalProduct_Debugger ($_REQUEST, '$_REQUEST', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore	

$WooDecimalProduct_Admin_Notice = get_option ('woodecimalproduct_admin_notice', '');
WooDecimalProduct_Debugger ($WooDecimalProduct_Admin_Notice, '$WooDecimalProduct_Admin_Notice', $debug_process, __FUNCTION__, __LINE__);
		
		$WooDecimalProduct_Min_Quantity_Default    	= get_option ('woodecimalproduct_min_qnt_default', 1);  
		$WooDecimalProduct_Step_Quantity_Default   	= get_option ('woodecimalproduct_step_qnt_default', 1); 
		$WooDecimalProduct_Item_Quantity_Default   	= get_option ('woodecimalproduct_item_qnt_default', 1);
		$WooDecimalProduct_Max_Quantity_Default    	= get_option ('woodecimalproduct_max_qnt_default', '');
		$WooDecimalProduct_Price_Unit_Label			= get_option ('woodecimalproduct_price_unit_label', 0);
		$WooDecimalProduct_RSS_Feed_Enable = false;
		
$WooDecimalProduct_Min_Quantity_Default    	= WooDecimalProduct_DecimalValueFormatting( $WooDecimalProduct_Min_Quantity_Default );
$WooDecimalProduct_Max_Quantity_Default    	= WooDecimalProduct_DecimalValueFormatting( $WooDecimalProduct_Max_Quantity_Default );
$WooDecimalProduct_Step_Quantity_Default    = WooDecimalProduct_DecimalValueFormatting( $WooDecimalProduct_Step_Quantity_Default );
$WooDecimalProduct_Item_Quantity_Default    = WooDecimalProduct_DecimalValueFormatting( $WooDecimalProduct_Item_Quantity_Default );		
		
		$Style_and_Title_PriceUnitLabel = '';
		$Style_and_Title_RSSFeedEnable = "style='display:inline-block;'";
		
		if (! $WooDecimalProduct_Price_Unit_Label) {
			$Title = __('Disabled in Global Option.', 'decimal-product-quantity-for-woocommerce');
			$Style_and_Title_PriceUnitLabel = "style='cursor:help; color:red;' title='$Title'";
		}

		if (! $WooDecimalProduct_RSS_Feed_Enable) {
			$Title = __('Disabled in Global Option.', 'decimal-product-quantity-for-woocommerce');
			$Style_and_Title_RSSFeedEnable = "style='display:inline-block; cursor:help; color:red;' title='$Title'";
		}	

		$WooDecimalProduct_Plugin_URL = plugin_dir_url ( __FILE__ ); // со слэшем на конце

		$Plugin_Data = get_plugin_data( __FILE__ );
		$WooDecimalProduct_Plugin_Version = $Plugin_Data['Version'];

		wp_enqueue_style ('wdpq_admin_style', $WooDecimalProduct_Plugin_URL .'admin-style.css', array(), $WooDecimalProduct_Plugin_Version); // phpcs:ignore 
				
		if ($term == 'product_cat') {
			// Mode: "Add new category"
			ob_start();
				?>
				<div style='margin-top: 40px; margin-top: 40px; border-style: solid; border-width: thin; border-radius: 5px; padding: 5px; margin-bottom: 20px;'>
					<h2><?php echo esc_html( __('Decimal Quantity', 'decimal-product-quantity-for-woocommerce') ); ?></h2>
					<p class="description"><?php echo esc_html( __('This Options will be shown for all Products in this Category.', 'decimal-product-quantity-for-woocommerce') ); ?></p>
					<p class="description"><?php echo esc_html( __('But each Product can have its own Options.', 'decimal-product-quantity-for-woocommerce') ); ?></p>
					
					<div style='margin-left: 20px; margin-bottom: 20px;'>						
						<hr>
						
						<div class='form-field term-price_unit'>
							<label for='wdpq_term_price_unit' <?php echo esc_html( $Style_and_Title_PriceUnitLabel ); ?>><?php echo esc_html( __('Price Unit-Label', 'decimal-product-quantity-for-woocommerce') ); ?></label>
							<input type="text" name="wdpq_term_price_unit" id="wdpq_term_price_unit" value="">
							<p class="description"><?php echo esc_html( __('Sample: "Price per Kg." / "Price per Meter". Or leave blank.', 'decimal-product-quantity-for-woocommerce') ); ?></p>
						</div>
						
						<div style='text-align: right; margin-right: 16px;'>
							<div class='form-field term-min-qnt' style='margin-bottom: 2px;'>
								<label for='wdpq_term_min_qnt' style='display: inline-block;'><?php echo esc_html( __('Min Quantity', 'decimal-product-quantity-for-woocommerce') ); ?></label>
								<input type="text" name="wdpq_term_min_qnt" id="wdpq_term_min_qnt" style="margin-left: 5px; width: 64px; text-align: center;" placeholder="<?php echo esc_attr($WooDecimalProduct_Min_Quantity_Default); ?>" value="">
							</div>
							
							<div class='form-field term-max-qnt' style='margin-top: 2px; margin-bottom: 2px;'>
								<label for='wdpq_term_max_qnt' style='display: inline-block;'><?php echo esc_html( __('Max Quantity', 'decimal-product-quantity-for-woocommerce') ); ?></label>
								<input type="text" name="wdpq_term_max_qnt" id="wdpq_term_max_qnt" style="margin-left: 5px; width: 64px; text-align: center;" placeholder="<?php echo esc_attr($WooDecimalProduct_Max_Quantity_Default); ?>" value="" >
							</div>

							<div class='form-field term-step-qnt' style='margin-top: 2px; margin-bottom: 2px;'>
								<label for='wdpq_term_step_qnt' style='display: inline-block;'><?php echo esc_html( __('Step Quantity', 'decimal-product-quantity-for-woocommerce') ); ?></label>
								<input type="text" name="wdpq_term_step_qnt" id="wdpq_term_step_qnt" style="margin-left: 5px; width: 64px; text-align: center;" placeholder="<?php echo esc_attr($WooDecimalProduct_Step_Quantity_Default); ?>" value="" >
							</div>
							
							<div class='form-field term-set-qnt' style='margin-top: 2px;'>
								<label for='wdpq_term_set_qnt' style='display: inline-block;'><?php echo esc_html( __('Default set Quantity', 'decimal-product-quantity-for-woocommerce') ); ?></label>
								<input type="text" name="wdpq_term_set_qnt" id="wdpq_term_set_qnt" style="margin-left: 5px; width: 64px; text-align: center;" placeholder="<?php echo esc_attr($WooDecimalProduct_Item_Quantity_Default); ?>" value="" >						
							</div>									
						</div>
						
						<hr>
						
						<div>
							<p class="description"><?php echo esc_html( __('Sample (Min,Max,Step,Set): 1 or 0.1 or 0.25 or 1.5 etc.', 'decimal-product-quantity-for-woocommerce') ); ?></p>
							<p class="description"><?php echo esc_html( __('or leave blank for ', 'decimal-product-quantity-for-woocommerce') ); ?><a target="_blank" href="/wp-admin/edit.php?post_type=product&page=decimal-product-quantity-for-woocommerce/includes/admin/options.php"><?php echo esc_html( __('General Settings', 'decimal-product-quantity-for-woocommerce') ); ?></a></p>
						</div>
						
						<hr>
						
						<div class='form-field'>
							<label for="wdpq_term_rss_feed_enable" <?php echo $Style_and_Title_RSSFeedEnable; // phpcs:ignore?>>
								<?php echo esc_html( __('RSS', 'decimal-product-quantity-for-woocommerce') ); ?>
							</label>
							<input type="checkbox" name="wdpq_term_rss_feed_enable" id="wdpq_term_rss_feed_enable" <?php if($WooDecimalProduct_RSS_Feed_Enable) {echo 'checked';} ?> <?php if(!$WooDecimalProduct_RSS_Feed_Enable) {echo 'disabled="true"';} ?>>
							<span class="wdpq_term_options_field_description">
								<?php echo esc_html( __('WooCommerce RSS Feed', 'decimal-product-quantity-for-woocommerce') ); ?>
							</span>	
							<br>
							<span class="wdpq_options_field_vpro_about"><?php echo esc_html( __('* PRO Version only!', 'decimal-product-quantity-for-woocommerce') ); ?><span>
						</div>
					</div>
				</div>			
				<?php

			$contents = ob_get_contents();
			ob_end_clean();
			
			echo $contents; // phpcs:ignore 	
			
		} else {
			// Mode: "Edit category"
			$Term_ID = $term -> term_id;

			$Term_Price_Unit 	= get_term_meta ($Term_ID, 'woodecimalproduct_term_price_unit', $single = true);
			
			$Term_Min_Qnt 		= get_term_meta ($Term_ID, 'woodecimalproduct_term_min_qnt', $single = true);			
			$Term_Max_Qnt 		= get_term_meta ($Term_ID, 'woodecimalproduct_term_max_qnt', $single = true);
			$Term_Step_Qnt 		= get_term_meta ($Term_ID, 'woodecimalproduct_term_step_qnt', $single = true);
			$Term_Set_Qnt 		= get_term_meta ($Term_ID, 'woodecimalproduct_term_item_qnt', $single = true);
			
$Term_Min_Qnt   = WooDecimalProduct_DecimalValueFormatting( $Term_Min_Qnt );
$Term_Max_Qnt   = WooDecimalProduct_DecimalValueFormatting( $Term_Max_Qnt );
$Term_Step_Qnt  = WooDecimalProduct_DecimalValueFormatting( $Term_Step_Qnt );
$Term_Set_Qnt   = WooDecimalProduct_DecimalValueFormatting( $Term_Set_Qnt );			
			
			ob_start();
				?>				
				<tr class='form-field wdpq-term-header'>
					<th scope='row' style="text-align: right;">
						<?php echo esc_html( __('Decimal Quantity', 'decimal-product-quantity-for-woocommerce') ); ?>
					</th>
					<td>
						<p class="description"><?php echo esc_html( __('This Options will be shown for all Products in this Category.', 'decimal-product-quantity-for-woocommerce') ); ?></p>
						<p class="description"><?php echo esc_html( __('But each Product can have its own Options.', 'decimal-product-quantity-for-woocommerce') ); ?></p>
					</td>
				</tr>
				
				<tr class='form-field wdpq-term-item wdpq-term-item-hr'>
					<td colspan="2">
						<hr>
					</td>
				</tr>

				<tr class='form-field wdpq-term-item'>
					<th scope='row'>
						<span <?php echo esc_html( $Style_and_Title_PriceUnitLabel ); ?>><?php echo esc_html( __('Price Unit-Label', 'decimal-product-quantity-for-woocommerce') ); ?></span>
					</th>
					<td>
						<input type="text" name="wdpq_term_price_unit" id="wdpq_term_price_unit" value="<?php echo esc_attr($Term_Price_Unit); ?>">
						<p class="description"><?php echo esc_html( __('Sample: "Price per Kg." / "Price per Meter". Or leave blank.', 'decimal-product-quantity-for-woocommerce') ); ?></p>						
					</td>
				</tr>

				<tr class='form-field wdpq-term-item wdpq-term-item-hr'>
					<td colspan="2">
						<hr>
					</td>
				</tr>				

				<tr class='form-field wdpq-term-item'>
					<th scope='row'>
						<?php echo esc_html( __('Min Quantity', 'decimal-product-quantity-for-woocommerce') ); ?></label>
					</th>
					<td>
						<input type="text" name="wdpq_term_min_qnt" id="wdpq_term_min_qnt" style="width: 64px; text-align: center;" placeholder="<?php echo esc_attr($WooDecimalProduct_Min_Quantity_Default); ?>" value="<?php echo esc_attr($Term_Min_Qnt); ?>">
					</td>
				</tr>	

				<tr class='form-field wdpq-term-item'>
					<th scope='row'>
						<?php echo esc_html( __('Max Quantity', 'decimal-product-quantity-for-woocommerce') ); ?></label>
					</th>
					<td>
						<input type="text" name="wdpq_term_max_qnt" id="wdpq_term_max_qnt" style="width: 64px; text-align: center;" placeholder="<?php echo esc_attr($WooDecimalProduct_Max_Quantity_Default); ?>" value="<?php echo esc_attr($Term_Max_Qnt); ?>">
					</td>
				</tr>	

				<tr class='form-field wdpq-term-item'>
					<th scope='row'>
						<?php echo esc_html( __('Step Quantity', 'decimal-product-quantity-for-woocommerce') ); ?></label>
					</th>
					<td>
						<input type="text" name="wdpq_term_step_qnt" id="wdpq_term_step_qnt" style="width: 64px; text-align: center;" placeholder="<?php echo esc_attr($WooDecimalProduct_Step_Quantity_Default); ?>" value="<?php echo esc_attr($Term_Step_Qnt); ?>">
					</td>
				</tr>	

				<tr class='form-field wdpq-term-item'>
					<th scope='row'>
						<?php echo esc_html( __('Default set Quantity', 'decimal-product-quantity-for-woocommerce') ); ?></label>
					</th>
					<td>
						<input type="text" name="wdpq_term_set_qnt" id="wdpq_term_set_qnt" style="width: 64px; text-align: center;" placeholder="<?php echo esc_attr($WooDecimalProduct_Item_Quantity_Default); ?>" value="<?php echo esc_attr($Term_Set_Qnt); ?>">
					</td>
				</tr>

				<tr class='form-field wdpq-term-item'>
					<th scope='row'>
						*
					</th>
					<td>
						<p class="description"><?php echo esc_html( __('Sample (Min,Max,Step,Set): 1 or 0.1 or 0.25 or 1.5 etc.', 'decimal-product-quantity-for-woocommerce') ); ?></p>
						<p class="description"><?php echo esc_html( __('or leave blank for ', 'decimal-product-quantity-for-woocommerce') ); ?><a target="_blank" href="/wp-admin/edit.php?post_type=product&page=decimal-product-quantity-for-woocommerce/includes/admin/options.php"><?php echo esc_html( __('General Settings', 'decimal-product-quantity-for-woocommerce') ); ?></a></p>
					</td>
				</tr>

				<tr class='form-field wdpq-term-item wdpq-term-item-hr'>
					<td colspan="2">
						<hr>
					</td>
				</tr>	

				<tr class='form-field wdpq-term-item'>
					<th scope='row' <?php echo $Style_and_Title_RSSFeedEnable; // phpcs:ignore?>>
						<?php echo esc_html( __('RSS', 'decimal-product-quantity-for-woocommerce') ); ?>
					</th>
					<td>
						<input type="checkbox" name="wdpq_term_rss_feed_enable" id="wdpq_term_rss_feed_enable" <?php if($WooDecimalProduct_RSS_Feed_Enable) {echo 'checked';} ?> <?php if(!$WooDecimalProduct_RSS_Feed_Enable) {echo 'disabled="true"';} ?>>
						<span class="wdpq_term_options_field_description">
							<?php echo esc_html( __('WooCommerce RSS Feed', 'decimal-product-quantity-for-woocommerce') ); ?>
						</span>
						<br>
						<span class="wdpq_options_field_vpro_about"><?php echo esc_html( __('* PRO Version only!', 'decimal-product-quantity-for-woocommerce') ); ?><span>
					</td>
				</tr>
				
				<tr class='form-field wdpq-term-footer'>
					<td colspan="2">

					</td>
				</tr>				
				
				<?php	
				
// Проверяем наличие Сообщения о Коррекции Значений.
if (! empty($WooDecimalProduct_Admin_Notice) ) {
	
	echo "<div style='margin-bottom: 20px; border-style: solid; border-width: 1px; border-radius: 5px; padding: 10px; background: white; border-color: grey;'>";
	echo esc_html( __('Warning!', 'decimal-product-quantity-for-woocommerce') );
	echo "<ul style='color: red; list-style-type: circle; margin-left: 20px;'>";
	 
	foreach ($WooDecimalProduct_Admin_Notice as $NoticeItem) {
		echo "<li>" .esc_html ($NoticeItem) ."</li>";
	}

	echo "</ul>";
	echo esc_html( __('Check this Settings.', 'decimal-product-quantity-for-woocommerce') );
	echo "</div>";	

	update_option('woodecimalproduct_admin_notice', '');
}				

			$contents = ob_get_contents();
			ob_end_clean();
			
			echo $contents; // phpcs:ignore 
		}
	}
	
	/* DashBoard. Products-Categories Setup Page. Save.
	 * Опция "Price Unit-Label"
	----------------------------------------------------------------- */
	add_action ('create_product_cat', 'WooDecimalProduct_action_edited_product_cat');
	add_action ('edited_product_cat', 'WooDecimalProduct_action_edited_product_cat');
	function WooDecimalProduct_action_edited_product_cat ($Term_ID) {
		$debug_process = 'edited_product_cat';
		
		$Term_Price_Unit 	= isset ($_POST['wdpq_term_price_unit'])? sanitize_text_field( wp_unslash( $_POST['wdpq_term_price_unit'] ) ): ''; // phpcs:ignore	
		
		$Term_Min_Qnt 		= isset ($_POST['wdpq_term_min_qnt'])? sanitize_text_field( wp_unslash( $_POST['wdpq_term_min_qnt'] ) ): ''; // phpcs:ignore	
		$Term_Max_Qnt 		= isset ($_POST['wdpq_term_max_qnt'])? sanitize_text_field( wp_unslash( $_POST['wdpq_term_max_qnt'] ) ): ''; // phpcs:ignore	
		$Term_Step_Qnt 		= isset ($_POST['wdpq_term_step_qnt'])? sanitize_text_field( wp_unslash( $_POST['wdpq_term_step_qnt'] ) ): ''; // phpcs:ignore	
		$Term_Set_Qnt 		= isset ($_POST['wdpq_term_set_qnt'])? sanitize_text_field( wp_unslash( $_POST['wdpq_term_set_qnt'] ) ): ''; // phpcs:ignore	
		
$WooDecimalProduct_CatOptions = array(
	'min' => $Term_Min_Qnt,
	'max' => $Term_Max_Qnt,
	'step' => $Term_Step_Qnt,
	'default' => $Term_Set_Qnt,
	'price_unit_labale' => $Term_Price_Unit,
);
WooDecimalProduct_Debugger ($WooDecimalProduct_CatOptions, '$WooDecimalProduct_CatOptions', $debug_process, __FUNCTION__, __LINE__);
		
		$Term_Min_Qnt 	= WooDecimalProduct_Normalize_Number ($Term_Min_Qnt);
		$Term_Max_Qnt 	= WooDecimalProduct_Normalize_Number ($Term_Max_Qnt);
		$Term_Step_Qnt 	= WooDecimalProduct_Normalize_Number ($Term_Step_Qnt);
		$Term_Set_Qnt 	= WooDecimalProduct_Normalize_Number ($Term_Set_Qnt);		
		
		
$Input_Parameters = array();

$Input_Parameters['new_min'] 	= $Term_Min_Qnt;
$Input_Parameters['new_max'] 	= $Term_Max_Qnt;
$Input_Parameters['new_step'] 	= $Term_Step_Qnt;
$Input_Parameters['new_set'] 	= $Term_Set_Qnt;

$WooDecimalProduct_Checked_Input_Parameters = WooDecimalProduct_Check_Input_Parameters ($Input_Parameters);
WooDecimalProduct_Debugger ($WooDecimalProduct_Checked_Input_Parameters, '$WooDecimalProduct_Checked_Input_Parameters', $WooDecimalProduct_Debug_Process, __FUNCTION__, __LINE__);

$Term_Min_Qnt 	= $WooDecimalProduct_Checked_Input_Parameters['new_min'];
$Term_Max_Qnt 	= $WooDecimalProduct_Checked_Input_Parameters['new_max'];
$Term_Step_Qnt 	= $WooDecimalProduct_Checked_Input_Parameters['new_step'];
$Term_Set_Qnt 	= $WooDecimalProduct_Checked_Input_Parameters['new_set'];

$WooDecimalProduct_Errors_Msg = $WooDecimalProduct_Checked_Input_Parameters['errors_msg'];

// Передаем Сообщения о Коррекции Значений в Админку.
// другими способами выводить непосредственно от сюда не получается.
if ( !empty($WooDecimalProduct_Errors_Msg) ) {	
	$WooDecimalProduct_Admin_Notice = array();
	
	foreach ( $WooDecimalProduct_Errors_Msg as $Error_Msg ) {
		$WooDecimalProduct_Admin_Notice[] = $Error_Msg;
	}
}
WooDecimalProduct_Debugger ($WooDecimalProduct_Admin_Notice, '$WooDecimalProduct_Admin_Notice', $debug_process, __FUNCTION__, __LINE__);
update_option('woodecimalproduct_admin_notice', $WooDecimalProduct_Admin_Notice);

$WooDecimalProduct_CatOptions = array(
	'min' => $Term_Min_Qnt,
	'max' => $Term_Max_Qnt,
	'step' => $Term_Step_Qnt,
	'default' => $Term_Set_Qnt,
	'price_unit_labale' => $Term_Price_Unit,
);
WooDecimalProduct_Debugger ($WooDecimalProduct_CatOptions, '$WooDecimalProduct_CatOptions', $debug_process, __FUNCTION__, __LINE__);
	
		update_term_meta ($Term_ID, 'woodecimalproduct_term_price_unit', $Term_Price_Unit); // phpcs:ignore	
		update_term_meta ($Term_ID, 'woodecimalproduct_term_min_qnt', $Term_Min_Qnt); // phpcs:ignore	
		update_term_meta ($Term_ID, 'woodecimalproduct_term_max_qnt', $Term_Max_Qnt); // phpcs:ignore	
		update_term_meta ($Term_ID, 'woodecimalproduct_term_step_qnt', $Term_Step_Qnt); // phpcs:ignore	
		update_term_meta ($Term_ID, 'woodecimalproduct_term_item_qnt', $Term_Set_Qnt); // phpcs:ignore	
	}