<br />
<!-- CONTENT: TODO: Renaming variables to something general. -->
<table width='100%'>	
	<tr>	
		<th class='header' width=10%>ID</th>
		<th class='header' width=30%>Email</th>
		<th class='header' width=15%>Status</th>
		<th class='header' width=20%>Last Login</th>
		<th class='header' width=10%>Edit</th>
		<th class='header' width=10%>Delete</th>
	</tr>
	<?php foreach ($entries as $rows): ?>
	<tr>
		<?php foreach ($rows as $column): ?>
		<td><?php echo $column ?></td>
		<?php endforeach; ?>
	</tr>
	<?php endforeach; ?>
</table>
