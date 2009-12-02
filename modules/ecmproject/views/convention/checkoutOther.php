<div id="list">

<p>
To ensure the successful processing of your payment and entry into the convention, please ensure that you <strong>read everything on this page</strong> before submitting or bringing payment.
Listed below are the registrations that will be paid for. If in error, press the back button and add, edit or remove registrations as necessary.
</p>

<?php $acctDetails = $this->auth->getAccount() ?>
<p>
<strong>
Account: <?php echo $acctDetails->email; ?> </strong><br />
Account Status: <?php echo $acctDetails->statusToString(); ?>

</p>

<table width='100%'>    
	<tr>    
		<th width='40%'>For</th>
		<th width='30%'>Item</th>
		<th width='10%'>Price</th>  
		<th width='10%'>Status</th>  
	</tr>
	<?php
	foreach ($registrations as $reg)
	{
		$class_row = text::alternate('odd','even');
		echo '<tr class="'.$class_row.'">';
		echo '<td>' . html::specialchars($reg->gname . ' ' . $reg->sname) . '</td>';
		echo '<td>' . html::specialchars($reg->pass->name) . '</td>';
		echo '<td>' . html::specialchars(sprintf('$%01.2F', $reg->pass->price)) . '</td>';
		echo '<td>'.$reg->statusToString().'</td>';
		echo '</tr>';
	}
	?>
</table>
<br />
<p>
For <strong>minor passes</strong> also attach the parental consent form (link here) or bring a completed parental consent form to the convention when picking up your pass. <strong>Minors
without a completed written parental consent form will be unable to pick up their badge with no exceptions</strong>
</p>

<p>
<strong> Your next step is to print this page and to do one of the following: </strong><br /><br />

If paying by <strong>MAIL</strong> submit with your payment to:
<br /><br />
AE Convention Corp. <br />
Box 641 <br />
141 - 6200 McKay Ave. <br />
Burnaby, BC  V5H 4M9" <br />
</p>

<p>
If you are paying <strong>IN-PERSON</strong>, please bring in this printed page, your payment and parental consent if applicable. <br /><br />
NOTE: The system will automatically update your registration status when payment is recieved and processed. Any questions or concerns may be directed to registration@animeevolution.com
</p>

</div>