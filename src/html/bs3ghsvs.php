<?php
/*
JHTML::_('bs3ghsvs.something'...);
*/
?>
<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Registry\Registry;

abstract class JHtmlBs3ghsvs
{
	protected static $loaded = [];

	// media-Ordner:
	protected static $basepath = 'plg_system_bs3ghsvs';

	/*
	 * bs3ghsvs.layout
	 *
	 * 2016-05-29: Überarbeitung wegen jetzt einfacherer Abwicklung mit file.php aus Joomla 3.6.0.
	 * Helferklasse layoutfileghsvs.php nicht mehr nötig!
	 *
	 * Aufrufe von JLayouts der Art JHtml::_('bs3ghsvs.layout', 'ghsvs.irgendwas'
	 * Joomlas JLayoutFile fügt immer selbst hinzugefügten basePath als ersten ein.
	 * Dann werden die existierenden JLayouts im media-ordner zuerst genommen.
	 * Hier geht es um Wiederverwendbarkeit für mehrere Templates.
	 * Bei joomla.irgendwas sucht das JLayout also erst im media-Ordner, falls kein anderer basePath
	 * definiert ist.
	 *
	 * Ausnahmen sind layouts-Ordner, die in §newSortIf hinterlegt sind, bspw. ghsvs. .
	 * Dann wird $basePath ans Ende sortiert, damit Overrides in Template, Komponente etc.
	 * zuerst berücksichtigt werden. Und erst dann JLayout im media-Ordner.
	 * Vorher
				[0] => .../media/plg_system_bs3ghsvs/layouts
				[1] => .../templates/protostarbs3ghsvs/html/layouts/com_content
				[2] => .../components/com_content/layouts
				[3] => .../templates/protostarbs3ghsvs/html/layouts
				[4] => .../layouts

				Nachher
				[0] => .../templates/protostarbs3ghsvs/html/layouts/com_content
				[1] => .../components/com_content/layouts
				[2] => .../templates/protostarbs3ghsvs/html/layouts
				[3] => .../media/plg_system_bs3ghsvs/layouts
				[4] => .../layouts
	 */
	public static function layout($layoutFile, $displayData = null, $basePath = null, $options = null)
	{
		$isBs3ghsvs = false;

		$newSortIf = [
			'ghsvs.',
		];

		foreach ($newSortIf as $folder)
		{
			if (strpos($layoutFile, $folder) === 0)
			{
				$isBs3ghsvs = true;
				break;
			}
		}

		if ($isBs3ghsvs && empty($basePath))
		{
			$layout = new FileLayout($layoutFile, $basePath = null, $options);

			// Liefert an dieser Stelle die Defaultwerte seitens Joomla.
			## $renderer->getDefaultIncludePaths();
			$includePaths = $layout->getIncludePaths();

			// Mutterpfad raus.
			$includePaths = array_diff($includePaths, [JPATH_SITE . '/layouts']);

			// media-basePath und Mutterpfad danach wieder rein.
			array_push(
				$includePaths,
				JPATH_SITE . '/media/' . static::$basepath . '/layouts',
				JPATH_SITE . '/layouts'
			);

			// Default-Pfade überschreiben.
			$layout->setIncludePaths($includePaths);

			return $layout->render($displayData);
		}
		elseif (empty($basePath))
		{
			$basePath = JPATH_SITE . '/media/' . static::$basepath . '/layouts';
		}

		return LayoutHelper::render($layoutFile, $displayData, $basePath, $options);
	}

	/**
	 * bs3ghsvs.rendermodules
	 * Gruppenrendering von Modulen in einer Ghost-Position.
	 */
	public static function rendermodules($position = null, $attribs = [])
	{
		if (empty($position))
		{
			return '';
		}

		$output = [];
		$modules = ModuleHelper::getModules($position);

		foreach ($modules as $module)
		{
			$output[] = ModuleHelper::renderModule($module, $attribs);
		}

		return implode('', $output);
	}

