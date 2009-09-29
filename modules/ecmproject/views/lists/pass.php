<tr>
	<td><?php print $row->name; ?></td>
	<td><?php print $row->price; //Force price formatting. ?></td> 
	
	<td>
	<?php 
		if (isset($row->startDate))
			print date("M j, Y", $row->startDate);		
		else
			print '--';	
	?>
	</td>
	<td>
	<?php 
		if (isset($row->endDate))
			print date("M j, Y", $row->endDate);		
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