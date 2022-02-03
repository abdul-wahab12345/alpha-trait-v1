<?php
/*
Plugin Name: Alpha Trait
Description: A simple access to account page
Author: Sol Yousefi
Version: 1.6
*/

define( 'EDD_STORE_URL', 'https://citymealprep.com' );
define( 'EDD_ITEM_ID', 78 );
define( 'EDD_ITEM_NAME', 'Alpha Trait' );
define('AW_PLUGIN_VERSION','1.6');
define( 'EDD_PLUGIN_LICENSE_PAGE', 'alpha-trait-license' );
define("AW_PLUGIN_FILE",__FILE__);
define("Orders_Management_PATH",plugin_dir_path(__FILE__) . "orders-management");









function at_admin_menu() {

  add_menu_page(

    __( 'Alpha Trait', 'my-textdomain' ),

    __( 'Alpha Trait', 'my-textdomain' ),

    'manage_options',

    'alpha-trait',

    'my_admin_page_contents',

    plugin_dir_url( __FILE__ ) . 'wpdashicon.png',

    3

  );

}



add_action( 'admin_menu', 'at_admin_menu' );



function my_admin_page_contents() {

  ?>

  <h1>

    <?php esc_html_e( 'Welcome to Alpha Trait', 'my-plugin-textdomain' ); ?>

  </h1>

  <?php

  echo '<p>To manage your account please visit your Alpha Trait Account page using My Account button below</p>';
  echo '<a href="https://alphatrait.com/my-account/" target="_blank">
  <button style="background-color:#007CBA; color:white; padding:10px 25px; border-radius:5px; border:0px;">My Account</button></a>';

}



function register_my_plugin_scripts() {

  wp_register_style( 'my-plugin', plugins_url( 'ddd/css/plugin.css' ) );

  wp_register_script( 'my-plugin', plugins_url( 'ddd/js/plugin.js' ) );

}



add_action( 'admin_enqueue_scripts', 'register_my_plugin_scripts' );



function load_my_plugin_scripts( $hook ) {

  // Load only on ?page=sample-page

  if( $hook != 'toplevel_page_sample-page' ) {

    return;

  }

  // Load style & scripts.

  wp_enqueue_style( 'my-plugin' );

  wp_enqueue_script( 'my-plugin' );

}



add_action( 'admin_enqueue_scripts', 'load_my_plugin_scripts' );

/*
*Including orders management plugin inside the alphatrait plugin
*/

require_once plugin_dir_path( __FILE__ ) . "orders-management/orders-management.php";

/*
*Including account management plugin inside the alphatrait plugin
*/

require_once plugin_dir_path( __FILE__ ) . "account-management/account-management.php";


/*
*Including updator
*/

require_once plugin_dir_path( __FILE__ ) . "edd-updator.php";



?>
