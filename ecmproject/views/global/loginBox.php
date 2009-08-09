<ul> 
    <li class="title">Login</li> 
</ul>
<form method="post" action="<?php echo url::site('user/login') ?>">
<p>
    <label for="user">Email: </label>
    <input type="text" name="user" id="user" />
</p>
                                                   
<p>
    <label for="pass">Password: </label>
    <input type="password" name="pass" id="pass" /> 
</p>
                                                   
<p class="submit">
<input class="submit" type="submit" name="Submit" value="Login" />
</p>
<div><?php echo html::anchor('/user/register', 'Register'); ?></div>
</form>
