<div id="list">
<p>
<strong>Registration Status: </strong><?php echo $reg->statusToString(); ?>
<br /><br />
<strong>Name: </strong><?php echo $reg->gname . ' ' . $reg->sname; ?><br />
<strong>Badge: </strong><?php echo $reg->badge; ?><br />
<strong>DOB: </strong><?php echo $reg->dob; ?><br />
<strong>Phone: </strong><?php echo $reg->phone; ?><br />
<strong>Email: </strong><?php echo $reg->email; ?>
</p>
<p>
Registrations that have had payment processed for can no longer be edited by the user. You can however view the details of the registration
below. Should something need correcting, please contact registration@animeevolution.com.
</p>
</div>