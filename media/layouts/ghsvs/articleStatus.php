<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * $item: article
*/

extract($displayData);

if (empty($item->bs3ghsvsFields['various']['articleStatus']))
{
	return;
}

$articleStatus = $item->bs3ghsvsFields['various']['articleStatus'];

if ($articleStatus > 0 && Factory::getApplication()->input->get('view')
	=== 'article')
{
	Factory::getDocument()->setMetadata('robots', 'noindex, follow');
}
?>
<p class="articleStatus articleStatus_<?php echo $articleStatus; ?>
	alert alert-warning mb-0">
	<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_ARTICLESTATUS_' . $articleStatus); ?>
</p>
