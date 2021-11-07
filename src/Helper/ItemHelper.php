<?php
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Filesystem\Path;;
use Joomla\CMS\Utility\Utility;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Plugin\PluginHelper;

class Bs3ghsvsItem
{
	protected static $loaded = array();
	protected static $imageResizer = null;

	/**
	 * Fügt Kategorie-Tags zu übergebenem $item hinzu????
	 * Setzt voraus, dass $item->tags->itemTags schon vorhanden
	 * Liefert $item->tagsCatGhsvs
	 * Liefert $item->tagsCatGhsvs->texts und ->titles
	 * Für direkt Kategorie als $item: $catKey = 'id'
	 */
	public static function setCatTagsToItem(
		&$item,
		$typeAlias = 'com_content.category',
		$catKey = 'catid'
	){
		if (!empty($item->tagsCatGhsvs) || empty($item->$catKey))
		{
			return;
		}

		/* "empty" Joomla\CMS\Helper\TagsHelper Object */
		$item->tagsCatGhsvs = new TagsHelper;

		// ehemals $item->concatedCatTagsGhsvs. implode(...) it yourself whereever you need it.
		$item->tagsCatGhsvs->texts = array();
		$item->tagsCatGhsvs->titles = array();

		/* Adds array [itemTags] of stdClass Objects */
		$item->tagsCatGhsvs->getItemTags($typeAlias, $item->$catKey);

		if (!$item->tagsCatGhsvs->itemTags)
		{
			return;
		}

		/* Adds text property to [itemTags]-Objects. Why the hell does this need an extra step? */
		// Warum nicht einfach title nehmen? Weil es auch verschachtelte Tags geben kann. text bildet das ab: "obertag/untertag".
		$item->tagsCatGhsvs->convertPathsToNames($item->tagsCatGhsvs->itemTags);

		foreach ($item->tagsCatGhsvs->itemTags AS $tag)
		{
			$item->tagsCatGhsvs->texts[] = $tag->text;
			$item->tagsCatGhsvs->titles[] = $tag->title;
		}

		sort($item->tagsCatGhsvs->texts);
		sort($item->tagsCatGhsvs->titles);
		return true;
	}

