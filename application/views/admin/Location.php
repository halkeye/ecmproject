<div id='form'>
<br />
<!-- CONTENT: Expect initial convention id for this pass to be associated to. -->
<?php 	
	View::set_global('field_lang_prefix', 'field_');
	
	/* Set default values for unset variables. */
	if (!isset($row['prefix']))
		$row['prefix'] = '';
		
	if (!isset($row['location']))
		$row['location'] = '';

	echo form::open("admin/$callback"); 
?>
	<h3>General Information</h3>
	<p>The below information defines a location - a prefix (maximum 5 characters) and the full location name.</p>		
	<fieldset>		
		<?php
			foreach (array('prefix', 'location') as $field)
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