<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.framework');

$canEdit = $displayData['params']->get('access-edit');

// ToDo $displayData['align']
$align = isset($displayData['align']) ? $displayData['align'] : 'right';
$time = str_replace('.', '', uniqid('', true));
?>

<?php if ($canEdit || $displayData['params']->get('show_print_icon') || $displayData['params']->get('show_email_icon')) : ?>
 <div class="iconscog">
  <div class="hidden-print dropdown text-<?php echo $align;?>" id="dropdown-<?php echo $time;?>">
   <button class="btn dropdown-toggle" type="button" id="pagination-dropdown-<?php echo $time;?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <span class="icon-cog"></span>
    <span class="caret"></span>
   </button>
   <ul class="dropdown-menu dropdown-menu-<?php echo $align;?>" aria-labelledby="pagination-dropdown-<?php echo $time;?>">
<?php
if ($displayData['params']->get('show_print_icon'))
{ ?>
				<li class="print-icon"> <?php echo HTMLHelper::_('iconghsvs.print_popup', $displayData['item'], $displayData['params']); ?></li>
<?php
	} ?>
<?php
if ($displayData['params']->get('show_email_icon'))
{ ?>
				<li class="email-icon"> <?php echo HTMLHelper::_('icon.email', $displayData['item'], $displayData['params']); ?> </li>
<?php
	} ?>
<?php if ($canEdit)
	{ ?>
				<li class="edit-icon"> <?php echo HTMLHelper::_('icon.edit', $displayData['item'], $displayData['params']); ?> </li>
<?php
	} ?>
 </ul>
</div><!--/dropdown-->
</div><!--/iconscog-->

<?php endif; ?>
<div class="pull-right visible-print">
	<?php

	#echo ' 4654sd48sa7d98sD81s8d71dsa <pre>' . print_r($displayData, true) . '</pre>';exit;

	echo HTMLHelper::_('iconghsvs.print_screen', $displayData['params']); ?>
</div>