	/**
	 * Populate item->Imagesghsvs (catgory or article).
	 * Thus also category images can be displayed via common JLayouts.
	 * On the other hand articles: If image resize is not active then we
	 * get a minimum set of entries in Imagesghsvs.
	 */
	public static function getItemImagesghsvs(&$item, $context = null)
	{
		$sig = md5(serialize(array($item, $context)));

		if (empty(self::$loaded[__METHOD__][$sig]))
		{
			if (
				!isset($item->Imagesghsvs)
				|| !($item->Imagesghsvs instanceof Registry)
			){
				$item->Imagesghsvs = null;

				$fallbackImage = '';
				$replaceIntroFull = 0;

				if (PluginHelper::isEnabled('system', 'bs3ghsvs'))
				{
					$plgParams = PlgSystemBS3Ghsvs::getPluginParams();
					$fallbackImage = $plgParams->get('Fallbackimage', '');
					$replaceIntroFull = (int) $plgParams->get('replaceIntroFull', 0);
				}

				// Something somewhere adds stupidly $item->images = '{}' to e.g. category.
				if ($context !== 'category' && !empty($item->images)
					&& $item->images !== '{}')
				{
					$item->Imagesghsvs = new Registry($item->images);

					// JA: 1. Nur Komplettes > Intro: -1. Nur Intro > Komlettes: -2.
					if ($replaceIntroFull !== 0)
					{
						$emptyIntro = empty($item->Imagesghsvs->get('image_intro'));
						$emptyFull = empty($item->Imagesghsvs->get('image_fulltext'));

						if (
							($replaceIntroFull === 1 || $replaceIntroFull === -2)
							&& $emptyIntro === false && $emptyFull === true
						){
							$item->Imagesghsvs->set('image_fulltext',
								$item->Imagesghsvs->get('image_intro'));
							$item->Imagesghsvs->set('image_fulltext_caption',
								$item->Imagesghsvs->get('image_intro_caption'));
							$item->Imagesghsvs->set('image_fulltext_alt',
								$item->Imagesghsvs->get('image_intro_alt'));
						}

						if (
							($replaceIntroFull === 1 || $replaceIntroFull === -1)
							&& $emptyIntro === true && $emptyFull === false
						){
							$item->Imagesghsvs->set('image_intro',
								$item->Imagesghsvs->get('image_fulltext'));
							$item->Imagesghsvs->set('image_intro_caption',
								$item->Imagesghsvs->get('image_fulltext_caption'));
							$item->Imagesghsvs->set('image_intro_alt',
								$item->Imagesghsvs->get('image_fulltext_alt'));
						}
					}

					if ($fallbackImage !== '')
					{
						foreach (['image_intro', 'image_fulltext'] as $type)
						{
							if (empty($item->Imagesghsvs->get($type)))
							{
								$item->Imagesghsvs->set($type, $fallbackImage);
							}
						}
					}

					// Since Joomla 4 necessary
					$item->Imagesghsvs->set('image_intro',
						self::extractImagePath($item->Imagesghsvs->get('image_intro')));
					$item->Imagesghsvs->set('image_fulltext',
						self::extractImagePath($item->Imagesghsvs->get('image_fulltext')));

					if (!$item->Imagesghsvs->get('image_intro_popupghsvs', ''))
					{
						$item->Imagesghsvs->set('image_intro_popupghsvs',
							$item->Imagesghsvs->get('image_intro'));
					}

					if (!$item->Imagesghsvs->get('image_fulltext_popupghsvs', ''))
					{
						$item->Imagesghsvs->set('image_fulltext_popupghsvs',
							$item->Imagesghsvs->get('image_fulltext')
						);
					}

					if (!$item->Imagesghsvs->get('float_fulltext', ''))
					{
						$item->Imagesghsvs->set('float_fulltext', $item->params->get('float_fulltext'));
					}

					if (!$item->Imagesghsvs->get('float_intro', ''))
					{
						$item->Imagesghsvs->set('float_intro',
							$item->params->get('float_intro'));
					}

					// 2016-07: Sonst sind Unterarrays alle stdObjects.
					$item->Imagesghsvs = new Registry($item->Imagesghsvs->toArray());
				}
				elseif ($context === 'category' && !empty($item->params))
				{
					$item->Imagesghsvs = new Registry;

					if ($image = $item->params->get('image', $fallbackImage))
					{
						$alt = $item->params->get('image_alt');
						$float = $item->params->get('float_intro_categories', 'none');
						$item->Imagesghsvs->set('image_intro', $image);
						$item->Imagesghsvs->set('float_intro', $float);
						$item->Imagesghsvs->set('image_intro_alt', $alt);
						$item->Imagesghsvs->set('image_intro_caption', '');
						$item->Imagesghsvs->set('image_fulltext', $image);
						$item->Imagesghsvs->set('float_fulltext', $float);
						$item->Imagesghsvs->set('image_fulltext_alt', $alt);
						$item->Imagesghsvs->set('image_fulltext_caption', '');
						$item->Imagesghsvs->set('image_intro_popupghsvs', $image);
						$item->Imagesghsvs->set('image_fulltext_popupghsvs', $image);
					}
				}

				if (!($item->Imagesghsvs instanceof Registry))
				{
					$item->Imagesghsvs = new Registry;
				}
			}
			self::$loaded[__METHOD__][$sig] = 1;
		}
		return $item->Imagesghsvs;
	}

	/** Since Joomla 4 new stupid path format like
	 * images/blue.jpg#joomlaImage://local-images/blue.jpg?width=80&height=80
	 */
	public static function extractImagePath($image)
	{
		if (empty($image) || version_compare(JVERSION, '4', 'lt')
			|| strpos($image, '#') === false)
		{
			return $image;
		}

		return explode('#', $image)[0];
	}

