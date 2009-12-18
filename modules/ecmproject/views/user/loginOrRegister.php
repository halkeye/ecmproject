<script type="text/javascript"> 
function switchForm()
{
	if(document.getElementById("new_login").style.display == 'block')
	{
		document.getElementById("new_login").style.display = 'none';
		document.getElementById("existing_login").style.display = 'block';
	}
	else
	{
		document.getElementById("new_login").style.display = 'block';
		document.getElementById("existing_login").style.display = 'none';
	}
}
</script>

<!-- CONTENT -->
<div id='form'>
<table width='100%' class='login'>	
	<tr>
		<td width='100%' id="new_login" style="display:block;">
			<?php echo form::open('/user/register'); ?>
			<h1>Create Log-in ID</h1>
			<p class='fixed'>Please enter your e-mail address and a password of your choice and click Continue. The e-mail address will serve as your login ID.</p>
			<fieldset class='fixed'>			
				<label for="email">Email Address <span class="small">Add a valid email address</span></label>
				<input name="email" type="text" />
				<label for="password">Password <span class="small">Min. size 6 characters</span></label>
				<input name="password" type="password" />
				<label for="confirm_password">Re-type Password <span class="small">Type in the same characters again.</span></label>
				<input name="confirm_password" type="password" />
			</fieldset>
			<fieldset class='left'>
				<button type="submit">Continue</button>
			</fieldset>
			<fieldset>
				<a href="#" rel="existing_login" onclick="switchForm();">Click here to log-in with an existing ID</a>
			</fieldset>
			<?php echo form::close(); ?>
		</td>
		<td width='100%' id="existing_login" style="display:none;">
			<?php echo form::open('/user/login'); ?>
			<h1>Log-in with Existing ID</h1>
			<p class='fixed'>Please enter your e-mail address and password and click <strong>Continue</strong>.</p>
		
			<fieldset class='fixed'>		
				<label for="email">Email Address <span class="small">Add a valid email address</span></label>
				<input name="email" type="text" />
				<label for="password">Password <span class="small">Min. size 6 characters</span></label>
				<input name="password" type="password" />	
				
				<?php echo html::anchor("/user/lostPassword", "Forgot your password?"); ?>
			</fieldset>			
			<fieldset class='left'>				
				<button type="submit">Continue</button>				
			</fieldset>	
			<fieldset>
				<a href="#" rel="new_login" onclick="switchForm();">New User? Click here.</a>	
			</fieldset>					
			<?php echo form::close(); ?>
			
		</td>
	</tr>
</table>
</div>