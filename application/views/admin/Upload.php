<?php
/*
* Upload view for the mass import feature.
*/
?>
<h3>Select ticket to assign</h3>
<p>This ticket will be assigned to <strong>all registrations</strong> that are imported. 
Likewise, the event that the ticket belongs to will be associated to the registration.</p>
<?php 
	echo Form::open('admin/import', array('enctype' => 'multipart/form-data'));

	$options[-1] = "SELECT A TICKET";
	foreach ($passes as $pass) {						
		$options[$pass->convention->name][$pass->id] = "$pass";
	}
	echo form::select('pass_id', $options, $pass_id) . "\n";
?>
<br /><br />
<h3>Select CSV File to import data from</h3>
<p>CSV File that is defined in the following format: Registration ID, Name, Email, Phone Number, Date of Birth, Emailed. Extra columns beyond email will be ignored.</p>
<?php
	//Format this section.
	echo Form::file('csv_file');
	echo "<br />";
?>
<h3>Email registrations</h3>
<p>Email the user with the standard template upon successful import.</p>

<?php
	echo Form::checkbox('email_on_completion');
?>

<?php
	echo "<br />";
	echo Form::submit(NULL, 'Import', array('class' => 'submit'));
	echo Form::close();		
?>		
 
 
