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
				$New_Columns['quantity'] = __('Quantity', 'decimal-product-quantity-for-woocommerce');
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
				
			echo esc_html( __('Min:', 'decimal-product-quantity-for-woocommerce') ) .' ' .esc_html($Min_Qnt) ."<br>";
			echo esc_html( __('Max:', 'decimal-product-quantity-for-woocommerce') ) .' ' .esc_html($Max_Qnt) ."<br>";
			echo esc_html( __('Step:', 'decimal-product-quantity-for-woocommerce') ) .' ' .esc_html($Stp_Qnt) ."<br>";
			echo esc_html( __('Set:', 'decimal-product-quantity-for-woocommerce') ) .' ' .esc_html($Def_Qnt) ."<br>";			
		}
	}
	
	/* DashBoard. WooCommerce. List Products.
	 * Добавляем в Колонку "Price" -> "Price_Unit_Label" в Списке Товаров.
	----------------------------------------------------------------- */	
	add_filter ('woocommerce_get_price_html', 'WooDecimalProduct_filter_get_price_html', 10, 2);	
	function WooDecimalProduct_filter_get_price_html ($price, $product) {		
		$WooDecimalProduct_Price_Unit_Label	= get_option ('woodecimalproduct_price_unit_label', 0);			
		
		if ($WooDecimalProduct_Price_Unit_Label) {
			global $pagenow;

			if ($pagenow == 'edit.php') {
				$Product_ID = $product -> get_id();
				
				$Price_Unit_Label = WooDecimalProduct_Get_PriceUnitLabel_by_ProductID ($Product_ID);
				
				if ($Price_Unit_Label) {
					$price .= "<br>$Price_Unit_Label";
					
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
		WooDecimalProduct_Debugger ($WooDecimalProduct_QuantityData, __FUNCTION__ .' $WooDecimalProduct_QuantityData ' .__LINE__, 'product_options_general', true);	

		$Min_Qnt 		= $WooDecimalProduct_QuantityData['min_qnt'];
		$Max_Qnt 		= $WooDecimalProduct_QuantityData['max_qnt'];
		$Def_Qnt 		= $WooDecimalProduct_QuantityData['def_qnt'];
		$Stp_Qnt 		= $WooDecimalProduct_QuantityData['stp_qnt'];
		$QNT_Precision 	= $WooDecimalProduct_QuantityData['precision'];	
		
		// PlaceHolders
		$PlaceHolder_Min_Qnt = $WooDecimalProduct_QuantityData['placeholder_min_qnt'];
		$PlaceHolder_Max_Qnt = $WooDecimalProduct_QuantityData['placeholder_max_qnt'];
		$PlaceHolder_Def_Qnt = $WooDecimalProduct_QuantityData['placeholder_def_qnt'];
		$PlaceHolder_Stp_Qnt = $WooDecimalProduct_QuantityData['placeholder_stp_qnt'];		

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

		$Label_Price_Unit_Label = __('Price Unit-Label', 'decimal-product-quantity-for-woocommerce');
		
		$Style_and_Title = '';
		
		if (! $WooDecimalProduct_Price_Unit_Label) {
			$Title = __('Disabled in Global Option.', 'decimal-product-quantity-for-woocommerce');
			$Style_and_Title = "style='color:red;' title='$Title'";
			
			$Label_Price_Unit_Label = '<span ' .$Style_and_Title .'>' .$Label_Price_Unit_Label .'</span>';
		}	
	
		echo '<div class="options_group">';
			echo '<div style="margin-top: 10px; margin-left: 10px;">';
				echo '<span style="font-weight: bold;">';
					echo esc_html( __('Quantity Options', 'decimal-product-quantity-for-woocommerce') );
				echo '</span>';
				
				echo '<br>';
				
				echo '<div>';					
					$Note = esc_html( __('* If not specified, it will be as in', 'decimal-product-quantity-for-woocommerce') );
					$Note .= ' <a target="_blank" href="/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product">Categories</a> / <a target="_blank" href="/wp-admin/edit.php?post_type=product&page=decimal-product-quantity-for-woocommerce/includes/admin/options.php">Global Settings</a>';
					echo $Note; // phpcs:ignore	
				echo '</div>';
			echo '</div>';
		
			// Minimum
			woocommerce_wp_text_input( 
				array( 
					'id'          => 'woodecimalproduct_min_qnt', 
					'label'       => __('Minimum', 'decimal-product-quantity-for-woocommerce'), 
					'placeholder' => $PlaceHolder_Min_Qnt,
					'desc_tip'    => 'true',
					'description' => sprintf ( esc_html( __('Set the Min of changing the quantity: 0.1 0.5 100 e.t.c (Default = ', 'decimal-product-quantity-for-woocommerce') ) .'%s' .')', $Min_Qnt)
				)
			);
			
			// Default Set
			woocommerce_wp_text_input( 
				array( 
					'id'          => 'woodecimalproduct_item_qnt', 
					'label'       => __('Default Set', 'decimal-product-quantity-for-woocommerce'), 
					'placeholder' => $PlaceHolder_Def_Qnt,
					'desc_tip'    => 'true',
					'description' => sprintf ( esc_html( __('Set the Default quantity: 0.1 0.5 100 e.t.c (Default = ', 'decimal-product-quantity-for-woocommerce') ) .'%s' .')', $Def_Qnt)
				)
			);		

			// Step change +/-
			woocommerce_wp_text_input( 
				array( 
					'id'          => 'woodecimalproduct_step_qnt', 
					'label'       => __('Step change +/-', 'decimal-product-quantity-for-woocommerce'), 
					'placeholder' => $PlaceHolder_Stp_Qnt,
					'desc_tip'    => 'true',
					'description' => sprintf ( esc_html( __('Set the Step of changing the quantity: 0.1 0.5 100 e.t.c (Default = ', 'decimal-product-quantity-for-woocommerce') ) .'%s' .')', $Stp_Qnt)
				)
			);

			// Maximum
			woocommerce_wp_text_input( 
				array( 
					'id'          => 'woodecimalproduct_max_qnt', 
					'label'       => __('Maximum', 'decimal-product-quantity-for-woocommerce'), 
					'placeholder' => $PlaceHolder_Max_Qnt,
					'desc_tip'    => 'true',
					'description' => __('Set the Max of changing the quantity: 0.1 0.5 100 e.t.c (or leave blank)', 'decimal-product-quantity-for-woocommerce')
				)
			);
			
			// Price Unit Label
			woocommerce_wp_text_input( 
				array( 
					'id'          	=> 'woodecimalproduct_price_unit_label', 
					'label'       	=> $Label_Price_Unit_Label, 
					'placeholder' 	=> $Product_Price_Unit_Label,
					'desc_tip'    	=> 'true',
					'description' => __('View Price Unit-Label. Sample: "Price per Kg." / "Price per Meter". Or leave blank to use Category value.', 'decimal-product-quantity-for-woocommerce')
				)
			);	

			// Disable: Price Unit Label
			woocommerce_wp_checkbox( 
				array( 
					'id'          	=> 'woodecimalproduct_price_unit_disable', 
					'label'       	=> __('Disable Price Unit-Label', 'decimal-product-quantity-for-woocommerce'), 
					'desc_tip'    	=> 'true',
					'description' => __('Disable Price Unit-Label for this Product.', 'decimal-product-quantity-for-woocommerce')
				)
			);
			
			// Disable: RSS Feed
			woocommerce_wp_checkbox( 
				array( 
					'id'          	=> 'woodecimalproduct_rss_feed_disable', 
					'label'       	=> __('Disable RSS Feed', 'decimal-product-quantity-for-woocommerce'), 
					'desc_tip'    	=> 'true',
					'description' => __('Disable RSS Feed for this Product.', 'decimal-product-quantity-for-woocommerce')
				)
			);

			echo '<div style="clear: both; margin-top: -20px; margin-left: 12px; color: red;">';
			echo '(' .esc_html( __('* PRO Version only!', 'decimal-product-quantity-for-woocommerce') ) .')';
			echo '</div>';
		echo '</div>';    	
	} 
	
	/* Сохраняем "Опции кол-ва Товара" для данного Товара.
	----------------------------------------------------------------- */	
	add_action ('woocommerce_process_product_meta', 'WooDecimalProduct_save_product_field_step_Qnt');	
	function WooDecimalProduct_save_product_field_step_Qnt ($post_id) {	
        $new_min_Qnt    		= isset($_POST['woodecimalproduct_min_qnt']) ? sanitize_text_field (wp_unslash($_POST['woodecimalproduct_min_qnt'])): 1; // phpcs:ignore	
        $new_step_Qnt   		= isset($_POST['woodecimalproduct_step_qnt']) ? sanitize_text_field (wp_unslash($_POST['woodecimalproduct_step_qnt'])): 1; // phpcs:ignore	  
		$new_dft_Qnt    		= isset($_POST['woodecimalproduct_item_qnt']) ? sanitize_text_field (wp_unslash($_POST['woodecimalproduct_item_qnt'])): 1; // phpcs:ignore	
		$new_max_Qnt			= isset($_POST['woodecimalproduct_max_qnt']) ? sanitize_text_field (wp_unslash($_POST['woodecimalproduct_max_qnt'])): ''; // phpcs:ignore	
		$new_Price_Unit_Label	= isset($_POST['woodecimalproduct_price_unit_label']) ? sanitize_text_field (wp_unslash($_POST['woodecimalproduct_price_unit_label'])): ''; // phpcs:ignore	
		$new_Price_Unit_Disable	= isset($_POST['woodecimalproduct_price_unit_disable']) ? 'yes': '';	 // phpcs:ignore	
		
		update_post_meta ($post_id, 'woodecimalproduct_min_qnt', $new_min_Qnt); // phpcs:ignore	
		update_post_meta ($post_id, 'woodecimalproduct_step_qnt', $new_step_Qnt); // phpcs:ignore	
		update_post_meta ($post_id, 'woodecimalproduct_item_qnt', $new_dft_Qnt); // phpcs:ignore		
		update_post_meta ($post_id, 'woodecimalproduct_max_qnt', $new_max_Qnt);	// phpcs:ignore		
		update_post_meta ($post_id, 'woodecimalproduct_price_unit_label', $new_Price_Unit_Label);	// phpcs:ignore	
		update_post_meta ($post_id, 'woodecimalproduct_price_unit_disable', $new_Price_Unit_Disable); // phpcs:ignore		
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
				<?php echo esc_html( __('* PRO Version only!', 'decimal-product-quantity-for-woocommerce') ); ?>
			</div>
			
			<p class="form-field">
				<label for="woodecimalproduct_options_variation_field_min_qnt">
					<?php echo esc_html( __('Min cart Quantity (if the variation has a separate value)', 'decimal-product-quantity-for-woocommerce') ); ?>
				</label>
				<input type="text" class="short" disabled="true" name="woodecimalproduct_options_variation_field_min_qnt" value="" placeholder="<?php echo esc_attr($WooDecimalProduct_Min_Quantity_Default);?>">
			</p>
			
			<p class="form-field">
				<label for="woodecimalproduct_options_variation_field_step_qnt">
					<?php echo esc_html( __('Step change Quantity (if the variation has a separate value)', 'decimal-product-quantity-for-woocommerce') ); ?>
				</label>
				<input type="text" class="short" disabled="true" name="woodecimalproduct_options_variation_field_step_qnt" value="" placeholder="<?php echo esc_attr($WooDecimalProduct_Step_Quantity_Default);?>">
			</p>

			<p class="form-field">
				<label for="woodecimalproduct_options_variation_field_default_qnt">
					<?php echo esc_html( __('Default Quantity (if the variation has a separate value)', 'decimal-product-quantity-for-woocommerce') ); ?>
				</label>
				<input type="text" class="short" disabled="true" name="woodecimalproduct_options_variation_field_default_qnt" value="" placeholder="<?php echo esc_attr($WooDecimalProduct_Item_Quantity_Default);?>">
			</p>	

			<p class="form-field">
				<label for="woodecimalproduct_options_variation_field_max_qnt">					
					<?php echo esc_html( __('Max cart Quantity (if the variation has a separate value)', 'decimal-product-quantity-for-woocommerce') ); ?>
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