<?php

View::set_global('field_lang_prefix', 'auth.changePassword_field_');

echo '<div id="form">';
echo form::open('user/changePassword');
echo '<h2 class="grey">'.html::chars(__('Change your password')) . '</h2>';
echo '<p>Type in your new password twice to change it. Make sure to <strong>remember</strong> your new password!</p>';

echo "<fieldset>";

foreach (array('password', 'confirm_password') as $field)
{
    echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $form[$field], 'hasError'=>isset($errors[$field]) && $errors[$field], 'class' => 'inline'));
	echo (empty ($errors[$field])) ? '' : '&nbsp<p class="errormsg">'.$errors[$field].'</p><br />';
}
echo '</fieldset>';
 
echo "<fieldset class='left'>";
echo form::submit(null,__('Change Password'));
echo '</fieldset>'; 

echo form::close();
echo '</div>';

