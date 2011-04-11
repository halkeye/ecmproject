<?php
	$reg_id = $row['reg_id'];
	$reg_id_pieces = explode('_', $reg_id);
	
	if (!isset($reg_id_pieces[1])) { $reg_id_pieces[1] = ''; }
	if (!isset($reg_id_pieces[2])) { $reg_id_pieces[2] = ''; }
	
	echo Form::label('reg_id', 'Registration ID' . '<span class="required">*</span>');
	echo Form::input('reg_id_cid', $row['convention_id'], array('readonly' => 'readonly', 'class' => 'short_inline'));	
	echo Form::select('reg_id_loc', array(), $reg_id_pieces[1], array('class' => 'inline'));	
	echo Form::input('reg_id_id',  $reg_id_pieces[2], array('class' => 'short_inline'));		
	echo "<br />";
	
	
?>