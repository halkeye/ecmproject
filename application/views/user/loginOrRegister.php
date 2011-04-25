<!-- CONTENT -->
<?php
	if (!isset($form))
	{
		$form = array();
	}
	
	//Initialize to blank.
	$form['sname'] = empty($form['sname']) ? '' : $form['sname'];
	$form['gname'] = empty($form['gname']) ? '' : $form['gname'];
	$form['email'] = empty($form['email']) ? '' : $form['email'];
	$form['phone'] = empty($form['phone']) ? '' : $form['phone'];

?>
<div id="newLogin">
	<h2 class='grey'>Create Log-in ID</h1>
	<p class='fixed'>Please fill out the information below. <strong>Your name will be used for all registrations</strong> that you purchase through this account, so double check! 
	Your e-mail address will serve as your login ID. <a href="#" rel="existing_login" onclick="return switchForm();" class='special'><strong>Click here to log-in with an existing ID</strong>.</a></p>
	<?php echo form::open('/user/register'); ?>	
	
	<fieldset>			
		<!-- Temporary: Using <br /> to block the line again. -->
		<label for="sname"><span class="required">*</span> First Name <span class="small">Your first name.</span></label>
		<input name="sname" type="text" class='inline' value='' />
		<?php $field = 'sname'; echo (empty ($errors[$field])) ? '' : '<p class="errormsg">'.$errors[$field].'</p>'; ?>
		<br />
		<label for="gname"><span class="required">*</span> Last Name <span class="small">Your last name.</span></label>
		<input name="gname" type="text" class='inline' value='<?php echo html::chars($form['gname']); ?>' />
		<?php $field = 'gname'; echo (empty ($errors[$field])) ? '' : '<p class="errormsg">'.$errors[$field].'</p>'; ?>
		<br />
		<label for="email"><span class="required">*</span> Email Address <span class="small">Add a valid email address</span></label>
		<input name="email" type="text" class='inline' value='<?php echo html::chars($form['email']); ?>' />
		<?php $field = 'email'; echo (empty ($errors[$field])) ? '' : '<p class="errormsg">'.$errors[$field].'</p>'; ?> 
		<br />
		<label for="phone">Phone Number <span class="small">Phone number</span></label>
		<input name="phone" type="text" class='inline' value='<?php echo html::chars($form['phone']); ?>' />
		<?php $field = 'phone'; echo (empty ($errors[$field])) ? '' : '<p class="errormsg">'.$errors[$field].'</p>'; ?>
		<br />
		<label for="password"><span class="required">*</span> Password <span class="small">Min. size 6 characters</span></label>
		<input name="password" type="password" class='inline'/>
		<?php $field = 'password'; echo (empty ($errors[$field])) ? '' : '<p class="errormsg">'.$errors[$field].'</p>'; ?>
		<br />
		<label for="confirm_password"><span class="required">*</span> Re-type Password <span class="small">Type in the same characters again.</span></label>		
		<input name="confirm_password" type="password" class='inline'/>
		<?php $field = 'confirm_password'; echo (empty ($errors[$field])) ? '' : '<p class="errormsg">'.$errors[$field].'</p>'; ?>
		<br />
	</fieldset>
	<fieldset class='left'>
		<button type="submit">Continue</button>
	</fieldset>
	<fieldset>
		
	</fieldset>
	<?php echo form::close(); ?>
</div>
<div id="existingLogin">
	<h2 class='grey'>Log-in with Existing ID</h2>
	<p>Please enter your e-mail address and password and click continue. <a href="#" rel="newLogin" onclick="return switchForm();" class='special'><strong>New users, click here</strong>!</a></p>
	<?php echo form::open('/user/login'); ?>	
	<fieldset>		
		<label for="email">Email Address <span class="small">Add a valid email address</span></label>
		<input name="email" type="text" value='<?php echo empty($email) ? '' : html::chars($email) ?>' />
		<label for="password">Password <span class="small">Min. size 6 characters</span></label>
		<input name="password" type="password" />			
		<?php echo html::anchor("/user/lostPassword", "Forgot your password?", null, null, true); ?>
	</fieldset>			
	<fieldset class='left'>				
		<button type="submit">Continue</button>				
	</fieldset>					
	<?php echo form::close(); ?>
</div>
<script type='text/javascript' src="<?php echo url::site('static/js/loginOrRegister') ?>"></script>
