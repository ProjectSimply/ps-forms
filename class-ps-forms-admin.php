<?php
/**
 * PS Forms.
 *
 * @package   Ps_forms_admin
 * @author    Michael Watson <michae@projectsimply.com>
 * @license   GPL-2.0+
 * @link      http://projectsimply.com
 * @copyright 2014 Project Simply
 */

/**
 * Plugin Admin class.
 *
 * TODO: Rename this class to a proper name for your plugin.
 *
 * @package Ps_forms_admin
 * @author  Your Name <email@example.com>
 */
class Ps_forms_admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {


		if(isset($_GET['csv']))
		{
			$csv = $this->generate_csv();

			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private", false);
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"report.csv\";" );
			header("Content-Transfer-Encoding: binary");

			echo $csv; die;

		}

		// Call $plugin_slug from initial plugin class. TODO: Rename "Plugin_Name" to the name of your initial plugin class
		$plugin = Ps_forms::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page. TODO: Rename "plugin-name.php" to the name your plugin
		$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . 'ps-forms.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		// Define custom functionality. Read more about actions and filters: http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		add_action( 'TODO', array( $this, 'action_method_name' ) );
		add_filter( 'TODO', array( $this, 'filter_method_name' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == 'toplevel_page_ps-form-entries' || 'settings_page_ps-forms'|| 'toplevel_page_view-ps-submission' ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), Ps_forms::VERSION );
		}


	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == 'toplevel_page_ps-form-entries' || 'settings_page_ps-forms' ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), Ps_forms::VERSION );
		}
		//Include the scripts for media uploader
		if ( $screen->id == 'settings_page_ps-forms' ) {
			wp_enqueue_media();
		}

	}

	/**
	 * Look up entries and parse an array of 'em
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Echos out the whole fricking table of entries
	 */
	public function get_entries_table($form_name=null) {

		$table = new Ps_forms_table($form_name);
		$table->prepare_items();
		$table->display();

	}


	/**
	 * Make the csv 
	 *
	 * @since     1.0.0
	 *
	 * @return    null    
	 */
	public function generate_csv() {

		global $wpdb;

		$form_name = $_GET['form_name'];

		//Select all submit ids for further querying
		$query = $wpdb->prepare("SELECT DISTINCT submit_id FROM `{$wpdb->prefix}ps_form_data` WHERE {$wpdb->prefix}ps_form_data.form_name = %s ORDER BY submit_id DESC",$form_name);

		$result = $wpdb->get_results($query,ARRAY_A);

		$submit_ids = array();

		foreach($result as $s) :
			$submit_ids[] = $s['submit_id'];
		endforeach;

		$query = $wpdb->prepare("SELECT submit_id, name, value, time FROM `{$wpdb->prefix}ps_form_data` WHERE {$wpdb->prefix}ps_form_data.form_name = %s AND submit_id IN(" . implode(',',$submit_ids) . ") ORDER BY submit_id DESC", $form_name);
		
		$result = $wpdb->get_results($query);

		$this->data = array();
		$this->columns = array('submitted'=>'Submitted');

		foreach($result as $row) : 

			$name = $row->name;
			$date = new DateTime($row->time);
			$data[$row->submit_id]['submitted'] 		= $date->format('d-m-Y \a\t H:i');
			$data[$row->submit_id]['submit_id'] 		= $row->submit_id;
			$data[$row->submit_id][$name]	 			= $row->value;
			if(sizeof($this->columns) < 7)
				$this->columns[$name] = ucwords($name);

		endforeach;
		
		// $query = $wpdb->prepare("SELECT DISTINCT submit_id FROM `{$wpdb->prefix}ps_form_data` WHERE {$wpdb->prefix}ps_form_data.form_name = %s",$this->form_name);

		// $this->total_items = $wpdb->query($query);


	 //  $hidden = array('submit_id');

		// //Set columns to include checkbox column
		// $this->columns = array('cb'=>'<input type="checkbox">') + $this->columns;




	 //  $this->_column_headers = array($this->columns, $hidden, $sortable);
	 //  $this->items = $this->data;

		// /* -- Register the pagination -- */
		// $this->set_pagination_args( array(
		// 	"total_items" => $this->total_items,
		// 	"total_pages" => ceil($this->total_items/10),
		// 	"per_page" => 10,
		// ) );

		// //The pagination links are automatically built according to those parameters


		// return 'penis';

	}


	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 * TODO:
		 *
		 * Change 'Page Title' to the title of your plugin admin page
		 * Change 'Menu Text' to the text for menu item for the plugin settings page
		 * Change 'manage_options' to the capability you see fit (http://codex.wordpress.org/Roles_and_Capabilities)
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'PS Forms Settings', $this->plugin_slug ),
			__( 'PS Forms', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_settings_page' )
		);

		add_menu_page('Form Entries','Form Entries','edit_posts','ps-form-entries',array( $this, 'display_plugin_entries_page' ));
		add_menu_page('Form Submission','Form Submission','edit_posts','view-ps-submission',array( $this, 'display_single_entry' ));
		remove_menu_page('view-ps-submission');

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_settings_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Render the entries page 
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_entries_page() {
		if($_GET['csv']) {
			print_r('die'); 
		}
		include_once( 'views/entries.php' );
	}

	public function display_single_entry() {
		include_once( 'views/entry.php' );
	}

	public function get_entry($id) {
		global $wpdb;

		if(!(int)$id)
			return false;

		$query = $wpdb->prepare("SELECT name, value, form_name, ip, time FROM `{$wpdb->prefix}ps_form_data` WHERE submit_id = %d",$id);

		$result = $wpdb->get_results($query);

		foreach($result as $row) : 

			$date = new DateTime($row->time);
			$data['submitted'] 		= $date->format('d-m-Y \a\t H:i');
			$data['form_name'] 		= $row->form_name;
			$data['inputs'][ucwords($row->name)]	 	= $row->value;

		endforeach;

		return $data;
	} 
	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        WordPress Actions: http://codex.wordpress.org/Plugin_API#Actions
	 *        Action Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        WordPress Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Filter Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// TODO: Define your filter hook callback here
	}



	//return all form names submitted as an array
	public function get_form_name_options() {
		global $wpdb;
		
		$result = $wpdb->get_results( "SELECT form_name FROM {$wpdb->prefix}ps_forms_settings" );

		return $result;


	}

	//Update the database with new validation rules
	//That have been saved through $_POST
	public function set_form_settings($data) {
		global $wpdb;

		$form_name = $data['form_name'];
		if($data['new_form'])
			$form_name = $data['new_form'];
		$names = $data['name'];
		$rules = $data['validation_rule'];
		$message = $data['custom-message'];
		
		$result = $wpdb->get_results($wpdb->prepare("SELECT name,rule FROM {$wpdb->prefix}ps_forms_validation_rules WHERE form_name=%s",$form_name));
		
		//Delete existing validation rules for this form
		$query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}ps_forms_validation_rules WHERE form_name=%s",$form_name);
		$wpdb->query($query);

		//Build the beginning of a query
		$query = "INSERT INTO " . $wpdb->prefix . "ps_forms_validation_rules (name,rule,message,form_name) VALUES";

		//Loop the rules and add to the MySQL as we go
		$i=1;
		$c = 0;
		foreach($names as $o => $name) :

			if($name != '' && $rules[$o] != '') :
				$query .= $wpdb->prepare("(%s,%s,%s,%s)",$name,$rules[$o],$message[$o],$form_name);

				if($i!=sizeof($names))
					$query .= ',';
				else
					$query .= ';';
				$c++;
			endif;

			$i++;

		endforeach;

		//If there were  rows to add (they were all blank)
		//insert
		if($c>0)
			$wpdb->query($query);


		//Let's now look at the default validation messsages
		//And update them in the database
		$default_validation = $data['default_validation'];

		$query = '';

		//Loop the messages and update the database as we go
		foreach($default_validation as $rule => $message) :

			//Build the beginning of a query
			$query .= $wpdb->update(
 				$wpdb->prefix . "ps_forms_validation_messages",
 				array(
 						'message' 		=> $message
 					),
 				array(
 						'rule'			 => $rule,
 						'default_message'=> 1
 					),
 				array(
 						'%s'
 					),
 				array(
 						'%s',
 						'%d'
 					)
				);


		endforeach;

		$query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}ps_forms_settings WHERE form_name=%s",$form_name);
		$wpdb->query($query);


		$insert_data = array(
				'email_address' => $data['email_address'],
				'html_file' 	=> $data['html_file'],
				'css_file' 		=> $data['css_file'],
				'logo_id'	 	=> $data['upload_image_id'],
				'form_name'		=> $form_name
			);

		//Insert the settings
		$wpdb->insert($wpdb->prefix . "ps_forms_settings",$insert_data);


		return "Form rules have been updated. You're the best.";


	}

	//Return all form validation rules from the database
	//Returns an array of fields
	public function get_validation_rules($form_name) {
		global $wpdb;

		$result = $wpdb->get_results($wpdb->prepare("SELECT name,rule,message FROM {$wpdb->prefix}ps_forms_validation_rules WHERE form_name=%s",addslashes($form_name)));
		

		//If there's no rules currently set,
		// let's just return an empty row.
		// So that it displays
		if(!is_array($result) || empty($result)) :

			$empty = new stdClass();
			$empty->name = '';
			$empty->rule = '';
			$result = array($empty);


		endif;


		return $result;


	}

	//Return all form settings from the database
	//Returns an array of fields
	public function get_form_settings($form_name) {
		global $wpdb;

		return $wpdb->get_row($wpdb->prepare("SELECT html_file,css_file,logo_id,email_address FROM {$wpdb->prefix}ps_forms_settings WHERE form_name=%s",addslashes($form_name)));
		
	}


	//Get the current default validation message
	//For this validation rule.
	public function get_default_validation_message($rule) {
		
		global $wpdb;

		$result = $wpdb->get_var($wpdb->prepare("SELECT message FROM {$wpdb->prefix}ps_forms_validation_messages WHERE default_message=1 AND rule=%s",$rule));
		
		echo $result;


	}

	//Try and find custom html and css files to use for templating
	public function get_custom_email_templates() {

		$dir = get_template_directory(). '/ps-email-templates/';
		if(!is_dir($dir))
			return false;

	    $dir = new DirectoryIterator($dir);

	    $files = array();

	    foreach($dir as $file) :

	      if($file->getExtension() == 'html') :
	        
	        $files['html'][] = $file->getFilename();
	        
	      endif;

	      if($file->getExtension() == 'css') :
	        
	        $files['css'][] = $file->getFilename();
	        
	      endif;

	    endforeach;


	    return $files;

	}

}