<?php 
//If the row containing the information is set, print out the information.
if(isset($row)) { ?>

<tr>
	<td><?php print html::specialchars($row->name) . (!$row->isPurchasable ? ' <strong>(Private)</strong>' : ''); ?></td>
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
		if (isset($actions))
		{
			foreach ($actions as $action): 
				print '<td>' . $action; '</td>';
			endforeach;
		}		
	?>
</tr>

<?php } else { ?>
<tr>
	<th width='40%'>Name</th>
	<th width='10%'>Price</th>
	<th width='20%'>Start Date</th>
	<th width='20%'>End Date</th>
	<th width='5%'>Edit</th>
	<th width='5%'>Delete</th>
</tr>
<?php } ?>
