<?php

View::set_global('field_lang_prefix', 'auth.changeEmail_field_');

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
echo form::open();
echo '<h1>'.html::specialchars(__('convention.registration_form_header')) . '</h1>';
echo '<p>'.__('ecmproject.form_required') . '</p>';

echo "<fieldset>";
foreach (array('email') as $field)
{
    echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $form[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
}
echo '</fieldset>';
 
echo "<fieldset class='left'>";
echo form::submit(null,__('auth.changeEmail_field_submit'));
echo '</fieldset>'; 

echo form::close();
echo '</div>';

