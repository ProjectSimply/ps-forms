<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://projectsimply.com
 * @since      2.0.0
 *
 * @package    ps-forms
 * @subpackage ps-forms/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    ps-forms
 * @subpackage ps-forms/admin
 * @author     Michael Watson michael@projectsimply.com
 */
class PS_Forms_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    		The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
        $this->version = $version;
        
        if(isset($_GET['csv']))
		{
			$csv = $this->generate_csv();

			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private", false);
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"" . 'My Contact Form' . " report.csv\";" );
			header("Content-Transfer-Encoding: binary");

			echo $csv['headers'] .$csv['csv']; die;

		}

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in PS_Forms_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The PS_Forms_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ps-forms-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in PS_Forms_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The PS_Forms_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ps-forms-admin.js', array( 'jquery' ), $this->version, false );

    }
    

    /**
	 * Make the csv 
	 *
	 * @since     2.0.0
	 *
	 * @return    null    
	 */
	public function generate_csv() {

		global $wpdb;

		$form_name = $_GET['form_name'];


		if(!$form_name) :

			$result = $wpdb->get_row("SELECT form_name FROM `{$wpdb->prefix}ps_forms_settings`");
			$form_name = $result->form_name;

		endif;


		//Select all submit ids for further querying
		$query = $wpdb->prepare("SELECT DISTINCT submit_id FROM `{$wpdb->prefix}ps_form_data` WHERE {$wpdb->prefix}ps_form_data.form_name = %s ORDER BY submit_id DESC",$form_name);

		$result = $wpdb->get_results($query,ARRAY_A);

		$submit_ids = array();

        foreach($result as $s) :
            
            $submit_ids[] = $s['submit_id'];
            
		endforeach;

		$query = $wpdb->prepare("SELECT submit_id, name, value, time FROM `{$wpdb->prefix}ps_form_data` WHERE {$wpdb->prefix}ps_form_data.form_name = %s AND submit_id IN(" . implode(',',$submit_ids) . ") ORDER BY submit_id DESC", $form_name);
		
		$result = $wpdb->get_results($query);

		$data       = array();
		$columns    = array('submitted'=>'Submitted');

		foreach($result as $row) : 

			$name = $row->name;
			$date = new DateTime($row->time);
			$data[$row->submit_id]['submitted'] 		= $date->format('d-m-Y \a\t H:i');
			$data[$row->submit_id][$name]	 			= str_replace("\n",' ',str_replace(',','',$row->value));
			$columns[$name] = ucwords($name);

		endforeach;

		$query = $wpdb->prepare("SELECT DISTINCT submit_id FROM `{$wpdb->prefix}ps_form_data` WHERE {$wpdb->prefix}ps_form_data.form_name = %s",$form_name);

		$csv = '';

		foreach($data as $row) :

			$organisedRow = array_merge($columns,$row);
			$csv .= implode(',', $organisedRow);
			$csv .= "\n";
		endforeach;

		return array('csv'=>$csv,'headers'=>implode(',',$columns) . "\n");

	}

}
