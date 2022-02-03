<?php



/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
 define( 'ORDERS_MANAGEMENT_VERSION', '1.0.0' );
define( 'aw_order_url', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-orders-management-activator.php
 */
function activate_orders_management() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-orders-management-activator.php';
	Orders_Management_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-orders-management-deactivator.php
 */
function deactivate_orders_management() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-orders-management-deactivator.php';
	Orders_Management_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_orders_management' );
register_deactivation_hook( __FILE__, 'deactivate_orders_management' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-orders-management.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_orders_management() {

	$plugin = new Orders_Management();
	$plugin->run();

}
run_orders_management();
