<?php


if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ACCOUNT_MANAGEMENT_VERSION', '1.0.0' );


define("AW_PLUGIN_PATH", plugin_dir_path( __FILE__ ));


require plugin_dir_path( __FILE__ ) . 'includes/class-account-management.php';

function run_account_management() {

	$plugin = new Account_Management();
	$plugin->run();

}
run_account_management();
