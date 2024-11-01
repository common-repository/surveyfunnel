<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://app.surveyfunnel.com
 * @since             2.0.0
 * @package           Survey_Funnel
 *
 * @wordpress-plugin
 * Plugin Name:       Survey Funnel
 * Plugin URI:        http://app.surveyfunnel.com/
 * Description:       Enables you to talk with your customers through Surveys.
 * Version:           2.0.0
 * Author:            WPEka
 * Author URI:        http://club.wpeka.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_survey_funnel() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-survey-funnel-activator.php';
	Survey_Funnel_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_survey_funnel' );


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_survey_funnel() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-survey-funnel-deactivator.php';
	Survey_Funnel_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_survey_funnel' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-survey-funnel.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.0.0
 */
function run_survey_funnel() {

	$sf_plugin = new Survey_Funnel();
	$sf_plugin->run();

}

run_survey_funnel();
