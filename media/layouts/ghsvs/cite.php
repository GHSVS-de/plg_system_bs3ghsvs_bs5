<?php
defined('_JEXEC') or die;

/**
 * $item ($article)
*/

extract($displayData);

if (empty($item->bs3ghsvsFields['cite']['text']))
{
	return;
}

$text = $item->bs3ghsvsFields['cite']['text'];
$source = $item->bs3ghsvsFields['cite']['source'];
?>
<hr>
<blockquote class="citeGhsvs">
	<q><?php echo $text; ?></q>
	<?php if ($source)
	{ ?>
	<footer><cite><?php echo $source; ?></cite></footer>
	<?php
	} ?>
</blockquote>
