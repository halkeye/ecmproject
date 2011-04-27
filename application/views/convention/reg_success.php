<?php
/*
* Pass in $registrations array containing the rows in the shopping cart SORTED by event ID (convention ID currently).
*/
?>
<style type="text/css">
	table {
		width: 400px;
	}
	tr {
		border: 1px solid #A2B0C8;
	}
	td {
		padding: 3px 20px;
	}
	th {
		background-color: #A2B0C8;
	}
</style>

<h2>IRLEvents: Confirmation of Payment</h2>
<p>This is to confirm that we have recieved your payment for the tickets listed below as of <strong>Monday, March 12th.</strong> Please double check the details below.</p> 

<?php

$cur_eventID = -1;
foreach ($registrations as $reg) {
	/* Open a new table per event */
	if ($reg->convention_id != $cur_eventID) {
		if ($cur_eventID != -1) {
			echo '</table>';
		}
		echo '<table>';
		echo '<tr><th colspan="2">' . $reg->convention->name . '</th></tr>';		
	}
	
	$id = $reg->reg_id;
	$name = HTML::chars($reg->gname) . ' ' . HTML::chars($reg->sname);
	echo "<tr>\n";
	echo "<td>" . /* html::image */ "</td>\n"; //TODO: ADD QR CODE IMAGE HERE.
	echo "<td><strong>$id</strong><br />$name</td>\n";
	echo "</tr>\n";
}

/* Close off last table if one was opened */
if ($cur_eventID != -1) {
	echo '</table>';
}

?>

<p>Thank you and we look forward to seeing you there!</p>
<p>Regards, </p>
<p><strong>The IRL Events Team </strong><br />contact@irlevents.com</p> <!-- Change email address to appropriate contact point. -->