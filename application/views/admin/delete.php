<p><strong>
You are deleting the <?php echo $entityType ?>: <?php print htmlspecialchars($entityName, ENT_COMPAT, "UTF-8") ?>.</strong>
This action is <strong>NOT</strong> reversible. Do you wish to continue? </p>
<br />
<?php echo form::open("admin/$callback/$id"); ?>
<button name="No" value="No" type="submit" class='wide'>No</button>
<button name="Yes" value="Yes" type="submit">Yes</button>
<?php echo form::close(); ?>
