<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array(
 'email' => Array
    (
        'required' => 'An email must be supplied.',
        'alpha' => 'Only alphabetic characters are allowed.',
        'length' => 'The name must be between three and twenty letters.',
        'email_exists' => 'Email already exists. Please login or try a different email address.',
        'email_not_exists' => 'Email does not exist. Please make sure to supply a valid email address.',
        'default' => 'Invalid Input for email.',
    ),
 'password' => Array
    (
        'required' => 'You must supply a password.',
        'default' => 'Invalid Input for password. (Password length must be > 6 characters)',
    ),
 'confirm_password' => Array
    (
        'required' => 'Must confirm the password.',
        'default' => 'Invalid Input for confirmation password.',
    ),
 'phone' => Array
    (
        'required' => 'You must supply a phone number.',
        'default' => 'Invalid Input for phone number.',
    ),
 'ephone' => Array
    (
        'required' => 'You must supply a emergency phone number.',
        'default' => 'Invalid Input for emergency phone number.',
    ),
 'econtact' => Array
    (
        'required' => 'You must supply a emergency contact.',
        'default' => 'Invalid Input for emergency contact.',
    ),
 'sname' => Array
    (
        'required' => 'You must supply a surname.',
        'default' => 'Invalid Input for surname.',
    ),
 'gname' => Array
    (
        'required' => 'You must supply a given name.',
        'default' => 'Invalid Input for given name.',
    ),
  'pass_id' => Array
    (
        'default' => 'Invalid pass, please try again.',
        'invalid_pass_age' => 'That pass is not available for your age, please choose another selection.',
    ),
 'badge' => Array
    (
        'required' => 'You must supply a badge name.',
        'default' => 'Invalid Input for badge.',
    ),
 'address' => Array
    (
        'required' => 'You must supply a valid address.',
        'default' => 'Invalid Input for address.',
    ),
 'city' => Array
  (
		'default' => 'You need to type something in for city!',
  ),
  'prov' => Array
  (
		'default' => 'You need to type something in for your province/state/corresponding term!',
  ),
 'dob' => Array
    (
        'date' => 'You must supply a date of birth.',
        'required' => 'You must supply a date of birth.',
        'default' => 'Invalid Input for date of birth.',
    ),
 'agree_toc' => Array
    (
        'required' => 'You must agree to the Terms and Conditions to continue.',
        'default' => 'You must agree to the Terms and Conditions to continue.',
    ),
 'start_date' => Array
    (
        'required' => 'You must set a valid Start Date.',
        'default' => 'You must set a valid Start Date.',
    ),
  'startDate' => Array
    (
        'required' => 'You must set a valid Start Date.',
        'default' => 'You must set a valid Start Date.',
    ),
 'end_date' => Array
    (
        'required' => 'You must set a valid End Date',
        'default' => 'You must set a valid End Date',
    ),
 'endDate' => Array
    (
        'required' => 'You must set a valid End Date',
        'default' => 'You must set a valid End Date',
    ),
 'name' => Array
    (
        'required' => 'You must provide a name for this item.',
        'default' => 'You must provide a name for this item.',
    ),
 'location' => Array
    (
        'required' => 'You must specify a location!',
        'default' => 'You must specify a location!',
    ),
  'convention_id' => Array
  (
		'default' => 'You must select a convention first!',  
  ),
  'valid_range' => Array
  (
		'default' => 'End date must be after the Start date!',
  ),
  'unique_badge' => Array
  (
		'default' => "You've already created a registration with the same full name as this one.",
  ),
);
