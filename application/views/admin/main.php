<div id="list">
	<table>
	<tr>
		<th class='header' width=50%><?php echo html::anchor(url::site('admin/manageConventions',TRUE), 'Manage Conventions') ?></th>
		<th class='header' width=50%><?php echo html::anchor(url::site('admin/managePasses',TRUE), 'Manage Passes') ?></th>
	</tr>
	<tr>
		<td>Add, modify or delete conventions in the system.</td>
		<td>Add, modify or delete pass types associated with a convention - purchasable or otherwise</td>
	</tr>
	<tr>
		<th class='header'><?php echo html::anchor(url::site('admin/manageAccounts', TRUE), 'Manage Accounts') ?></th>
		<th class='header'><?php echo html::anchor(url::site('admin/manageRegistrations', TRUE), 'Manage Registrations') ?></th>	
	</tr>
	<tr>
		<td>Add, modify, or delete user accounts in the system.</td>
		<td>Add, modify or delete convention registrations for user(s) as well as view and manage payments.</td>
	</tr>
	<tr>
		<th class='header'><?php echo html::anchor(url::site('admin/export', TRUE), 'Export Registration Information (CSV)') ?></th>
		<th class='header'><?php echo html::anchor(url::site('admin/manageAdmin', TRUE), 'Manage Administrators') ?></th>
	</tr>
	<tr>
		<td>Exports selected (or all) registration information for a particular convention to a CSV file which can be opened in Excel.</td>
		<td>Grant and manage administrator access to accounts</td>
	</tr>
</table>
</div>
