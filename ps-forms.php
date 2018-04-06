<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://projectsimply.com
 * @since             2.0.0
 * @package           ps-forms
 *
 * @wordpress-plugin
 * Plugin Name:       PS Forms
 * Plugin URI:        https://projectsimply.com
 * Description:       A very simply development platform for quick n simple ajax forms
 * Version:           2.0.0
 * Author:            Michael Watson
 * Author URI:        https://projectsimply.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ps-forms
 * Domain Path:       /languages
 */

 

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PS_FORMS_VERSION', '2.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ps-forms-activator.php
 */
function activate_ps_forms() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ps-forms-activator.php';
	PS_Forms_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ps-forms-deactivator.php
 */
function deactivate_ps_forms() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ps-forms-deactivator.php';
	PS_Forms_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ps_forms' );
register_deactivation_hook( __FILE__, 'deactivate_ps_forms' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ps-forms.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.0.0
 */
function run_ps_forms() {

	$plugin = new PS_Forms();
	$plugin->run();

}
run_ps_forms();
