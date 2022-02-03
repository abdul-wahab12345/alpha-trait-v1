<?php
/**
* The public-facing functionality of the plugin.
*
* @link       http://abdulwahab.live/
* @since      1.0.0
*
* @package    Account_Management
* @subpackage Account_Management/public
*/

/**
* The public-facing functionality of the plugin.
*
* Defines the plugin name, version, and two examples hooks for how to
* enqueue the public-facing stylesheet and JavaScript.
*
* @package    Account_Management
* @subpackage Account_Management/public
* @author     Abdul Wahab <rockingwahab9@gmail.com>
*/
class Account_Management_Public {

  /**
  * The ID of this plugin.
  *
  * @since    1.0.0
  * @access   private
  * @var      string    $plugin_name    The ID of this plugin.
  */
  private $plugin_name;

  /**
  * The version of this plugin.
  *
  * @since    1.0.0
  * @access   private
  * @var      string    $version    The current version of this plugin.
  */
  private $version;

  public $woo_clients;

  public $client_index;

  /**
  * Initialize the class and set its properties.
  *
  * @since    1.0.0
  * @param      string    $plugin_name       The name of the plugin.
  * @param      string    $version    The version of this plugin.
  */
  public function __construct( $plugin_name, $version ) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

    $this->woo_clients = [];
    $this->client_index = 0;


  }

  public function template_redirect(){
    global $wpdb,$wp;

    if(isset($_POST['save_account_details'])){

      $user = get_userdata(get_current_user_id());

      $first_name = $_POST['account_first_name'];
      $last_name = $_POST['account_last_name'];
      $display_name = $_POST['account_display_name'];

      foreach (get_field('sites','option') as $key => $value) {
        $url = $value['site_url'];

        $endpoint = "wp-json/aw/aw_save_account_details?email={$user->user_email}&first_name={$first_name}&last_name={$last_name}&display_name={$display_name}";


        $response = wp_remote_get($url . $endpoint);
      }

    }

    /*
    *Pause Subscription
    */

    if(isset($_POST['aw_pause'])){

      $period = $_POST['period'];
      $url = $_POST['url'];

      $id = $_POST['id'];
      $reason = $_POST['reason'];
      $status = $_POST['status'];

      if($period != 0){

        $period = 7 * $period;

        //  wp_schedule_single_event( time() + $period, 'aw_reactive_subscription', array( $id,$url,'active' ) );

      }else{
        $period = 365;
      }


      wp_remote_get($url . "wp-json/aw/aw_extend_subscription?id={$id}&days={$period}&reason={$reason}");
      wp_redirect( home_url($wp->request) . "?id={$id}&site={$url}");

    }

    if(isset($_POST['aw_pause_2gg'])){

      $weeks = $_POST['period'];
      $period = $_POST['period'];
      $id = $_POST['id'];
      $nourl = $_POST['nourl'];
      $status = $_POST['status'];
      $reason = $_POST['reason'];
      $want_meal = $_POST['want_meal'];
      $duration = $period;
      $current_time = current_time('timestamp');

      $aw_week_cutoff = get_option('aw_week_cutoff')?get_option('aw_week_cutoff'):"Wednesday";

      $cuttofftime = new Datetime(date("Y-m-d H:i:s",  $current_time));
      $cuttofftime->modify("Last {$aw_week_cutoff}");
      $cuttofftime->modify("+1 days");

      $next_day = new Datetime(date("Y-m-d H:i:s", $current_time) );
      $next_day->modify("Next {$aw_week_cutoff}");
      $next_day->setTime(23,59,59);

      $today = $next_day;

      if(date ('l',$current_time) == $aw_week_cutoff){
        $today = new Datetime(date("Y-m-d H:i:s",$current_time));
      }
      $subscription = wcs_get_subscription($id);
      $last_payment =$subscription->get_date('date_paid');
      //
      $last_payment = new Datetime($last_payment);

      if($period != 0){
        $period = 7 * $period;
        $weeks = "{$weeks} week time";
        //  wp_schedule_single_event( time() + $period, 'aw_reactive_subscription', array( $id,$url,'active' ) );
      }else{
        $weeks = "Indefinitely time";
        $period = 365;
      }

      //
      $aw_sms = "";
      $pause = "true";

      update_post_meta($id,'aw_next_delivery',"false");

      if($last_payment >= $cuttofftime && $last_payment <= $today ){
        if($want_meal == "no"){

          $aw_sms = "Kindly reach out to us to process your refund";
        }else{
          $aw_sms = "You will recieve meals on sunday.";
        }

        update_post_meta($id,'aw_reason',$reason);
        update_post_meta($id,'aw_pause_date',date("Y-m-d"));

        if($period != 365){
          $date = $subscription->get_date( 'next_payment' );
          $end_date = date('Y-m-d H:i:s',strtotime("+{$period} days", strtotime($date)));
          $dates_to_update = array();
          $dates_to_update['next_payment'] = $end_date;
          $subscription->update_dates($dates_to_update);

          if($want_meal != "no"){
            $aw_last_payment1 = $subscription->get_date('date_paid');
            $next_delivery = new Datetime($aw_last_payment1);
            $next_delivery->modify("Next Sunday");
            update_post_meta($id,'aw_next_delivery',$next_delivery->format("Y-m-d"));

          }

        }else{
          $subscription->update_status('on-hold');
        }
        $note = __("Subscriptions has been pased due to '{$reason}' for {$weeks}");
        $note = htmlspecialchars($note, ENT_QUOTES, 'UTF-8', false);
        $this->add_hubspot_note(get_current_user_id(),$note);
        $subscription->add_order_note( $note );

        $note = "Subscriptions has been pased for {$weeks}";
        if(!empty(  $aw_sms )){
          $note .= "<br>" .   $aw_sms;
        }


      }else{
        if($want_meal == "yes"){

          $dates_to_update = array();
          $dates_to_update['next_payment'] = date("Y-m-d H:i:s",strtotime(date() . " +1 seconds"));
          $subscription->update_dates($dates_to_update);

          $aw_last_payment1 = $subscription->get_date('date_paid');
          $next_delivery = new Datetime($aw_last_payment1);
          $next_delivery->modify("Next Sunday");
          update_post_meta($id,'aw_next_delivery',$next_delivery->format("Y-m-d"));

          wp_schedule_single_event( time() + 60, 'aw_pause_subscription', array( $id,$duration,$reason ) );
          $note = "Your subscription will pause after automatic payment. It may take an hour";

        }else{
          update_post_meta($id,'aw_reason',$reason);
          update_post_meta($id,'aw_pause_date',date("Y-m-d"));


          if($period != 365){
            $date = $subscription->get_date( 'next_payment' );
            $end_date = date('Y-m-d H:i:s',strtotime("+{$period} days", strtotime($date)));
            $dates_to_update = array();
            $dates_to_update['next_payment'] = $end_date;
            $subscription->update_dates($dates_to_update);
          }else{
            $subscription->update_status('on-hold');
          }
          $note = __("Subscriptions has been pased due to '{$reason}' for {$weeks}");
          $note = htmlspecialchars($note, ENT_QUOTES, 'UTF-8', false);
          $this->add_hubspot_note(get_current_user_id(),$note);
          $subscription->add_order_note( $note );
        }
      }


      $url = wc_get_endpoint_url('view-subscription/'.$id.'?woo-status='.urlencode($note));
      if($nourl == "false"){
        $url = wc_get_endpoint_url('subscriptions?woo-status='.urlencode($note));

      }
      wp_redirect( $url);
    }



    /*
    *Show success message
    */

    if(isset($_GET['woo-status'])){
      wc_add_notice( $_GET['woo-status'], 'success' );
    }

    /*
    *Cancel Subscription
    */

    if(isset($_GET['aw_change_status'])){
      $url = $_GET['site'];
      $id = $_GET['id'];
      $status = $_GET['status'];

      wp_remote_get($url . "wp-json/aw/aw_change_status?id={$id}&status={$status}");

      wp_redirect( home_url($wp->request) . "?id={$id}&site={$url}");
    }

    /*
    *Cancel Subscription
    */

    if(isset($_GET['action']) && $_GET['action'] == "aw_renew"){
      $url = $_GET['url'];
      $email = $_GET['email'];

      $user = get_user_by('email',$email);

      if($user){
        wp_clear_auth_cookie();
        wp_set_current_user ( $user->ID );
        wp_set_auth_cookie  ( $user->ID);
      }

      wp_redirect( $url);
    }

  }

  public function wp_loaded(){
    if(isset($_GET['change_subscription_to']) && $_GET['change_subscription_to'] == "active"){

      $id = $_GET['subscription_id'];

      delete_post_meta($id,'aw_pause_date');

      $aw_week_cutoff = get_option('aw_week_cutoff')?get_option('aw_week_cutoff'):"Wednesday";
      $current_time = current_time('timestamp');

      $cuttofftime = new Datetime(date("Y-m-d H:i:s",$current_time));
      $cuttofftime->modify("Next {$aw_week_cutoff}");
      $cuttofftime->setTime(23,59,59);


      $monday = new Datetime(date("Y-m-d H:i:s",$current_time));
      $monday->modify("Next {$aw_week_cutoff}");
      $monday->modify("Last Monday");
      $monday->setTime(00,00,00);



      $cuttofftime_end = new Datetime(date("Y-m-d H:i:s",$current_time));
      $cuttofftime_end->modify("Last {$aw_week_cutoff}");
      $cuttofftime_end->setTime(23, 59,59);

      $sunday = new Datetime(date("Y-m-d H:i:s",$current_time));
      $sunday->modify("Last {$aw_week_cutoff}");
      $sunday->modify("Next Sunday");
      $sunday->setTime(23, 59,59);

      if(date('l',$current_time) == $aw_week_cutoff){

        $cuttofftime = new Datetime(date("Y-m-d H:i:s",$current_time));
        $cuttofftime->setTime(23,59,59);

        $monday = new Datetime(date("Y-m-d H:i:s",$current_time));
        $monday->modify("Last Monday");
        $monday->setTime(00,00,00);


        $cuttofftime_end = new Datetime(date("Y-m-d H:i:s",$current_time) );
        $cuttofftime_end->setTime(23, 59,59);

        $sunday = new Datetime(date("Y-m-d H:i:s",$current_time));
        $sunday->modify("Next Sunday");
        $sunday->setTime(23, 59,59);

      }


      $today = new Datetime(date("Y-m-d H:i:s",$current_time));

      $subscription  = wcs_get_subscription($id);



      $nourl = isset($_GET['nourl'])?"false":'true';

      if ( isset( $_GET['change_subscription_to'], $_GET['subscription_id'], $_GET['_wpnonce'] ) && ! empty( $_GET['_wpnonce'] ) ) {
        $user_id      = get_current_user_id();
        $subscription = wcs_get_subscription( absint( $_GET['subscription_id'] ) );
        $new_status   = wc_clean( $_GET['change_subscription_to'] );

        if ( self::validate_request( $user_id, $subscription, $new_status, $_GET['_wpnonce'] ) ) {
          self::change_users_subscription( $subscription, $new_status );


          if($today >= $monday && $today <= $cuttofftime){
            $dates_to_update['next_payment'] = date("Y-m-d H:i:s",strtotime("+2 hours"));
            $subscription->update_dates($dates_to_update);
          }else{
            $tu = date("Y-m-d",StrToTime("Next Tuesday"));
            $dates_to_update['next_payment'] = date("Y-m-d H:i:s",StrToTime("Next Tuesday"));
            $subscription->update_dates($dates_to_update);
            wc_add_notice("You will receive your meals next Sunday because our cutoff time has passed. Your next payment date is next Tuesday {$tu}","success");
          }


          if($nourl == "false"){
            $url = wc_get_endpoint_url('my-account/subscriptions');
            wp_safe_redirect( $url );

          }else{


            wp_safe_redirect( $subscription->get_view_order_url() );
            exit;

          }

        }
      }

      exit;

    }
  }

  public static function validate_request( $user_id, $subscription, $new_status, $wpnonce = null ) {
    $subscription = ( ! is_object( $subscription ) ) ? wcs_get_subscription( $subscription ) : $subscription;

    if ( ! wcs_is_subscription( $subscription ) ) {
      wc_add_notice( __( 'That subscription does not exist. Please contact us if you need assistance.', 'woocommerce-subscriptions' ), 'error' );
      return false;

    } elseif ( isset( $wpnonce ) && wp_verify_nonce( $wpnonce, $subscription->get_id() . $subscription->get_status() ) === false ) {
      wc_add_notice( __( 'Security error. Please contact us if you need assistance.', 'woocommerce-subscriptions' ), 'error' );
      return false;

    } elseif ( ! user_can( $user_id, 'edit_shop_subscription_status', $subscription->get_id() ) ) {
      wc_add_notice( __( 'That doesn\'t appear to be one of your subscriptions.', 'woocommerce-subscriptions' ), 'error' );
      return false;

    } elseif ( ! $subscription->can_be_updated_to( $new_status ) ) {
      // translators: placeholder is subscription's new status, translated
      wc_add_notice( sprintf( __( 'That subscription can not be changed to %s. Please contact us if you need assistance.', 'woocommerce-subscriptions' ), wcs_get_subscription_status_name( $new_status ) ), 'error' );
      return false;
    }

    return true;
  }

  public static function change_users_subscription( $subscription, $new_status ) {
    $subscription = ( ! is_object( $subscription ) ) ? wcs_get_subscription( $subscription ) : $subscription;
    $changed = false;

    do_action( 'woocommerce_before_customer_changed_subscription_to_' . $new_status, $subscription );

    switch ( $new_status ) {
      case 'active':
      if ( ! $subscription->needs_payment() ) {
        $subscription->update_status( $new_status );
        $subscription->add_order_note( _x( 'Subscription reactivated by the subscriber from their account page.', 'order note left on subscription after user action', 'woocommerce-subscriptions' ) );
        wc_add_notice( _x( 'Your subscription has been reactivated.', 'Notice displayed to user confirming their action.', 'woocommerce-subscriptions' ), 'success' );
        $changed = true;
      } else {
        wc_add_notice( __( 'You can not reactivate that subscription until paying to renew it. Please contact us if you need assistance.', 'woocommerce-subscriptions' ), 'error' );
      }
      break;
      case 'on-hold':
      if ( wcs_can_user_put_subscription_on_hold( $subscription ) ) {
        $subscription->update_status( $new_status );
        $subscription->add_order_note( _x( 'Subscription put on hold by the subscriber from their account page.', 'order note left on subscription after user action', 'woocommerce-subscriptions' ) );
        wc_add_notice( _x( 'Your subscription has been put on hold.', 'Notice displayed to user confirming their action.', 'woocommerce-subscriptions' ), 'success' );
        $changed = true;
      } else {
        wc_add_notice( __( 'You can not suspend that subscription - the suspension limit has been reached. Please contact us if you need assistance.', 'woocommerce-subscriptions' ), 'error' );
      }
      break;
      case 'cancelled':
      $subscription->cancel_order();
      $subscription->add_order_note( _x( 'Subscription cancelled by the subscriber from their account page.', 'order note left on subscription after user action', 'woocommerce-subscriptions' ) );
      wc_add_notice( _x( 'Your subscription has been cancelled.', 'Notice displayed to user confirming their action.', 'woocommerce-subscriptions' ), 'success' );
      $changed = true;
      break;
    }

    if ( $changed ) {
      do_action( 'woocommerce_customer_changed_subscription_to_' . $new_status, $subscription );
    }
  }

  public function aw_pause_subscription($id,$period,$reason){
    $current_time = current_time('timestamp');
    $subscription = wcs_get_subscription($id);
    $last_payment =$subscription->get_date('date_paid');
    $last_payment = new Datetime($last_payment);
    $today = new Datetime(date("Y-m-d H:i:s",$current_time));
    $duration = $period;

    update_post_meta($id,'aw_pause_date',date("Y-m-d",$current_time));


    $aw_week_cutoff = get_option('aw_week_cutoff')?get_option('aw_week_cutoff'):"Wednesday";

    $cuttofftime_start = new Datetime(date("Y-m-d H:i:s",$current_time));
    $cuttofftime_start->modify("Last {$aw_week_cutoff} + 1 days");
    $cuttofftime_start->setTime(00,00,00);

    $cuttofftime_end = new Datetime(date("Y-m-d H:i:s",$current_time));
    $cuttofftime_end->modify("Next {$aw_week_cutoff}");
    $cuttofftime_end->setTime(23,59,59);

    $sunday = new Datetime(date("Y-m-d H:i:s",$current_time));
    $sunday->modify("Last {$aw_week_cutoff} + 1 days");

    $sunday->modify("Next Sunday");
    $sunday->setTime(23, 59,59);

    if(date('l',$current_time) == $aw_week_cutoff){

      $cuttofftime_start = new Datetime(date("Y-m-d H:i:s",$current_time));
      $cuttofftime_start->modify("+ 1 days");
      $cuttofftime_start->setTime(00,00,00);

      $cuttofftime_end = new Datetime(date("Y-m-d H:i:s",$current_time) );
      $cuttofftime_end->modify("Next {$aw_week_cutoff}");
      $cuttofftime_end->setTime(23,59,59);

      $sunday = new Datetime(date("Y-m-d H:i:s",$current_time) );
      $sunday->modify("Next Sunday");
      $sunday->setTime(23,59,59);
    }


    $today = new Datetime(date("Y-m-d H:i:s",$current_time));

    if($last_payment > $cuttofftime_start && $last_payment < $cuttofftime_end){

      $weeks  = "";
      if($period != 0){
        $period = 7 * $period;
        $weeks = "{$weeks} week time";

      }else{
        $weeks = "Indefinitely time";
        $period = 365;
      }

      update_post_meta($id,'aw_reason',$reason);

      if($period != 365){
        $date = $subscription->get_date( 'next_payment' );
        $end_date = date('Y-m-d H:i:s',strtotime("+{$period} days", strtotime($date)));
        $dates_to_update = array();
        $dates_to_update['next_payment'] = $end_date;
        $subscription->update_dates($dates_to_update);
      }else{
        $subscription->update_status('on-hold');
      }
      $note = __("Subscriptions has been pased due to '{$reason}' for {$weeks}");
      $note = htmlspecialchars($note, ENT_QUOTES, 'UTF-8', false);
      $this->add_hubspot_note(get_current_user_id(),$note);
      $subscription->add_order_note( $note );


    }else{
      wp_schedule_single_event($current_time + 120, 'aw_pause_subscription', array( $id,$duration,$reason ) );
    }
  }

  public function add_hubspot_note($user_id,$note){

    $hub_user_id = get_user_meta($user_id,'hubwoo_user_vid',true);
    $aw_hubspotapikey = get_option('aw_hubspotapikey');

    if(!$user_id || !$aw_hubspotapikey){
      return;
    }

    $headers = array(
      "Content-Type:application/json",
    );

    $time = round(microtime(1) * 1000);

    $fields = '{
      "engagement": {
        "active": true,
        "type": "NOTE",
        "timestamp": '.$time.'
      },
      "associations": {
        "contactIds": ['.$hub_user_id.']
      },
      "metadata": {
        "body": "'.$note.'"
      }
    }';




    $url = "https://api.hubapi.com/engagements/v1/engagements?hapikey={$aw_hubspotapikey}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    //  var_dump($response);
  }

  public function wp_authenticate($user_login, $user_password){


    $user = wp_authenticate( $user_login, $user_password );

    if ( is_wp_error( $user ) ) {

    }

  }

  public function footer(){


    ?>

    <script>


    var $ = jQuery;

    $(document).ready(function(){

      $('.woocommerce-form-login').submit(function(e){
        e.preventDefault();

        var data = {
          action : 'aw_login',
          username : $('#username').val(),
          pass : $('#password').val()
        }

        var button = $('.woocommerce-form-login button[type="submit"]').text();
        $('#aw-error').remove();
        $('.woocommerce-form-login button[type="submit"]').text("Processing");

        $.post("<?= admin_url('admin-ajax.php');?>",data,function(res){

          $('.woocommerce-form-login button[type="submit"]').text(button);
          console.log(res);

          var response = JSON.parse(res);

          if(response.status == "success"){
            window.location = window.location.href;
          }else{



            $('.woocommerce-form-login').prepend(`<div id="aw-error" class="woocommerce-notices-wrapper"><ul class="woocommerce-error" role="alert">
            <li>
            ${response.sms}     </li>
            </ul>
            </div>`);
          }

        });

      });

    });

    </script>

    <?php

  }

  public function aw_login(){

    $username = $_POST['username'];
    $pass = $_POST['pass'];

    $creds = array(
      'user_login'    => $username,
      'user_password' => $pass,
      'remember'      => false
    );

    $user = wp_signon( $creds, false );

    if(!is_wp_error( $user )){

      echo json_encode(['status' => 'success', 'sms' => $user]);

    }else{

      $user = $this->call_api_login($username,$pass);


      if($user){
        $data = json_decode($user['body'],true);
        $userdata = array(
          'user_pass'             => $pass,   //(string) The plain-text user password.
          'user_login'            => $username,   //(string) The user's login username.
          'user_email'            => $data['data']['user_email'],   //(string) The user email address.

        );

        $id = wp_insert_user( $userdata );

        if($id){

          wp_clear_auth_cookie();
          wp_set_current_user ( $id );
          wp_set_auth_cookie  ( $id);

          add_user_meta($id,'aw_link',['id' => $data['ID'], 'url' => $user['url']]);

          echo json_encode(['status' => 'success', 'sms' => json_decode($user['body'])]);

        }else{

          echo json_encode(['status' => 'failed', 'sms' => 'unknown error']);

        }

      }

    }

    wp_die();
  }


  public function call_api_login( $username , $pass ){
    $sites = get_field('sites','option');
    $aw_body = false;

    if (count($sites) > $this->client_index) {

      $pass = urlencode($pass);
      $response = wp_remote_get( $sites[ $this->client_index ]['site_url'] . "wp-json/aw/login?username={$username}&pass={$pass}");

      if ( is_array( $response ) && ! is_wp_error( $response ) ) {
        $headers = $response['headers']; // array of http header lines
        $body    = $response['body']; // use the content

        if (!$body || $body == "false") {
          $aw_body = $this->call_api_login($username,$pass);
        }else{
          $aw_body = ['url' => $sites[ $this->client_index ]['site_url'],'body' => $body];
        }

        $this->client_index = $this->client_index + 1;

      }
    }

    return $aw_body;
  }

  public function register_api_hooks()
  {
    register_rest_route(
      'aw', '/login/',
      array(
        'methods'  => 'GET',
        'callback' => array($this,'login'),
      )
    );


    register_rest_route(
      'aw', '/get_orders/',
      array(
        'methods'  => 'GET',
        'callback' => array($this,'get_orders'),
      )
    );

    register_rest_route(
      'aw', '/get_subscriptions/',
      array(
        'methods'  => 'GET',
        'callback' => array($this,'get_subscriptions'),
      )
    );

    register_rest_route(
      'aw', '/get_order/',
      array(
        'methods'  => 'GET',
        'callback' => array($this,'get_order'),
      )
    );

    register_rest_route(
      'aw', '/get_subscription/',
      array(
        'methods'  => 'GET',
        'callback' => array($this,'get_subscription'),
      )
    );

    register_rest_route(
      'aw', '/aw_change_status/',
      array(
        'methods'  => 'GET',
        'callback' => array($this,'aw_change_status'),
      )
    );

    register_rest_route(
      'aw', '/aw_save_account_details/',
      array(
        'methods'  => 'GET',
        'callback' => array($this,'aw_save_account_details'),
      )
    );

    register_rest_route(
      'aw', '/aw_extend_subscription/',
      array(
        'methods'  => 'GET',
        'callback' => array($this,'aw_extend_subscription'),
      )
    );


  }

  public function aw_save_account_details(){

    $first_name = $_GET['first_name'];
    $last_name = $_GET['last_name'];
    $display_name = $_GET['display_name'];
    $email = $_GET['email'];

    $user = get_user_by('email',$email);

    if($user){

      wp_update_user( array( 'ID' => $user->ID, 'display_name' => $display_name ) );

      update_user_meta($user->ID,'first_name',$first_name);
      update_user_meta($user->ID,'last_name',$last_name);
      update_user_meta($user->ID,'nickname',$display_name);

    }


  }

  public function get_order(){


    $order = [];
    $ordr = wc_get_order($_GET['id']);
    $items = [];

    $order['ID'] = $ordr->get_id();
    $order['total'] = $ordr->get_total();
    $order['status'] = $ordr->get_status();
    $order['url'] = $ordr->get_checkout_order_received_url();
    $order['payment'] = $ordr->get_payment_method();
    $order['payment_title'] = $ordr->get_payment_method_title();
    $order['date'] = $ordr->get_date_created();
    $order['notes'] = $ordr->get_customer_order_notes();
    $order['address'] = $ordr->get_formatted_billing_address( esc_html__( 'N/A', 'woocommerce' ) );

    foreach ( $ordr->get_items() as $item_id => $item ) {
      $aw_item = [];

      $aw_item['product_id'] = $item->get_product_id();
      $aw_item['variation_id'] = $item->get_variation_id();

      $aw_item['name'] = $item->get_name();
      $aw_item['quantity'] = $item->get_quantity();
      $aw_item['subtotal'] = $item->get_subtotal();
      $aw_item['total'] = $item->get_total();
      $aw_item['tax'] = $item->get_subtotal_tax();

      $items[] = $aw_item;
    }
    $order['items'] = $items;


    return $order;

  }

  public function login(){


    $user = $_GET['username'];
    $pass = $_GET['pass'];

    $creds = array(
      'user_login'    => $user,
      'user_password' => $pass,
      'remember'      => true
    );

    $user = wp_signon( $creds, false );

    if ( is_wp_error( $user ) ) {
      return false;
    }

    return $user;

  }


  public function get_orders(){

    $email = $_GET['email'];

    $user = get_user_by('email',$email);

    if($user){

      $args = array(
        'customer_id' => $user->ID
      );
      $orders = wc_get_orders($args);


      $customer_orders = get_posts( array(
        'numberposts' => -1,
        'meta_key'    => '_customer_user',
        'meta_value'  => $user->ID,
        'post_type'   => wc_get_order_types(),
        'post_status' => array_keys( wc_get_order_statuses() ),
      ) );

      $orders = [];

      foreach ($customer_orders as $key => $value) {
        $order = [];
        $ordr = wc_get_order($value->ID);
        $items = [];

        $order['ID'] = $ordr->get_id();
        $order['total'] = $ordr->get_total();
        $order['status'] = $ordr->get_status();
        $order['url'] = $ordr->get_checkout_order_received_url();
        $order['payment'] = $ordr->get_payment_method();
        $order['payment_title'] = $ordr->get_payment_method_title();
        $order['date'] = $ordr->get_date_created();
        $order['notes'] = $ordr->get_customer_order_notes();


        foreach ( $ordr->get_items() as $item_id => $item ) {
          $aw_item = [];

          $aw_item['product_id'] = $item->get_product_id();
          $aw_item['variation_id'] = $item->get_variation_id();

          $aw_item['name'] = $item->get_name();
          $aw_item['quantity'] = $item->get_quantity();
          $aw_item['subtotal'] = $item->get_subtotal();
          $aw_item['total'] = $item->get_total();
          $aw_item['tax'] = $item->get_subtotal_tax();

          $items[] = $aw_item;
        }
        $order['items'] = $items;


        $orders[] =$order;
      }


      return $orders;

    }else{
      return false;
    }


  }


  public function get_subscriptions(){

    $email = $_GET['email'];

    $user = get_user_by('email',$email);

    if($user){

      $subscriptions = wcs_get_users_subscriptions($user->ID);
      $subscriptions_s = [];
      foreach ($subscriptions as $key => $subscription) {
        $single = [];

        $single['ID'] = $subscription->get_id();
        $single['status'] = $subscription->get_status();
        $single['next_payment'] = $subscription->get_date_to_display( 'next_payment' );

        if ( ! $subscription->is_manual() && $subscription->has_status( 'active' ) && $subscription->get_time( 'next_payment' ) > 0 ){
          $single['manual'] =$subscription->get_payment_method_to_display( 'customer' );
        }else{
          $single['manual'] = '';
        }

        $single['total'] = $subscription->get_formatted_order_total();

        $subscriptions_s[] = $single;
      }

      return $subscriptions_s;

    }else{
      return false;
    }

  }

  public function get_subscription(){

    $id = $_GET['id'];
    $subscription = wcs_get_subscription($id);
    $subscriptions_s = [];

    $single = [];

    $single['ID'] = $subscription->get_id();
    $single['status'] = $subscription->get_status();
    $single['next_payment'] = $subscription->get_date_to_display( 'next_payment' );

    if ( ! $subscription->is_manual() && $subscription->has_status( 'active' ) && $subscription->get_time( 'next_payment' ) > 0 ){
      $single['manual'] =$subscription->get_payment_method_to_display( 'customer' );
    }else{
      $single['manual'] = '';
    }

    $single['total'] = $subscription->get_formatted_order_total();

    $dates_to_display = apply_filters( 'wcs_subscription_details_table_dates_to_display', array(
      'start_date'              => _x( 'Start date', 'customer subscription table header', 'woocommerce-subscriptions' ),
      'last_order_date_created' => _x( 'Last order date', 'customer subscription table header', 'woocommerce-subscriptions' ),
      'next_payment'            => _x( 'Next payment date', 'customer subscription table header', 'woocommerce-subscriptions' ),
      'end'                     => _x( 'End date', 'customer subscription table header', 'woocommerce-subscriptions' ),
      'trial_end'               => _x( 'Trial end date', 'customer subscription table header', 'woocommerce-subscriptions' ),
    ), $subscription );

    $dates = [];
    foreach ( $dates_to_display as $date_type => $date_title ) :
      $date = $subscription->get_date( $date_type );
      if ( ! empty( $date ) ) :


        $d = [];
        $d['title'] = $date_title;
        $d['date'] = $subscription->get_date_to_display( $date_type );


        $dates[] = $d;
      endif;
    endforeach;

    $single['dates_to_display'] = $dates;
    $single['toggle_label'] = '';
    $single['toggle_classes'] = '';
    $single['can_user_toggle_auto_renewal'] = WCS_My_Account_Auto_Renew_Toggle::can_user_toggle_auto_renewal( $subscription );
    $single['is_duplicate_site'] = WC_Subscriptions::is_duplicate_site();


    if ( WCS_My_Account_Auto_Renew_Toggle::can_user_toggle_auto_renewal( $subscription ) ){
      $toggle_classes = array( 'subscription-auto-renew-toggle', 'subscription-auto-renew-toggle--hidden' );
      if ( $subscription->is_manual() ) {
        $single['toggle_label']     = __( 'Enable auto renew', 'woocommerce-subscriptions' );
        $single['toggle_classes'] = 'subscription-auto-renew-toggle--off';

        if ( WC_Subscriptions::is_duplicate_site() ) {
          $single['toggle_classes'] = 'subscription-auto-renew-toggle--disabled';
        }
      } else {
        $single['toggle_label']    = __( 'Disable auto renew', 'woocommerce-subscriptions' );
        $single['toggle_classes'] = 'subscription-auto-renew-toggle--on';
      }
    }
    $single['next_payment_action'] = false;
    if ( $subscription->get_time( 'next_payment' ) > 0 ){
      $single['next_payment_action'] = true;
      $single['is_manual'] = wc_bool_to_string( $subscription->is_manual() );
      $single['get_payment_method_to_display'] = $subscription->get_payment_method_to_display( 'customer' );
    }
    $actions = wcs_get_all_user_actions_for_subscription( $subscription, get_current_user_id() );

    $single['actions'] = $actions;
    $single['notes'] = $subscription->get_customer_order_notes();

    $single['include_switch_links']       = true;
    $single['include_item_removal_links'] = wcs_can_items_be_removed( $subscription );

    $totals = [];

    foreach($subscription->get_order_item_totals() as $key => $total){
      $totals[$key] = ['label' => $total['label'],'value' => $total['value']];
    }
    $single['totals'] = $totals;


    $items = [];


    foreach ( $subscription->get_items() as $item_id => $item ) {

      $item = new WC_Order_Item_Product($item_id);
      $_product  = $item->get_product();
      $itemm = [];


      $itemm['name'] = $item['name'];

      $itemm['quantity'] = ' <strong class="product-quantity">' . $item['qty'] . '</strong>';

      $itemm['total'] = wp_kses_post( $subscription->get_formatted_line_subtotal( $item ) );

      if ( $subscription->has_status( array( 'completed', 'processing' ) ) && ( $purchase_note = get_post_meta( $_product->id, '_purchase_note', true ) ) ) {
        $itemm['purchase_note'] = wp_kses_post( wpautop( do_shortcode( $purchase_note ) ) );

      }

      $items[] = $itemm;

    }
    $single['items'] = $items;

    $orders = $subscription->get_related_orders();
    $orderss = [];
    foreach ($orders as $key => $value) {
      $order = [];
      $ordr = wc_get_order($value);
      $items = [];

      $order['ID'] = $ordr->get_id();
      $order['total'] = $ordr->get_total();
      $order['status'] = $ordr->get_status();
      $order['url'] = $ordr->get_checkout_order_received_url();
      $order['payment'] = $ordr->get_payment_method();
      $order['payment_title'] = $ordr->get_payment_method_title();
      $order['date'] = $ordr->get_date_created();
      $order['notes'] = $ordr->get_customer_order_notes();


      foreach ( $ordr->get_items() as $item_id => $item ) {
        $aw_item = [];

        $aw_item['product_id'] = $item->get_product_id();
        $aw_item['variation_id'] = $item->get_variation_id();

        $aw_item['name'] = $item->get_name();
        $aw_item['quantity'] = $item->get_quantity();
        $aw_item['subtotal'] = $item->get_subtotal();
        $aw_item['total'] = $item->get_total();
        $aw_item['tax'] = $item->get_subtotal_tax();

        $items[] = $aw_item;
      }
      $order['items'] = $items;


      $orderss[] =$order;
    }

    $single['orderss'] = $orderss;

    return $single;

  }


  public function orders(){

    $user = get_userdata(get_current_user_id());

    foreach (get_field('sites','option') as $key => $value) {
      $url = $value['site_url'];

      $endpoint = "wp-json/aw/get_orders?email={$user->user_email}";


      $response = wp_remote_get($url . $endpoint);

      $orders = json_decode($response['body']);

      include AW_PLUGIN_PATH . 'public/partials/orders.php';
    }


  }

  public function menu_item($menu_links){

    //$new = array( 'aw-order' => 'View Order' );
    $new = array( 'aw-subscriptions' => 'Subscriptions' );

    // or in case you need 2 links
    // $new = array( 'link1' => 'Link 1', 'link2' => 'Link 2' );

    // array_slice() is good when you want to add an element between the other ones
    $menu_links = array_slice( $menu_links, 0, 2, true )
    + $new
    + array_slice( $menu_links, 1, NULL, true );


    unset($menu_links['subscriptions']);

    return $menu_links;

  }

  public function subscription_url($url, $endpoint, $value, $permalink){
    if( $endpoint === 'view-subscriptions' ) {

      // ok, here is the place for your custom URL, it could be external
      //$url = get_permalink( get_option('woocommerce_myaccount_page_id') ) . "subscriptions";

    }
    return $url;
  }

  public function init(){
    add_rewrite_endpoint( 'aw-order', EP_PAGES );
    add_rewrite_endpoint( 'aw-subscriptions', EP_PAGES );
    add_rewrite_endpoint( 'aw-subscription', EP_PAGES );
    flush_rewrite_rules();
  }


  public function view_order(){

    $id = $_GET['id'];
    $url = $_GET['site'];

    $endpoint = "wp-json/aw/get_order?id={$id}";

    $response = wp_remote_get($url . $endpoint);

    $order = json_decode($response['body']);

    include AW_PLUGIN_PATH . 'public/partials/view-order.php';


  }


  public function subscriptions(){

    include AW_PLUGIN_PATH . 'public/partials/this-subscriptions.php';

    $user = get_userdata(get_current_user_id());
    foreach (get_field('sites','option') as $key => $value) {
      $url = $value['site_url'];

      $endpoint = "wp-json/aw/get_subscriptions?email={$user->user_email}";

      $response = wp_remote_get($url . $endpoint);

      $aw_subscriptions = json_decode($response['body']);

      include AW_PLUGIN_PATH . 'public/partials/subscriptions.php';
    }

  }

  public function view_subscription(){

    $id = $_GET['id'];

    $url = $_GET['site'];

    $endpoint = "wp-json/aw/get_subscription?id={$id}";

    $response = wp_remote_get($url . $endpoint);

    $subscription = json_decode($response['body']);


    include AW_PLUGIN_PATH . 'public/partials/view-subscription.php';
  }

  public function aw_change_status(){

    $id = $_GET['id'];
    $status = $_GET['status'];

    $subscription  = wcs_get_subscription($id);
    $subscription->update_status($status);

  }

  public function aw_reactive_subscription($id,$url,$status){

    wp_remote_get($url . "wp-json/aw/aw_change_status?id={$id}&status={$status}");


  }

  public function aw_reactive_subscription_1($id,$status){

    $subscription  = wcs_get_subscription($id);
    $subscription->update_status($status);

  }

  public function aw_extend_subscription(){

    $id = $_GET['id'];
    $days = $_GET['days'];
    $reason = $_GET['reason'];

    update_post_meta($id,'aw_reason',$reason);


    $subscription  = wcs_get_subscription($id);

    if($subscription){
      $date = $subscription->get_date( 'next_payment' );
      $end_date = date('Y-m-d H:i:s',strtotime("+{$days} days", strtotime($date)));
      $dates_to_update = array();
      $dates_to_update['next_payment'] = $end_date;
      $subscription->update_dates($dates_to_update);


      // The text for the note
      $note = __("Subscriptions has been pased due to '{$reason}'");

      // Add the note
      $subscription->add_order_note( $note );

    }


  }
  public function aw_footer(){

    include AW_PLUGIN_PATH . 'public/partials/footer.php';

  }

  public function hidden_input($sub){

    ?>
    <input type="hidden" id='aw_sub_id' value="<?= $sub->get_id();?>"/>
    <input type="hidden" id='aw_sub_status' value="<?= $sub->get_status();?>"/>
    <input type="hidden" value="current" id="current" />
    <?php

  }


}

add_action("admin_footer",function(){


  // global $post;
  // $order_id = $post->ID;
  // $order = wc_get_order($order_id);
  //
  // $max_refund = wc_format_decimal($order->get_total() - $order->get_total_refunded());
  //     if (!$max_refund) {
  //       return;
  //     }
  //
  // if($result == null){
  // // Create the refund object
  // $refund = wc_create_refund(array('amount' => $max_refund, 'reason' => __('Order Fully Refunded', 'woocommerce'), 'order_id' => $order_id, 'line_items' => array()));
  // wc_delete_shop_order_transients($order_id);
  //
  // }


});
