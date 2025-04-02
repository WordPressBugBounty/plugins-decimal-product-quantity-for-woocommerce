// Decimal Product Quantity for WooCommerce
// Product Page
// wdpq_page_product.js
	
	window.addEventListener ('load', function() {
		console.log('wdpq_page_product.js Loaded.');		
		
		var WDPQ_ConsoleLog_Debug_Enable 	= wdpq_script_params['debug_enable'];
		var WDPQ_Min_Qnt 					= wdpq_script_params['qnt_min'];
		var WDPQ_Max_Qnt 					= wdpq_script_params['qnt_max'];
		var WDPQ_Default_Qnt 				= wdpq_script_params['qnt_default'];
		var WDPQ_Step_Qnt 					= wdpq_script_params['qnt_step'];
		var WDPQ_QNT_Precision				= wdpq_script_params['qnt_precision'];
		var WDPQ_ButtonsPM_Enable 			= wdpq_script_params['buttons_pm_enable'];
		var WDPQ_Msg_NoValidValue 			= wdpq_script_params['msg_no_valid_value'];
		var WDPQ_Msg_MoreThanMaxAllowed 	= wdpq_script_params['msg_more_than_the_max_allowed'];
		
		WDPQ_ConsoleLog_Debug_Enable 	= Number(WDPQ_ConsoleLog_Debug_Enable);
		WDPQ_Min_Qnt 					= Number(WDPQ_Min_Qnt);
		
		if (WDPQ_Max_Qnt != '-1') {
			WDPQ_Max_Qnt = Number(WDPQ_Max_Qnt);
		}	
		
		WDPQ_Default_Qnt 				= Number(WDPQ_Default_Qnt);
		WDPQ_Step_Qnt 					= Number(WDPQ_Step_Qnt);
		WDPQ_QNT_Precision 				= Number(WDPQ_QNT_Precision);		
		WDPQ_ButtonsPM_Enable 			= Number(WDPQ_ButtonsPM_Enable);
		
		var Element_Input_Quantity = jQuery("input[name=quantity]");
		
		// Fix Quatnity
		if (WDPQ_Min_Qnt == WDPQ_Max_Qnt) {
			WDPQ_ButtonsPM_Enable = 0;
			
			Element_Input_Quantity[0].type = 'text';
		}		
		
		// Buttons [+ / -]
		if (WDPQ_ButtonsPM_Enable) {
			var WDPQ_Button_Minus 	= document.createElement("button");
			var WDPQ_Button_Plus 	= document.createElement("button");
			
			WDPQ_Button_Minus.id = 'wdpq_minus';
			WDPQ_Button_Minus.name = 'wdpq_minus';
			WDPQ_Button_Minus.value = 'minus';
			WDPQ_Button_Minus.type = 'button';	
			WDPQ_Button_Minus.innerHTML = '-';	
			
			WDPQ_Button_Plus.id = 'wdpq_plus';
			WDPQ_Button_Plus.name = 'wdpq_plus';
			WDPQ_Button_Plus.value = 'plus';
			WDPQ_Button_Plus.type = 'button';	
			WDPQ_Button_Plus.innerHTML = '+';
			
			Element_Input_Quantity.before(WDPQ_Button_Minus);
			Element_Input_Quantity.after(WDPQ_Button_Plus);
			
			jQuery("#wdpq_minus").addClass('wpdq_buttons_step single_add_to_cart_button button');
			jQuery("#wdpq_plus").addClass('wpdq_buttons_step single_add_to_cart_button button');
			
			Element_Input_Quantity.addClass('wpdq_input_step_mode');
			
			jQuery(document).on("click", "#wdpq_minus", WDPQ_Quantity_Minus);
			jQuery(document).on("click", "#wdpq_plus", WDPQ_Quantity_Plus);
			
			// Minus
			function WDPQ_Quantity_Minus () {
				var WDPQ_QNT_Input = Element_Input_Quantity.val();
				WDPQ_QNT_Input = Number(WDPQ_QNT_Input);
						
				WDPQ_QNT_Input = WDPQ_QNT_Input - WDPQ_Step_Qnt;
				
				var WDPQ_QNT_Input_Normal = Number(WDPQ_QNT_Input.toFixed(WDPQ_QNT_Precision));
				
				if (WDPQ_QNT_Input_Normal >= WDPQ_Min_Qnt) {
					Element_Input_Quantity.val(WDPQ_QNT_Input_Normal);
				}
			}
			
			// Plus
			function WDPQ_Quantity_Plus () {
				var WDPQ_QNT_Input = Element_Input_Quantity.val();
				WDPQ_QNT_Input = Number(WDPQ_QNT_Input);
						
				WDPQ_QNT_Input = WDPQ_QNT_Input + WDPQ_Step_Qnt;
				var WDPQ_QNT_Input_Normal = Number(WDPQ_QNT_Input.toFixed(WDPQ_QNT_Precision));
				
				if (WDPQ_Max_Qnt != '-1') {
					if (WDPQ_QNT_Input_Normal <= WDPQ_Max_Qnt) {
						Element_Input_Quantity.val(WDPQ_QNT_Input_Normal);
					}		
				} else {
					Element_Input_Quantity.val(WDPQ_QNT_Input_Normal);
				}
			}	
		}

		jQuery (document).on('change','[name=quantity]',function() {
			var WDPQ_QNT_Msg = '';
			
			var WDPQ_QNT_Input = Element_Input_Quantity.val();
			WDPQ_QNT_Input = Number(WDPQ_QNT_Input);
			WDPQ_ConsoleLog_Debuging ('Input: ' + WDPQ_QNT_Input);
			
			var WDPQ_QNT_Input_Normal = Number(WDPQ_QNT_Input.toFixed(WDPQ_QNT_Precision));
			WDPQ_ConsoleLog_Debuging ('*Input: ' + WDPQ_QNT_Input_Normal);

			var WooDecimalProduct_QNT_Input_DivStep = Number((WDPQ_QNT_Input_Normal / WDPQ_Step_Qnt).toFixed(WDPQ_QNT_Precision));
			WDPQ_ConsoleLog_Debuging ('Input_DivStep: ' + WooDecimalProduct_QNT_Input_DivStep);
			
			var WDPQ_QNT_Input_DivStep_PartInt = WooDecimalProduct_QNT_Input_DivStep.toString();
			WDPQ_QNT_Input_DivStep_PartInt = WDPQ_QNT_Input_DivStep_PartInt.split('.');
			WDPQ_QNT_Input_DivStep_PartInt = WDPQ_QNT_Input_DivStep_PartInt[0];
			WDPQ_QNT_Input_DivStep_PartInt = Number(WDPQ_QNT_Input_DivStep_PartInt);
			WDPQ_ConsoleLog_Debuging ('Input_DivStep_PartInt: ' + WDPQ_QNT_Input_DivStep_PartInt);				
			
			var WDPQ_QNT_Input_Check = Number((WDPQ_QNT_Input_DivStep_PartInt * WDPQ_Step_Qnt).toFixed(WDPQ_QNT_Precision));
			WDPQ_ConsoleLog_Debuging ('Check: ' + WDPQ_QNT_Input_Check);
			
			var WDPQ_QNT_Valid = WDPQ_QNT_Input_Normal;
			
			// Check Validation
			if (WDPQ_QNT_Input_Normal != WDPQ_QNT_Input_Check) {																
				var WDPQ_QNT_Valid = Number((WDPQ_QNT_Input_Check + WDPQ_Step_Qnt).toFixed(WDPQ_QNT_Precision));
				WDPQ_ConsoleLog_Debuging ('Valid: ' + WDPQ_QNT_Valid);
				
				WDPQ_QNT_Msg = WDPQ_QNT_Input_Normal + ' ' + WDPQ_Msg_NoValidValue + ' ' + WDPQ_QNT_Valid;
			}

			// Check Max.
			if (WDPQ_Max_Qnt != '-1') {
				if (WDPQ_QNT_Valid > WDPQ_Max_Qnt) {
					var WDPQ_QNT_Input_PartInt = Math.trunc (WDPQ_Max_Qnt / WDPQ_Step_Qnt);
					
					WDPQ_QNT_Valid = Number((WDPQ_QNT_Input_PartInt * WDPQ_Step_Qnt).toFixed(WDPQ_QNT_Precision));

					WDPQ_QNT_Msg = WDPQ_QNT_Input_Normal + ' ' + WDPQ_Msg_MoreThanMaxAllowed + ' ' + WDPQ_QNT_Valid;
				}									
			}

			if (WDPQ_QNT_Msg != '') {
				Element_Input_Quantity.val(WDPQ_QNT_Valid);
				
				alert (WDPQ_QNT_Msg);
			} else {
				if (WDPQ_QNT_Input_Normal != WDPQ_QNT_Input) {
					Element_Input_Quantity.val(WDPQ_QNT_Input_Check);
				}
			}
			WDPQ_ConsoleLog_Debuging ('-------------');
		});	
		
		// Debug in Browser Console
		function WDPQ_ConsoleLog_Debuging (ConsoleLog) {
			if (WDPQ_ConsoleLog_Debug_Enable) {
				console.log (ConsoleLog);
			}
		}		
	});
