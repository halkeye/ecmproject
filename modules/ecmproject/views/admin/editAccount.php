<div id='newform'>
<br />
<!-- CONTENT -->
<?php echo form::open("admin/editAccount/$row->id"); ?>
	<h1>General Information</h1>
	<p>You must revalidate your email if you change it.</p>		
	<fieldset>		
		<label for="email">Email Address <span class="small">Must be a valid email address</span></label>
		<?php print form::input('email', $row->email); ?>
	</fieldset>
	
	<h1>Change Password</h1>
	<p>Enter the new password twice.</p>
	<fieldset>
		<label for="password">Password <span class="small">Min. size 6 characters</span></label>
		<?php print form::password('password'); ?>
		<label for="confirm_password">Re-type Password <span class="small">Do it again!</span></label>
		<?php print form::password('confirm_password'); ?>
	</fieldset>	

	<h1>Validation Status</h1>
	<p>Allows the overriding of an account's email validation status. NOTE: If the email address was changed above, status
	is automatically set to UNVERIFIED regardless of the setting here.</p>
	<fieldset>
		<label for="status">Verification Status: <span class="small">Current password here.</span></label>
		<?php 
			$settings = array('UNVERIFIED' =>'Unverified', 'VERIFIED' => 'Verified', 'BANNED' => 'Banned');			
			print form::dropdown('status', $settings, $row->statusToString()); 			
		?>
	</fieldset>	
	
	<fieldset class='right'>				
		<button type="submit">Continue</button>
	</fieldset>							
<?php echo form::close(); ?>
</div>

