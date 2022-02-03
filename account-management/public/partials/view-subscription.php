<?php
/**
* Subscription details table
*
* @author  Prospress
* @package WooCommerce_Subscription/Templates
* @since 2.2.19
* @version 2.6.5
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $wp;

$aw_suspend = home_url($wp->request) . "?id=$subscription->ID&site={$url}&aw_change_status=true&status=on-hold";
$reactive_url = home_url($wp->request) . "?id=$subscription->ID&site={$url}&aw_change_status=true&status=active";

$user = get_userdata(get_current_user_id());

?>
<table class="shop_table subscription_details">
	<tbody>
		<tr>
			<td><?php esc_html_e( 'Status', 'woocommerce-subscriptions' ); ?></td>
			<td><?php echo esc_html( wcs_get_subscription_status_name( $subscription->status ) ); ?></td>
		</tr>



		<?php




		foreach ( $subscription->dates_to_display as $date ) : ?>

		<tr>
			<td><?php echo esc_html( $date->title ); ?></td>
			<td><?php echo esc_html( $date->date ); ?></td>
		</tr>

	<?php endforeach; ?>


	<?php if ( $subscription->can_user_toggle_auto_renewal ) : ?>
		<tr>
			<td><?php esc_html_e( 'Auto renew', 'woocommerce-subscriptions' ); ?></td>
			<td>
				<div class="wcs-auto-renew-toggle">

					<a href="#" class="<?php echo esc_attr( implode( ' ' , $subscription->toggle_classes ) ); ?>" aria-label="<?php echo esc_attr( $subscription->toggle_label ) ?>"><i class="subscription-auto-renew-toggle__i" aria-hidden="true"></i></a>
					<?php if ( $subscription->is_duplicate_site() ) : ?>
						<small class="subscription-auto-renew-toggle-disabled-note"><?php echo esc_html__( 'Using the auto-renewal toggle is disabled while in staging mode.', 'woocommerce-subscriptions' ); ?></small>
					<?php endif; ?>
				</div>
			</td>
		</tr>
	<?php endif; ?>




	<?php if ( $subscription->next_payment_action > 0 ) : ?>
		<tr>
			<td><?php esc_html_e( 'Payment', 'woocommerce-subscriptions' ); ?></td>
			<td>
				<span data-is_manual="<?php echo esc_attr(  $subscription->is_manual ); ?>" class="subscription-payment-method"><?php echo esc_html( $subscription->get_payment_method_to_display ); ?></span>
			</td>
		</tr>
	<?php endif; ?>



	<?php


	if($subscription->status == "active"){


		?>

		<tr>
			<td><?php esc_html_e( 'Actions', 'woocommerce-subscriptions' ); ?></td>
			<td>

				<a href="javascript:" id="myBtn1" data-href="<?= $aw_suspend;?>" class="button cancel">Pause</a>

				<?php


				if($subscription->actions->subscription_renewal_early){
					$renew = $subscription->actions->subscription_renewal_early;

					$new_url = $url . "?action=aw_renew&email={$user->user_email}&url={$renew->url}";

					?>

					<a href="<?= $new_url;?>" target="_blank"  class="button reactive"><?= $renew->name;?></a>

					<?php
				}


				?>

			</td>
		</tr>

		<?php
	}elseif($subscription->status == "pending-cancel" || $subscription->status == "on-hold"){


		?>

		<tr>
			<td><?php esc_html_e( 'Actions', 'woocommerce-subscriptions' ); ?></td>
			<td>

				<a href="<?= $reactive_url;?>" class="button reactive">Reactive</a>

			</td>
		</tr>

		<?php

	}

	?>


</tbody>
</table>

<?php if ( $notes = $subscription->notes ) :  var_dump($notes);?>
	<h2><?php esc_html_e( 'Subscription updates', 'woocommerce-subscriptions' ); ?></h2>
	<ol class="woocommerce-OrderUpdates commentlist notes">
		<?php foreach ( $notes as $note ) : ?>
			<li class="woocommerce-OrderUpdate comment note">
				<div class="woocommerce-OrderUpdate-inner comment_container">
					<div class="woocommerce-OrderUpdate-text comment-text">
						<p class="woocommerce-OrderUpdate-meta meta"><?php echo esc_html( date_i18n( _x( 'l jS \o\f F Y, h:ia', 'date on subscription updates list. Will be localized', 'woocommerce-subscriptions' ), wcs_date_to_time( $note->comment_date ) ) ); ?></p>
						<div class="woocommerce-OrderUpdate-description description">
							<?php echo wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ); ?>
						</div>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>
<?php endif; ?>



<table class="shop_table order_details">
	<thead>
		<tr>
			<th class="product-name"><?php echo esc_html_x( 'Product', 'table headings in notification email', 'woocommerce-subscriptions' ); ?></th>
			<th class="product-total"><?php echo esc_html_x( 'Total', 'table heading', 'woocommerce-subscriptions' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ( $subscription->items as $item ) {

			?>
			<tr>

				<td class="product-name">
					<?php
					echo $item->name; echo " x";
					echo $item->quantity;
					echo $item->meta;

					?>
				</td>
				<td class="product-total">
					<?php echo $item->total; ?>
				</td>
			</tr>
			<?php

			if ( $subscription->purchase_note) {
				?>
				<tr class="product-purchase-note">
					<td colspan="3"><?php echo $subscription->purchase_note; ?></td>
				</tr>
				<?php
			}
		}
		?>
	</tbody>
	<tfoot>

		<h2><?php esc_html_e( 'Subscription totals', 'woocommerce-subscriptions' ); ?></h2>
		<?php
		$totals = $subscription->totals;
		foreach ( $totals as $key => $total ) : ?>
		<tr>
			<th scope="row"><?php echo esc_html( $total->label ); ?></th>
			<td><?php echo wp_kses_post( $total->value); ?></td>
		</tr>
	<?php endforeach; ?>
</tfoot>
</table>



<header>
	<h2><?php esc_html_e( 'Related orders', 'woocommerce-subscriptions' ); ?></h2>
</header>


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


		foreach ( $subscription->orderss as $customer_order ) {
			//$order      = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
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




<style>
body {font-family: Arial, Helvetica, sans-serif;}

/* The Modal (background) */
.modal {
	display: none; /* Hidden by default */
	position: fixed; /* Stay in place */
	z-index: 999999; /* Sit on top */
	padding-top: 100px; /* Location of the box */
	left: 0;
	top: 0;
	width: 100%; /* Full width */
	height: 100%; /* Full height */
	overflow: auto; /* Enable scroll if needed */
	background-color: rgb(0,0,0); /* Fallback color */
	background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
	background-color: #fefefe;
	margin: auto;
	padding: 20px;
	border: 1px solid #888;
	width: 100%;
}

