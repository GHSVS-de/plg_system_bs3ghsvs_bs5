<?php
namespace GHSVS\Plugin\System\Bs3Ghsvs\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\Database\ParameterType;
use GHSVS\Plugin\System\Bs3Ghsvs\Helper\Bs3GhsvsHelper;

class Bs3GhsvsTemplateHelper
{
	protected static $templates = null;

	protected static $templateOptionsFile = '/html/plgSystemBs3Ghsvs.json';

	protected static $loaded;

	protected static $TEMPLATEPARAMS;

	// Current template, falls es sich irgendwo ergibt, es zu füllen.
	public static $templateName;

	/**
	 * Return an array of template names (folders) where the plugin specific json configuration file exists OR @since 2023-11 that has been selected in plugin.
	 */
	public static function getActiveInTemplates() : array
	{
		if (is_array(self::$templates))
		{
			return self::$templates;
		}

		self::$templates = [];

		$path = JPATH_SITE . '/templates/';
		$templateFolders = Folder::folders($path);
		$PlgParams = Bs3GhsvsHelper::getPluginParams();

		// -1:Never|0:Wenn json|1:Immer|10:Wenn json oder ausgewählt|11:ausgewählte
		$initTemplateAlways = (int) $PlgParams->get('initTemplateAlways', 0);
		$load_in_templates = $PlgParams->get('load_in_templates', [], 'ARRAY');

		if ($initTemplateAlways === -1)
		{
			// never
			self::$templates = [];
		}
		else if ($initTemplateAlways === 1)
		{
			// immer
			self::$templates = $templateFolders;
		}
		else if ($initTemplateAlways === 0 || $initTemplateAlways === 10)
		{
			// wenn json
			foreach ($templateFolders as $i => $template)
			{
				if (is_file($path . $template . self::$templateOptionsFile))
				{
					self::$templates[] = $template;
				}
			}
		}

		if ($load_in_templates && ($initTemplateAlways === 10 || $initTemplateAlways === 11))
		{
			self::$templates = array_unique(array_merge(self::$templates, $load_in_templates));
		}

		return self::$templates;
	}

	/*
	 * Read options from json file plgSystemBs3GhsvsActive.json.
	 * Expects a $templateFolder (template name) that is already checked by getActiveInTemplates().
	 * Returns json_decoded result.
Array
(
    [comment] => See https://hash.online-convert.com/sha384-generator
    [bootstrapJs] => Array
        (
            [media] => plg_system_bs3ghsvs/bootstrap/3.4.1
            [cdnMin] => https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js
            [cdnIntegrityMin] => sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd
            [cdn] => https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.js
            [cdnIntegrity] => sha384-BqgdDRqoSM34U92AkJHjlNmbVsfEZStnqGg4pVMc/AMY0XbNFRu3cO5LfJXXXETD

            [jqueryMax] => 1.9.3
        )

    [jquery] => Array
        (
            [cdnMin] => https://code.jquery.com/jquery-1.9.1.min.js
            [cdnIntegrityMin] => sha256-wS9gmOZBqsqWxgIVgA8Y9WcQOa7PgSIX+rPA0VL2rbQ=
            [cdn] => https://code.jquery.com/jquery-1.9.1.js
            [cdnIntegrity] => sha256-e9gNBsAcA0DBuRWbm0oZfbiCyhjLrI6bmqAl5o+ZjUA=

        )

)
	*/
	public static function getTemplateOptionsFromJson($templateFolder)
	{
		$file = JPATH_SITE . '/templates/' . $templateFolder . self::$templateOptionsFile;

		/*
			Seit Version 2023.12.12 ist eine AUswahl initTemplateAlways im Plugin,
			die auch Suche in Templates
			erlaubt, die diese Datei nicht enthalten. Sie ist sowieso veraltet seit es
			den WAM gibt. Deshalb Bugfix is_file.
		 */
		if (is_file($file))
		{
			$templateOptions = file_get_contents($file);
		}
		else
		{
			$templateOptions = '{}';
		}
		return json_decode(trim($templateOptions), true);
	}

	public static function getTemplateByStyleId($id)
	{
		if (empty(static::$loaded[__METHOD__]))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select($db->qn('template'))
				->from($db->qn('#__template_styles'))
				->where($db->qn('id') . ' = ' . (int) $id)
			;
			$db->setQuery($query);

			try
			{
				static::$loaded[__METHOD__] = $db->loadResult('field');
			}
			catch (RuntimeException $e)
			{
				return false;
			}
		}

