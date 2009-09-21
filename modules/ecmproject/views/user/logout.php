<?php
echo form::open();
print form::submit('submit', Kohana::lang('auth.logoutConfirmButton'));
echo form::close();
