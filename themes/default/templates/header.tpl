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
            <br/>
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
