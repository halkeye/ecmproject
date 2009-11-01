<br />
<!-- CONTENT -->
<div id='newform'>
<br />
<!-- CONTENT -->
<?php echo form::open("admin/createAccount"); ?>
	<h1>General Information</h1>
	<p>Please enter the email address you wish to use, followed by a password twice. </p>		
	<fieldset>		
		<label for="email">Email Address <span class="small">Must be a valid email address</span></label>
		<?php print form::input('email', $row->email); ?>
		<label for="password">Password <span class="small">Min. size 6 characters</span></label>
		<?php print form::password('password'); ?>
		<label for="confirm_password">Re-type Password <span class="small">Do it again!</span></label>
		<?php print form::password('confirm_password'); ?>
	</fieldset>		
	<h1>Validation Status</h1>
	<p>Admins may override the default email validation status. Setting status to VERIFIED or BANNED will prevent a validation email from going out to the email specified above.</p>
	<fieldset>
		<label for="status">Verification Status: <span class="small">Current password here.</span></label>
		<?php 
			$settings = array('UNVERIFIED' =>'Unverified', 'VERIFIED' => 'Verified', 'BANNED' => 'Banned');			
			print form::dropdown('status', $settings, $row->status); 			
		?>
	</fieldset>		
	<fieldset>				
		<button type="submit">Create account</button>
	</fieldset>		
<?php echo form::close(); ?>
</div>