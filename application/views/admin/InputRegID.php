<?php	
	if (!isset($row['comp_loc'])) { $row['comp_loc'] = ''; }
	if (!isset($row['comp_id'])) { $row['comp_id'] = ''; }
	
	//echo "<div>";
	echo Form::label('reg_id', 'Registration ID' . '<span class="required">*</span>', array('class' => 'regid'));
	echo Form::select('comp_loc', 	$fields['comp_loc']['values'],		$row['comp_loc'], 	array('class' => 'comp_loc'));	
	echo Form::input ('comp_cid', 	$row['convention_id'],  								array('readonly' => 'readonly', 'class' => 'comp_prefix'));	
	echo "&nbsp;";
	echo Form::input ('comp_id',  	$row['comp_id'], 										array('class' => 'comp_id'));		
	echo "<br />";
	//echo "</div>";
	
?>