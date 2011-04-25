<?php
<p></p>
echo form::open('user/logout');
print form::submit('submit', __('Confirm Logout'));
echo form::close();
