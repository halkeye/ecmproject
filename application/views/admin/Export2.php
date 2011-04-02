<div id='form'>
<br />
<!-- CONTENT: Expect initial convention id for this pass to be associated to. -->
<?php 	
	View::set_global('field_lang_prefix', 'admin.reg_field_');	
	echo form::open("admin/$callback"); 
?>
	<h1>Step 2: Select Passes</h1>
	<p>Select (or unselect) the passes you wish to include in the exported CSV file.</p>		
	<fieldset>		
		<?php
			foreach ($passes as $pass):
				print form::label('p_' . $pass->id, $pass->name);	
				print form::checkbox('p_' . $pass->id, $pass->name, TRUE);
			endforeach;			
		?>
	</fieldset>
	<h1>Step 3: Select Status Values</h1>
	<p>You can choose to include only registrations that have a certain status within the system. For instance, you might want to export only the PAID registrations.</p>		
	<fieldset>		
		<?php
			foreach ($status_values as $k => $v):
				print form::label('s_' . $k, $v);
				print form::checkbox('s_' . $k, $v, TRUE);			
			endforeach;
		?>
	</fieldset>	
	<fieldset>				
		<button type="submit">Continue</button>
	</fieldset>							
<?php echo form::close(); ?>
</div>