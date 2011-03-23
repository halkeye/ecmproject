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
