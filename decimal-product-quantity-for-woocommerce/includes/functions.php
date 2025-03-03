<?php
/*
 * Decimal Product Quantity for WooCommerce
 * functions.php
 */

	/* Check PRO Plugin Installed
	----------------------------------------------------------------- */		
	function WooDecimalProduct_Check_Plugin_Installed ($Plugin_Slug = null) {
		$Result = false;
		
		if ($Plugin_Slug) {
			if (! function_exists ('get_plugins')) {
				require_once ABSPATH .'wp-admin/includes/plugin.php';
			}
			
			$Plugins = get_plugins();
			
			foreach ($Plugins as $Plugin) {				
				$Plugin_TextDomain = $Plugin['TextDomain'];
				if ($Plugin_TextDomain == $Plugin_Slug) {
					$Result = true;
				}
			}			
		}	
		
		return $Result;
	}
	
    /* Проверка успешного Обновления Пред.Версий.
    ----------------------------------------------------------------- */ 	
	function WooDecimalProduct_Check_Updated () {
		// Проверка успешной конвертации Названий Мета-Полей для Товаров.
			// woodecimalproduct_min_qnt_default  -> woodecimalproduct_min_qnt
			// woodecimalproduct_step_qnt_product -> woodecimalproduct_step_qnt
			// woodecimalproduct_item_qnt_default -> woodecimalproduct_item_qnt
		$WooDecimalProduct_Updated_PoductMeta = get_option ('woodecimalproduct_updated_poductmeta', false); 
		
		if (!$WooDecimalProduct_Updated_PoductMeta) {
			global $wpdb;
			$PostMeta_Table = $wpdb->prefix .'postmeta';
			
			$Query = "SELECT * FROM $PostMeta_Table WHERE meta_key LIKE 'woodecimalproduct_%'";			
			$Result = $wpdb -> get_results ($wpdb->prepare($Query, true)); // phpcs:ignore 
			
			if ($Result) {
				foreach ($Result as $Meta) {
					$post_id 	= $Meta->post_id;
					$meta_key 	= $Meta->meta_key;
					$meta_value = $Meta->meta_value;
					
					if ($meta_value) {
						if ($meta_key == 'woodecimalproduct_min_qnt_default' || $meta_key == 'woodecimalproduct_step_qnt_product' || $meta_key == 'woodecimalproduct_item_qnt_default') {
							if ($meta_key == 'woodecimalproduct_min_qnt_default') {
								$meta_key_new = 'woodecimalproduct_min_qnt';
							}
							
							if ($meta_key == 'woodecimalproduct_step_qnt_product') {
								$meta_key_new = 'woodecimalproduct_step_qnt';
							}

							if ($meta_key == 'woodecimalproduct_item_qnt_default') {
								$meta_key_new = 'woodecimalproduct_item_qnt';
							}

							// Уже может быть сохранено Новое Значение обновленной пред. версии
							$Meta_Key_New_Exist = get_post_meta ($post_id, $meta_key_new, true);
							
							if (!$Meta_Key_New_Exist) {
								update_post_meta($post_id, $meta_key_new, $meta_value);	
							}
							
							delete_post_meta($post_id, $meta_key);							
						}
					} else {
						delete_post_meta($post_id, $meta_key);
					}
				}
			}
			
			update_option('woodecimalproduct_updated_poductmeta', true);
		}
	}

	/* Инициализация Сессии, если это - Необходимо
	----------------------------------------------------------------- */
	function WooDecimalProduct_StartSession ($Initiator = '') {
		$debug_process = 'f_start_session';
		
		WDPQ_Debugger ($Initiator, '$Initiator', $debug_process, __FUNCTION__, __LINE__);
		
		// PHP_SESSION_NONE = 1
		// PHP_SESSION_ACTIVE = 2
				
		$PHP_Version = phpversion(); // Alt: $PHP_Version = PHP_VERSION;
		WDPQ_Debugger ($PHP_Version, '$PHP_Version', $debug_process, __FUNCTION__, __LINE__);
		
		// Проверяем, что Headers еще не были отправлены, иначе, возникает ошибка Инициализации Сессии.
		if( headers_sent() ) {
			WDPQ_Debugger ('headers already sent', 'headers_sent()', $debug_process, __FUNCTION__, __LINE__);
			
			$Headers_List = headers_list();
			WDPQ_Debugger ($Headers_List, '$Headers_List', $debug_process, __FUNCTION__, __LINE__);
			
			return false;
			
		} else {
			if ( version_compare( $PHP_Version, '5.4', '<=' ) ) {
				$Session_ID = session_id();
				WDPQ_Debugger ($Session_ID, '$Session_ID', $debug_process, __FUNCTION__, __LINE__);
				
				if (!session_id()) {
					$Result = session_start();
					WDPQ_Debugger ($Result, '$Result', $debug_process, __FUNCTION__, __LINE__);
				}
			}
			
			if ( version_compare( $PHP_Version, '7', '>=' ) ) {
				$Session_Status = session_status();
				WDPQ_Debugger ($Session_Status, '$Session_Status', $debug_process, __FUNCTION__, __LINE__);

				// if ($Session_Status === PHP_SESSION_NONE && $Session_Status !== PHP_SESSION_ACTIVE) {
				if ($Session_Status !== PHP_SESSION_ACTIVE) {
					$Result = session_start();
					WDPQ_Debugger ($Result, '$Result', $debug_process, __FUNCTION__, __LINE__);
				}
			}
		}
				
		return true;
	}
	
	/* Минимальное / Максимально кол-во выбора Товара, Шаг, Значение по-Умолчанию, Максимально-Необходимая Точность
	----------------------------------------------------------------- */	
	function WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID, $No_MaxEmpty = '') {
		$WooDecimalProduct_Min_Quantity_Default    	= get_option ('woodecimalproduct_min_qnt_default', 1);  
		$WooDecimalProduct_Step_Quantity_Default   	= get_option ('woodecimalproduct_step_qnt_default', 1); 
		$WooDecimalProduct_Item_Quantity_Default   	= get_option ('woodecimalproduct_item_qnt_default', 1);
		$WooDecimalProduct_Max_Quantity_Default    	= get_option ('woodecimalproduct_max_qnt_default', '');  
		$WooDecimalProduct_Auto_Correction_Quantity	= get_option ('woodecimalproduct_auto_correction_qnt', 1);			
		
		$WooDecimalProduct_QuantityData['product_id'] = 0;
		$WooDecimalProduct_QuantityData['min_qnt'] = 1;
		$WooDecimalProduct_QuantityData['max_qnt'] = '';
		$WooDecimalProduct_QuantityData['def_qnt'] = 1;
		$WooDecimalProduct_QuantityData['stp_qnt'] = 1;
		
		if ($Product_ID) {
			$WooDecimalProduct_QuantityData['product_id'] = $Product_ID;
			
			$Term_QuantityData = WooDecimalProduct_Get_Term_QuantityData_by_ProductID ($Product_ID);
			
			$Min_Qnt = get_post_meta ($Product_ID, 'woodecimalproduct_min_qnt', true);	// Минимальное Количество для данного Товара	
			$Max_Qnt = get_post_meta ($Product_ID, 'woodecimalproduct_max_qnt', true);  // Максимальное Количество для данного Товара	
			$Def_Qnt = get_post_meta ($Product_ID, 'woodecimalproduct_item_qnt', true);	// Default_Qnt для данного Товара
			$Stp_Qnt = get_post_meta ($Product_ID, 'woodecimalproduct_step_qnt', true);	// Шаг изменения для данного Товара		
			
			// PlaceHolders
			$PlaceHolder_Min_Qnt = isset($Term_QuantityData['min_qnt']) ? $Term_QuantityData['min_qnt'] : $WooDecimalProduct_Min_Quantity_Default;
			$PlaceHolder_Max_Qnt = isset($Term_QuantityData['max_qnt']) ? $Term_QuantityData['max_qnt'] : $WooDecimalProduct_Max_Quantity_Default;	
			$PlaceHolder_Def_Qnt = isset($Term_QuantityData['def_qnt']) ? $Term_QuantityData['def_qnt'] : $WooDecimalProduct_Item_Quantity_Default;	
			$PlaceHolder_Stp_Qnt = isset($Term_QuantityData['stp_qnt']) ? $Term_QuantityData['stp_qnt'] : $WooDecimalProduct_Step_Quantity_Default;

			if (!$Min_Qnt) {
				$Min_Qnt = $PlaceHolder_Min_Qnt;
			}
			
			if (!$Max_Qnt) {
				$Max_Qnt = $PlaceHolder_Max_Qnt;
			}
			if ($Max_Qnt == '') {
				$Max_Qnt = $No_MaxEmpty; // '-1' for Unlimited
			}			

			if (!$Def_Qnt) {
				$Def_Qnt = $PlaceHolder_Def_Qnt;
			}
			
			if (!$Stp_Qnt) {
				$Stp_Qnt = $PlaceHolder_Stp_Qnt;
			}		
			
			if ($Min_Qnt && $Def_Qnt) {
				if ($Def_Qnt < $Min_Qnt) {
					$Def_Qnt = $Min_Qnt;
				}
			}			
			
			$WooDecimalProduct_QuantityData['min_qnt'] = $Min_Qnt;
			$WooDecimalProduct_QuantityData['max_qnt'] = $Max_Qnt;
			$WooDecimalProduct_QuantityData['def_qnt'] = $Def_Qnt;
			$WooDecimalProduct_QuantityData['stp_qnt'] = $Stp_Qnt;
			$WooDecimalProduct_QuantityData['placeholder_min_qnt'] = $PlaceHolder_Min_Qnt;
			$WooDecimalProduct_QuantityData['placeholder_max_qnt'] = $PlaceHolder_Max_Qnt;
			$WooDecimalProduct_QuantityData['placeholder_def_qnt'] = $PlaceHolder_Def_Qnt;
			$WooDecimalProduct_QuantityData['placeholder_stp_qnt'] = $PlaceHolder_Stp_Qnt;
			
			$Locale_Delimiter = WooDecimalProduct_Get_Locale_Delimiter ();

			$Min_QNT_Precision = strlen (substr (strrchr ($Min_Qnt, $Locale_Delimiter), 1));	
			$Def_QNT_Precision = strlen (substr (strrchr ($Def_Qnt, $Locale_Delimiter), 1));
			$Stp_QNT_Precision = strlen (substr (strrchr ($Stp_Qnt, $Locale_Delimiter), 1));
					
			$QNT_Precision = max (array ($Min_QNT_Precision, $Def_QNT_Precision, $Stp_QNT_Precision));

			$WooDecimalProduct_QuantityData['precision'] = $QNT_Precision;				
			
			return $WooDecimalProduct_QuantityData;
		}
		
		return $WooDecimalProduct_QuantityData;
	}	
	
	/* Нормализуем дробное число с учетом настроек разделителя
	----------------------------------------------------------------- */	
	function WooDecimalProduct_Normalize_Number ($Number) {
		$Locale_Info = localeconv();
		$Locale_Delimiter = $Locale_Info['decimal_point'];
		
		$Number = str_replace ('.', $Locale_Delimiter, $Number);
		$Number = str_replace (',', $Locale_Delimiter, $Number);
		
		return $Number;
	}	
	
	/* Получаем Price_Unit_Label Товара.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Get_PriceUnitLabel_by_ProductID ($Product_ID) {
		$Price_Unit_Label = '<div class="woodecimalproduct_price_unit_label" style="min-height: 12px;"></div>';
		
		$Product_Price_Unit_Disable = get_post_meta ($Product_ID, 'woodecimalproduct_price_unit_disable', true);
		
		if (! $Product_Price_Unit_Disable) {					
			// Берем Значение из Товара
			$Product_Price_Unit_Label = get_post_meta ($Product_ID, 'woodecimalproduct_price_unit_label', true);				
			
			if ($Product_Price_Unit_Label) {
				$Price_Unit_Label = '<div class="woodecimalproduct_price_unit_label" style="min-height: 12px;">' .$Product_Price_Unit_Label .'</div>';
			} else {
				// Берем Значение из Категории Товара				
				$Term_QuantityData = WooDecimalProduct_Get_Term_QuantityData_by_ProductID ($Product_ID);

				if ($Term_QuantityData) {
					$Price_Unit_Label = $Term_QuantityData['price_unit'];
					
					if ($Price_Unit_Label) {
						$Price_Unit_Label = '<div class="woodecimalproduct_price_unit_label" style="min-height: 12px;">' .$Price_Unit_Label .'</div>';
					}	
				}		
			}			
		}
		
		return $Price_Unit_Label;
	}

	/* Получаем QuantityData Категории Товаров по Term_ID.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Get_Term_QuantityData_by_TermID ($Term_ID, $No_MaxEmpty = '') {
		$WooDecimalProduct_Min_Quantity_Default 	= get_option ('woodecimalproduct_min_qnt_default', 1);  
		$WooDecimalProduct_Max_Quantity_Default 	= get_option ('woodecimalproduct_max_qnt_default', '');  
		$WooDecimalProduct_Step_Quantity_Default   	= get_option ('woodecimalproduct_step_qnt_default', 1); 
		$WooDecimalProduct_Item_Quantity_Default   	= get_option ('woodecimalproduct_item_qnt_default', 1);	
		
		$Term_Min_Qnt 		= get_term_meta ($Term_ID, 'woodecimalproduct_term_min_qnt', $single = true);			
		$Term_Max_Qnt 		= get_term_meta ($Term_ID, 'woodecimalproduct_term_max_qnt', $single = true);
		$Term_Step_Qnt 		= get_term_meta ($Term_ID, 'woodecimalproduct_term_step_qnt', $single = true);
		$Term_Set_Qnt 		= get_term_meta ($Term_ID, 'woodecimalproduct_term_item_qnt', $single = true);
		$Term_Price_Unit 	= get_term_meta ($Term_ID, 'woodecimalproduct_term_price_unit', $single = true);
		
		if (! $Term_Min_Qnt) {
			$Term_Min_Qnt = $WooDecimalProduct_Min_Quantity_Default;
		}
		
		if (! $Term_Max_Qnt) {
			$Term_Max_Qnt = $WooDecimalProduct_Max_Quantity_Default;
		}
		if ($Term_Max_Qnt == '') {
			$Term_Max_Qnt = $No_MaxEmpty; // '-1' for Unlimited
		}		

		if (! $Term_Step_Qnt) {
			$Term_Step_Qnt = $WooDecimalProduct_Step_Quantity_Default;
		}

		if (! $Term_Set_Qnt) {
			$Term_Set_Qnt = $WooDecimalProduct_Item_Quantity_Default;
		}

		$WooDecimalProduct_QuantityData['min_qnt'] 		= $Term_Min_Qnt;
		$WooDecimalProduct_QuantityData['max_qnt'] 		= $Term_Max_Qnt;
		$WooDecimalProduct_QuantityData['def_qnt'] 		= $Term_Set_Qnt;
		$WooDecimalProduct_QuantityData['stp_qnt'] 		= $Term_Step_Qnt;
		$WooDecimalProduct_QuantityData['price_unit'] 	= $Term_Price_Unit;	

		return $WooDecimalProduct_QuantityData;
	}
	
	/* Получаем QuantityData Категории Товаров по Product_ID.
	 * Категорий может быть несколько. Выбираем ту, в которой имеется Price_Unit_Label
	----------------------------------------------------------------- */
	function WooDecimalProduct_Get_Term_QuantityData_by_ProductID ($Product_ID) {
		$Term_QuantityData = array();
		
		$Price_Unit_Label = '';
		
		$Product_Category_IDs = wc_get_product_term_ids ($Product_ID, 'product_cat');
		
		// Берем первую из Категорий если их несколько - в которой имеется Price_Unit_Label.
		// Если Price_Unit_Label отсутствует, то берем Первую из Категорий.
		foreach ($Product_Category_IDs as $Term_ID) {
			if ( empty( $Term_QuantityData ) ) {
				$Term_QuantityData = WooDecimalProduct_Get_Term_QuantityData_by_TermID ($Term_ID);
			}				
			
			if ($Price_Unit_Label == '') {
				$Term_Price_Unit = get_term_meta ($Term_ID, 'woodecimalproduct_term_price_unit', $single = true);

				if ($Term_Price_Unit) {
					$Price_Unit_Label = $Term_Price_Unit;
					
					$Term_QuantityData = WooDecimalProduct_Get_Term_QuantityData_by_TermID ($Term_ID);
				}		
			}
		}

		return $Term_QuantityData;		
	}
	
	/* Order. Update Item Meta.
	 * Woo version > 9.4.3
	----------------------------------------------------------------- */	
	function WooDecimalProduct_Update_Order_Item_Meta ($Item_ID, $Item_Name, $Item_Value) {
		global $wpdb;
		
		$Table_WooOrderItemMeta = $wpdb -> prefix .'woocommerce_order_itemmeta';
		
		$Query = "
			UPDATE $Table_WooOrderItemMeta 
			SET meta_value = %s
			WHERE (
				order_item_id = %s
				AND
				meta_key = %s
			)
		";
		
		$wpdb -> query ( $wpdb -> prepare ($Query, $Item_Value, $Item_ID, $Item_Name) ); // phpcs:ignore 
	}

	/* Order. Delete Item Meta.
	 * Woo version > 9.4.3
	----------------------------------------------------------------- */
	function WooDecimalProduct_Delete_Order_Item_Meta ($Item_ID) {
		global $wpdb;
		
		$Table_WooOrderItemMeta = $wpdb -> prefix .'woocommerce_order_itemmeta';
		
		$Query = "DELETE FROM $Table_WooOrderItemMeta WHERE order_item_id = %s";			
		$wpdb -> query ( $wpdb -> prepare ($Query, $Item_ID) ); // phpcs:ignore 
	}

	/* WDPQ Cart. Get Total.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Get_WDPQ_Cart_Total ($Cart_Total) {
		$debug_process = 'f_get_wdpq_cart_total';

		WDPQ_Debugger ($Cart_Total, '$Cart_Total', $debug_process, __FUNCTION__, __LINE__);
		
		$WDPQ_Cart_Total = 0;
		
		$WDPQ_Cart = WooDecimalProduct_Get_WDPQ_CartSession($isDraft = false);
		WDPQ_Debugger ($WDPQ_Cart, '$WDPQ_Cart', $debug_process, __FUNCTION__, __LINE__);
		
		if ($WDPQ_Cart) {
			foreach ($WDPQ_Cart as $Item) {
				// WDPQ_Debugger ($Item, '$Item', $debug_process, __FUNCTION__, __LINE__);
				
				// $Product_ID = $Item['product_id'];
				$Quantity 	= $Item['quantity'];
				$Price 		= $Item['price'];
				
				$Item_Subtotal = $Price * $Quantity;
				
				$WDPQ_Cart_Total += $Item_Subtotal;
			}

			return $WDPQ_Cart_Total;
		} 
		
		return $Cart_Total;
	}

	/* WDPQ Cart. Get $Item by $Product_ID.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Get_WDPQ_CartItem_by_ProductID ($Product_ID, $isVariation = false) {
		$debug_process = 'f_get_wdpq_cart_item_by_productid';

		WDPQ_Debugger ($Product_ID, '$Product_ID', $debug_process, __FUNCTION__, __LINE__);
		WDPQ_Debugger ($isVariation, '$isVariation', $debug_process, __FUNCTION__, __LINE__);
		
		$Item = null;
		
		$WDPQ_Cart = WooDecimalProduct_Get_WDPQ_CartSession ($isDraft = false);
		WDPQ_Debugger ($WDPQ_Cart, '$WDPQ_Cart', $debug_process, __FUNCTION__, __LINE__);

		if ($WDPQ_Cart) {
			foreach ($WDPQ_Cart as $Item) {
				if ($isVariation) {
					//Вариативный Товар.
					$Item_ProductID = $Item['variation_id'];
					
				} else {
					//Простой Товар.
					$Item_ProductID = $Item['product_id'];
				}
				
				if ($Product_ID == $Item_ProductID) {
					WDPQ_Debugger ($Item, '$Item', $debug_process, __FUNCTION__, __LINE__);
					return $Item;
				}	
			}
		}

		WDPQ_Debugger ($Item, '$Item', $debug_process, __FUNCTION__, __LINE__);
		return $Item;
	}

	/* WDPQ Cart. Get $Item by $CartProductKey.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Get_WDPQ_Cart_Item_by_CartProductKey ($Cart_Item_Key) {
		$Item = null;
		
		$WDPQ_Cart = WooDecimalProduct_Get_WDPQ_CartSession ($isDraft = false);

		if ($WDPQ_Cart) {
			foreach ($WDPQ_Cart as $Item) {
				$Item_Key = $Item['key'];	
				
				if ($Cart_Item_Key == $Item_Key) {
					return $Item;
				}	
			}
		}
		
		return $Item;
	}

	/* WDPQ Cart. Get $Product_ID by $CartProductKey.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Get_WDPQ_Cart_ProductID_by_CartProductKey ($Cart_Item_Key) {
		$Product_ID = 0;
		
		$WDPQ_Cart = WooDecimalProduct_Get_WDPQ_CartSession ($isDraft = false);

		if ($WDPQ_Cart) {
			foreach ($WDPQ_Cart as $Item) {
				$Item_Key 		= $Item['key'];	
				$Item_ProductID = $Item['product_id'];
				
				if ($Cart_Item_Key == $Item_Key) {
					return $Item_ProductID;
				}
			}
		}
		
		return $Product_ID;
	}

	/* WDPQ Cart. Get Quantity by $CartProductKey.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Get_WDPQ_Cart_Quantity_by_CartProductKey ($Cart_Item_Key) {
		$Quantity = 1;
		
		$WDPQ_Cart = WooDecimalProduct_Get_WDPQ_CartSession ($isDraft = false);

		if ($WDPQ_Cart) {
			foreach ($WDPQ_Cart as $Item) {
				$Item_Key 		= $Item['key'];	
				$Item_Quantity 	= $Item['quantity'];
				
				if ($Cart_Item_Key == $Item_Key) {
					return $Item_Quantity;
				}
			}
		}
		
		return $Quantity;
	}

	/* WDPQ Cart. Update Session.
	 * Create New / Update
	----------------------------------------------------------------- */
	function WooDecimalProduct_Update_WDPQ_CartSession ($Add_to_Cart, $isDraft = false) {
		$debug_process = 'f_update_cartsession';

		WDPQ_Debugger ($Add_to_Cart, '$Add_to_Cart', $debug_process, __FUNCTION__, __LINE__);
		WDPQ_Debugger ($isDraft, '$isDraft', $debug_process, __FUNCTION__, __LINE__);
		
		$WDPQ_Cart = WooDecimalProduct_Get_WDPQ_CartSession ($isDraft);
		WDPQ_Debugger ($WDPQ_Cart, '$WDPQ_Cart', $debug_process, __FUNCTION__, __LINE__);

		if ($WDPQ_Cart)	{
			// Корзина существует
			$NewCart_Data = array();
			
			// Обновление Количества для имеющихся в Корзине Товаров.
			foreach ($WDPQ_Cart as $Cart_Product_Item) {
				WDPQ_Debugger ($WDPQ_Cart, '$WDPQ_Cart', $debug_process, __FUNCTION__, __LINE__);
				WDPQ_Debugger ($Cart_Product_Item, '$Cart_Product_Item', $debug_process, __FUNCTION__, __LINE__);
				
				$Cart_Item_Key 			= $Cart_Product_Item['key'];			
				$Cart_Item_ProductID 	= $Cart_Product_Item['product_id'];
				$Cart_Item_Variation_ID = $Cart_Product_Item['variation_id'];
				$Cart_Item_Quantity 	= $Cart_Product_Item['quantity'];
				$Cart_Item_Price 		= $Cart_Product_Item['price'];
				
				$Item_RegularPrice 		= isset( $Cart_Product_Item['regular_price'] ) ? $Cart_Product_Item['regular_price']: 0;
				$Item_SalePrice 		= isset( $Cart_Product_Item['sale_price'] ) ? $Cart_Product_Item['sale_price']: 0;
				$Price_Excl_Tax 		= isset( $Cart_Product_Item['price_tax_excl'] ) ? $Cart_Product_Item['price_tax_excl']: 0;
				$Price_Incl_Tax 		= isset( $Cart_Product_Item['price_tax_incl'] ) ? $Cart_Product_Item['price_tax_incl']: 0;
				$Tax_Amount 			= isset( $Cart_Product_Item['tax_amount'] ) ? $Cart_Product_Item['tax_amount']: 0;
				
				$Item = array(
					'key' => $Cart_Item_Key,
					'product_id' => $Cart_Item_ProductID,
					'variation_id' => $Cart_Item_Variation_ID,
					'quantity' => $Cart_Item_Quantity,
					'price' => $Cart_Item_Price,
					'regular_price' => $Item_RegularPrice,
					'sale_price' => $Item_SalePrice,
					'price_tax_excl' => $Price_Excl_Tax,
					'price_tax_incl' => $Price_Incl_Tax,
					'tax_amount' => $Tax_Amount,					
				);		
				
				foreach ($Add_to_Cart as $Add_to_Cart_Product_Item) {	
					$Add_to_Cart_Key 			= $Add_to_Cart_Product_Item['key'];
					$Add_to_Cart_ProductID 		= $Add_to_Cart_Product_Item['product_id'];
					$Add_to_Cart_VariationID 	= $Add_to_Cart_Product_Item['variation_id'];
					$Add_to_Cart_Quantity 		= $Add_to_Cart_Product_Item['quantity'];
					$Add_to_Cart_Price 			= $Add_to_Cart_Product_Item['price'];
					
					$Add_to_Cart_RegularPrice	= $Add_to_Cart_Product_Item['regular_price'];
					$Add_to_Cart_SalePrice		= $Add_to_Cart_Product_Item['sale_price'];
					$Add_to_Cart_Excl_Tax		= $Add_to_Cart_Product_Item['price_tax_excl'];
					$Add_to_Cart_Incl_Tax		= $Add_to_Cart_Product_Item['price_tax_incl'];
					$Add_to_Cart_Tax_Amount		= $Add_to_Cart_Product_Item['tax_amount'];

					if ($Add_to_Cart_Key == $Cart_Item_Key) {
						// Суммируем
						$New_Quantity = $Add_to_Cart_Quantity + $Cart_Item_Quantity;
						$New_Quantity = WooDecimalProduct_Round_ProductQuantity ($Cart_Item_ProductID, $New_Quantity);
						
						$Item = array(
							'key' => $Cart_Item_Key,
							'product_id' => $Cart_Item_ProductID,
							'variation_id' => $Cart_Item_Variation_ID,
							'quantity' => $New_Quantity,
							'price' => $Add_to_Cart_Price,
							'regular_price' => $Add_to_Cart_RegularPrice,
							'sale_price' => $Add_to_Cart_SalePrice,
							'price_tax_excl' => $Add_to_Cart_Excl_Tax,
							'price_tax_incl' => $Add_to_Cart_Incl_Tax,
							'tax_amount' => $Add_to_Cart_Tax_Amount,							
						);
					}
				}
				
				$NewCart_Data[] = $Item;
			}

			// Добавление Новых Товаров
			foreach ($Add_to_Cart as $Add_to_Cart_Product_Item) {
				$Add_to_Cart_Key 			= $Add_to_Cart_Product_Item['key'];
				$Add_to_Cart_ProductID 		= $Add_to_Cart_Product_Item['product_id'];
				$Add_to_Cart_VariationID 	= $Add_to_Cart_Product_Item['variation_id'];
				$Add_to_Cart_Quantity 		= $Add_to_Cart_Product_Item['quantity'];
				$Add_to_Cart_Price 			= $Add_to_Cart_Product_Item['price'];
				
				$Add_to_Cart_RegularPrice	= $Add_to_Cart_Product_Item['regular_price'];
				$Add_to_Cart_SalePrice		= $Add_to_Cart_Product_Item['sale_price'];
				$Add_to_Cart_Excl_Tax		= $Add_to_Cart_Product_Item['price_tax_excl'];
				$Add_to_Cart_Incl_Tax		= $Add_to_Cart_Product_Item['price_tax_incl'];
				$Add_to_Cart_Tax_Amount		= $Add_to_Cart_Product_Item['tax_amount'];				
				
				$Item_Exist = false;
				
				foreach ($WDPQ_Cart as $Cart_Product_Item) {
					$Cart_Item_ProductID 	= $Cart_Product_Item['product_id'];
					
					if ($Add_to_Cart_Key == $Cart_Item_Key) {
						$Item_Exist = true;
					}
				}
				
				if (! $Item_Exist) {
					$Item = array(
						'key' => $Add_to_Cart_Key,
						'product_id' => $Add_to_Cart_ProductID,
						'variation_id' => $Add_to_Cart_VariationID,
						'quantity' => $Add_to_Cart_Quantity,
						'price' => $Add_to_Cart_Price,
						'regular_price' => $Add_to_Cart_RegularPrice,
						'sale_price' => $Add_to_Cart_SalePrice,
						'price_tax_excl' => $Add_to_Cart_Excl_Tax,
						'price_tax_incl' => $Add_to_Cart_Incl_Tax,
						'tax_amount' => $Add_to_Cart_Tax_Amount,						
					);

					$NewCart_Data[] = $Item;
				}
			}
			
			$WDPQ_Cart = $NewCart_Data;

		} else {
			// Пустая Корзина
			$WDPQ_Cart = $Add_to_Cart;			
		}
		
		WooDecimalProduct_Set_WDPQ_CartSession ($WDPQ_Cart, $isDraft);

		WDPQ_Debugger ($WDPQ_Cart, '$WDPQ_Cart', $debug_process, __FUNCTION__, __LINE__);
		return $WDPQ_Cart;
	}

	/* WDPQ Cart. Set CartSession.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Set_WDPQ_CartSession ($WDPQ_Cart, $isDraft = false) {
		$debug_process = 'f_set_cartsession';

		WDPQ_Debugger ($WDPQ_Cart, '$WDPQ_Cart', $debug_process, __FUNCTION__, __LINE__);
		WDPQ_Debugger ($isDraft, '$isDraft', $debug_process, __FUNCTION__, __LINE__);
		
		if ($isDraft) {
			// Черновая Корзина.
			$Cart_Name = 'wdpq_draft_cart';
		} else {
			// Основная Корзина.
			$Cart_Name = 'wdpq_cart';
		}
		WDPQ_Debugger ($Cart_Name, '$Cart_Name', $debug_process, __FUNCTION__, __LINE__);
		
		$WDPQ_Cart = wp_json_encode( $WDPQ_Cart );
		
		$User_ID = get_current_user_id();
		
		if ($User_ID > 0) {
			// Залогиненый Пользователь. Session User-Meta
			$WDPQ_Cart = serialize ($WDPQ_Cart);
			
			update_user_meta ($User_ID, $Cart_Name, $WDPQ_Cart);
			
			// В новоых версиях Woo Сессия для Залогиненных хранится в Таблице: woocommerce_sessions
			// seesion_key - Это User_ID
			// wps_woocommerce_sessions  
			// a:14:{s:4:"cart";s:854:"a:2:{s:32:"e00da03b685a0dd18fb6a08af0923de0";a:11:{s:3:"key";s:32:"e00da03b685a0dd18fb6a08af0923de0";s:10:"product_id";i:139;s:12:"variation_id";i:0;s:9:"variation";a:0:{}s:8:"quantity";i:1;s:9:"data_hash";s:32:"b5c1d5ca8bae6d4896cf1807cdf763f0";s:13:"line_tax_data";a:2:{s:8:"subtotal";a:0:{}s:5:"total";a:0:{}}s:13:"line_subtotal";d:1;s:17:"line_subtotal_tax";d:0;s:10:"line_total";d:0.9;s:8:"line_tax";d:0;}s:32:"1cc30a05c05758f71f84a8d8137ccfc5";a:11:{s:3:"key";s:32:"1cc30a05c05758f71f84a8d8137ccfc5";s:10:"product_id";i:1983;s:12:"variation_id";i:1984;s:9:"variation";a:1:{s:15:"attribute_tsvet";s:3:"red";}s:8:"quantity";i:1;s:9:"data_hash";s:32:"d6d93761ac6523331e32026c25e4ac76";s:13:"line_tax_data";a:2:{s:8:"subtotal";a:0:{}s:5:"total";a:0:{}}s:13:"line_subtotal";d:100;s:17:"line_subtotal_tax";d:0;s:10:"line_total";d:90;s:8:"line_tax";d:0;}}";s:11:"cart_totals";s:399:"a:15:{s:8:"subtotal";s:3:"101";s:12:"subtotal_tax";d:0;s:14:"shipping_total";s:1:"2";s:12:"shipping_tax";d:0;s:14:"shipping_taxes";a:0:{}s:14:"discount_total";d:10.1;s:12:"discount_tax";d:0;s:19:"cart_contents_total";s:4:"90.9";s:17:"cart_contents_tax";i:0;s:19:"cart_contents_taxes";a:0:{}s:9:"fee_total";s:1:"0";s:7:"fee_tax";d:0;s:9:"fee_taxes";a:0:{}s:5:"total";s:5:"92.90";s:9:"total_tax";d:0;}";s:15:"applied_coupons";s:20:"a:1:{i:0;s:3:"xxx";}";s:22:"coupon_discount_totals";s:23:"a:1:{s:3:"xxx";d:10.1;}";s:26:"coupon_discount_tax_totals";s:6:"a:0:{}";s:21:"removed_cart_contents";s:853:"a:2:{s:32:"e00da03b685a0dd18fb6a08af0923de0";a:11:{s:3:"key";s:32:"e00da03b685a0dd18fb6a08af0923de0";s:10:"product_id";i:139;s:12:"variation_id";i:0;s:9:"variation";a:0:{}s:8:"quantity";i:1;s:9:"data_hash";s:32:"b5c1d5ca8bae6d4896cf1807cdf763f0";s:13:"line_tax_data";a:2:{s:8:"subtotal";a:0:{}s:5:"total";a:0:{}}s:13:"line_subtotal";d:1;s:17:"line_subtotal_tax";d:0;s:10:"line_total";d:1;s:8:"line_tax";d:0;}s:32:"1cc30a05c05758f71f84a8d8137ccfc5";a:11:{s:3:"key";s:32:"1cc30a05c05758f71f84a8d8137ccfc5";s:10:"product_id";i:1983;s:12:"variation_id";i:1984;s:9:"variation";a:1:{s:15:"attribute_tsvet";s:3:"red";}s:8:"quantity";i:1;s:9:"data_hash";s:32:"d6d93761ac6523331e32026c25e4ac76";s:13:"line_tax_data";a:2:{s:8:"subtotal";a:0:{}s:5:"total";a:0:{}}s:13:"line_subtotal";d:100;s:17:"line_subtotal_tax";d:0;s:10:"line_total";d:100;s:8:"line_tax";d:0;}}";s:22:"shipping_for_package_0";s:1282:"a:2:{s:12:"package_hash";s:40:"wc_ship_d7803301eea5d4abf075997f559579ed";s:5:"rates";a:3:{s:15:"free_shipping:1";O:16:"WC_Shipping_Rate":2:{s:7:"
	
		} else {
			// Анонимный Пользователь.
			
			// Session Cookie. Проблемы с AJAX Cart
			// wc_setcookie('wdpq_cart', $WDPQ_Cart, time() + HOUR_IN_SECONDS);
			
			// PHP Sesion
			$Session_Started = WooDecimalProduct_StartSession( $Initiator = __FUNCTION__ );
			WDPQ_Debugger ($Session_Started, '$Session_Started', $debug_process, __FUNCTION__, __LINE__);
			
			if ($Session_Started) {
				$_SESSION[$Cart_Name] = $WDPQ_Cart;	
			}			
		}	
	}

	/* WDPQ Cart. Get CartSession .
	----------------------------------------------------------------- */
	function WooDecimalProduct_Get_WDPQ_CartSession ($isDraft = false) {
		$debug_process = 'f_get_cartsession';

		WDPQ_Debugger ($isDraft, '$isDraft', $debug_process, __FUNCTION__, __LINE__);
		
		if ($isDraft) {
			// Черновая Корзина.
			$Cart_Name = 'wdpq_draft_cart';
		} else {
			// Основная Корзина.
			$Cart_Name = 'wdpq_cart';
		}
		WDPQ_Debugger ($Cart_Name, '$Cart_Name', $debug_process, __FUNCTION__, __LINE__);
		
		$User_ID = get_current_user_id();
		
		if ($User_ID > 0) {
			// Залогиненый Пользователь. Session User-Meta
			$WDPQ_Cart = get_user_meta ($User_ID, $Cart_Name, true);
			
			$WDPQ_Cart = unserialize( $WDPQ_Cart );
		
		} else {
			// Анонимный Пользователь. 
			
			// Session Cookie. Проблемы с AJAX Cart
			// $WDPQ_Cart = isset( $_COOKIE[$Cart_Name] ) ? $_COOKIE[$Cart_Name] : null; // phpcs:ignore	
			
			// PHP Sesion
			$Session_Started = WooDecimalProduct_StartSession( $Initiator = __FUNCTION__ );
			WDPQ_Debugger ($Session_Started, '$Session_Started', $debug_process, __FUNCTION__, __LINE__);

			$WDPQ_Cart = isset( $_SESSION[$Cart_Name] ) ? $_SESSION[$Cart_Name]: null; // phpcs:ignore
			WDPQ_Debugger ($WDPQ_Cart, '$WDPQ_Cart', $debug_process, __FUNCTION__, __LINE__);
		}
		
		if ($WDPQ_Cart) {
			$WDPQ_Cart = stripslashes( $WDPQ_Cart );
			$WDPQ_Cart = json_decode( $WDPQ_Cart, true );
		}	
			
		return $WDPQ_Cart;
	}

	/* WDPQ Cart. Delete CartSession.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Delete_WDPQ_CartSession ($isDraft = false) {
		$debug_process = 'f_delete_cartsession';

		WDPQ_Debugger ($isDraft, '$isDraft', $debug_process, __FUNCTION__, __LINE__);
		
		if ($isDraft) {
			// Черновая Корзина.
			$Cart_Name = 'wdpq_draft_cart';
		} else {
			// Основная Корзина.
			$Cart_Name = 'wdpq_cart';
		}
		WDPQ_Debugger ($Cart_Name, '$Cart_Name', $debug_process, __FUNCTION__, __LINE__);
		
		$User_ID = get_current_user_id();
		
		if ($User_ID > 0) {
			// Залогиненый Пользователь. Session User-Meta
			update_user_meta ($User_ID, $Cart_Name, '');
			
		} else {
			// Анонимный Пользователь. 
			
			// Session Cookie. Проблемы с AJAX Cart
			// wc_setcookie('wdpq_cart', null, time() - HOUR_IN_SECONDS);
			
			// PHP Sesion
			$Session_Started = WooDecimalProduct_StartSession( $Initiator = __FUNCTION__ );
			WDPQ_Debugger ($Session_Started, '$Session_Started', $debug_process, __FUNCTION__, __LINE__);
			
			if ($Session_Started) {
				$_SESSION[$Cart_Name] = '';
			}
		}	
	}

	/* Cart. Get Item Key by $Product_ID.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Get_CartItem_Key_by_ProductID ($Product_ID, $isVariation = false) {
		$debug_process = 'f_get_cartitem_key_by_productid';
		
		WDPQ_Debugger ($Product_ID, '$Product_ID', $debug_process, __FUNCTION__, __LINE__);
		
		$CartItem_Key = 'nokey';
		
		$WooCart = WC() -> cart;
		// WDPQ_Debugger ($WooCart, '$WooCart', $debug_process, __FUNCTION__, __LINE__);

		$Cart_Contents = $WooCart -> cart_contents;	

		if ($Cart_Contents) {
			foreach ($Cart_Contents as $key => $Item) {
				if ($isVariation) {
					//Вариативный Товар.
					$Item_ProductID = $Item['variation_id'];
					
				} else {
					//Простой Товар.
					$Item_ProductID = $Item['product_id'];
				}
				WDPQ_Debugger ($Item_ProductID, '$Item_ProductID', $debug_process, __FUNCTION__, __LINE__);
				
				if ($Item_ProductID == $Product_ID) {
					$CartItem_Key = $key;
				}
			}
		}

		return $CartItem_Key;
	}
	
	/* Cart. Get Item Product by Item Key.
	----------------------------------------------------------------- */	
	function WooDecimalProduct_Get_CartItem_Product_by_ItemKey ($CartItem_Key) {
		$debug_process = 'f_get_cartitem_product_by_itemkey';
		
		$CartItem = array ();
		
		$WooCart = WC() -> cart;
		// WDPQ_Debugger ($WooCart, '$WooCart', $debug_process, __FUNCTION__, __LINE__);

		$Cart_Contents = $WooCart -> cart_contents;	
		
		if ($Cart_Contents) {
			foreach ($Cart_Contents as $key => $Item) {
				// WDPQ_Debugger ($Item, '$Item', $debug_process, __FUNCTION__, __LINE__);
				
				$Item_Key = $Item['key'];

				if ($Item_Key == $CartItem_Key) {
					$CartItem = $Item;
				}
			}
		}

		return $CartItem;		
	}
	
	/* Get Woo Price Decimals Settings.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Get_Woo_PriceDecimals_Settings () {	
		$debug_process = 'f_get_price_decimal_settings';
		
		$PriceDecimals = 2;
		
		if (function_exists( 'wc_get_price_decimals' )) {
			$PriceDecimals = wc_get_price_decimals();
		}

		WDPQ_Debugger ($PriceDecimals, '$PriceDecimals', $debug_process, __FUNCTION__, __LINE__);
		return $PriceDecimals;
	}	
	
	/* Get Locale_Delimiter.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Get_Locale_Delimiter () {
		$debug_process = 'init';
		
		$Locale_Info = localeconv();
		$Locale_Delimiter = $Locale_Info['decimal_point'];

		WDPQ_Debugger ($Locale_Delimiter, '$Locale_Delimiter', $debug_process, __FUNCTION__, __LINE__);
		return $Locale_Delimiter;
	}	
	
	/* Totals-Round.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Totals_Round ($Total) {
		$debug_process = 'f_totals_round';
		
		$PriceDecimals = WooDecimalProduct_Get_Woo_PriceDecimals_Settings ();
		
		$Totals_Round = round( $Total, $PriceDecimals );

		WDPQ_Debugger ($Totals_Round, '$Totals_Round', $debug_process, __FUNCTION__, __LINE__);
		return $Totals_Round;
	}
	
	/* Get Quantity-Precision for Product.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Get_Product_QNT_Precision ($Product_ID) {
		$No_MaxEmpty = '-1';	// Unlimited
		$WooDecimalProduct_QuantityData = WooDecimalProduct_Get_QuantityData_by_ProductID ($Product_ID, $No_MaxEmpty);
					
		$QNT_Precision = $WooDecimalProduct_QuantityData['precision'];
		
		return $QNT_Precision;
	}	
	
	/* Round Product Quantity with Precision.
	----------------------------------------------------------------- */
	function WooDecimalProduct_Round_ProductQuantity ($Product_ID, $Quantity) {
		$QNT_Precision = WooDecimalProduct_Get_Product_QNT_Precision ($Product_ID);
		
		if ($QNT_Precision) {
			$Quantity = round( $Quantity, $QNT_Precision );
		}
		
		return $Quantity;
	}
	
	/* Check is WDPQ-Cart is Empty.
	----------------------------------------------------------------- */
	function WooDecimalProduct_is_WDPQCart_Empty () {
		$debug_process = 'f_check_wdpqcart_empty';
		
		$User_ID = get_current_user_id();
		
		if ($User_ID > 0) {
			// Залогиненый Пользователь. Session User-Meta
			$WDPQ_Cart = get_user_meta ($User_ID, 'wdpq_cart', true);
			
			$WDPQ_Cart = unserialize( $WDPQ_Cart );
			
		} else {
			// Анонимный Пользователь. 
			
			$Session_Started = WooDecimalProduct_StartSession( $Initiator = __FUNCTION__ );
			WDPQ_Debugger ($Session_Started, '$Session_Started', $debug_process, __FUNCTION__, __LINE__);		
			
			$WDPQ_Cart = isset( $_SESSION["wdpq_cart"] ) ? $_SESSION["wdpq_cart"]: null; // phpcs:ignore
		}

		WDPQ_Debugger ($WDPQ_Cart, '$WDPQ_Cart', $debug_process, __FUNCTION__, __LINE__);
		
		if ($WDPQ_Cart && $WDPQ_Cart != '[]') {			
			return false;
			
		} else {
			return true;
		}
	}
	
	/* Clear Woo Cart if WDPQ-Cart Emty. 
	----------------------------------------------------------------- */
	function WooDecimalProduct_Clear_WooCart_if_WDPQCart_Emty () {
		$debug_process = 'clear_cart';
		
		// Возможна ситуация, когда Корзина была сформирована, но Браузер закрыли и открыли снова. 
		// Но открыли не на странице Корзина (там нормально это отрабатывается, а на странице Товара)
		// при этом, WDPQ-Cart пустая, но Woo-Cart автоматически создана из прошлой Сессии.
		// Поэтому, в такой ситуации, необходимо Удалить Woo-Cart.
		
		$isWDPQ_Cart_Empty = WooDecimalProduct_is_WDPQCart_Empty ();
		WDPQ_Debugger ($isWDPQ_Cart_Empty, '$isWDPQ_Cart_Empty', $debug_process, __FUNCTION__, __LINE__);
		
		if ($isWDPQ_Cart_Empty) {
			wc_empty_cart();
		}
		
		return true;
	}
	
	/* Debugger. 
	----------------------------------------------------------------- */
	function WDPQ_Debugger ($Content, $Subject = null, $Process = null, $Function = '', $Line = '') {
		if (function_exists( 'WPGear_Debugger' )) {
			$Source = 'WDPQ';
			$TimeStamp = true;
			
			$Parameters = array(
				'source' => $Source,
				'content' => $Content,
				'subject' => esc_html( $Subject ),
				'process' => esc_html( $Process ),
				'function' => esc_html( $Function ),
				'timestamp' => $TimeStamp,
				'line' => esc_html( $Line ),
			);
			
			WPGear_Debugger ($Parameters);
		}

		// OnLine ConsoleLog Debugger.
		global $WooDecimalProduct_ConsoleLog_Debuging;
		
		if ($WooDecimalProduct_ConsoleLog_Debuging) {
			$is_DebugOnLine = isset( $_REQUEST['debug'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['debug'] ) ) : false; // phpcs:ignore

			if ($is_DebugOnLine == $Process || $is_DebugOnLine == 'all') {
				if ($Content) {
					$Content = wp_json_encode( $Content );
				} else {
					if (is_null( $Content )) {
						$Content = 'is NULL';
					} 					
				}
				
				?>
				<script type='text/javascript'>	
					var WDPQ_Debug_Subject 	= '<?php echo esc_html( $Subject ); ?>';
					var WDPQ_Debug_Process 	= '<?php echo esc_html( $Process ); ?>';
					var WDPQ_Debug_Content 	= <?php var_export( $Content ); // phpcs:ignore?>;
					
					console.log( 'WDPQ_Debug: ' + WDPQ_Debug_Subject + ' ' + WDPQ_Debug_Process );
					console.log( WDPQ_Debug_Content );
					console.log( '------------------------' );
				</script>
				<?php
			}
		}
	}	