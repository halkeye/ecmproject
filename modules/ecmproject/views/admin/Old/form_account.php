<br />
<!-- CONTENT -->

<?php echo form::open('/user/register'); ?>
	<fieldset>
		<legend>General Account</legend>
			<label for="email">First name: <span class="required">*</span></label>
			<input name="email" type="text" style="width:156px;" /><br />
	</fieldset>
<?php echo form::close(); ?>

<table>
	<tr>
		<td>
			<p>Please enter your e-mail address and a password of your choice and click <strong>Continue</strong>.  
			The e-mail address will serve as your login ID when you return to this site.</p>
			
				<fieldset>
					<table cellspacing="0" cellpadding="2" border="0">
						<tr><td colspan="3">&nbsp;</td></tr>
						<tr>
							<td class="fieldlabel"><DIV>E-mail Address:</DIV></TD>
							<td><span class="required">*</span></td>
							<td><input name="email" type="text"  style="width:156px;" /></td>
						</tr>
						<tr>
							<td class="fieldlabel"><div>Password:</div></td>
							<td><span class="required">*</span></td>
							<td><input name="password" type="password" style="width:156px;" /></td>
						</tr>
						<tr>
							<td class="fieldlabel"><div>Re-type Password:</div></td>
							<td><span class="required">*</span></td>
							<td><input name="confirm_password" type="password" style="width:156px;" /></td>
						</tr>
						<tr>
							<td class="fieldlabel"><div></div></td>
							<td></td>
							<td><input type="submit" value="Continue" /></td>
						</tr>
					</table>
				</fieldset>
				<?php echo form::close(); ?>
		</td>
	</tr>
</table>
