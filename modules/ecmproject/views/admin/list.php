<br />
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