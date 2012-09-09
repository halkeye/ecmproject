<?php
//If the row containing the information is set, print out the information.
if(isset($row)) { ?>

<tr>
	<td><?php print htmlspecialchars($row->name, ENT_COMPAT, "UTF-8") ?></td>
	<td><?php print htmlspecialchars($row->location, ENT_COMPAT, "UTF-8") ?></td>
	<?php
		foreach ($actions as $action):
			print '<td class="center">' . $action; '</td>';
		endforeach;
	?>
</tr>

<?php } else { ?>
<tr>
	<th width='40%'>Name</th>
	<th width='50%'>Location</th>
	<th width='5%'>Edit</th>
	<th width='5%'>Delete</th>
</tr>
<?php } ?>


