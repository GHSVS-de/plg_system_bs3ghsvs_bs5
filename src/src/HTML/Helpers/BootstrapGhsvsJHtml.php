<?php
namespace GHSVS\Plugin\System\Bs3Ghsvs\HTML\Helpers;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;
use Joomla\Utilities\ArrayHelper;

// @since 2023-11
use GHSVS\Plugin\System\Bs3Ghsvs\Helper\Bs3GhsvsHelper;
use Joomla\CMS\HTML\Helpers\Bootstrap;

class BootstrapGhsvsJHtml extends Bootstrap
{
	protected static $loaded = [];

	// media-Ordner:
	protected static $basepath = 'plg_system_bs3ghsvs';

	/**
   * Load BOOTSTRAP Framework via JHtml::_('bootstrap.framework').
	 * See https://www.bootstrapcdn.com/
	 * See https://www.bootstrapcdn.com/legacy/bootstrap/
	 * See https://github.com/twbs/bootstrap/blob/v3.4.1/bower.json
	 * @param string | integer $bsversion '' => folder bootstrap/ | '4' => (folder bootstrap4/)
   */
	public static function framework($debug = null): void
	{#
		$isJ5 = version_compare(JVERSION, '5', 'ge');

		if (!$isJ5 && isset(static::$loaded[__METHOD__]))
		{
			# Für Joomla 5 muss das auch öfter durchlaufen werden können.
			return;
		}

		$wa = Bs3GhsvsHelper::getWa();
		$templateName = Bs3GhsvsHelper::getTemplateName();

		// Krücke für Joomla 5
		$wamName = 'bootstrap.es5';
		$type = 'script';

		/*
		Ein altes Template könnte 'bootstrap.es5' mit eigener Uri enthalten.
		Alles Ok. Nimms!
		Ggf. hat aber das Rückwärts-Plugin ein leeres 'bootstrap.es5' (Joomla 5)
		untergejubelt. Dann versuche zu ersetzen, falls 'template.bs4ghsvs.bootstrap-js'
		existiert.
		Und dann gibt es noch Fall3, dass in Joomla 5 gar kein 'bootstrap.es5' definiert
		ist.
		*/
		if ($wa->assetExists($type, $wamName))
		{
			$war = $wa->getRegistry();
			$asset = $war->get($type, $wamName);

			if (empty($asset->getUri()))
			{
				// Nötig? Nachdem ich untiges geädert habe? A: Ja.
				$war->remove($type, $wamName);
				$wamName = 'template.' . $templateName .  '.bootstrap.es5';
			}
		}

		if ($wa->assetExists($type, $wamName))
		{
			$wa->useAsset($type, $wamName);
		}
		else
		{
			array_map(
				function ($script) use ($wa) {
					$wa->useScript('' . $script);
				},
				[
					'core',
					'bootstrap.alert',
					'bootstrap.button',
					'bootstrap.carousel',
					'bootstrap.collapse',
					'bootstrap.dropdown',
					'bootstrap.modal',
					'bootstrap.offcanvas',
					'bootstrap.popover',
					'bootstrap.scrollspy',
					'bootstrap.tab',
					'bootstrap.toast'
				]
			);
		}
		static::$loaded[__METHOD__] = 1;

		return;
	}

