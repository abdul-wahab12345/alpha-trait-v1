<?php

/**
* Provide a admin area view for the plugin
*
* This file is used to markup the admin-facing aspects of the plugin.
*
* @link       http://abdulwahab.live/
* @since      1.0.0
*
* @package    Orders_Management
* @subpackage Orders_Management/admin/partials
*/

$aw_week_cutoff = get_option('aw_week_cutoff')?get_option('aw_week_cutoff'):"Wednesday";

//$Friday = Date('Y-m-d', StrToTime("Last {$aw_week_cutoff} + 1 days"));

$current_time = current_time('timestamp');
$Friday = new Datetime(date("Y-m-d H:i:s",$current_time));
$Friday->modify("Last {$aw_week_cutoff}");
$Friday->modify("+1 days");
$Friday = $Friday->format("Y-m-d");
$today = date('Y-m-d',$current_time);
$week = "current";
if(isset($_GET['week']) && $_GET['week'] == 'last'){
	$week = "last";
	$today = date('Y-m-d', strToTime("Last {$aw_week_cutoff}"));

	$today = new Datetime(date("Y-m-d H:i:s",$current_time));
	$today->modify("Last {$aw_week_cutoff}");
	$today = $today->format("Y-m-d");

	$Friday = new Datetime($today);
	$Friday->modify('-1 week');
	$Friday->modify('+1 days');
	$Friday = $Friday->format('Y-m-d');
}


global $wpdb;

$date_from = $Friday;
$date_to = $today;
$post_status = implode("','", array('wc-processing') );

