<?php


require_once Orders_Management_PATH . '/includes/excel/Classes/PHPExcel.php';
require_once Orders_Management_PATH . '/includes/excel/Classes/PHPExcel/IOFactory.php';


/**
* The admin-specific functionality of the plugin.
*
* @link       http://abdulwahab.live/
* @since      1.0.0
*
* @package    Orders_Management
* @subpackage Orders_Management/admin
*/

/**
* The admin-specific functionality of the plugin.
*
* Defines the plugin name, version, and two examples hooks for how to
* enqueue the admin-specific stylesheet and JavaScript.
*
* @package    Orders_Management
* @subpackage Orders_Management/admin
* @author     Abdul Wahab <rockingwahab9@gmail.com>
*/
class Orders_Management_Admin {

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
	* @param      string    $plugin_name       The name of this plugin.
	* @param      string    $version    The version of this plugin.
	*/
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	* Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/orders-management-admin.css', array(), $this->version, 'all' );

	}

	/**
	* Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/orders-management-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function admin_menu(){
		$option = get_option("edd_sample_license_status");
		if(	$option && 	$option == "valid"){
			add_submenu_page( 'alpha-trait', __( 'Delivery', 'woocommerce' ), __( 'Delivery', 'woocommerce' ), 'manage_woocommerce', 'wc-week-orders', array( $this, 'custom_wc_menu' ) );
			add_submenu_page( 'alpha-trait', __( 'Orders', 'woocommerce' ), __( 'Orders', 'woocommerce' ), 'manage_woocommerce', 'aw-orders', array( $this, 'aw_orders' ) );

			add_submenu_page( 'alpha-trait', __( 'Settings', 'woocommerce' ), __( 'Settings', 'woocommerce' ), 'manage_woocommerce', 'aw-settings', array( $this, 'aw_settings' ) );
		}
	}

	public function custom_wc_menu(){
		include plugin_dir_path(__FILE__) . 'partials/orders-management-admin-display.php';
	}

	public function aw_settings(){
		include plugin_dir_path(__FILE__) . 'partials/aw-settings.php';
	}

	public function aw_orders(){
		include plugin_dir_path(__FILE__) . 'partials/aw_orders.php';
	}

	public function assign_driver(){

		if(isset($_GET['assign_driver'])){

			$driver = $_GET['aw_drivers'];
			$orders = $_GET['aw_orders1'];

			if(!empty($orders)){

				foreach ($orders as $key => $value) {

					update_post_meta($value,'aw_assign_driver',$driver);

				}

			}

		}

	}

	public function aw_products($order){
		$string = "";
		foreach ( $order->get_items() as $item_id => $item ) {
			$product_id = $item->get_product_id();
			$variation_id = $item->get_variation_id();
			$product = $item->get_product();
			$name = $item->get_name();
			$quantity = $item->get_quantity();
			$subtotal = $item->get_subtotal();
			$total = $item->get_total();
			$string = "[Product ID: {$product_id}; Product Name: {$name}; Quantity: {$quantity}], ";


		}

		return $string;
	}

	public function aw_csv(){

		$data = [];


		$drivers = $_POST['drivers'];

		$sunday = Date('m/d/Y', StrToTime("Next Sunday"));

		$aw_pickup_phone = get_option('aw_pickup_phone');

		$pickup_date = "{$sunday} 13:00";
		$aw_pickup_address = get_option('aw_pickup_address');

		$data[] = $this->get_csv_header();

		foreach ($drivers as $driver) {
			$data[] = $this->get_driver_row($aw_pickup_address,$aw_pickup_phone,$driver,$pickup_date);
		}

		$orders = $_POST['aw_orders'];


		foreach($orders as $order){


			$pickup_date = "{$sunday} 13:00";

			$delivery_date = "{$sunday} 19:00";


			$data[] = $this->get_customer_row($order,$delivery_date,$pickup_date);

		}

		//var_dump($data);

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="delivery.csv"');


		$fp = fopen('php://output', 'wb');
		foreach ( $data as $line ) {
			//echo $val;
			//     $val = explode(",", $line);
			fputcsv($fp, $line);

		}
		fclose($fp);
		wp_die();
	}

	public function get_csv_header(){
		$row = 'Task Description*,Customer Email*,Customer Name*,Street Level Address*,City*,Zipcode/ Pincode*,Country*,Latitude,Longitude,Customer Phone Number,Delivery Date and Time (MM/DD/YYYY) (HH:MM:SS)*,Agent ID (Settings > Agents),Merchant Email,Merchant Name,P_Street Level Address*,P_City*,P_Zipcode/ Pincode*,P_Country*,P_Latitude,P_Longitude,Phone Number*,Pickup Date and Time (MM/DD/YYYY) (HH:MM:SS)*,P_Order_id,D_Order_id,Tags,has_pickup,has_delivery,type,P_custom_field1,P_custom_field2,P_custom_field3,P_custom_field4,D_custom_field11,D_custom_field21,D_custom_field31,D_custom_field41,auto_assignment,geofence,team_id';

		return explode(',',$row);
	}

	public function get_driver_row($address,$phone,$driver_id,$date){
		$address = str_replace(',','',$address);

		$aw_pickup_city = get_option('aw_pickup_city');
		$aw_pickup_postcode = get_option('aw_pickup_postcode');
		$aw_chef_email = get_option('aw_chef_email');

		$row = 'Pickup location,'.$aw_chef_email.',Chef,'.$address.','.$aw_pickup_city.','.$aw_pickup_postcode.',United States,,,'.$phone.',,'.$driver_id.',,Chef,'.$address.','.$aw_pickup_city.',,United States,,,'.$phone.','.$date.',p1,,,1,0,3,,,,,,,,,0,,';

		return explode(',',$row);

	}

	public function get_customer_row($order_id,$delivery_date,$pickup_date){

		$order = wc_get_order($order_id);

		$name = htmlspecialchars($order->get_formatted_shipping_full_name(), ENT_QUOTES, 'UTF-8', false);
		$address = htmlspecialchars($order->get_shipping_address_1() . " " . $order->get_shipping_address_2(), ENT_QUOTES, 'UTF-8', false);
		$city = htmlspecialchars($order->get_shipping_city(), ENT_QUOTES, 'UTF-8', false);
		$postcode = htmlspecialchars($order->get_shipping_postcode(), ENT_QUOTES, 'UTF-8', false);
		$address = str_replace(',','',$address);
		$city = str_replace(',','',$city);
		$phone = htmlspecialchars($order->get_billing_phone(), ENT_QUOTES, 'UTF-8', false);
		$delivery_note = htmlspecialchars(get_post_meta($order->get_id(),"delivery_note",true), ENT_QUOTES, 'UTF-8', false);

		$row = $delivery_note . ','.$order->get_billing_email().','.$name.','.$address.','.$city.','.$postcode.',United States,,,'.$phone.','.$delivery_date.',,,,,,,,,,,'.$pickup_date.',,'.$order_id.',,,,,,,,,,,,,,,';

		return explode(',',$row);
	}

	public function delivery_inspection(){

		$aw_week_cutoff = get_option('aw_week_cutoff')?get_option('aw_week_cutoff'):"Wednesday";
		$aw_meal = get_option('aw_meal_number')?get_option('aw_meal_number'):"15 Meals";


		$drivers = $_POST['drivers'];


		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("RN Kushwaha")
		->setLastModifiedBy("Aryan")
		->setTitle("Reports")
		->setSubject("CSV Download")
		->setDescription("Test document ")
		->setKeywords("phpExcel")
		->setCategory("Test file");

		// Create a first sheet, representing sales data
		$i = 0;
		foreach ($drivers as $key => $value) {
			$objPHPExcel->setActiveSheetIndex(0);
			if($i != 0){
				$objPHPExcel->createSheet($i);
				$objPHPExcel->setActiveSheetIndex($i);
			}

			$objPHPExcel->getActiveSheet()->setCellValue('A1', "Driver's Note*");
			$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Number of Bags*');
			//	$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Customer Email*');
			$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Customer Name*');
			$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Street Level Address*');
			$objPHPExcel->getActiveSheet()->setCellValue('E1', 'City*');
			$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Zipcode/ Pincode*');
			$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Tags');
			$objPHPExcel->getActiveSheet()->setTitle($value);

			$j = 2;

			$orders = $_POST['aw_orders'];


			foreach($orders as $order){

				$order_id = $order;

				$aw_assign_driver = get_post_meta($order_id,'aw_assign_driver',true);
				$delivery_note = get_post_meta($order_id,'delivery_note',true);

				if($aw_assign_driver == $value){

					$order = wc_get_order($order_id);

					$name = $order->get_formatted_shipping_full_name();;
					$email = $order->get_billing_email();
					$address = $order->get_shipping_address_1();
					$address .= ", ".$order->get_shipping_address_2();
					$city = $order->get_shipping_city();
					$zip = $order->get_shipping_postcode();

					$total_bags = 0;
					foreach ( $order->get_items() as $item_id => $item ) {

						$product_id = $item->get_product_id();
						$variation_id = $item->get_variation_id();
						$product = $item->get_product();
						$title = $item->get_name();
						$quantity = $item->get_quantity();
						$subtotal = $item->get_subtotal();
						$total = $item->get_total();

						$bags = $quantity;

						if(preg_match("/{$aw_meal}/i", $title)) {
							$bags = $bags * 2;
						}
						$total_bags += $bags;

					}

					$bag_text = "{$total_bags} Bags";

					if($bags == 1){
						$bag_text = "{$total_bags} Bag";

					}

					$objPHPExcel->getActiveSheet()->setCellValue('A' . $j , $delivery_note);
					$objPHPExcel->getActiveSheet()->setCellValue('B' . $j , $bag_text);
					//	$objPHPExcel->getActiveSheet()->setCellValue('C' . $j , $email);
					$objPHPExcel->getActiveSheet()->setCellValue('C' . $j , $name);
					$objPHPExcel->getActiveSheet()->setCellValue('D' . $j , $address);
					$objPHPExcel->getActiveSheet()->setCellValue('E' . $j , $city);
					$objPHPExcel->getActiveSheet()->setCellValue('F' . $j , $zip);
					$objPHPExcel->getActiveSheet()->setCellValue('G' . $j , $value);



					$j++;
				}

			}
			$i++;
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Inspection.xlsx"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');


	}


	public function aw_csv1(){

		$data = [];

		$orders = $_POST['aw_orders'];

		$data[] = $this->get_csv_header_for_order();

		foreach($orders as $order){

			$aw_order = wc_get_order($order);
			$cou = 0;

			foreach ( $aw_order->get_items() as $item_id => $item ) {

				$product_id = $item->get_product_id();
				$variation_id = $item->get_variation_id();
				$product = $item->get_product();
				$name = $item->get_name();
				$quantity = $item->get_quantity();
				$subtotal = $item->get_subtotal();
				$total = $item->get_total();

				$aw_id = $aw_order->get_id();
				$aw_total = $aw_order->get_total();

				if(count($aw_order->get_items()) > 1){
					$aw_id .= " - " . $cou++;

					$aw_total = $item->get_total() + $item->get_total_tax();

				}

				$data[] = $this->get_csv_row_for_order($order,$product_id,$variation_id,$name,$quantity,$aw_id,$aw_total);
			}

		}


		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="orders.csv"');


		$fp = fopen('php://output', 'wb');
		foreach ( $data as $line ) {

			fputcsv($fp, $line);

		}
		fclose($fp);
		wp_die();
	}




	public function get_csv_header_for_order(){

		$row = "Order ID,Order Date,Completed Date,Order Status,Product ID ,Product categories ,Quantity,Product Name ,Payment Method Title,Order Total,Sale Price ,Total Tax Amount,_stripe_fee,Refund Total,Shipping Cost,Discount Amount ,Coupon Code,Customer User ID,Billing First Name,Billing Last Name,Billing Phone,Customer Account Email Address,Shipping Address 1,Shipping Address 2,Shipping City,Shipping Postcode,Customer Note,Delivery Note";

		return explode(',',$row);
	}

	public function get_csv_row_for_order($order_id,$product_id,$variation_id,$name,$quantity,$id,$aw_total){
		$order = wc_get_order( $order_id );

		$coupon = [];

		foreach( $order->get_used_coupons() as $coupon_code ){
			$coupon[]= $coupon_code;
		}

		$coupon = implode(',',$coupon);

		$tax = 0;

		foreach( $order->get_tax_totals() as $value ){
			$tax += $value;
		}


		$product = wc_get_product($product_id);

		$cats = [];

		$terms = get_the_terms( $product_id, 'product_cat' );
		foreach ($terms as $term) {
			$cats[] = $term->name;
		}

		if($variation_id){
			$product = wc_get_product($variation_id);
		}



		$row = [
			$id,
			$order->get_date_created(),
			$order->get_date_completed(),
			$order->get_status(),
			$variation_id,
			implode(">",$cats),
			$quantity,
			$name,
			$order->get_payment_method_title(),
			$aw_total,
			$product->get_sale_price(),
			$order->get_total_tax(),
			get_post_meta($order_id,'_stripe_fee',true),
			$order->get_total_refunded(),
			$order->get_shipping_total(),
			$order->get_discount_total(),
			$coupon,
			$order->get_customer_id(),
			$order->get_shipping_first_name()?$order->get_shipping_first_name():$order->get_billing_first_name(),
			$order->get_shipping_last_name()?$order->get_shipping_last_name():$order->get_billing_last_name(),
			$order->get_shipping_phone()?$order->get_shipping_phone():$order->get_billing_phone(),
			$order->get_billing_email(),
			$order->get_shipping_address_1(),
			$order->get_shipping_address_2(),
			$order->get_shipping_city(),
			$order->get_shipping_postcode(),
			$order->get_customer_note(),
			get_post_meta($order_id,'delivery_note',true),
		];



		return $row;
	}

	public function aw_products1($order,$type = "id"){
		$string = [];
		foreach ( $order->get_items() as $item_id => $item ) {
			$product_id = $item->get_product_id();
			$variation_id = $item->get_variation_id();
			$product = $item->get_product();
			$name = $item->get_name();
			$quantity = $item->get_quantity();
			$subtotal = $item->get_subtotal();
			$total = $item->get_total();

			if($type == "id"){
				$string[] = " ".$product_id;
			}elseif($type == "quantity"){
				$string[] = $quantity;
			}else{
				$string[] = $name;
			}


		}

		return implode(',',$string);
	}

	public function admin_init(){

		if(isset($_GET['aw_genrate_pdf'])){
			include Orders_Management_PATH . '/admin/partials/pdf.php';
			die;

		}

	}

}
