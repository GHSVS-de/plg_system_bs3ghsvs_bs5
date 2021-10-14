<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * $item: article
*/

extract($displayData);

if (
	empty($item->bs3ghsvsFields['various']['bs3ghsvs_various_active'])
	|| empty($item->bs3ghsvsFields['various']['articleStatus'])
){
	return;
}

$articleStatus = $item->bs3ghsvsFields['various']['articleStatus'];
?>
<p class="articleStatus articleStatus_<?php echo $articleStatus; ?>
	alert alert-warning mb-0">
	<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_ARTICLESTATUS_' . $articleStatus); ?>
</p>
