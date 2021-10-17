<?php
/*
page_header_n_icons.php
GHSVS 2014-12-23

Fasst Print-Email-Icons und page-header zusammen, so, dass Icons rechts vom Titel floaten können,
anstatt unterhalb Titel.
Sollte sowohl in Blog, Featured und Article-View funktionieren.
*/
?>
<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$params = $displayData['item']->params;
$print = $displayData['print'];
$titletag = $params->get('article_titletag', 'h2');
$displayData = $displayData['item'];

// com_tags-Views
$typeAlias = isset($displayData->type_alias) ? $displayData->type_alias : false;
\JLoader::register('ContentHelperRoute', JPATH_SITE
	. '/components/com_content/helpers/route.php');

$linkHeadline = ($params->get('link_titles') && $params->get('access-view'));
$maskHClass = ($params->get('mask_pageheaderclass_ghsvs', 0) ? 'Masked' : '');

if ($linkHeadline && empty($displayData->linkGhsvs))
{
 switch($typeAlias){
  case 'com_content.article':
  $displayData->linkGhsvs = Route::_(ContentHelperRoute::getArticleRoute($displayData->slug, $displayData->catid));
  break;
  case 'com_content.category':
  $displayData->linkGhsvs = Route::_(ContentHelperRoute::getCategoryRoute($displayData->slug));
  break;
  default:
  $displayData->linkGhsvs = Route::_(ContentHelperRoute::getArticleRoute($displayData->slug, $displayData->catid));
 }
}
?>
<?php echo HTMLHelper::_('bs3ghsvs.layout', 'ghsvs.icons',
 array(
  'params' => $params,
  'item' => $displayData,
  'print' => $print,
  'position' => 'above'
 ));
?>

<?php if ($params->get('show_title'))
{ ?>
<div class="page-header<?php echo $maskHClass;?> state<?php echo $displayData->state ?>">
	<?php
		$title = $this->escape($displayData->title);
	?>
	<?php echo '<' . $titletag ?>><?php
	if ($linkHeadline) : ?>
		<a href="<?php echo $displayData->linkGhsvs; ?>">
		<?php echo $title; ?>
		</a>
	<?php else :
		echo $title;
	endif; ?>
	<?php
		echo LayoutHelper::render('ghsvs.articleSubtitle', ['item' => $displayData]);
	?><?php echo '</' . $titletag . '>' ?>
	<?php
		echo LayoutHelper::render('ghsvs.articleStatus', ['item' => $displayData]);
	?>
	<?php if (1== 2 && !empty($displayData->pagination))
	{ ?>
	<div class="articlePagination above">
	<?php echo $displayData->pagination; ?>
	</div>
	<?php
	} ?>
  </div><!--/page-header<?php echo $maskHClass;?>-->

<?php
} ?>
<?php echo HTMLHelper::_('bs3ghsvs.layout', 'ghsvs.icons',
 array(
  'params' => $params,
  'item' => $displayData,
  'print' => $print,
  'position' => 'below'
 ));
?>
