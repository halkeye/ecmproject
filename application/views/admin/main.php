<table>
	<tr>
		<th width=50%><?php echo html::anchor(url::site('admin/manageConventions', TRUE), __('Manage Events') ) ?></th>
		<th width=50%><?php echo html::anchor(url::site('admin/managePasses', TRUE), __('Manage Tickets')) ?></th>
	</tr>
	<tr>
		<td>Add, modify or delete events in the system.</td>
		<td>Add, modify or delete tickets associated with a convention - purchasable or otherwise</td>
	</tr>
	<tr>
		<th><?php echo html::anchor(url::site('admin/manageAccounts', TRUE), __('Manage Accounts')) ?></th>
		<th><?php echo html::anchor(url::site('admin/manageRegistrations', TRUE), __('Manage Registrations')) ?></th>	
	</tr>
	<tr>
		<td>Add, modify, or delete user accounts in the system.</td>
		<td>Add, modify or delete event registrations.</td>
	</tr>
	<tr>
		<th><?php echo html::anchor(url::site('admin/manageLocations', TRUE), __('Manage Locations')) ?></th>
		<th><?php echo html::anchor(url::site('admin/manageAdmin', TRUE), __('Manage Administrators')) ?></th>
	</tr>
	<tr>
		<td>Manage the list of sale locations and their prefixes (which are used to help generate registration ID's)</td>
		<td>Grant and manage administrator access to accounts</td>
	</tr>	
	<tr>
		<th><?php echo html::anchor(url::site('admin/export', TRUE), __('Export Registration Information (CSV)')) ?></th>
	</tr>
	<tr>
		<td>Exports selected (or all) registration information for a particular event to a CSV file which can be opened in Excel.</td>
	</tr>
</table>

