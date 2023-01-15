<?php
/*
#### Include via:

echo LayoutHelper::render('ghsvs.toTop');

OR

HTMLHelper::_('bs3ghsvs.layout', 'ghsvs.toTop');

#### Example for joomla.asset.json:

{
	"name": "herzpraxis_astroid_ghsvs.toTop",
	"description": "Override im Zusammenspiel mit JLayout etc.. Leider geht das nicht, dass man nur Style plg_system_bs3ghsvs.toTop unten �berschreibt.",
	"type": "preset",
	"dependencies": [
		"plg_system_bs3ghsvs.toTop#script",
		"herzpraxis_astroid_ghsvs.toTop#style"
	]
},
{
	"name": "herzpraxis_astroid_ghsvs.toTop",
	"description": "Override im Zusammenspiel mit JLayout etc. Leider geht das nicht, dass man hier Style plg_system_bs3ghsvs.toTop verwendet.",
	"type": "style",
	"uri": "toTop.css",
	"version": "2023.01.09",
	"attributes": {
		"defer": true
	}
		}

#### Example for toTop.scss:

#toTop
{
	z-index:989;
	text-align:center;

	position:fixed;
	right:0.8rem;
	bottom:0.8rem;
	cursor:pointer;
	display:none;
color: black;
	&.btn
	{
		padding: 0;
		line-height: 1;
	}
}

#toTop.visible
{
	display: block;
}

#### Example for AstroidGhsvsHelper with toTop|noInsert:

AstroidGhsvsHelper::$filesToCompile = [
	//'editorghsvs|noInsert',
	//'slick|noInsert',
	//'template-zalta|noInsert',
	'template',
	'mod_splideghsvs|noInsert',
	'toTop|noInsert',
];
*/
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->usePreset('herzpraxis_astroid_ghsvs.toTop');

// Auf mehrseitigen Blogansichten wechselt sonst die Seite.
$uri = Uri::getInstance()->toString();
?>
<a href="<?php echo $uri; ?>#TOP" class="btnsss btn-lightsss" id="toTop" tabindex="-1">
	<span class="visually-hidden">
		<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_TO_TOP'); ?>
	</span>
	{svg{bi/arrow-up-circle}class="bg-whitesss"}</a>

<!--/ arrow-up-square arrow-up-->
