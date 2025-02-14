// Decimal Product Quantity for WooCommerce
// Product Cart. Restore Mode.
// wdpq_page_cart_restore.js
	
	window.addEventListener ('load', function() {
		console.log('wdpq_page_cart_restore.js Loaded.');	
		console.log('Disable Order Processing');
		
		var WDPQ_CartRestore_Nonce 				= wdpq_script_cart_restore_params['nonce'];
		var WDPQ_CartRestore_ItemSubtotal_Title = wdpq_script_cart_restore_params['item_subtotal_title'];
		var WDPQ_CartRestore_Button_Class 		= wdpq_script_cart_restore_params['button_class'];
		var WDPQ_CartRestore_Button_Value 		= wdpq_script_cart_restore_params['button_value'];
		var WDPQ_CartRestore_Button_Label 		= wdpq_script_cart_restore_params['button_label'];
		
		jQuery("td.product-subtotal").html('<span style="cursor: help;" title="' + WDPQ_CartRestore_ItemSubtotal_Title +'">N/A</span>');
		jQuery("td").remove(".actions");
		jQuery("div").remove(".cart-collaterals");
		
		var WDPQ_Element_CartRestore_Nonce = '<input id="wdpq_wpnonce" name="wdpq_wpnonce" type="hidden" value="' + WDPQ_CartRestore_Nonce + '">';
		jQuery(WDPQ_Element_CartRestore_Nonce).appendTo('form.woocommerce-cart-form');									
		
		var WDPQ_Element_Button_Create_Cart = '<div style="text-align: right;"><button type="submit" class="button' + WDPQ_CartRestore_Button_Class + '" name="wdpq_create_cart" value="' + WDPQ_CartRestore_Button_Value + '">' + WDPQ_CartRestore_Button_Label + '</button></div>';
		
		jQuery(WDPQ_Element_Button_Create_Cart).appendTo('form.woocommerce-cart-form');
	});
