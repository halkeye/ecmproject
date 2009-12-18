
<!-- CONTENT -->
<div id='form'>
    <div id="newLogin">
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
            <a href="#" rel="existing_login" onclick="return switchForm();">Click here to log-in with an existing ID</a>
        </fieldset>
        <?php echo form::close(); ?>
    </div>
    <div id="existingLogin">
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
            <a href="#" rel="newLogin" onclick="return switchForm();">New User? Click here.</a>	
        </fieldset>					
        <?php echo form::close(); ?>
    </div>
</div>

<script type="text/javascript"> 
/* <![CDATA[ */
var newLoginContainer = document.getElementById("newLogin");
var existingLoginContainer = document.getElementById("existingLogin");

function createCookie(name,value,days) 
{
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) 
{
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function switchForm()
{
    newLoginContainer.style.display=(newLoginContainer.style.display=='none')? 'block' : 'none';
    existingLoginContainer.style.display=(existingLoginContainer.style.display=='none')? 'block' : 'none';
    createCookie('loginPageTab', (existingLoginContainer.style.display!='none' ? 'existing' : 'login'));
    /* Don't continue click */
    return false;
}

if ( readCookie('loginPageTab') == 'existing') 
{
    newLoginContainer.style.display='none';
}
else
{
    /* Start off hiding existing Login */
    existingLoginContainer.style.display='none';
}
/* ]]> */
</script>
