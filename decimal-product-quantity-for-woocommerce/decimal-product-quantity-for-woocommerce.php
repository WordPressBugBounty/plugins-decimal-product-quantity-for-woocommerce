<?php
/*
Plugin Name: Decimal Product Quantity for WooCommerce
Plugin URI: https://wpgear.xyz/decimal-product-quantity-woo
Description: Decimal Product Quantity for WooCommerce. (Piece of Product). Min, Max, Step & Default preset. Variable Products Supported. Auto correction "No valid value". Update Cart Automatically on Quantity Change (AJAX Cart Update). Read about <a href="http://wpgear.xyz/decimal-product-quantity-woo-pro/">PRO Version</a> for separate Minimum Quantity, Step of Changing & Default preset Quantity - for each Product Variation. Create XML/RSS Feed for WooCommerce. Support: "Google Merchant Center" (Product data specification) whith "Price_Unit_Label" -> [unit_pricing_measure], separate hierarchy Categories -> Products.
Version: 18.56
Text Domain: decimal-product-quantity-for-woocommerce
Domain Path: /languages
Author: WPGear
Author URI: https://wpgear.xyz
License: GPLv2
*/

	include_once( __DIR__ .'/includes/blocks/blocks.php' );	
	include_once( __DIR__ .'/includes/functions.php' );
	include_once( __DIR__ .'/includes/admin/admin_setup_woo.php' );
	include_once( __DIR__ .'/includes/admin/admin_setup_product.php' );
	include_once( __DIR__ .'/includes/admin/admin_setup_category.php' );
	include_once( __DIR__ .'/includes/admin/admin_order.php' );

	WooDecimalProduct_Check_Updated ();

	__('Decimal Product Quantity for WooCommerce. (Piece of Product). Min, Max, Step & Default preset. Variable Products Supported. Auto correction "No valid value". Update Cart Automatically on Quantity Change (AJAX Cart Update). Read about <a href="http://wpgear.xyz/decimal-product-quantity-woo-pro/">PRO Version</a> for separate Minimum Quantity, Step of Changing & Default preset Quantity - for each Product Variation. Create XML/RSS Feed for WooCommerce. Support: "Google Merchant Center" (Product data specification) whith "Price_Unit_Label" -> [unit_pricing_measure], separate hierarchy Categories -> Products.', 'decimal-product-quantity-for-woocommerce');	
	
	/* JS Script.
	----------------------------------------------------------------- */	
	add_action ('wp_enqueue_scripts', 'WooDecimalProduct_Admin_Style');
	add_action ('admin_enqueue_scripts', 'WooDecimalProduct_Admin_Style');
	function WooDecimalProduct_Admin_Style ($hook) {
		$debug_process = 'enqueue_scripts';
		
		$WooDecimalProduct_Plugin_URL = plugin_dir_url ( __FILE__ ); // со слэшем на конце

		$Plugin_Data = get_plugin_data( __FILE__ );
		$WooDecimalProduct_Plugin_Version = $Plugin_Data['Version'];	
		
		$BlockLayots = WooDecimalProduct_Blocks_Check_BlockLayots();
		WDPQ_Debugger ($BlockLayots, '$BlockLayots', $debug_process, __FUNCTION__, __LINE__);				
				
		if (in_array('Cart', $BlockLayots))	{
			// Block Cart
			wp_enqueue_script ('woodecimalproduct_block_cart', $WooDecimalProduct_Plugin_URL .'includes/blocks/wdpq_block_page_cart.js', array(), $WooDecimalProduct_Plugin_Version); // phpcs:ignore 
			
		} else {
			// Classic Cart
			// Там нормально вызывается Скрипт. Это - Лишнее.
			// wp_enqueue_script ('wdpq_page_cart', $WooDecimalProduct_Plugin_URL .'includes/wdpq_page_cart.js', array(), $WooDecimalProduct_Plugin_Version); // phpcs:ignore 
		}

		// Script for Extended Integration.		
		wp_enqueue_script ('wdpq_quantity_data', $WooDecimalProduct_Plugin_URL .'includes/woodecimalproduct.js', array(), $WooDecimalProduct_Plugin_Version); // phpcs:ignore 
				
		wp_enqueue_style ('wdpq_style', $WooDecimalProduct_Plugin_URL .'style.css', array(), $WooDecimalProduct_Plugin_Version); // phpcs:ignore 		
	}

	/* AJAX Processing
	----------------------------------------------------------------- */
    add_action ('wp_ajax_wdpq_ext_processing', 'WooDecimalProduct_Ajax');
	add_action ('wp_ajax_nopriv_wdpq_ext_processing', 'WooDecimalProduct_Ajax');
	add_action ('wp_ajax_nopriv_update_wdpq_cart', 'WooDecimalProduct_Ajax');
    function WooDecimalProduct_Ajax() {
		$debug_process = 'ajax';
		
		include_once ('includes/ajax_processing.php');
    }	

	/* Translate.
	----------------------------------------------------------------- */
	add_action ('plugins_loaded', 'WooDecimalProduct_Action_plugins_loaded');
	function WooDecimalProduct_Action_plugins_loaded() {
		$WooDecimalProduct_LocalePath = dirname (plugin_basename ( __FILE__ )) . '/languages/';	
		
		load_plugin_textdomain ('decimal-product-quantity-for-woocommerce', false, $WooDecimalProduct_LocalePath);	
	}
	
	/* Init. Инициализация.
     * Запускаем самым последним, чтобы быть уверенным, что WooCommerce уже инициализировался.
	----------------------------------------------------------------- */ 
	add_action ('init', 'WooDecimalProduct_Init', 999999);
	function WooDecimalProduct_Init () {		
		$debug_process = 'init';
		
		$WooDecimalProduct_StorageType = get_option ('woodecimalproduct_storage_type', 'system');
		
		// Инициализация Сессии, для Незалогиненых Пользователей.
		if (! is_user_logged_in() ) {
			if ($WooDecimalProduct_StorageType == 'session') {
				// StorageType: PHP Session
				$Session_Started = WooDecimalProduct_StartSession( $Initiator = __FUNCTION__ );
			}	
		} 	
		
		WooDecimalProduct_Woo_remove_filters();	

		// "WooCommerce High-Performance Order Storage" Mode
		$WooDecimalProduct_is_HPOS_Mode_Enable = filter_var( get_option( 'woocommerce_custom_orders_table_enabled', false ), FILTER_VALIDATE_BOOLEAN );
		WDPQ_Debugger ($WooDecimalProduct_is_HPOS_Mode_Enable, '$WooDecimalProduct_is_HPOS_Mode_Enable', $debug_process, __FUNCTION__, __LINE__);
	}
	
	/* Страница Товара и Корзина. Классический вариант. Не Блочные.
     * Минимальное / Максимально кол-во выбора Товара, Шаг, Значение по-Умолчанию на странице Товара и Корзины.
	 * woocommerce\includes\wc-template-functions.php
	 * Woo version > 9.4.3
    ----------------------------------------------------------------- */   
	add_filter ('woocommerce_quantity_input_args', 'WooDecimalProduct_Filter_quantity_input_args', 999999, 2);
    function WooDecimalProduct_Filter_quantity_input_args($args, $product) {
		$debug_process = 'quantity_input_args';

		WDPQ_Debugger ($args, '$args', $debug_process, __FUNCTION__, __LINE__);

		if ($product) {
			$Product_ID = $product -> get_id();
			WDPQ_Debugger ($Product_ID, '$Product_ID', $debug_process, __FUNCTION__, __LINE__);
			
			if ($Product_ID) {
				$item_product_id = $product -> get_parent_id();
				WDPQ_Debugger ($item_product_id, '$item_product_id', $debug_process, __FUNCTION__, __LINE__);
				
				if ($item_product_id > 0) {
					// Вариативный Товар.
				} else {
					// Простой Товар.
					$item_product_id = $Product_ID;
				}
				
				$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($item_product_id);
				WDPQ_Debugger ($WooDecimalProduct_QuantityData, '$WooDecimalProduct_QuantityData', $debug_process, __FUNCTION__, __LINE__);
				
				$Min_Qnt = $WooDecimalProduct_QuantityData['min_qnt'];
				$Max_Qnt = $WooDecimalProduct_QuantityData['max_qnt'];
				$Def_Qnt = $WooDecimalProduct_QuantityData['def_qnt'];
				$Stp_Qnt = $WooDecimalProduct_QuantityData['stp_qnt'];	
				
				$args['min_value'] 	= $Min_Qnt;			
				$args['step'] 		= $Stp_Qnt;
				$args['max_value'] 	= $Max_Qnt;			

				$Field_Input_Name 	= isset($args['input_name']) ? $args['input_name']: '';
				$Field_Input_Value 	= isset($args['input_value']) ? $args['input_value']: '';
				WDPQ_Debugger ($Field_Input_Name, '$Field_Input_Name', $debug_process, __FUNCTION__, __LINE__);

				if ($Field_Input_Name == 'quantity') {
					// Страница Товара.
					if ($Field_Input_Value == 1) {
						// Возможно, надо изменить на Предустановленное значение.
						$args['input_value'] = $Def_Qnt;
					}	

					// Если Фиксированное Количество.
					if ($Min_Qnt == $Max_Qnt) {
						$args['readonly'] = true;	
					}					
				}
			
				// Возможно, это - Корзина. Например: 'cart[e00da03b685a0dd18fb6a08af0923de0][qty]'	
				$PosStart_Cart_Item_Key	= strpos( $Field_Input_Name, 'art[' );
				WDPQ_Debugger ($PosStart_Cart_Item_Key, '$PosStart_Cart_Item_Key', $debug_process, __FUNCTION__, __LINE__);

				if ( $PosStart_Cart_Item_Key > 0) {
					$PosEnd_Cart_Item_Key = strpos( $Field_Input_Name, '][qty]' );

					if ($PosEnd_Cart_Item_Key > 0) {
						$Cart_Item_Key = substr($Field_Input_Name, $PosStart_Cart_Item_Key + 4, $PosEnd_Cart_Item_Key - 5);
						WDPQ_Debugger ($Cart_Item_Key, '$Cart_Item_Key', $debug_process, __FUNCTION__, __LINE__);
						
						// Корзина. Корректируем $Quantity из WDPQ-Cart
						if ($Cart_Item_Key) {
							$Cart_Quantity = WooDecimalProduct_Get_WDPQ_Cart_Quantity_by_CartProductKey ($Cart_Item_Key);
							WDPQ_Debugger ($Cart_Item_Key, '$Cart_Item_Key', $debug_process, __FUNCTION__, __LINE__);
						
							if ($Cart_Quantity > 0) {
								$args['input_value'] = $Cart_Quantity;
							}			
						}
					}
				}		
			}				
		}
			
		WDPQ_Debugger ($args, '$args', $debug_process, __FUNCTION__, __LINE__);
        return $args;
    }     

    /* Вариативный Товар. Минимальное кол-во выбора Товара на странице Товара.
    ----------------------------------------------------------------- */ 
    add_filter ('woocommerce_available_variation', 'WooDecimalProduct_Filter_quantity_available_variation', 10, 3);
	function WooDecimalProduct_Filter_quantity_available_variation ($args, $product, $variation) {	
		$debug_process = 'variation';
		
        $Product_ID = $product -> get_id();
		WDPQ_Debugger ($Product_ID, '$Product_ID', $debug_process, __FUNCTION__, __LINE__);

		$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID);
		WDPQ_Debugger ($WooDecimalProduct_QuantityData, '$WooDecimalProduct_QuantityData', $debug_process, __FUNCTION__, __LINE__);
		
		$Min_Qnt = $WooDecimalProduct_QuantityData['min_qnt'];
		$Max_Qnt = $WooDecimalProduct_QuantityData['max_qnt'];		

        $args['min_qty'] = $Min_Qnt;
		$args['max_qty'] = $Max_Qnt;

        return $args;
    }
    
    /* Проверка условий превышения Максимального количества Товара при попытке добавления в Корзину.
	 * \woocommerce\includes\wc-cart-functions.php
    ----------------------------------------------------------------- */
	add_filter ('woocommerce_add_to_cart_validation', 'WooDecimalProduct_Filter_add_to_cart_validation', 10, 3);
	function WooDecimalProduct_Filter_add_to_cart_validation ($Passed, $Product_ID, $Quantity) {
		$debug_process = 'add_to_cart_validation';

		WDPQ_Debugger ($Passed, '$Passed', $debug_process, __FUNCTION__, __LINE__);
		WDPQ_Debugger ($Product_ID, '$Product_ID', $debug_process, __FUNCTION__, __LINE__);
		WDPQ_Debugger ($Quantity, '$Quantity', $debug_process, __FUNCTION__, __LINE__);
		
		if ($Passed) {
			$WooDecimalProduct_Max_Quantity_Default    	= get_option ('woodecimalproduct_max_qnt_default', '');  
			
			$cart = WC() -> session -> cart;
			
			if (empty($cart) || !is_array($cart) || 0 === count($cart)) {
				return $Passed;
			} else {
				foreach ($cart as $Item) {
					if (is_array($Item)) {
						$Item_Product_ID = isset($Item['product_id']) ? $Item['product_id'] : false;
						
						if ($Item_Product_ID == $Product_ID) {
							$Item_Quantity = isset($Item['quantity']) ? $Item['quantity'] : false;
							
							if ($Item_Quantity) {
								$No_MaxEmpty = '';								
								$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID, $No_MaxEmpty);
								WDPQ_Debugger ($WooDecimalProduct_QuantityData, '$WooDecimalProduct_QuantityData', $debug_process, __FUNCTION__, __LINE__);

								$Max_Qnt = $WooDecimalProduct_QuantityData['max_qnt'];

								if ($Max_Qnt != '') {
									$Total_Quantity = $Item_Quantity + $Quantity;
									
									if ($Total_Quantity > $Max_Qnt) {
										$Passed = false;
										
										wc_add_notice (__('You have exceeded the allowed Maximum Quantity for this product. Check Cart.', 'decimal-product-quantity-for-woocommerce'), 'error');

										return $Passed;									
									}	
								}	
							}
						}
					}
				}
			}
		}
		
		return $Passed;
	}
	
    /* Сообщение о добавлении Товара в Корзину с учетом возможного дробного Значения Количества.
	 * \woocommerce\includes\wc-cart-functions.php
	 * Woo version > 9.4.3
    ----------------------------------------------------------------- */
	add_filter ('wc_add_to_cart_message_html', 'WooDecimalProduct_Filter_wc_add_to_cart_message_html', 10, 2);
	function WooDecimalProduct_Filter_wc_add_to_cart_message_html ($message, $products) {
		$debug_process = 'add_to_cart_message';

		WDPQ_Debugger ($message, '$message', $debug_process, __FUNCTION__, __LINE__);
		WDPQ_Debugger ($products, '$products', $debug_process, __FUNCTION__, __LINE__);
		WDPQ_Debugger ($_REQUEST, '$_REQUEST', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore	
		
		$Variation_ID = isset( $_REQUEST['variation_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['variation_id'] ) ) : 0; // phpcs:ignore
		
		$Add_to_Cart = array();
	
		$count = 0;	

		foreach ($products as $Product_ID => $Quantity) {
			WDPQ_Debugger ($Quantity, '$Quantity', $debug_process, __FUNCTION__, __LINE__);
			
			if ($Quantity > 0) {				
				$Cart_Item_Key = WooDecimalProduct_Get_CartItem_Key_by_ProductID ($Product_ID);
				WDPQ_Debugger ($Cart_Item_Key, '$Cart_Item_Key', $debug_process, __FUNCTION__, __LINE__);
				
				$Item_Price = 0;
									
				if ($Variation_ID) {
					//Вариативный Товар.
					
					// $Product_Variation_Prices = $Product -> get_variation_prices();
					// WDPQ_Debugger ($Product_Variation_Prices, '$Product_Variation_Prices', $debug_process, __FUNCTION__, __LINE__);
											
					// $Item_Price = $Product_Variation_Prices['price'][$Variation_ID]; // price						
					// $Item_RegularPrice = $Product_Variation_Prices['regular_price'][$Variation_ID]; // regular_price
					// $Item_SalePrice = $Product_Variation_Prices['sale_price'][$Variation_ID]; // sale_price
					
					$Product = wc_get_product( $Variation_ID );
					
				} else {
					//Простой Товар.
					
					$Product = wc_get_product( $Product_ID );
				}	

				// WDPQ_Debugger ($Product, '$Product', $debug_process, __FUNCTION__, __LINE__);
				if ($Product) {	
					$Item_Price 		= $Product -> get_price();
					$Item_RegularPrice 	= $Product -> get_regular_price();
					$Item_SalePrice 	= $Product -> get_sale_price();
					
					$Item_Price 		= floatval( $Item_Price );
					$Item_RegularPrice 	= floatval( $Item_RegularPrice );
					$Item_SalePrice 	= floatval( $Item_SalePrice );

					WDPQ_Debugger ($Item_Price, '$Item_Price', $debug_process, __FUNCTION__, __LINE__);
					WDPQ_Debugger ($Item_RegularPrice, '$Item_RegularPrice', $debug_process, __FUNCTION__, __LINE__);
					WDPQ_Debugger ($Item_SalePrice, '$Item_SalePrice', $debug_process, __FUNCTION__, __LINE__);
				
					$Price_Excl_Tax = wc_get_price_excluding_tax( $Product ); 	// price without VAT
					$Price_Incl_Tax = wc_get_price_including_tax( $Product );  	// price with VAT
					$Tax_Amount     = $Price_Incl_Tax - $Price_Excl_Tax; 		// VAT amount

					WDPQ_Debugger ($Price_Excl_Tax, '$Price_Excl_Tax', $debug_process, __FUNCTION__, __LINE__);
					WDPQ_Debugger ($Price_Incl_Tax, '$Price_Incl_Tax', $debug_process, __FUNCTION__, __LINE__);
					WDPQ_Debugger ($Tax_Amount, '$Tax_Amount', $debug_process, __FUNCTION__, __LINE__);

					$Item = array(
						'key' => $Cart_Item_Key,
						'product_id' => $Product_ID,
						'variation_id' => $Variation_ID,
						'quantity' => $Quantity,
						'price' => $Item_Price,
						'regular_price' => $Item_RegularPrice,
						'sale_price' => $Item_SalePrice,
						'price_tax_excl' => $Price_Excl_Tax,
						'price_tax_incl' => $Price_Incl_Tax,
						'tax_amount' => $Tax_Amount,
					);
					WDPQ_Debugger ($Item, '$Item', $debug_process, __FUNCTION__, __LINE__);

					$Add_to_Cart[] = $Item;
				}					
			}
			
			$titles[] = ($Quantity > 0 ? $Quantity . ' &times; ' : '') . sprintf (_x('&ldquo;%s&rdquo;', 'Item name in quotes', 'woocommerce'), wp_strip_all_tags( (get_the_title ($Product_ID)) )); // phpcs:ignore	
			$count   += $Quantity;
		}

		WDPQ_Debugger ($Add_to_Cart, '$Add_to_Cart', $debug_process, __FUNCTION__, __LINE__);

		WooDecimalProduct_Update_WDPQ_CartSession ($Add_to_Cart, $isDraft = false);
		
		$titles = array_filter ($titles);
		$added_text = sprintf( _n('%s has been added to your cart.', '%s have been added to your cart.', $count, 'woocommerce'), wc_format_list_of_items ($titles)); // phpcs:ignore	

		// Output success messages.
		if ('yes' === get_option ('woocommerce_cart_redirect_after_add')) {
			$return_to = apply_filters ('woocommerce_continue_shopping_redirect', wc_get_raw_referer() ? wp_validate_redirect( wc_get_raw_referer(), false ) : wc_get_page_permalink('shop'));
			$message = sprintf ('<a href="%s" class="button wc-forward">%s</a> %s', esc_url ($return_to), esc_html__('Continue shopping', 'woocommerce'), esc_html ($added_text)); // phpcs:ignore	
		} else {
			$message = sprintf ('<a href="%s" class="button wc-forward">%s</a> %s', esc_url (wc_get_page_permalink('cart')), esc_html__('View cart', 'woocommerce'), esc_html($added_text)); // phpcs:ignore	
		}

		if (has_filter( 'wc_add_to_cart_message')) {
			wc_deprecated_function ('The wc_add_to_cart_message filter', '3.0', 'wc_add_to_cart_message_html');
			$message = apply_filters ('wc_add_to_cart_message', $message, $Product_ID);
		}	

		WDPQ_Debugger ($message, '$message', $debug_process, __FUNCTION__, __LINE__);
		return $message;
	}

    /* Добавление Товара не со Страницы Товара, а из Каталога (без выбора Количества), с учетом возможного минимального Значения Количества и Количества по-Умолчанию.
	 * \woocommerce\includes\wc-template-functions.php
	 * \woocommerce\templates\loop\add-to-cart.php
    ----------------------------------------------------------------- */	
	add_filter ('woocommerce_loop_add_to_cart_args', 'WooDecimalProduct_Filter_loop_add_to_cart_args', 10, 2);
	function WooDecimalProduct_Filter_loop_add_to_cart_args ($args, $product) {
		$debug_process = 'add_to_cart_args';

		WDPQ_Debugger ($args, '$args', $debug_process, __FUNCTION__, __LINE__);
		// WDPQ_Debugger ($product, '$product', $debug_process, __FUNCTION__, __LINE__);
		
		$Product_ID = $product -> get_id();	
		
		if ($Product_ID) {
			$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID);
			WDPQ_Debugger ($WooDecimalProduct_QuantityData, '$WooDecimalProduct_QuantityData', $debug_process, __FUNCTION__, __LINE__);
			
			$Min_Qnt = $WooDecimalProduct_QuantityData['min_qnt'];
			$Max_Qnt = $WooDecimalProduct_QuantityData['max_qnt'];
			$Def_Qnt = $WooDecimalProduct_QuantityData['def_qnt'];
			$Stp_Qnt = $WooDecimalProduct_QuantityData['stp_qnt'];
			
			$args['quantity'] = $Def_Qnt;
			
			return $args;
		}		

		return $args;
	}
	
	/* Страница Товара. 
	 * Авто-Коррекция неправильно введенного Значения Количества.
	 * Request: Ady DeeJay
	----------------------------------------------------------------- */	
	add_action ('woocommerce_before_single_product_summary', 'WooDecimalProduct_Action_before_single_product_summary', 10);
	function WooDecimalProduct_Action_before_single_product_summary () {
		$debug_process = 'product_page';

		WooDecimalProduct_Clear_WooCart_if_WDPQCart_Emty ();

		$WooDecimalProduct_ConsoleLog_Debuging		= get_option ('woodecimalproduct_debug_log', 0);
		$WooDecimalProduct_Auto_Correction_Quantity	= get_option ('woodecimalproduct_auto_correction_qnt', 1);
		$WooDecimalProduct_ButtonsPM_Product_Enable	= get_option ('woodecimalproduct_buttonspm_product_enable', 0);
			
		global $product;
		
		$Product_ID = $product -> get_id();	
		WDPQ_Debugger ($Product_ID, '$Product_ID', $debug_process, __FUNCTION__, __LINE__);
		
		if ($Product_ID) {
			$No_MaxEmpty = '-1';	// Unlimited
			$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID, $No_MaxEmpty);
			WDPQ_Debugger ($WooDecimalProduct_QuantityData, '$WooDecimalProduct_QuantityData', $debug_process, __FUNCTION__, __LINE__);
			
			$Min_Qnt 		= $WooDecimalProduct_QuantityData['min_qnt'];
			$Max_Qnt 		= $WooDecimalProduct_QuantityData['max_qnt'];
			$Def_Qnt 		= $WooDecimalProduct_QuantityData['def_qnt'];
			$Stp_Qnt 		= $WooDecimalProduct_QuantityData['stp_qnt'];				
			$QNT_Precision 	= $WooDecimalProduct_QuantityData['precision'];
			
			$WooDecimalProduct_Plugin_URL = plugin_dir_url ( __FILE__ ); // со слэшем на конце

			$Plugin_Data = get_plugin_data( __FILE__ );
			$WooDecimalProduct_Plugin_Version = $Plugin_Data['Version'];

			wp_enqueue_script ('wdpq_page_product', $WooDecimalProduct_Plugin_URL .'includes/wdpq_page_product.js', array(), $WooDecimalProduct_Plugin_Version); // phpcs:ignore 

			$Params = array (
				'qnt_min' => $Min_Qnt,
				'qnt_max' => $Max_Qnt,
				'qnt_default' => $Def_Qnt,
				'qnt_step' => $Stp_Qnt,
				'qnt_precision' => $QNT_Precision,
				'debug_enable' => $WooDecimalProduct_ConsoleLog_Debuging,
				'buttons_pm_enable' => $WooDecimalProduct_ButtonsPM_Product_Enable,
				'msg_no_valid_value' => esc_html( __('- No valid value. Auto correction nearest valid value:', 'decimal-product-quantity-for-woocommerce') ),
				'msg_more_than_the_max_allowed' => esc_html( __('- More than the maximum allowed for this Product. Auto correction to Max:', 'decimal-product-quantity-for-woocommerce') ),
			);
			WDPQ_Debugger ($Params, '$Params', $debug_process, __FUNCTION__, __LINE__);
			
			wp_localize_script('wdpq_page_product', 'wdpq_script_params', $Params);
		}
	}
	
	/* Корзина. 
	 * Авто-Коррекция неправильно введенного Значения Количества.
	 * AJAX Обновление Корзины при изменении Количества Товара.
	----------------------------------------------------------------- */	
	add_action ('woocommerce_before_cart', 'WooDecimalProduct_Action_before_cart', 10);
	function WooDecimalProduct_Action_before_cart () {
		$debug_process = 'cart';
		
		$WooDecimalProduct_ConsoleLog_Debuging		= get_option ('woodecimalproduct_debug_log', 0);
		$WooDecimalProduct_Auto_Correction_Quantity	= get_option ('woodecimalproduct_auto_correction_qnt', 1);
		$WooDecimalProduct_AJAX_Cart_Update			= get_option ('woodecimalproduct_ajax_cart_update', 0);	
		$WooDecimalProduct_ButtonsPM_Cart_Enable	= get_option ('woodecimalproduct_buttonspm_cart_enable', 0);

		WDPQ_Debugger ($WooDecimalProduct_Auto_Correction_Quantity, '$WooDecimalProduct_Auto_Correction_Quantity', $debug_process, __FUNCTION__, __LINE__);
		WDPQ_Debugger ($WooDecimalProduct_AJAX_Cart_Update, '$WooDecimalProduct_AJAX_Cart_Update', $debug_process, __FUNCTION__, __LINE__);		
		
		$WooDecimalProduct_Cart = array();
		
		$No_MaxEmpty = '-1';	// Unlimited
		
		foreach( WC() -> cart -> get_cart() as $cart_item ){	
			// WDPQ_Debugger ($cart_item, '$cart_item', $cart_item, __FUNCTION__, __LINE__);			
			
			$product_id 		= $cart_item['data']->get_id();
			$parent_product_id 	= $cart_item['data']->get_parent_id();
			WDPQ_Debugger ($product_id, '$product_id', $debug_process, __FUNCTION__, __LINE__);
			WDPQ_Debugger ($parent_product_id, '$parent_product_id', $debug_process, __FUNCTION__, __LINE__);

			if ($parent_product_id > 0) {
				// Вариативный Товар.
				$product_id 		= $parent_product_id;
				$item_product_id 	= $cart_item['data']->get_id();
				
			} else {
				// Простой Товар.
				$item_product_id = $product_id;
			}
			WDPQ_Debugger ($item_product_id, '$item_product_id', $debug_process, __FUNCTION__, __LINE__);

			$cart_item_key 	= $cart_item['key'];
			WDPQ_Debugger ($cart_item_key, '$cart_item_key', $debug_process, __FUNCTION__, __LINE__);

			$WooDecimalProduct_Cart[$item_product_id] = $cart_item_key;
			WDPQ_Debugger ($WooDecimalProduct_Cart, '$WooDecimalProduct_Cart', $debug_process, __FUNCTION__, __LINE__);
			
			$WooDecimalProduct_QuantityData[$item_product_id] = WooDecimalProduct_Get_QuantityData_by_ProductID ($product_id, $No_MaxEmpty);
			WDPQ_Debugger ($WooDecimalProduct_QuantityData, '$WooDecimalProduct_QuantityData', $debug_process, __FUNCTION__, __LINE__);
		}
		
		$WooDecimalProduct_Plugin_URL = plugin_dir_url ( __FILE__ ); // со слэшем на конце

		$Plugin_Data = get_plugin_data( __FILE__ );
		$WooDecimalProduct_Plugin_Version = $Plugin_Data['Version'];

		$WooDecimalProduct_StorageType = get_option ('woodecimalproduct_storage_type', 'system');
		$StorageType_Local = 0;

		if ($WooDecimalProduct_StorageType == 'local') {
			if (! is_user_logged_in() ) {
				// StorageType: Local Storage
				$StorageType_Local = 1;
			}
		} 
	
		wp_enqueue_script ('wdpq_page_cart', $WooDecimalProduct_Plugin_URL .'includes/wdpq_page_cart.js', array(), $WooDecimalProduct_Plugin_Version); // phpcs:ignore 

		$Params = array (
		'cart_items_keys' => $WooDecimalProduct_Cart,
		'quantity_data' => $WooDecimalProduct_QuantityData,
		'debug_enable' => $WooDecimalProduct_ConsoleLog_Debuging,
		'autocorrection_enable' => $WooDecimalProduct_Auto_Correction_Quantity,
		'ajax_cart_update_enable' => $WooDecimalProduct_AJAX_Cart_Update,
		'buttons_pm_enable' => $WooDecimalProduct_ButtonsPM_Cart_Enable,
		'msg_no_valid_value' => esc_html( __('- No valid value. Auto correction nearest valid value:', 'decimal-product-quantity-for-woocommerce') ),
		'msg_more_than_the_max_allowed' => esc_html( __('- More than the maximum allowed for this Product. Auto correction to Max:', 'decimal-product-quantity-for-woocommerce') ),
		'storage_type_local' => $StorageType_Local,
		);		
		
		WDPQ_Debugger ($Params, '$Params', $debug_process, __FUNCTION__, __LINE__);
		
		wp_localize_script( 'wdpq_page_cart', 'wdpq_script_params', $Params );
	}
	
	/* Страница Товара. 
	 * "Price Unit-Label"
	----------------------------------------------------------------- */	
	add_action ('woocommerce_before_add_to_cart_button', 'WooDecimalProduct_Action_before_add_to_cart_button');
	function WooDecimalProduct_Action_before_add_to_cart_button () {	
		$debug_process = 'product_page';
		
		$WooDecimalProduct_Price_Unit_Label	= get_option ('woodecimalproduct_price_unit_label', 0);	
		WDPQ_Debugger ($WooDecimalProduct_Price_Unit_Label, '$WooDecimalProduct_Price_Unit_Label', $debug_process, __FUNCTION__, __LINE__);
		
		if ($WooDecimalProduct_Price_Unit_Label) {
			global $product;

			if ($product) {
				$Product_ID = $product -> get_id();
				WDPQ_Debugger ($Product_ID, '$Product_ID', $debug_process, __FUNCTION__, __LINE__);
				
				$Price_Unit_Label = WooDecimalProduct_Get_PriceUnitLabel_by_ProductID ($Product_ID);
				WDPQ_Debugger ($Price_Unit_Label, '$Price_Unit_Label', $debug_process, __FUNCTION__, __LINE__);
				
				echo $Price_Unit_Label; // phpcs:ignore 					
			}				
		}		
	}
	
	/* Страница Каталог Товаров. / "Похожие Товары"
	 * "Price Unit-Label"
	----------------------------------------------------------------- */	
	add_filter ('woocommerce_loop_add_to_cart_link', 'WooDecimalProduct_Filter_loop_add_to_cart_link', 10, 2);
	function WooDecimalProduct_Filter_loop_add_to_cart_link ($add_to_cart_html, $product) {
		$debug_process = 'catalog_page';

		WDPQ_Debugger ($add_to_cart_html, '$add_to_cart_html', $debug_process, __FUNCTION__, __LINE__);
		// WDPQ_Debugger ($product, '$product', $debug_process, __FUNCTION__, __LINE__);
		
		$WooDecimalProduct_Price_Unit_Label	= get_option ('woodecimalproduct_price_unit_label', 0);
		WDPQ_Debugger ($WooDecimalProduct_Price_Unit_Label, '$WooDecimalProduct_Price_Unit_Label', $debug_process, __FUNCTION__, __LINE__);
		
		if ($WooDecimalProduct_Price_Unit_Label) {
			$Product_ID = $product -> get_id();
			WDPQ_Debugger ($Product_ID, '$Product_ID', $debug_process, __FUNCTION__, __LINE__);
			
			if ($Product_ID) {
				$Price_Unit_Label = WooDecimalProduct_Get_PriceUnitLabel_by_ProductID ($Product_ID);
				WDPQ_Debugger ($Price_Unit_Label, '$Price_Unit_Label', $debug_process, __FUNCTION__, __LINE__);
				
				if ($Price_Unit_Label) {
					$add_to_cart_html = $Price_Unit_Label .$add_to_cart_html;
				}
			}			
		}
		
		return $add_to_cart_html;
	}

	/* WDPQ Cart. Событие после Обновления позиции в Корзине.
	 * Обновляем WDPQ Cart 
	 * woocommerce\includes\class-wc-cart-session.php
	----------------------------------------------------------------- */
	add_filter ('woocommerce_cart_contents_changed', 'WooDecimalProduct_Filter_cart_contents_changed');
	function WooDecimalProduct_Filter_cart_contents_changed ($Cart_Content) {	
		$debug_process = 'cart_contents_changed';
		
		WDPQ_Debugger ($Cart_Content, '$Cart_Content', $debug_process, __FUNCTION__, __LINE__);
		
		$Cart_Items = isset( $_REQUEST['cart'] ) ? $_REQUEST['cart'] : null; // phpcs:ignore	
		WDPQ_Debugger ($_REQUEST, '$_REQUEST', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore
		
		// Это - Корзина.
		if ($Cart_Items) {
			$WDPQ_Cart = array ();
			
			foreach ($Cart_Items as $Item_Key => $Item_Value) {
				$Item_Quantity = $Item_Value['qty'];
				
				$WDPQ_Cart_Item = WooDecimalProduct_Get_WDPQ_Cart_Item_by_CartProductKey ($Item_Key);
				WDPQ_Debugger ($WDPQ_Cart_Item, '$WDPQ_Cart_Item', $debug_process, __FUNCTION__, __LINE__);
				
				if ($WDPQ_Cart_Item) {
					$Item = array(
						'key' => $Item_Key,
						'product_id' => $WDPQ_Cart_Item['product_id'],
						'variation_id' => $WDPQ_Cart_Item['variation_id'],
						'quantity' => $Item_Quantity,
						'price' => $WDPQ_Cart_Item['price'],
					);
					
					$WDPQ_Cart[] = $Item;
				}
			}

			WDPQ_Debugger ($WDPQ_Cart, '$WDPQ_Cart', $debug_process, __FUNCTION__, __LINE__);
			
			WooDecimalProduct_Delete_WDPQ_CartSession ($isDraft = false);
			WooDecimalProduct_Update_WDPQ_CartSession ($WDPQ_Cart, $isDraft = false);
		} else {
			// Игнорируем Попытки Обновления Корзины из любых других мест.
			// Т.к. открытие любой страницы - Обновляет Корзину с Округлением!!!
			// Woo - Вы это делаете Намеренно. Говно.
		}	
		
		return $Cart_Content;
	}

	/* WDPQ Cart. Delete Product from WDPQ-Cart on Cart Page.
	 * woocommerce\includes\class-wc-form-handler.php
	----------------------------------------------------------------- */
	add_action ('woocommerce_remove_cart_item', 'WooDecimalProduct_Action_remove_cart_item', 10, 2);
	function WooDecimalProduct_Action_remove_cart_item ($cart_item_key, $instance) {
		$debug_process = 'remove_cart_item';

		WDPQ_Debugger ($cart_item_key, '$cart_item_key', $debug_process, __FUNCTION__, __LINE__);
		
		$Cart_Contents = $instance -> cart_contents;
		
		$Product_ID = $Cart_Contents[$cart_item_key]['product_id'];
		WDPQ_Debugger ($Product_ID, '$Product_ID', $debug_process, __FUNCTION__, __LINE__);
		
		if ($Product_ID) {
			$WDPQ_Cart = WooDecimalProduct_Get_WDPQ_CartSession();
			
			if ($WDPQ_Cart) {
				$NewCart_Data = array();
				
				foreach ($WDPQ_Cart as $Cart_Product_Item) {
					$Cart_Product_Key 	= $Cart_Product_Item['key'];
					$Cart_Product_ID 	= $Cart_Product_Item['product_id'];
					$Cart_Variation_ID 	= $Cart_Product_Item['variation_id'];
					$Cart_Quantity 		= $Cart_Product_Item['quantity'];
					$Cart_Price 		= $Cart_Product_Item['price'];
					$Quantity_Precision = $Cart_Product_Item['quantity_precision'];					
				
					$Item_RegularPrice 	= $Cart_Product_Item['regular_price'];
					$Item_SalePrice 	= $Cart_Product_Item['sale_price'];
					$Price_Excl_Tax 	= $Cart_Product_Item['price_tax_excl'];
					$Price_Incl_Tax 	= $Cart_Product_Item['price_tax_incl'];
					$Tax_Amount 		= $Cart_Product_Item['tax_amount'];					
					
					if ($Cart_Product_ID != $Product_ID) {
						$Item = array(
							'key' => $Cart_Product_Key,
							'product_id' => $Cart_Product_ID,
							'variation_id' => $Cart_Variation_ID,
							'quantity' => $Cart_Quantity,
							'quantity_precision' => $Quantity_Precision,							
							'price' => $Cart_Price,
							'regular_price' => $Item_RegularPrice,
							'sale_price' => $Item_SalePrice,
							'price_tax_excl' => $Price_Excl_Tax,
							'price_tax_incl' => $Price_Incl_Tax,
							'tax_amount' => $Tax_Amount,							
						);

						$NewCart_Data[] = $Item;
					}
				}

				if( empty( $NewCart_Data ) ) {
					// Delete WDPQ CartSession.
					WooDecimalProduct_Delete_WDPQ_CartSession ($isDraft = false);
					
				} else {
					// Update WDPQ CartSession.
					WooDecimalProduct_Delete_WDPQ_CartSession ($isDraft = false);					
					WooDecimalProduct_Update_WDPQ_CartSession ($NewCart_Data, $isDraft = false);					
				}
			} else {
				// Это странно.
			}
		}
	}

	/* WDPQ Cart. Clear WDPQ-Cart after Payment. 
	 * woocommerce\includes\wc-cart-functions.php
	----------------------------------------------------------------- */
	add_filter ('woocommerce_should_clear_cart_after_payment', 'WooDecimalProduct_Filter_should_clear_cart_after_payment', 10);
	function WooDecimalProduct_Filter_should_clear_cart_after_payment ($should_clear_cart_after_payment) {	
		$debug_process = 'clear_cart_after_payment';

		WDPQ_Debugger ($should_clear_cart_after_payment, '$should_clear_cart_after_payment', $debug_process, __FUNCTION__, __LINE__);
		
		if ($should_clear_cart_after_payment) {
			// Clear.
			WooDecimalProduct_Delete_WDPQ_CartSession ($isDraft = true);
			WooDecimalProduct_Delete_WDPQ_CartSession ($isDraft = false);
		}
		
		return $should_clear_cart_after_payment;
	}	
	
	/* Корзина. Classic.
	 * Переопределяем WC-Cart непосредственно перед формированием Контента Корзины
	----------------------------------------------------------------- */
	add_action('woocommerce_before_cart_contents', 'WooDecimalProduct_Action_before_cart_contents', 9999);
	function WooDecimalProduct_Action_before_cart_contents () {
		$debug_process = 'before_cart_contents';

		$WooCart = WC() -> cart -> get_cart();
		// WDPQ_Debugger ($WooCart, '$WooCart', $debug_process, __FUNCTION__, __LINE__);
			
		foreach ( $WooCart as $Cart_Item_Key => $cart_item ) {
			WDPQ_Debugger ($Cart_Item_Key, '$Cart_Item_Key', $debug_process, __FUNCTION__, __LINE__);
			// WDPQ_Debugger ($cart_item, '$cart_item', $debug_process, __FUNCTION__, __LINE__);

			$WDPQ_Cart_Item = WooDecimalProduct_Get_WDPQ_Cart_Item_by_CartProductKey ($Cart_Item_Key);
			WDPQ_Debugger ($WDPQ_Cart_Item, '$WDPQ_Cart_Item', $debug_process, __FUNCTION__, __LINE__);
			
			if ($WDPQ_Cart_Item) {
				$WDPQ_Cart_Item_Quantity = $WDPQ_Cart_Item['quantity'];
				WDPQ_Debugger ($WDPQ_Cart_Item_Quantity, '$WDPQ_Cart_Item_Quantity', $debug_process, __FUNCTION__, __LINE__);

				if ($WDPQ_Cart_Item_Quantity > 0) {
					WC() -> cart -> set_quantity( $Cart_Item_Key, $WDPQ_Cart_Item_Quantity, $refresh_totals = true );
				}
			}
		}
	
		return true;
	}	
	
	/* Cart. Total.
	 * woocommerce\templates\cart\cart-totals.php
	 * woocommerce\includes\class-wc-cart.php
	 * get_total
	 * Срабатывает раньше, чем: Cart. Item Subtotal.
	----------------------------------------------------------------- */
	add_filter ('woocommerce_cart_get_total', 'WooDecimalProduct_Filter_cart_get_total', 9999);
	function WooDecimalProduct_Filter_cart_get_total ($Cart_Total) {
		$debug_process = 'cart_get_total';

		WDPQ_Debugger ($Cart_Total, '$Cart_Total', $debug_process, __FUNCTION__, __LINE__);
		
		$WooDecimalProduct_StorageType = get_option ('woodecimalproduct_storage_type', 'system');
		
		// Инициализация Сессии, для Незалогиненых Пользователей.
		if (! is_user_logged_in() ) {
			if ($WooDecimalProduct_StorageType == 'session') {
				// StorageType: PHP Session
				$Session_Started = WooDecimalProduct_StartSession( $Initiator = __FUNCTION__ );
			}
		} 		
		
		return $Cart_Total;	
	}

	/* Cart. Tax. Amount.
	 * woocommerce\templates\cart\cart-totals.php
	 * woocommerce\includes\wc-cart-functions.php
	 * wc_cart_totals_taxes_total_html( $tax )
	----------------------------------------------------------------- */
	// Не отображаем. Потому, что тогда нужно делать так же и для Subtotal. А в нем нет Фильтра.
	add_filter ('woocommerce_cart_totals_taxes_total_html', 'WooDecimalProduct_Filter_cart_totals_taxes_total_html', 9999, 1);
	function WooDecimalProduct_Filter_cart_totals_taxes_total_html ($Tax_html) {	
		$debug_process = 'taxes_total';

		WDPQ_Debugger ($Tax_html, '$Tax_html', $debug_process, __FUNCTION__, __LINE__);
		
		// $Tax_About_Title = __('Real value may be Rounded', 'decimal-product-quantity-for-woocommerce');
		// $Tax_About_Text  = __('(may be Rounded)', 'decimal-product-quantity-for-woocommerce');
		
		// $Tax_html .= '<span class="wdpq_tax_about" title="' .$Tax_About_Title .'">' .$Tax_About_Text .'</span>';
		
		return $Tax_html;
	}	
	
	/* Cart. Total. Amount.
	 * woocommerce\templates\cart\cart-totals.php
	 * woocommerce\includes\wc-cart-functions.php
	 * wc_cart_totals_order_total_html( $tax )
	----------------------------------------------------------------- */		
	add_filter ('woocommerce_cart_totals_order_total_html', 'WooDecimalProduct_Filter_cart_totals_order_total_html', 9999, 1);
	function WooDecimalProduct_Filter_cart_totals_order_total_html ($Total_html) {	
		$debug_process = 'cart_total';

		WDPQ_Debugger ($Total_html, '$Total_html', $debug_process, __FUNCTION__, __LINE__);
		
		// $Total_About_Title = __('Real value may be Rounded', 'decimal-product-quantity-for-woocommerce');
		// $Total_About_Text  = __('(may be Rounded)', 'decimal-product-quantity-for-woocommerce');
		
		// $Total_html .= '<span class="wdpq_total_about" title="' .$Total_About_Title .'">' .$Total_About_Text .'</span>';
		
		return $Total_html;
	}	

	/* Cart. Item Subtotal.
	 * woocommerce\templates\cart\cart.php
	 * woocommerce\includes\class-wc-cart.php
	----------------------------------------------------------------- */
	add_filter ('woocommerce_cart_product_subtotal', 'WooDecimalProduct_Filter_cart_product_subtotal', 9999, 4);
	function WooDecimalProduct_Filter_cart_product_subtotal ($Product_Subtotal, $Product, $Quantity, $Cart_Items) {	
		$debug_process = 'item_subtotal';

		WDPQ_Debugger ($Product_Subtotal, '$Product_Subtotal', $debug_process, __FUNCTION__, __LINE__);
		// WDPQ_Debugger ($Product, '$Product', $debug_process, __FUNCTION__, __LINE__);
		// WDPQ_Debugger ($Quantity, '$Quantity', $debug_process, __FUNCTION__, __LINE__);
		// WDPQ_Debugger ($Cart_Items, '$Cart_Items', $debug_process, __FUNCTION__, __LINE__);

		return $Product_Subtotal;
	}

	/* Cart. Subtotal.
	 * woocommerce\templates\cart\cart.php
	 * woocommerce\includes\class-wc-cart.php
	 * get_cart_subtotal get_subtotal
	 * Срабатывает раньше Cart. Item Subtotal.
	----------------------------------------------------------------- */
	add_filter ('woocommerce_cart_get_subtotal', 'WooDecimalProduct_Filter_cart_get_subtotal', 9999);
	function WooDecimalProduct_Filter_cart_get_subtotal ($Cart_Subtotal) {
		$debug_process = 'cart_subtotal';

		WDPQ_Debugger ($Cart_Subtotal, '$Cart_Subtotal', $debug_process, __FUNCTION__, __LINE__);
		
		$isWDPQ_Cart_Empty = WooDecimalProduct_is_WDPQCart_Empty ();
		WDPQ_Debugger ($isWDPQ_Cart_Empty, '$isWDPQ_Cart_Empty', $debug_process, __FUNCTION__, __LINE__);

		if ($isWDPQ_Cart_Empty) {	
			return 0;
		}

		return $Cart_Subtotal;
	}
	
	/* Cart. Ситуация, когда Корзина была сформирована, но Браузер закрыли и открыли снова.
	 * отсутствует WDPQ_Cart. Оформление невозможно.
	 * woocommerce\templates\cart\cart.php
	----------------------------------------------------------------- */	
	add_action ('woocommerce_after_cart', 'WooDecimalProduct_Action_after_cart', 9999);
	function WooDecimalProduct_Action_after_cart () {
		$debug_process = 'after_cart';
		
		$isWDPQ_Cart_Empty = WooDecimalProduct_is_WDPQCart_Empty ();
		WDPQ_Debugger ($isWDPQ_Cart_Empty, '$isWDPQ_Cart_Empty', $debug_process, __FUNCTION__, __LINE__);
		
		if ($isWDPQ_Cart_Empty) {			
			$Add_to_DraftCart = array ();
				
			$WooCart = WC() -> cart;
			// WDPQ_Debugger ($WooCart, '$WooCart', $debug_process, __FUNCTION__, __LINE__);

			if ($WooCart) {
				$Cart_Contents = $WooCart -> cart_contents;	
				// WDPQ_Debugger ($Cart_Contents, '$Cart_Contents', $debug_process, __FUNCTION__, __LINE__);
				
				if ($Cart_Contents) {							
					foreach ($Cart_Contents as $key => $Cart_Item) {
						WDPQ_Debugger ($Cart_Item, '$Cart_Item', $debug_process, __FUNCTION__, __LINE__);
						
						$Item_Key 			= $Cart_Item['key'];
						$Item_ProductID 	= $Cart_Item['product_id'];
						$Item_VariationID 	= $Cart_Item['variation_id'];
						
						$Cart_Item_Product 	= $Cart_Item['data'];
						// WDPQ_Debugger ($Cart_Item_Product, '$Cart_Item_Product', $debug_process, __FUNCTION__, __LINE__);
						
						$Item_Price 		= $Cart_Item_Product -> get_price();							
						$Item_RegularPrice 	= $Cart_Item_Product -> get_regular_price();
						$Item_SalePrice 	= $Cart_Item_Product -> get_sale_price();

						$Item_Price 		= floatval( $Item_Price );
						$Item_RegularPrice 	= floatval( $Item_RegularPrice );
						$Item_SalePrice 	= floatval( $Item_SalePrice );

						WDPQ_Debugger ($Item_Price, '$Item_Price', $debug_process, __FUNCTION__, __LINE__);
						WDPQ_Debugger ($Item_RegularPrice, '$Item_RegularPrice', $debug_process, __FUNCTION__, __LINE__);
						WDPQ_Debugger ($Item_SalePrice, '$Item_SalePrice', $debug_process, __FUNCTION__, __LINE__);

						$Price_Excl_Tax = wc_get_price_excluding_tax( $Cart_Item_Product ); 	// price without VAT
						$Price_Incl_Tax = wc_get_price_including_tax( $Cart_Item_Product );  	// price with VAT
						$Tax_Amount     = $Price_Incl_Tax - $Price_Excl_Tax; 		// VAT amount

						WDPQ_Debugger ($Price_Excl_Tax, '$Price_Excl_Tax', $debug_process, __FUNCTION__, __LINE__);
						WDPQ_Debugger ($Price_Incl_Tax, '$Price_Incl_Tax', $debug_process, __FUNCTION__, __LINE__);
						WDPQ_Debugger ($Tax_Amount, '$Tax_Amount', $debug_process, __FUNCTION__, __LINE__);
						
						$Item_Quantity = 1;
						
						$Item = array(
							'key' => $Item_Key,
							'product_id' => $Item_ProductID,
							'variation_id' => $Item_VariationID,
							'quantity' => $Item_Quantity,
							'price' => $Item_Price,
							'regular_price' => $Item_RegularPrice,
							'sale_price' => $Item_SalePrice,
							'price_tax_excl' => $Price_Excl_Tax,
							'price_tax_incl' => $Price_Incl_Tax,
							'tax_amount' => $Tax_Amount,								
						);
						
						$Add_to_DraftCart[] = $Item;
						WDPQ_Debugger ($Add_to_DraftCart, '$Add_to_DraftCart', $debug_process, __FUNCTION__, __LINE__);
					}
					
					WooDecimalProduct_Update_WDPQ_CartSession ($Add_to_DraftCart, $isDraft = true);
					
					$WDPQ_Nonce = 'Restore_WDPQ-Cart_DecimalProductQuantityForWooCommerce';
					$nonce = wp_create_nonce ($WDPQ_Nonce);	
					
					$WooDecimalProduct_Plugin_URL = plugin_dir_url ( __FILE__ ); // со слэшем на конце

					$Plugin_Data = get_plugin_data( __FILE__ );
					$WooDecimalProduct_Plugin_Version = $Plugin_Data['Version'];

					wp_enqueue_script ('wdpq_page_cart_restore', $WooDecimalProduct_Plugin_URL .'includes/wdpq_page_cart_restore.js', array(), $WooDecimalProduct_Plugin_Version); // phpcs:ignore 

					$Params = array (
						'nonce' => esc_html( $nonce ),
						'item_subtotal_title' => esc_html( __('Will be available after creating the Cart', 'decimal-product-quantity-for-woocommerce') ),
						'button_class' => esc_html( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ),
						'button_value' => esc_html( 'create_cart', 'decimal-product-quantity-for-woocommerce'),
						'button_label' => esc_html( __('Create Cart', 'decimal-product-quantity-for-woocommerce') ),
					);
					WDPQ_Debugger ($Params, '$Params', $debug_process, __FUNCTION__, __LINE__);
					
					wp_localize_script('wdpq_page_cart_restore', 'wdpq_script_cart_restore_params', $Params);			
					
					echo '<div class="wdpq_about_create_cart">' .esc_html( __('These Products were in your previous Cart. You can "Create Cart" based on them.', 'decimal-product-quantity-for-woocommerce' ) ).'</div>';
					
					wc_empty_cart();
				}
			}	
		}			
	}

	/* Cart. Переход на Страницу Cart "cart.php", вместо Страницы: "cart-empty.php" если WDPQ-Cart не пустая.
	 * woocommerce\templates\cart\cart-empty.php
	----------------------------------------------------------------- */	
	add_action ('woocommerce_cart_is_empty', 'WooDecimalProduct_Action_cart_is_empty', 9999);
	function WooDecimalProduct_Action_cart_is_empty () {
		$debug_process = 'cart-empty';

		WDPQ_Debugger ($_REQUEST, '$_REQUEST', $debug_process, __FUNCTION__, __LINE__); // phpcs:ignore

		$Action = isset($_REQUEST['wdpq_create_cart']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_create_cart'])) : null; // phpcs:ignore
		$Nonce 	= isset($_REQUEST['wdpq_wpnonce']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_wpnonce'])) : 'none'; // phpcs:ignore	

		if ($Action == 'create_cart') {
			$WDPQ_Nonce = 'Restore_WDPQ-Cart_DecimalProductQuantityForWooCommerce';

			if (!wp_verify_nonce( $Nonce, $WDPQ_Nonce )) {
				exit;
			}
			
			$Cart_Items = isset( $_REQUEST['cart'] ) ? $_REQUEST['cart'] : null; // phpcs:ignore	
			WDPQ_Debugger ($Cart_Items, '$Cart_Items', $debug_process, __FUNCTION__, __LINE__);
				
			$isWDPQ_Cart_Empty = WooDecimalProduct_is_WDPQCart_Empty ();
			WDPQ_Debugger ($isWDPQ_Cart_Empty, '$isWDPQ_Cart_Empty', $debug_process, __FUNCTION__, __LINE__);
			
			if ($isWDPQ_Cart_Empty) {
				$Draft_Cart = WooDecimalProduct_Get_WDPQ_CartSession ($isDraft = true);	
				WDPQ_Debugger ($Draft_Cart, '$Draft_Cart', $debug_process, __FUNCTION__, __LINE__);
				
				// Create WDPQ-Cart
				if ($Draft_Cart) {
					$Add_to_Cart = array ();
					
					foreach ($Draft_Cart as $Draft_Cart_Item) {
						$Item_Key 			= $Draft_Cart_Item['key'];
						$Item_ProductID 	= $Draft_Cart_Item['product_id'];
						$Item_VariationID 	= $Draft_Cart_Item['variation_id'];
						$Item_Quantity		= $Draft_Cart_Item['quantity'];
						$Item_Price			= $Draft_Cart_Item['price'];
						
						$Item_RegularPrice	= $Draft_Cart_Item['regular_price'];
						$Item_SalePrice		= $Draft_Cart_Item['sale_price'];
						$Price_Excl_Tax		= $Draft_Cart_Item['price_tax_excl'];
						$Price_Incl_Tax		= $Draft_Cart_Item['price_tax_incl'];
						$Tax_Amount			= $Draft_Cart_Item['tax_amount'];
						
						if ($Cart_Items) {
							$Item_Quantity = isset( $Cart_Items[$Item_Key]['qty'] ) ? $Cart_Items[$Item_Key]['qty'] : $Item_Quantity;
						}
						
						$Item = array(
							'key' => $Item_Key,
							'product_id' => $Item_ProductID,
							'variation_id' => $Item_VariationID,
							'quantity' => $Item_Quantity,
							'price' => $Item_Price,
							'regular_price' => $Item_RegularPrice,
							'sale_price' => $Item_SalePrice,
							'price_tax_excl' => $Price_Excl_Tax,
							'price_tax_incl' => $Price_Incl_Tax,
							'tax_amount' => $Tax_Amount,							
						);
						WDPQ_Debugger ($Item, '$Item', $debug_process, __FUNCTION__, __LINE__);
						
						$Add_to_Cart[] = $Item;
						
						// Формируем Корзину Woo
						WC() -> cart -> add_to_cart( $Item_ProductID, $Item_Quantity, $Item_VariationID );
					}
					
					WDPQ_Debugger ($Add_to_Cart, '$Add_to_Cart', $debug_process, __FUNCTION__, __LINE__);
					
					WooDecimalProduct_Update_WDPQ_CartSession ($Add_to_Cart, $isDraft = false);

					WooDecimalProduct_Delete_WDPQ_CartSession ($isDraft = true);				
					
					// Возвращаемся на Страницу Корзина избегая: "Повторная отправка Формы"
					wp_safe_redirect( wc_get_cart_url() );
					exit;				
				}
			}	
		}
	}
	
	/* DashBoard. Уведомления.
	 *
	 * Coupons. Woo Settings.
	 * woocommerce\templates\cart\cart-totals.php
	 * woocommerce\includes\wc-cart-functions.php
	 * wc_cart_totals_coupon_html( $coupon )
	 *
	 * Block Layouts. All Pages.
	----------------------------------------------------------------- */	
	add_action ('admin_notices', 'WooDecimalProduct_Action_admin_notices');
	function WooDecimalProduct_Action_admin_notices () {
		$debug_process = 'admin_notices';
		
		// Coupons
		$screen = get_current_screen();
		$screen_id = $screen -> id;
		// WDPQ_Debugger ($screen_id, '$screen_id', $debug_process, __FUNCTION__, __LINE__);

		// if ($screen_id == 'edit-shop_coupon') {
			// $Notice = __( 'Correct processing of coupons is possible only in the Pro version "Decimal Product Quantity for WooCommerce". Sorry.', 'decimal-product-quantity-for-woocommerce' );
	
			// echo '<div id="wdpq_warning_coupons" class="notice notice-warning notice-wdpq"><p>' .esc_html( $Notice ) .'</p></div>';
		// }	
		
		// Block Layouts
		$BlockLayots = WooDecimalProduct_Blocks_Check_BlockLayots();
		WDPQ_Debugger ($BlockLayots, '$BlockLayots', $debug_process, __FUNCTION__, __LINE__);
		
		$BlockLayots_Items = '';
		
		foreach ($BlockLayots as $Item) {
			if ($BlockLayots_Items) {
				$BlockLayots_Items .= ', ';
			}
			$BlockLayots_Items .= $Item;
		}
		
		if ($BlockLayots_Items) {
			$Notice = __( 'The current version of the plugin "Decimal Product Quantity for Woocommerce" will not work correctly if the pages: Cart, Checkout, Order - have a block structure. Check the Pages: ', 'decimal-product-quantity-for-woocommerce' );
			$Notice .= $BlockLayots_Items;
			
			echo '<div id="wdpq_warning_blocks" class="notice notice-warning notice-wdpq"><p>' .esc_html( $Notice ) .'</p></div>';
		}
	}
	
	/* Checkout
	----------------------------------------------------------------- */
	add_action('woocommerce_checkout_order_processed', 'WooDecimalProduct_Action_checkout_order_processed', 9999, 3);
	function WooDecimalProduct_Action_checkout_order_processed ($Order_ID, $posted_data, $order) {
		$debug_process = 'checkout';
		
		if ( version_compare( WC_VERSION, '9.4.3', '>' ) ) {
			// Версия Woo > 9.4.3 Требуется дополнительная обработка Данных Заказа.
			
			// Позиции Ордера теперь хранятся в Таблице: woocommerce_order_itemmeta 
			// woocommerce\includes\class-wc-post-data.php
			// update_order_item_metadata
			// 	update_metadata( 'order_item', $object_id, $meta_key, $meta_value, $prev_value );		

			WDPQ_Debugger ($Order_ID, '$Order_ID', $debug_process, __FUNCTION__, __LINE__);
			// WDPQ_Debugger ($posted_data, '$posted_data', $debug_process, __FUNCTION__, __LINE__);
			// WDPQ_Debugger ($order, '$order', $debug_process, __FUNCTION__, __LINE__);

			if ($Order_ID) {
				global $wpdb;
				
				$Table_WooOrderItems 		= $wpdb -> prefix .'woocommerce_order_items';
				$Table_WooOrderItemMeta 	= $wpdb -> prefix .'woocommerce_order_itemmeta';
				
				$Query = "
					SELECT * 
					FROM $Table_WooOrderItems 
					WHERE (
						order_id = %d
					)
				";			
				
				$OrderItems = $wpdb -> get_results( $wpdb -> prepare( $Query, $Order_ID ) ); // phpcs:ignore 
				WDPQ_Debugger ($OrderItems, '$OrderItems', $debug_process, __FUNCTION__, __LINE__);
				
				if ($OrderItems) {
					foreach ($OrderItems as $Item) {
						$Item_ID 	= $Item -> order_item_id;
						$Item_Type 	= $Item -> order_item_type;
						
						if ($Item_ID) {
							if ($Item_Type == 'line_item') {
								// Мета-Данные Товара
								
								// Product_ID
								$Query = "
									SELECT meta_value
									FROM $Table_WooOrderItemMeta 
									WHERE (
										order_item_id = %d
										AND
										meta_key = '_product_id'
									)
								";
								
								$Product_ID = $wpdb -> get_var( $wpdb -> prepare( $Query, $Item_ID ) ); // phpcs:ignore 
								WDPQ_Debugger ($Product_ID, '$Product_ID', $debug_process, __FUNCTION__, __LINE__);
								
								// Variation_ID
								$Query = "
									SELECT meta_value
									FROM $Table_WooOrderItemMeta 
									WHERE (
										order_item_id = %d
										AND
										meta_key = '_variation_id'
									)
								";								
								
								$Variation_ID = $wpdb -> get_var( $wpdb -> prepare( $Query, $Item_ID ) ); // phpcs:ignore 
								WDPQ_Debugger ($Variation_ID, '$Variation_ID', $debug_process, __FUNCTION__, __LINE__);
								
								if ($Variation_ID) {
									//Вариативный Товар.
									$Product_ID = $Variation_ID;
									
									$isVariation = true;
								} else {
									//Простой Товар.
									$isVariation = false;
								}
								
								if ($Product_ID) {
									$WDPQ_CartItem = WooDecimalProduct_Get_WDPQ_CartItem_by_ProductID ($Product_ID, $isVariation);
									WDPQ_Debugger ($WDPQ_CartItem, '$WDPQ_CartItem', $debug_process, __FUNCTION__, __LINE__);
									
									if ($WDPQ_CartItem) {
										$WDPQ_CartItem_Quantity = isset( $WDPQ_CartItem['quantity'] ) ? $WDPQ_CartItem['quantity'] : 1;
										$WDPQ_CartItem_Price 	= isset( $WDPQ_CartItem['price'] ) ? $WDPQ_CartItem['price'] : 0;
										
										$WDPQ_CartItem_Total = $WDPQ_CartItem_Price * $WDPQ_CartItem_Quantity;
										
										if ($WDPQ_CartItem_Total == 0) {
											$WDPQ_CartItem_Total = 1;
										}
										
										// Сложности и Глюки с Налогами.
										// Вынужден отключить Результаты ПостОбработки.
										// Будем наблюдать и думать.
										
											// OrderMeta. Update Quantity	
											WooDecimalProduct_Update_Order_Item_Meta ($Item_ID, '_qty', $WDPQ_CartItem_Quantity);					

											// OrderMeta. Update SubTotal
											// WooDecimalProduct_Update_Order_Item_Meta ($Item_ID, '_line_subtotal', $WDPQ_CartItem_Total);	

											// OrderMeta. Update Total
											// WooDecimalProduct_Update_Order_Item_Meta ($Item_ID, '_line_total', $WDPQ_CartItem_Total);
									}
								}
							}
						}
					}
				}
			}
		} else {
			// Заказ формируется корректно в версиях Woo до 9.4.3
		}	
	}

	/* Order. Complete. "Повторить Заказ" если Пользователь - Залогинен.
	 * woocommerce\includes\wc-template-functions.php
	 * woocommerce_order_again_button ()
	----------------------------------------------------------------- */	
	add_filter('woocommerce_valid_order_statuses_for_order_again', 'WooDecimalProduct_valid_order_statuses_for_order_again', 9999, 1);
	function WooDecimalProduct_valid_order_statuses_for_order_again ($completed) {
		$debug_process = 'order_again';
		
		WDPQ_Debugger ($completed, '$completed', $debug_process, __FUNCTION__, __LINE__);
		
		// Возможно, это будет в PRO версии.
		// Но пока, просто скрываем такую Кнопку.
		
		$completed = array();
		
		return $completed;
	}