		return static::$loaded[__METHOD__];
	}

	public static function getIsFrontpage()
	{
		if (!isset(static::$loaded[__METHOD__]))
		{
			$menu = Factory::getApplication()->getMenu();

			if ($menu->getActive() === $menu->getDefault(Factory::getLanguage()->getTag()))
			{
				static::$loaded[__METHOD__] = true;
			}
			else
			{
				static::$loaded[__METHOD__] = false;
			}
		}

		return static::$loaded[__METHOD__];
	}

	/**
	 * Collect additional template params and more (too much?).
	 * Don't call before onAfterRoute!
	 * E.g. fired in plugin's onAfterDispatch.
	 */
	public static function initTemplate()
	{
		if (!isset(static::$loaded[__METHOD__]))
		{
			$app = Factory::getApplication();
			$view = $app->input->getCmd('view', '');
			$option = $app->input->getCmd('option', '');
			$layout = $app->input->getCmd('layout', '');
			$task = $app->input->getCmd('task', '');
			$itemid = $app->input->getInt('Itemid', 0);

			$template = $app->getTemplate(true);
			self::$templateName = $template->template;

			$tplPath = 'templates/' . $template->template;
			$BodyClasses = [];

			/*
				Special für hypnosteam u.a. ältere.
			*/
			// Under some weird circumstances, e.g. virtuemart::cart it may happen that id is not set:
			// This is a very harsh and stupid "Krücke"!
			if (empty($template->id))
			{
				$template->id = self::getStyle(['client_id' => 0, 'home' => 1])->id;
				$template->home = 1;
			}

			$template->params->set('isFrontpage', static::getIsFrontpage());

			if (!$template->params->get('sitetitle'))
			{
				$template->params->set('sitetitle', trim($app->get('sitename', '')));
			}

			if (!$template->params->get('sitetitle'))
			{
				$template->params->set('sitetitleHide', -1);
			}

			if ($menuParams = static::getActiveMenuParams())
			{
				$BodyClasses = static::splitPageClass($menuParams);

				// 2015-12-30: Artikeleinzelansicht innerhalb eines Catgory-Menüs
				if ($view === 'article' && $menuParams->get('query')->get('view') === 'category')
				{
					$BodyClasses[] = 'categoryView-article';
				}

				// 2015-12-31
				// "Seitentitel im Browser", der im Menüeintrag eingetragen ist
				$template->params->set('page_titleMenu', trim($menuParams->get('page_title', '')));
			}

			// Special für hypnosteam u.a. ältere.
			$template->params->set('menuParams', is_bool($menuParams) ? new Registry : $menuParams);

			$BodyClasses[] = $option ? 'option-' . $option : 'no-option';
			$BodyClasses[] = $view ? 'view-' . $view : 'no-view';

			if ($view === 'article')
			{
				$BodyClasses[] = 'articleId-' . $app->input->getInt('id', 0);
				$BodyClasses[] = 'catId-' . $app->input->getInt('catid', 0);
			}
			elseif (in_array($view, ['category', 'categories']))
			{
				$BodyClasses[] = 'catId-' . $app->input->getInt('id', 0);
			}

			$BodyClasses[] = $layout ? 'layout-' . $layout : 'no-layout';
			$BodyClasses[] = $task ? 'task-' . ApplicationHelper::stringURLSafe($task) : 'no-task';
			$BodyClasses[] = $itemid ? 'itemid-' . $itemid : 'no-itemid';

			// In meinem Templatestil einstellbar
			$BodyClasses[] = $template->params->get('templatestyleclass', '');

			$BodyClasses[] = static::getIsFrontpage() ? 'isFrontpage' : 'isNotFrontpage';
			$template->params->set('isRobot', $app->client->robot);

			$BodyClasses[] = $app->client->robot ? 'isRobot' : 'isNotRobot';

			$template->params->set('BodyClasses', $BodyClasses);

			####START - LOGO, SEITENTITEL, SITEDESCRIPTION, SITENAME
			#Logo-Bild:
			$logo = $template->params->get('logo', '');
			$path = $tplPath . '/images/logos/';

			if ((int) $logo === -1)
			{
				$logo = trim($template->params->get('logoalternativ', ''));
				$path = '';
			}

			if ($logo !== '' && $logo !== 'JNONE')
			{
				$logo = $path . $logo;

				$logoAlt = Text::_($template->params->get('logoAltText', ''));

				$template->params->set('companylogo', $logo);
				$logoAlt = htmlentities($logoAlt, ENT_QUOTES, 'UTF-8');
				$template->params->set('logoAltTranslated', $logoAlt);

				$logo = '<img class="logo" id="SITELOGO" src="' . $logo . '" alt="' . $logoAlt . '"/>';
				$template->params->set('logoimg', $logo);
			}

			$template->params->set('initTemplate', true);

			// Special für hypnosteam u.a. ältere.
			self::$TEMPLATEPARAMS = $template->params;

			static::$loaded[__METHOD__] = 1;
		}
	}

	/**
	 * Aus übergebenen $params pageclass_sfx holen.
	 * An Leerzeichen splitten in Einzelklassen.
	 * Ggf. Postfix an alle Klassen.
	 * Als Array zurückgeben.
	 */
	protected static function splitPageClass($params)
	{
		if (!isset(static::$loaded[__METHOD__]))
		{
			$classes = (string) $params->get('pageclass_sfx');

			if ($classes = trim($classes))
			{
				$classes = preg_replace('/\s\s+/', ' ', $classes);
				$classes = str_replace(' ', 'Body' . ' ', $classes) . 'Body';
			}

			static::$loaded[__METHOD__] = explode(' ', $classes);
		}

		return static::$loaded[__METHOD__];
	}

	/**
	 * Check 2016-06: Wird verwendet.
	 * Aktive Menüparameter holen
	 * 2015-12-30: ->query hinzugefügt, um ermitteln zu können, ob Artikelansicht unterhalb category-View
	 * 2018-03-03: Beachte! Hier fehlt das merging mit den globalen Einstellungen der com_menus.
	 */
	public static function getActiveMenuParams()
	{
		if (!isset(static::$loaded[__METHOD__]))
		{
			$menu = self::getActiveMenu();

			//Bugfix removed isset($menu->params). Always false.
			if ($menu)
			{
				$menuParams = $menu->getParams();
				$menuParams->set('query', new Registry($menu->query));
				static::$loaded[__METHOD__] = $menuParams;
			}
			else
			{
				static::$loaded[__METHOD__] = false;
			}
		}

		return static::$loaded[__METHOD__];
	}

	public static function getActiveMenu()
	{
		if (!isset(static::$loaded[__METHOD__]))
		{
			$menu = Factory::getApplication()->getMenu()->getActive();

			//Bugfix removed isset($menu->params). Always false.
			if ($menu)
			{
				static::$loaded[__METHOD__] = $menu;
			}
			else
			{
				static::$loaded[__METHOD__] = false;
			}
		}

		return static::$loaded[__METHOD__];
	}

	/**
	 * Get template name (without params) e.g. in onAfterRoute.
	 * From https://github.com/joomla/joomla-cms/blob/4.0.0-alpha10/libraries/src/Application/SiteApplication.php#L397
	 */
	public static function getTemplateNameEarly($app, $db)
	{
		$menu = $app->getMenu();
		$item = $menu->getActive();

		if (!$item)
		{
			$item = $menu->getItem($app->input->getInt('Itemid', null));
		}

		$id = 0;

		if (is_object($item))
		{
			// Valid item retrieved
			$id = $item->template_style_id;
		}

		$tid = $app->input->getUint('templateStyle', 0);

		if (is_numeric($tid) && (int) $tid > 0)
		{
			$id = (int) $tid;
		}

		if ($app->getLanguageFilter())
		{
			$tag = $app->getLanguage()->getTag();
		}
		else
		{
			$tag = '';
		}

		$select = ['id', 'home', 'template', 's.params'];
			$select = array_merge($select, ['s.inheritable', 's.parent']);

		// Load styles
		$query = $db->getQuery(true)
			->select($db->quoteName($select))
			->from('#__template_styles as s')
			->where('s.client_id = 0')
			->where('e.enabled = 1')
			->join('LEFT', '#__extensions as e ON e.element=s.template AND e.type=' . $db->quote('template') . ' AND e.client_id=s.client_id');

		$db->setQuery($query);
		$templates = $db->loadObjectList('id');

		foreach ($templates as &$template)
		{
			// Create home element
			if ($template->home == 1 && !isset($template_home) || $app->getLanguageFilter() && $template->home == $tag)
			{
				$template_home = clone $template;
			}
		}

		// Unset the $template reference to the last $templates[n] item cycled in the foreach above to avoid editing it later
		unset($template);

		// Add home element, after loop to avoid double execution
		if (isset($template_home))
		{
			$templates[0] = $template_home;
		}

		if (isset($templates[$id]))
		{
			$template = $templates[$id];
		}
		else
		{
			$template = $templates[0];
		}

		// Allows for overriding the active template from the request
		$template_override = $app->input->getCmd('template', '');

		// Only set template override if it is a valid template (= it exists and is enabled)
		if (!empty($template_override))
		{
			if (file_exists(JPATH_THEMES . '/' . $template_override . '/index.php'))
			{
				foreach ($templates as $tmpl)
				{
					if ($tmpl->template === $template_override)
					{
						$template = $tmpl;
						break;
					}
				}
			}
		}

		// Need to filter the default value as well
		$template->template = InputFilter::getInstance()->clean($template->template, 'cmd');

		// Child templates since Joomla 4.
		if (!empty($template->parent))
		{
			if (!is_file(JPATH_THEMES . '/' . $template->template . '/index.php'))
			{
				if (!is_file(JPATH_THEMES . '/' . $template->parent . '/index.php'))
				{
					$app->enqueueMessage(Text::_('JERROR_ALERTNOTEMPLATE')
						. ' in __METHOD__', 'error');

					// Try to find data for 'cassiopeia' template
					$original_tmpl = $template->template;

					foreach ($templates as $tmpl)
					{
						if ($tmpl->template === 'cassiopeia')
						{
							$template = $tmpl;
							break;
						}
					}

					// Check, the data were found and if template really exists
					if (!is_file(JPATH_THEMES . '/' . $template->template . '/index.php'))
					{
						throw new \InvalidArgumentException(
							Text::sprintf('JERROR_COULD_NOT_FIND_TEMPLATE', $original_tmpl));
					}
				}
			}
		}
		else
		{
		if (!file_exists(JPATH_THEMES . '/' . $template->template . '/index.php'))
		{
				$app->enqueueMessage(Text::_('JERROR_ALERTNOTEMPLATE') . ' in __METHOD__', 'error');

			// Try to find data for 'cassiopeia' template
			$original_tmpl = $template->template;

			foreach ($templates as $tmpl)
			{
				if ($tmpl->template === 'protostar')
				{
					$template = $tmpl;
					break;
				}
			}

			// Check, the data were found and if template really exists
			if (!file_exists(JPATH_THEMES . '/' . $template->template . '/index.php'))
			{
				throw new \InvalidArgumentException(Text::sprintf('JERROR_COULD_NOT_FIND_TEMPLATE', $original_tmpl . ' in __METHOD__'));
			}
		}
		}
		return $template->template;
	}

	/*
		Special für hypnosteam u.a. ältere.
	*/
	public static function getTemplateParams()
	{
		self::initTemplate();
		return self::$TEMPLATEPARAMS;
	}

	/*
		Special für hypnosteam u.a. ältere.
	*/
	protected static function getStyle(array $options)
	{
		$sig = md5(serialize($options));

		if (!isset(self::$loaded[__METHOD__][$sig])) {
				$style = new Joomla\Component\Templates\Administrator\Table\StyleTable(Factory::getDbo());
			$style->load($options);
			self::$loaded[__METHOD__][$sig] = $style;
		}

		return self::$loaded[__METHOD__][$sig];
	}

	/*
	Für die Auswahlliste von Templates (nicht Stilen).
	Auch deaktivierte Template-Erweiterungen.
	*/
	public static function getTemplateOptions($clientId = '*')
	{
			// Build the filter options.
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select($db->quoteName('element', 'value'))
					->select($db->quoteName('name', 'text'))
					->select($db->quoteName('extension_id', 'e_id'))
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('type') . ' = ' . $db->quote('template'))
					// ->where($db->quoteName('enabled') . ' = 1')
					->order($db->quoteName('client_id') . ' ASC')
					->order($db->quoteName('name') . ' ASC');

			if ($clientId != '*') {
					$clientId = (int) $clientId;
					$query->where($db->quoteName('client_id') . ' = :clientid')
							->bind(':clientid', $clientId, ParameterType::INTEGER);
			}

			$db->setQuery($query);
			$options = $db->loadObjectList();

			return $options;
	}

	public static function getTemplateName()
	{
		if (empty(self::$templateName))
		{
			self::$templateName = Factory::getApplication()->getTemplate();
		}
		return self::$templateName;
	}

}
