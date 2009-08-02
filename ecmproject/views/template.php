<?php
$this->load->helper('url');
if (!isset($pageTitle))
    $pageTitle = '';
else
    $pageTitle = ' :: ' . $pageTitle;

if (!isset($messages))
    $messages = array();
if (!isset($errors))
    $errors = array();
if ($this->session->flashdata('messages')) 
	$messages = $messages + $this->session->flashdata('messages');
if ($this->session->flashdata('errors')) 
	$errors  = $errors + $this->session->flashdata('errors');

?><!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
<title>Electronic Convention Management (ECM)<?php echo $pageTitle ?></title> 
<link href="<?php echo base_url() ?>css/main.css" rel="stylesheet" type="text/css" /> 
<?= $_scripts ?>
<?= $_styles ?>
<script type="text/javascript" src="<?php echo base_url() ?>js/jquery-1.3.2.min.js"></script>
</head> 
 
<body> 
 
<!-- Content container beings here --> 
<div id="container"> 
    <!-- Header --> 
    <div id="header"></div> 
 
    <!-- Left Sidebar. --> 
    <div id="menu"> 
        <?php if (!$isLoggedIn): ?>
        <?php $this->load->view('global/loginBox'); ?>
        <?php endif; ?>
        <?php if ($menu || $isLoggedIn): ?>
        <ul> 
            <li class="menuItem title"><a href="<?php base_url(); ?>">Menu</a></li>
            <?php if ($isLoggedIn): ?>
            <li><b><?= htmlentities($user_name) ?></b></li>
            <li>&nbsp;</li>
            <?php endif; ?>
            <?php if($menu): ?><?php foreach ($menu as $m): ?>
            <li><?php echo anchor($m['url'], $m['title']); ?></li> 
            <?php endforeach; ?><?php endif ?>

            <?php if ($isLoggedIn): ?>
            <li><?php echo anchor('user/logout', 'Logout'); ?></li>
            <?php endif; ?>
        </ul>
        <?php endif ?>
    </div> 
 
    <!-- Content Pane (Right side) --> 
    <div id="content">
    <?php if ($heading) echo "<h2>$heading</h2>"; ?>
    <?php if ($subheading) echo "<h3>$subheading</h3>"; ?>
    
<?php foreach ($messages as $msg) { echo '<p class="msg">'.$msg.'</p>'; } ?>
<?php foreach ($errors as $err) { echo '<p class="errormsg">'.$err.'</p>'; } ?>

    <br />
    <!-- content start -->
    <?php print $content ?>
    <!-- content end -->
    <br />
</div> 
<div id="footer"><p><a href="http://jigsaw.w3.org/css-validator/">CSS 2.1 Validated</a>, <a href="http://validator.w3.org/check?uri=referer">XHTML 1.0 Strict Validated</a>, <a href="mailto:stt@sfu.ca">Admin: stt@sfu.ca</a></p></div> 
<!-- Page rendered in {elapsed_time} seconds -->
 
</div> 
</body> 
</html> 
 
