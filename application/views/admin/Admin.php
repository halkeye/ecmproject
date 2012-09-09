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
	<h3>Admin Account</h3>
	<p>Specify the account (email address login) to grant admin access to. <strong>Granting admin access to the registration system is (almost) like giving free reign in our system</strong>.
	</p>

	<fieldset>
		<?php
			foreach (array('email') as $field)
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