	/*
		2022-07: Wird verwendet. J3 und J4.
	*/
	//public static function addsprungmarke($selector, $sprungmarke = '#BELOWHEADER')
	public static function addsprungmarke(array $options = [])
	{
		if (Factory::getApplication()->client->robot)
		{
			return;
		}

		if (!isset($options['selector']) || !($selector = trim($options['selector']))) {
			return;
		}

		if (!isset($options['sprungmarke']) || !($sprungmarke = trim($options['sprungmarke']))) {
			$sprungmarke = '#BELOWHEADER';
		}

		$sig = 'addsprungmarke' . md5(serialize([$selector, $sprungmarke]));

		if (isset(static::$loaded[__METHOD__][$sig]))
		{
			return;
		}

		if (!isset(static::$loaded[__METHOD__]['core']))
		{
			HTMLHelper::_('bs3ghsvs.templatejs');
			static::$loaded[__METHOD__]['core'] = 1;
		}

		$js = ';(function($){$(document).ready(function(){'
			. '$.fn.addSprungmarkeToUrl("' . $selector . '", "' . $sprungmarke . '");'
			. '})})(jQuery);';

		if (($wa = PlgSystemBS3Ghsvs::getWa())) {
			$wa->addInline('script', $js, ['name' => 'plg_system_bs3ghsvs.' . $sig]);
		} else {
			Factory::getDocument()->addScriptDeclaration($js);
		}

		static::$loaded[__METHOD__][$sig] = 1;

		return;
	}

	/**
	 * Eigener Bootstrap-Spoiler.
	 * See https://getbootstrap.com/docs/3.4/javascript/#collapse-usage
	 * $in = false => closed
	 */
	public static function spoiler($text, $options = [])
	{
		if (!trim($text))
		{
			return '';
		}

		$defaultOptions = 		[
			'buttontext' => 'GHSVS_MODULES_SPOILER_BTN_TEXT_SHOW_HIDE',
			'in' => 0,
			'spoilerclass' => '',
			'buttonclass' => 'btn btn-primary',
			'role' => '',
		];

		$options = array_merge($defaultOptions, $options);
		HTMLHelper::_('bootstrap.framework');
		$html = [];
		$id = 'spoiler' . str_replace('.', '', uniqid('', true));

		$buttonclass = trim('accordion-toggle ' . $options['buttonclass']);
		$spoilerclass = trim('spoilerghsvs ' . $options['spoilerclass']);
		$role = $options['role'] ? ' role="' . $options['role'] . '"' : '';

		$html[] = '<div class="' . $spoilerclass . '"' . $role . '>';
		$html[] = '<button class="' . $buttonclass . '" type="button" data-bs-toggle="collapse" data-bs-target="#' . $id
			. '" aria-expanded="' . ($options['in'] ? 'true' : 'false') . '" aria-controls="' . $id . '">';
		$html[] = '{svg{solid/plus-square}} ';
		$html[] = Text::_($options['buttontext']);
		$html[] = '</button>';
		$html[] = '<div class="collapse spoilerbody' . ($options['in'] ? ' in show' : '') . '" id="' . $id . '">';
		$html[] = $text;
		$html[] = '</div><!--/spoilerbody-->';
		$html[] = '</div><!--/spoilerghsvs-->';

		return implode('', $html);
	}

