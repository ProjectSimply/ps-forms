<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that also follow
 * WordPress coding standards and PHP best practices.
 *
 * @package   Ps_forms
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 *
 * @wordpress-plugin
 * Plugin Name: PS Forms
 * Plugin URI:  http://projectsimply.com
 * Description: A very simply development platform for quick n simple ajax forms
 * Version:     1.1.0
 * Author:      Michael Watson
 * Author URI:  http://projectsimply.com
 * Text Domain: psforms-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// TODO: replace `class-plugin-name.php` with the name of the actual plugin's class file
require_once( plugin_dir_path( __FILE__ ) . 'class-ps-forms.php' );
// TODO: replace `class-plugin-admin.php` with the name of the actual plugin's admin class file
if( is_admin() ) {
	require_once( plugin_dir_path( __FILE__ ) . 'class-ps-forms-table.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'class-ps-forms-admin.php' );
}

// Register hooks that are fired when the plugin is activated or deactivated.
// When the plugin is deleted, the uninstall.php file is loaded.
// TODO: replace Plugin_Name with the name of the class defined in `class-plugin-name.php`
register_activation_hook( __FILE__, array( 'Ps_forms', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Ps_forms', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Ps_forms', 'get_instance' ) );

add_action('wp_ajax_nopriv_ps_forms_validate_data',array( 'Ps_forms', 'ps_forms_validate_data'));
add_action('wp_ajax_ps_forms_validate_data',array( 'Ps_forms', 'ps_forms_validate_data'));
add_action('wp_ajax_nopriv_ps_check_field',array( 'Ps_forms', 'ps_check_field'));
add_action('wp_ajax_ps_check_field',array( 'Ps_forms', 'ps_check_field'));

// TODO: replace Plugin_Name_Admin with the name of the class defined in `class-plugin-name-admin.php`
if( is_admin() ) {
	add_action( 'plugins_loaded', array( 'Ps_forms_admin', 'get_instance' ) );
}