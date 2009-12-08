<div id="list">
<p>Listed here are the various actions you can perform on your account as well as your current and past history of registrations. Please note that <strong>changing your email </strong>
address will require your account to be re-validated.</p>
<table width='100%'>
	<tr><th>Account Actions</th></tr>
	<tr><td><?php echo html::anchor("/user/changeEmail","Change Email"); ?> | <?php echo html::anchor("/user/changePassword","Change Password"); ?></td></tr>	
</table>

<table width = 100%>
<?php
	/* $reg is assumed to be sorted by convention_id. If it's not, this will still work - just rather randomly. */
	$cid = -1;
	foreach($registrations as $r):
	
		//Print convention header if it's different than previous.
		if ($cid != $r->convention_id)
		{
			print '<tr><th colspan=4>' . $r->convention->name . '</th></tr>';
			?>
			<tr>
				<th width='25%'>First Name</th>
				<th width='25%'>Last Name</th>
				<th width='25%'>Email</th>
				<th width='25%'>Status</th>
			</tr>
			<?php
			$cid = $r->convention_id;
		}
	
		$class_row = text::alternate('odd','even');
		echo '<tr class="'.$class_row.'">';
		echo '<td>' . html::specialchars($r->gname . ' ' . $r->sname) . '</td>';
		echo '<td>' . html::specialchars($r->pass->name) . '</td>';
		echo '<td>' . html::specialchars(sprintf('$%01.2F', $r->pass->price)) . '</td>';
		echo '<td>'.$r->statusToString().'</td>';
		echo '</tr>';
		
	endforeach;
?>
</table>
</div>