	/**
	 * Loads CSS files needed by Bootstrap
	 *
	 * @param   boolean  $includeMainCss  If true, main bootstrap.css files are loaded
	 * @param   string   $direction       rtl or ltr direction. If empty, ltr is assumed
	 * @param   array    $attribs         Optional array of attributes to be passed to JHtml::_('stylesheet')
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
public static function loadCss($includeMainCss = true, $direction = 'ltr', $attribs = []): void
{
		Bs3GhsvsHelper::getWa()->useStyle('bootstrap.css');
	}

	/**
	 * Add javascript support for Bootstrap carousels
	 *
	 * @param   string  $selector  Common class for the carousels.
	 * @param   array   $params    An array of options for the carousel.
	 *                             Options for the carousel can be:
	 *                             - interval  number  The amount of time to delay between automatically cycling an item.
	 *                                                 If false, carousel will not automatically cycle.
	 *                             - pause     string  Pauses the cycling of the carousel on mouseenter and resumes the cycling
	 *                                                 of the carousel on mouseleave.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function carousel($selector = 'carousel', $params = []): void
	{
		$sig = md5(serialize([$selector, sort($params)]));

		if (!isset(static::$loaded[__METHOD__][$sig]))
		{
			HTMLHelper::_('bootstrap.framework');

			$opt = [];
			$opt['interval'] = isset($params['interval']) ? (int) $params['interval'] : 5000;
			$opt['pause']    = isset($params['pause']) ? $params['pause'] : 'hover';
			$opt['wrap']     = isset($params['wrap']) ? (bool) $params['wrap'] : true;
			$opt['keyboard'] = isset($params['keyboard']) ? (bool) $params['keyboard'] : true;
			$opt             = HTMLHelper::getJSObject($opt);

			Factory::getDocument()->addScriptDeclaration(
				'jQuery(function($){$(' . json_encode('.' . $selector) . ').carousel(' . $opt . ');});'
			);

			// Set static array
			static::$loaded[__METHOD__][$sig] = true;
		}

		return;
	}

	/**
	 * Now Collapse
	*/
	public static function startAccordion($selector = 'myAccordian', $options = []): string
	{
		if (!isset(static::$loaded[__METHOD__][$selector]))
		{
			HTMLHelper::_('bootstrap.framework');
			$divAttributes = [];
			$opt = [];

			// OHNE parent KANNST DU MEHRERE GLEICHZEITIG ÖFFNEN.

			/* Kein Typecast-Vergleich, weil ich noch nicht alle aufrufenden
			Codestellen geprüft habe. */
			if (isset($params['parent']) && $params['parent'])
			{
				$opt['parent'] = '#' . $selector;
			}

			/*
				Toggles the collapsible element on invocation
			*/
			/* $opt['toggle'] = isset($params['toggle'])
				? (boolean) $params['toggle']
				: ($opt['parent'] === false || isset($params['active']) ? false : true); */

			/*
				This event fires immediately when the show instance method is called.
			*/
			//$onShow = isset($params['onShow']) ? (string) $params['onShow'] : null;

			/*
				This event is fired when a 	 element has been made
				visible to the user (will wait for CSS transitions to complete).
			*/
			//$onShown = isset($params['onShown']) ? (string) $params['onShown'] : null;

			/*
				This event is fired immediately when the hide method has been called.
			*/
			//$onHide = isset($params['onHide']) ? (string) $params['onHide'] : null;

			/*
				This event is fired when a collapse element has been hidden from
				the user (will wait for CSS transitions to complete).
			*/
			//$onHidden = isset($params['onHidden']) ? (string) $params['onHidden'] : null;

			if (isset($opt['parent']))
			{
				$divAttributes['aria-multiselectable'] = 'false';
			}
			else
			{
				$divAttributes['aria-multiselectable'] = 'true';
			}

			/* Scroll-JS funktioniert in dieser simplen Form nicht mit
			multiselectable = JA. Scrollt zum falschen Slider. */
			if ($divAttributes['aria-multiselectable'] === 'false')
			{
				$js = <<<JS
;(function(){
document.addEventListener('DOMContentLoaded',function()
{
document.getElementById("$selector").addEventListener('shown.bs.collapse', function ()
{
	jQuery.fn.scrollToSliderHead("$selector");
});
});})();
JS;
				Factory::getDocument()->addScriptDeclaration($js);
			}

			//$options = HTMLHelper::getJSObject($opt);

			//$opt['active'] = isset($params['active']) ? (string) $params['active'] : '';
			/* #### Soweit ich sehe, braucht man den ganzen Scheiß überhaupt nicht, wenn man
			mit data-Attributen arbeitet. Außerdem ist der Kram eh total veraltet */
			/* 			$script = array();

						$script[] = 'jQuery(document).ready(function($){';

						$script[] = "$('#" . $selector . "').collapse(" . $options . ")";

						if ($onShow)
						{
							$script[] = ".on('show.bs.collapse', " . $onShow . ")";
						}

						if ($onShown)
						{
							$script[] = ".on('shown.bs.collapse', " . $onShown . ")";
						}

						if ($onHide)
						{
							$script[] = ".on('hide.bs.collapse', " . $onHide . ")";
						}

						if ($onHidden)
						{
							$script[] = ".on('hidden.bs.collapse', " . $onHidden . ")";
						}

						$script[] = '});';

						Factory::getDocument()->addScriptDeclaration(implode('', $script)); */

			static::$loaded[__METHOD__][$selector] = $opt;

			$divAttributes['class'] = 'panel-group accordion';
			$divAttributes['id'] = $selector;

			return PHP_EOL . '<!--startAccordion-->' . PHP_EOL
				. '<div ' . ArrayHelper::toString($divAttributes) . '>';
		}
	}

	/**
	 * bootstrap.addSlide BS5
	 */
	public static function addSlide(
		$selector,
		$text,
		$id,
		$class = '',
		$headingTagGhsvs = '',
		$title = ''
	) : string {
		// "in" = BS3. "show" = BS4/BS5.
		//$in = (static::$loaded[__CLASS__ . '::startAccordion'][$selector]['active'] == $id)
		//? ' in show' : '';
		$in = '';
		$parent = '';

		if (!empty(
			static::$loaded[__CLASS__ . '::startAccordion'][$selector]['parent'])
		) {
			// Dies Attribut gehört in den Slide, nicht in den Toggler!
			$parent = ' data-bs-parent="'
				. static::$loaded[__CLASS__ . '::startAccordion'][$selector]['parent']
				. '"';
		}

		$aClass = 'accordion-toggle btn btn-link text-left p-0';

		if (!trim($headingTagGhsvs))
		{
			$headingTagGhsvs = 'div';
		}

		if ($title = trim($title))
		{
			$title = ' <span class="pageBreakSlideTitle">- ' . $title . '</span>';
		}

		$html = [];
		$html[] = '<div class="card pageBreakGhsvsCard">';

		$html[] = '<div class="card-header" id="heading' . $id . '">';
		$html[] = '<' . $headingTagGhsvs . ' class="panel-title">';

		// The Toggler element.
		$html[] = '<button class="' . $aClass . '" data-bs-toggle="collapse"'
			. ' data-bs-target="#collapse' . $id . '" aria-expanded="false"'
			. ' aria-controls="collapse' . $id . '" role="button">';
		$html[] = '{svg{bi/arrows-expand}class="hideIfActive"}';
		$html[] = '{svg{bi/arrows-collapse}class="showIfActive"}';
		$html[] = $text . $title;
		$html[] = '</button>';

		$html[] = '</' . $headingTagGhsvs . '>';
		$html[] = '</div><!--/heading' . $id . '-->';

		$html[] = '<div id="collapse' . $id . '" class="collapse ' . $in . '"'
			. ' aria-labelledby="heading' . $id . '"' . $parent . '>';
		$html[] = '<div class="card-body">';

		return implode("\n", $html);
	}
}
