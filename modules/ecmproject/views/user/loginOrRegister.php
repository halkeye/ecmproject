<br />
<!-- CONTENT -->
<div id='newform'>
<table width='97%' class='login'>	
	<tr>
		<td width='50%'>
			<?php echo form::open('/user/register'); ?>
			<h1>Create Log-in ID</h1>
			<p>Please enter your e-mail address and a password of your choice and click Continue. The e-mail address will serve as your login ID when you return to this site.</p>
			<fieldset class='fixed'>			
				<label for="email">Email Address <span class="small">Add a valid email address</span></label>
				<input name="email" type="text" />
				<label for="password">Password <span class="small">Min. size 6 characters</span></label>
				<input name="password" type="password" />
				<label for="confirm_password">Re-type Password <span class="small">Type in the same characters again.</span></label>
				<input name="confirm_password" type="password" />
			</fieldset>
			<fieldset class='right'>
				<button type="submit">Continue</button>
			</fieldset>
			<?php echo form::close(); ?>
		</td>
		<td width='50%'>
			<?php echo form::open('/user/login'); ?>
			<h1>Log-in with Existing ID</h1>
			<p>Please enter your e-mail address and password and click <strong>Continue</strong>.</p>
		
			<fieldset class='fixed'>		
				<label for="email">Email Address <span class="small">Add a valid email address</span></label>
				<input name="email" type="text" />
				<label for="password">Password <span class="small">Min. size 6 characters</span></label>
				<input name="password" type="password" />	
				<label><?php echo html::anchor("/user/lostPassword", "Forgot your password?"); ?></label>	
			</fieldset>			
			<fieldset class='right'>				
				<button type="submit">Continue</button>
			</fieldset>							
			<?php echo form::close(); ?>
			
		</td>
	</tr>
</table>
</div>