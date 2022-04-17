<?php
/*
Kürzt HTML-String und schiebt die Ellipsis (...) in den letzten schließenden
Tag. Statt blöd dahinter wie im Original-Joomla.
Bzw. ersetzt sie durch Icon.
*/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * $text
 * $item als Referenz (kann geändert werden)
 * $length Optional
*/

extract($displayData);

$item->textTruncated = null;

if (empty($text) || !is_string($text) || trim($text) === '')
{
	echo '';

	return;
}

if (empty($length))
{
	$length = 500;
}

/* Bug in truncateComplex, wenn der Text schon vor Bearbeitung auf '...' endet.
Dann kommt ein leerer String zurück. */
if (strpos($text, '...') !== false)
{
	$text = str_replace('...', '&hellip;', $text);
}

$truncated = HTMLHelper::_('string.truncateComplex', $text, $length);

if ($truncated === $text)
{
	$item->textTruncated = 0;
}
elseif (mb_substr($truncated, -3) === '...')
{
	$regex = '#(<\/[a-z0-9]+>)(...)$#';
	$truncated = preg_replace(
		$regex,
		' {svg{bi/three-dots}class="text-primary svg-2x"}$1',
		$truncated
	);
	$item->textTruncated = 1;
	$item->readmore = true;
}
?>
<?php echo $truncated; ?>
