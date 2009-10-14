<p class='action'>
<?php
	echo form::open("admin/$cmd_target");
	if (isset($commands))
	{
		foreach ($commands as $cmd):
			print $cmd;
		endforeach;
	}	
	
	//This is so cheap.
	if (isset($hack))
		echo form::submit('submit', 'Change convention');
		
	echo form::close();
?>
</p>
<!-- CONTENT: TODO: Renaming variables to something general. -->
<table width='100%'>	
	<tr>	
		<?php 
			foreach ($headers as $header): 
				print "<th class='header' width=" . $header['width'] . ">" . $header['name'] . "</th>";
			endforeach;
			
		?>
	</tr>
	<?php 

		foreach ($entries as $entry):
			print $entry;
		endforeach;	
	?>
</table>