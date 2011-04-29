<?php 
 
return array( 
	'comp_id'		=> array(
		'not_empty'		=> __('Please provide an ID number for the registration ID.'),
		'numeric'		=> __('The ID component must be a number.'),
	),
	'comp_loc'		=> array(
		'in_array'		=> __('Not a valid purchase location!'),
	),
	'comp_cid'		=> array(
		'not_empty'		=> __('á_á'),
	),
); 

?>