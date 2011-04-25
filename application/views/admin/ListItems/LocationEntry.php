<?php 
//If the row containing the information is set, print out the information.
if(isset($row)) { ?>

<tr>
	<td><?php print $row->prefix; ?></td>
	<td><?php print $row->location; ?></td>
	<?php
		foreach ($actions as $action): 		
			print '<td class="center">' . $action; '</td>';
		endforeach;
	?>
</tr>

<?php } else { ?>
<tr>
	<th width='10%'>Prefix</th>
	<th width='80%'>Location</th>
	<th width='5%'>Edit</th>
	<th width='5%'>Delete</th>
</tr>
<?php } ?>


