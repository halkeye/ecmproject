<h3>Recover your password</h3>
<p>To recover your password for an account, please provide us the account email address! An email will be sent to this email address with more instructions.</p>

<?php echo form::open('/user/lostPassword'); ?>
<fieldset>
	<?php
	foreach (array('email') as $field)
	{
		echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $form[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
	}
	?>
</fieldset>

<fieldset class='left'>
<?php echo form::submit('Submit', __('Send Email'), array('class' => 'submit') ); ?>
</fieldset>

<?php echo form::close(); ?>


