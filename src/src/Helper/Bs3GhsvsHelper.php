<?php
/*
@since 2023-11
Methoden für Plugin plg_system_bs3ghsvs, auch um Aufrufe wie PlgSystemBS3Ghsvs::getWa() loszuwerden, die mit neuer Struktur floppen.
*/

namespace GHSVS\Plugin\System\Bs3Ghsvs\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use GHSVS\Plugin\System\Bs3Ghsvs\Helper\Bs3GhsvsTemplateHelper;

#[\AllowDynamicProperties]
class Bs3GhsvsHelper
{
	private static $wa;
	private static $pluginParams;
	private static $templateName;

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

	public static function getTemplateName()
	{
		if (empty(self::$templateName))
		{
			self::$templateName = Bs3GhsvsTemplateHelper::getTemplateName();
		}
		return self::$templateName;
	}

	public static function getPluginParams($plugin = ['system', 'bs3ghsvs'])
	{
		if (empty(self::$pluginParams) || !(self::$pluginParams instanceof Registry))
		{
			$model = Factory::getApplication()->bootComponent('plugins')
				->getMVCFactory()->createModel('Plugin', 'Administrator', ['ignore_request' => true]);
			$pluginObject = $model->getItem(['folder' => $plugin[0], 'element' => $plugin[1]]);

			if (!\is_object($pluginObject) || empty($pluginObject->params))
			{
				self::$pluginParams = new Registry();
				self::$pluginParams->set('isEnabled', -1);
				self::$pluginParams->set('isInstalled', 0);
			}
			elseif (!($pluginObject->params instanceof Registry))
			{
				self::$pluginParams = new Registry($pluginObject->params);
				self::$pluginParams->set('isEnabled', ($pluginObject->enabled ? 1 : 0));
				self::$pluginParams->set('isInstalled', 1);
			}
		}
		return self::$pluginParams;
	}
}
