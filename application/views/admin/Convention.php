<div id='form'>
<br />
<!-- CONTENT: Expect initial convention id for this pass to be associated to. -->
<?php 	
	View::set_global('field_lang_prefix', 'admin.pass_field_conv_');
	
	/* Set default values for unset variables. */
	if (!isset($row['name']))
		$row['name'] = '';
		
	if (!isset($row['location']))
		$row['location'] = '';

	echo form::open("admin/$callback"); 
?>
	<h1>General Information</h1>
	<p>The below information defines a convention - it's name, when it will start, when it will end, and it's location. Of these fields,
	 setting the start and end dates are very important as pass purchases times may depend on them.</p>		
	<fieldset>		
		<?php
			foreach (array('name', 'location') as $field)
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