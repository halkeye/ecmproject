<div id='form'>
<br />
<!-- CONTENT: Expect initial convention id for this pass to be associated to. -->
<?php 	
	View::set_global('field_lang_prefix', 'admin.pass_field_');
	
	/* Set default values for unset variables. */
	if (!isset($row['email']))
		$row['email'] = '';
		
	echo form::open("admin/$callback"); 
?>
	<h3>General Information</h3>
	<p>An account is defined by an email address. Password must be typed in twice for verification.</p>		
	<fieldset>		
		<?php
			foreach (array('email', 'gname', 'sname', 'phone') as $field)
			{
				echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
			}
					
		?>
	</fieldset>
	<h3>Password</h3>
	<p>Set the login password for this account.</p>		
	<fieldset>
			<?php 
			foreach (array('password', 'confirm_password') as $field) {				
				echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => '', 'hasError'=>isset($errors[$field]) && $errors[$field]));				
			}
			?>
	</fieldset>
	<h3>Email Validation Status</h3>
	<p>Admins may override the default email validation status. Setting status to VERIFIED or BANNED will prevent a validation email from going out to the email specified above.</p>		
	<fieldset>	
		<?php 	
		foreach (array('status') as $field)
		{
			echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
		}
		?>
	</fieldset>	
	<fieldset>				
		<button type="submit">Continue</button>
	</fieldset>							
<?php echo form::close(); ?>
</div>