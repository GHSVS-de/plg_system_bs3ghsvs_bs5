<?php
/*
 * Collect configuration infos from plgSystemBs3Ghsvs.json in templates html/ folders.
 * Simple output.
 */
namespace GHSVS\Plugin\System\Bs3Ghsvs\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// @since 2023-11
use GHSVS\Plugin\System\Bs3Ghsvs\Helper\Bs3GhsvsTemplateHelper;

class TemplatesJsonConfigurationInfoField extends FormField
{
	protected $type = 'TemplatesJsonConfigurationInfo';

	protected function getInput()
	{
		$html = ['<h4>' . Text::_('PLG_SYSTEM_BS3GHSVS_TEMPLATES_JSON_CONFIGURATION_INFO') . '</h4>'];
		$templates = Bs3GhsvsTemplateHelper::getActiveInTemplates();

		if (!$templates)
		{
			$html[] = Text::_('PLG_SYSTEM_BS3GHSVS_TEMPLATES_JSON_CONFIGURATION_NONE_FOUND');
		}
		else
		{
			foreach ($templates as $template)
			{
				$path = 'templates/' . $template . '/html/plgSystemBs3Ghsvs.json';
				$html[] = '<h5>* Template: ' . $template . '<br>** File: <a href="'
					. Uri::root() . $path . '" target="_blank">'
					. $path . '</a></h5>';
				$options = Bs3GhsvsTemplateHelper::getTemplateOptionsFromJson($template);

				foreach ($options as $key => $dingsbums)
				{
					$html[] = '<h6> &gt;&gt; ' . $key . '</h6>';
					$html[] = '<pre>' . print_r($dingsbums, true) . '</pre>';
				}
			}
		}

		return implode('', $html);
	}
}
