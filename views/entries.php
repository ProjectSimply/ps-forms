<?php
/**
 * Represents the view for the entries page.
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

	<?php if(isset($_POST['submit_ids'])) : ?>

    	<div id="message" class="updated"><p>Selected posts deleted</p></div>

    <?php endif ?>

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php if($form_names = Ps_forms_admin::get_form_name_options()) : ?>

		<label for="form_name" class="align-left">Pick a form:</label>
		<?php $current_form_name = stripslashes($_GET['form_name']); ?>

		<form>
			<select name="form_name" id="form_name_select">

			<?php foreach($form_names as $i=>$row) : ?>

				<?php if(!$current_form_name && $i == 0) $current_form_name = $row->form_name; ?>

				<option<?php if($current_form_name==$row->form_name) echo ' selected="selected'; ?> value="<?php echo $row->form_name; ?>"><?php echo stripslashes($row->form_name); ?></option>

			<?php endforeach; ?>

			</select>
		</form>

	<?php endif; ?>


	<a style="margin:20px 0;" class="button-primary" href="<?php echo admin_url('?page=ps-form-entries&form_name='. stripslashes($_GET['form_name']) .'&csv=1' ) ?>">Download CSV</a>


	<form id="ps-entries-form-main" method="post">

		<?php Ps_forms_admin::get_entries_table($current_form_name); ?>

	</form>

	<!-- TODO: Provide markup for your options page here. -->

</div>

