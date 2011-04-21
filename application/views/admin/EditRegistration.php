<div id='form'>
<br />
<!-- CONTENT: Expect initial convention id for this pass to be associated to. -->
<?php 	
	View::set_global('field_lang_prefix', '');	
	echo form::open("admin/$callback"); 	
?>
	<h1>Registration ID</h1>
	<p class='centerID'><?php print htmlspecialchars($row['reg_id'], ENT_COMPAT, "UTF-8") ?></p>
	<p class='center'>Both the Registration ID and the Event it is associated with cannot be modified after creation. 
	This registration is for <strong> <?php print htmlspecialchars($fields['convention_name'], ENT_COMPAT, "UTF-8") ?></strong>.</p>
	
	<h1>Editable Information</h1>
	<p>You must provide <strong>either</strong> a phone number or an email.</p>		
	<fieldset>	
		<?php
			$field = 'pass_id';
			$fields[$field]['required'] = @$fields[$field]['adminRequired'];
			echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
		?>
		<?php	
			echo Form::hidden('convention_id', $fields['convention_id']);	
			
			foreach (array('gname', 'sname', 'email', 'phone', 'status') as $field)
			{							
                $fields[$field]['required'] = @$fields[$field]['adminRequired'];
				echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
			}	
		?>
	</fieldset>	
	
	<fieldset>		
		<button type="submit">Edit Registration</button>
	</fieldset>							
<?php echo form::close(); ?>
</div>