/* The Close Button */
.close {
	color: #aaaaaa;
	float: right;
	font-size: 28px;
	font-weight: bold;
}

.close:hover,
.close:focus {
	color: #000;
	text-decoration: none;
	cursor: pointer;
}
</style>


<!-- The Modal -->
<div id="myModal1" class="modal">

	<!-- Modal content -->
	<div class="modal-content">
		<span class="close1">&times;</span>
		<p>


			<form action="" method="post">


				<p>How long do you want to pause your subscription for?</p>


				<input type="radio" id="age1" name="period" value="1">
				<label for="age1">1 week</label><br>
				<input type="radio" id="age2" name="period" value="2">
				<label for="age2">2 weeks</label><br>
				<input type="radio" id="age3" name="period" value="4">
				<label for="age3">4 weeks</label><br>
				<input type="radio" id="age4" name="period" value="0">
				<label for="age4">Indefinitely</label><br>

				<label>Reason</label><br>
				<textarea name="reason" style="width:100%" rows="5"></textarea>

				<input type="hidden" name="id" value="<?= $subscription->ID;?>">
				<input type="hidden" name="url" value="<?= $url;?>">
				<input type="hidden" name="status" value="on-hold">

				<br>

				<input type="submit" name="aw_pause" value="Pause">

			</form>

		</p>
	</div>

</div>



<script>


var modal = document.getElementById("myModal1");

// Get the button that opens the modal
var btn = document.getElementById("myBtn1");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close1")[0];

// When the user clicks the button, open the modal
btn.onclick = function() {
	modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
	modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
	if (event.target == modal) {
		modal.style.display = "none";
	}
}
</script>
