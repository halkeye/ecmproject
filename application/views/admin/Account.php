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
	<h1>General Information</h1>
	<p>At a minimum, a badge requires a name and a price. Upon creation, this badge will be available for purchase through
	normal registration unless otherwise specified. </p>		
	<fieldset>		
		<?php
			foreach (array('email') as $field)
			{
				echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
			}
			echo new View('global/_form_field', array('field'=>'password', 'fieldData'=>'text', 'value' => '', 'hasError'=>isset($errors[$field]) && $errors[$field]));			
			echo new View('global/_form_field', array('field'=>'confirm_password', 'fieldData'=>'text', 'value' => '', 'hasError'=>isset($errors[$field]) && $errors[$field]));			
		?>
	</fieldset>
	<h1>Email Validation Status</h1>
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