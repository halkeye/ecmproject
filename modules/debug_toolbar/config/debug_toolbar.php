<?php defined('SYSPATH') or die('No direct script access.');

/* 
 * If true, the debug toolbar will be automagically displayed
 * NOTE: if IN_PRODUCTION is set to TRUE, the toolbar will
 * not automatically render, even if auto_render is TRUE
 */
$config['auto_render'] = TRUE;

/* 
 * If true, the toolbar will default to the minimized position
 */
$config['minimized'] = FALSE;

/* 
 * Location of icon images
 * relative to your site_domain
 */
$config['icon_path'] = 'images';

/* 
 * List config files you would like to exclude
 * from showing in the toolbar (without extension).
 * Alternatively, set to true to stop all 
 * config files from showing.
 */
$config['skip_configs'] = array('database', 'encryption');

/*
 * Log toolbar data to FirePHP
 */
$config['firephp_enabled'] = FALSE;

/* 
 * Enable or disable specific panels
 */
$config['panels'] = array(
	'benchmarks'      => TRUE,
	'database'        => TRUE,
	'vars_and_config' => TRUE,
	'logs'            => TRUE,
	'ajax'            => TRUE,
	'files'           => TRUE
);

/*
 * Toolbar alignment
 * options: right, left, center
 */
$config['align'] = 'right';

/*
 * Secret Key
 */ 
$config['secret_key'] = FALSE;

