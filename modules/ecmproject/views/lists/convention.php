<tr>
	<td><?php print $row->name; ?></td>
	<td><?php print $row->location; ?></td>
	
	<?php
		foreach ($actions as $action): 
			print '<td>' . $action; '</td>';
		endforeach;
	?>
</tr>