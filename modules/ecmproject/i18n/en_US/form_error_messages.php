<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array(
 'email' => Array
    (
        'required' => 'The name cannot be blank.',
        'alpha' => 'Only alphabetic characters are allowed.',
        'length' => 'The name must be between three and twenty letters.',
        'email_exists' => 'Email already exists. Please login or try a different email address.',
        'default' => 'Invalid Input.',
    ),
 'password' => Array
    (
        'required' => 'You must supply a password.',
        'default' => 'Invalid Input.',
    ),
 'confirm_password' => Array
    (
        'required' => 'Must confirm the password.',
        'default' => 'Invalid Input.',
    ),
 'phone' => Array
    (
        'required' => 'You must supply a phone number.',
        'default' => 'Invalid Input.',
    ),
 'sname' => Array
    (
        'required' => 'You must supply a surname.',
        'default' => 'Invalid Input.',
    ),
 'gname' => Array
    (
        'required' => 'You must supply a given name.',
        'default' => 'Invalid Input.',
    ),
  'pass_id' => Array
    (
        'default' => 'Invalid pass, please try again.',
        'invalid_pass_age' => 'That pass is not available for your age, please choose another selection.',
    ),
);