	/**
	 * $what bspw. 'image_intro' für <fields name="image_intro"> in bs3ghsvs.xml.
	 */
	public static function getImageResizeAttribs(string $what)
	{
		$sizePostfixes = array();
		$what = PlgSystemBS3Ghsvs::getPluginParams()->get($what);

		if (is_object($what))
		{
			foreach ($what as $key => $value)
			{
				if (strpos($key, 'active_') === 0 && $value === 1)
				{
					$k = str_replace('active_', 'attribs_', $key);
					$postfix = substr($key, 6);
					$sizePostfixes[$postfix] = self::parseImageResizeOptions($what->$k);
				}
			}
		}
		return $sizePostfixes;
	}

	public static function parseImageResizeOptions(string $str)
	{
		$opts = array();
		$str = str_replace(array(' ', '"', "'"), '', $str);

		if (!$str || strpos($str, '=') === false)
		{
			return $opts;
		}

		$pairs = explode(',', $str);

		foreach ($pairs as $pair)
		{
			$parts = explode('=', $pair);

			if (count($parts) == 2 && !empty($parts[0]))
			{
				if (strtoupper($parts[1]) == 'TRUE'){
					$parts[1] = true;
				}
				elseif (strtoupper($parts[1]) == 'FAlSE')
				{
					$parts[1] = false;
				}
				$opts[$parts[0]] = $parts[1];
			}
		}
		return $opts;
	}

	/**
	 * Create collection of all relevant resized images.
	 *
	 * $what 'image_intro', 'image_fulltext', 'image_articletext' (see settings fields in bs3ghsvs.xml)
	 * and 'image_module' (see PlgSystemBS3Ghsvs::moduleImagesResizing()).
	 * $useSizePostfixes Force usage of this sizePostfixes. E.g. modules should use 'image_articletext'.
	 */
	public static function getImageResizeImages(
		string $what,
		$IMAGES,
		string $useSizePostfixes = ''): array
	{
		$collect_images = array();
		$key = 0;

		$IMAGES = (array) $IMAGES;

		// Weiterer Kennzeichner in Dateinamen. i für Intro-Bilder.
		// e.g. 'image_intro' => '_i'
		$groupPrefix = substr($what, 5, 2);

		// Get plugin settings for resizing.
		if ($useSizePostfixes === '')
		{
			$sizePostfixes = self::getImageResizeAttribs($what);
		}
		else
		{
			$sizePostfixes = self::getImageResizeAttribs($useSizePostfixes);
		}

		if (!$sizePostfixes)
		{
			return $collect_images;
		}

		self::loadImageResizer();

		foreach ($IMAGES as $key => $IMAGE)
		{
			// _u wie unbearbeitet.
			// Seit mehrere Formate erzeugt werden könnten, nun doch durche den Prozessor schicken.
			list(
				$sizePostfixes['_u']['w'],
				$sizePostfixes['_u']['h']
			) = self::getImageSize($IMAGE, true);

			$sizePostfixes['_u']['maxonly'] = 1;
			$sizePostfixes['_u']['quality'] = null;
			$sizePostfixes['_u']['size'] = '_u';

			$IMAGEorig = $IMAGE;

			/*
				Some older images can contain german umlauts, spaces and things.
				The editor urlencodes them sometimes.
				Therefore we have to kill encoding for the resizer.
				All Images will be urlencoded later in cleanIMAGEPath() if necessary.
			*/
			if (strpos($IMAGEorig, '%') !== false)
			{
				$IMAGEorig = Path::clean($IMAGEorig, '/');
				$IMAGEorig = implode('/', array_map('rawurldecode',
					explode('/', $IMAGEorig)));
			}


			foreach ($sizePostfixes as $sizePostfix => $opts)
			{
				if ($sizePostfix === '_og')
				{
					$scaleMethod = 'SCALE_OUTSIDE';
				}
				else
				{
					$scaleMethod = 'SCALE_INSIDE';
				}

				// Verkleinertes Bild erstellen und u.a. relative Pfade abholen.
				// Enthält aber auch weitere Infos wie width, 2. Bild  etc.
				$IMAGE = self::$imageResizer->resize($IMAGEorig,
					$opts,
					$scaleMethod,
					$groupPrefix . $sizePostfix
				);

				$collect_images[$key][$sizePostfix] = self::cleanIMAGEPath($IMAGE);
				$collect_images[$key][$sizePostfix]['size'] = $sizePostfix;
			} // end foreach ($sizePostfixes
		} // end foreach ($IMAGES as $key => $IMAGE)

		// ToDo. 'order' as dynamic plugin parameter if more than 1 image to be created.
		if ($collect_images)
		{
			// Ordering of 'img-1', 'img-2' during output
			switch (PlgSystemBS3Ghsvs::getPluginParams()->get('webpSupport'))
			{
				case 1:
					$collect_images['order'] = '2,1';
				break;
				case 0:
				case 2:
					$collect_images['order'] = '1';
			}
		}

		return $collect_images;
	}

