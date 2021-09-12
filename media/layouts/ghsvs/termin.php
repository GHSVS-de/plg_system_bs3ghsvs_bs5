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

if (empty($articleId))
{
	return;
}

if (!isset($dateFormat))
{
	$dateFormat = Text::_('DATE_FORMAT_LC4');
}

JLoader::register(
	'Bs3ghsvsArticle',
	JPATH_PLUGINS . '/system/bs3ghsvs/Helper/ArticleHelper.php'
);

if (($dates = Bs3ghsvsArticle::getTerminData($articleId)) !== false)
{
	$nullDate = Factory::getDbo()->getNullDate();
	$start = ($dates->start && $dates->start !== $nullDate) ? $dates->start : null;
	$end = ($dates->end && $dates->end !== $nullDate) ? $dates->end : null;

	if (!$start && !$end)
	{
		return;
	} ?>
	<p class="terminGhsvs alert alert-info"><?php echo Text::_('GHSVS_DATUM'); ?>

	<?php
	if ($start)
	{
		echo HTMLHelper::_('date', $start, $dateFormat, false);
	}

	if ($end)
	{
		echo ' bis ' . HTMLHelper::_('date', $end, $dateFormat, false);
	}
	?>
	</p>
<?php
}
