<?php
/*
Plugin Name: Decimal Product Quantity for WooCommerce
Plugin URI: https://wpgear.xyz/decimal-product-quantity-woo
Description: Decimal Product Quantity for WooCommerce. (Piece of Product). Min, Max, Step & Default preset. Variable Products Supported. Auto correction "No valid value". Update Cart Automatically on Quantity Change (AJAX Cart Update). Read about <a href="http://wpgear.xyz/decimal-product-quantity-woo-pro/">PRO Version</a> for separate Minimum Quantity, Step of Changing & Default preset Quantity - for each Product Variation. Create XML/RSS Feed for WooCommerce. Support: "Google Merchant Center" (Product data specification) whith "Price_Unit_Label" -> [unit_pricing_measure], separate hierarchy Categories -> Products.
Version: 17.50.1
Text Domain: decimal-product-quantity-for-woocommerce
Domain Path: /languages
Author: WPGear
Author URI: https://wpgear.xyz
License: GPLv2
*/

	include_once( __DIR__ .'/includes/functions.php' );
	include_once( __DIR__ .'/includes/admin/admin_setup_woo.php' );
	include_once( __DIR__ .'/includes/admin/admin_setup_product.php' );
	include_once( __DIR__ .'/includes/admin/admin_setup_category.php' );
	include_once( __DIR__ .'/includes/admin/admin_order.php' );

	WooDecimalProduct_Check_Updated ();
	
	$WooDecimalProduct_Min_Quantity_Default    	= get_option ('woodecimalproduct_min_qnt_default', 1);  
    $WooDecimalProduct_Step_Quantity_Default   	= get_option ('woodecimalproduct_step_qnt_default', 1); 
	$WooDecimalProduct_Item_Quantity_Default   	= get_option ('woodecimalproduct_item_qnt_default', 1);
	$WooDecimalProduct_Max_Quantity_Default    	= get_option ('woodecimalproduct_max_qnt_default', '');  
	
	$WooDecimalProduct_ButtonsPM_Product_Enable	= get_option ('woodecimalproduct_buttonspm_product_enable', 0);
	$WooDecimalProduct_ButtonsPM_Cart_Enable	= get_option ('woodecimalproduct_buttonspm_cart_enable', 0);	
	
	$WooDecimalProduct_Auto_Correction_Quantity	= get_option ('woodecimalproduct_auto_correction_qnt', 1);
	$WooDecimalProduct_AJAX_Cart_Update			= get_option ('woodecimalproduct_ajax_cart_update', 0);	
	
	$WooDecimalProduct_ConsoleLog_Debuging		= get_option ('woodecimalproduct_debug_log', 0);
	$WooDecimalProduct_Uninstall_Del_MetaData 	= get_option ('woodecimalproduct_uninstall_del_metadata', 0);
	
	$WooDecimalProduct_Plugin_URL 		= plugin_dir_url ( __FILE__ ); // со слэшем на конце	
	$WooDecimalProduct_Plugin_Version 	= '';
	
	$WooDecimalProduct_LocalePath = dirname (plugin_basename ( __FILE__ )) . '/languages/';
	__('Decimal Product Quantity for WooCommerce. (Piece of Product). Min, Max, Step & Default preset. Variable Products Supported. Auto correction "No valid value". Update Cart Automatically on Quantity Change (AJAX Cart Update). Read about <a href="http://wpgear.xyz/decimal-product-quantity-woo-pro/">PRO Version</a> for separate Minimum Quantity, Step of Changing & Default preset Quantity - for each Product Variation. Create XML/RSS Feed for WooCommerce. Support: "Google Merchant Center" (Product data specification) whith "Price_Unit_Label" -> [unit_pricing_measure], separate hierarchy Categories -> Products.', 'decimal-product-quantity-for-woocommerce');	
	
	/* JS Script.
	----------------------------------------------------------------- */	
	add_action ('wp_enqueue_scripts', 'WooDecimalProduct_Admin_Style', 25);
	add_action ('admin_enqueue_scripts', 'WooDecimalProduct_Admin_Style', 25);
	function WooDecimalProduct_Admin_Style ($hook) {		
		global $WooDecimalProduct_Plugin_URL;
		
		wp_enqueue_script ('woodecimalproduct', $WooDecimalProduct_Plugin_URL .'includes/woodecimalproduct.js'); // phpcs:ignore 
		
		wp_enqueue_style ('wdpq_style', $WooDecimalProduct_Plugin_URL .'style.css'); // phpcs:ignore
	}

	/* AJAX Processing
	----------------------------------------------------------------- */
    add_action ('wp_ajax_WooDecimalProductQNT', 'WooDecimalProduct_Ajax');
    function WooDecimalProduct_Ajax() {
		include_once ('includes/ajax_processing.php');
    }		

	/* Translate.
	----------------------------------------------------------------- */
	add_action ('plugins_loaded', 'WooDecimalProduct_Action_plugins_loaded');
	function WooDecimalProduct_Action_plugins_loaded() {
		global $WooDecimalProduct_LocalePath;		
		
		load_plugin_textdomain ('decimal-product-quantity-for-woocommerce', false, $WooDecimalProduct_LocalePath);		
	}
	
	/* Init. Инициализация.
     * Запускаем самым последним, чтобы быть уверенным, что WooCommerce уже инициализировался.
	----------------------------------------------------------------- */ 
	add_action ('init', 'WooDecimalProduct_Init', 999999);
	function WooDecimalProduct_Init () {		
		WooDecimalProduct_Woo_remove_filters();	

		// "WooCommerce High-Performance Order Storage" Mode
		$WooDecimalProduct_is_HPOS_Mode_Enable = filter_var( get_option( 'woocommerce_custom_orders_table_enabled', false ), FILTER_VALIDATE_BOOLEAN );
		WooDecimalProduct_Debugger ($WooDecimalProduct_is_HPOS_Mode_Enable, __FUNCTION__ .' $WooDecimalProduct_is_HPOS_Mode_Enable ' .__LINE__, 'init', true);	
		
		$Plugin_Data = get_plugin_data( __FILE__ );

		global $WooDecimalProduct_Plugin_Version;
		$WooDecimalProduct_Plugin_Version = $Plugin_Data['Version'];
	}
	
	/* Страница Товара и Корзина
     * Минимальное / Максимально кол-во выбора Товара, Шаг, Значение по-Умолчанию на странице Товара и Корзины.
	 * woocommerce\includes\wc-template-functions.php
	 * Woo version > 9.4.3
    ----------------------------------------------------------------- */   
	add_filter ('woocommerce_quantity_input_args', 'WooDecimalProduct_Filter_quantity_input_args', 999999, 2);
    function WooDecimalProduct_Filter_quantity_input_args($args, $product) {
		WooDecimalProduct_Debugger ($args, __FUNCTION__ .' $args ' .__LINE__, 'quantity_input_args', true);

		if ($product) {
			$Product_ID = $product -> get_id();
			WooDecimalProduct_Debugger ($Product_ID, __FUNCTION__ .' $Product_ID ' .__LINE__, 'quantity_input_args', true);
			
			if ($Product_ID) {
				$item_product_id = $product -> get_parent_id();
				WooDecimalProduct_Debugger ($item_product_id, __FUNCTION__ .' $item_product_id ' .__LINE__, 'quantity_input_args', true);
				
				if ($item_product_id > 0) {
					// Вариативный Товар.
				} else {
					// Простой Товар.
					$item_product_id = $Product_ID;
				}
				
				$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($item_product_id);
				WooDecimalProduct_Debugger ($WooDecimalProduct_QuantityData, __FUNCTION__ .' $WooDecimalProduct_QuantityData ' .__LINE__, 'quantity_input_args', true);
				
				$Min_Qnt = $WooDecimalProduct_QuantityData['min_qnt'];
				$Max_Qnt = $WooDecimalProduct_QuantityData['max_qnt'];
				$Def_Qnt = $WooDecimalProduct_QuantityData['def_qnt'];
				$Stp_Qnt = $WooDecimalProduct_QuantityData['stp_qnt'];	
				
				$args['min_value'] 	= $Min_Qnt;			
				$args['step'] 		= $Stp_Qnt;
				$args['max_value'] 	= $Max_Qnt;			

				$Field_Input_Name 	= isset($args['input_name']) ? $args['input_name']: '';
				$Field_Input_Value 	= isset($args['input_value']) ? $args['input_value']: '';
				WooDecimalProduct_Debugger ($Field_Input_Name, __FUNCTION__ .' $Field_Input_Name ' .__LINE__, 'quantity_input_args', true);

				if ($Field_Input_Name == 'quantity') {
					// Страница Товара.
					if ($Field_Input_Value == 1) {
						// Возможно, надо изменить на Предустановленное значение.
						$args['input_value'] = $Def_Qnt;
					}			
				}
			
				// Возможно, это - Корзина. Например: 'cart[e00da03b685a0dd18fb6a08af0923de0][qty]'		
				$PosStart_Cart_Item_Key	= strpos( $Field_Input_Name, 'art[' );
				WooDecimalProduct_Debugger ($PosStart_Cart_Item_Key, __FUNCTION__ .' $PosStart_Cart_Item_Key ' .__LINE__, 'quantity_input_args', true);

				if ( $PosStart_Cart_Item_Key > 0) {
					$PosEnd_Cart_Item_Key = strpos( $Field_Input_Name, '][qty]' );

					if ($PosEnd_Cart_Item_Key > 0) {
						$Cart_Item_Key = substr($Field_Input_Name, $PosStart_Cart_Item_Key + 4, $PosEnd_Cart_Item_Key - 5);
						WooDecimalProduct_Debugger ($Cart_Item_Key, __FUNCTION__ .' $Cart_Item_Key ' .__LINE__, 'quantity_input_args', true);
						
						// Корзина. Корректируем $Quantity из WDPQ-Cart
						if ($Cart_Item_Key) {
							$Cart_Quantity = WooDecimalProduct_Get_WDPQ_Cart_Quantity_by_CartProductKey ($Cart_Item_Key);
							WooDecimalProduct_Debugger ($Cart_Item_Key, __FUNCTION__ .' $Cart_Item_Key ' .__LINE__, 'quantity_input_args', true);
							
							if ($Cart_Quantity > 0) {
								$args['input_value'] = $Cart_Quantity;
							}			
						}
					}
				}	
			}				
		}
			
		WooDecimalProduct_Debugger ($args, __FUNCTION__ .' $args ' .__LINE__, 'quantity_input_args', true);
        return $args;
    }     

    /* Вариативный Товар. Минимальное кол-во выбора Товара на странице Товара.
    ----------------------------------------------------------------- */ 
    add_filter ('woocommerce_available_variation', 'WooDecimalProduct_Filter_quantity_available_variation', 10, 3);
	function WooDecimalProduct_Filter_quantity_available_variation ($args, $product, $variation) {	
        $Product_ID = $product -> get_id();
		WooDecimalProduct_Debugger ($Product_ID, __FUNCTION__ .' $Product_ID ' .__LINE__, 'variation', true);

		$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID);
		WooDecimalProduct_Debugger ($WooDecimalProduct_QuantityData, __FUNCTION__ .' $WooDecimalProduct_QuantityData ' .__LINE__, 'variation', true);
		
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
		WooDecimalProduct_Debugger ($Passed, __FUNCTION__ .' $Passed ' .__LINE__, 'add_to_cart_validation', true);
		WooDecimalProduct_Debugger ($Product_ID, __FUNCTION__ .' $Product_ID ' .__LINE__, 'add_to_cart_validation', true);
		WooDecimalProduct_Debugger ($Quantity, __FUNCTION__ .' $Quantity ' .__LINE__, 'add_to_cart_validation', true);
		
		if ($Passed) {
			global $WooDecimalProduct_Max_Quantity_Default;
			
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
								WooDecimalProduct_Debugger ($WooDecimalProduct_QuantityData, __FUNCTION__ .' $WooDecimalProduct_QuantityData ' .__LINE__, 'add_to_cart_validation', true);

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
		WooDecimalProduct_Debugger ($message, __FUNCTION__ .' $message ' .__LINE__, 'add_to_cart_message', true);
		WooDecimalProduct_Debugger ($products, __FUNCTION__ .' $products ' .__LINE__, 'add_to_cart_message', true);
		WooDecimalProduct_Debugger ($_REQUEST, __FUNCTION__ .' $_REQUEST ' .__LINE__, 'add_to_cart_message', true); // phpcs:ignore	
		
		$Variation_ID = isset( $_REQUEST['variation_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['variation_id'] ) ) : 0; // phpcs:ignore
		
		$Add_to_Cart = array();
	
		$count = 0;	
		
		foreach ($products as $Product_ID => $qty) {
			if ($qty > 0) {				
				$Cart_Item_Key = WooDecimalProduct_Get_CartItem_Key_by_ProductID ($Product_ID);
				WooDecimalProduct_Debugger ($Cart_Item_Key, __FUNCTION__ .' $Cart_Item_Key ' .__LINE__, 'add_to_cart_message', true);
				
				$Item_Price = 0;
									
				if ($Variation_ID) {
					//Вариативный Товар.
					
					// $Product_Variation_Prices = $Product -> get_variation_prices();
					// WooDecimalProduct_Debugger ($Product_Variation_Prices, __FUNCTION__ .' $Product_Variation_Prices ' .__LINE__, 'add_to_cart_message', true);
											
					// $Item_Price = $Product_Variation_Prices['price'][$Variation_ID]; // price						
					// $Item_RegularPrice = $Product_Variation_Prices['regular_price'][$Variation_ID]; // regular_price
					// $Item_SalePrice = $Product_Variation_Prices['sale_price'][$Variation_ID]; // sale_price
					
					$Product = wc_get_product( $Variation_ID );
					
				} else {
					//Простой Товар.
					
					$Product = wc_get_product( $Product_ID );
				}	
				
				// WooDecimalProduct_Debugger ($Product, __FUNCTION__ .' $Product ' .__LINE__, 'add_to_cart_message', true);
				if ($Product) {	
					$Item_Price 		= $Product -> get_price();
					$Item_RegularPrice 	= $Product -> get_regular_price();
					$Item_SalePrice 	= $Product -> get_sale_price();
					
					$Item_Price 		= floatval( $Item_Price );
					$Item_RegularPrice 	= floatval( $Item_RegularPrice );
					$Item_SalePrice 	= floatval( $Item_SalePrice );
					
					WooDecimalProduct_Debugger ($Item_Price, __FUNCTION__ .' $Item_Price ' .__LINE__, 'add_to_cart_message', true);
					WooDecimalProduct_Debugger ($Item_RegularPrice, __FUNCTION__ .' $Item_RegularPrice ' .__LINE__, 'add_to_cart_message', true);
					WooDecimalProduct_Debugger ($Item_SalePrice, __FUNCTION__ .' $Item_SalePrice ' .__LINE__, 'add_to_cart_message', true);
				
					$Price_Excl_Tax = wc_get_price_excluding_tax( $Product ); 	// price without VAT
					$Price_Incl_Tax = wc_get_price_including_tax( $Product );  	// price with VAT
					$Tax_Amount     = $Price_Incl_Tax - $Price_Excl_Tax; 		// VAT amount

					WooDecimalProduct_Debugger ($Price_Excl_Tax, __FUNCTION__ .' $Price_Excl_Tax ' .__LINE__, 'add_to_cart_message', true);
					WooDecimalProduct_Debugger ($Price_Incl_Tax, __FUNCTION__ .' $Price_Incl_Tax ' .__LINE__, 'add_to_cart_message', true);
					WooDecimalProduct_Debugger ($Tax_Amount, __FUNCTION__ .' $Tax_Amount ' .__LINE__, 'add_to_cart_message', true);

					$Item = array(
						'key' => $Cart_Item_Key,
						'product_id' => $Product_ID,
						'variation_id' => $Variation_ID,
						'quantity' => $qty,
						'price' => $Item_Price,
						'regular_price' => $Item_RegularPrice,
						'sale_price' => $Item_SalePrice,
						'price_tax_excl' => $Price_Excl_Tax,
						'price_tax_incl' => $Price_Incl_Tax,
						'tax_amount' => $Tax_Amount,
					);				
					WooDecimalProduct_Debugger ($Item, __FUNCTION__ .' $Item ' .__LINE__, 'add_to_cart_message', true);

					$Add_to_Cart[] = $Item;
				}					
			}
			
			$titles[] = ($qty > 0 ? $qty . ' &times; ' : '') . sprintf (_x('&ldquo;%s&rdquo;', 'Item name in quotes', 'woocommerce'), wp_strip_all_tags( (get_the_title ($Product_ID)) )); // phpcs:ignore	
			$count   += $qty;
		}

		WooDecimalProduct_Debugger ($Add_to_Cart, __FUNCTION__ .' $Add_to_Cart ' .__LINE__, 'add_to_cart_message', true);		

		$WDPQ_Cart = WooDecimalProduct_Update_WDPQ_CartSession ($Add_to_Cart, $isDraft = false);
		WooDecimalProduct_Debugger ($WDPQ_Cart, __FUNCTION__ .' $WDPQ_Cart ' .__LINE__, 'add_to_cart_message', true);		
		
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
		
		WooDecimalProduct_Debugger ($message, __FUNCTION__ .' $message ' .__LINE__, 'add_to_cart_message', true);
		return $message;
	}

    /* Добавление Товара не со Страницы Товара, а из Каталога (без выбора Количества), с учетом возможного минимального Значения Количества и Количества по-Умолчанию.
	 * \woocommerce\includes\wc-template-functions.php
	 * \woocommerce\templates\loop\add-to-cart.php
    ----------------------------------------------------------------- */	
	add_filter ('woocommerce_loop_add_to_cart_args', 'WooDecimalProduct_Filter_loop_add_to_cart_args', 10, 2);
	function WooDecimalProduct_Filter_loop_add_to_cart_args ($args, $product) {
		WooDecimalProduct_Debugger ($args, __FUNCTION__ .' $args ' .__LINE__, 'add_to_cart_args', true);
		// WooDecimalProduct_Debugger ($product, __FUNCTION__ .' $product ' .__LINE__, 'add_to_cart_args', true);
		
		$Product_ID = $product->get_id();	
		
		if ($Product_ID) {
			$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID);
			WooDecimalProduct_Debugger ($WooDecimalProduct_QuantityData, __FUNCTION__ .' $WooDecimalProduct_QuantityData ' .__LINE__, 'add_to_cart_args', true);
			
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
		WooDecimalProduct_Clear_WooCart_if_WDPQCart_Emty ();
		
		global $WooDecimalProduct_ConsoleLog_Debuging;
		global $WooDecimalProduct_Auto_Correction_Quantity;	
		global $WooDecimalProduct_ButtonsPM_Product_Enable;
		
		if ($WooDecimalProduct_Auto_Correction_Quantity) {	
			global $product;
			
			$Product_ID = $product -> get_id();	
			WooDecimalProduct_Debugger ($Product_ID, __FUNCTION__ .' $Product_ID ' .__LINE__, 'product_page', true);
			
			if ($Product_ID) {
				$No_MaxEmpty = '-1';	// Unlimited
				$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID, $No_MaxEmpty);
				WooDecimalProduct_Debugger ($WooDecimalProduct_QuantityData, __FUNCTION__ .' $WooDecimalProduct_QuantityData ' .__LINE__, 'product_page', true);
				
				$Min_Qnt 		= $WooDecimalProduct_QuantityData['min_qnt'];
				$Max_Qnt 		= $WooDecimalProduct_QuantityData['max_qnt'];
				$Def_Qnt 		= $WooDecimalProduct_QuantityData['def_qnt'];
				$Stp_Qnt 		= $WooDecimalProduct_QuantityData['stp_qnt'];				
				$QNT_Precision 	= $WooDecimalProduct_QuantityData['precision'];
				
				global $WooDecimalProduct_Plugin_URL;
				global $WooDecimalProduct_Plugin_Version;

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
				WooDecimalProduct_Debugger ($Params, __FUNCTION__ .' $Params ' .__LINE__, 'cart', true);
				
				wp_localize_script('wdpq_page_product', 'wdpq_script_params', $Params);
			}			
		}
	}
	
	/* Корзина. 
	 * Авто-Коррекция неправильно введенного Значения Количества.
	 * AJAX Обновление Корзины при изменении Количества Товара.
	----------------------------------------------------------------- */	
	add_action ('woocommerce_before_cart', 'WooDecimalProduct_Action_before_cart', 10);
	function WooDecimalProduct_Action_before_cart () {
		global $WooDecimalProduct_ConsoleLog_Debuging;
		global $WooDecimalProduct_Auto_Correction_Quantity;
		global $WooDecimalProduct_AJAX_Cart_Update;
		global $WooDecimalProduct_ButtonsPM_Cart_Enable;
		
		WooDecimalProduct_Debugger ($WooDecimalProduct_Auto_Correction_Quantity, __FUNCTION__ .' $WooDecimalProduct_Auto_Correction_Quantity ' .__LINE__, 'cart', true);
		WooDecimalProduct_Debugger ($WooDecimalProduct_AJAX_Cart_Update, __FUNCTION__ .' $WooDecimalProduct_AJAX_Cart_Update ' .__LINE__, 'cart', true);
		
		if ($WooDecimalProduct_Auto_Correction_Quantity || $WooDecimalProduct_AJAX_Cart_Update || $WooDecimalProduct_ButtonsPM_Cart_Enable) {	
			$WooDecimalProduct_Cart = array();
			
			$No_MaxEmpty = '-1';	// Unlimited
			
			foreach( WC() -> cart -> get_cart() as $cart_item ){						
				
				$product_id 		= $cart_item['data']->get_id();
				$item_product_id 	= $cart_item['data']->get_parent_id();
				WooDecimalProduct_Debugger ($product_id, __FUNCTION__ .' $product_id ' .__LINE__, 'cart', true);
				WooDecimalProduct_Debugger ($item_product_id, __FUNCTION__ .' $item_product_id ' .__LINE__, 'cart', true);

				if ($item_product_id > 0) {
					// Вариативный Товар.
					$product_id 		= $item_product_id;
					$item_product_id 	= $cart_item['data']->get_id();
					
				} else {
					// Простой Товар.
					$item_product_id = $product_id;
				}
				WooDecimalProduct_Debugger ($item_product_id, __FUNCTION__ .' $item_product_id ' .__LINE__, 'cart', true);				

				$cart_item_key 	= $cart_item['key'];
				WooDecimalProduct_Debugger ($cart_item_key, __FUNCTION__ .' $cart_item_key ' .__LINE__, 'cart', true);

				$WooDecimalProduct_Cart[$item_product_id] = $cart_item_key;
				WooDecimalProduct_Debugger ($WooDecimalProduct_Cart, __FUNCTION__ .' $WooDecimalProduct_Cart ' .__LINE__, 'cart', true);
				
				$WooDecimalProduct_QuantityData[$item_product_id] = WooDecimalProduct_Get_QuantityData_by_ProductID ($product_id, $No_MaxEmpty);
				WooDecimalProduct_Debugger ($WooDecimalProduct_QuantityData, __FUNCTION__ .' $WooDecimalProduct_QuantityData ' .__LINE__, 'cart', true);				
			}
			
			global $WooDecimalProduct_Plugin_URL;

			global $WooDecimalProduct_Plugin_Version;			
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
			);
			WooDecimalProduct_Debugger ($Params, __FUNCTION__ .' $Params ' .__LINE__, 'cart', true);
			
			wp_localize_script('wdpq_page_cart', 'wdpq_script_params', $Params);			
		}	
	}
	
	/* Страница Товара. 
	 * "Price Unit-Label"
	----------------------------------------------------------------- */	
	add_action ('woocommerce_before_add_to_cart_button', 'WooDecimalProduct_Action_before_add_to_cart_button');
	function WooDecimalProduct_Action_before_add_to_cart_button () {	
		$WooDecimalProduct_Price_Unit_Label	= get_option ('woodecimalproduct_price_unit_label', 0);	
		WooDecimalProduct_Debugger ($WooDecimalProduct_Price_Unit_Label, __FUNCTION__ .' $WooDecimalProduct_Price_Unit_Label ' .__LINE__, 'product_page', true);
		
		if ($WooDecimalProduct_Price_Unit_Label) {
			global $product;

			if ($product) {
				$Product_ID = $product -> get_id();
				WooDecimalProduct_Debugger ($Product_ID, __FUNCTION__ .' $Product_ID ' .__LINE__, 'product_page', true);
				
				$Price_Unit_Label = WooDecimalProduct_Get_PriceUnitLabel_by_ProductID ($Product_ID);
				WooDecimalProduct_Debugger ($Price_Unit_Label, __FUNCTION__ .' $Price_Unit_Label ' .__LINE__, 'product_page', true);
				
				echo $Price_Unit_Label; // phpcs:ignore 					
			}				
		}		
	}
	
	/* Страница Каталог Товаров. / "Похожие Товары"
	 * "Price Unit-Label"
	----------------------------------------------------------------- */	
	add_filter ('woocommerce_loop_add_to_cart_link', 'WooDecimalProduct_Filter_loop_add_to_cart_link', 10, 2);
	function WooDecimalProduct_Filter_loop_add_to_cart_link ($add_to_cart_html, $product) {
		WooDecimalProduct_Debugger ($add_to_cart_html, __FUNCTION__ .' $add_to_cart_html ' .__LINE__, 'catalog_page', true);
		// WooDecimalProduct_Debugger ($product, __FUNCTION__ .' $product ' .__LINE__, 'catalog_page', true);
		
		$WooDecimalProduct_Price_Unit_Label	= get_option ('woodecimalproduct_price_unit_label', 0);
		WooDecimalProduct_Debugger ($WooDecimalProduct_Price_Unit_Label, __FUNCTION__ .' $WooDecimalProduct_Price_Unit_Label ' .__LINE__, 'catalog_page', true);
		
		if ($WooDecimalProduct_Price_Unit_Label) {
			$Product_ID = $product -> get_id();
			WooDecimalProduct_Debugger ($Product_ID, __FUNCTION__ .' $Product_ID ' .__LINE__, 'catalog_page', true);
			
			if ($Product_ID) {
				$Price_Unit_Label = WooDecimalProduct_Get_PriceUnitLabel_by_ProductID ($Product_ID);
				WooDecimalProduct_Debugger ($Price_Unit_Label, __FUNCTION__ .' $Price_Unit_Label ' .__LINE__, 'catalog_page', true);
				
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
		// WooDecimalProduct_Debugger ($Cart_Content, __FUNCTION__ .' $Cart_Content ' .__LINE__, 'cart_contents_changed', true);
		
		$Cart_Items = isset( $_REQUEST['cart'] ) ? $_REQUEST['cart'] : null; // phpcs:ignore	
		WooDecimalProduct_Debugger ($Cart_Items, __FUNCTION__ .' $Cart_Items ' .__LINE__, 'cart_contents_changed', true);
		
		if ($Cart_Items) {
			$WDPQ_Cart = array ();
			
			foreach ($Cart_Items as $Item_Key => $Item_Value) {
				$Item_Quantity = $Item_Value['qty'];
				
				$WDPQ_Cart_Item = WooDecimalProduct_Get_WDPQ_Cart_Item_by_CartProductKey ($Item_Key);
				WooDecimalProduct_Debugger ($WDPQ_Cart_Item, __FUNCTION__ .' $WDPQ_Cart_Item ' .__LINE__, 'cart_contents_changed', true);
				
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
			
			WooDecimalProduct_Debugger ($WDPQ_Cart, __FUNCTION__ .' $WDPQ_Cart ' .__LINE__, 'cart_contents_changed', true);
			WooDecimalProduct_Set_WDPQ_CartSession ($WDPQ_Cart, $isDraft = false);	
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
		WooDecimalProduct_Debugger ($cart_item_key, __FUNCTION__ .' $cart_item_key ' .__LINE__, 'remove_cart_item', true);
		
		$Cart_Contents = $instance -> cart_contents;
		
		$Product_ID = $Cart_Contents[$cart_item_key]['product_id'];
		WooDecimalProduct_Debugger ($Product_ID, __FUNCTION__ .' $Product_ID ' .__LINE__, 'remove_cart_item', true);
		
		if ($Product_ID) {
			$Cart_Data = WooDecimalProduct_Get_WDPQ_CartSession ($isDraft = false);
			WooDecimalProduct_Debugger ($Cart_Data, __FUNCTION__ .' $Cart_Data ' .__LINE__, 'remove_cart_item', true);
		
			if ($Cart_Data) {
				$NewCart_Data = array();
				
				foreach ($Cart_Data as $Cart_Product_Item) {
					$Cart_Product_Key 	= $Cart_Product_Item['key'];
					$Cart_Product_ID 	= $Cart_Product_Item['product_id'];
					$Cart_Variation_ID 	= $Cart_Product_Item['variation_id'];
					$Cart_Quantity 		= $Cart_Product_Item['quantity'];
					$Cart_Price 		= $Cart_Product_Item['price'];
					
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
					WooDecimalProduct_Set_WDPQ_CartSession ($NewCart_Data, $isDraft = false);
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
		WooDecimalProduct_Debugger ($should_clear_cart_after_payment, __FUNCTION__ .' $should_clear_cart_after_payment ' .__LINE__, 'clear_cart_after_payment', true);
		
		if ($should_clear_cart_after_payment) {
			// Clear.
			WooDecimalProduct_Delete_WDPQ_CartSession ($isDraft = true);
			WooDecimalProduct_Delete_WDPQ_CartSession ($isDraft = false);
		}
		
		return $should_clear_cart_after_payment;
	}	
	
	/* Cart. Total.
	 * woocommerce\templates\cart\cart-totals.php
	 * woocommerce\includes\class-wc-cart.php
	 * get_total
	 * Срабатывает раньше, чем: Cart. Item Subtotal.
	----------------------------------------------------------------- */
	add_filter ('woocommerce_cart_get_total', 'WooDecimalProduct_Filter_cart_get_total', 9999);
	function WooDecimalProduct_Filter_cart_get_total ($Cart_Total) {
		WooDecimalProduct_Debugger ($Cart_Total, __FUNCTION__ .' $Cart_Total ' .__LINE__, 'cart_get_total', true);
		
		return $Cart_Total;		
		
		// Видимо, были некоторые заморогчки в предыдущих версиях. С Купонами - Определенно.
	}

	/* Cart. Tax. Amount.
	 * woocommerce\templates\cart\cart-totals.php
	 * woocommerce\includes\wc-cart-functions.php
	 * wc_cart_totals_taxes_total_html( $tax )
	----------------------------------------------------------------- */
	// Не отображаем. Потому, что тогда нужно делать так же и для Subtotal. А в нем нет Фильтра.
	// add_filter ('woocommerce_cart_totals_taxes_total_html', 'WooDecimalProduct_Filter_cart_totals_taxes_total_html', 9999, 1);
	// function WooDecimalProduct_Filter_cart_totals_taxes_total_html ($Tax_html) {	
		// WooDecimalProduct_Debugger ($Tax_html, __FUNCTION__ .' $Tax_html ' .__LINE__, 'taxes_total', true);		
		
		// $Tax_About_Title = __('Real value may be Rounded', 'decimal-product-quantity-for-woocommerce');
		// $Tax_About_Text  = __('(may be Rounded)', 'decimal-product-quantity-for-woocommerce');
		
		// $Tax_html .= '<span class="wdpq_tax_about" title="' .$Tax_About_Title .'">' .$Tax_About_Text .'</span>';
		
		// return $Tax_html;
	// }	
	
	/* Cart. Total. Amount.
	 * woocommerce\templates\cart\cart-totals.php
	 * woocommerce\includes\wc-cart-functions.php
	 * wc_cart_totals_order_total_html( $tax )
	----------------------------------------------------------------- */		
	add_filter ('woocommerce_cart_totals_order_total_html', 'WooDecimalProduct_Filter_cart_totals_order_total_html', 9999, 1);
	function WooDecimalProduct_Filter_cart_totals_order_total_html ($Total_html) {	
		// WooDecimalProduct_Debugger ($Total_html, __FUNCTION__ .' $Total_html ' .__LINE__, 'cart_total', true);		
		
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
		WooDecimalProduct_Debugger ($Product_Subtotal, __FUNCTION__ .' $Product_Subtotal ' .__LINE__, 'item_subtotal', true);
		// WooDecimalProduct_Debugger ($Product, __FUNCTION__ .' $Product ' .__LINE__, 'item_subtotal', true);
		// WooDecimalProduct_Debugger ($Quantity, __FUNCTION__ .' $Quantity ' .__LINE__, 'item_subtotal', true);
		// WooDecimalProduct_Debugger ($Cart_Items, __FUNCTION__ .' $Cart_Items ' .__LINE__, 'item_subtotal', true);

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
		WooDecimalProduct_Debugger ($Cart_Subtotal, __FUNCTION__ .' $Cart_Subtotal ' .__LINE__, 'cart_subtotal', true);
		
		$isWDPQ_Cart_Empty = WooDecimalProduct_is_WDPQCart_Empty ();
		WooDecimalProduct_Debugger ($isWDPQ_Cart_Empty, __FUNCTION__ .' $isWDPQ_Cart_Empty ' .__LINE__, 'cart_subtotal', true);

		if ($isWDPQ_Cart_Empty) {	
			return 0;
		}		
		
// $WDPQ_Cart_Subtotal = WooDecimalProduct_Get_WDPQ_Cart_Total($Cart_Subtotal);
// WooDecimalProduct_Debugger ($WDPQ_Cart_Subtotal, __FUNCTION__ .' $WDPQ_Cart_Subtotal ' .__LINE__, 'cart_subtotal', true);

// return $WDPQ_Cart_Subtotal;

		return $Cart_Subtotal;
	}
	
	/* Cart. Ситуация, когда Корзина была сформирована, но Браузер закрыли и открыли снова.
	 * отсутствует WDPQ_Cart. Оформление невозможно.
	 * woocommerce\templates\cart\cart.php
	----------------------------------------------------------------- */	
	add_action ('woocommerce_after_cart', 'WooDecimalProduct_Action_after_cart', 9999);
	function WooDecimalProduct_Action_after_cart () {
		$isWDPQ_Cart_Empty = WooDecimalProduct_is_WDPQCart_Empty ();
		WooDecimalProduct_Debugger ($isWDPQ_Cart_Empty, __FUNCTION__ .' $isWDPQ_Cart_Empty ' .__LINE__, 'after_cart', true);
		
		if ($isWDPQ_Cart_Empty) {			
			$Draft_Cart = WooDecimalProduct_Get_WDPQ_CartSession ($isDraft = true);	
			WooDecimalProduct_Debugger ($Draft_Cart, __FUNCTION__ .' $Draft_Cart ' .__LINE__, 'after_cart', true);
					
			if ($Draft_Cart) {
				// Create WDPQ-Cart from Draft_Cart
				// Go to: WooDecimalProduct_Action_cart_is_empty ()
				
			} else {
				// Disable Order Processing & Create Draft-Cart
				$Add_to_Cart = array ();
				
				$WooCart = WC() -> cart;
				// WooDecimalProduct_Debugger ($WooCart, __FUNCTION__ .' $WooCart ' .__LINE__, 'after_cart', true);

				if ($WooCart) {
					$Cart_Contents = $WooCart -> cart_contents;	
					// WooDecimalProduct_Debugger ($Cart_Contents, __FUNCTION__ .' $Cart_Contents ' .__LINE__, 'after_cart', true);
					
					if ($Cart_Contents) {
						foreach ($Cart_Contents as $key => $Cart_Item) {
							WooDecimalProduct_Debugger ($Cart_Item, __FUNCTION__ .' $Cart_Item ' .__LINE__, 'after_cart', true);
							
							$Item_Key 			= $Cart_Item['key'];
							$Item_ProductID 	= $Cart_Item['product_id'];
							$Item_VariationID 	= $Cart_Item['variation_id'];
							
							$Cart_Item_Product 	= $Cart_Item['data'];
							// WooDecimalProduct_Debugger ($Cart_Item_Product, __FUNCTION__ .' $Cart_Item_Product ' .__LINE__, 'after_cart', true);
							
							$Item_Price 		= $Cart_Item_Product -> get_price();							
							$Item_RegularPrice 	= $Cart_Item_Product -> get_regular_price();
							$Item_SalePrice 	= $Cart_Item_Product -> get_sale_price();

							$Item_Price 		= floatval( $Item_Price );
							$Item_RegularPrice 	= floatval( $Item_RegularPrice );
							$Item_SalePrice 	= floatval( $Item_SalePrice );

							WooDecimalProduct_Debugger ($Item_Price, __FUNCTION__ .' $Item_Price ' .__LINE__, 'add_to_cart_message', true);
							WooDecimalProduct_Debugger ($Item_RegularPrice, __FUNCTION__ .' $Item_RegularPrice ' .__LINE__, 'add_to_cart_message', true);
							WooDecimalProduct_Debugger ($Item_SalePrice, __FUNCTION__ .' $Item_SalePrice ' .__LINE__, 'add_to_cart_message', true);

							$Price_Excl_Tax = wc_get_price_excluding_tax( $Cart_Item_Product ); 	// price without VAT
							$Price_Incl_Tax = wc_get_price_including_tax( $Cart_Item_Product );  	// price with VAT
							$Tax_Amount     = $Price_Incl_Tax - $Price_Excl_Tax; 		// VAT amount

							WooDecimalProduct_Debugger ($Price_Excl_Tax, __FUNCTION__ .' $Price_Excl_Tax ' .__LINE__, 'add_to_cart_message', true);
							WooDecimalProduct_Debugger ($Price_Incl_Tax, __FUNCTION__ .' $Price_Incl_Tax ' .__LINE__, 'add_to_cart_message', true);
							WooDecimalProduct_Debugger ($Tax_Amount, __FUNCTION__ .' $Tax_Amount ' .__LINE__, 'add_to_cart_message', true);							
							
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
							WooDecimalProduct_Debugger ($Add_to_DraftCart, __FUNCTION__ .' $Add_to_DraftCart ' .__LINE__, 'after_cart', true);
						}
						
						$Draft_Cart = WooDecimalProduct_Update_WDPQ_CartSession ($Add_to_DraftCart, $isDraft = true);
						WooDecimalProduct_Debugger ($Draft_Cart, __FUNCTION__ .' $Draft_Cart ' .__LINE__, 'after_cart', true);
						
						$WDPQ_Nonce = 'Restore_WDPQ-Cart_DecimalProductQuantityForWooCommerce';
						$nonce = wp_create_nonce ($WDPQ_Nonce);	
						
						global $WooDecimalProduct_Plugin_URL;
						global $WooDecimalProduct_Plugin_Version;

						wp_enqueue_script ('wdpq_page_cart_restore', $WooDecimalProduct_Plugin_URL .'includes/wdpq_page_cart_restore.js', array(), $WooDecimalProduct_Plugin_Version); // phpcs:ignore 

						$Params = array (
							'nonce' => esc_html( $nonce ),
							'item_subtotal_title' => esc_html( __('Will be available after creating the Cart', 'decimal-product-quantity-for-woocommerce') ),
							'button_class' => esc_html( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ),
							'button_value' => esc_html( 'create_cart', 'decimal-product-quantity-for-woocommerce'),
							'button_label' => esc_html( __('Create Cart', 'decimal-product-quantity-for-woocommerce') ),
						);
						WooDecimalProduct_Debugger ($Params, __FUNCTION__ .' $Params ' .__LINE__, 'cart', true);
						
						wp_localize_script('wdpq_page_cart_restore', 'wdpq_script_cart_restore_params', $Params);			
						
						echo '<div class="wdpq_about_create_cart">' .esc_html( __('These Products were in your previous Cart. You can "Create Cart" based on them.', 'decimal-product-quantity-for-woocommerce' ) ).'</div>';
						
						wc_empty_cart();
					}
				}				
			}			
		}	
	}

	/* Cart. Переход на Страницу Cart "cart.php", вместо Страницы: "cart-empty.php" если WDPQ-Cart не пустая.
	 * woocommerce\templates\cart\cart-empty.php
	----------------------------------------------------------------- */	
	add_action ('woocommerce_cart_is_empty', 'WooDecimalProduct_Action_cart_is_empty', 9999);
	function WooDecimalProduct_Action_cart_is_empty () {
		WooDecimalProduct_Debugger ($_REQUEST, __FUNCTION__ .' $_REQUEST ' .__LINE__, 'cart-empty', true); // phpcs:ignore

		$Action = isset($_REQUEST['wdpq_create_cart']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_create_cart'])) : null; // phpcs:ignore
		$Nonce 	= isset($_REQUEST['wdpq_wpnonce']) ? sanitize_text_field (wp_unslash($_REQUEST['wdpq_wpnonce'])) : 'none'; // phpcs:ignore	

		if ($Action == 'create_cart') {
			$WDPQ_Nonce = 'Restore_WDPQ-Cart_DecimalProductQuantityForWooCommerce';

			if (!wp_verify_nonce( $Nonce, $WDPQ_Nonce )) {
				exit;
			}
			
			$Cart_Items = isset( $_REQUEST['cart'] ) ? $_REQUEST['cart'] : null; // phpcs:ignore	
			WooDecimalProduct_Debugger ($Cart_Items, __FUNCTION__ .' $Cart_Items ' .__LINE__, 'cart-empty', true);
				
			$isWDPQ_Cart_Empty = WooDecimalProduct_is_WDPQCart_Empty ();
			WooDecimalProduct_Debugger ($isWDPQ_Cart_Empty, __FUNCTION__ .' $isWDPQ_Cart_Empty ' .__LINE__, 'cart-empty', true);
			
			if ($isWDPQ_Cart_Empty) {
				$Draft_Cart = WooDecimalProduct_Get_WDPQ_CartSession ($isDraft = true);	
				WooDecimalProduct_Debugger ($Draft_Cart, __FUNCTION__ .' $Draft_Cart ' .__LINE__, 'cart-empty', true);	
				
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
						WooDecimalProduct_Debugger ($Item, __FUNCTION__ .' $Item ' .__LINE__, 'cart-empty', true);
						
						$Add_to_Cart[] = $Item;
						
						// Формируем Корзину Woo
						WC() -> cart -> add_to_cart( $Item_ProductID, $Item_Quantity, $Item_VariationID );
					}
					
					WooDecimalProduct_Debugger ($Add_to_Cart, __FUNCTION__ .' $Add_to_Cart ' .__LINE__, 'cart-empty', true);
					
					$WDPQ_Cart = WooDecimalProduct_Update_WDPQ_CartSession ($Add_to_Cart, $isDraft = false);
					WooDecimalProduct_Debugger ($WDPQ_Cart, __FUNCTION__ .' $WDPQ_Cart ' .__LINE__, 'cart-empty', true);

					WooDecimalProduct_Delete_WDPQ_CartSession ($isDraft = true);				
					
					// Возвращаемся на Страницу Корзина избегая: "Повторная отправка Формы"
					wp_safe_redirect( wc_get_cart_url() );
					exit;				
				}
			}	
		}		
	}
	
	/* Coupons. Woo Settings.
	 * woocommerce\templates\cart\cart-totals.php
	 * woocommerce\includes\wc-cart-functions.php
	 * wc_cart_totals_coupon_html( $coupon )
	----------------------------------------------------------------- */	
	add_action ('admin_notices', 'WooDecimalProduct_Action_admin_notices');
	function WooDecimalProduct_Action_admin_notices () {
		$screen = get_current_screen();
		$screen_id = $screen -> id;
		// WooDecimalProduct_Debugger ($screen_id, __FUNCTION__ .' $screen_id ' .__LINE__, 'admin_notices', true);	

		if ($screen_id == 'edit-shop_coupon') {
			$Notice = __( 'Correct processing of coupons is possible only in the Pro version "Decimal Product Quantity for WooCommerce". Sorry.', 'decimal-product-quantity-for-woocommerce' );
	
			echo '<div id="wdpq_warning" class="notice notice-warning"><p>' .esc_html( $Notice ) .'</p></div>';
		}	
	}
	
	/* Coupons. Disable.
	 * Переопределяем разрешение Использования Купонов. (Доступно только в Pro версии)
	 * woocommerce\includes\wc-coupon-functions.php
	 * wc_coupons_enabled()
	----------------------------------------------------------------- */	
	add_filter ('woocommerce_coupons_enabled', 'WooDecimalProduct_Filter_coupons_enabled', 9999, 1);
	function WooDecimalProduct_Filter_coupons_enabled ($Coupon_Enable) {
		WooDecimalProduct_Debugger ($Coupon_Enable, __FUNCTION__ .' $Coupon_Enable ' .__LINE__, 'coupons_enabled', true);
		
		$Coupon_Enable = false;
		
		return $Coupon_Enable;
	}

	/* Coupons. Cart. PRO
	 * woocommerce\templates\cart\cart-totals.php
	 * woocommerce\includes\wc-cart-functions.php
	 * wc_cart_totals_coupon_html( $coupon )
	----------------------------------------------------------------- */	
	add_filter ('woocommerce_cart_totals_coupon_html', 'WooDecimalProduct_Filter_cart_totals_coupon_html', 9999, 3);
	function WooDecimalProduct_Filter_cart_totals_coupon_html ($Coupon_html, $coupon, $Discount_Amount_html) {
		// WooDecimalProduct_Debugger ($Coupon_html, __FUNCTION__ .' $Coupon_html ' .__LINE__, 'coupons_totals', true);
		// WooDecimalProduct_Debugger ($coupon, __FUNCTION__ .' $coupon ' .__LINE__, 'coupons_totals', true);
		// WooDecimalProduct_Debugger ($Discount_Amount_html, __FUNCTION__ .' $Discount_Amount_html ' .__LINE__, 'coupons_totals', true);
		
		if ( version_compare( WC_VERSION, '9.4.3', '>' ) ) {
			// Версия Woo > 9.4.3 Требуется дополнительная обработка Купонов.
			// Реализовано в PRO версии.
			$Coupon_html = esc_html( __('Coupons Disabled, sorry.', 'decimal-product-quantity-for-woocommerce') );
		} 
		
		return $Coupon_html;
	}
	
	/* Checkout
	----------------------------------------------------------------- */
	add_action('woocommerce_checkout_order_processed', 'WooDecimalProduct_Action_checkout_order_processed', 9999, 3);
	function WooDecimalProduct_Action_checkout_order_processed ($Order_ID, $posted_data, $order) {
		if ( version_compare( WC_VERSION, '9.4.3', '>' ) ) {
			// Версия Woo > 9.4.3 Требуется дополнительная обработка Данных Заказа.
			
			// Позиции Ордера теперь хранятся в Таблице: woocommerce_order_itemmeta 
			// woocommerce\includes\class-wc-post-data.php
			// update_order_item_metadata
			// 	update_metadata( 'order_item', $object_id, $meta_key, $meta_value, $prev_value );		
			
			WooDecimalProduct_Debugger ($Order_ID, __FUNCTION__ .' $Order_ID ' .__LINE__, 'checkout', true);
			// WooDecimalProduct_Debugger ($posted_data, __FUNCTION__ .' $posted_data ' .__LINE__, 'checkout', true);
			// WooDecimalProduct_Debugger ($order, __FUNCTION__ .' $order ' .__LINE__, 'checkout', true);

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
				WooDecimalProduct_Debugger ($OrderItems, __FUNCTION__ .' $OrderItems ' .__LINE__, 'checkout', true);
				
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
								WooDecimalProduct_Debugger ($Product_ID, __FUNCTION__ .' $Product_ID ' .__LINE__, 'checkout', true);
								
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
								WooDecimalProduct_Debugger ($Variation_ID, __FUNCTION__ .' $Variation_ID ' .__LINE__, 'checkout', true);								
								
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
									WooDecimalProduct_Debugger ($WDPQ_CartItem, __FUNCTION__ .' $WDPQ_CartItem ' .__LINE__, 'checkout', true);
									
									if ($WDPQ_CartItem) {
										$WDPQ_CartItem_Quantity = isset( $WDPQ_CartItem['quantity'] ) ? $WDPQ_CartItem['quantity'] : 1;
										$WDPQ_CartItem_Price 	= isset( $WDPQ_CartItem['price'] ) ? $WDPQ_CartItem['price'] : 0;
										
										$WDPQ_CartItem_Total = $WDPQ_CartItem_Price * $WDPQ_CartItem_Quantity;
										
										if ($WDPQ_CartItem_Total == 0) {
											$WDPQ_CartItem_Total = 1;
										}
										
										// OrderMeta. Update Quantity	
										WooDecimalProduct_Update_Order_Item_Meta ($Item_ID, '_qty', $WDPQ_CartItem_Quantity);					

										// OrderMeta. Update SubTotal
										WooDecimalProduct_Update_Order_Item_Meta ($Item_ID, '_line_subtotal', $WDPQ_CartItem_Total);	

										// OrderMeta. Update Total
										WooDecimalProduct_Update_Order_Item_Meta ($Item_ID, '_line_total', $WDPQ_CartItem_Total);
									}
								}
							}
							
							if ($Item_Type == 'coupon') {
								// Удаляем Купон. 
								// Версия Woo > 9.4.3 Требуется дополнительная обработка Купонов.
								// Реализовано в PRO версии.	
								
								WooDecimalProduct_Delete_Order_Item_Meta ($Item_ID);
							}
							
						}
					}
				}
			}
		} else {
			// Заказ формируется корректно в версиях Woo до 9.4.3
		}	
	}

	/* Order. Filter Totals.
	 * woocommerce\templates\order\order-details.php
	 * woocommerce\includes\class-wc-order.php
	 * get_order_item_totals ()
	----------------------------------------------------------------- */
	add_filter('woocommerce_get_order_item_totals', 'WooDecimalProduct_Filter_get_order_item_totals', 9999, 3);
	function WooDecimalProduct_Filter_get_order_item_totals ($total_rows, $This, $tax_display) {
		WooDecimalProduct_Debugger ($total_rows, __FUNCTION__ .' $total_rows ' .__LINE__, 'order_item_totals', true);
		// WooDecimalProduct_Debugger ($This, __FUNCTION__ .' $This ' .__LINE__, 'order_item_totals', true);
		// WooDecimalProduct_Debugger ($tax_display, __FUNCTION__ .' $tax_display ' .__LINE__, 'order_item_totals', true);
		
		if ( version_compare( WC_VERSION, '9.4.3', '>' ) ) {
			// Версия Woo > 9.4.3 Требуется дополнительная обработка Купонов.
			// Реализовано в PRO версии.
			// Удаляем Скидки.
			
			if ( isset( $total_rows['discount'] ) ) {
				unset( $total_rows['discount'] );
			}			
		}	
		
		return $total_rows;
	}

	/* Order. Complete. "Повторить Заказ" если Пользователь - Залогинен.
	 * woocommerce\includes\wc-template-functions.php
	 * woocommerce_order_again_button ()
	----------------------------------------------------------------- */	
	add_filter('woocommerce_valid_order_statuses_for_order_again', 'WooDecimalProduct_valid_order_statuses_for_order_again', 9999, 1);
	function WooDecimalProduct_valid_order_statuses_for_order_again ($completed) {
		WooDecimalProduct_Debugger ($completed, __FUNCTION__ .' $completed ' .__LINE__, 'order_again', true);
		
		// Возможно, это будет в PRO версии.
		// Но пока, просто скрываем такую Кнопку.
		
		$completed = array();
		
		return $completed;
	}