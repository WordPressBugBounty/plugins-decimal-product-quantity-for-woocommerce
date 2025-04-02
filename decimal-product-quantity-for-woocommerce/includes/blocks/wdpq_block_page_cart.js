// Decimal Product Quantity for WooCommerce
// Bloks. Product Cart
// wdpq_block_page_cart.js
	
	var WDPQ_Cart_Object;
	window.addEventListener ('load', function() {
		var WDPQ_Block_Cart_Items = document.getElementsByClassName( "wc-block-cart-items__row" );	

		if (WDPQ_Block_Cart_Items.length > 0) {
			// Block Cart Detected
			console.log( 'wdpq_block_page_cart.js Loaded.' );
			
			
			
var WDPQ_Nonce = '*';

var WDPQ_localStorage = localStorage.getItem('storeApiCartData');
WDPQ_localStorage = JSON.parse(WDPQ_localStorage);
console.log( WDPQ_localStorage );
			
			var WooDecimalProductQNT_Ajax_URL = ajaxurl;
			var WooDecimalProductQNT_Ajax_Data = 'action=WooDecimalProduct_Blocks_Get_WDPQ-Cart&wdpq_wpnonce=' + WDPQ_Nonce;
			
			jQuery.ajax({
				type:"POST",
				url: WooDecimalProductQNT_Ajax_URL,
				dataType: 'json',
				data: WooDecimalProductQNT_Ajax_Data,
				cache: false,
				success: function(jsondata) {
					var Obj_Request = jsondata;	

					WDPQ_Cart_Object = Obj_Request.wdpq_cart;
					
					console.log(Obj_Request);
					
					var WDPQ_Cart_Items_Slug = [];
					
					for (i = 0; i < WDPQ_Block_Cart_Items.length; i++) {
						var WDPQ_Block_Cart_Item_Name = WDPQ_Block_Cart_Items[i].getElementsByClassName( "wc-block-components-product-name" );
						
						var WDPQ_Cart_Item_Pathname = WDPQ_Block_Cart_Item_Name[0].pathname;
						
						WDPQ_Cart_Item_Pathname = WDPQ_Cart_Item_Pathname.substring( 1, WDPQ_Cart_Item_Pathname.length - 1 );
						
						var WDPQ_Cart_Item_Pathname_Parts = WDPQ_Cart_Item_Pathname.split('/');
						
						var WDPQ_Cart_Item_Slug = WDPQ_Cart_Item_Pathname_Parts[WDPQ_Cart_Item_Pathname_Parts.length -1];
						
						for (j = 0; j < WDPQ_Cart_Object.length; j++) {
							WDPQ_Cart_Object_Item_Slug = WDPQ_Cart_Object[j].slug;
							
							if (WDPQ_Cart_Item_Slug == WDPQ_Cart_Object_Item_Slug) {
								// Переназначение Параметров Input.
								var WDPQ_ItemProduct_QuantityData = WDPQ_Cart_Object[j].quantity_data;
								console.log(WDPQ_ItemProduct_QuantityData);
								
								var WDPQ_ItemProduct_ID			= WDPQ_Cart_Object[j].product_id;
								var WDPQ_ItemProduct_Min_Qnt 	= Number( WDPQ_ItemProduct_QuantityData['min_qnt'] );
								var WDPQ_ItemProduct_Max_Qnt 	= Number( WDPQ_ItemProduct_QuantityData['max_qnt'] );
								var WDPQ_ItemProduct_Stp_Qnt 	= Number( WDPQ_ItemProduct_QuantityData['stp_qnt'] );
								var WDPQ_ItemProduct_Precision 	= Number( WDPQ_ItemProduct_QuantityData['precision'] );
								var WDPQ_ItemProduct_Quantity 	= WDPQ_Cart_Object[j].quantity;
								
								var WDPQ_Cart_Item_Input_Quantity = WDPQ_Block_Cart_Items[i].getElementsByClassName("wc-block-components-quantity-selector__input")[0];
								console.log(WDPQ_Cart_Item_Input_Quantity);

								
								
								WDPQ_Cart_Item_Input_Quantity.setAttribute( 'product_id', WDPQ_ItemProduct_ID );
								WDPQ_Cart_Item_Input_Quantity.setAttribute( 'min', WDPQ_ItemProduct_Min_Qnt );
								WDPQ_Cart_Item_Input_Quantity.setAttribute( 'max', WDPQ_ItemProduct_Max_Qnt );
								WDPQ_Cart_Item_Input_Quantity.setAttribute( 'step', WDPQ_ItemProduct_Stp_Qnt );
								// WDPQ_Cart_Item_Input_Quantity.setAttribute( 'value', WDPQ_ItemProduct_Quantity );
								WDPQ_Cart_Item_Input_Quantity.value = WDPQ_ItemProduct_Quantity;
								
								// WDPQ_Cart_Item_Input_Quantity.trigger("change");
								
								WDPQ_localStorage.items[j].quantity = WDPQ_ItemProduct_Quantity;
								WDPQ_localStorage.items[j].quantity_limits.maximum = WDPQ_ItemProduct_Max_Qnt;
								WDPQ_localStorage.items[j].quantity_limits.minimum = WDPQ_ItemProduct_Min_Qnt;
								
								console.log( 'WDPQ_localStorage:' );
								console.log( WDPQ_localStorage );
							}
						}
						
						// WDPQ_Cart_Items.push(WDPQ_Cart_Item_Slug);
						WDPQ_Cart_Items_Slug[WDPQ_Cart_Item_Slug] = 0;
					}
					console.log(WDPQ_Cart_Items_Slug);	

					WDPQ_localStorage_New = JSON.stringify( WDPQ_localStorage );
					console.log(WDPQ_localStorage_New);	
					
					localStorage.setItem( 'storeApiCartData', WDPQ_localStorage_New );
				}
			});			
		}
				
	});		