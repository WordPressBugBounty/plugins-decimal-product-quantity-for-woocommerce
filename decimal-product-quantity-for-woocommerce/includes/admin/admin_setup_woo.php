<?php
/*
 * Decimal Product Quantity for WooCommerce
 * Admin WooCommerce Setup Page.
 * admin_setup_woo.php
 */

	/* Инициализация.
     * Запускаем самым последним, чтобы быть уверенным, что WooCommerce уже инициализировался.
	 * decimal-product-quantity-for-woocommerce.php	-> WooDecimalProduct_Init ()
	----------------------------------------------------------------- */        
	function WooDecimalProduct_Woo_remove_filters(){
        if (class_exists ('WooCommerce')){
            // Разрешаем использование дробного количества изменения Товара
            remove_filter ('woocommerce_stock_amount', 'intval');
            add_filter ('woocommerce_stock_amount', 'floatval');
        } 	
    } 

	/* DashBoard. Products Menu. Create plugin SubMenu
	----------------------------------------------------------------- */	
	add_action('admin_menu', 'WooDecimalProduct_Action_create_menu');	
	function WooDecimalProduct_Action_create_menu () {	
		add_submenu_page (
			'edit.php?post_type=product',
			'Decimal Product Quantity for WooCommerce',
			__('Decimal Quantity', 'decimal-product-quantity-for-woocommerce'),
			'manage_woocommerce',
			'decimal-product-quantity-for-woocommerce/includes/admin/options.php',
			''
		);		
	}
	
	/* DashBoard. WooCommerce. Page: Status.
	 * Добавляем блок Системной Информации.
	----------------------------------------------------------------- */	
	add_action('woocommerce_system_status_report', 'WooDecimalProduct_Action_system_status_report');
	function WooDecimalProduct_Action_system_status_report () {
		$WDPQ_PHP_SESSION_NONE 		= 'N/A';
		$WDPQ_PHP_SESSION_ACTIVE 	= 'N/A';
		
		if ( defined( 'PHP_SESSION_NONE' ) ) {
			$WDPQ_PHP_SESSION_NONE = PHP_SESSION_NONE;
		}
		
		if ( defined( 'PHP_SESSION_ACTIVE' ) ) {
			$WDPQ_PHP_SESSION_ACTIVE = PHP_SESSION_ACTIVE;
		}		
		
		ob_start();
		?>
			<table class="wc_status_table widefat" cellspacing="0">
				<thead>
					<tr>
						<th colspan="3" data-export-label="Status report information"><h2>WPGear Info<?php echo wc_help_tip( esc_html__( 'This section shows information about Ext. Data.', 'decimal-product-quantity-for-woocommerce' ) ); ?></h2></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td data-export-label="Generated at">Session: 'PHP_SESSION_NONE'</td>
						<td class="help"><?php echo wc_help_tip( esc_html__( 'Displays is defined constant Session. For WPGear support.', 'decimal-product-quantity-for-woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
						<td><?php echo $WDPQ_PHP_SESSION_NONE; ?></td>
					</tr>
					
					<tr>
						<td data-export-label="Generated at">Session 'PHP_SESSION_ACTIVE'</td>
						<td class="help"><?php echo wc_help_tip( esc_html__( 'Displays is defined constant Session. For WPGear support.', 'decimal-product-quantity-for-woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
						<td><?php echo $WDPQ_PHP_SESSION_ACTIVE; ?></td>
					</tr>					
				</tbody>
			</table>		
		<?php

		$contents = ob_get_contents();
		ob_end_clean();
		echo $contents; // phpcs:ignore 				
	}