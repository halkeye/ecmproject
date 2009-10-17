<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
<title>Electronic Convention Management (ECM)<?php echo $title ? "::$title" : "" ?></title> 
<?php echo html::stylesheet(array('css/main.css')); ?>
<?php echo html::script(array('js/jquery-1.3.2.min.js')); ?>
</head> 
 
<body> 
 
<!-- Content container beings here --> 
<div id="container"> 
    <!-- Header --> 
    <div id="header"></div> 
 
    <!-- Left Sidebar. --> 
    <?php if ($isLoggedIn || $menu): ?>
    <div id="sidebar">
        <ul> 
            <li class="title"><a href="<?php echo url::base(); ?>">Welcome</a></li>
            <?php if ($isLoggedIn): ?>
            <li><b><?php echo htmlentities($account->email) ?></b></li>
            <li>&nbsp;</li>
            <?php endif; ?>

            <?php if($menu) { echo View::Factory('global/menu', array('menu'=>$menu)); } ?>
        </ul>
    </div> 
    <?php endif; ?>
 
    <!-- Content Pane (Right side) --> 
    <div id="content"<?php if ($isLoggedIn || $menu) { echo " class='contentMenu'"; }?>>
    <?php if ($heading) echo "<h2>$heading</h2>"; ?>
    <?php if ($subheading) echo "<h3>$subheading</h3>"; ?>
    
    <?php foreach ($messages as $msg) { echo '<p class="msg">'.$msg.'</p>'; } ?>
    <?php foreach ($errors as $err) { echo '<p class="errormsg">'.$err.'</p>'; } ?>
    <?php if ($messages || $errors) { echo '<br />'; } ?>

    <!-- content start -->
    <?php print $content ?>
    <?php print $profiler ?>
    <!-- content end -->
    <br />
</div> 
<div id="footer"><p><a href="http://jigsaw.w3.org/css-validator/">CSS 2.1 Validated</a>, <a href="http://validator.w3.org/check?uri=referer">XHTML 1.0 Strict Validated</a>, <a href="mailto:stt@sfu.ca">Admin: stt@sfu.ca</a></p></div> 
<!-- Page rendered in {elapsed_time} seconds -->
 
</div> 

</body> 
</html> 