	/**
	 * Method getAllImgSrc extracts values of SRC attributes (and more) of all IMG tags from a string.
		* Tries to be HTML5 compliant concerning SRC value (unquoted is allowed).
		*
		* Returns an array with keys
		*  all : array of strings Whole image tag <...>.
		*  pre : array of strings Attributes before SRC. Includes surrounding spaces.
		*  quote : array of strings Quotes around SRC value ('|"|empty).
		*  src : array of strings SRC value.
		*  post : array of strings Attributes after SRC. Includes surrounding spaces AND(!) closing >.
		*  attributes : array of Registry Objects. Like alt, data-xyz.
		*   The attributes extraction relies on quoted attributes!!
		*
		* @param string $txt String that contains IMG tags.
		* @param string $filter File/image extensions as regex (default: 'png|jpg|jpeg|gif').
		* @param bool $filter File/image extensions as regex (default: 'png|jpg|jpeg|gif').
		*
		* @return array Array with above keys | empty array.
	 */
	public static function getAllImgSrc(
		// Reference. If we have to handle <figcaption>.
		&$txt,
		$filter = 'png|jpg|jpeg|gif|webp'
	){
		if (stripos($txt, '<img ') !== false)
		{
			self::replaceFigureImg($txt, $filter);

			// Quotes. HTML5 allows also unquoted src attribute.
			$imgSrcDetection = '("|\'|)';

			/*
			 there are stupid image names for which the detection fails and
			 images are not displayed. E.g. <code>image.JPG51.jpg</code>.
			 Then user can switch detection to HTML4 in plugin settings.
			*/
			if (PluginHelper::isEnabled('system', 'bs3ghsvs'))
			{
				if (PlgSystemBS3Ghsvs::getPluginParams()->get('imgSrcDetection')
					=== 'html4')
				{
					// Quotes. HTML4. Only quoted src attributes.
					$imgSrcDetection = '("|\')';
				}
			}

			$muster = array();
			$muster[] = '<img';
			$muster[] = '(\s+[^>]*)'; // key 1
			$muster[] = 'src=';
			$muster[] = $imgSrcDetection;
			$muster[] = '([^>]+?\.(' . $filter . '))';
			$muster[] = $imgSrcDetection;

			$muster[] = '([^>]*>)';

			if (preg_match_all('/' . implode('', $muster) . '/i', $txt, $matches))
			{
				$results = array();
				$results['all'] = $matches[0];
				$results['pre'] = $matches[1];
				$results['quote'] = $matches[2];
				$results['src'] = $matches[3];
				$results['post'] = $matches[6];

				foreach ($results['pre'] as $key => $pre)
				{
					$str = str_replace("'", '"', $pre . $results['post'][$key]);
					$results['attributes'][$key] = new Registry(Utility::parseAttributes($str));
				}
				return $results;
			}
		}
		return array();
	}

