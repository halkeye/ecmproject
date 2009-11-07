<div id='form'>
<br />
<!-- CONTENT: Expect initial convention id for this pass to be associated to. -->
<?php 	
	View::set_global('field_lang_prefix', 'convention.registration_field_');	
	echo form::open("admin/$callback"); 	
?>
	<h1>Step 2: General Information</h1>
	<p>NOTE: Age restrictions for badges are <strong>not</strong> enforced when creating a registration as an Administrator.</p>		
	<fieldset>			
		<?php	
			//var_dump($row);
			foreach (array('gname','sname', 'badge', 'dob', 'email', 'phone','cell', 'city', 'prov', 'econtact', 'ephone',) as $field)
			{				
				echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
			}		
		?>
	</fieldset>
	
	<h1>Step 3: Select a Pass</h1>
	<p>NOTE: Age restrictions for badges are <strong>not</strong> enforced when creating a registration as an Administrator.</p>
	<fieldset>
		<?php
			$field = 'pass_id';
			echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
		?>
	</fieldset>
	<fieldset>				
		<button type="submit">Create Registration</button>
	</fieldset>							
<?php echo form::close(); ?>
</div>