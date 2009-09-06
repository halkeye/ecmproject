<!-- CONTENT -->
<table cellspacing="0" cellpadding="0" width="97%" border="0">
    <!-- REQUIRED FIELD LEGEND CONTROL-->
    <TR>
        <td></td>
        <td>
            <div class="requiredLegend"> <span class="required">*</span> This symbol indicates a required field.</div>
        </td>
    </tr>
    <!-- end required field legend -->
    <tr>
        <td></td>
        <td valign="top"><span class="error"></span>
        </td>
    </tr>
    <tr>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td valign="top">
            <table id="_ctl9__ctl28_Table1" cellspacing="1" cellpadding="1" width="100%" border="0">
                <tr>
                    <td colspan="2" align="left"></td>
                </tr>
                <tr>
                    <td align="left" width="50%">
                        <?php echo form::open('/user/register'); ?>
                        <fieldset>
                            <legend><span><p>Create Log-in</p></span></legend>
                            <div>
                                Please enter your e-mail address and a password of your choice and click <strong>Continue</strong>.  
                                The e-mail address will serve as your login ID when you return to this site.
                            </div>
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
                    <td width="50%">
                        <?php echo form::open('/user/login'); ?>
                        <fieldset>
                            <legend><span><p>Log-in</p></span></legend>
                            <div>Please enter your e-mail address and password and click <strong>Continue</strong>.</div>
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
                                    <td class="fieldlabel"><div></div></td>
                                    <td></td>
                                    <td><input type="submit" value="Continue" /></td>
                                </tr>
                            </table>
                            <div id="_ctl9__ctl28_LoginControl1_DivForgotPassword" ms_positioning="FlowLayout">
                                <strong><span>Forgot your password?</span>:</strong> 
                                <?php echo html::anchor("/user/lostPassword", "Click here"); ?> to have your password emailed to you.
                            </div>
                        </fieldset>
                        <?php echo form::close(); ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
