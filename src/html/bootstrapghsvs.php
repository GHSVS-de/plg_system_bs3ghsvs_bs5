<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;
use Joomla\Utilities\ArrayHelper;

abstract class JHtmlBootstrapghsvs
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
	public static function framework()
	{
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		if (($wa = PlgSystemBS3Ghsvs::getWa()))
		{
			// Tipp: To be defined in Template if other files wanted than core.
			$wa->useScript('bootstrap.es5');
		}
		else
		{
			$attribs = [];
			$min = JDEBUG ? '' : '.min';
			$suf = $min ? 'Min' : '';
			$version = JDEBUG ? time() : 'auto';
			HTMLHelper::_('jquery.framework');

				// From Template's plgSystemBs3Ghsvs.json.
			$options = PlgSystemBS3Ghsvs::$options['bootstrapJs'];
			$Load = $options['Load'];

			if ($Load === 'media')
			{
				// B/C
				if (!isset($options['otherFileName']))
				{
					$options['otherFileName'] = '';
				}

				$file = $options['otherFileName'] ? : 'bootstrap';
				$file = $options['media'] . '/' . $file . $min . '.js';
			}
			else
			{
				self::$loaded[__METHOD__] = 1;

				return;
			}

			HTMLHelper::_(
				'script',
				$file,
				['version' => $version, 'relative' => true],
				$attribs
			);

			if (PlgSystemBS3Ghsvs::$log)
			{
				$add = __METHOD__ . ': File ' . $file . '. Loaded?';
				Log::add($add, Log::INFO, 'bs3ghsvs');
			}
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

	/*
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">
	*/
	public static function loadCss(
		$includeMainCss = true,
		$direction = 'ltr',
		$attribs = []
	) {
		if (!empty(self::$loaded[__METHOD__]))
		{
			return;
		}

		$attribs = [];
		$min = JDEBUG ? '' : '.min';
		$suf = $min ? 'Min' : '';
		$version = JDEBUG ? time() : 'auto';

		$options = PlgSystemBS3Ghsvs::$options['bootstrapCss'];
		$Load = $options['Load'];

		if ($Load === 'cdn')
		{
			$file = $options['cdn' . $suf];
			$attribs['crossorigin'] = 'anonymous';
			$attribs['integrity'] = $options['cdnIntegrity' . $suf];
			$version = '';
		}
		elseif ($Load === 'media')
		{
			// B/C
			if (!isset($options['otherFileName']))
			{
				$options['otherFileName'] = '';
			}

			$file = $options['otherFileName'] ? : 'bootstrap';
			$file = ltrim($options['media'] . '/' . $file . $min . '.css', '/');
		}
		else
		{
			self::$loaded[__METHOD__] = 1;

			return;
		}

		HTMLHelper::_(
			'stylesheet',
			$file,
			['version' => $version, 'relative' => true],
			$attribs
		);

		if (PlgSystemBS3Ghsvs::$log)
		{
			$add = __METHOD__ . ': File ' . $file . '. Loaded?';
			Log::add($add, Log::INFO, 'bs3ghsvs');
		}

		static::$loaded[__METHOD__] = 1;

		return;
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
	public static function carousel($selector = 'carousel', $params = [])
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
	public static function startAccordion($selector = 'myAccordian', $params = [])
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
	) {
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
