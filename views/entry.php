<?php
/**
 * Represents the view for the single entry page
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 */
?>
<div class="wrap">

	<?php $data = Ps_forms_admin::get_entry($_GET['submit_id']); ?>

	<h2>Submission details for <?php echo $data['form_name']; ?> <a href="<?php echo admin_url('admin.php?page=ps-form-entries&amp;form_name=' . urlencode($data['form_name']) ); ?>" class="add-new-h2">Back</a></h2>

	<h5>Submitted on <?php echo $data['submitted']; ?></h5>

	<table id="ps-form-entry">

		<?php foreach($data['inputs'] as $name => $value) :
			//Generate email data
			$table_data .= '<tr class="data-table-row';
			if($alt) :
				$table_data .= '-alt';
				$alt = false;
			else :
				$alt = true;
			endif;
			$table_data .=  '"><td width="30%">' . $name . '</td><td width="70%">' . $value . '</td></tr>';


		endforeach; ?>

		<?php echo $table_data; ?>

	</table>



	<!-- TODO: Provide markup for your options page here. -->

</div>

