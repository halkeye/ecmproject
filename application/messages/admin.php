<?php 
 
return array( 
	'comp_id'      => array( 
		'not_empty' 	=> __('An ID value must be specified.'), 
		'numeric' 		=> __('ID values must be numeric.'),
    ), 
	'comp_loc'      => array( 
        'in_array' 		=> __('Please select a valid location value.'), 
    ),  
); 

?>