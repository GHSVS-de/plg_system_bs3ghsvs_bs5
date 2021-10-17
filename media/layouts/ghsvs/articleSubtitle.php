<?php
defined('_JEXEC') or die;

/**
 * $item: article
 * $params: Optional. Registry object. If not provided $item->params.
*/

extract($displayData);

if (!isset($params))
{
	$params = $item->params;
}

if (!$params->get('show_title')
	|| $params->get('show_articlesubtitle') === 0
	|| empty($item->bs3ghsvsFields['various']['articlesubtitle']))
{
	return '';
}

$articlesubtitle = htmlspecialchars(
	$item->bs3ghsvsFields['various']['articlesubtitle'], ENT_COMPAT, 'utf-8');
?>
<small class="articlesubtitle text-muted fst-italic">
	(<?php echo $articlesubtitle; ?>)
</small>
