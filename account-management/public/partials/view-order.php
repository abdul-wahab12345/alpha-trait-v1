<?php
/**
* View Order
*
* Shows the details of a particular order on the account page.
*
* This template can be overridden by copying it to yourtheme/woocommerce/myaccount/view-order.php.
*
* HOWEVER, on occasion WooCommerce will need to update template files and you
* (the theme developer) will need to copy the new files to your theme to
* maintain compatibility. We try to do this as little as possible, but it does
* happen. When this occurs the version of the template file will be bumped and
* the readme will list any important changes.
*
* @see     https://docs.woocommerce.com/document/template-structure/
* @package WooCommerce\Templates
* @version 3.0.0
*/

defined( 'ABSPATH' ) || exit;

$notes = $order->notes;

$date = new DateTime($order->date->date);

$date = $date->format('F d,Y');


?>
<p>
	<?php
	printf(
		/* translators: 1: order number 2: order date 3: order status */
		esc_html__( 'Order #%1$s was placed on %2$s and is currently %3$s.', 'woocommerce' ),
		'<mark class="order-number">' . $order->ID . '</mark>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'<mark class="order-date">' . $date . '</mark>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'<mark class="order-status">' . wc_get_order_status_name( $order->status ) . '</mark>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
	?>
</p>

<?php if ( $notes ) : ?>
	<h2><?php esc_html_e( 'Order updates', 'woocommerce' ); ?></h2>
	<ol class="woocommerce-OrderUpdates commentlist notes">
		<?php foreach ( $notes as $note ) : ?>
			<li class="woocommerce-OrderUpdate comment note">
				<div class="woocommerce-OrderUpdate-inner comment_container">
					<div class="woocommerce-OrderUpdate-text comment-text">
						<p class="woocommerce-OrderUpdate-meta meta"><?php echo date_i18n( esc_html__( 'l jS \o\f F Y, h:ia', 'woocommerce' ), strtotime( $note->comment_date ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<div class="woocommerce-OrderUpdate-description description">
							<?php echo wpautop( wptexturize( $note->comment_content ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>
<?php endif; ?>


<section class="woocommerce-order-details">

	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h2>

	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">

		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php

			foreach ( $order->items as $item_id => $item ) {


				?>

				<tr class="">

					<td class="woocommerce-table__product-name product-name">
						<?php

						echo  $item->name;

						echo ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', $item->quantity ) . '</strong>';

						?>
					</td>

					<td class="woocommerce-table__product-total product-total">
						<?php echo wc_price($item->subtotal ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>

				</tr>

				<?php


			}


			?>
		</tbody>

		<tfoot>

			<tr>
				<th scope="row">Payment Method</th>
				<td><?= $order->payment_title;?></td>
			</tr>

			<tr>
				<th scope="row">Total</th>
				<td><?= wc_price($order->total);?></td>
			</tr>


		</tfoot>
	</table>


</section>

<section class="woocommerce-customer-details">

	<?php if ( $show_shipping ) : ?>

		<section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">
			<div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">

			<?php endif; ?>

			<h2 class="woocommerce-column__title"><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></h2>

			<address>
				<?php echo $order->address; ?>

			</address>
