<?php

View::set_global('field_lang_prefix', 'convention.registration_field_');

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
echo form::open(url::site($url, TRUE));
echo '<h1>'.HTML::chars(__('convention.registration_form_header')) . '</h1>';
echo '<p>' . __('ecmproject.form_required') . '</p>';

echo "<fieldset>";
foreach (array('gname','sname', 'badge', 'dob', 'email', 'phone','cell', 'city', 'prov', 'econtact', 'ephone') as $field)
{
    if (!@$fields[$field]) continue;
    echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $form[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
}
echo '</fieldset>';

    
echo '<h1>'.HTML::chars(__('convention.registration_select_pass_header')) . '</h1>';
echo '<p>'.HTML::chars(__('convention.registration_select_pass_desc')) . '</p>';

echo '<fieldset>';
{
    $field = 'pass_id';
    echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $form[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
}
echo '</fieldset>';

echo '<h1>'.HTML::chars(__('convention.registration_tac_header')) . '</h1>';
echo '<p>'.HTML::chars(__('convention.registration_tac_desc')) . '</p>';

echo '<fieldset>';
echo form::textarea('agree_toc', __('convention.registration_tac'), array('rows'=>15, 'style'=>'width:95%; margin-left: 2%;'));
{
    $field = 'agree_toc';
    $fields[$field]['type'] = 'boolean';
    $fields[$field]['required'] = true;
    if (!isset($form[$field])) $form[$field]=0;
    echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $form[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
}
echo '</fieldset>';
        
echo "<fieldset class='left'>";
echo form::submit(null,__('convention.registration_submit'));
echo '</fieldset>'; 

echo form::close();
echo '</div>';

