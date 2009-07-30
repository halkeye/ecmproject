<?php

$this->load->library('form_validation');
$this->lang->load('recaptcha');
$this->load->helper('form');

if ($object && $object->errors && $object->errors->all)
    foreach ($object->errors->all as $err) { echo '<p class="errormsg">'.$err.'</p>'; } 
echo "\n";

echo '<fieldset><legend accesskey="N">User Registration</legend>';
echo "\n";

echo form_open('/user/register');
echo "\n";

echo '<h5><label class="form" for="email">Email Address</label></h5>';
echo '<input type="text" name="email" id="email" value="' . $object->email . '" size="50" />';
echo "\n";

echo '<h5><label class="form" for="password">Password</label></h5>';
echo '<input type="password" name="password" id="password" value="" size="50" />';
echo "\n";

echo '<h5><label class="form" for="confirm_password">Password Confirm</label></h5>';
echo '<input type="password" name="confirm_password" id="confirm_password" value="" size="50" />';
echo "\n";

foreach ($object->validation as $field)
{
    if ($field['field'] == 'email' || $field['field'] == 'password' || $field['field'] == 'confirm_password' || $field['field'] == 'id')
        continue;
    if (!$field['label']) 
        continue;

    $fieldName = $field['field'];
    echo '<h5><label class="form" for="'.htmlentities($fieldName).'">'.htmlentities($field['label']).'</label></h5>';
    echo '<input type="text" name="'.htmlentities($fieldName).'" id="'.htmlentities($fieldName).'" value="' . htmlentities($object->$fieldName) . '" size="50" />';
    echo "\n";
}

if ($this->config->item('use_captcha'))
{
    echo '<h5><label class="form" >Captcha</label></h5>';
    echo form_error('recaptcha_response_field');
    echo $this->recaptcha->get_html(); 
}

echo "<br style='clear:both' />";
echo form_submit('registerUser','Register');
echo form_close();

echo '</fieldset>';
