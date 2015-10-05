<?php

/**
 * @package		Field Editor
 * @author		Vector Media Group
 * @copyright	Copyright (c) 2015, Vector Media Group
 */

if ( ! defined('FIELD_EDITOR_VERSION'))
{
	define('FIELD_EDITOR_VERSION', '2.0.0');
}

if (defined('PATH_THEMES'))
{
	if ( ! defined('PATH_THIRD_THEMES'))
	{
		define('PATH_THIRD_THEMES', PATH_THEMES.'third_party/');
	}
	
	if ( ! defined('URL_THIRD_THEMES'))
	{
		define('URL_THIRD_THEMES', get_instance()->config->slash_item('theme_folder_url').'third_party/');
	}
}