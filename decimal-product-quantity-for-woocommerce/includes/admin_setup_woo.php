<?php
/*
 * Decimal Product Quantity for WooCommerce
 * Admin WooCommerce Setup Page.
 * admin_setup_woo.php
 */

	/* Инициализация.
     * Запускаем самым последним, чтобы быть уверенным, что WooCommerce уже инициализировался.
	----------------------------------------------------------------- */    
    add_action ('init', 'WooDecimalProduct_remove_filters', 999999);
	function WooDecimalProduct_remove_filters(){
        if (class_exists ('WooCommerce')){
            // Разрешаем использование дробного количества изменения Товара
            remove_filter ('woocommerce_stock_amount', 'intval');
            add_filter ('woocommerce_stock_amount', 'floatval');
        } 
    } 

	/* DashBoard. Products Menu. Create plugin SubMenu
	----------------------------------------------------------------- */	
	add_action('admin_menu', 'WooDecimalProduct_create_menu');	
	function WooDecimalProduct_create_menu () {	
		add_submenu_page (
			'edit.php?post_type=product',
			'Decimal Product Quantity for WooCommerce',
			__('Decimal Quantity', 'decimal_product_quantity_for_woocommerce'),
			'manage_woocommerce',
			'decimal-product-quantity-for-woocommerce/includes/options.php',
			''
		);		
	}