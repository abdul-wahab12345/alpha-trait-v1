
<style>

#next_delivery_date{
  max-width: 250px;
  margin: 10px;
}

/* The Modal (background) */
.modal1 {
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
  padding: 30px;
  border: 1px solid #888;
  width: 80%;
  border-radius: 20px;
  border-radius:20px;
  box-shadow: 0px 0px 15px 4px rgb(0 0 0 / 30%);
}

/* The Close Button */
.close1 {
  color: #aaaaaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close1:hover,
.close1:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
}

.close2 {
  color: #aaaaaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close2:hover,
.close2:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
}

.form-text-normal {
  font-weight:400;
  margin-bottom: 10px;
}

.form-text-bold {
  font-weight:700;
}

.pause-duration {
  margin-bottom:30px;
}

.control-radio {
  height: 15px;
  width: 15px;
}

.control-radio input:checked ~ .control_indicator {
  background: #2aa1c0;
}

</style>

<link rel="stylesheet" href="//code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
<!-- The Modal -->
<div id="myModal1" class="modal1">

  <!-- Modal content -->
  <div class="modal-content">
    <span class="close1">&times;</span>
    <p>


      <form action="" method="post">


        <p class="form-text-bold">How long do you want to pause your subscriptions for?</p>

        <div class="pause-duration">

          <input type="radio" required id="age1" name="period" value="1" class="control-radio">
          <label for="age1" class="form-text-normal">1 week</label><br>


          <input type="radio" required id="age2" name="period" value="2" class="control-radio">
          <label for="age2" class="form-text-normal">Pick your next delivery date</label><br>

          <div style="margin-bottom:10px;" id="datepick">
            <label for="age2" class="form-text-normal">Next delivery date</label><br>
            <input type="text" placeholder="Select a date" name="next_delivery" id="next_delivery_date"/><br>

          </div>


          <input type="radio" required id="age4" name="period" value="0" class="control-radio">
          <label for="age4" class="form-text-normal">Indefinitely</label><be>

          </div>

          <?php

          $aw_week_cutoff = get_option('aw_week_cutoff')?get_option('aw_week_cutoff'):"Wednesday";

          $cuttofftime = new Datetime(date("Y-m-d",StrToTime("Last {$aw_week_cutoff} + 1 days")));

          $next_day = new Datetime(date("Y-m-d",StrToTime("Next {$aw_week_cutoff}")) . " 23:59:59" );

          $today = $next_day;

          if(date ('l') == $aw_week_cutoff){
            $today = new Datetime();
          }


          $sub = get_query_var('view-subscription');
          // //
          $subscription  = wcs_get_subscription($sub);
          //
          $dates_to_update['next_payment'] = "2021-10-30 23:00:00";

          ?>

          <div id="sub_html"></div>

          <label style="margin:20px 0px; line-height: 25px;">What has made you to pause your subscription?</label><br>
          <textarea name="reason" required style="width:100%; border-radius:20px; margin-bottom:20px; padding:20px;" rows="5"></textarea>

          <input type="hidden" id='aw_s_id' name="id" >
          <input type="hidden"  name="nourl" value="true">
          <input type="hidden"  name="status" value="on-hold">

          <br>

          <input type="submit" name="aw_pause_1" value="Pause" style="border:none;">

        </form>

      </p>
    </div>

  </div>





  <!-- The Modal -->
  <div id="myModal2" class="modal1">

    <!-- Modal content -->
    <div class="modal-content">
      <span class="close2">&times;</span>
      <p>


        <form action="" method="post" id="form-reactivate">

          <?php


          $aw_week_cutoff = get_option('aw_week_cutoff')?get_option('aw_week_cutoff'):"Wednesday";

          $cuttofftime_start = new Datetime(date("Y-m-d",StrToTime("Last {$aw_week_cutoff} + 1 days")) . " 00:00:00");

          $cuttofftime_end = new Datetime(date("Y-m-d",StrToTime("Last {$aw_week_cutoff}")) . " 23:59:59");

          $sunday = new Datetime(date("Y-m-d",StrToTime("Last {$aw_week_cutoff} + 1 days")));
          $sunday->modify("Next Sunday");
          $sunday->setTime(23, 59,59);

          if(date('l') == $aw_week_cutoff){

            $cuttofftime_start = new Datetime(date("Y-m-d",StrToTime("+ 1 days")) . " 00:00:00");

            $cuttofftime_end = new Datetime(date("Y-m-d") . " 23:59:59");

            $sunday = new Datetime(date("Y-m-d",StrToTime("Next Sunday")) . " 23:59:00");

          }


          $today = new Datetime();

          if($today > $cuttofftime_start && $today < $sunday){

            ?>

            <div>
              <p class="form-text-bold">You will receive your meals the next Sunday because we have already closed our week's menu.</p>
              <input type="radio" required id="meal" name="next_sunday" value="yes" class="control-radio">
              <label for="meal" class="form-text-normal">Ok</label><br>
              <input type="radio" required id="meal1" name="next_sunday" value="no" class="control-radio">
              <label for="meal1" class="form-text-normal">Keep my account on pause.</label><br>

            </div>
          <?php }else{ ?>


            <div>
              <p class="form-text-bold">Do you want your meals on sunday?</p>
              <input type="radio" required id="meal" name="want_meal" value="yes" class="control-radio">
              <label for="meal" class="form-text-normal">Yes</label><br>
              <input type="radio" required id="meal1" name="want_meal" value="no" class="control-radio">
              <label for="meal1" class="form-text-normal">No</label><br>

            </div>


          <?php } ?>



          <br>
          <input type="hidden" id='aw_s_id' name="id" >
          <input type="hidden" id="aw-nourl"  name="nourl" value="true">
          <input type="submit" name="reactive" value="Reactivate" style="border:none;">

        </form>

      </p>
    </div>

  </div>



  <script>



  // Get the modal

  var $ = jQuery;

  $(document).ready(function(){
    $('#datepick').hide();

    $('input[name="period"]').change(function(){

      if($('#age2:checked').length > 0){
        console.log(1234);
        $('#datepick').show().find('input').attr('required','required');
      }else{
        $('#datepick').hide().find('input').removeAttr('required');

      }

    });

    $("#age4").change(function(){

      if($('#age4:checked').length > 0){

      }

    });

    $( "#next_delivery_date" ).datepicker({ dateFormat: 'yy-mm-dd',
    beforeShowDay: function(date) {
      return [date.getDay() === 0,''];
    },
  });

  var aw_sub_id = $('#aw_sub_id');


  if(aw_sub_id.length > 0){
    $('a.suspend').hide();
    $('a.suspend').parent().prepend(`<a href="javascript:" id="myBtn1" class="button pause">Pause</a>`);
    $('#myBtn1').click(function(){
      $('#aw_s_id').val($('#aw_sub_id').val());
      $('#myModal1').show();
    });
  

  }


  $('#form-reactivate').submit(function(e){
    e.preventDefault();
    var url = $('.aw-sub-reactivate').attr('href');
    var next_sunday = $('input[name="next_sunday"]:checked');
    var nourl = $("#aw-nourl").val();
    if(next_sunday.length > 0){
      url += "&next_sunday="+next_sunday.val() + "&nourl="+nourl;
      if(next_sunday.val() == "no"){
        $('#myModal2').hide();
      }else{
        console.log(url);
        window.location.href = url;
      }
    }else{
      var want_meal = $('input[name="want_meal"]:checked').val()
      url += "&want_meal="+want_meal + "&nourl="+nourl;
      console.log(url);
      window.location.href = url;
    }
  });




});

var modal = document.getElementById("myModal1");
var modal1 = document.getElementById("myModal2");


// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close1")[0];
var span1 = document.getElementsByClassName("close2")[0];



// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}

span1.onclick = function() {
  modal1.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
