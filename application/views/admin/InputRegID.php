<?php	
	if (!isset($row['comp_loc'])) { $row['comp_loc'] = ''; }
	if (!isset($row['comp_id'])) { $row['comp_id'] = ''; }
	
	echo Form::label('reg_id', 'Registration ID' . '<span class="required">*</span>');
	echo Form::input ('comp_cid', 	$row['convention_id'],  								array('readonly' => 'readonly', 'class' => 'inline'));	
	echo Form::select('comp_loc', 	$fields['comp_loc']['values'],		$row['comp_loc'], 	array('class' => 'inline'));	
	echo Form::input ('comp_id',  	$row['comp_id'], 										array('class' => 'inline'));		
	echo "<br />";
	
	
?>