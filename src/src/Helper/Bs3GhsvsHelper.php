<?php
/*
@since 2023-11
Methoden für Plugin plg_system_bs3ghsvs, auch um Aufrufe wie PlgSystemBS3Ghsvs::getWa() loszuwerden, die mit neuer Struktur floppen.
*/

namespace GHSVS\Plugin\System\Bs3Ghsvs\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class Bs3GhsvsHelper
{
	private static $wa;

	/*
	Initialisere WAM und lade joomla.asset.json.
	*/
	public static function getWa()
	{
		if (empty(self::$wa))
		{
			self::$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
			self::$wa->getRegistry()->addExtensionRegistryFile('plg_system_bs3ghsvs');
		}

		return self::$wa;
	}
}
