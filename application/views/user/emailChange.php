<?php

View::set_global('field_lang_prefix', 'auth.changeEmail_field_');

echo '<div id="form">';
echo form::open('user/changeEmail');
echo '<h2 class="grey">'.html::chars(__('Change Email Address')) . '</h2>';
echo '<p>Please note that changing your email address will require you to <strong>re-validate</strong> your account.</p>';

echo "<fieldset>";
foreach (array('email') as $field)
{
    echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $form[$field], 'hasError'=>isset($errors[$field]) && $errors[$field], 'class' => 'inline'));
	echo (empty ($errors[$field])) ? '' : '&nbsp<p class="errormsg">'.$errors[$field].'</p><br />';
}
echo '</fieldset>';
 
echo "<fieldset class='left'>";
echo form::submit(null,__('Change email'));
echo '</fieldset>'; 

echo form::close();
echo '</div>';

