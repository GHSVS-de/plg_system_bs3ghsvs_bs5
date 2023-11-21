<?php
namespace GHSVS\Plugin\System\Bs3Ghsvs\HTML;
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;

class RegisterJHtml
{
	private static $serviceMap = [
		'bootstrap' => Helpers\BootstrapGhsvsJHtml::class,
		'bs3ghsvs' => Helpers\Bs3ghsvsJHtml::class,
		'iconghsvs' => Helpers\IconGhsvsJHtml::class,
	];

	public static function register($whatKey)
	{
		if (!isset(self::$serviceMap[$whatKey]))
		{
			return false;
		}
		HTMLHelper::getServiceRegistry()->register($whatKey, self::$serviceMap[$whatKey], true);
		return true;
	}

}
