<ul>
    <li>
        Enter code<br />
        <?php echo form::open('/user/validate'); ?>
        <?php echo form::input('verifyCode') ?>
        <?php echo form::submit('', 'Verify Email') ?>
        <?php echo form::close(); ?>
    </li>
    <li><?php echo html::anchor('/user/resendVerification', 'Send new Email') ?></li>
    <li><?php echo html::anchor('/user/changeEmail', 'Change Email') ?></li>
</ul>

