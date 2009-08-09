<?php

echo "\n";

echo '<fieldset><legend accesskey="N">User Registration</legend>';
echo "\n";

echo form::open('/user/register');
echo "\n";


$fields = array(
    'email' => 'Email',
    'password' => 'Password',
    'confirm_password' => 'Password Confirm',
    'gname' => 'Given Name',
    'sname' => 'Surname',
    'phone' => 'Phone',
);

foreach ($fields as $field => $name)
{
    echo (empty ($errors[$field])) ? '' : '<p class="errormsg">'.$errors[$field].'</p>';
    echo '<h5>'.form::label($field, $name).'</h5>';
    if ($field == 'password' || $field == 'confirm_password')
        echo form::password($field, ($form[$field]));
    else
        echo form::input($field, ($form[$field]));
    echo "<br />\n";
}

/*
if ($this->config->item('use_captcha'))
{
    echo '<h5><label class="form" >Captcha</label></h5>';
    echo form_error('recaptcha_response_field');
    echo $this->recaptcha->get_html(); 
}
*/

echo "<br style='clear:both' />";
echo form::submit('registerUser','Register');
echo form::close();

echo '</fieldset>';
