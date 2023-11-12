<?php
namespace GHSVS\Plugin\System\Bs3Ghsvs\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

class ImgResizeGhsvsInstalledField extends FormField
{
	protected $type = 'ImgResizeGhsvsInstalled';

	protected function getInput()
	{
		if (is_file(JPATH_LIBRARIES
			. '/imgresizeghsvs/vendor/autoload.php'))
		{
			return '';
		}

		return Text::_('PLG_SYSTEM_BS3GHSVS_LIB_IMGRESIZEGHSVSGHSVS_MISSING');
	}
}
