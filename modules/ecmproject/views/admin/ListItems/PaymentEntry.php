<?php 
//If the row containing the information is set, print out the information.
if(isset($row)) { ?>

<tr>
	<td><?php print $row->type; ?></td>
	<td><?php print $row->mc_gross; ?></td>	
	<td><?php print $row->last_modified; ?></td>
	<td><?php print $row->payment_status; ?></td>	
	<?php
		foreach ($actions as $action): 		
			print '<td>' . $action; '</td>';
		endforeach;
	?>
</tr>

<?php } else { ?>
<tr>
	<th width='25%'>Payment Type</th>
	<th width='15%'>Amount</th>
	<th width='25%'>Last Modified</th>
	<th width='25%'>Status</th>
	<th width='5%'>Edit</th>
	<th width='5%'>Delete</th>
</tr>
<?php } ?>