	/**
	 * Bei Klick auf Accordion-Slides Status in Session schreiben,
	 * In Session gespeicherte, aktive Slides öffnen.
	 */
	public static function activeToSession($selector = 'myAccordian')
	{
		$key = '#' . $selector . '.accordion';

		if (!isset(static::$loaded[__METHOD__][$key]))
		{
			$sessionData = Factory::getSession()->get(static::$basepath);
			$IDs = [];

			if (!empty($sessionData[$key]))
			{
				$IDs = explode('|', $sessionData[$key]);
			}

			$js = [];
			$js[] = '
(function($)
{
	$(document).ready(function()
	{
		if (! $("' . $key . '").length)
		{
			return;
		}';

			// Open active slides in session.
			foreach ($IDs as $id)
			{
				$js[] = '$("#' . $id . '").collapse("show");';
			}

			// AJAX-save status of open slides in session if any slide clicked.
			$js[] = '
		$("' . $key . '").on("shown.bs.collapse hidden.bs.collapse", function (event)
		{
			$actives = $("' . $key . ' .show");

			var activeIds = [];

			$actives.each(function()
			{
				activeIds.push($(this).attr("id"));
			});

			var activeIds = activeIds.join("|");

			var KEY = "' . urlencode($key) . '"; //Key in der Session
			var PLUGIN = "SessionBs3Ghsvs";
			var GROUP = "system";
			var FORMAT = "raw";
			var OPTION = "com_ajax";
			var CMD = "add";
			var DATA = activeIds;

			var systemPaths = Joomla.getOptions("system.paths");
			var Uri = (systemPaths ? systemPaths.root + "/index.php" : window.location.pathname) + "?"
				+ "option=" + OPTION + "&group=" + GROUP + "&plugin=" + PLUGIN + "&format=" + FORMAT
				+ "&cmd=" + CMD + "&key=" + KEY + "&data=" + DATA;

			Joomla.request({
				url: Uri,
				method: "POST",
				onError: function(xhr)
				{
					console.log("error: " + activeIds);
				}
			});
		});
	});
})(jQuery);';

			// Wegen Umstellung von $.ajax auf Joomla.request.
			HTMLHelper::_('behavior.core');
			Factory::getDocument()->addScriptDeclaration(implode('', $js));
		}
	}

	/**
	 * bs3ghsvs.templatejs
	 * Load js file from plugin media folder (self::$basepath).
	 * 2022-07: Wird verwendet. J3 und J4.
	 */
	public static function templatejs()
	{
		if (($wa = PlgSystemBS3Ghsvs::getWa()))
		{
			$wa->useScript('plg_system_bs3ghsvs.templatejs');
		}
		else
		{
			HTMLHelper::_('jquery.framework');

			if (!empty(static::$loaded[__METHOD__]))
			{
				return;
			}

			$attribs = [];
			$min = JDEBUG ? '' : '.min';
			$version = JDEBUG ? time() : 'auto';

			$file = static::$basepath . '/template' . $min . '.js';

			HTMLHelper::_(
				'script',
				$file,
				['version' => $version, 'relative' => true],
				$attribs
			);

			static::$loaded[__METHOD__] = 1;
		}

		return;
	}

	/*
		2022-07: Wird verwendet. J3 und J4.
	*/
	public static function toTop()
	{
		if (($wa = PlgSystemBS3Ghsvs::getWa()))
		{
			$wa->usePreset('plg_system_bs3ghsvs.toTop');
		}
		else if (empty(static::$loaded[__METHOD__]))
		{
			$attribs = ['defer' => 'defer'];
			$min = JDEBUG ? '' : '.min';
			$version = JDEBUG ? time() : 'auto';

			// JS wird benötigt für Einblenden des Knopfs.
			$file = self::$basepath . '/toTop' . $min . '.js';

			HTMLHelper::_(
				'script',
				$file,
				['version' => $version, 'relative' => true],
				$attribs
			);

			$file = self::$basepath . '/toTop' . $min . '.css';

			HTMLHelper::_(
				'stylesheet',
				$file,
				['version' => $version, 'relative' => true],
				$attribs
			);
			static::$loaded[__METHOD__] = 1;
		}

		// Auf mehrseitigen Blogansichten wechselt sonst die Seite.
		$uri = \Joomla\CMS\Uri\Uri::getInstance()->toString();

		return '<a href="' . $uri . '#TOP" id="toTop" tabindex="-1">'
			. '<span class="visually-hidden">' . Text::_('PLG_SYSTEM_BS3GHSVS_TO_TOP')
			. '</span></a>';
	}
}
