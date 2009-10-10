<?php

echo "\n";

echo '<fieldset><legend accesskey="N">Registration</legend>';
echo "\n";

echo form::open();
echo "\n";

echo new View('global/_form', array('fields'=>$fields, 'errors'=>$errors, 'form'=>$form));

echo form::submit(null,'Submit');
echo form::close();

echo '</fieldset>';

echo '<!-- ' . var_export($errors, 1) . ' -->';
