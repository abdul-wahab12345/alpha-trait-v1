<?php
/**
* My Subscriptions section on the My Account page
*
* @author   Prospress
* @category WooCommerce Subscriptions/Templates
* @version  2.6.4
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}


?>
<div class="woocommerce_account_subscriptions">

  <?php if ( ! empty( $subscriptions ) ) : ?>





    <?php if(!wp_is_mobile()){?>
      <table class="my_account_subscriptions my_account_orders woocommerce-orders-table woocommerce-MyAccount-subscriptions shop_table shop_table_responsive woocommerce-orders-table--subscriptions">

        <thead>
          <tr>
            <th class="subscription-id order-number woocommerce-orders-table__header woocommerce-orders-table__header-order-number woocommerce-orders-table__header-subscription-id"><span class="nobr"><?php esc_html_e( 'Image', 'woocommerce-subscriptions' ); ?></span></th>
            <th class="subscription-id order-number woocommerce-orders-table__header woocommerce-orders-table__header-order-number woocommerce-orders-table__header-subscription-id"><span class="nobr"><?php esc_html_e( 'Subscription', 'woocommerce-subscriptions' ); ?></span></th>
            <th class="subscription-status order-status woocommerce-orders-table__header woocommerce-orders-table__header-order-status woocommerce-orders-table__header-subscription-status"><span class="nobr"><?php esc_html_e( 'Status', 'woocommerce-subscriptions' ); ?></span></th>
            <th class="subscription-next-payment order-date woocommerce-orders-table__header woocommerce-orders-table__header-order-date woocommerce-orders-table__header-subscription-next-payment"><span class="nobr"><?php echo esc_html_x( 'Next payment', 'table heading', 'woocommerce-subscriptions' ); ?></span></th>
            <th class="subscription-total order-total woocommerce-orders-table__header woocommerce-orders-table__header-order-total woocommerce-orders-table__header-subscription-total"><span class="nobr"><?php echo esc_html_x( 'Total', 'table heading', 'woocommerce-subscriptions' ); ?></span></th>
            <th class="subscription-actions order-actions woocommerce-orders-table__header woocommerce-orders-table__header-order-actions woocommerce-orders-table__header-subscription-actions">&nbsp;</th>
          </tr>
        </thead>

        <tbody>
          <?php /** @var WC_Subscription $subscription */ ?>
          <?php foreach ( $subscriptions as $subscription_id => $subscription ) :


            $name = [];
            $aw_product_id = 0;




            foreach ( $subscription->get_items() as $item_id => $item ) {
              $product_id = $item->get_product_id();
              $variation_id = $item->get_variation_id();
              if($aw_product_id == 0){
                $aw_product_id = $product_id;
              }

              $product = $item->get_product();
              $product_name = $item->get_name();

              $name[] = $product_name;

              $quantity = $item->get_quantity();
              $subtotal = $item->get_subtotal();
              $total = $item->get_total();
            }

            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), array( 100, 100)  );

            ?>


            <tr class="order woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $subscription->get_status() ); ?>">

              <td class="subscription-id order-number woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-id woocommerce-orders-table__cell-order-number" data-title="<?php esc_attr_e( 'ID', 'woocommerce-subscriptions' ); ?>">
                <img src="<?php  echo $image[0]; ?>" data-id="<?php echo $product_id; ?>">
              </td>

              <td class="subscription-id order-number woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-id woocommerce-orders-table__cell-order-number" data-title="<?php esc_attr_e( 'ID', 'woocommerce-subscriptions' ); ?>">
                <a href="<?php echo esc_url( $subscription->get_view_order_url() ); ?>">
                  <?php echo implode("<br>",$name); ?>
                </a>
                <?php do_action( 'woocommerce_my_subscriptions_after_subscription_id', $subscription ); ?>
              </td>
              <td class="subscription-status order-status woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-status woocommerce-orders-table__cell-order-status" data-title="<?php esc_attr_e( 'Status', 'woocommerce-subscriptions' ); ?>">
                <?php echo esc_attr( wcs_get_subscription_status_name( $subscription->get_status() ) == "On hold"?"In active": wcs_get_subscription_status_name( $subscription->get_status() )); ?>
              </td>
              <td class="subscription-next-payment order-date woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-next-payment woocommerce-orders-table__cell-order-date" data-title="<?php echo esc_attr_x( 'Next Payment', 'table heading', 'woocommerce-subscriptions' ); ?>">
                <?php echo esc_attr( $subscription->get_date_to_display( 'next_payment' ) ); ?>
                <?php if ( ! $subscription->is_manual() && $subscription->has_status( 'active' ) && $subscription->get_time( 'next_payment' ) > 0 ) : ?>
                  <br/><small><?php echo esc_attr( $subscription->get_payment_method_to_display( 'customer' ) ); ?></small>
                <?php endif; ?>
              </td>
              <td class="subscription-total order-total woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-total woocommerce-orders-table__cell-order-total" data-title="<?php echo esc_attr_x( 'Total', 'Used in data attribute. Escaped', 'woocommerce-subscriptions' ); ?>">
                <?php echo wp_kses_post( $subscription->get_formatted_order_total() ); ?>
              </td>
              <td class="subscription-actions order-actions woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-actions woocommerce-orders-table__cell-order-actions">

                <?php if($subscription->has_status( 'active' )){?>
                  <div class="dropdown">
                    <a href="javascript:" data-url="<?php echo esc_url( $subscription->get_view_order_url() ) ?>" data-id="<?= $subscription->get_id();?>" class="dropbtn woocommerce-button button view">
                      <i class="fas fa-ellipsis-h"></i>
                    </a>
                    <div class="dropdown-content">
                      <a nohref class=" view aw-sub-pause" data-id="<?= $subscription->get_id();?>">Pause</a>

                    </div>
                  </div>

                  <!-- <a nohref class="woocommerce-button button view aw-sub-pause" data-id="<?= $subscription->get_id();?>">Pause</a> -->

                <?php }?>

                <?php if($subscription->has_status( 'on-hold' )){

                  $aw_actions = wcs_get_all_user_actions_for_subscription( $subscription, get_current_user_id() );

                  if ( ! empty( $aw_actions ) ) :
                    foreach ( $aw_actions as $key => $action ) :
                      if($key == "reactivate"){
                        ?>
                        <a href="<?php echo esc_url( $action['url'] ); ?>" class="woocommerce-button button reactivate view aw-sub-reactivate" data-id="<?= $subscription->get_id();?>"><?php echo esc_html( $action['name'] ); ?></a>

                      <?php }
                    endforeach;
                  endif;
                }
                ?>

                <?php do_action( 'woocommerce_my_subscriptions_actions', $subscription ); ?>
              </td>
            </tr>
          <?php endforeach; ?>


        </tbody>

      </table>



    <?php }else{
      include plugin_dir_path(__FILE__) . "mobile-subscriptions.php";
      ?>



      <?php


    }?>


    <?php if ( 1 < $max_num_pages ) : ?>
      <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
        <?php if ( 1 !== $current_page ) : ?>
          <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'subscriptions', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce-subscriptions' ); ?></a>
        <?php endif; ?>

        <?php if ( intval( $max_num_pages ) !== $current_page ) : ?>
          <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'subscriptions', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce-subscriptions' ); ?></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php else : ?>
    <p class="no_subscriptions woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
      <?php if ( 1 < $current_page ) :
        printf( esc_html__( 'You have reached the end of subscriptions. Go to the %sfirst page%s.', 'woocommerce-subscriptions' ), '<a href="' . esc_url( wc_get_endpoint_url( 'subscriptions', 1 ) ) . '">', '</a>' );
        else :
          esc_html_e( 'You have no active subscriptions.', 'woocommerce-subscriptions' );
          ?>
          <a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
            <?php esc_html_e( 'Browse products', 'woocommerce-subscriptions' ); ?>
          </a>
          <?php
        endif; ?>
      </p>

    <?php endif; ?>

  </div>

  <script>

  var $ = jQuery;

  $(document).ready(function(){


    $('.aw-sub-pause').click(function(){
      console.log($(this).data('id'));
      $('#myModal1 #aw_s_id').val($(this).data('id'));
      $("#myModal1 input[name='nourl']").val("false");
      $('#myModal1').show();
    });

    $('.aw-sub-reactivate').click(function(e){
      e.preventDefault();
      var url = $('.aw-sub-reactivate').attr('href');
      //
      url +=  "&nourl=false";

      window.location.href = url;

    });

  });

</script>

<style>

<style>
.dropbtn {
  background-color: #04AA6D;
  color: white;
  padding: 16px;
  font-size: 16px;
  border: none;
}

.dropdown {
  position: relative;
  display: inline-block;
}

.dropdown-content {
  display: none;
  position: absolute;
  background-color: #f1f1f1;
  min-width: 160px;
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  z-index: 1;
}

.dropdown-content a {
  color: black;
  cursor: pointer;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
}

.dropdown-content a:hover {background-color: #ddd;}

.dropdown:hover .dropdown-content {display: block;}

</style>

</style>
