<?php 
//If the row containing the information is set, print out the information.
if(isset($row)) { ?>

<tr>
	<td><?php print $row->id; ?></td>
	<td><?php print $row->email; ?></td>
	<td><?php print $row->statusToString() ?></td>
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
			print '<td class="center">' . $action; '</td>';
		endforeach;
	?>
</tr>

<?php } else { ?>
<tr>
	<th width='10%'>ID</th>
	<th width='35%'>Email</th>
	<th width='15%'>Status</th>
	<th width='39%'>Last Login</th>
	<th width='5%'>Edit</th>
	<th width='5%'>Delete</th>
</tr>
<?php } ?>


