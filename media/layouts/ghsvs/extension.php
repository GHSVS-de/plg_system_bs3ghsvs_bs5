<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * $item = article object.
*/

extract($displayData);

if (empty($item->bs3ghsvsFields['extension']['bs3ghsvs_extension_active']))
{
	return;
}

$extensionData = new Registry($item->bs3ghsvsFields['extension']);
?>
<div class="block-download" aria-labelledby="EXTENSION_INFO">
<h3 id="EXTENSION_INFO"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_INFO'); ?></h3>

<h4 class="h6"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_NAME'); ?></h4>
<p class="breakall">
	<?php echo Text::_($extensionData->get('name')); ?>
</p>

<h4 class="h6"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_DESCRIPTION'); ?></h4>
<p>
	<?php echo nl2br($extensionData->get('description')); ?>
</p>

<?php
if (($out = trim($extensionData->get('inspiredby'))))
{
	if (strpos($out, ' ') === false && Bs3ghsvsItem::hasScheme($out))
	{
		$out = '<a href="' . $out . '">' . $out . '</a>';
	} ?>
	<h4 class="h6"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_INSPIREDBY'); ?></h4>
	<p>
		<?php echo $out; ?>
	</p>
<?php
} ?>

<h4 class="h6"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_URL'); ?></h4>
<p>
	<a href="<?php echo $extensionData->get('url'); ?>" class="btn btn-catcolor">
		<span class="visually-hidden"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_URL_DESC')?></span>
		{svg{bi/download}}
	</a>
</p>

<?php
// Is array or not exists if nothing selected in list.
if (!empty($extensionData->get('tergetplatform')))
{
	$targetplatforms = $extensionData->get('tergetplatform');

	foreach ($targetplatforms as $key => $version)
	{
		$targetplatforms[$key] = Text::_(
			'PLG_SYSTEM_BS3GHSVS_EXTENSION_TERGETPLATFORM_' . $version
		);
	} ?>
<h4 class="h6"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_TERGETPLATFORM'); ?></h4>
<p>
	<?php echo implode(', ', $targetplatforms); ?>
</p>
<?php
} ?>

<?php
if ($extensionData->get('updateserver'))
{ ?>
<h4 class="h6"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_UPDATESERVER'); ?></h4>
<p>
	<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_UPDATESERVER_'
		. $extensionData->get('updateserver')); ?>
</p>
<?php
} ?>

<?php
if ($extensionData->get('languages'))
		{
			$flags = Bs3ghsvsArticle::buildFlagImages($extensionData->get('languages')); ?>
<h4 class="h6"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_LANGUAGES'); ?></h4>
<p>
	<?php echo implode(' ', $flags); ?>:
</p>
<?php
		} ?>

<?php
if (($out = trim($extensionData->get('project'))))
{ ?>
<h4 class="h6"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_PROJECT'); ?></h4>
<p>
	<a href="<?php echo $out; ?>" class="btn btn-catcolor">
		<span class="visually-hidden"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_PROJECT_OPEN'); ?></span>
		{svg{bi/link-45deg}}
	</a>
</p>
<?php
} ?>

<?php
if (($out = trim($extensionData->get('comment'))))
{ ?>
<h4 class="h6"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_COMMENT'); ?></h4>
<p>
	<?php echo nl2br($out); ?>
</p>
<?php
} ?>

<?php
if (($out = trim($extensionData->get('history'))))
{ ?>
<h4 class="h6"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_HISTORY'); ?></h4>
<p>
	<a href="<?php echo $out; ?>" class="btn btn-catcolor">
		<span class="visually-hidden"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_HISTORY_OPEN'); ?></span>
		{svg{bi/link-45deg}}
	</a>
</p>
<?php
} ?>

<?php echo Text::_('GHSVS_MODULES_SCRIPT_HINT'); ?>
</div><!--/block-download-->
