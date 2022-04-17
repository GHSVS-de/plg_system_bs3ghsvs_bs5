<?php
/*
J3.8.9
*/
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;

class Bs3GhsvsRegisterIcon
{
	protected static $icon = [
		'create',
		'email', // Blocked.
		'edit', // Erst mal lassen. 2021: Dann doch nicht, weils zu Absturz f�hren kann.
		'print_popup',
		'print_screen',
	];

	public static function register()
	{
		foreach (self::$icon as $method)
		{
			HTMLHelper::register('icon.' . $method, 'Bs3GhsvsRegisterIcon::' . $method);
		}

		return true;
	}

	/**
	 * icon.create
	 */
	public static function create($category, $params, $attribs = [], $legacy = false)
	{
		HTMLHelper::_('iconghsvs.create', $category, $params, $attribs, $legacy);

		return;
	}

	/**
	 * icon.email
	 */
	public static function email($article, $params, $attribs = [], $legacy = false)
	{
		HTMLHelper::_('iconghsvs.email', $category, $params, $attribs, $legacy);

		return;
	}

	/**
	 * icon.print_popup
	 */
	public static function print_popup(
		$article,
		$params,
		$attribs = [],
		$legacy = false,
		$tmpl = 'print',
		$iconClass =''
	) {
		return HTMLHelper::_(
			'iconghsvs.print_popup',
			$article,
			$params,
			$attribs,
			$legacy,
			$tmpl,
			$iconClass
		);
	}

	/**
	 * icon.print_screen
	 */
	public static function print_screen($article, $params, $attribs = [], $legacy = false)
	{
		return HTMLHelper::_('iconghsvs.print_screen', $article, $params, $attribs, $legacy);
	}

	public static function edit($article, $params, $attribs = [], $legacy = false)
	{
		return '';
	}
}
