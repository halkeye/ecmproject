<div id="list">
<p>
<strong>Registration Status: </strong><?php echo $reg->statusToString(); ?>
<br /><br />
<strong>Name: </strong><?php echo HTML::chars($reg->gname . ' ' . $reg->sname); ?><br />
<strong>Badge: </strong><?php echo HTML::chars($reg->badge); ?><br />
<strong>DOB: </strong><?php echo HTML::chars($reg->dob); ?><br />
<strong>Phone: </strong><?php echo HTML::chars($reg->phone); ?><br />
<strong>Email: </strong><?php echo HTML::chars($reg->email); ?>
</p>
<p>
Registrations that have had payment processed for can no longer be edited by the user. You can however view the details of the registration
below. Should something need correcting, please contact contact@irlevents.com.
</p>
</div>
