<!-- Custom Style -->

<?php

wp_enqueue_style( 'aw-mobile-sub',aw_order_url . 'public/css/mobile-subscriptions.css', array(), '1.0', 'all' );
$aw_week_cutoff = get_option('aw_week_cutoff')?get_option('aw_week_cutoff'):"Wednesday";
?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>

<!-- Google Fonts -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Sofia">


<div class="main-container">

  <div class="meals-wrapper">
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

      <div class="meal-wrap <?= $subscription->get_status()  == 'on-hold'?'inactive-meal':'active-meal';?> " data-id="<?= $subscription->get_id();?>">
        <div class="img-wrap">
          <img src="<?= $image[0]?>">
        </div><!-- ===========  img-wrap =========== ------>
        <div class="content-wrap">
          <p class="meal-title"><?= implode("<br>",  $name);?></p>
          <p class="meal-price"><?php echo wp_kses_post( $subscription->get_formatted_order_total() ); ?></p>

          <?php

          $aw_pause_date = get_post_meta($subscription->get_id(),'aw_pause_date',true);
          $sub_next_delivery = get_post_meta($subscription->get_id(),'aw_next_delivery',true);
          $current_time = current_time('timestamp');
          $calculated_next_date = '';


          if($subscription->has_status("active")){

            $aw_last_payment1 = $subscription->get_date('date_paid');
            $aw_last_payment = $subscription->get_date('date_paid');
            $aw_last_payment = new Datetime($aw_last_payment);
            $current_time = current_time('timestamp');

            $cuttofftime_end = new Datetime(date("Y-m-d",StrToTime("Next {$aw_week_cutoff}")) . " 23:59:59");

            $sunday = new Datetime(date("Y-m-d",StrToTime("Next {$aw_week_cutoff}")) . " 23:59:59");
            $sunday->modify("Last Monday");
            $sunday->setTime(00, 00,00);

            if(date('l') == $aw_week_cutoff){

              $cuttofftime_end = new Datetime(date("Y-m-d", $current_time));
              $cuttofftime_end->setTime(23,59,59);

              $sunday = new Datetime(date("Y-m-d", $current_time));
              $sunday->modify("Last Monday");
              $sunday->setTime(00, 00,00);

            }

            $next_payment = $subscription->get_date('next_payment');

            $next_delivery = new Datetime($aw_last_payment1);
            $next_delivery->modify("Next Sunday");

            if($aw_pause_date && $aw_pause_date >= date("Y-m-d",strtotime($aw_last_payment1))){
              //  echo 1;

              $next_payment =$subscription->get_date('next_payment');
              $next_delivery = new Datetime($next_payment);
              $next_delivery->modify("Next Sunday");

              if( $sub_next_delivery != "false" && $sub_next_delivery > $aw_last_payment1){//echo 11;
                ?>

                <p class="meal-payment">Next Delivery:   <?php echo date("m/d/Y",strtotime($sub_next_delivery)); $calculated_next_date = $sub_next_delivery; ?></p>
                <?php
              }else{
                $today = new Datetime(date("Y-m-d H:i:s",$current_time));
                $today->modify("Next Sunday");
                ?>

                <p class="meal-payment">Next Delivery:   <?php echo $today->format("m/d/Y"); $calculated_next_date = $today->format("Y-m-d"); ?></p>

                <?php
              }


            }else{
              //  echo 2;


              if($aw_last_payment >= $sunday && $aw_last_payment <= $cuttofftime_end){
                ?>

                <p class="meal-payment">Next Delivery:   <?php echo $next_delivery->format("m/d/Y"); $calculated_next_date = $next_delivery->format("Y-m-d"); ?></p>

              <?php }else{

                $next_payment =$subscription->get_date('next_payment');
                $sub_next_delivery = get_post_meta($subscription->get_id(),'aw_pause_date',true);


                $next_delivery = new Datetime($next_payment);
                $next_delivery->modify("Next Sunday");

                if( $sub_next_delivery && $sub_next_delivery > date("Y-m-d",$current_time)){
                  ?>

                  <p class="meal-payment">Next Delivery:   <?php echo date("m/d/Y",strtotime($sub_next_delivery)); $calculated_next_date = $sub_next_delivery; ?></p>
                  <?php
                }else{
                  ?>

                  <p class="meal-payment">Next Delivery:   <?php echo $next_delivery->format("m/d/Y"); $calculated_next_date = $next_delivery->format("Y-m-d"); ?></p>

                  <?php
                }


              }


            }

          } ?>

          <div class="meal-option">

            <?php

            $aw_last_payment1 = $subscription->get_date('date_paid');
            $aw_pause_date = get_post_meta($subscription->get_id(),'aw_pause_date',true);
            $sub_next_delivery = get_post_meta($subscription->get_id(),'aw_next_delivery',true);

            $status = $subscription->get_status();

            $chk = true;
            if($sub_next_delivery && $sub_next_delivery != "false"){

              $sub_delv_date = new DateTime($sub_next_delivery);

              $today = new DateTime(date("Y-m-d H:i:s",$current_time));
              //  $today = new DateTime("2021-11-10");
              $dif =   $today->diff($sub_delv_date);

              if($dif->days <= 7){
                $chk = false;
              }

            }

            //  var_dump($chk);

            if( $calculated_next_date ){
              $calculated_next_date_ob = new DateTime( $calculated_next_date);
              $today = new DateTime(date("Y-m-d H:i:s",$current_time));
              //$today = new DateTime("2021-11-10");
              $dif =   $today->diff($calculated_next_date_ob);

              if($dif->days <= 7){
                $chk = false;
              }else{
                $chk = true;
              }

            }

            //    var_dump($calculated_next_date,$chk);


            //  if($aw_pause_date && $aw_pause_date >= date("Y-m-d",strtotime($aw_last_payment1)) && $chk && $status == "active"){
            if( $chk && $status == "active"){

              ?>  <p class="meal-state aw_paused" style="color:orange">
                <?php
                echo "Paused";
                ?> </p> <?php
              }else{

                ?>

                <p class="meal-state <?= $subscription->get_status()  == 'on-hold'?'inactive':'';?>">
                  <?php


                  echo esc_attr( wcs_get_subscription_status_name( $subscription->get_status() ) == "On hold"?"Inactive": wcs_get_subscription_status_name( $subscription->get_status() ));

                  ?></p>

                <?php } ?>

                <?php if($subscription->get_status()  != 'on-hold'){?>
                  <span class="meal-menus"><i class="fas fa-ellipsis-h"></i></span>
                <?php }else{?>

                  <?php

                  $aw_actions = wcs_get_all_user_actions_for_subscription( $subscription, get_current_user_id() );

                  if ( ! empty( $aw_actions ) ) :
                    foreach ( $aw_actions as $key => $action ) :
                      if($key == "reactivate"){
                        ?>
                        <a href="<?php echo esc_url( $action['url'] ); ?>" class="meal-reactive aw-sub-reactivate" data-id="<?= $subscription->get_id();?>"><?php echo esc_html( $action['name'] ); ?></a>

                      <?php }
                    endforeach;
                  endif;

                  ?>

                <?php } ?>
              </div><!--===========  meal-option =========== ------>

            </div><!--===========  content-wrap =========== ------>
          </div><!-- ===========  meal-wrap =========== ------>

        <?php   endforeach;?>


        <button class="btn-meal-prep" id="add_new_plan">Add New Plan</button>

      </div><!--===========  meals-wrapper =========== ------>

    </div> <!--===========  main-container =========== ------>












    <!----- ================================================
    ============    Active Popup ========================== --->

    <div class="aw-overlay" id="activePop" style="display: none">
      <div class="removeActive"></div>
      <div class="active-menu-wrap">

        <button class="btn-meal-bordered" id="pause">Pause</button>
        <button class="btn-meal-bordered" id="switch">Switch</button>
        <button class="btn-meal-bordered" id="addNote">Add Note</button>

      </div><!---- =======  active-menu-wrap ======= --->
    </div><!---- =======  activePop ======= --->


    <!----- ================================================
    ============    Pause Popup ========================== --->

    <div class="aw-overlay" id="pausePop" style="display: none">
      <div class="removePause"></div>
      <div class="pause-popup-wrap">

        <div class="ques-wrap">

          <p class="ques-title">How long do you want to pause your subscriptions for?</p>
          <label for=""><input type="radio" name="ques1"> 1 Week</label>
          <label for=""><input type="radio" name="ques1"> 2 Week</label>
          <label for=""><input type="radio" name="ques1"> 4 Week</label>
          <label for=""><input type="radio" name="ques1"> Indefinitly</label>

          <br>
          <p class="ques-title">Do you want your meals on sunday?</p>
          <label for=""><input type="radio" name="ques2"> Yes</label>
          <label for=""><input type="radio" name="ques2"> No</label>
          <br>
          <p class="ques-title">What has made you to pause your subscription?</p>
          <textarea name="subscription"></textarea>


        </div><!---- =======  ques-wrap ======= --->

        <button class="btn-meal-prep">Pause</button>

      </div><!---- =======  pause-popup-wrap ======= --->
    </div><!---- =======  pausePop ======= --->





    <!----- ================================================
    ============    Switch Popup ========================== --->

    <div class="aw-overlay" id="switchPop" >
      <div class="removeswitch"></div>
      <div class="switch-popup-wrap">

        <?php

        $products = wc_get_products(['status' => "publish",'return' => 'objects','visibility' => 'catalog']);


        $aw_week_cutoff = get_option('aw_week_cutoff')?get_option('aw_week_cutoff'):"Wednesday";
        $current_time = current_time('timestamp');


        $cuttofftime_start = new Datetime(date("Y-m-d H:i:s",$current_time));
        $cuttofftime_start->modify("Last {$aw_week_cutoff} + 1 days");
        $cuttofftime_start->setTime(00,00,00);

        $sunday = new Datetime(date("Y-m-d H:i:s",$current_time));
        $sunday->modify("Last {$aw_week_cutoff}");
        $sunday->modify("Next Sunday");
        $sunday->setTime(23, 59,59);

        if(date('l',$current_time) == $aw_week_cutoff){

          $cuttofftime_start = new Datetime(date("Y-m-d H:i:s",$current_time));
          $cuttofftime_start->modify("+ 1 days");
          $cuttofftime_start->setTime(00,00,00);

          $sunday = new Datetime(date("Y-m-d H:i:s",$current_time));
          $sunday->modify("Next Sunday");
          $sunday->setTime(23, 59,59);

        }

        $today = new DateTime(date("Y-m-d H:i:s",$current_time));

        $delivery_date = new DateTime(date("Y-m-d H:i:s",$current_time));
        $delivery_date->modify("Next Sunday");


        foreach ($products as $key => $product) {
          $today = new DateTime(date("Y-m-d H:i:s",$current_time));
          $product_id = $product->get_id();
          $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), array( 100, 100)  );

          $price = $product->get_price();

          $variations = [];

          if($product->is_type('variable')){

            $price = $product->get_variation_price();
            $variations_ids = $product->get_children();

            foreach ($variations_ids as $key => $value) {
              $v = wc_get_product($value);
              $variations[] = ['id' => $value,
              'title' => get_the_title($value),
              'image' =>  wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), array( 100, 100)  )[0],
              'price' => $v->get_price(),
              'product_id' => $product_id,
            ];
          }

        }


        ?>

        <div class="meal-wrap switch-meal" data-variations='<?= json_encode($variations)?>' data-id="<?= $product_id;?>">
          <div class="img-wrap">
            <img src="<?= $image[0]?>">
          </div><!---- ===========  img-wrap =========== ------>
          <div class="content-wrap">
            <p class="meal-title"><?= $product->get_name();?></p>
            <p class="meal-price">Starting <?= wc_price($price)?> / week</p>
            <?php

            if($today > $cuttofftime_start && $today < $sunday){

              if($today->format("l") != "Sunday"){
                $today->modify("+1 week");
              }
              $today->modify("Next Sunday");

              ?>
              <p class="meal-payment">Next Delivery: <?= $today->format("m/d/Y")?></p>
              <?php
            }else{

              if($today->format("l") != "Sunday"){
                $today->modify("Next Sunday");
              }
              ?>
              <p class="meal-payment">Next Delivery: <?= $today->format("m/d/Y")?></p>
            <?php } ?>
            <div class="meal-option">

              <p class="meal-state">Select This Plan</p>

            </div><!---- ===========  meal-option =========== ------>

          </div><!---- ===========  content-wrap =========== ------>
        </div><!---- ===========  meal-wrap =========== ------>


      <?php } ?>


    </div><!---- =======  switch-popup-wrap ======= --->
  </div><!---- =======  switchPop ======= --->








  <!----- ================================================
  ============    Note Popup ========================== --->

  <div class="aw-overlay" id="notePop" style="display: none">
    <div class="removeNote"></div>
    <div class="note-popup-wrap" >
      <form  method="post" action="">
        <div class="ques-wrap">

          <div class="note-state">

            <p class="ques-title" id="dNote">Delivery Note</p>

            <label class="switch">
              <input type="checkbox" name="note_type" id="noteCheck">
              <span class="slider round"></span>
            </label>

            <p class="regular" id="oNote" style="
            text-align:right;">Order Note</p>

          </div><!---- =======  note-state ======= --->

          <textarea name="note"></textarea>
          <input type="hidden" name="aw_subscription_id" id="aw_subscription_id"/>

        </div><!---- =======  ques-wrap ======= --->

        <button type="submit" name="add_sub_note" class="btn-meal-prep">Submit Note</button>
      </form>
    </div><!---- =======  note-popup-wrap ======= --->
  </div><!---- =======  notePop ======= --->




  <!----- ================================================
  ============    Variation Popup ========================== --->

  <div class="aw-overlay" id="variationPop" style="display: none;background: #000">
    <div class="removeVariation"></div>

    <div class="variation-menu-wrap">

      <label class="btn-meal-bordered variation" ><input typr="radio" name="variation" style="display: none"> 5 Meals - 1 Week</label>
      <label class="btn-meal-bordered variation" ><input typr="radio" name="variation" style="display: none"> 5 Meals / Week</label>
      <label class="btn-meal-bordered variation" ><input typr="radio" name="variation" style="display: none"> 10 Meals / Week</label>
      <label class="btn-meal-bordered variation" ><input typr="radio" name="variation" style="display: none"> 15 Meals / Week</label>

    </div><!---- =======  active-menu-wrap ======= --->


  </div><!---- =======  pausePop ======= --->




  <!----- ================================================
  ============    Plan Popup ========================== --->

  <div class="aw-overlay" id="planPop" style="display: none;background: #000">
    <div class="removePlan"></div>
    <div class="plan-popup-wrap" >
      <form method="post" action="">
        <div class="meal-wrap ">
          <div class="img-wrap">
            <img id="aw-pop-img" src="assets/images/meal.jpg">
          </div><!---- ===========  img-wrap =========== ------>
          <div class="content-wrap">
            <p class="meal-title" id="aw-pop-title">Lean</p>
            <p class="meal-price" id="aw-pop-price"> $60 / week</p>
            <?php
            $today = new DateTime(date("Y-m-d H:i:s",$current_time));

            //var_dump($today->format("Y-m-d"),$cuttofftime_start->format("Y-m-d"),$sunday->format("Y-m-d"));

            if($today > $cuttofftime_start && $today < $sunday){

              if($today->format("l") != "Sunday"){
                $today->modify("+1 week");
              }
              $today->modify("Next Sunday");

              ?>
              <p class="meal-payment">Next Delivery: <?= $today->format("m/d/Y")?></p>

              <script>



              var $ = jQuery;
              $(document).ready(function(){
                setTimeout(function(){
                  $("#switch").off('click');

                  $("#switch").click(function(){
                    alert("We are not able to switch your plan today because we have already purchased your ingredients. If you have any questions, please contact our support.");

                  });

                },500);

              });
              </script>

              <?php
            }else{

              if($today->format("l") != "Sunday"){
                $today->modify("Next Sunday");
              }
              ?>
              <p class="meal-payment">Next Delivery: <?= $today->format("m/d/Y")?></p>
            <?php } ?>
            <!-- <div class="meal-option">

            <p class="meal-state">Select This Plan</p>

          </div><!---- ===========  meal-option =========== ------>

        </div><!---- ===========  content-wrap =========== ------>
      </div><!---- ===========  meal-wrap =========== ------>


      <input type="hidden" name="aw_switch_id" id="aw_switch_id" value="false"/>
      <input type="hidden" name="product_id" id="aw_product_id"/>
      <input type="hidden" name="aw_variation_id" id="aw_variation_id"/>
      <button type="submit" name="aw_add_to_cart" class="btn-meal-prep">Checkout</button>
    </form>


  </div><!---- =======  switch-popup-wrap ======= --->
