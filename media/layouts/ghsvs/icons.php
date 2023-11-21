<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.framework');

$canEdit = $displayData['params']->get('access-edit');

$position = (!isset($displayData['position']) ? '' : ' ' . $displayData['position']);
?>
<?php
if (empty($displayData['print']))
{ ?>
	<?php if ($canEdit) : ?>
	<div class="icons<?php echo $position; ?>">
		<p class="edit-icon">
			<?php echo HTMLHelper::_('icon.edit', $displayData['item'], $displayData['params']); ?>
		</p>
	</div><!--/icons-->
	<?php endif; ?>
<?php
}
elseif ($position != ' below')
{ ?>
	<div class="printButton pull-right btn" style="margin-left:10px;">
	<?php

	#echo ' 4654sd48sa7d98sD81s8d71dsa <pre>' . print_r($displayData['params'], true) . '</pre>';exit;

	echo HTMLHelper::_('iconghsvs.print_screen', $displayData['params']); ?>
	</div>
<?php } ?>
