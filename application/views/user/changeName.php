<?php

View::set_global('field_lang_prefix', 'auth.changePassword_field_');

echo '<div id="form">';
echo form::open('user/changeName');
echo '<h2 class="grey">'.html::chars(__('Change your name')) . '</h2>';
echo '<p><strong>Your account name is used on all ticket purchases!</strong> Ensure that it matches the name on accepted identification.</p>';

echo "<fieldset>";

foreach (array('gname', 'sname') as $field)
{
    echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $form[$field], 'hasError'=>isset($errors[$field]) && $errors[$field], 'class' => 'inline'));
	echo (empty ($errors[$field])) ? '' : '&nbsp<p class="errormsg">'.$errors[$field].'</p><br />';
}
echo '</fieldset>';
 
echo "<fieldset class='left'>";
echo form::submit(null,__('Change Name'));
echo '</fieldset>'; 

echo form::close();
echo '</div>';

