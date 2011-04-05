<?php 
//If the row containing the information is set, print out the information.
if(isset($row)) { ?>

<tr>
	<td><?php print $row->gname; ?></td>
	<td><?php print $row->sname; ?></td>	
	<td><?php print $row->email; ?></td>
	<td><?php print html::anchor('admin/managePayments/'. $row->id, $row->statusToString()); ?></td>	
	<?php
		foreach ($actions as $action): 		
			print '<td>' . $action; '</td>';
		endforeach;
	?>
</tr>

<?php } else { ?>
<tr>
	<th width='25%'>First Name</th>
	<th width='25%'>Last Name</th>
	<th width='20%'>Email</th>
	<th width='20%'>Status</th>
	<th width='5%'>Edit</th>
	<th width='5%'>Delete</th>
</tr>
<?php } ?>


