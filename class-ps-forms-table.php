<?php
/**
 * PS Forms Table 
 *
 * @package   Ps_forms_table
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 */

/**
 * Plugin Admin class.
 *
 * TODO: Rename this class to a proper name for your plugin.
 *
 * @package Ps_forms_table
 * @author  Your Name <email@example.com>
 */


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Ps_forms_table extends WP_List_Table {

	protected $columns = array();
	protected $data = array();
	protected $total_items = 0;
	protected $form_name = 0;

	public function __construct($form_name='') {

		$this->form_name = $form_name;

	}

	
	public function prepare_items() {

		global $wpdb;

	  	$this->process_bulk_action();

		$paged = (int)$_GET['paged'] ? (int)$_GET['paged'] : 1;
		$limit = ($paged-1)*10;

		//Select all submit ids for further querying
		$query = $wpdb->prepare("SELECT DISTINCT submit_id FROM `{$wpdb->prefix}ps_form_data` WHERE {$wpdb->prefix}ps_form_data.form_name = %s ORDER BY submit_id DESC LIMIT %d,10",$this->form_name,$limit);

		$result = $wpdb->get_results($query,ARRAY_A);

		$submit_ids = array();

		foreach($result as $s) :
			$submit_ids[] = $s['submit_id'];
		endforeach;

		$query = $wpdb->prepare("SELECT submit_id, name, value, time FROM `{$wpdb->prefix}ps_form_data` WHERE {$wpdb->prefix}ps_form_data.form_name = %s AND submit_id IN(" . implode(',',$submit_ids) . ") ORDER BY submit_id DESC", $this->form_name);
		
		$result = $wpdb->get_results($query);

		$this->data = array();
		$this->columns = array('submitted'=>'Submitted');

		foreach($result as $row) : 

			$name = $row->name;
			$date = new DateTime($row->time);
			$this->data[$row->submit_id]['submitted'] 		= $date->format('d-m-Y \a\t H:i');
			$this->data[$row->submit_id]['submit_id'] 		= $row->submit_id;
			$this->data[$row->submit_id][$name]	 			= self::limit_words($row->value,20);
			if(sizeof($this->columns) < 7)
				$this->columns[$name] = ucwords($name);

		endforeach;
		
		$query = $wpdb->prepare("SELECT DISTINCT submit_id FROM `{$wpdb->prefix}ps_form_data` WHERE {$wpdb->prefix}ps_form_data.form_name = %s",$this->form_name);

		$this->total_items = $wpdb->query($query);


	  $hidden = array('submit_id');

		//Set columns to include checkbox column
		$this->columns = array('cb'=>'<input type="checkbox">') + $this->columns;




	  $this->_column_headers = array($this->columns, $hidden, $sortable);
	  $this->items = $this->data;

		/* -- Register the pagination -- */
		$this->set_pagination_args( array(
			"total_items" => $this->total_items,
			"total_pages" => ceil($this->total_items/10),
			"per_page" => 10,
		) );

		//The pagination links are automatically built according to those parameters

	}


	function limit_words($string, $word_limit)
	{
	    $words = explode(" ",$string);
	    if(sizeof($words) > $word_limit)
	   		return implode(" ",array_splice($words,0,$word_limit)) . '...';
	   	return $string;
	}
 


	function column_default( $item, $column_name ) {

	      return $item[ $column_name ];
	  
	}


	function single_row_columns($item) {

       list($columns, $hidden) = $this->get_column_info();
       foreach ($columns as $column_name => $column_display_name) {
           $class = "class='$column_name column-$column_name'";

           $style = '';
           if (in_array($column_name, $hidden))
                 $style = ' style="display:none;"';

           $attributes = "$class$style";

           if ('cb' == $column_name) {
               echo  "<td $attributes>";
               echo '<input type="checkbox" name="submit_ids[]" value="' . $item['submit_id'] . '" />';
               echo "</td>";
            }
            else {
                echo "<td $attributes><a href=\"" . get_admin_url() . "/admin.php?page=view-ps-submission&submit_id=" . $item['submit_id'] . "\">";
                echo $this->column_default( $item, $column_name );
                echo "</a></td>";
            } 

        } 

    } 

	/**
	 * Define our bulk actions
	 * 
	 * @since 1.2
	 * @returns array() $actions Bulk actions
	 */
	function get_bulk_actions() {
	    $actions = array(
	        'delete' => 'Delete'
	    );

	    return $actions;
	}

	/**
	 * Process our bulk actions
	 * 
	 * @since 1.2
	 */
	function process_bulk_action() {  
  

	    if ( 'delete' === $this->current_action() ) {
	        global $wpdb;

	        if(is_array($_POST['submit_ids'])) :

		        foreach ( $_POST['submit_ids'] as $id ) {
		            $id = absint( $id );
		            $wpdb->query( "DELETE FROM {$wpdb->prefix}ps_form_data WHERE submit_id = $id" );
		        }

		    endif;
	    }
	}

}
