<?php
namespace GHSVS\Plugin\System\Bs3Ghsvs\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

class AssetsBeField extends FormField
{
	protected $type = 'AssetsBe';

	// Path inside /media/.
	protected $basePath = 'plg_system_bs3ghsvs';

	protected function getInput()
	{
		// Only explicit "true" will be respected.
		$loadjs     = isset($this->element['loadjs'])
			? (string) $this->element['loadjs'] : 'false';
		$loadcss    = isset($this->element['loadcss'])
			? (string) $this->element['loadcss'] : 'false';
		$loadJQuery = isset($this->element['loadJQuery'])
			? (string) $this->element['loadJQuery'] : 'false';

		if ($loadcss === 'true' || $loadJQuery === 'true' || $loadjs === 'true')
		{
			$file = $this->basePath . '/backend';
			$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

			if ($loadcss === 'true')
			{
				$wam->registerAndUseStyle(
					$this->basePath . '.AssetsBeField.loadcss', $file . '.css',
					['weight' => 1000]
				);
			}

			if ($loadJQuery === 'true')
			{
				$wam->useScript('jquery-migrate');
			}

			if ($loadjs === 'true')
			{
				$wam->registerAndUseScript(
					$this->basePath . '.AssetsBeField.loadjs', $file . '.js',
					['weight' => 1000]
				);
			}
		}

		return '';
	}

	protected function getLabel()
	{
		return '';
	}
}
