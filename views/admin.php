<?php
/**
 * Represents the view for the administration dashboard.
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

<?php //At the page load
if( isset( $_POST["submit"])) : ?>
    
    <?php if($message = Ps_forms_admin::set_form_settings($_POST)) : ?>

    	<div id="message" class="updated"><p><?php echo $message; ?></p></div>

    <?php endif ?>

<?php endif; ?>

<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>


<form action="" method="POST">
	<table class="widefat" id="ps_forms_settings">
		<tbody>
			<tr>
				<td class="label">
					<h3>Validation rules</h3>
					<p>Here you can set all your validation rules, based on the <em>name</em> attribute of any HTML form your write.</p>
				
					<?php if($form_names = Ps_forms_admin::get_form_name_options()) : ?>

						<label>Pick a form to edit: </label>

						<?php $current_form_name = stripslashes($_GET['form_name']); ?>
						<?php if($_POST['new_form']) $current_form_name = $_POST['new_form']; ?>

						<select name="form_name" id="form_name_select">

						<?php foreach($form_names as $i=>$row) : ?>

							<?php if(!$current_form_name && $i == 0) $current_form_name = stripslashes($row->form_name); ?>

							<option<?php if($current_form_name==$row->form_name) echo ' selected="selected'; ?> value="<?php echo stripslashes($row->form_name); ?>"><?php echo stripslashes($row->form_name); ?></option>

						<?php endforeach; ?>

						</select>

						<p>Or</p>

					<?php endif; ?>

					<label>Add a new form:</label>

					<input type="text" name="new_form" id="new_form" />

				</td>
				<td>
					<div class="ps_forms_rules">

						<h4>Rules for form</h4>
						<table>
							<tbody id="rules_wrap">

								<?php $validation_rules = Ps_forms_admin::get_validation_rules( $current_form_name ); ?>

								<?php foreach($validation_rules as $i => $validation_rule) : ?>
							
									<tr id="rule_<?php echo $i; ?>" class="rule_row">

										<td class="name"><input type="text" name="name[<?php echo $i; ?>]" placeholder="Input name" value="<?php echo $validation_rule->name; ?>"></td>
										
								
										<td class="param">

											<select class="select" name="validation_rule[<?php echo $i; ?>]">

												<?php $choices = array(
																	'required' 	=> 'Required',
																	'email'		=> 'Email',
																	'integer'	=> 'Integer',
																	'is_phone'	=> 'Is Phone Number',
																	'is_mobile'	=> 'Is Mobile Number'
												); ?>

												<?php foreach($choices as $value => $choice) : ?>
											
													<option<?php if($validation_rule->rule==$value) echo ' selected="selected"'; ?> value="<?php echo $value; ?>"><?php echo $choice; ?></option>

												<?php endforeach; ?>

											</select>

										</td>

										<td class="custom-message">

											<input type="text" placeholder="Optional message" name="custom-message[<?php echo $i; ?>]" value="<?php echo $validation_rule->message; ?>">

										</td>
										<td class="add">
											<a href="#" class="ps_forms_add_rule button">add another</a>
										</td>
										<td class="remove">
											<a href="#" class="ps_forms_remove_rule button-remove"></a>
										</td>
									</tr>

								<?php endforeach; ?>

							</tbody>
						</table>

						<input type="submit" class="ps-forms-button" name="submit" value="Save Options">
					</div>
				
				</td>
			</tr>

		</tbody>
	</table>

	<table class="widefat" id="ps_default_validation_messages">
		<tbody>

			<tr>
				<td class="label">
					<h3>Default validation messages</h3>
					<p>Here you can set custom default validation messages for all validation types.</p>
				
				</td>
				<td></td>
			</tr>
			<tr>
				<td>
					<div class="ps_forms_validation_messages">

						<table>
							<tbody id="validation_message_wrap">


								<?php foreach($choices as $value => $choice) : ?>

									<tr>

										<td class="label">
									
											<label for="default_validation_<?php echo $value; ?>"><?php echo $choice; ?></label>

										</td>

										<td>
											<input type="text" name="default_validation[<?php echo $value; ?>]" value="<?php Ps_forms_admin::get_default_validation_message($value); ?>">

										</td>

									</tr>

								<?php endforeach; ?>

							</tbody>
						</table>

						<input type="submit" class="ps-forms-button" name="submit" value="Save Options">
					</div>
				
				</td>
			</tr>

		</tbody>
	</table>

	<?php if($current_form_name) : ?>

		<?php $settings = Ps_forms_admin::get_form_settings( $current_form_name ); ?>

		<table class="widefat" id="ps_submission_settings">
			<tbody>



				<tr>
					<td class="label">
						<h3>Submission form settings</h3>
						<p>Here you can set some form settings.</p>
					</td>
					<td></td>

				</tr>
				<tr>
					<td class="label">
						<h3>Pick an email template</h3>
						<p>If you put css &amp; html files in a folder called 'email-templates' in your root template folder you can use them here.</p>
						<?php $files = Ps_forms_admin::get_custom_email_templates(); ?>
					</td>
					<td></td>
				</tr>
				<tr>
					<td class="label">
						<label for="html_file">HTML</h3>
						<select name="html_file">
							<option value="" name="default">Default</option>
							<?php foreach($files['html'] as $file) : ?>

								<option <?php if($file == $settings->html_file) echo 'selected'; ?> name="<?php echo $file; ?>"><?php echo $file; ?></option>

							<?php endforeach; ?>
						</select>
					</td>
					<td></td>
				</tr>
				<tr>
					<td class="label">
						<label for="css_file">CSS</h3>
						<select name="css_file">
							<option value="" name="default">Default</option>
							<?php foreach($files['css'] as $file) : ?>

								<option <?php if($file == $settings->css_file) echo 'selected'; ?> name="<?php echo $file; ?>"><?php echo $file; ?></option>
								
							<?php endforeach; ?>
						</select>
					</td>
					<td></td>
				</tr>
				<tr>
					<td>
						<h4>Logo upload</h4>


						<div class="logo-thumb">

							<?php if($settings->logo_id) : ?>

								<?php echo wp_get_attachment_image($settings->logo_id,'thumbnail'); ?>

							<?php endif; ?>
						</div>

						<label for="upload_image">
						    <input id="upload_image" type="text" size="36" name="logo" value="" /> 
						    <input id="upload_image_id" type="hidden" name="upload_image_id" value="<?php if($settings->logo_id) echo $settings->logo_id; ?>" /> 
						    <input id="upload_image_button" class="button" type="button" value="Upload Image" />
						    <br />Enter a URL or upload an image
						</label>
					</td>
					<td class="label">

						<?php $email_address = $settings->email_address ? $settings->email_address : get_option('admin_email'); ?>	
										
						<label for="email_address">Who will recieve the notification email?</label><br>

						<input type="text" name="email_address" value="<?php echo $email_address; ?>">
					</td>

				</tr>

					
					<td></td>
				<tr>
					<td>
						<input type="submit" class="ps-forms-button" name="submit" value="Save Options">
					</td>
					<td></td>
				</tr>
			</tbody>
		</table>

	<?php endif; ?>

</form>

</div>
