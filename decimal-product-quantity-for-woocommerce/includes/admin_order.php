<?php
/*
 * Decimal Product Quantity for WooCommerce
 * Admin WooCommerce Order Page.
 * admin_order.php
 */

    /* WooCommerce Order Page.
	 * Шаг изменения кол-ва Товара на странице Администрирования Заказа.
	 * \woocommerce\includes\admin\meta-boxes\views\html-order-item.php
    ----------------------------------------------------------------- */ 
	add_filter ('woocommerce_quantity_input_step_admin', 'WooDecimalProduct_quantity_Input_Step', 10, 3);
	function WooDecimalProduct_quantity_Input_Step($Step_Qnt, $product, $mode) {
		// $mode = 'edit' or 'refund'

		if (is_admin()) {
			if ($product) {
				$Parent_ID = 0;
				
				if (method_exists($product, 'get_parent_id')) {
					$Parent_ID = $product->get_parent_id();
				}
				
				if ($Parent_ID > 0) {
					// Вариативный Товар.
					$Product_ID = $Parent_ID;
				} else {
					// Простой Товар.				
					if (method_exists($product, 'get_id')) {
						$Product_ID = $product->get_id();
					}
				}

				$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID);
				
				$Step_Qnt = $WooDecimalProduct_QuantityData['stp_qnt'];	

				return $Step_Qnt;				
			}
		}
		
		return $Step_Qnt;
	}
	
    /* WooCommerce Order Page.
	 * Минимальное кол-во Товара на странице Администрирования Заказа.
	 * \woocommerce\includes\admin\meta-boxes\views\html-order-item.php
    ----------------------------------------------------------------- */ 
	add_filter ('woocommerce_quantity_input_min_admin', 'WooDecimalProduct_quantity_Input_Min', 10, 3);
	function WooDecimalProduct_quantity_Input_Min($Min_Qnt, $product, $mode) {
		if (is_admin()) {
			if ($product) {
				$Parent_ID = 0;
				
				if (method_exists($product, 'get_parent_id')) {
					$Parent_ID = $product->get_parent_id();
				}
				
				if ($Parent_ID > 0) {
					// Вариативный Товар.
					$Product_ID = $Parent_ID;
				} else {
					// Простой Товар.
					if (method_exists($product, 'get_id')) {
						$Product_ID = $product->get_id();
					}
				}
				
				$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID);
				
				$Min_Qnt = $WooDecimalProduct_QuantityData['min_qnt'];
				
				return $Min_Qnt;				
			}
		}
		
		return $Min_Qnt;
	}
	
    /* WooCommerce Order Page.
	 * Поиск Товара при Добавлении его на странице Администрирования Заказа с учетом Запасов.
	 * \woocommerce\includes\class-wc-ajax.php
    ----------------------------------------------------------------- */
	add_filter ('woocommerce_json_search_found_products', 'WooDecimalProduct_json_search_found_products');
	function WooDecimalProduct_json_search_found_products ($products) {
		if ($products) {
			$Result = array();
			
			foreach ($products as $id => $value) {
				if ($id) {		
					$product_object = wc_get_product ($id);

					if (! wc_products_array_filter_readable ($product_object)) {
						continue;
					}

					$formatted_name = $product_object -> get_formatted_name();
					$managing_stock = $product_object -> managing_stock();

					if ($managing_stock && ! empty ($_GET['display_stock'])) {
						$Parent_ID = $product_object -> get_parent_id();

						// Простой Товар.
						$Product_ID = $id;
							
						if ($Parent_ID > 0) {
							// Вариативный Товар.
							$Product_ID = $Parent_ID;
						}
					
						$stock_amount = $product_object -> get_stock_quantity();				
						
						$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID);

						if ($WooDecimalProduct_QuantityData) {
							$QNT_Precision = isset($WooDecimalProduct_QuantityData['precision']) ? $WooDecimalProduct_QuantityData['precision'] : null;

							$Stock_Translators = __('In stock', 'decimal_product_quantity_for_woocommerce');
							
							$Stock_QNT_Precision_Format = "$Stock_Translators: %f";
							
							if ($QNT_Precision) {
								$Stock_QNT_Precision_Format = "$Stock_Translators: %." .$QNT_Precision ."f";
							} 
						}
						
						$formatted_name .= ' &ndash; ' . sprintf( $Stock_QNT_Precision_Format, wc_format_stock_quantity_for_display ($stock_amount, $product_object));
					}

					$Result[$product_object -> get_id()] = rawurldecode (wp_strip_all_tags ($formatted_name));
				}			
			}
			
			wp_send_json ($Result);
			exit();
		}	
		
		return $products;		
	}