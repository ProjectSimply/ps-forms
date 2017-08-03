<?php
/**
 * PS Forms.
 *
 * @package   Ps_forms
 * @author    Michael Watson <michael@projectsimply.com>
 * @license   GPL-2.0+
 * @link      http://projectsimply.com
 * @copyright 2014 Michael Watson
 */

/**
 * Plugin class.
 *
 *
 * @package Ps_forms
 * @author  Michael Watson <michael@projectsimply.com>
 */

require_once(dirname(__FILE__) . '/vendor/MailChimp.php');

class Ps_forms {

	const VERSION = '1.1.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * The variable name is used as the text domain when internationalizing strings of text.
	 * Its value should match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'ps-forms';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Define custom functionality. Read more about actions and filters: http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		add_action( 'TODO', array( $this, 'action_method_name' ) );
		add_filter( 'TODO', array( $this, 'filter_method_name' ) );


	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 *@return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
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
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $network_wide  ) {
				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::single_activate();
				}
				restore_current_blog();
			} else {
				self::single_activate();
			}
		} else {
			self::single_activate();
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $network_wide ) {
				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::single_deactivate();
				}
				restore_current_blog();
			} else {
				self::single_deactivate();
			}
		} else {
			self::single_deactivate();
		}
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {
		if ( 1 !== did_action( 'wpmu_new_blog' ) )
			return;

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {
		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";
		return $wpdb->get_col( $sql );
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
			
	   global $wpdb;

	      
	   $sql = "CREATE TABLE {$wpdb->prefix}ps_form_data (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			  name tinytext NOT NULL,
			  value text,
			  submit_id mediumint(9) NOT NULL,
			  form_name tinytext NOT NULL,
			  ip tinytext NOT NULL,
		      UNIQUE KEY id (id)
		      );

		      CREATE TABLE {$wpdb->prefix}ps_forms_validation_rules (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  name tinytext NOT NULL,
			  rule tinytext,
			  message  tinytext,
			  form_name tinytext NOT NULL,
		      UNIQUE KEY id (id)
	  		  );
				
			  CREATE TABLE {$wpdb->prefix}ps_forms_settings (
				  id mediumint(9) NOT NULL AUTO_INCREMENT,
				  html_file tinytext,
				  css_file tinytext,
				  logo_id tinytext,
				  email_address tinytext,
				  form_name tinytext NOT NULL,
			      UNIQUE KEY id (id)
	  		  );

	  		  CREATE TABLE {$wpdb->prefix}ps_forms_global_settings (
				  id mediumint(9) NOT NULL AUTO_INCREMENT,
				  option_name tinytext,
				  option_value tinytext,
			      UNIQUE KEY id (id)
	  		  );
		      
		      CREATE TABLE {$wpdb->prefix}ps_forms_validation_messages (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  name tinytext,
			  rule tinytext,
			  form_name tinytext NOT NULL,
			  default_message mediumint(9) NOT NULL,
			  message text NOT NULL,
		      UNIQUE KEY id (id)
	  		  );

			  INSERT INTO {$wpdb->prefix}ps_forms_validation_messages (rule,default_message,message) VALUES
			  	('required',1,'You need to fill in this field.'),
				('email',1,'Needs to be a valid email'),
				('integer',1,'Needs to be a number'),
				('is_phone',1,'Needs to be a phone number'),
				('is_mobile',1,'Needs to be a mobile')

			";


	   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	   dbDelta( $sql );

	}

	// private static function ps_install_data() {
	//    global $wpdb;
	//    $welcome_name = "Mr. WordPress";
	//    $welcome_text = "Congratulations, you just completed the installation!";

	//    $rows_affected = $wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'name' => $welcome_name, 'text' => $welcome_text ) );
	// }

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		
	   // Don't really want to do this. Just for testing purposes. REMEMBER!!!!!

	   global $wpdb;

	   //For debugging, remove everything
	   //What to do with a deactivate? I'm not sure
	   $sql = 	"DROP TABLE IF EXISTS {$wpdb->prefix}ps_form_data, {$wpdb->prefix}ps_forms_validation_rules, {$wpdb->prefix}ps_forms_global_settings, {$wpdb->prefix}ps_forms_settings, {$wpdb->prefix}ps_forms_validation_messages";
	   //";

	   $wpdb->query( $sql );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), array(), self::VERSION );
    	wp_localize_script($this->plugin_slug . '-plugin-styles', 'ajaxURL', admin_url('admin-ajax.php'));
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/psforms.js', __FILE__ ), array( 'jquery' ), self::VERSION );
		wp_localize_script($this->plugin_slug . '-plugin-script', 'ajaxURL',  admin_url( 'admin-ajax.php' ) );
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

	/**
	 * Ajax handler for form submission
	 *
	 * @since    1.0.0
	 *
	 * @param   <NONE>
	 */


	public function ps_check_field($data = null) {

		$name = $_GET['name'];
		$value = $_GET['value'];
		$form_name = $_GET['form'];

		//Get validation rules for this form
		$validation_rules = self::get_validation_rules($form_name);
		$validation_messages = self::get_default_validation_messages();

		$name = str_replace('ps-', '', $name);

		//Check if there's a validation rule
		if(array_key_exists($name,$validation_rules)) :

			//Loop all validation rules for this name.
			//There might be multiple.
			foreach($validation_rules[$name] as $rule) :

				//create our function name for checking
				$func = 'ps_forms_validate_' . $rule['rule'];

				//Just in case, let's check that this validation function exists
				//As we're screwing about with variable function names here
				if(method_exists(Ps_forms,$func)) :

					//Validation returned false, so set errors
					//And set the valid flag to false.
					if(!self::$func($value)) :

						if(!$error = $rule['message'])
							$error = $validation_messages[$rule['rule']];

						echo json_encode($error); 
						die;
						

					endif;

				endif;

			endforeach;

		else :
			echo json_encode('no validation');
			die;

		endif;

		echo json_encode('success'); 
		die;
	}


	public function ps_forms_validate_data($inputs = null, $ajax = true) {

		//These are temporary. Need to be settings
		$validation_error_message = "Sorry, some fields have yet to be filled in correctly.";
		$error_message = "Oops, there was an error saving this form.";
		$success_message = "Thank you for making an enquiry with us. We will be in touch very soon.";
		$too_soon_message = "You've already submitted, please wait a minute before resubmitting.";

		//Validation messages. 
		//We should have defaults, that can be overwritten 
		//in settings. For sure!
		$validation_messages = self::get_default_validation_messages();


		global $wpdb;

		//Get everything coming our way from AJAX, if we're ajaxed
		if($inputs == null) {
			$inputs = $_GET['inputs'];
		}

		$form_name = $inputs['ps-form-name']['value'];

		//Get validation rules for this form
		$validation_rules = self::get_validation_rules($form_name);

		//Get rid of the submit button and form name inputs
		unset($inputs['ps-submit']);
		unset($inputs['ps-form-name']);

		//Temp merge vars for Mailchimp
		$merge_vars = array();

		//Remove our prefixes
		$temp = array();

		foreach($inputs as $key => $input) :

			$key = str_replace('ps-', '', $key);
			$temp[$key] = $input['value'];

			// if we have any fields that are to be integrated with mailchimp, we add them to the $merge_vars array
			if($input['mc']){
				$merge_vars[$input['mc']] = $input['value'];
			}

		endforeach;

		$inputs = $temp;

		//Validation Time
		//First we set an empty array to store errors in
		//The key is the field name from the form, 
		//and the value will be validation text for returning to the view
		$errors = array();

		//Set a flag for if the form's valid or not.
		$valid = true;

		//The form name data variable hasn't been set, so
		//let's return an error
		if(!$form_name) :

			$data['message'] = $error_message;
			$data['errors'] = $errors;
			$valid = false;

			if($ajax == true) :
				echo(json_encode( $data));

				exit;
			else :
				return $data;
			endif;


		endif;

		//Let's do a flood check
		$ip = $_SERVER['REMOTE_ADDR'];
		$last_submit = $wpdb->get_row("SELECT time AS latest_time FROM {$wpdb->prefix}ps_form_data WHERE ip='$ip' AND time > NOW() - INTERVAL 1 SECOND");
		
		if($last_submit) :

			$data['message'] = $too_soon_message;
			$data['errors'] = $errors;
			$valid = false;

			if($ajax == true) :
				echo(json_encode( $data));

				exit;
			else :
				return $data;
			endif;

		endif;



		//Loop  through all fields submitted
		foreach($inputs as $name => $value) :

			//Check if there's a validation rule for the current field
			if(array_key_exists($name,$validation_rules)) :

				//Loop all validation rules for this name.
				//There might be multiple.
				foreach($validation_rules[$name] as $rule) :

					//create our function name for checking
					$func = 'ps_forms_validate_' . $rule['rule'];

					//Just in case, let's check that this validation function exists
					//As we're screwing about with variable function names here
					if(method_exists(Ps_forms,$func)) :

						//Validation returned false, so set errors
						//And set the valid flag to false.
						if(!self::$func($value)) :

							$valid = false;
							if(!$errors['ps-'.$name] = $rule['message'])
								$errors['ps-'.$name] = $validation_messages[$rule['rule']];


						endif;

					endif;

				endforeach;

			endif;

		endforeach;


		//The form did not pass validation. Return the errors.
		if($valid == false) :

			$data = array(
					'errors' 	=> $errors,
					'message'	=> $validation_error_message
			);

			if($ajax == true) :
				echo(json_encode( $data));

				exit;
			else :
				return $data;
			endif;

		endif;

		//Submission! Get the max value from the db of submit ids
		//So we can increment it and add a new one 
		$submit_id = $wpdb->get_var("SELECT max(submit_id) FROM {$wpdb->prefix}ps_form_data");

		if(!$submit_id)
			$submit_id = 0;

		$submit_id++;

		//Build the beginning of a query
		$query = "INSERT INTO " . $wpdb->prefix . "ps_form_data (name,time, value, submit_id,form_name,ip) VALUES";

		//Loop the fields and add to the MySQL and template as we go
		$i=1;
		$table_data = '';

		$alt = false;

		foreach($inputs as $name => $value) :

			//Generate email data
			$table_data .= '<tr class="data-table-row';
			if($alt) :
				$table_data .= '-alt';
				$alt = false;
			else :
				$alt = true;
			endif;
			$table_data .=  '"><td width="30%">' . $name . '</td><td width="70%">' . $value . '</td></tr>';

			$query .= $wpdb->prepare("(%s,now(),%s,%d,%s,%s)",$name,$value,$submit_id,$form_name,$ip);

			if($i!=sizeof($inputs))
				$query .= ',';
			else
				$query .= ';';

			$i++;

		endforeach;

		require_once( plugin_dir_path( __FILE__ ) . 'class-ps-forms-admin.php' );
		$settings = Ps_forms_admin::get_form_settings($form_name );


		//Set some defaults
		$html_file = dirname(__FILE__) . '/email-templates/default.html';
		$css_file = dirname(__FILE__) . '/email-templates/default.css';
		$image_file = '';
		$email_address = get_option('admin_email');


		//If settings have been set, let's pick the css and html files
		if($settings !== null) :

			if($settings->html_file)
				$html_file = $settings->html_file;
			if($settings->css_file)
				$css_file = $settings->css_file;
			if($settings->logo_id)
				$image_file = wp_get_attachment_image($settings->logo_id);
			if($settings->email_address)
				$email_address = $settings->email_address;

		endif;

		//Let's search the html for our marked up template links and replace with our table of
		//form submissions.

		$html = file_get_contents($html_file);
		$css = file_get_contents($css_file);


		$html = str_replace('{{FORM_DATA_ROWS}}', $table_data, $html);
		$html = str_replace('{{SITENAME}}', get_bloginfo('name'), $html);
		$html = str_replace('{{FORMNAME}}', $form_name, $html);
		$html = str_replace('{{LOGO}}', $image_file, $html);

		//We'll use the csstoinlinestyles class to sort out the string
		require_once( plugin_dir_path( __FILE__ ) . '/vendor/CssToInlineStyles.php' );
		$email_html = new CssToInlineStyles($html,$css);
		$email_html = $email_html->convert();

		//With our magnificently created table data, we can insert this into the database and send as an email.
		$wpdb->query($query);

		//Send email
		$headers[] = "Content-type: text/html";
		wp_mail($email_address,'Form has been submitted',$email_html,$headers);

		$data = array(
			'message' => $success_message
		);


		//Mailchimp bits
		$mailchimp_api_key = Ps_forms_admin::get_mailchimp_api_key();

		if($mailchimp_api_key && isset($inputs['Email'])) :

			$MailChimp = new \Drewm\MailChimp($mailchimp_api_key);

			$mailchimpListId = $inputs['mailchimp-list-id'];

			$result = $MailChimp->call('lists/subscribe', array(
			                'id'                => $mailchimpListId,
			                'email'             => array('email'=>$inputs['Email']),
			                'merge_vars'		=> $merge_vars,
			                'double_optin'      => false,
			                'update_existing'   => true,
			                'replace_interests' => false,
			                'send_welcome'      => false
			            ));

		endif;


		if($ajax == true)
			echo(json_encode($data));
		else
			return $data;
		exit;
		
	}


	private static function ps_forms_validate_required($value=null) {
		if(!$value)
			return false;
		return true;
	}


	private static function ps_forms_validate_email($value=null) {
		if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
			return false;
		}
		return true;
	}


	private static function ps_forms_validate_integer($value=null) {
		if(!filter_var($value, FILTER_VALIDATE_INT)) {
			return false;
		}
		return true;
	}


	private static function ps_forms_validate_is_phone($value=null){

	     $stripped_value = preg_replace("/[^0-9]/", "", $value);

	    if(strlen($stripped_value) === 11) :
	        if(substr($stripped_value, 0, 2) === "07" ||substr($stripped_value, 0, 2) === "02" ||substr($stripped_value, 0, 2) === "01") :
	            return true;
	        endif;
	    endif;
	}


	private static function ps_forms_validate_is_mobile($value=null){

	    $stripped_value = preg_replace("/[^0-9]/", "", $value);

	    if(strlen($stripped_value) === 11) :
	        if(substr($stripped_value, 0, 2) === "07") :
	            return true;
	        endif;
	    endif;

	    return false;
	}


	public function get_validation_rules($form_name='') {

		global $wpdb;
		$results = $wpdb->get_results($wpdb->prepare("SELECT name,rule,message FROM {$wpdb->prefix}ps_forms_validation_rules WHERE form_name=%s",$form_name));
		$return = array();
		foreach($results as $result) :

			$return[$result->name][] = array('rule'=>$result->rule,'message'=>$result->message);

		endforeach;

		return $return;
	}


	public function get_default_validation_messages() {
		global $wpdb;
		$results = $wpdb->get_results($wpdb->prepare("SELECT rule,message FROM {$wpdb->prefix}ps_forms_validation_messages WHERE default_message=%d",1));
		$return = array();
		foreach($results as $result) :

			$return[$result->rule] = $result->message;

		endforeach;

		return $return;
	}


	//This is the nonjs fallback
	public function js_fallback_check()
	{
		if(!$_POST['ps-submit'])
			return false;

		$inputs = $_POST;
		$data = self::ps_forms_validate_data($inputs, false);
		$errors = '';

		if(is_array($data['errors'])) :
			$box_class = 'alert';
			if(!empty($data['errors'])) :

				$errors .= '<ul>';

				foreach($data['errors'] as $key => $error) :

					$errors .= '<li>' . $key . ': ' . $error . '</li>';

				endforeach;

				$errors .= '</ul>';

			endif;

		else :
			$box_class = 'success';

		endif;

		if($data['message']) :
			echo '<div class="alert-box ' .  $box_class . '">' . $data['message'];
			echo $errors;
			echo '</div>';
		endif;

	}


}