<?php
/*
 * Decimal Product Quantity for WooCommerce
 * Admin Product Setup Page.
 * admin_setup_product.php
 */

	/* DashBoard. WooCommerce. List Products. 
	 * Добавляем новые Колонки в Списке Товаров.
	----------------------------------------------------------------- */	
	add_filter ('manage_edit-product_columns', 'WooDecimalProduct_filter_manage_edit_product_columns');
	function WooDecimalProduct_filter_manage_edit_product_columns ($Columns) {		
		$New_Columns = array();
		
		foreach ($Columns as $column_name => $column_info) {
			$New_Columns [$column_name] = $column_info;
			
			if ($column_name == 'price' ) {
				$New_Columns['quantity'] = __('Quantity', 'decimal_product_quantity_for_woocommerce');
			}			
		}		
		
		return $New_Columns;
	}	
	
	/* DashBoard. WooCommerce. List Products.
	 * Заполняем новые Колонки в Списке Товаров.
	----------------------------------------------------------------- */
	add_action ('manage_product_posts_custom_column', 'WooDecimalProduct_action_manage_product_posts_custom_column', 10, 2);
	function WooDecimalProduct_action_manage_product_posts_custom_column ($Column, $Product_ID) {
		if ($Column == 'quantity') {
			$No_MaxEmpty = '---';
			$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID, $No_MaxEmpty);
			
			$Min_Qnt 		= $WooDecimalProduct_QuantityData['min_qnt'];
			$Max_Qnt 		= $WooDecimalProduct_QuantityData['max_qnt'];
			$Def_Qnt 		= $WooDecimalProduct_QuantityData['def_qnt'];
			$Stp_Qnt 		= $WooDecimalProduct_QuantityData['stp_qnt'];				
			$QNT_Precision 	= $WooDecimalProduct_QuantityData['precision'];		
				
			echo __('Min:', 'decimal_product_quantity_for_woocommerce') .' ' .esc_html($Min_Qnt) ."<br>";
			echo __('Max:', 'decimal_product_quantity_for_woocommerce') .' ' .esc_html($Max_Qnt) ."<br>";
			echo __('Step:', 'decimal_product_quantity_for_woocommerce') .' ' .esc_html($Stp_Qnt) ."<br>";
			echo __('Set:', 'decimal_product_quantity_for_woocommerce') .' ' .esc_html($Def_Qnt) ."<br>";			
		}
	}
	
	/* DashBoard. WooCommerce. List Products.
	 * Добавляем в Колонку "Price" -> "Pice_Unit_Label" в Списке Товаров.
	----------------------------------------------------------------- */	
	add_filter ('woocommerce_get_price_html', 'WooDecimalProduct_filter_get_price_html', 10, 2);	
	function WooDecimalProduct_filter_get_price_html ($price, $product) {		
		$WooDecimalProduct_Price_Unit_Label	= get_option ('woodecimalproduct_price_unit_label', 0);			
		
		if ($WooDecimalProduct_Price_Unit_Label) {
			global $pagenow;

			if ($pagenow == 'edit.php') {
				$Product_ID = $product -> get_id();
				
				$Pice_Unit_Label = WooDecimalProduct_Get_PiceUnitLabel_by_ProductID ($Product_ID);
				
				if ($Pice_Unit_Label) {
					$price .= "<br>$Pice_Unit_Label";
					
					return $price;			
				}				
			}			
		}

		return $price;
	}

	/* WooCommerce Product Setup Page. | Вкладка "General"
	 * Добавляем опции: "Mинимальное / Максимальное кол-во Товара, Шаг изменения кол-ва и Количество по-умалчанию".
	 * Если не указано, то будет как в Глобальных Настройках.
	----------------------------------------------------------------- */		
	add_action ('woocommerce_product_options_general_product_data', 'WooDecimalProduct_Tab_General_add_Options');
	function WooDecimalProduct_Tab_General_add_Options() {			
		$WooDecimalProduct_Price_Unit_Label			= get_option ('woodecimalproduct_price_unit_label', 0);		

		$Product_ID = get_the_ID();
			
		$No_MaxEmpty = '---';
		$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID, $No_MaxEmpty);

		$Min_Qnt 		= $WooDecimalProduct_QuantityData['min_qnt'];
		$Max_Qnt 		= $WooDecimalProduct_QuantityData['max_qnt'];
		$Def_Qnt 		= $WooDecimalProduct_QuantityData['def_qnt'];
		$Stp_Qnt 		= $WooDecimalProduct_QuantityData['stp_qnt'];				
		$QNT_Precision 	= $WooDecimalProduct_QuantityData['precision'];	

		$Product_Category_IDs = wc_get_product_term_ids ($Product_ID, 'product_cat');

		$Product_Price_Unit_Label = '';

		// Берем Значение "Price Unit Label" из первой значимой Категории если их несколько.
		foreach ($Product_Category_IDs as $Term_ID) {
			if ($Product_Price_Unit_Label == '') {
				$Term_Price_Unit = get_term_meta ($Term_ID, 'woodecimalproduct_term_price_unit', $single = true);

				if ($Term_Price_Unit) {
					$Product_Price_Unit_Label = $Term_Price_Unit;
				}		
			}
		}

		$Label_Price_Unit_Label = __('Price Unit-Label', 'decimal_product_quantity_for_woocommerce');
		
		$Style_and_Title = '';
		
		if (! $WooDecimalProduct_Price_Unit_Label) {
			$Title = __('Disabled in Global Option.', 'decimal_product_quantity_for_woocommerce');
			$Style_and_Title = "style='color:red;' title='$Title'";
			
			$Label_Price_Unit_Label = '<span ' .$Style_and_Title .'>' .$Label_Price_Unit_Label .'</span>';
		}	
	
		echo '<div class="options_group">';
			echo '<div style="margin-top: 10px; margin-left: 10px;">';
				echo '<span style="font-weight: bold;">';
					echo __('Quantity Options', 'decimal_product_quantity_for_woocommerce');
				echo '</span>';
				
				echo '<br>';
				
				echo '<div>';					
					$Note = __('* If not specified, it will be as in <a_admin_category>Categories</a> / <a_admin_global>Global Settings</a>', 'decimal_product_quantity_for_woocommerce');
					$Note = str_replace ('<a_admin_category>', '<a target="_blank" href="/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product">', $Note);
					$Note = str_replace ('<a_admin_global>', '<a target="_blank" href="/wp-admin/edit.php?post_type=product&page=decimal-product-quantity-for-woocommerce/includes/options.php">', $Note);
					
					echo $Note;
				echo '</div>';
			echo '</div>';
		
			woocommerce_wp_text_input( 
				array( 
					'id'          => 'woodecimalproduct_min_qnt', 
					'label'       => __('Minimum', 'decimal_product_quantity_for_woocommerce'), 
					'placeholder' => $Min_Qnt,
					'desc_tip'    => 'true',
					'description' => sprintf (__('Set the Min of changing the quantity: 0.1 0.5 100 e.t.c (Default = %s)', 'decimal_product_quantity_for_woocommerce'), $Min_Qnt)
				)
			);
			
			woocommerce_wp_text_input( 
				array( 
					'id'          => 'woodecimalproduct_item_qnt', 
					'label'       => __('Default Set', 'decimal_product_quantity_for_woocommerce'), 
					'placeholder' => $Def_Qnt,
					'desc_tip'    => 'true',
					'description' => sprintf (__('Set the Default quantity: 0.1 0.5 100 e.t.c (Default = %s)', 'decimal_product_quantity_for_woocommerce'), $Def_Qnt)
				)
			);		

			woocommerce_wp_text_input( 
				array( 
					'id'          => 'woodecimalproduct_step_qnt', 
					'label'       => __('Step change +/-', 'decimal_product_quantity_for_woocommerce'), 
					'placeholder' => $Stp_Qnt,
					'desc_tip'    => 'true',
					'description' => sprintf (__('Set the Step of changing the quantity: 0.1 0.5 100 e.t.c (Default = %s)', 'decimal_product_quantity_for_woocommerce'), $Stp_Qnt)
				)
			);

			woocommerce_wp_text_input( 
				array( 
					'id'          => 'woodecimalproduct_max_qnt', 
					'label'       => __('Maximum', 'decimal_product_quantity_for_woocommerce'), 
					'placeholder' => $Max_Qnt,
					'desc_tip'    => 'true',
					'description' => __('Set the Max of changing the quantity: 0.1 0.5 100 e.t.c (or leave blank)', 'decimal_product_quantity_for_woocommerce')
				)
			);
			
			woocommerce_wp_text_input( 
				array( 
					'id'          	=> 'woodecimalproduct_pice_unit_label', 
					'label'       	=> $Label_Price_Unit_Label, 
					'placeholder' 	=> $Product_Price_Unit_Label,
					'desc_tip'    	=> 'true',
					'description' => __('View Price Unit-Label. Sample: "Price per Kg." / "Price per Meter". Or leave blank to use Category value.', 'decimal_product_quantity_for_woocommerce')
				)
			);	

			woocommerce_wp_checkbox( 
				array( 
					'id'          	=> 'woodecimalproduct_pice_unit_disable', 
					'label'       	=> __('Disable Price Unit-Label', 'decimal_product_quantity_for_woocommerce'), 
					'desc_tip'    	=> 'true',
					'description' => __('Disable Price Unit-Label for this Product.', 'decimal_product_quantity_for_woocommerce')
				)
			);
			
			woocommerce_wp_checkbox( 
				array( 
					'id'          	=> 'woodecimalproduct_rss_feed_disable', 
					'label'       	=> __('Disable RSS Feed', 'decimal_product_quantity_for_woocommerce'), 
					'desc_tip'    	=> 'true',
					'description' => __('Disable RSS Feed for this Product.', 'decimal_product_quantity_for_woocommerce')
				)
			);

			echo '<div style="clear: both; margin-top: -20px; margin-left: 12px; color: red;">';
			echo '(' .__('* PRO Version only!', 'decimal_product_quantity_for_woocommerce') .')';
			echo '</div>';
		echo '</div>';    	
	} 
	
	/* Сохраняем "Опции кол-ва Товара" для данного Товара.
	----------------------------------------------------------------- */	
	add_action ('woocommerce_process_product_meta', 'WooDecimalProduct_save_product_field_step_Qnt');	
	function WooDecimalProduct_save_product_field_step_Qnt ($post_id) {	
        $new_min_Qnt    		= isset($_POST['woodecimalproduct_min_qnt']) ? sanitize_text_field (wp_unslash($_POST['woodecimalproduct_min_qnt'])): 1;
        $new_step_Qnt   		= isset($_POST['woodecimalproduct_step_qnt']) ? sanitize_text_field (wp_unslash($_POST['woodecimalproduct_step_qnt'])): 1;  
		$new_dft_Qnt    		= isset($_POST['woodecimalproduct_item_qnt']) ? sanitize_text_field (wp_unslash($_POST['woodecimalproduct_item_qnt'])): 1;
		$new_max_Qnt			= isset($_POST['woodecimalproduct_max_qnt']) ? sanitize_text_field (wp_unslash($_POST['woodecimalproduct_max_qnt'])): '';
		$new_Pice_Unit_Label	= isset($_POST['woodecimalproduct_pice_unit_label']) ? sanitize_text_field (wp_unslash($_POST['woodecimalproduct_pice_unit_label'])): '';
		$new_Pice_Unit_Disable	= isset($_POST['woodecimalproduct_pice_unit_disable']) ? 'yes': '';	
		
		update_post_meta ($post_id, 'woodecimalproduct_min_qnt', $new_min_Qnt);
		update_post_meta ($post_id, 'woodecimalproduct_step_qnt', $new_step_Qnt);
		update_post_meta ($post_id, 'woodecimalproduct_item_qnt', $new_dft_Qnt);	
		update_post_meta ($post_id, 'woodecimalproduct_max_qnt', $new_max_Qnt);		
		update_post_meta ($post_id, 'woodecimalproduct_pice_unit_label', $new_Pice_Unit_Label);	
		update_post_meta ($post_id, 'woodecimalproduct_pice_unit_disable', $new_Pice_Unit_Disable);	
	}

    /* Вариативный Товар. Админка. Шаг и Минимальное кол-во выбора Товара на странице Товара по каждой из Вариаций.
    ---*PRO-------------------------------------------------------------- */
	add_action ('woocommerce_product_after_variable_attributes', 'WooDecimalProductPro_quantity_product_after_variable_attributes', 10);
	function WooDecimalProductPro_quantity_product_after_variable_attributes () {
        global $WooDecimalProduct_Min_Quantity_Default;
		global $WooDecimalProduct_Step_Quantity_Default;
		global $WooDecimalProduct_Item_Quantity_Default;
		global $WooDecimalProduct_Max_Quantity_Default;
		
		ob_start();
		?>

		<div class="form-row form-row-full" style="background: aliceblue; padding: 10px; border-radius: 9px;">
			<div style="color: red;">
				<?php echo __('* PRO Version only!', 'decimal_product_quantity_for_woocommerce'); ?>
			</div>
			
			<p class="form-field">
				<label for="woodecimalproduct_options_variation_field_min_qnt">
					<?php echo __('Min cart Quantity (if the variation has a separate value)', 'decimal_product_quantity_for_woocommerce'); ?>
				</label>
				<input type="text" class="short" disabled="true" name="woodecimalproduct_options_variation_field_min_qnt" value="" placeholder="<?php echo esc_attr($WooDecimalProduct_Min_Quantity_Default);?>">
			</p>
			
			<p class="form-field">
				<label for="woodecimalproduct_options_variation_field_step_qnt">
					<?php echo __('Step change Quantity (if the variation has a separate value)', 'decimal_product_quantity_for_woocommerce'); ?>
				</label>
				<input type="text" class="short" disabled="true" name="woodecimalproduct_options_variation_field_step_qnt" value="" placeholder="<?php echo esc_attr($WooDecimalProduct_Step_Quantity_Default);?>">
			</p>

			<p class="form-field">
				<label for="woodecimalproduct_options_variation_field_default_qnt">
					<?php echo __('Default Quantity (if the variation has a separate value)', 'decimal_product_quantity_for_woocommerce'); ?>
				</label>
				<input type="text" class="short" disabled="true" name="woodecimalproduct_options_variation_field_default_qnt" value="" placeholder="<?php echo esc_attr($WooDecimalProduct_Item_Quantity_Default);?>">
			</p>	

			<p class="form-field">
				<label for="woodecimalproduct_options_variation_field_max_qnt">					
					<?php echo __('Max cart Quantity (if the variation has a separate value)', 'decimal_product_quantity_for_woocommerce'); ?>
				</label>
				<input type="text" class="short" disabled="true" name="woodecimalproduct_options_variation_field_max_qnt" value="" placeholder="<?php echo esc_attr($WooDecimalProduct_Max_Quantity_Default);?>">
			</p>
		</div>
		<?php
		$Buffer = ob_get_contents();		
		ob_end_clean();
		
		echo $Buffer; // phpcs:ignore 
	}
	
	/* Страница Настроек Товара.
	 * Stock Threshold. Thanks to: sammyblueeyes & kylie
	----------------------------------------------------------------- */	
	add_action ('woocommerce_before_product_object_save', 'WooDecimalProduct_before_product_object_save', 10, 2);
	function WooDecimalProduct_before_product_object_save ($product, $product_data_store) {
		// Do this check again, but don't cast the stock_quantity to int since that will mark values < 1 as "out of stock"
		// even if woocommerce_notify_no_stock_amount is set to 0.
		$stock_is_above_notification_threshold = ($product -> get_stock_quantity() > absint (get_option('woocommerce_notify_no_stock_amount', 0)));

		if ($stock_is_above_notification_threshold) {
			$new_stock_status = 'instock';
			$product -> set_stock_status ($new_stock_status);
		}
	}	