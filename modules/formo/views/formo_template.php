<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="keywords" content="utah web design, utah, salt lake city, web firm, utah search engine optimization, utah seo" />
<meta name="Description" content="Avant Hill Design is one of Utah's cutting-edge web design and development companies." />
<link href='<?php echourl::base()?>modules/formo/assets/css/formo.css' rel='stylesheet' type='text/css' />
<?php if ($scripts):?>
<script type="text/javascript" src="<?php echourl::base()?>modules/formo/assets/js/jquery.js"></script>
<script type="text/javascript" src="<?php echourl::base()?>modules/formo/assets/js/jquery.form.js"></script>
<script type="text/javascript" src="<?php echourl::base()?>modules/formo/assets/js/jquery.autocomplete.js"></script>
<?php endif; ?>
<title><?php echo$title?></title>
</head>

<body>
<div id="header">
	<div id='inside_head'>
		<div id='logo'>
			<a href='http://www.avanthill.com/formo'><img src="<?php echourl::base()?>modules/formo/assets/img/logo.gif" alt="Avant Hill Design" /></a>
		</div>
		<div id='menu'>
			<ul>
			</ul>
		</div>
		<div class="clear"></div>
	</div>
</div>

<div class="container padded" id="hcon">
	<?php echo$header?>
	<div id='hbox'>
		<?php echo$content?>
		<div style="margin-top:20px">Executed in {execution_time} seconds.</div>
	</div>
</div>

<div id="footer">
<div id="inside_footer">
<p>
	<?php echohtml::anchor('formo_demo','Playground')?> | 
	<?php echohtml::anchor('formo_demo/demo1','Basic')?> | 
	<?php echohtml::anchor('formo_demo/demo2','Templates')?> | 
	<?php echohtml::anchor('formo_demo/demo3','More Settings')?> | 
	<?php echohtml::anchor('formo_demo/demo4','Groups')?> | 
	<?php echohtml::anchor('formo_demo/demo5','Comments')?> |
	<?php echohtml::anchor('formo_demo/demo6','Captcha')?>	|
	<?php echohtml::anchor('formo_demo/demo7','Syntax')?> |
	<?php echohtml::anchor('formo_demo/demo8','Ajax')?>	
</p>
<p>Copyright &copy; 2008 Avant Hill Design. All rights reserved.</p>
</div>
</div> <!-- #footer-->
</body>

</html>