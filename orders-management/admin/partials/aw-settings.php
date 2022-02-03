<?php

if(isset($_POST['aw_submit'])){

    update_option('aw_week_cutoff',$_POST['aw_week']);
    update_option('aw_meal_number',$_POST['aw_meal_number']);
    update_option('aw_hubspotapikey',$_POST['aw_hubspotapikey']);
    update_option('aw_alphatraitapikey',$_POST['aw_alphatraitapikey']);
    update_option('aw_chef_email',$_POST['aw_chef_email']);
    update_option('aw_pickup_phone',$_POST['aw_pickup_phone']);
    update_option('aw_pickup_address',$_POST['aw_pickup_address']);
    update_option('aw_pickup_city',$_POST['aw_pickup_city']);
    update_option('aw_pickup_postcode',$_POST['aw_pickup_postcode']);

}

$aw_week = get_option('aw_week_cutoff');
$aw_meal = get_option('aw_meal_number');
$aw_hubspotapikey = get_option('aw_hubspotapikey');
$aw_alphatraitapikey = get_option('aw_alphatraitapikey');
$aw_pickup_phone = get_option('aw_pickup_phone');
$aw_chef_email = get_option('aw_chef_email');
$aw_pickup_address = get_option('aw_pickup_address');
$aw_pickup_city = get_option('aw_pickup_city');
$aw_pickup_postcode = get_option('aw_pickup_postcode');

?>

<style>
.aw-container input[type=text],input[type=password], select {
  width: 40% !important;
  padding: 12px 20px !important;
  max-width: 100% !important;
  margin: 8px 0 !important;
  display: inline-block;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

.aw-container input[type=submit] {
  width: 20%;
  background-color: #a60130;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.aw-container input[type=submit]:hover {
  background-color: #760b2a;
}

.aw-container div {
  border-radius: 5px;
  background-color: #f2f2f2;
  padding: 20px;
}
</style>


<h3>Settings</h3>

<div class="aw-container">
  <form action="" method="post">

    <label for="aw_meal_number">For following text in title genrate 2 labels</label><br>
    <input type="text" required id="aw_meal_number" value="<?= $aw_meal;?>" name="aw_meal_number" placeholder="Enter meals title.."><br>

    <label for="aw_week">Cutoff Day</label><br>
    <select id="aw_week" required name="aw_week">
      <option value="">Select Day</option>
      <option <?= $aw_week == "Monday"?"selected":"";?> value="Monday">Monday</option>
      <option <?= $aw_week == "Tuesday"?"selected":"";?> value="Tuesday">Tuesday</option>
      <option <?= $aw_week == "Wednesday"?"selected":"";?> value="Wednesday">Wednesday</option>
      <option <?= $aw_week == "Thursday"?"selected":"";?> value="Thursday">Thursday</option>
      <option <?= $aw_week == "Friday"?"selected":"";?> value="Friday">Friday</option>
      <option <?= $aw_week == "Saturday"?"selected":"";?> value="Saturday">Saturday</option>
      <option <?= $aw_week == "Sunday"?"selected":"";?> value="Sunday">Sunday</option>
    </select><br>

    <label for="aw_hubspotapikey">Hubspot Api Key</label><br>
    <input type="password" required id="aw_hubspotapikey" value="<?= $aw_hubspotapikey;?>" name="aw_hubspotapikey" placeholder="Enter Hubspot api key.."><br>

    <label for="aw_alphatraitapikey">Alphatrait Delivery Api Key <b style="font-size: 17px;">(v2*)</b></label><br>
    <input type="password" required id="aw_alphatraitapikey" value="<?= $aw_alphatraitapikey;?>" name="aw_alphatraitapikey" placeholder="Enter Alphatrait Delivery api key.."><br>

    <label for="aw_chef_email">Chef Email</label><br>
    <input type="text" required id="aw_chef_email" value="<?= $aw_chef_email;?>" name="aw_chef_email" placeholder="Chef Email.."><br>

    <label for="aw_pickup_address">Pickup Address</label><br>
    <input type="text" required id="aw_pickup_address" value="<?= $aw_pickup_address;?>" name="aw_pickup_address" placeholder="Pickup Address.."><br>

    <label for="aw_pickup_city">Pickup City</label><br>
    <input type="text" required id="aw_pickup_city" value="<?= $aw_pickup_city;?>" name="aw_pickup_city" placeholder="Pickup City.."><br>
    <label for="aw_pickup_postcode">Pickup Postcode</label><br>
    <input type="text" required id="aw_pickup_postcode" value="<?= $aw_pickup_postcode;?>" name="aw_pickup_postcode" placeholder="Pickup Postcode.."><br>

    <label for="aw_pickup_phone">Pickup Phone</label><br>
    <input type="text" required id="aw_pickup_phone" value="<?= $aw_pickup_phone;?>" name="aw_pickup_phone" placeholder="Pickup Phone.."><br>

    <input type="submit" name="aw_submit" value="Save Settings">
  </form>
</div>
