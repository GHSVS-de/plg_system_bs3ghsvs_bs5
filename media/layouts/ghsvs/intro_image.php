<?php
/* intro_image ohne Kokolores wie venobox oder readmore */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

JLoader::register('Bs3ghsvsItem', JPATH_PLUGINS
	. '/system/bs3ghsvs/Helper/ItemHelper.php');

$options = new Registry(isset(
	$displayData['options']) ? $displayData['options'] : []
);

$item = $displayData['item'];
$images = Bs3ghsvsItem::getItemImagesghsvs($item);

if ($image = $images->get('image_intro'))
{
	echo PHP_EOL . '<!--File: ' . str_replace(JPATH_SITE, '', dirname(__FILE__)) . '/'. basename(__FILE__) . '-->' . PHP_EOL;

	$figureClasses = ['autoLimited item-image image_intro'];

	// Siehe Beschreibung in der Datei.
	require(__DIR__ . '/imgClassTranslator.php');
	$figureClass = $images->get('float_intro', 'ghsvs_img-default');

	if (!empty($imgClassTranslator[$figureClass]))
	{
		$figureClass = $imgClassTranslator[$figureClass];
	}

	$figureClasses[] = $figureClass;

	// e.g. blog 'leadItem'. Use it for different $mediaQueries if you want!
	$whichItem = $options->get('whichItem', '');
	$figureClasses[] = $whichItem;

	$mediaQueries = [];
	$classes = $options->get('classes', '');
	$alt = $images->get('image_intro_alt');
	$caption = $images->get('image_intro_caption');
	$alt = htmlspecialchars(($alt ? $alt : $caption), ENT_QUOTES, 'UTF-8');
	$caption = htmlspecialchars($caption, ENT_QUOTES, 'UTF-8');

	$picture = array('<picture>');

	// From plg_system_bs3ghsvs. If resizer active.
	// Returns empty array if nothing.
	$imgs = $images->get('introtext_imagesghsvs');

	if (!empty($imgs[0]) && is_array($imgs[0]))
	{
		// ############# LEAD ITEMS!!! START ################
		// Leading items have normally col-6 of page full width (currently 1440px).
		if ($whichItem === 'leadItem')
		{
			$mediaQueries = array(
				// xs-max; Bild ganze Breite, oberhalb Text
				'(max-width: 575px)' => '_s',
				// sm-max;  Bild ganze Breite, oberhalb Text
				'(max-width: 767px)' => '_m',
				// md-max, Bild rechts, neben Text
				//'(max-width: 991px)' => !empty($imgs['_l']) ? $imgs['_l'] : null,
				// lg-max
				'(max-width: 1199px)' => '_l',
				// xl-min
				'(min-width: 1200px)' => '_x',

				// Largest <source> without mediaQuery. Also for fallback <img> src, width and height calculation.
				// Value only if you want to force one. Otherwise _x or fallback _u is used.
				'srcSetKey' => '',
			);
		}
		// ############# INTRO ITEMS!!! START ################
		else
		{
			$mediaQueries = array(

				// figure hat dann max 332px
				'(max-width: 410px)' => '_s',

				// figure hat dann max 402px.
				'(max-width: 460px)' => '_m',

				// Hier hört xs auf.
				'(max-width: 575.98px)' => '_l',

				// sm endet (1 Card-Spalte). Bild hat 700px. figure 689px.
				'(max-width: 767.98px)' => '_x',

				// md-max (2 Card-Spalten). Und lg
				'(max-width: 800.98px)' => '_s',
				'(max-width: 880.98px)' => '_m',
				'(max-width: 980.98px)' => '_l',
				'(max-width: 1199.98px)' => '_x',

				// xl startet (3 Card-Spalten)
				'(max-width: 1320.98px)' => '_m',
				'(max-width: 1700.98px)' => '_l',
				'(min-width: 1701px)' => '_x',

				// Largest <source> without mediaQuery. Also for fallback <img> src, width and height calculation.
				// Value only if you want to force one. Otherwise _x or fallback _u is used.
				'srcSetKey' => '',
			);
		}
		// ############# INTRO ITEMS!!! END ################
	}

	// Use $imgs not $imgs[0] because of ['order'] index.
	// And because other $imgs collections can contain more than just 1 image.
	$sources   = Bs3ghsvsItem::getSources($imgs, $mediaQueries, $image);

	$sources   = $sources[0];
	$picture[] = $sources['sources'];
	$picture[] = '<img loading="lazy"'
		. ' src="' . $sources['assets']['img'] . '"'
		. ' alt="' . $alt . '"'
		. ' width="' . $sources['assets']['width'] . '"'
		. ' height="' . $sources['assets']['height'] . '"'
		. ' class="h-auto"'
		. '>';
	$picture[] = '</picture>';
	$picture = implode('', $picture);
	$figureClasses = implode(' ', $figureClasses);
?>
<div class="<?php echo $classes; ?>">
	<figure class="<?php echo $figureClasses; ?>">
		<?php echo $picture; ?>
		<?php if ($caption)
		{ ?>
		<figcaption><?php echo $caption; ?></figcaption>
		<?php
		} ?>
	</figure>
</div>
<?php
} //not empty image_intro ?>
