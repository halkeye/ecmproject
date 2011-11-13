<?php $content = (string) $content;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
<title>Electronic Convention Management (ECM)<?php echo $title ? "::$title" : "" ?></title> 
<?php {
    echo HTML::style(url::site('static/css/main2.css',TRUE), NULL, TRUE) . "\n";
	echo HTML::style(url::site('static/css/jquery-ui.css', TRUE), NULL, TRUE) . "\n";
	echo HTML::script(url::site('static/js/jquery.js',TRUE), NULL, TRUE) . "\n";
	echo HTML::script(url::site('static/js/jquery-ui.js',TRUE), NULL, TRUE) . "\n";
	
    foreach (Assets::getCSS() as $style)
        echo HTML::style(url::site("static/css/$style",TRUE), NULL, TRUE);
    foreach (Assets::getJS() as $js)
        echo HTML::script(url::site("static/js/$js",TRUE), NULL, TRUE);
} ?>
</head> 
 
<body> 
 
<!-- Content container beings here --> 
<div id="container"> 
    <!-- Header --> 
	<?php if ($isLoggedIn): // || $menu ?>
    <div id="menu">
        <ul>      
			<li class='header'>Menu: </li>
            <?php if($menu) { echo View::Factory('global/menu', array('menu'=>$menu)); } ?>
        </ul>
    </div> 
    <?php endif; ?>
    <div id="header">
		<h1>IRL Events Registration</h1>
	</div>   
 
    <!-- Content Pane (Right side) --> 
    <div id="content">
	<?php foreach ($messages as $msg) { echo '<p class="msg">'.$msg.'</p>'; } ?>
    <?php foreach ($errors as $err) { echo '<p class="errormsg">'.$err.'</p>'; } ?>
	
    <?php if ($heading) echo "<h2>$heading</h2>"; ?>
    <?php if ($subheading) echo "<p class='h2'>$subheading</p>"; ?>
       
    <!-- content start -->
    <?php print $content ?>
    <?php print $profiler ?>
    <!-- content end -->
    <br />
</div> 
 
</div> 

<?php if (class_exists('DebugToolbar', TRUE)) { echo DebugToolbar::render(); } ?>
</body> 
</html> 

