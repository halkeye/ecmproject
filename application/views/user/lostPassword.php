<?php

View::set_global('field_lang_prefix', 'auth.lostPassword_field_');

if (count($errors))
{
    foreach ($errors as $field => $error)
    {
        if (!$error) { continue; /* just incase of empty error */ }
        echo '<p class="errormsg">';
        echo $error;
        echo '</p>';
    }
}

echo '<div id="form">';
echo form::open('/user/lostPassword');
echo '<h2 class="grey">'.html::chars(__('Recover your password!')) . '</h2>';
echo '<p>To recover your password for an account, please provide us the account email address! An email will be sent to this email address with more instructions.</p>';

echo "<fieldset>";
foreach (array('email') as $field)
{
    echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $form[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
}
echo '</fieldset>';
 
echo "<fieldset>";
echo form::button('Submit', __('Send Email'), array('type' => 'submit') );
echo '</fieldset>'; 

echo form::close();
echo '</div>';

