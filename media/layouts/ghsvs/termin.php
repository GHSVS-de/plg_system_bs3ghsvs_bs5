<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * $articleId
 * $dateFormat
*/

extract($displayData);

if (empty($item->bs3ghsvsFields['termin']['bs3ghsvs_termin_active']))
{
	return;
}

$nullDate = Factory::getDbo()->getNullDate();
$dates = (object) $item->bs3ghsvsFields['termin'];
$dates->start = ($dates->start && $dates->start !== $nullDate) ? $dates->start
	: null;
$dates->end = ($dates->end && $dates->end !== $nullDate) ? $dates->end : null;

if (!$dates->start && !dates->$end)
{
	return;
}

if (!isset($dateFormat))
{
	$dateFormat = Text::_('DATE_FORMAT_LC4');
}
?>
<p class="terminGhsvs alert alert-info"><?php echo Text::_('GHSVS_DATUM'); ?>
	<?php if ($dates->start)
	{
		echo HTMLHelper::_('date', $dates->start, $dateFormat, false);
	}

	if ($dates->end)
	{
		echo ' bis ' . HTMLHelper::_('date', $dates->end, $dateFormat, false);
	} ?>
</p>
