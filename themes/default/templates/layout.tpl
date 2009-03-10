<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
<title>Electronic Convention Management (ECM) :: {$title}</title> 
<link href="{$_themeURL}/css/main.css" rel="stylesheet" type="text/css" /> 
</head> 
 
<body> 
 
<!-- Content container beings here --> 
<div id="container"> 
    <!-- Header --> 
    <div id="header"></div> 
 
    <!-- Left Sidebar. --> 
    <div id="menu"> 
        {if !$loggedIn}
            {include file=loginBox.tpl}
        {/if}
        <ul> 
            <li class="title">Menu</li> 
            {foreach from=$menu item=m}
            <li><a href="{$m.url}">{$m.title}</a></li> 
            {/foreach}
        </ul> 
    </div> 
 
    <!-- Content Pane (Right side) --> 
    <div id="content"> 
    <h2>{$heading}</h2>
    <h3>{$subheading}</h3>
    <br />
    <!-- content start -->
    {$content}
    <!-- content end -->
    <br />
</div> 
<div id="footer"><p><a href="http://jigsaw.w3.org/css-validator/">CSS 2.1 Validated</a>, <a href="http://validator.w3.org/check?uri=referer">XHTML 1.0 Strict Validated</a>, <a href="mailto:stt@sfu.ca">Admin: stt@sfu.ca</a></p></div> 
 
</div> 
</body> 
</html> 
 
