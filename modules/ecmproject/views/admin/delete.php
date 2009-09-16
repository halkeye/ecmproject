<br />
<!-- CONTENT: TODO: Renaming variables to something general. -->
<p><strong>You are deleting the <?php echo $entityType ?>: <?php echo $entityName ?>.</strong> This action is <strong>NOT</strong> reversible. Do you wish to continue? </p>
<br />
<?php echo form::open('admin/deleteAccount'); ?>
<input type="hidden" name="id" value=<?php echo $id ?>>
<button name="No" value="No" type="submit" class='wide'>No</button>
<button name="Yes" value="Yes" type="submit">Yes</button>
<?php echo form::close(); ?>