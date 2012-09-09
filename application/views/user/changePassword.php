<?php

View::set_global('field_lang_prefix', 'auth.changePassword_field_');
echo '<h3>'.html::chars(__('Change your password')) . '</h3>';
echo '<p>Type in your new password twice to change it. Make sure to <strong>remember</strong> your new password!</p>';

echo '<div id="form">';
echo form::open('user/changePassword');
echo "<fieldset>";

foreach (array('password', 'confirm_password') as $field)
{
	echo "<div>";
    echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $form[$field], 'hasError'=>isset($errors[$field]) && $errors[$field], 'class' => 'inline', 'errors' => $errors));
	echo "</div>";
}
echo '</fieldset>';

echo "<fieldset class='left'>";
echo form::submit(null,__('Change Password'), array('class' => 'submit'));
echo '</fieldset>';

echo form::close();
echo '</div>';

