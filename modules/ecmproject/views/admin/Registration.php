<div id='form'>
<br />
<!-- CONTENT: Expect initial convention id for this pass to be associated to. -->
<?php 	
	View::set_global('field_lang_prefix', 'admin.reg_field_');	
	echo form::open("admin/$callback"); 
?>
	<h1>Step 1: Select Account & Convention</h1>
	<p>All registrations are for a particular convention. By default, the active convention is the selected choice but you can specify a different one
if you need to. NOTE: Once a registration is created, it <strong>cannot</strong> be moved to a different convention.</p>		
	<fieldset>		
		<?php
			foreach (array('email', 'convention_id') as $field)
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