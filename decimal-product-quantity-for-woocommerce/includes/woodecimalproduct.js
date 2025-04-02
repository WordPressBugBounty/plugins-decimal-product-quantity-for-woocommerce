// Decimal Product Quantity for WooCommerce
	
	var QNT_Data;
	var WDPQ_Nonce = 'wdpq_ajax_processing';
	
	window.addEventListener ('load', function() {
		console.log('woodecimalproduct.js Loaded.');
	});
	
	function WDPQ_Get_QuantityData (Product_ID) {
		if (Product_ID) {
			var WooDecimalProductQNT_Ajax_URL = ajaxurl;
			var WooDecimalProductQNT_Ajax_Data = 'action=WooDecimalProductQNT&mode=get_product_quantity&id=' + Product_ID + '&_wpnonce=' + WDPQ_Nonce;

			jQuery.ajax({
				type:"POST",
				url: WooDecimalProductQNT_Ajax_URL,
				dataType: 'json',
				data: WooDecimalProductQNT_Ajax_Data,
				cache: false,
				success: function(jsondata) {
					var Obj_Request = jsondata;	

					QNT_Data = Obj_Request.qnt_data;
					
					console.log(Obj_Request);
				}
			});	
		} 
	}