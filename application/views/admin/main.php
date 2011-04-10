<div id="list">
	<table>
	<tr>
		<th class='header' width=50%><?php echo html::anchor('admin/manageConventions', __('Manage Events') ) ?></th>
		<th class='header' width=50%><?php echo html::anchor('admin/managePasses', __('Manage Tickets')) ?></th>
	</tr>
	<tr>
		<td>Add, modify or delete events in the system.</td>
		<td>Add, modify or delete tickets associated with a convention - purchasable or otherwise</td>
	</tr>
	<tr>
		<th class='header'><?php echo html::anchor('admin/manageAccounts', __('Manage Accounts')) ?></th>
		<th class='header'><?php echo html::anchor('admin/manageRegistrations', __('Manage Registrations')) ?></th>	
	</tr>
	<tr>
		<td>Add, modify, or delete user accounts in the system.</td>
		<td>Add, modify or delete event registrations.</td>
	</tr>
	<tr>
		<th class='header'><?php echo html::anchor('admin/export', __('Export Registration Information (CSV)')) ?></th>
		<th class='header'><?php echo html::anchor('admin/manageAdmin', __('Manage Administrators')) ?></th>
	</tr>
	<tr>
		<td>Exports selected (or all) registration information for a particular event to a CSV file which can be opened in Excel.</td>
		<td>Grant and manage administrator access to accounts</td>
	</tr>
</table>
</div>