$result = $wpdb->get_results( "SELECT * FROM $wpdb->posts
	WHERE post_type = 'shop_order'
	AND post_status IN ('{$post_status}')
	AND post_date BETWEEN '{$date_from}  00:00:00' AND '{$date_to} 23:59:59' ORDER BY ID DESC
	",ARRAY_A);

	if(isset($_GET['aw_date']) && !empty($_GET['aw_date'])){
		$aw_date = $_GET['aw_date'];

		$result = $wpdb->get_results( "SELECT * FROM $wpdb->posts
			WHERE post_type = 'shop_order'
			AND post_status IN ('{$post_status}')
			AND post_date <= '{$aw_date} 23:59:59' ORDER BY ID DESC
			",ARRAY_A);

		}


		?>

		<!-- This file should primarily consist of HTML with a little bit of PHP. -->
		<link rel="stylesheet" href="//code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
		<form id="posts-filter" method="get" action="">
			<input type="hidden" name="page" value="wc-week-orders"/>

			<p class="search-box">

				<div class="tablenav top">

					<div class="alignleft actions">
						<label for="filter-by-date" class="screen-reader-text">Filter by date</label>

						<div style="display: flex;">
							

							<input value="<?= isset($_GET['aw_date'])?$_GET['aw_date']:'';?>" style="margin-right:5px;" autocomplete="off" placeholder="Select Date to filter" type="text" id="datepicker" name="aw_date">

							<input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter" style="height: 100%; margin-top: 8px;">
						</div>

						<?php

						$aw_alphatraitapikey = get_option('aw_alphatraitapikey');
						$aw_pickup_address = get_option('aw_pickup_address');
						$aw_pickup_phone = get_option('aw_pickup_phone');

						if($aw_alphatraitapikey && $aw_pickup_address && $aw_pickup_phone){

							$ch = curl_init();

							curl_setopt($ch, CURLOPT_URL, "https://api.tookanapp.com/v2/get_all_fleets");
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
							curl_setopt($ch, CURLOPT_HEADER, FALSE);

							curl_setopt($ch, CURLOPT_POST, TRUE);

							curl_setopt($ch, CURLOPT_POSTFIELDS, "{
								\"api_key\": \"{$aw_alphatraitapikey}\",
								\"include_any_tag\": 1,
								\"include_team_id\": 0
							}");

							curl_setopt($ch, CURLOPT_HTTPHEADER, array(
								"Content-Type: application/json"
							));

							$response = curl_exec($ch);
							curl_close($ch);

							if($response){
								$data = json_decode($response,true);
								$drivers = $data['data'];
								if(count($drivers) <= 0){
									echo "No driver avalible at this moment!<br>";
								}
								?>

								<div style="display: flex;">
									<select name="aw_drivers" id="assign-driver">
										<option value="">Assign Driver</option>

										<?php foreach ($drivers as $key => $value) {
											// code...
											?>
											<option value="<?= $value['name'];?>"><?= $value['name'];?></option>

										<?php } ?>
									</select>

									<input type="submit" name="assign_driver" id="post-query-submit1" class="button" value="Assign Driver" style="height: 100%; margin-top: 8px;">
								</div>

							<?php }}else{


								?>

								<h2>Please add api key and other data in settings</h2>

								<?php

							} ?>

							<div style="margin: 10px 0px;">

								<button href="<?= admin_url('admin.php?page=wc-week-orders&open_modal=true&week='.$week)?>" id="myBtn1"  class="button aw-modal-1"  type="button">Export For Delivery</button>

								<button id="myBtn2"  class="button aw-modal-2"  type="button">Delivery Inspection</button>

								<button class="button" onclick="jQuery('#aw_genrate_pdf_form').submit();" id="aw-pdf" type="button">Delivery Labels</button>

							</div>

						</div>

						<br class="clear">
					</div>
					<h2 class="screen-reader-text">Orders list</h2><table class="wp-list-table widefat fixed striped table-view-list posts">
						<thead>
							<tr>
								<td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>

								<th scope="col" id="order_number" class="manage-column column-order_number column-primary sortable desc">Order</th>

								<th scope="col" id="order_date" class="manage-column column-order_date sortable desc">Date</th>

								<th scope="col" id="order_status" class="manage-column column-order_status">Status</th>
								<th scope="col" id="order_status" class="manage-column column-order_status">Driver</th>

								<th scope="col" id="order_total" class="manage-column column-order_total sortable desc"><span>Total</span></th>	</tr>
							</thead>

							<tbody id="the-list">

								<?php

								if($result){

									foreach($result as $order){
										$date = $order['post_date'];
										$order = wc_get_order($order['ID']);
										$sms = "get_id";
										try {
											$sms =  call_user_func(array($order,$sms));

										}
										catch(Exception $e) {
											echo 'Message: ' .$e->getMessage();
										}

										$aw_assign_driver = get_post_meta($order->get_id(),'aw_assign_driver',true);

										?>
										<input type="hidden" name="aw_orders[]" value="<?= $order->get_id();?>"/>

										<tr id="post-567" class="iedit author-other level-0 post-567 type-shop_order status-wc-cancelled post-password-required hentry">
											<th scope="row" class="check-column">			<label class="screen-reader-text" for="cb-select-567">
												Select Order – January 21, 2021 @ 05:13 AM			</label>
												<input id="cb-select-567" type="checkbox" name="aw_orders1[]" value="<?= $order->get_id();?>">
												<div class="locked-indicator">

													<span class="screen-reader-text">
														“Order – January 21, 2021 @ 05:13 AM” is locked				</span>
													</div>
												</th>
												<td class="order_number column-order_number has-row-actions column-primary" data-colname="Order">
													<a href="<?= get_edit_post_link($order->get_id())?>" class="order-view"><strong>#<?= $order->get_id() . ' ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();?></strong></a>
												</td>

												<td class="order_date column-order_date" data-colname="Date"><time datetime="2021-01-21T05:13:44+00:00" title="January 21, 2021 5:13 am"><?= $date;?></time>
												</td>

												<td class="order_status column-order_status" data-colname="Status"><span class="contains_subscription" data-contains_subscription="false" style="display: none;"></span><mark class="order-status status-cancelled tips"><span><?= wc_get_order_status_name($order->get_status());?></span></mark></td>
												<td class="order_total column-order_total" data-colname="Total"><?= $aw_assign_driver?></td>


												<td class="order_total column-order_total" data-colname="Total"><?= wc_price($order->get_total());?></td>

												<td class="wc_actions column-wc_actions hidden" data-colname="Actions"><p></p></td>

											</tr>


										<?php }} ?>


									</tbody>

									<tfoot>
										<tr>
											<td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>

											<th scope="col" id="order_number" class="manage-column column-order_number column-primary sortable desc">Order</th>

											<th scope="col" id="order_date" class="manage-column column-order_date sortable desc">Date</th>

											<th scope="col" id="order_status" class="manage-column column-order_status">Status</th>
											<th scope="col" id="order_status" class="manage-column column-order_status">Driver</th>

											<th scope="col" id="order_total" class="manage-column column-order_total sortable desc"><span>Total</span></th>	</tr>
										</tfoot>

									</table>


									<div style="margin: 10px 0px;">

										<button class="button  aw-modal-1" href="<?= admin_url('admin.php?page=wc-week-orders&open_modal=true&week='.$week)?>" id="myBtn" type="button">Export For Delivery</button>
										<button id="myBtn3"  class="button  aw-modal-2"  type="button">Delivery Inspection</button>
										<button class="button" onclick="jQuery('#aw_genrate_pdf_form').submit();" id="aw-pdf" type="button">Delivery Labels</button>

									</div>

								</form>

								<script>

								var $ = jQuery;

								$(document).ready(function(){

									$('.aw_genrate_csv').click(function(){

										$("#genrate_csv_order").submit();

									});

								})

								</script>

								<form style="display:none" id="genrate_csv_order" action="<?= admin_url('admin-ajax.php')?>" method="post">

									<input type="hidden" name="action" value="aw_csv1"/>
									<input type="hidden" name="week" value="<?= $week?>"/>

								</form>

								<form style="display:none" id="aw_genrate_pdf_form" action="<?= admin_url()?>" target="_blank" method="get">

									<?php

									if($result){

										foreach($result as $order){
											?>
											<input type="hidden" name="aw_orders[]" value="<?= $order['ID'];?>"/>
											<?php
										}
									}

									?>


									<input type="hidden" name="aw_genrate_pdf" value="true"/>

								</form>

								<!-- Trigger/Open The Modal -->
								<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
								<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

								<!-- The Modal -->
								<div id="myModal" class="modal">

									<!-- Modal content -->
									<div class="modal-content">
										<span class="close">&times;</span>

										<div class="aw-csv">
											<form action="<?= admin_url('admin-ajax.php')?>" method="post">

												<?php

												if($result){

													foreach($result as $order){
														?>
														<input type="hidden" name="aw_orders[]" value="<?= $order['ID'];?>"/>
														<?php
													}
												}

												?>

												<input type="hidden" name="action" value="aw_csv"/>
												<input type="hidden" name="week" value="<?= $week?>"/>
												<?php

												$aw_alphatraitapikey = get_option('aw_alphatraitapikey');
												$aw_pickup_address = get_option('aw_pickup_address');
												$aw_pickup_phone = get_option('aw_pickup_phone');
												if($aw_alphatraitapikey && $aw_pickup_address && $aw_pickup_phone){
													if($response){
														$data = json_decode($response,true);
														$drivers = $data['data'];
														if(count($drivers) <= 0){
															echo "No driver avalible at this moment!<br>";
														}
														?>
														<label for="drivers" style="font-size:18px;font-weight:bold;margin-bottom:10px;">Selects drivers</label><br><br>
														<select class="js-example-basic-multiple" required name="drivers[]" multiple="multiple">

															<?php foreach ($drivers as $key => $value) {
																// code...
																?>
																<option value="<?= $value['fleet_id'];?>"><?= $value['name'];?></option>

															<?php } ?>
														</select><br>

													<?php } ?>

													<input type="submit" name="genrate_csv" value="Genrate">		<?php }else{		?>

														<h2>Please add Alphatrait delivery api key, address and phone number in Alphatrait settings</h2>

														<?php

													} ?>
												</form>
											</div>
										</div>
									</div>
									<!-- The Modal -->
									<div id="myModal2" class="modal">

										<!-- Modal content -->
										<div class="modal-content">
											<span class="close">&times;</span>
											<div class="aw-csv">
												<form action="<?= admin_url('admin-ajax.php')?>" method="post">


													<?php

													if($result){

														foreach($result as $order){
															?>
															<input type="hidden" name="aw_orders[]" value="<?= $order['ID'];?>"/>
															<?php
														}
													}

													?>

													<input type="hidden" name="action" value="delivery_inspection"/>
													<input type="hidden" name="week" value="<?= $week?>"/>

													<?php

													$aw_alphatraitapikey = get_option('aw_alphatraitapikey');
													$aw_pickup_address = get_option('aw_pickup_address');
													$aw_pickup_phone = get_option('aw_pickup_phone');

													if($aw_alphatraitapikey && $aw_pickup_address && $aw_pickup_phone){

														if($response){
															$data = json_decode($response,true);
															$drivers = $data['data'];
															if(count($drivers) <= 0){
																echo "No driver avalible at this moment!<br>";
															}
															?>
															<label for="drivers" style="font-size:18px;font-weight:bold;margin-bottom:10px;">Selects drivers</label><br><br>
															<select class="js-example-basic-multiple" required name="drivers[]" multiple="multiple">

																<?php foreach ($drivers as $key => $value) {
																	// code...
																	?>
																	<option value="<?= $value['name'];?>"><?= $value['name'];?></option>

																<?php } ?>
															</select><br>

														<?php } ?>

														<input type="submit" name="genrate_csv" value="Genrate">

													<?php }else{	?>

														<h2>Please add Alphatrait delivery api key, address and phone number in Alphatrait settings</h2>

														<?php

													} ?>
												</form>
											</div>


										</div>

									</div>

									<script>
									var $ = jQuery;
									$(document).ready(function() {

										$( "#datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });

										$(".aw-modal-1").click(function(e){
											e.preventDefault();
											$("#myModal").show();

										});

										$(".aw-modal-2").click(function(e){
											e.preventDefault();
											$("#myModal2").show();

										});

										$(".close").click(function(){

											$("#myModal").hide();
											$("#myModal2").hide();

										});


										$('.js-example-basic-multiple').select2();
									});

									</script>
