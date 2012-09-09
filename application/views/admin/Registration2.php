<div id='form'>
<br />
<!-- CONTENT: Expect initial convention id for this pass to be associated to. -->
<?php
	View::set_global('field_lang_prefix', '');
	echo form::open("admin/$callback");
?>
	<h3>Step 1: Select a Ticket</h3>
	<p>The ticket that will be given. Any restrictions that have been placed on the ticket are not enforced.</p>
	<fieldset>
		<?php
			$field = 'pass_id';
			$fields[$field]['required'] = @$fields[$field]['adminRequired'];
			echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
		?>
	</fieldset>

	<h3>Step 2: Ticket Information</h3>
	<p>Registration ID Numbers are <strong>final</strong> once assigned and cannot be changed. You must provide <strong>either</strong> a phone number or an email.</p>
	<fieldset>
		<?php
			echo Form::hidden('convention_id', $fields['convention_id']);
			echo new View('admin/InputRegID', array('row' => $row, 'fields' => $fields) );

			foreach (array('gname', 'sname', 'email', 'phone', 'dob', 'status', 'pickupStatus') as $field)
			{
                $fields[$field]['required'] = @$fields[$field]['adminRequired'];
				echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => @$row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
			}
		?>
	</fieldset>

	<fieldset>
		<button type="submit">Create Registration</button>
	</fieldset>
<?php echo form::close(); ?>
</div>
