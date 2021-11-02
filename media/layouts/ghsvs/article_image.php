<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// NEIN NEIN NEIN! Da Prüfung auf empty() fehlschlägt! Also runter
#echo PHP_EOL . '<!--File: ' . str_replace(JPATH_SITE, '', dirname(__FILE__)) . '/'. basename(__FILE__) . '-->' . PHP_EOL;

/*
 * $attributes: Registry. All found attributes like class, alt ... of img tag.
 * $imgs: This is just for 1 image. For each image in article text this JLayout is
called.
Array[0] of arrays. Relevant collected resized images with size keys like _u, _l, _m and so on.
* $image: The origImage path.
*/
extract($displayData);

#if (empty($imgs) && empty($image))
if (empty($image))
{
	return;
}

echo PHP_EOL . '<!--File: ' . str_replace(JPATH_SITE, '', dirname(__FILE__)) . '/'. basename(__FILE__) . '-->' . PHP_EOL;

$imgAttributes = $attributes->toArray();

$imgClasses = explode(' ', $attributes->get('class', ''));
$imgClasses = array_map('trim', $imgClasses);

if (in_array('IMG_DoNotTouch', $imgClasses))
{ ?>
	<img src="<?php echo $image; ?>" <?php echo ArrayHelper::toString($imgAttributes); ?>>
<?php
	return;
}

$aAttributes = [];
$options = new Registry(isset($options) ? $displayData['options'] : []);
$venobox = '';
$figureClasses = ['autoLimited article_image item-image-in-article'];

// Siehe Beschreibung in der Datei.
require(__DIR__ . '/imgClassTranslator.php');
$figureClass = $options->get('float_article_image', 'ghsvs_img-default');

if (!empty($imgClassTranslator[$figureClass]))
{
	$figureClass = $imgClassTranslator[$figureClass];
}

$figureClasses[] = $figureClass;
$mediaQueries = [];
$classes = $options->get('classes', '') . ' article_image-limiter';
$aTitle = 'GHSVS_HIGHER_RESOLUTION_1';

// Zoom-Button.
$aClass = ['btn btn-dark btn-sm stretched-link'];


/* Das Chaos ist der Tatsache geschuldet, dass ich bisher ALT für die
Beaschreibung im JCE verwendet habe. Es sollte aber title die Beschreibung sein
und alt wirklich ALT. */

// Because editors encode already quotes.
if (($alt = $attributes->get('alt', '')) !== '')
{
	$alt = htmlspecialchars_decode($alt, ENT_QUOTES);
	$alt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
}

if (($title = $attributes->get('title', '')) !== '')
{
	$title = htmlspecialchars_decode($title, ENT_QUOTES);
	$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
}

if (($data_title = $attributes->get('data_title', '')) !== '')
{
	$data_title = htmlspecialchars_decode($data_title, ENT_QUOTES);
	$data_title = htmlspecialchars($data_title, ENT_QUOTES, 'UTF-8');
}

/* Krücke für obigen Kommentar für meine bisher falsche Arbeitsweise, die aber
zukünftig auch richtige korrekt machen sollte. */
if ($data_title !== '')
{
	$caption = $data_title;
}
elseif ($title !== '')
{
	$caption = $title;
}
elseif ($alt !== '')
{
	$caption = '';
}
else
{
	$caption = '';
}

if ($data_title === '' && $caption !== '')
{
	$data_title = $caption;
}

$picture = ['<picture>'];

$imgAttributes['loading'] = 'lazy';
$imgAttributes['class'] = isset($imgAttributes['class']) ?
	$imgAttributes['class'] . ' h-auto' : 'h-auto';

if (PluginHelper::isEnabled('system', 'venoboxghsvs'))
{
	if (!in_array('EXCLUDEVENOBOX', $imgClasses)
		&& !in_array('excludevenobox', $imgClasses)
	){
		HTMLHelper::_('plgvenoboxghsvs.venobox', '.venobox');
		$venobox = 'venobox';
		$aTitle = 'GHSVS_HIGHER_RESOLUTION_0';
		/* Beachte, dass Seite dann scrollt, wenn man zu anderem Bild blättert. */

		// Burdorf-Zeugs:
		$aAttributes['data-gall'] = (!empty($imgAttributes['data-gall'])
			? $imgAttributes['data-gall'] : 'myGallery');
		$aAttributes['data-title'] = $data_title;
		$aClass[] = $venobox;
	}
}

$aTitle = htmlspecialchars(Text::_($aTitle), ENT_QUOTES, 'UTF-8');

if ($title === '')
{
	$title = $aTitle;
}

if (!empty($imgs[0]) && is_array($imgs[0]))
{
	/* Derzeit folgende Größen im plg_system_bs3ghsvs

	*/
	$mediaQueries = array(
		// figure hat dann max 332px
		'(max-width: 410px)' => '_s',

		// figure hat dann max 402px.
		'(max-width: 480px)' => '_m',

		// Hier hört xs auf.
		'(max-width: 575.98px)' => '_l',

		// Hier fängt md an.
		'(min-width: 768px)' => '_x',

		// Largest <source> without mediaQuery. Also for fallback <img> src, width and height calculation.
		// Value only if you want to force one. Otherwise _x or fallback _u is used.
		'srcSetKey' => '',
	);
}
else
{
	$imgs  = array();
}

// Use $imgs not $imgs[0] because of ['order'] index.
// And because other $imgs collections can contain more than just 1 image.
$sources = Bs3ghsvsItem::getSources($imgs, $mediaQueries, $image);
$sources = $sources[0];

unset($imgAttributes['style']);

$imgAttributes['width'] = $sources['assets']['width'];
$imgAttributes['height'] = $sources['assets']['height'];
$imgAttributes['src'] = $sources['assets']['img'];
$picture[] = $sources['sources'];
$picture[] = '<img ' . ArrayHelper::toString($imgAttributes) . '>';
$picture[] = '</picture>';
$picture = implode('', $picture);

$figureClasses = implode(' ', $figureClasses);

$aAttributes['href'] = $image;
$aAttributes['title'] = $title;
$aAttributes['class'] = implode(' ', $aClass);
?>
<div class="<?php echo $classes; ?>">
	<figure class="<?php echo $figureClasses; ?>">
		<div class="position-relative">
			<?php echo $picture; ?>
			<div class="iconGhsvs text-end">
				<a <?php echo ArrayHelper::toString($aAttributes); ?>>
					<span class="visually-hidden"><?php echo Text::_($aTitle); ?></span>
					{svg{bi/zoom-in}}
				</a>
			</div>
		</div>
		<?php if ($caption)
		{ ?>
		<figcaption><?php echo $caption; ?></figcaption>
		<?php
		} ?>
	</figure>
</div>
