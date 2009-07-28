<?php
$this->load->helper('url');
if (!isset($pageTitle))
    $pageTitle = '';
else
    $pageTitle = ' :: ' . $pageTitle;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
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
            <li class="title">Menu<?php if ($isLoggedIn) { echo ' - ' . htmlentities($user_name); } ?></li>
            <?php if($menu): ?><?php foreach ($menu as $m): ?>
            <li><?php echo anchor($m['url'], $m['title']); ?></li> 
            <?php endforeach; ?><?php endif ?>

            <?php if ($isLoggedIn): ?>
            <li><?php echo anchor('user/logout', 'Logout'); ?></li>
            <?php endif; ?>

            </li>
        </ul>
        <?php endif ?>
    </div> 
 
    <!-- Content Pane (Right side) --> 
    <div id="content">
    <h2><?php echo $heading ?></h2>
    <h3><?php echo $subheading ?></h3>
    <br />
    
<?php if($this->session->flashdata('message')) : ?>
	<p><?=$this->session->flashdata('message')?></p>
<?php endif; ?>

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
 
