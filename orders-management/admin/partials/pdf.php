<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1" />

	<title>Orders PDF</title>


</head>
<body>

	<style>

	body{
		padding:0px;
		margin:0px;
	}
	.aw-container{
		width:96%;
		padding: 15px 2%;
		display: flex;
		flex-wrap:wrap;
		background:#fff !important;
	}
	.post-wrap{
		position: relative;
		width: 100%;
		min-height: calc(100% / 2);
		max-height: calc(100% / 2);
		max-width: calc(100% / 5);
		overflow:hidden;
	}
	.order-wrap{
		margin:17.5px 12px;
		position: relative;
		min-height: 335px;
		overflow: hidden;
		background: #fff;
		border-radius: 15px;
		padding: 15px;
	}
	.logo-wrap{text-align: center;}
	.logo-wrap>img{width: 60px;height: auto;margin-bottom: 10px}
	.logo-wrap>.logo-text{
		font-family: sans-serif;
		font-size: 14px;
		letter-spacing: 0.7px;
		color: #444;
		font-weight: 600;
	}
	.aw-content{margin: 10px 0 10px 0;}
	.aw-content>.aw-color{
		color: #a60130;
		font-family: sans-serif;
		font-size: 14px;
		font-weight: 600;
		margin-top: 0px;
		word-spacing: 3px;
		line-height: 20px;
	}
	.aw-content>.title{
		color: #111;
		font-family: sans-serif;
		font-size: 14px;
		font-weight: 600;
		margin-bottom: 0;
	}
	</style>

	<div class="aw-container" id="aworders">

		<?php

		$logo = get_theme_mod( 'custom_logo' );

		$site_name = get_bloginfo('name');

		$aw_week_cutoff = get_option('aw_week_cutoff')?get_option('aw_week_cutoff'):"Wednesday";


		$aw_meal = get_option('aw_meal_number')?get_option('aw_meal_number'):"15 Meals";


		$orders = $_GET['aw_orders'];


		foreach($orders as $order){

			$post = get_post($order);

			$date = $post->post_date;
			$order = wc_get_order($order);
			$aw_assign_driver = get_post_meta($order->get_id(),'aw_assign_driver',true);


			foreach ( $order->get_items() as $item_id => $item ) {
				$variation_id = $item->get_variation_id();
				$quantity = $item->get_quantity();
				$title = get_the_title($variation_id);

				if(preg_match("/{$aw_meal}/i", $title)) {



					for($i = 0;$i <=1;$i++){



						for($q = 0;$q < $quantity;$q++){

							?>

							<div class="post-wrap">
								<div class="order-wrap">

									<div class="logo-wrap">
										<img src="<?=  wp_get_attachment_url($logo);?>"><br>
										<span class="logo-text"><?= $site_name;?></span>
									</div><!-- logo-wrap -->

									<div class="aw-content">
										<p class="title">Customer Name:</p>
										<p class="aw-color"><?php echo $order->get_shipping_first_name() . " " . $order->get_shipping_last_name();?></p>

										<p class="title">Address:</p>
										<p class="aw-color"><?= $order->get_shipping_address_1() . " " . $order->get_shipping_address_2() . ", " . $order->get_shipping_city() . ", " . $order->get_shipping_country();?></p>

										<p class="title">Order Description:</p>
										<p class="aw-color"><?php

										//echo get_the_title($product_id) . "<br>";
										echo get_the_title($variation_id) . "<br>";
										if ($quantity > "1") {
											echo "X" . ($quantity);
										}
										echo "<br><p class='title'>Driver</p><p class='aw-color'>" . $aw_assign_driver . "</p>";


										?></p>
									</div><!-- aw-content -->

								</div><!-- order-wrap -->
							</div><!-- post-wrap -->
							<?php
						}//end for
					}
				}	else{

					for($q = 0;$q < $quantity;$q++){

						?>

						<div class="post-wrap">
							<div class="order-wrap">

								<div class="logo-wrap">
									<img src="<?=  wp_get_attachment_url($logo);?>"><br>
									<span class="logo-text"><?= $site_name;?></span>
								</div><!-- logo-wrap -->

								<div class="aw-content">
									<p class="title">Customer Name:</p>
									<p class="aw-color"><?php echo $order->get_billing_first_name() . " " . $order->get_billing_last_name();?></p>

									<p class="title">Address:</p>
									<p class="aw-color"><?= $order->get_shipping_address_1() . ", " . $order->get_shipping_city() . ", " . $order->get_shipping_country();?></p>

									<p class="title">Order Description:</p>
									<p class="aw-color"><?php

									//echo get_the_title($product_id) . "<br>";
									echo get_the_title($variation_id) . "<br>";
									if ($quantity > "1") {
										echo "X" . ($quantity);
									}

									echo "<br><p class='title'>Driver</p><p class='aw-color'>" . $aw_assign_driver . "</p>";


									?></p>
								</div><!-- aw-content -->

							</div><!-- order-wrap -->
						</div><!-- post-wrap -->
						<?php

					}//end quantity for loop
				}//end elseif

			}//end items foreach loop

		}//end orders loop

		?>

		<!-- aw-container -->
	</div>

	<button id="download" style="display:none" >download</button>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
	<script type="text/javascript">


	window.onload = function () {
		document.getElementById("download")
		.addEventListener("click", () => {
			const invoice = this.document.getElementById("aworders");
			console.log(invoice);
			console.log(window);
			var opt = {
				margin: 0,
				filename: 'delivery-labels.pdf',
				image: { type: 'jpeg', quality: 0.98 },
				html2canvas: { scale: 2 },
				jsPDF: { unit: 'in', format: 'legal', orientation: 'landscape' }
			};
			html2pdf().from(invoice).set(opt).save();
		});

		document.getElementById("download").click();
	}


	</script>
</body>
</html>
