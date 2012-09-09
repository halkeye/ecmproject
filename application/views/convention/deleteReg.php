<p><strong>
You are about to remove the registration for <?php echo $reg->gname . ' ' . $reg->sname ?> from your shoppping cart.</strong>
This action is <strong>NOT</strong> reversible.</p>
<br />
<?php echo form::open("convention/deleteReg/" . $reg->id); ?>
<button name="No" value="No" type="submit" class='wide'>No</button>
<button name="Yes" value="Yes" type="submit">Yes</button>
<?php echo form::close(); ?>
