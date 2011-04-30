<?php
/*
* Pass in $registrations array containing the rows in the shopping cart SORTED by event ID (convention ID currently).
*/
?>
<style type="text/css">
	table {
		width: 400px;
        border-spacing: 0;
	}
	tr {
		border: 1px solid #A2B0C8;
	}
	td {
		padding: 3px 20px;
		border: 1px solid #A2B0C8;
	}
	th {
		background-color: #A2B0C8;
	}
</style>

<h2>IRLEvents: Confirmation of Payment</h2>
<p>This is to confirm that we have received your payment for the tickets listed below as of <strong>Monday, March 12th.</strong> Please double check the details below.</p> 

<?php

foreach (array_keys($registrations) as $convention_name) {
    /* Open a new table per event */
    echo '<table>';
    echo '<tr><th colspan="2">' . $convention_name . '</th></tr>';		

    foreach ($registrations[$convention_name] as $reg) {
        $id = $reg->reg_id;
        $name = HTML::chars($reg->gname) . ' ' . HTML::chars($reg->sname);
        echo "<tr>\n";
        echo "<td width='100'>" . html::image('http://chart.apis.google.com/chart?chs=100x100&cht=qr&chl='.$id) . "</td>\n"; 
        echo "<td style='text-align:center' valign='middle'><strong>$id</strong><br />$name</td>\n";
        echo "</tr>\n";
    }
    echo '</table>';
}

?>

<p>Thank you and we look forward to seeing you there!</p>
<p>Regards, </p>
<p><strong>The IRL Events Team </strong><br />contact@irlevents.com</p> 
