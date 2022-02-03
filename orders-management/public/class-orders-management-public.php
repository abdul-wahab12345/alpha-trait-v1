<?php

/**
* The public-facing functionality of the plugin.
*
* @link       http://abdulwahab.live/
* @since      1.0.0
*
* @package    Orders_Management
* @subpackage Orders_Management/public
*/

/**
* The public-facing functionality of the plugin.
*
* Defines the plugin name, version, and two examples hooks for how to
* enqueue the public-facing stylesheet and JavaScript.
*
* @package    Orders_Management
* @subpackage Orders_Management/public
* @author     Abdul Wahab <rockingwahab9@gmail.com>
*/
class Orders_Management_Public {

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

	}

	/**
	* Register the stylesheets for the public-facing side of the site.
	*
	* @since    1.0.0
	*/
	public function enqueue_styles() {

		/**
		* This function is provided for demonstration purposes only.
		*
		* An instance of this class should be passed to the run() function
		* defined in Orders_Management_Loader as all of the hooks are defined
		* in that particular class.
		*
		* The Orders_Management_Loader will then create the relationship
		* between the defined hooks and the functions defined in this
		* class.
		*/

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/orders-management-public.css', array(), $this->version, 'all' );

	}

	/**
	* Register the JavaScript for the public-facing side of the site.
	*
	* @since    1.0.0
	*/
	public function enqueue_scripts() {

		/**
		* This function is provided for demonstration purposes only.
		*
		* An instance of this class should be passed to the run() function
		* defined in Orders_Management_Loader as all of the hooks are defined
		* in that particular class.
		*
		* The Orders_Management_Loader will then create the relationship
		* between the defined hooks and the functions defined in this
		* class.
		*/

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/orders-management-public.js', array( 'jquery' ), $this->version, false );

	}

	function change_template( $template, $template_name, $template_path ) {
		$basename = basename( $template );
		if( $basename == 'my-subscriptions.php' ) {
			$template =  Orders_Management_PATH . '/public/partials/my-subscriptions.php';
		}
		return $template;
	}

	public function aw_add_to_cart(){
		if(isset($_POST['aw_add_to_cart'])){
			$product_id = $_POST['product_id'];
			$variation_id = $_POST['aw_variation_id'];
			$aw_switch_id = $_POST['aw_switch_id'];

			$data = array();

			if($aw_switch_id != "false"){
				$data = array('aw_switch_id' => $aw_switch_id );
			}

			WC()->cart->add_to_cart( $product_id ,1,	$variation_id,array(), $data);

			wp_redirect(wc_get_checkout_url());

		}
		
		

		if(isset($_POST['add_sub_note'])){

			$note = $_POST['note'];
			$sub = $_POST['aw_subscription_id'];
			$subscription = wcs_get_subscription($sub);
			$orders = $subscription->get_related_orders();

			foreach ($orders as $key => $value) {
				$order = wc_get_order($value);

				if(isset($_POST['note_type'])){
					//	$order->add_order_note($note);

					$pr_note = $order->get_customer_note();
					$pr_note .=   "<br>" . $note;
					$order->set_customer_note($pr_note);
					$order->save();

				}else{

					$delivery_note = get_post_meta($value,'delivery_note',true);

					if ($delivery_note) {
						$delivery_note .= "<br>" . $note;
						update_post_meta($value,'delivery_note',$delivery_note);

					}else{
						update_post_meta($value,'delivery_note',$note);
					}

				}

			}

		}

	}

	public function change_subscription($item_id,$values){
		if(isset($values['aw_switch_id'])){
			$subscription = wcs_get_subscription($values['aw_switch_id']);
			$subscription->update_status("on-hold");
		}

	}

	public function aw_pause(){

		$mobile = wp_is_mobile();

		if(isset($_POST['aw_pause_1'])){

			$weeks = $_POST['period'];
			$period = $_POST['period'];
			$id = $_POST['id'];
			$nourl = $_POST['nourl'];
			$status = $_POST['status'];
			$charged = $_POST['aw_charged'];
			$reason = $_POST['reason'];
			$next_delivery_user = $_POST['next_delivery'];
			$current_time = current_time('timestamp');
			
			if(!empty($next_delivery_user)){
			    update_post_meta($id,"next_delivery_user",$next_delivery_user);
			}

			$duration = $period;

			$subscription = wcs_get_subscription($id);

			$note = __("Subscriptions has been pased due to '{$reason}'");

			update_post_meta($id,'aw_pause_date',date("Y-m-d",	$current_time));
			$next_payment = $subscription->get_date( 'next_payment' );
			$aw_last_payment1 = $subscription->get_date('date_paid');

			if($period != 0){
				//pause for a week
				if(isset($_POST['want_meal'])){
					$next_week = new Datetime(date("Y-m-d H:i:s",$current_time));
					$next_week->modify("+1 week");
					$next_week->modify("Next Sunday");

					$next_delivery = new Datetime($aw_last_payment1);
					$next_delivery->modify("Next Sunday");

					$want_meal = $_POST['want_meal'];
					echo $charged;
					if(	$charged  == "true"){
						//charged
						if($want_meal == "no"){
							//process refund, don't want meals

							$this->handle_refund($subscription);

							if($period == 1){
								wc_add_notice($note . " for 1 week. Your amount is refunded.","success");
							}else{
								wc_add_notice($note . " Your next delivery date is {$next_delivery_user}. Your amount is refunded.","success");
							}


							$note = htmlspecialchars($note, ENT_QUOTES, 'UTF-8', false);
							$this->add_hubspot_note(get_current_user_id(),$note);
							$subscription->add_order_note( $note );

							if($period == 1){
								update_post_meta($id,'aw_next_delivery',$next_week->format("Y-m-d H:i:s"));
							}else{
								update_post_meta($id,'aw_next_delivery',$next_delivery_user . " " . date("H:i:s",$current_time));
							}

						}else{
							echo "Want meal";//no need to update dates for next delivery
							delete_post_meta($id,'aw_next_delivery');
							if($period == 1){
								wc_add_notice($note . " for 1 week. You will receive meals on sunday.","success");
							}else{
								wc_add_notice($note . ". Your next delivery date is {$next_delivery_user}. You will receive meals on sunday.","success");

							}
						}
					}else{
						//no charge
						if($want_meal == "no"){


							if($period == 1){
								update_post_meta($id,'aw_next_delivery',$next_delivery->format("Y-m-d H:i:s"));
								wc_add_notice($note . " for 1 week.","success");
							}else{
								update_post_meta($id,'aw_next_delivery',$next_delivery_user . " " . date("H:i:s",$current_time));
								wc_add_notice($note . ". Your next delivery date is {$next_delivery_user}.","success");
							}


							echo "no meal";
						}else{
							echo "Want meal";//capture charge
							//must be renewed, want meal

							$this->handle_renewal_for_pause($subscription,$note,$period);
						}

					}
				}else{
					//no meal check just pause

					if($period == 1){
						wc_add_notice($note . " for 1 week.","success");
					}else{
						wc_add_notice($note . ". Your next delivery date is {$next_delivery_user}.","success");

					}
				}


				$date = new Datetime($next_payment);
				$date->modify("+7 days");
				if($period == 1){
					$dates_to_update = array('next_payment' => $date->format("Y-m-d H:i:s"));
					$subscription->update_dates($dates_to_update);
				}else{
					$next_payment = new DateTime($next_delivery_user);
					$next_payment->modify("-4 days");
					$dates_to_update = array('next_payment' => $next_payment->format("Y-m-d H:i:s"));
					$subscription->update_dates($dates_to_update);
				}


			}else{
				// handle indifinitly
				$note = __("Subscriptions has been pased due to '{$reason}' for Indefinitely time");
				delete_post_meta($id,'aw_pause_date');
				if(isset($_POST['want_meal'])){

					$want_meal = $_POST['want_meal'];
					if(	$charged  == "true"){
						//charged

						if($want_meal == "no"){

							//process refund, don't want meals

							$this->handle_refund($subscription);
							$subscription->update_status('on-hold');
							wc_add_notice($note . ". Your amount is refunded.","success");
							$note = htmlspecialchars($note, ENT_QUOTES, 'UTF-8', false);
							$this->add_hubspot_note(get_current_user_id(),$note);
							$subscription->add_order_note( $note );

						}else{
							// "yes want meal";

							$subscription->update_status('on-hold');
							wc_add_notice($note . ". You will receive your meals on sunday.","success");
							$note = htmlspecialchars($note, ENT_QUOTES, 'UTF-8', false);
							$this->add_hubspot_note(get_current_user_id(),$note);
							$subscription->add_order_note( $note );

						}

					}else{
						// Not charged
						if($want_meal == "no"){
							// "don't want meal";
							$subscription->update_status('on-hold');
							wc_add_notice($note ,"success");
							$note = htmlspecialchars($note, ENT_QUOTES, 'UTF-8', false);
							$this->add_hubspot_note(get_current_user_id(),$note);
							$subscription->add_order_note( $note );

						}else{
							//must be renewed, want meal

							$this->handle_renewal($subscription,$note);

						}

					}

				}else{

					// no meal check, due to cuttoff time passed

					$subscription->update_status('on-hold');
					wc_add_notice($note ,"success");
					$note = htmlspecialchars($note, ENT_QUOTES, 'UTF-8', false);
					$this->add_hubspot_note(get_current_user_id(),$note);
					$subscription->add_order_note( $note );

				}

			}
			//	die;

			$url = wc_get_endpoint_url('view-subscription/'.$id);
			if($nourl == "false"){
				$url = wc_get_endpoint_url('my-account/subscriptions?mobile='.$mobile);

			}
			wp_safe_redirect( $url);

			die;
		}//end pause if

	}//end pause function

	//handle renew

	public function handle_renewal($subscription,$note){

		WCS_Admin_Meta_Boxes::process_renewal_action_request($subscription);

		$subscription = wcs_get_subscription($subscription->get_id());
		if($subscription->get_status() == "active"){

			$subscription->update_status('on-hold');
			wc_add_notice($note . ". We are processing your renewal, after payment capture you will receive your meals on sunday.","success");
			$note = htmlspecialchars($note, ENT_QUOTES, 'UTF-8', false);
			$this->add_hubspot_note(get_current_user_id(),$note);
			$subscription->add_order_note( $note );
		}else{
			wc_add_notice( "We are unable to process your renewal, make sure you have active card attached or contact us for details.","error");
		}

	}

	public function handle_renewal_for_pause($subscription,$note){

		WCS_Admin_Meta_Boxes::process_renewal_action_request($subscription);

		$subscription = wcs_get_subscription($subscription->get_id());
		if($subscription->get_status() == "active"){

			wc_add_notice($note . ". We are processing your renewal, after payment capture you will receive your meals on sunday.","success");

			$note = htmlspecialchars($note, ENT_QUOTES, 'UTF-8', false);
			$this->add_hubspot_note(get_current_user_id(),$note);
			$subscription->add_order_note( $note );
		}else{
			wc_add_notice( "We are unable to process your renewal, make sure you have active card attached or contact us for details.","error");
		}

	}

	public function handle_refund($subscription){
		$relared_orders_ids_array = $subscription->get_related_orders();
		$i = 0;
		foreach ($relared_orders_ids_array as $key => $value) {
			$order = wc_get_order($value);
			if($order->get_status() == "processing" && $i == 0){
				echo "process" . $value . "<br>";
				$this->refund($order);
				break;
			}
			$i++;
		}
	}

	public function refund($order){
		$order_id = $order->get_id();
		$max_refund = wc_format_decimal($order->get_total() - $order->get_total_refunded());
		if (!$max_refund) {
			return;
		}
		$refund = wc_create_refund(array('amount' => $max_refund, 'reason' => __('Order Fully Refunded, customer pause subscription and don\'t want meals on sunday.', 'woocommerce'), 'order_id' => $order_id, 'line_items' => array()));
		wc_delete_shop_order_transients($order_id);

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




	public function add_subscription_fields(){
		$id = $_POST['id'];
		$subscription  = wcs_get_subscription($id);

		$aw_week_cutoff = get_option('aw_week_cutoff')?get_option('aw_week_cutoff'):"Wednesday";
		$current_time = current_time('timestamp');
		$cuttofftime = new Datetime(date("Y-m-d H:i:s",  $current_time));
		$cuttofftime->modify("Next {$aw_week_cutoff}");
		$cuttofftime->setTime(23,59,59);

		$cuttofftime_last = new Datetime(date("Y-m-d H:i:s",  $current_time));
		$cuttofftime_last->modify("Last {$aw_week_cutoff} +1 days");
		$cuttofftime_last->setTime(00,00,00);

		$monday = new Datetime(date("Y-m-d H:i:s",  $current_time));
		$monday->modify("Next {$aw_week_cutoff}");
		$monday->modify('Last Monday');
		$monday->setTime(00,00,00);


		$today = new Datetime(date("Y-m-d H:i:s",  $current_time));

		if(date('l',$current_time) == $aw_week_cutoff){
			$cuttofftime = new Datetime(date("Y-m-d H:i:s",  $current_time));
			$cuttofftime->setTime(23,59,59);


			$monday = new Datetime(date("Y-m-d H:i:s",  $current_time));
			$monday->modify('Last Monday');
			$monday->setTime(00,00,00);
		}

		$last_payment =$subscription->get_date('date_paid');
		$last_payment = new Datetime($last_payment);

		$charged = false;

		if($last_payment >= $cuttofftime_last && $last_payment <= $cuttofftime){
			$charged = true;
		}

		//	var_dump($charged,$id,$cuttofftime->format("Y-m-d H:i:s"),$today->format("Y-m-d H:i:s"),$monday->format("Y-m-d H:i:s"));

		if($today >= $monday && $today <= $cuttofftime){

			?>

			<div>
				<p class="form-text-bold">Would you like your meals this Sunday?</p>
				<input type="radio" required id="meal" name="want_meal" value="yes" class="control-radio">
				<label for="meal" class="form-text-normal">Yes</label><br>
				<input type="radio" required id="meal1" name="want_meal" value="no" class="control-radio">
				<label for="meal1" class="form-text-normal">No</label><br>

			</div>

			<?php
		}else{
			if($charged){
				?>

				<div>
					<p class="form-text-bold">Would you like your meals this Sunday?</p>
					<input type="radio" required id="meal" name="want_meal" value="yes" class="control-radio">
					<label for="meal" class="form-text-normal">Yes</label><br>
					<input type="radio" required id="meal1" name="want_meal" value="no" class="control-radio">
					<label for="meal1" class="form-text-normal">No</label><br>

				</div>

				<?php
			}
		}

		?>

		<input type="hidden" id='aw_charged' name="aw_charged" value="<?= $charged?'true':'false';?>"  >

		<?php


		wp_die();
	}

}