	/**
	 * Zerlegt <figure>-Blöcke in einzelne <img>.
	 * Entfernt alle Attribute des <img>, die später stören.
	 * Extrahiert <figcaption>
	 * Ersetzt auf Umweg direkt z.B. im $article->text (txt ist eine Referenz).
	*/
	public static function replaceFigureImg(&$txt, $filter)
	{
		if (stripos($txt, '<figure ') === false
			&& stripos($txt, '<img ') === false)
		{
			return;
		}

		# Zum debuggen: Beiträge bei Kujm, die <figure> drin haben: 2275, 2294,2638

		$muster = '/<figure [^>]*?>.*?'
		. '(<img [^>]*>)'
		.'(.*?)'
		. '<\/figure>'
		.'/is';

		if (preg_match_all($muster, $txt, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$attributes = Utility::parseAttributes($match[1]);
				unset(
					$attributes['style'],
					$attributes['width'],
					$attributes['height']
				);

				if (
					isset($attributes['title'])
					&& !trim($attributes['title'])
				){
					unset($attributes['title']);
				}

				// Should be the <figcaption> if no markup error.
				if($match[2] = self::strip_tags($match[2]))
				{
					// Muss im JLayout o.ä extrahiert werden.
					$attributes['data-caption-from-replaceFigureImg']
						= htmlspecialchars($match[2], ENT_QUOTES, 'UTF-8');
				}

				$replaceWith = '<img ' . ArrayHelper::toString($attributes)
					. ' />';
				$txt = str_replace($match[0], $replaceWith, $txt);
			}
		}
	}

	/** Beispiel: {svg{bi/x-circle-fill}class="text-danger"}
	 * $txt Search in for $muster and repalce $muster with SVG or SPAN/SVG.
	 * $options array:
	 * addSpan Surround SVG with SPAN.
	 * spanClass CSS class of surrounding SPAN.
	 * removeTag Remove tag/$muster from $txt if no SVG file found
	 * removeSpaces Removes newlines and spaces around tag respectively SVG.
	*/
	public static function replaceSvgPlaceholders(
		string $txt,
		$options = array()
	){
		$matches = [];
		$options = new Registry($options);

		if (strpos($txt, '{svg{') !== false)
		{
			if ($options->get('removeSpaces'))
			{
				$muster  = '\s*{svg{([^}]+)}[^}]*}\s*';
			}
			else
			{
				$muster  = '{svg{([^}]+)}[^}]*}';
			}

			$results = [];

			if (preg_match_all('/' . $muster . '/m', $txt, $matches, PREG_SET_ORDER ))
			{
				foreach ($matches as $key => $match)
				{
					$results['replaceWhat'][$key] = $match[0];
					$svg = JPATH_SITE . '/media/plg_system_bs3ghsvs/svgs/' . $match[1] . '.svg';

					if (file_exists($svg))
					{
						$svg = file_get_contents($svg);

						if ($options->get('addSpan'))
						{
							$class = trim($options->get('spanClass', ''));
							$passedAttributes = Utility::parseAttributes($match[0]);

							if (isset($passedAttributes['class']))
							{
								$class .= ' ' . $passedAttributes['class'];
							}

							$svg   = '<span aria-hidden="true"' . ($class ? ' class="' . $class . '"' : '') . '>'
								. $svg . '</span>';

							if (!empty($passedAttributes['href']))
							{
								$svg = '<a href="' . $passedAttributes['href'] . '">' . $svg
									. '</a>';
							}
						}
						$results['replaceWith'][$key] = $svg;
					}
					elseif ($options->get('removeTag'))
					{
						$results['replaceWith'][$key] = '';
					}
					else
					{
						unset($results['replaceWhat'][$key]);
					}
				}

				if (!empty($results['replaceWith']))
				{
					$txt = str_replace($results['replaceWhat'], $results['replaceWith'], $txt);
				}
			}
		}
		return $txt;
	}

	protected static function loadImageResizer()
	{
		if (is_null(self::$imageResizer))
		{
			JLoader::register('ImgResizeCache', __DIR__ . '/ImgResizeCache.php');
			self::$imageResizer = new ImgResizeCache(PlgSystemBS3Ghsvs::getPluginParams());
		}
	}

