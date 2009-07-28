<?php

$this->load->library('form_validation');
$this->lang->load('recaptcha');
$this->load->helper('form');
echo "\n";

echo '<fieldset><legend accesskey="N">User Registration</legend>';
echo "\n";

echo form_open('/user/register');
echo "\n";

echo '<h5><label class="form" for="email">Email Address</label></h5>';
echo form_error('email');
echo '<input type="text" name="email" id="email" value="' . set_value('email') . '" size="50" />';
echo "\n";

echo '<h5><label class="form" for="password">Password</label></h5>';
echo form_error('password');
echo '<input type="password" name="password" id="password" value="" size="50" />';
echo "\n";

echo '<h5><label class="form" for="passconf">Password Confirm</label></h5>';
echo form_error('passconf');
echo '<input type="password" name="passconf" id="passconf" value="" size="50" />';
echo "\n";

echo '<h5><label class="form" >Captcha</label></h5>';
echo form_error('recaptcha_response_field');
echo $this->recaptcha->get_html(); 

echo "<br style='clear:both' />";
echo form_submit('registerUser','Register');
echo form_close();

echo '</fieldset>';
