<?php

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
echo '<h1>'.html::specialchars(Kohana::lang('convention.registration_form_header')) . '</h1>';
echo '<p>'.html::specialchars(Kohana::lang('convention.form_required')) . '</p>';

echo "<fieldset>";
foreach (array('gname','sname', 'badge', 'dob', 'email', 'phone','cell', 'address', 'econtact', 'ephone', 'heard_from', 'attendance_reason') as $field)
{
    echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $form[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
}
echo '</fieldset>';

    
echo '<h1>'.html::specialchars(Kohana::lang('convention.registration_select_pass_header')) . '</h1>';
echo '<p>'.html::specialchars(Kohana::lang('convention.registration_select_pass_desc')) . '</p>';

echo '<fieldset>';
{
    $field = 'pass_id';
    echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $form[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
}
echo '</fieldset>';
        
echo "<fieldset class='left'>";
echo form::submit(null,Kohana::lang('convention.registration_submit'));
echo '</fieldset>'; 

echo form::close();

