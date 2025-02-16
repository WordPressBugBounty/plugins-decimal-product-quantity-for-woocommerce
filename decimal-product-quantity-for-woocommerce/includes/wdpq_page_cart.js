// Decimal Product Quantity for WooCommerce
// Product Cart
// wdpq_page_cart.js
	
	window.addEventListener ('load', function() {
		console.log('wdpq_page_cart.js Loaded.');	

		var WDPQ_ConsoleLog_Debug_Enable 	= wdpq_script_params['debug_enable'];
		var WDPQ_Cart_Items_Keys 			= wdpq_script_params['cart_items_keys'];
		var WDPQ_QuantityData 				= wdpq_script_params['quantity_data'];
		var WDPQ_Auto_Correction_Enable 	= wdpq_script_params['autocorrection_enable'];
		var WDPQ_AJAX_Cart_Update_Enable	= wdpq_script_params['ajax_cart_update_enable'];
		var WDPQ_ButtonsPM_Cart_Enable 		= wdpq_script_params['buttons_pm_enable'];
		var WDPQ_Msg_NoValidValue 			= wdpq_script_params['msg_no_valid_value'];
		var WDPQ_Msg_MoreThanMaxAllowed 	= wdpq_script_params['msg_more_than_the_max_allowed'];	

		WDPQ_ConsoleLog_Debug_Enable 	= Number(WDPQ_ConsoleLog_Debug_Enable);	
		WDPQ_Auto_Correction_Enable		= Number( WDPQ_Auto_Correction_Enable );
		WDPQ_AJAX_Cart_Update_Enable	= Number( WDPQ_AJAX_Cart_Update_Enable );
		WDPQ_ButtonsPM_Cart_Enable		= Number( WDPQ_ButtonsPM_Cart_Enable );
		
		// Купоны доступны только в Pro Версии. Потому, что с ними все стало совсем не просто.
		WDPQ_Hide_CouponBox ();
		
		// AJAX Cart Update. Скрываем Кнопку "Обновить Корзину"
		WDPQ_Hide_CartButton ();					

		var WDPQ_ButtonsPM_Processing_Busy = false;
							
		// Input Quantity. Добавляем Аттрибуты "product_id" и Кнопки: +/-
		WDPQ_Add_Attribute_ProductID_for_InputQNT();
		
		// Buttons Change Quantity Processing.
		jQuery('.woocommerce').on('click', 'button.wpdq_cart_buttons_step', function(e) {
			WDPQ_ConsoleLog_Debuging ('...Click...');
			
			var WDPQ_ButtonPM_Value = e.currentTarget.value;
			WDPQ_ConsoleLog_Debuging ('value: ' + WDPQ_ButtonPM_Value);
								
			var WDPQ_ItemProductID = e.currentTarget.attributes.item_index.value;														
			WDPQ_ConsoleLog_Debuging ('product_id: ' + WDPQ_ItemProductID);

			var WDPQ_QNT_Input_Normal = '';

			var WDPQ_Item_Element = "input[product_id='" + WDPQ_ItemProductID + "']";	

			var WDPQ_Item_Element_InputQNT = jQuery(WDPQ_Item_Element);
			WDPQ_ConsoleLog_Debuging (WDPQ_Item_Element_InputQNT);

			var WDPQ_ItemProduct_QuantityData = WDPQ_QuantityData[WDPQ_ItemProductID];
			WDPQ_ConsoleLog_Debuging (WDPQ_ItemProduct_QuantityData);

			var WDPQ_ItemProduct_Min_Qnt 	= Number(WDPQ_ItemProduct_QuantityData['min_qnt']);
			var WDPQ_ItemProduct_Max_Qnt 	= Number(WDPQ_ItemProduct_QuantityData['max_qnt']);
			var WDPQ_ItemProduct_Stp_Qnt 	= Number(WDPQ_ItemProduct_QuantityData['stp_qnt']);
			var WDPQ_ItemProduct_Precision 	= Number(WDPQ_ItemProduct_QuantityData['precision']);
						
			var WDPQ_QNT_Input_Value = Number( WDPQ_Item_Element_InputQNT.val() );
			WDPQ_ConsoleLog_Debuging ('val: ' + WDPQ_QNT_Input_Value);
			
			// Plus
			if (WDPQ_ButtonPM_Value == 'plus') {	
				WDPQ_QNT_Input_Value = WDPQ_QNT_Input_Value + WDPQ_ItemProduct_Stp_Qnt;
				WDPQ_QNT_Input_Normal = Number(WDPQ_QNT_Input_Value.toFixed(WDPQ_ItemProduct_Precision));

				if (WDPQ_ItemProduct_Max_Qnt != -1) {
					WDPQ_ButtonsPM_Processing_Busy = true;			

					if (WDPQ_QNT_Input_Normal <= WDPQ_ItemProduct_Max_Qnt) {
						WDPQ_Item_Element_InputQNT.val(WDPQ_QNT_Input_Normal).trigger("change");

						WDPQ_ConsoleLog_Debuging ('[Plus] new value: ' + WDPQ_QNT_Input_Normal);
					}
					
				} else {
					WDPQ_ButtonsPM_Processing_Busy = true;	
					
					WDPQ_Item_Element_InputQNT.val(WDPQ_QNT_Input_Normal).trigger("change");
					
					WDPQ_ConsoleLog_Debuging ('[Plus] new value: ' + WDPQ_QNT_Input_Normal);
				}		
			}
			
			// Minus
			if (WDPQ_ButtonPM_Value == 'minus') {
				WDPQ_QNT_Input_Value = WDPQ_QNT_Input_Value - WDPQ_ItemProduct_Stp_Qnt;
				WDPQ_QNT_Input_Normal = Number(WDPQ_QNT_Input_Value.toFixed(WDPQ_ItemProduct_Precision));

				if (WDPQ_QNT_Input_Normal >= WDPQ_ItemProduct_Min_Qnt) {
					WDPQ_ButtonsPM_Processing_Busy = true;
					
					WDPQ_Item_Element_InputQNT.val(WDPQ_QNT_Input_Normal).trigger("change");
					
					WDPQ_ConsoleLog_Debuging ('[Minus] new value: ' + WDPQ_QNT_Input_Normal);		
				}
			}
		});

		// Manual Change Quantity Processing.
		jQuery('.woocommerce').on('change', 'input.qty', function(e){
			WDPQ_ConsoleLog_Debuging ('OnChange Processing...');
			WDPQ_ConsoleLog_Debuging (e);	

			if (WDPQ_ButtonsPM_Processing_Busy) {
				// Изменение Количества Кнопками. Коррекция не требуется.
			} else {
				// Изменение Количества Мышкой или Руками.
				// Авто-Коррекция.
				if (WDPQ_Auto_Correction_Enable) {
					var WDPQ_ItemProduct_QNT_Msg = '';
					
					var WDPQ_ItemInputID = e.currentTarget.attributes.id.value;
					WDPQ_ConsoleLog_Debuging ('input_id: ' + WDPQ_ItemInputID);

					var WDPQ_ItemProductID = e.currentTarget.attributes.product_id.value;
					WDPQ_ConsoleLog_Debuging ('item_product_id: ' + WDPQ_ItemProductID);
					
					var WDPQ_ItemProduct_QuantityData = WDPQ_QuantityData[WDPQ_ItemProductID];
					WDPQ_ConsoleLog_Debuging (WDPQ_ItemProduct_QuantityData);
					
					var WDPQ_ItemProduct_Min_Qnt 	= Number(WDPQ_ItemProduct_QuantityData['min_qnt']);
					var WDPQ_ItemProduct_Max_Qnt 	= Number(WDPQ_ItemProduct_QuantityData['max_qnt']);
					var WDPQ_ItemProduct_Def_Qnt 	= Number(WDPQ_ItemProduct_QuantityData['def_qnt']);
					var WDPQ_ItemProduct_Stp_Qnt 	= Number(WDPQ_ItemProduct_QuantityData['stp_qnt']);
					var WDPQ_ItemProduct_Precision 	= Number(WDPQ_ItemProduct_QuantityData['precision']);
					
					var WDPQ_ItemProduct_Input = e.currentTarget.value;
					WDPQ_ItemProduct_Input = Number(WDPQ_ItemProduct_Input);
					WDPQ_ConsoleLog_Debuging ('Input: ' + WDPQ_ItemProduct_Input);

					var WDPQ_ItemProduct_Input_Normal = Number(WDPQ_ItemProduct_Input.toFixed(WDPQ_ItemProduct_Precision));
					WDPQ_ConsoleLog_Debuging ('*Input: ' + WDPQ_ItemProduct_Input_Normal);

					var WDPQ_ItemProduct_DivStep = Number((WDPQ_ItemProduct_Input_Normal / WDPQ_ItemProduct_Stp_Qnt).toFixed(WDPQ_ItemProduct_Precision));
					WDPQ_ConsoleLog_Debuging ('Input_DivStep: ' + WDPQ_ItemProduct_DivStep);

					var WDPQ_ItemProduct_DivStep_PartInt = WDPQ_ItemProduct_DivStep.toString();
					WDPQ_ItemProduct_DivStep_PartInt = WDPQ_ItemProduct_DivStep_PartInt.split('.');
					WDPQ_ItemProduct_DivStep_PartInt = WDPQ_ItemProduct_DivStep_PartInt[0];
					WDPQ_ItemProduct_DivStep_PartInt = Number(WDPQ_ItemProduct_DivStep_PartInt);
					WDPQ_ConsoleLog_Debuging ('Input_DivStep_PartInt: ' + WDPQ_ItemProduct_DivStep_PartInt);				
					
					var WDPQ_ItemProduct_QNT_Input_Check = Number((WDPQ_ItemProduct_DivStep_PartInt * WDPQ_ItemProduct_Stp_Qnt).toFixed(WDPQ_ItemProduct_Precision));
					WDPQ_ConsoleLog_Debuging ('Check: ' + WDPQ_ItemProduct_QNT_Input_Check);
					
					var WDPQ_ItemProduct_QNT_Valid = WDPQ_ItemProduct_Input_Normal;
					
					// Check Validation
					if (WDPQ_ItemProduct_Input_Normal != WDPQ_ItemProduct_QNT_Input_Check) {																
						WDPQ_ItemProduct_QNT_Valid = Number((WDPQ_ItemProduct_QNT_Input_Check + WDPQ_ItemProduct_Stp_Qnt).toFixed(WDPQ_ItemProduct_Precision));
						WDPQ_ConsoleLog_Debuging ('Valid: ' + WDPQ_ItemProduct_QNT_Valid);
						
						WDPQ_ItemProduct_QNT_Msg = WDPQ_ItemProduct_Input_Normal + ' ' + WDPQ_Msg_NoValidValue + ' ' + WDPQ_ItemProduct_QNT_Valid;
													
						jQuery ("#" + WDPQ_ItemInputID).val(WDPQ_ItemProduct_QNT_Valid);
					} 
					
					// Check Max.
					if (WDPQ_ItemProduct_Max_Qnt != '-1') {
						if (WDPQ_ItemProduct_QNT_Valid > WDPQ_ItemProduct_Max_Qnt) {
							var WDPQ_ItemProduct_QNT_Input_PartInt = Math.trunc (WDPQ_ItemProduct_Max_Qnt / WDPQ_ItemProduct_Stp_Qnt);
							
							WDPQ_ItemProduct_QNT_Valid = Number((WDPQ_ItemProduct_QNT_Input_PartInt * WDPQ_ItemProduct_Stp_Qnt).toFixed(WDPQ_ItemProduct_Precision));

							WDPQ_ItemProduct_QNT_Msg = WDPQ_ItemProduct_Input_Normal + ' ' + WDPQ_Msg_MoreThanMaxAllowed + ' ' + WDPQ_ItemProduct_QNT_Valid;
						}									
					}

					if (WDPQ_ItemProduct_QNT_Msg != '') {
						jQuery ("#" + WDPQ_ItemInputID).val(WDPQ_ItemProduct_QNT_Valid);
						
						alert (WDPQ_ItemProduct_QNT_Msg);
					} else {
						if (WDPQ_ItemProduct_Input_Normal != WDPQ_ItemProduct_Input) {
							WDPQ_ConsoleLog_Debuging ('Floating Number - Detected.');
							jQuery ("#" + WDPQ_ItemInputID).val(WDPQ_ItemProduct_QNT_Input_Check);
						}
					}
					WDPQ_ConsoleLog_Debuging ('-------------');	
				}	
			}

			// AJAX Cart Update. Обновляем Корзину	
			WDPQ_AJAX_Cart_Update ();
			
		});
		
		// Input Quantity. Добавляем Аттрибуты "product_id". (Простой и Вариативный Товары)	
		// Добавляем Кнопки: +/-
		function WDPQ_Add_Attribute_ProductID_for_InputQNT () {
			var Elements_ButtonsPM = jQuery('button.wpdq_cart_buttons_step');
			
			Object.keys(WDPQ_Cart_Items_Keys).forEach(function(key) {
				WDPQ_ConsoleLog_Debuging ('key: ' + WDPQ_Cart_Items_Keys[key]);
				WDPQ_ConsoleLog_Debuging ('Add Attribute product_id: ' + key);
				
				var WDPQ_Element_InputQNT = jQuery("input[name='cart[" + WDPQ_Cart_Items_Keys[key] + "][qty]']");
				WDPQ_ConsoleLog_Debuging (WDPQ_Element_InputQNT);

				WDPQ_Element_InputQNT.attr('product_id', key);
				
				if (WDPQ_ButtonsPM_Cart_Enable) {
					// Добавляем Кнопки: +/- для текущего Элемента Input Quantity.
					if (Elements_ButtonsPM.length > 0) {
						// Кнопки уже сформированы. Проходим мимо.							
					} else {
						WDPQ_Add_Buttons_QNT (key);
					}
				}

			});	
		}

		// Добавляем Кнопки: +/- для текущего Элемента Input Quantity.
		function WDPQ_Add_Buttons_QNT (WDPQ_ProductID) {
			WDPQ_ConsoleLog_Debuging ('Add Buttons for Product_ID: ' + WDPQ_ProductID);
			
			var WDPQ_Button_Minus 	= document.createElement("button");
			var WDPQ_Button_Plus 	= document.createElement("button");
			
			WDPQ_Button_Minus.id = 'wdpq_minus_' + WDPQ_ProductID;
			WDPQ_Button_Minus.name = 'wdpq_minus_' + WDPQ_ProductID;
			WDPQ_Button_Minus.value = 'minus';
			WDPQ_Button_Minus.type = 'button';	
			WDPQ_Button_Minus.innerHTML = '-';	
			
			WDPQ_Button_Plus.id = 'wdpq_plus_' + WDPQ_ProductID;
			WDPQ_Button_Plus.name = 'wdpq_plus_' + WDPQ_ProductID;
			WDPQ_Button_Plus.value = 'plus';
			WDPQ_Button_Plus.type = 'button';	
			WDPQ_Button_Plus.innerHTML = '+';
			
			var Element_InputQNT = jQuery("input[product_id = '" + WDPQ_ProductID + "'");
			
			Element_InputQNT.before(WDPQ_Button_Minus);
			Element_InputQNT.after(WDPQ_Button_Plus);
			
			jQuery("#wdpq_minus_" + WDPQ_ProductID).addClass('wpdq_cart_buttons_step single_add_to_cart_button button');
			jQuery("#wdpq_plus_" + WDPQ_ProductID).addClass('wpdq_cart_buttons_step single_add_to_cart_button button');	
			
			jQuery("#wdpq_plus_" + WDPQ_ProductID).attr('item_index', WDPQ_ProductID);
			jQuery("#wdpq_minus_" + WDPQ_ProductID).attr('item_index', WDPQ_ProductID);	
			
			Element_InputQNT.addClass('wpdq_cart_input_step_mode');					
		}

		// Событие после обновления корзины.
		jQuery(document.body).on('updated_cart_totals', function(){
			WDPQ_ConsoleLog_Debuging ('updated_cart_totals');
			
			WDPQ_Hide_CouponBox ();
			WDPQ_Hide_CartButton ();
			
			WDPQ_Add_Attribute_ProductID_for_InputQNT ();
			
			WDPQ_ButtonsPM_Processing_Busy = false;						
		});						
		
		// AJAX Cart Update. Обновляем Корзину
		function WDPQ_AJAX_Cart_Update () {						
			if (WDPQ_AJAX_Cart_Update_Enable) {
				jQuery("[name='update_cart']").removeAttr("disabled");							
				jQuery("[name='update_cart']").trigger("click");
				
				WDPQ_ConsoleLog_Debuging ('Cart Updating');
			} else {
				jQuery("button[name='update_cart']").prop("disabled", false);							
			}
		}					
		
		// Debug in Browser Console
		function WDPQ_ConsoleLog_Debuging (ConsoleLog) {
			if (WDPQ_ConsoleLog_Debug_Enable) {
				console.log (ConsoleLog);
			}
		}

		// AJAX Cart Update. Скрываем Кнопку "Обновить Корзину"
		function WDPQ_Hide_CartButton () {
			if (WDPQ_AJAX_Cart_Update_Enable) {
				jQuery("[name='update_cart']").parent().css('display', 'none');
			}
		}
		
		// Удаляем Блок Купонов.
		function WDPQ_Hide_CouponBox () {
			var WooDecimalProduct_Element_CouponBox = jQuery("div[class='coupon']");
			WooDecimalProduct_Element_CouponBox.remove();
		}
	});
