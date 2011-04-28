<?php 
//If the row containing the information is set, print out the information.
if(isset($row)) { ?>

<tr>
	<td><?php print html::chars($row->gname) . ' ' . html::chars($row->sname); ?></td>
	<td><?php print $row->reg_id; ?></td>	
	<td><?php print html::chars($row->email); ?></td>
	<td><?php print $row->statusToString(); ?></td>	
	<?php
		foreach ($actions as $action): 		
			print '<td class="center">' . $action; '</td>';
		endforeach;
	?>
</tr>

<?php } else { ?>
<tr>
	<th width='25%'>Name</th>
	<th width='25%'>Assigned Reg ID</th>
	<th width='20%'>Email</th>
	<th width='20%'>Status</th>
	<th width='5%'>Edit</th>
	<th width='5%'>Delete</th>
</tr>
<?php } ?>


