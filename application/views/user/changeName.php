<?php

View::set_global('field_lang_prefix', 'auth.changePassword_field_');

echo '<h3>'.html::chars(__('Change your name')) . '</h3>';
echo '<p><strong>Your account name is used on all ticket purchases!</strong> Ensure that it matches the name on accepted identification.</p>';

echo form::open('user/changeName');

echo "<fieldset>";
	foreach (array('gname', 'sname') as $field)
	{
		echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $form[$field], 'hasError'=>isset($errors[$field]) && $errors[$field], 'class' => 'inline', 'errors' => $errors));
	}
echo '</fieldset>';
 
echo "<fieldset>";
echo form::submit(null,__('Change Name'), array('class' => 'submit'));
echo '</fieldset>'; 

echo form::close();

