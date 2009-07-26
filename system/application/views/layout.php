<?php
$this->load->helper('url');
if (!isset($title))
    $title = '';
else
    $title = ' :: ' . $title;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
<title>Electronic Convention Management (ECM)<?php echo $title ?></title> 
<link href="<?php echo base_url() ?>css/main.css" rel="stylesheet" type="text/css" /> 
<script type="text/javascript" src="<?php echo base_url() ?>js/jquery-1.3.2.min.js"></script>
</head> 
 
<body> 
 
<!-- Content container beings here --> 
<div id="container"> 
    <!-- Header --> 
    <div id="header"></div> 
 
    <!-- Left Sidebar. --> 
    <div id="menu"> 
        <?php if (!isset($isLoggedIn) || !$isLoggedIn): ?>
        <?php $this->load->view('global/loginBox'); ?>
        <?php endif; ?>
        <?php if (isset($menu)): ?>
        <?php $this->load->view('global/menu'); ?>
        <?php endif; ?>
        </ul> 
    </div> 
 
    <!-- Content Pane (Right side) --> 
    <div id="content"> 
    <h2><?php echo $heading ?></h2>
    <h3><?php echo $subheading ?></h3>
    <br />
    <!-- content start -->
    <?php $this->load->view($view); ?>
    <!-- content end -->
    <br />
</div> 
<div id="footer"><p><a href="http://jigsaw.w3.org/css-validator/">CSS 2.1 Validated</a>, <a href="http://validator.w3.org/check?uri=referer">XHTML 1.0 Strict Validated</a>, <a href="mailto:stt@sfu.ca">Admin: stt@sfu.ca</a></p></div> 
<!-- Page rendered in {elapsed_time} seconds -->
 
</div> 
</body> 
</html> 
 
