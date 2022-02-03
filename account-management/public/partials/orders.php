

<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
	<thead>
		<tr>
			<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
			<?php endforeach; ?>
		</tr>
	</thead>

	<tbody>
		<?php


		foreach ( $orders as $customer_order ) {
			$item_count = count($customer_order->items);

			$view = get_permalink( get_option('woocommerce_myaccount_page_id') ) . 'aw-order?id=' . $customer_order->ID . '&site=' . $url;

			?>
			<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $customer_order->status ); ?> order">
				<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
					<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
						<?php if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) : ?>


						<?php elseif ( 'order-number' === $column_id ) : ?>
							<a href="<?php echo esc_url( $view ); ?>">
								<?php echo esc_html( _x( '#', 'hash before order number', 'woocommerce' ) . $customer_order->ID ); ?>
							</a>

						<?php elseif ( 'order-date' === $column_id ) : ?>
							<time> <?php



							$date = new DateTime($customer_order->date->date);

							echo  $date->format('F d,Y');


							?></time>

						<?php elseif ( 'order-status' === $column_id ) : ?>
							<?php echo esc_html( wc_get_order_status_name( $customer_order->status ) ); ?>

						<?php elseif ( 'order-total' === $column_id ) : ?>
							<?php
							/* translators: 1: formatted order total 2: total order items */
							echo wp_kses_post( sprintf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'woocommerce' ), wc_price($customer_order->total), $item_count ) );
							?>

						<?php elseif ( 'order-actions' === $column_id ) : ?>
							<?php
							//WordPress.WP.GlobalVariablesOverride.Prohibited
							echo '<a href="' . esc_url( $view  ) . '" class="woocommerce-button button ' . sanitize_html_class( 'view' ) . '">View</a>';

							?>
						<?php endif; ?>
					</td>
				<?php endforeach; ?>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>
