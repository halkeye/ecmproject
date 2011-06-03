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

<p>Congratulations <?php echo HTML::chars($name); ?> You have completed purchasing a ticket for Cos &amp; Effect 2011.</p>
<p>Here are your ticket details:</p>
<?php
foreach (array_keys($registrations) as $convention_name) {
    /* Open a new table per event */
    echo '<table>';
    echo '<tr><th colspan="2">' . $convention_name . '</th></tr>';		

    foreach ($registrations[$convention_name] as $reg) {
        $id = $reg->reg_id;
        $name = HTML::chars($reg->gname . ' ' . $reg->sname);
        echo "<tr>\n";
        echo "<td width='100'>" . html::image('http://chart.apis.google.com/chart?chs=100x100&cht=qr&chl='.$id) . "</td>\n"; 
        echo "<td style='text-align:center' valign='middle'>Ticket Number: <strong>$id</strong><br/>Pickup By: <strong>$name</strong></td>\n";
        echo "</tr>\n";
    }
    echo '</table>';
}

?>

<p>Instead of using personalized badges, Cos &amp; Effect will use non-personalized convention wristbands as proof of purchase. To redeem this digital ticket for a convention pass wristband, just bring your ticket number and government ID to the ticket booth at Cos & Effect, and we'll give you your convention pass wristband! The ticket booth will open for wrist band pick up at 9am on Saturday August 13th in the South Side Lounge of the Student Union Building at UBC.</p>

<p>Please ensure that the name attached to your ticket is the same as is written on your government ID. We will match the name provided here to the government ID you bring when picking up your ticket. You must bring government issued ID in order to redeem your ticket for your convention pass wristband. High School students may bring picture student ID if they do not have a government issued ID card.</p>

<p>Cos &amp; Effect 2011 - August 13-14<br/>
University of British Columbia - Student Union Building<br/>
August 13th 9am to Midnight<br/>
August 14th 8am to 5:30pm<br/>
</p>

<p>
For updates on Cos &amp; Effect, please visit our dedicated website: <a href="http://IRLEvents.com/coseffect">http://IRLEvents.com/coseffect</a><br/>
For news about other geek events happening in Vancouver, please visit: <a href="http://IRLEvents.com">http://IRLEvents.com</a><br/>
For Any questions regarding Cos &amp; Effect, please email: contact@irlevents.com, or visit our forums: <a href="http://forum.irlevents.com">http://forum.irlevents.com</a><br/>
</p>
<p>General Terms:</p>
<p><ol>
<li>Each ticket is redeemable for 1 (one) weekend pass wristband only. Wristbands must be worn on display at all times during the event as proof of purchase, while on convention grounds.</li>
<li>Only the person who purchased the ticket may redeem it for a weekend pass wristband. The wristbands themselves are non-personalized and exchangable.</li>
<li>IRL Events and the NPCs of Cos &amp; Effect may refuse admission or may expel from the convention grounds any person whose presence or conduct is deemed to be objectionable.</li>
<li>IRL Events and the NPCs of Cos &amp; Effect is not liable for any injury or loss (whether or not caused by negligence) sustained to the holder of a weekend pass, or his/her property while on convention grounds.</li>
<li>This ticket has no cash value, and is non-refundable.</li>
</ol>
</p>
<p>-The NPCs of Cos &amp; Effect 2011</p>