	public static function strip_tags($content)
	{
		$content = preg_replace("'<script[^>]*>.*?</script>'si", '', $content);
		$content = preg_replace("'<style[^>]*>.*?</style>'si", '', $content);
		$content = preg_replace('/{.+?}/', '', $content);
		$content = preg_replace("'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $content);
		$content = trim(strip_tags($content));
		$content = preg_replace('/(\n|\r|\t|\s)+/', ' ', $content);
		return $content;
	}

	public static function hasScheme(string $path)
	{
		return !empty(parse_url($path, PHP_URL_SCHEME));
	}

	public static function addUriRoot(string $path)
	{
		if ($path && !self::hasScheme($path))
		{
			$path = Uri::root() . ltrim($path, '/\\');
		}
		return $path;
	}

	/**
	 * Returns Array array('width' => $w, 'height' => $h).
	 * If getimagesize fails return array('width' => 0, 'height' => 0).
	 * Even if PHP manual says "BEFORE PHP 7.1.0, list() only worked on numerical arrays and assumes the numerical indices start at 0." PHP 7.3 doesn't care and still throws Notices like "Undefined offset: 0 in /ItemHelper.php on line 242" if you don't convert indices to numerical ones and then use list(). => $numericalIndices established that returns array(0 => $w, 0 => $h)
	 */
	public static function getImageSize(string $image,
		bool $numericalIndices = false
	) : array {
		$w = $h = 0;

		if (!self::hasScheme($image))
		{
			$image = JPATH_SITE . '/' . ltrim($image, '/\\');
		}

		/* A smmll fall back for urlencoded images (space replaced with %20
		and other shit). */
		if (!\is_file($image))
			{
				$image = urldecode($image);
			}

		if (is_file($image))
		{
			$imagesize = getimagesize($image);
			list($w, $h) = $imagesize;
		}

		if ($numericalIndices === true)
		{
			return array(0 => $w, 1 => $h);
		}
		return array('width' => $w, 'height' => $h);
	}

	/*
	 * Build and return array with sources HTML block and infos for fallback <img>.
	 * Returns something like. Example for just one intro_image:
Array
(
    [0] => Array
        (
            [sources] => <source srcset="cache/images/Plakat_DJLP-2020_Paul_quer_i_s.jpg" media="(max-width: 575px)">
<source srcset="cache/images/Plakat_DJLP-2020_Paul_quer_i_l.jpg" media="(max-width: 1199px)">
<source srcset="cache/images/Plakat_DJLP-2020_Paul_quer_i_x.jpg" media="(min-width: 1200px)">
<source srcset="cache/images/Plakat_DJLP-2020_Paul_quer_i_x.jpg">
            [assets] => Array
                (
                    [img] => cache/images/Plakat_DJLP-2020_Paul_quer_i_x.jpg
                    [width] => 700
                    [height] => 219
                )
        )
)
	*/
	public static function getSources($imgs, array $mediaQueries, string $origImage): array
	{
		$returnArray = [];

		/*
		$mediaQueries. Ungeprüft. Wie vom JLayout übergeben.
		Array
		(
    [(max-width: 575px)] => _s
    [(max-width: 767px)] => _m
    [(max-width: 1199px)] => _l
    [(min-width: 1200px)] => _x
		[srcSetKey] =>
		)
		*/

		/*
		Setze nicht existierende Größen-Keys auf '_l'.
		Leeres Array, wenn eh nix zu tun. Bspw. $imgs Schrott ist.
		*/
		$mediaQueries = self::cleanMediaQueries($mediaQueries, $imgs);

		if ($mediaQueries)
		{
			$sources  = [];
			/*
			$mediaQueries['srcSetKey'] kann z.B. im JLayout übergeben werden.
			Z.B. '_l'.
			Diese Größe wird dann für das Bild im <source> ohne mediaQuery verwendet,
			aber auch für das <img>.
			Siehe auch unten cleanMediaQueries(), wo 'srcSetKey' ggf. korrigiert wird.
			*/
			$srcSetKey = $mediaQueries['srcSetKey'];
			unset($mediaQueries['srcSetKey']);

			/* 'order' ist ein blödes Ding, wenn z.B. eine webp-Version
			UND eine jpg-Version der selben Bild-Größe in die <sources>/<img>
			einzubauen ist.
			Das 'order' entscheidet darüber, welches zuerst kommt.
			'order' wird in dieser Klasse hart-kodiert gesetzt.

			Unterscheide vom 'count', das jedes einzelne Bild zusätzlich hat. Da ist
			es ein Integer, das dem 'order' folgt. Siehe unten foreach($ordering)

			$ordering Im einfachsten und bisher häufigsten  Fall:
			Array
			(
    [0] => 1
			)
			*/
			$ordering = \explode(',', $imgs['order']);
			unset($imgs['order']);

			/* Folgendes foreach:
			$sizedImageKey ist numerischer Index aus übergebenenem $imgs.
			Bei intro_image gibts bspw. nur einen (0).
			Bei article_images können es aber mehrere $sizedImageKey sein.

			$sizedImages ist das zum $sizedImageKey gehörige Array aus $imgs.
			Array
			(
    [_x] => Array
        (
            [img-1] => cache/images/Plakat_DJLP-2020_Paul_quer_i_x.jpg
            [count] => 1
            [width] => 700
            [height] => 219
            [size] => _x
        )

    [_l] => Array
        (
            [img-1] => cache/images/Plakat_DJLP-2020_Paul_quer_i_l.jpg
            [count] => 1
            [width] => 480
            [height] => 150
            [size] => _l
        )

    [_s] => Array
        (
            [img-1] => cache/images/Plakat_DJLP-2020_Paul_quer_i_s.jpg
            [count] => 1
            [width] => 320
            [height] => 100
            [size] => _s
        )

    [_u] => Array
        (
            [img-1] => images/Plakat_DJLP-2020_Paul_quer.jpg
            [width] => 1200
            [height] => 375
            [count] => 1
            [size] => _u
        )

			)
			*/
			foreach ($imgs as $sizedImageKey => $sizedImages)
			{
				foreach ($ordering as $order)
				{
					/*
					Ein Zähler, der für jedes eingesetzte <source>-Tag hochzählt, um
					das letzte+1 zu ermitteln: Ein nachgereichtes <source> ohne Query.
					*/
					$i = 1;

					// img-1, img-2. Wobei zweiteres eher Exot ist. Siehe oben.
					$imgKey = 'img-' . $order;

					/*
					$mediaQuery z.B. '(max-width: 370px)'.
					$sizeIndex z.B. '_s'.
					*/
					foreach ($mediaQueries as $mediaQuery => $sizeIndex)
					{
						/*
						$image ist z.B. für $sizeIndex _s
						Array
						(
    [img-1] => cache/images/Plakat_DJLP-2020_Paul_quer_i_s.jpg
    [count] => 1
    [width] => 320
    [height] => 100
    [size] => _s
						)
						*/
						$image = $sizedImages[$sizeIndex];

						// Fall back to first image. Warum auch immer nötig.
						if ($image['count'] < $order)
						{
							$imgKey = 'img-1';
						}

						/*
						Übernimmt also den numerischen Key des imgs-Arrays.
						Da, wie gesagt, auch 2 Varianten (webp, jpg) für selbes Query
						existieren kann, ist $sources[$sizedImageKey] ein Array.
						*/
						$sources[$sizedImageKey][] = '<source srcset="' . $image[$imgKey]
							. '" media="' . $mediaQuery . '">';
						$i++;

						// Die Logik mit dem > bleibt mir momentan verborgen.
						// Jedenfalls ist das das Bild für den <src>-Tag ohne Query.
						if ($i > count($mediaQueries))
								{
									$srcSetImage    = $sizedImages[$srcSetKey][$imgKey];
									$srcSetKeySaved = $srcSetKey;
									$imgKeySaved    = $sizedImageKey;
							$sources[$sizedImageKey][] = '<source srcset="' . $srcSetImage . '">';
						} // if ($i > $count)
					} // foreach ($mediaQueries as $mediaQuery => $sizeIndex)
				} // foreach ($ordering as $order)

				$returnArray[$sizedImageKey]['sources'] = implode("\n", $sources[$sizedImageKey]);

				// ToDo: There should be an earlier place in code to get missing width and height
				//  instead of running self::getImageSize() again and again here and there.
				//  The imageresizer returns false in _checkImage(...) with no width/height and
				//  a 'resized' => 0 that can be used to check w/h earlier.
				if (!isset($imgs[$imgKeySaved][$srcSetKeySaved]['width']))
				{
					$size = self::getImageSize($imgs[$imgKeySaved][$srcSetKeySaved]['img-1']);
					$imgs[$imgKeySaved][$srcSetKeySaved]['width'] = $size['width'];
					$imgs[$imgKeySaved][$srcSetKeySaved]['height'] = $size['height'];
				}

				$returnArray[$sizedImageKey]['assets']  = array(
					'img'    => $imgs[$imgKeySaved][$srcSetKeySaved]['img-1'],
					'width'  => $imgs[$imgKeySaved][$srcSetKeySaved]['width'],
					'height' => $imgs[$imgKeySaved][$srcSetKeySaved]['height'],
				);
			} // foreach ($imgs as $sizedImageKey => $sizedImages)
		} // if ($mediaQueries)

		// Resizer disabled.
		if (!$returnArray && $origImage)
		{
			$returnArray[0]['sources']       = '<source srcset="' . $origImage . '">';
			$returnArray[0]['assets']['img'] = $origImage;
			$returnArray[0]['assets']        = \array_merge($returnArray[0]['assets'],
				self::getImageSize($origImage));

		}
		return $returnArray;
	}

	/**
	* Räumt $mediaQueries auf. Wenn eine Größe, z.B. '_s' gar nicht im Bilder-Array
	* 	dann setze das $mediaQuery plump auf '_l'.
	* Ermittelt weiters das 'srcSetKey'.
	*/
	protected static function cleanMediaQueries(array $mediaQueries, $imgs) : array
	{
		$sig = md5(serialize($mediaQueries) . serialize($imgs));

		if (!isset(self::$loaded['mediaQueries'][$sig]))
		{
			if (!is_array($imgs) || empty($imgs[0]['_l']) || empty($mediaQueries))
			{
				self::$loaded['mediaQueries'][$sig] = [];
			}
			else
			{
				foreach ($mediaQueries as $mediaQuery => $sizeIndex)
				{
					if ($mediaQuery !== 'srcSetKey' && !isset($imgs[0][$sizeIndex]))
					{
						$mediaQueries[$mediaQuery] = '_l';
					}
				}

				/*
				$srcSetKeys, falls kein $mediaQueries['srcSetKey'] erxplizit übergeben.
				Dass in diesem Array mehrere enthalten sind, liegt daran, dass je nach
				Resizer-Einstellungen der eine oder andere Key auch fehlen könnte in der
				$imgs-Sammlung.
				Jedenfalls gibt es immer ein Fallback auf _u (= Originalbild).
				Array
				(
						[0] => z.B. _l oder NULL.
						[1] => _x
				)
				*/

				$srcSetKeys = [
					empty($mediaQueries['srcSetKey']) ? null : $mediaQueries['srcSetKey'],
					'_x',
				];

				// Fall back.
				$mediaQueries['srcSetKey'] = '_u';

				foreach ($srcSetKeys as $srcSetKey)
				{
					if ($srcSetKey !== null && isset($imgs[0][$srcSetKey]))
					{
						$mediaQueries['srcSetKey'] = $srcSetKey;
						break;
					}
				}

				if (count($mediaQueries) < 2)
				{
					$mediaQueries = [];
				}

				self::$loaded['mediaQueries'][$sig] = $mediaQueries;
			}
		}

		return self::$loaded['mediaQueries'][$sig];
	}

	/**
	* rawurlencoded Pfade. Leider gibt es immer noch Pfade mit Leerzeichen.
	* Und das SEF-Plugin erzeugt dann komplettes Chaos für srcset. Z.B. so was.
	* srcset="/prof. /peter /fuchs /zum /abschlus_f_m.jpg".
	*/
	protected static function cleanIMAGEPath(array $IMAGE) : array
	{
		if ($IMAGE)
		{
			if (!isset($IMAGE['count']))
			{
				$IMAGE['count'] = 1;
			}

			for ($i = 1; $i <= $IMAGE['count']; $i++)
			{
				$IMAGE['img-' . $i] = self::cleanPath($IMAGE['img-' . $i]);
			}
		}

		return $IMAGE;
	}

	/**
	* rawurlencoded Pfade.
	*/
	public static function cleanPath(string $path) : string
	{
		if ($path)
		{
			$path = Path::clean($path, '/');
			$path = implode('/', array_map('rawurlencode', explode('/', $path)));
		}

		return $path;
	}
}
