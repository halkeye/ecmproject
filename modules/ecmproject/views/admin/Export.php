<div id='form'>
<br />
<!-- CONTENT: Expect initial convention id for this pass to be associated to. -->
<?php 	
	View::set_global('field_lang_prefix', 'admin.reg_field_');	
	echo form::open("admin/$callback"); 
?>
	<h1>Step 1: Select Convention</h1>
	<p>All registrations are for a particular convention. </p>		
	<fieldset>		
		<?php
			foreach (array('convention_id') as $field)
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