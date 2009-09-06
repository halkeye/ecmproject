<?php defined('SYSPATH') or die('No direct script access.');

class Formo_required {

	public static $symbol = '<span class="required">*</span>';
			
	public static function load()
	{
		Event::add('formoel.pre_render', 'Formo_required::add_symbol');
		Event::add('formogroup.pre_render', 'Formo_required::add_symbol');
	}
	
	public static function add_symbol()
	{
		if (Event::$data->required)
		{
			Event::$data->label_close = ':'.self::$symbol.'</label>';
		}
	}

} // end Formo_required Plugin
