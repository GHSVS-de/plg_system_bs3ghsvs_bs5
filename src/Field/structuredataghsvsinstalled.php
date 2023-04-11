<?php
defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

class plgSystemBs3GhsvsFormFieldStructuredataGhsvsInstalled extends FormField
{
	protected $type = 'StructuredataGhsvsInstalled';

	protected function getInput()
	{
		if (!is_file(JPATH_LIBRARIES
			. '/structuredataghsvs/vendor/autoload.php'))
		{
			return Text::_('PLG_SYSTEM_BS3GHSVS_LIB_STRUCTUREDATAGHSVS_MISSING');
		}

		return;
	}
}
