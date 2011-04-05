<?php
echo form::open('user/logout');
print form::submit('submit', __('auth.logoutConfirmButton'));
echo form::close();
