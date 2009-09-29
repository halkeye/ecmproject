<tr>
	<td><?php print $row->id; ?></td>
	<td><?php print $row->email; ?></td>
	<td><?php print $row->statusToString(); ?></td>
	<td>
	<?php 
		if (isset($row->login))
			print date("M j, Y H:i", $row->login);		
		else
			print '--';	
	?>
	</td>	
	<?php
		foreach ($actions as $action): 		
			print '<td>' . $action; '</td>';
		endforeach;
	?>
</tr>