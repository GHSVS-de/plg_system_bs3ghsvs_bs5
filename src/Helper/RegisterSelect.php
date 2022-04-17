<?php
/*
J3.8.9
*/
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;

class Bs3GhsvsRegisterSelect
{
	protected static $select = [
		'radiolist',
	];

	public static function register()
	{
		foreach (self::$select as $method)
		{
			HTMLHelper::register('select.' . $method, 'Bs3GhsvsRegisterSelect::' . $method);
		}

		return true;
	}

	/**
	 * select.radiolist
	 */
	public static function radiolist(
		$data,
		$name,
		$attribs = null,
		$optKey = 'value',
		$optText = 'text',
		$selected = null,
		$idtag = false,
		$translate = false
	) {
		return HTMLHelper::_(
			'selectghsvs.radiolist',
			$data,
			$name,
			$attribs,
			$optKey,
			$optText,
			$selected,
			$idtag,
			$translate
		);
	}
}
