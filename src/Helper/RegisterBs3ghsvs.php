<?php
/*
J3.8.9
*/
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;

class Bs3GhsvsRegisterBs3ghsvs
{
	protected static $toRegister = [
		'bs3ghsvs' => [
			'layout',
			'rendermodules',
			'templatejs',
			'addsprungmarke',
			'spoiler', // Eigener Bootstrap Spoiler-Button.
			'activeToSession',
			'toTop',
		],
	];

	public static function register()
	{
		$prefix = 'JHtml';

		foreach (self::$toRegister as $file => $what)
		{
			$class = $prefix . ucfirst($file);

			JLoader::register($class, __DIR__ . '/../html/' . $file . '.php');

			foreach ($what as $method)
			{
				HTMLHelper::register('bs3ghsvs.' . $method, $class . '::' . $method);
			}
		}

		return true;
	}
}
