<h3>Verify Email</h3>
<p>Please enter the verification code that was sent to your email address. 
    If the message is not in your mailbox, please check your junk mail or have it 
    <strong><?php echo html::anchor('/user/resendVerification', 'resent to you', NULL, NULL, TRUE) ?>.</strong>
     Alternatively, you can try 
    <strong><?php echo html::anchor('/user/changeEmail', 'changing your email address', NULL, NULL, TRUE) ?></strong>
     if what you entered for your email was wrong.
</p>

<?php echo form::open('/user/validate'); ?>
<fieldset>
	<div>
		<label for="verifyCode">Verification Code <span class="small">The code in the email message...</span></label> 
		<input type="text" id="verifyCode" name="verifyCode" value=""  /> 
	</div>
</fieldset>
<fieldset class='left'> 
    <input type="submit" value="Validate" class='submit' />        
</fieldset>

<?php echo form::close(); ?>
</div>
