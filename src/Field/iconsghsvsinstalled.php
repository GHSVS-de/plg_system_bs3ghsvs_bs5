<?php
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

class plgSystemBs3GhsvsFormFieldIconsGhsvsInstalled extends FormField
{
	protected $type = 'iconsGhsvsInstalled';

	protected function getInput()
	{
		if (is_file(JPATH_SITE . '/media/iconsghsvs/svgs/prepped-icons.json'))
		{
			return '';
		}

		return Text::_('PLG_SYSTEM_BS3GHSVS_FILE_ICONSGHSVS_MISSING');
	}
}