</div><!---- =======  switchPop ======= --->

<style>

#switchPop{
  height: 93%;
  overflow-y: scroll;
}

#switchPop .switch-popup-wrap{
  top: 74%;
}

</style>




<script type="text/javascript">
var $ = jQuery;
$(document).ready(function(){

  /* ---======== Active Popup =======--- */

  $(".active-meal").click(function(){

    $("#activePop").show();

    var id = $(this).data("id");

    $('#aw_s_id').val(id);
    $("#aw_subscription_id").val(id);

    $("#activePop button").each(function(){
      $(this).attr("data-id",id);
    });

    console.log(id);

    $(".active-menu-wrap").animate({right: '10px'});

  });

  $(".removeActive").click(function(){


    $(".active-menu-wrap").animate({right: '-110%'}, "slow");

    setTimeout(function(){ $("#activePop").hide();  }, 1000);

  });


  /* ---======== Pause Popup =======--- */

  $("#pause").click(function(){

    //  $("#pausePop").show();
    console.log($('#aw_s_id').val());

    var id = $('#aw_s_id').val();
    $("#sub_html").html(null);

    var data = {
      action:"add_subscription_fields",
      id:id,
    };

    $.post("<?= admin_url('admin-ajax.php')?>",data,function(res){

      $("#sub_html").html(res);

    });

    $("input[name='nourl']").val("false");
    $('#myModal1').show();
    $("#myModal1").animate({top: '1%'}, "slow");
    //$(".pause-popup-wrap").animate({top: '50%'}, "slow");

  });

  $(".removePause").click(function(){


    $(".pause-popup-wrap").animate({top: '150%'}, "slow");

    setTimeout(function(){ $("#pausePop").hide();  }, 1000);



  });



  /* ---======== Switch Popup =======--- */

  $("#switch").click(function(){


    $("#switchPop").show();
    var id = $(this).data('id');
    $('#aw_switch_id').val(id);

    $(".switch-popup-wrap").animate({top: '70%'}, "slow");

  });


  $("#add_new_plan").click(function(){


    $("#switchPop").show();
    $('#aw_switch_id').val("false");

    $(".switch-popup-wrap").animate({top: '70%'}, "slow");

  });

  //add_new_plan

  $(".removeswitch").click(function(){


    $(".switch-popup-wrap").animate({top: '150%'}, "slow");

    setTimeout(function(){ $("#switchPop").hide();  }, 1000);


  });

  /* ---======== Note Popup =======--- */

  $("#addNote").click(function(){

    $("#notePop").show();

    $(".note-popup-wrap").animate({top: '50%'}, "slow");

  });

  $(".removeNote").click(function(){


    $(".note-popup-wrap").animate({top: '150%'}, "slow");

    setTimeout(function(){ $("#notePop").hide();  }, 1000);


  });


  $(document).on('click','#noteCheck',function(){
    if($(this).is(':checked')){


      $("#dNote").removeClass("ques-title");
      $("#dNote").addClass("regular");

      $("#oNote").addClass("ques-title");
      $("#oNote").removeClass("regular");

    } else {

      $("#oNote").removeClass("ques-title");
      $("#oNote").addClass("regular");

      $("#dNote").addClass("ques-title");
      $("#dNote").removeClass("regular");

    }
  });




  /* ---======== Variation Popup =======--- */

  $(".switch-meal").click(function(){
    console.log($(this).data("variations"));
    var variations = $(this).data("variations");
    $("#variationPop .variation-menu-wrap").html(null);
    for(var i = 0; i < variations.length; i++){
      var variation = variations[i];
      $("#variationPop .variation-menu-wrap").append(`<label class="btn-meal-bordered variation" data-variation='${JSON.stringify(variation)}' ><input typr="radio" name="variation" value="${variation.id}" style="display: none"> ${variation.title}</label>`);
    }

    $("#variationPop").show();


    $(".variation").click(function(){

      var variation = $(this).data('variation');
      console.log(variation);

      $('#aw-pop-img').attr('src',variation.image);
      $('#aw-pop-title').html(variation.title);
      $('#aw_variation_id').val(variation.id);
      $('#aw_product_id').val(variation.product_id);
      $('#aw-pop-price').html(" $" + variation.price + " / week");
      //$('#aw-pop-img').attr('src',variation.image);

      $("#planPop").show();

      $(".plan-popup-wrap").animate({top: '50%'}, "slow");

    });

    $(".variation-menu-wrap").animate({top: '50%'}, "slow");

  });

  $(".removeVariation").click(function(){


    $(".variation-menu-wrap").animate({top: '150%'}, "slow");

    setTimeout(function(){ $("#variationPop").hide();  }, 1000);


  });


  /* ---======== Plan Popup =======--- */


  $(".removePlan").click(function(){


    $(".plan-popup-wrap").animate({top: '150%'}, "slow");

    setTimeout(function(){ $("#planPop").hide();  }, 1000);


  });


});
</script>
