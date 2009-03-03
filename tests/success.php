<?php

// put full path to Smarty.class.php
require('../lib/smarty/Smarty.class.php');
$smarty = new Smarty();
$smarty->template_dir = '../themes/default/templates';

$smarty->compile_dir = '../themes/default/compiled';
$smarty->cache_dir = '../themes/default/cache';
$smarty->config_dir = '../themes/default/configs';

$smarty->assign('name', 'Ned');
$smarty->display('success.tpl');
