<?php 
 
return array( 
	'gname'		   => array(
		'not_empty' 	=> __('You must specify the first name of the person.'), 
	),
	'sname'		   => array(
		'not_empty' 	=> __('You must specify the last name of the person.'), 
	),
    'comp_id'      => array( 
        'not_empty' 	=> __('An ID value must be specified.'), 
		'numeric' 		=> __('ID values must be numeric.'),
    ), 
	'comp_loc'      => array( 
        'in_array' 		=> __('Please select a valid location value.'), 
    ), 
	'reg_id'		=> array(
		'not_empty'		=> __('The registration ID specified is not valid.'),
	),
); 

?>