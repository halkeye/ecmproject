<h3>General Information</h3>
<p>The below information defines an event - the name of the event and it's location (optional)</p>	

<?php echo form::open("admin/$callback"); ?>	
<fieldset>		
	<?php
		foreach (array('name', 'location') as $field)
		{
			$row[$field] = empty($row[$field]) ? '' : $row[$field] = '';
			echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]), 'nomar' => true);
		}		
	?>
</fieldset>	
<fieldset>				
	<button type="submit">Continue</button>
</fieldset>							
<?php echo form::close(); ?>
