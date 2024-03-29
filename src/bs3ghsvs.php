<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Spatie\SchemaOrg\Schema;

// @since 2023-11
use Joomla\CMS\Event\Application\AfterInitialiseDocumentEvent;
use GHSVS\Plugin\System\Bs3Ghsvs\Helper\Bs3GhsvsHelper;
use GHSVS\Plugin\System\Bs3Ghsvs\Helper\Bs3GhsvsItemHelper;
use GHSVS\Plugin\System\Bs3Ghsvs\Helper\Bs3GhsvsArticleHelper;
use GHSVS\Plugin\System\Bs3Ghsvs\Helper\Bs3GhsvsFormHelper;
use GHSVS\Plugin\System\Bs3Ghsvs\Helper\Bs3GhsvsTemplateHelper;
use GHSVS\Plugin\System\Bs3Ghsvs\HTML\RegisterJHtml;

class PlgSystemBS3Ghsvs extends CMSPlugin
{
	protected $app;

	protected $db;

	// Da ich language "missbrauche" für andere Komponenten, Template etc.
	protected $autoloadLanguage = true;

	/**
	 * Array von Template-Namen für die im Plugin "Run TemplateHelper::init()" gewählt wurden.
	 */
	protected $templates = [];

	// A switch for some features that shall only run in $this->templates templates.
	private $executeFe = false;

	protected static $loaded = [];

	// Path inside /media/
	protected static $basepath = 'plg_system_bs3ghsvs';

	// Aktuelles FE-Template
	protected $template = null;

	// A shortcut for $this->app->getTemplate(true)->params;
	protected $templateParams = null;

	protected $formPath = null;

	/**
	 * Optionen aus plgSystemBs3Ghsvs.json des Templates.
	 *
	 * Null | RegistryObject
	 */
	public static $options = null;

	public static $log = 0;

	// Collect og images for later output.
	protected $ogCollection = [];

	protected $sd_robotsStateOk = false;

	// Is library installed?
	protected $imgresizeghsvsinstalled = false;

	// Is library installed?
	protected $structuredataghsvsinstalled = false;

	// We need a cache. Otherwise already set <figure> tags will be removed in second run of getAllImgSrc(). E.g. when resizer is disabled.
	protected $allImgSrc = null;


	function __construct(&$subject, $config = [])
	{
		// NEIN!!!!!!!!!!!!!!!! Das darfst nicht in __construct!!!!
		#if (Factory::getDocument()->getType() !== 'html')

		parent::__construct($subject, $config);

		$this->imgresizeghsvsinstalled =
			is_file(JPATH_LIBRARIES . '/imgresizeghsvs/vendor/autoload.php');

		$this->structuredataghsvsinstalled =
			is_file(JPATH_LIBRARIES . '/structuredataghsvs/vendor/autoload.php');

		if (
			$this->params->get('resizeGlobalActive', 1) === 0
			|| $this->imgresizeghsvsinstalled === false
		) {
			$this->params->set('resizeForce', 0);
			$this->params->set('imageoptimizer_intro_full', 0);
			$this->params->set('imageoptimizer_articletext', 0);
		}

		if (self::$log || (self::$log = $this->params->get('log', 0)))
		{
			Log::addLogger(
				['text_file' => self::$basepath . '-log.php'],
				Log::ALL,
				['bs3ghsvs']
			);
		}

		// Open graph feature can be used in any template.
		// Create arrays early to avoid PHP Notices later on.
		if ($this->params->get('opengraphActive', 1) === 1 && $this->app->isClient('site'))
		{
			$this->ogCollection['com_content.article'] = [];
			$this->ogCollection['mod_custom.content'] = [];
			$this->ogCollection['default_images'] = [];
		}

		// Order this plugin as first running one in #__extensions
		$this->order();

		// Check for file plgSystemBs3Ghsvs.json in templatefolders.
		$this->templates = Bs3GhsvsTemplateHelper::getActiveInTemplates();

		$this->formPath = JPATH_PLUGINS . '/system/bs3ghsvs/myforms/';

		$this->sd_robotsStateOk = $this->params->get('sd_robots', 1) === 0
			|| ($this->params->get('sd_robots', 1) === 1 && $this->app->client->robot);

		if (!$this->templates || !$this->app->isClient('site'))
		{
			$this->executeFe = false;

			return;
		}
	}

	public function onAfterRoute()
	{
		if (!$this->app->isClient('site'))
		{
			$this->executeFe = false;

			return;
		}

		// GZIP off for Facebook and LinkedIn bots.
		if (
			$this->params->get('gzipOffFacebook', 1) === 1
			&& $this->app->get('gzip', 0)
			&& preg_match('/facebookexternalHit|LinkedInBot/i', $this->app->client->userAgent)
		) {
			$this->app->set('gzip', 0);
		}

		$this->template = Bs3GhsvsTemplateHelper::getTemplateNameEarly($this->app, $this->db);
		$this->executeFe = in_array($this->template, $this->templates);

		if ($this->executeFe === false)
		{
			return;
		}

		self::$options = Bs3GhsvsTemplateHelper::getTemplateOptionsFromJson($this->template);

		$this->executeFe = $this->register();
	}

	public function onAfterDispatch()
	{
		// Hint for user in back-end that image resizer doesn't cache.
		if (
			$this->app->isClient('administrator')
			&& ($this->params->get('resizeForce')
				+ $this->params->get('resizeForceMessage') === 2)
		) {
			$this->app->enqueueMessage(
				Text::_('PLG_SYSTEM_BS3GHSVS_FORCE_MESSAGE'),
				'info'
			);
		}

		if ($this->app->isClient('site') && ($this->executeFe === true))
		{
			Bs3GhsvsTemplateHelper::initTemplate();

			if ($this->params->get('loadBootstrapEarly', 1))
			{
				// Load BS.
				HTMLHelper::_('bootstrap.framework');
			}
		}
	}
public function onAfterInitialiseDocument(AfterInitialiseDocumentEvent $event)
{
	if ($this->params->get('loadBootstrapEarly', 1))
	{
		# NEIN! HIER IST ZU FRÜH!!
		// Load BS.
		#HTMLHelper::_('bootstrap.framework');
	}
}
public function onBeforeRender()
{
	if ($this->params->get('loadBootstrapEarly', 1))
	{
		// Load BS.
		HTMLHelper::_('bootstrap.framework');
	}
}
	public function onContentBeforeSave($context, $article, $isNew, $data = [])
	{
		// articleconnect is deprecated as far as I remember.
		// A: depends on articleconnect plugin. Following code seems to be just for clean out.
		if (
			$context === 'com_content.article'
			&& !empty($article->attribs)
			&& is_string($article->attribs)
		) {
			$attribs = json_decode($article->attribs);
			$registry = new Registry($attribs);

			if (is_object($registry->get('articleconnect')))
			{
				foreach ($attribs->articleconnect as $key => $value)
				{
					if (!trim($value))
					{
						unset($attribs->articleconnect->$key);
					}
				}
			}

			$article->attribs = json_encode($attribs);
		}
	}

	/**
	 * Save/renew bs3ghsvs extra fields in database table #__bs3ghsvs_article.
	 */
	public function onContentAfterSave($context, $article, $isNew, $data = [])
	{
		if ($context === 'com_content.article')
		{
			// Deletion protection e.g. when j2xml is importing.
			// More j2xml specific exclusions:
			// Factory::getDocument()->getType() === 'xmlrpc'
			// $this->app->input->get('option') === 'com_j2xml'
			// $this->app->input->get('task') === 'services.import'
			// $this->app->input->get('format') === 'xmlrpc'
			if (Factory::getDocument()->getType() !== 'html')
			{
				return true;
			}

			$articleId = isset($article->id) ? (int) $article->id : 0;

			if (!$articleId)
			{
				return true;
			}

			$prefix = 'article';
			$activeXml = Bs3GhsvsFormHelper::getActiveXml($prefix, $this->params, [1]);

			// Delete all old rows in db table.
			$query = $this->db->getQuery(true)
				->delete($this->db->qn('#__bs3ghsvs_article'))
				->where($this->db->qn('article_id') . ' = ' . $articleId);
			$this->db->setQuery($query);
			$this->db->execute();

			// Get the relevant values for column "key" in db table.
			foreach ($activeXml as $key => $status)
			{
				$activeXml[$key] = strtolower(substr_replace($key, '', 0, strlen($prefix)));
			}

			$prefix = 'bs3ghsvs_';
			$tuples = [];

			foreach ($activeXml as $column)
			{
				$dataKey = $prefix . $column;

				if (isset($data[$dataKey]))
				{
					// e.g. field "bs3ghsvs_extension_active".
					$activeKey = $dataKey . '_active';

					if (isset($data[$dataKey][$activeKey]) && $data[$dataKey][$activeKey] === 0)
					{
						continue;
					}

					$values = json_encode($data[$dataKey]);
					// Because filter="url" leads to (bool) false instead of empty string:
					// Shouldn't be a problem.
					##$values = str_replace(array(':false,', ':false}'), array(':"",', ':""}'), $values);

					$tuples[] = $articleId
						. ', ' . $this->db->q($column)
						. ', ' . $this->db->q($values);
				}
			}

			if ($tuples)
			{
				$query = $this->db->getQuery(true)
					->insert($this->db->quoteName('#__bs3ghsvs_article'))
					->columns($this->db->quoteName(['article_id', 'key', 'value']))
					->values($tuples);
				$this->db->setQuery($query);
				$this->db->execute();
			}
		}

		return true;
	}

	/**
	 * .
	 */
	public function onContentAfterDelete($context, $article)
	{
		if ($context === 'com_content.article')
		{
			$query = $this->db->getQuery(true)
				->delete($this->db->qn('#__bs3ghsvs_article'))
				->where($this->db->qn('article_id') . ' = ' . (int) $article->id);
			$this->db->setQuery($query);
			$this->db->execute();
		}

		return true;
	}

	/**
	 * Load form field values from db table.
	 */
	public function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if ($context === 'com_content.article')
		{
			$prefix = 'article';

			if (is_object($data))
			{
				$articleId = isset($data->id) ? (int) $data->id : 0;

				if (!$articleId)
				{
					return true;
				}

				$activeXml = Bs3GhsvsFormHelper::getActiveXml($prefix, $this->params, [1]);

				if (!$activeXml)
				{
					return true;
				}

				foreach ($activeXml as $key => $status)
				{
					// Get the key name for db request.
					$activeXml[$key] = strtolower(substr_replace($key, '', 0, strlen($prefix)));
				}

				// Load the article extra fields from the database.
				$query = $this->db->getQuery(true);
				$query->select($this->db->qn(['key', 'value']))
				->from($this->db->qn('#__bs3ghsvs_article'))
				->where($this->db->qn('article_id') . ' = ' . $articleId)
				->where($this->db->qn('key') . ' IN('
					. implode(', ', $this->db->q($activeXml)) . ')')
				;
				$this->db->setQuery($query);
				$result = $this->db->loadObjectList('key');

				if (!$result)
				{
					return true;
				}

				foreach ($result as $key => $value)
				{
					$result[$key] = json_decode($value->value, true);

					if (json_last_error() !== JSON_ERROR_NONE)
					{
						$result[$key] = $value->value;
					}
					$data->{'bs3ghsvs_' . $key} = $result[$key];
				}
			} // is_object($data)
		} // end $context === 'com_content.article'

		return true;
	}

	public function onContentPrepareForm(Form $form, $data)
	{
		$context = $form->getName();

		// Template styles.
		/* if (
			$this->app->isClient('administrator')
			&& $context === 'com_templates.style'
			&& ($template = Bs3GhsvsTemplateHelper::getTemplateByStyleId($this->app->input->get('id')))
			&& in_array($template, $this->templates)
		) {
			$form->loadFile($this->formPath . '/base.xml', $reset = false, $path = false);
			$form->loadFile($this->formPath . '/template.xml', $reset = false, $path = false);
		} */

		if (
			$this->app->isClient('administrator')
			&& $context === 'com_templates.style'
		) {
			$key = 'Template';

			foreach (Bs3GhsvsFormHelper::getActiveXml($key, $this->params) as $file => $x)
			{
				$form->loadFile($this->formPath . '/' . $file . '.xml', $reset = false, $path = false);
			}
		}

		// Module edit in backend UND in Frontend.

		// advanced module manager has changed the form name since Joomla 4.
		// com_advancedmodules

		if (
			($this->app->isClient('administrator') && $context === 'com_modules.module')
			|| ($this->app->isClient('administrator')
				&& $context === 'com_advancedmodules.module')
			// Load form:
			|| ($this->app->isClient('site') && $context === 'com_config.modules')
			// Save form (how stupid is that?):
			|| ($this->app->isClient('site') && $context === 'com_modules.module')
		) {
			$key = 'Module';

			foreach (Bs3GhsvsFormHelper::getActiveXml($key, $this->params) as $file => $x)
			{
				$form->loadFile($this->formPath . '/' . $file . '.xml', $reset = false, $path = false);
			}
		}

		// Article edit in backend.
		if (
			$this->app->isClient('administrator')
			&& $context === 'com_content.article'
		) {
			$key = 'Article';

			foreach (Bs3GhsvsFormHelper::getActiveXml($key, $this->params) as $file => $x)
			{
				$form->loadFile($this->formPath . '/' . $file . '.xml', $reset = false, $path = false);
			}
		}

		// Menuitem edit in backend.
		if (
			$this->app->isClient('administrator')
			&& $context === 'com_menus.item'
		) {
			$key = 'Menuitem';

			foreach (Bs3GhsvsFormHelper::getActiveXml($key, $this->params) as $file => $x)
			{
				$form->loadFile($this->formPath . '/' . $file . '.xml', $reset = false, $path = false);
			}
		}

		// Contact form frontend
		if (
			$this->app->isClient('site')
			&& $this->app->input->get('view', '') === 'contact'
			&& $this->app->input->get('option', '') === 'com_contact'
			&& $context === 'com_contact.contact'
		) {
			$key = 'ContactForm';

			foreach (Bs3GhsvsFormHelper::getActiveXml($key, $this->params) as $file => $x)
			{
				$form->loadFile($this->formPath . '/' . $file . '.xml', $reset = false, $path = false);

				// Remove "* Required field" / "* Benötigtes Feld"
				$form->removeField('spacer');

				$paras = new Registry($this->params->get('XmlActive' . $key));
				$setRequired = ['contact_phoneghsvs', 'contact_name'];

				foreach ($setRequired as $field)
				{
					$required = $paras->get($field . '_required', 0);

					if ($required === -1)
					{
						$form->setFieldAttribute($field, 'type', 'hidden');
						$required = 0;
					}

					$form->setFieldAttribute($field, 'required', $required ? 'true' : 'false');
				}
			}
		}

		return true;
	}

	/**
	 * Called after the list of modules that must be rendered is created.
	 *
	 * 1) Add a module marker 'iAmAModuleGhsvs' to exclude modules like mod_articles_news
	 *  from running through onContentPrepare with context com_content.article.
	 * 2) Make parameters in 'bs3ghsvsModule' directly available in module's $params.
	 */
	public function onAfterModuleList(&$modules)
	{
		if (!$this->app->isClient('site'))
		{
			return;
		}

		foreach ($modules as $module)
		{
			$registry = new Registry($module->params);
			$registry->set('iAmAModuleGhsvs', 1);

			###### Bootstrap-Size-Parameters and others from bs3ghsvsModule.xml - START
			$colClass = [];
			$freeColClasses = '';

			// Is module.xml active?
			$prefix = 'module';
			$activeXml = Bs3GhsvsFormHelper::getActiveXml(
				$prefix,
				$this->params,
				[1] // stati
			);

			if ($activeXml)
			{
				// BS-Bootstrap Classes
				$bs3ghsvsModule = $registry->get('bs3ghsvsModule');

				if (
					is_object($bs3ghsvsModule)
					&& count(get_object_vars($bs3ghsvsModule))
				) {
					if (!empty($bs3ghsvsModule->bootstrap_size_new))
					{
						foreach ($bs3ghsvsModule->bootstrap_size_new as $size)
						{
							if (!$size->active || !$size->bootstrap_class)
							{
								continue;
							}

							if ($size->bootstrap_class === 'col')
							{
								$size->bootstrap_size = '';
							}

							// e.g "col-md-6"
							$colClass[] = $size->bootstrap_class . $size->bootstrap_size;
						}
					}

					// Change module position on selected menu items?
					if (!empty($bs3ghsvsModule->modulePosition) && !empty($bs3ghsvsModule->modulePositionMenuItems))
					{
						$currentPageId = $this->app->input->get('Itemid', 0);

						if (
							in_array($currentPageId, $bs3ghsvsModule->modulePositionMenuItems)
							&& $bs3ghsvsModule->modulePosition !== $module->position
						) {
							$module->position = $bs3ghsvsModule->modulePosition;
						}
					}

					// Fine for parameters in first level.
					$bs3ghsvsModule = new Registry($bs3ghsvsModule);
					$registry->merge($bs3ghsvsModule);
				}
			}
			$registry->set('colClass', implode(' ', $colClass));
			###### Bootstrap-Size-Parameters and others from bs3ghsvsModule.xml -  - END

			$registry->set('isRobot', (int) $this->app->client->robot);

			$module->params = $registry->toString();
		}
	}

	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		$wa = self::getWa();
		$isRobot = (int) $this->app->client->robot;
		$isHtml = Factory::getDocument()->getType() === 'html';

		$this->templateParams = $this->app->getTemplate(true)->params;

		$view = $this->app->input->get('view');

		// Create a switch to exclude fucking modules with fucking wrong context.
		$iAmAnArticle =
			$this->app->isClient('site')
			&& isset($article->params)
			&& $article->params instanceof Registry
			&& isset($article->images)
			&& $params instanceof Registry
			&& !$params->get('iAmAModuleGhsvs', 0)
			&& isset($article->text);

		// J4 bug? https://github.com/joomla/joomla-cms/issues/35044
		// Fix for me: Split the following lines.
		$iAmAContactView =
			$this->app->isClient('site')
			&& $context === 'com_contact.contact'
			&& $view === 'contact'
			&& !empty($article->id) && !empty($article->name)
			&& !empty($article->params) && isset($article->misc);

		if ($iAmAContactView)
		{
			if (!($params instanceof Registry))
			{
				$params = $article->params;
			}

			if ($params->get('iAmAModuleGhsvs', 0))
			{
				$iAmAContactView = false;
			}
		}
		// End J4 bug.

		$print = $this->app->input->getBool('print');

		###### Extra fields from #__bs3ghsvs_article - START
		if ($iAmAnArticle && $isHtml === true)
		{
			$article->bs3ghsvsFields = Bs3GhsvsArticleHelper::getExtraFields(
				$article->id,
				[],
				true
			);
		}
		###### Extra fields from #__bs3ghsvs_article - EnD

		###### image resize - START

		// START image resize Article-Intro-Images in catgeory/featured view.
		if (
			$iAmAnArticle
			&& in_array($context, ['com_content.category', 'com_content.featured'])
			&& empty($article->introtext_imagesghsvs)
		) {
			// Build basic $article->Imagesghsvs based upon $article->images and more.
			Bs3GhsvsItemHelper::getItemImagesghsvs($article);
			$collect_images = [];

			if ($this->params->get('imageoptimizer_intro_full') === 1
				&& ($IMAGE = $article->Imagesghsvs->get('image_intro'))
				&& file_exists(JPATH_SITE . '/' . $IMAGE)
			) {
				$collect_images = Bs3GhsvsItemHelper::getImageResizeImages('image_intro', $IMAGE);
			} // end if ($IMAGE = $article->Imagesghsvs->get('image_intro'))

			$article->Imagesghsvs->set('introtext_imagesghsvs', $collect_images);
			$article->introtext_imagesghsvs = true;
		}
		// END image resize Article-Intro-Images in catgeory/featured view.

		// START image resize Article-fulltext-Images in article view.
		if (
			$iAmAnArticle
			&& in_array($context, ['com_content.article'])
			&& empty($article->fulltext_imagesghsvs)
		) {
			// Build basic $article->Imagesghsvs based upon $article->images and more.
			Bs3GhsvsItemHelper::getItemImagesghsvs($article);
			$collect_images = [];

			if ($this->params->get('imageoptimizer_intro_full') === 1
				&& ($IMAGE = $article->Imagesghsvs->get('image_fulltext'))
				&& file_exists(JPATH_SITE . '/' . $IMAGE)
			) {
				$collect_images = Bs3GhsvsItemHelper::getImageResizeImages(
					'image_fulltext',
					$IMAGE
				);
			} // end if ($IMAGE = $article->Imagesghsvs->get('image_fulltext'))

			$article->Imagesghsvs->set('fulltext_imagesghsvs', $collect_images);
			$article->fulltext_imagesghsvs = true;
		}
		// END image resize Article-fulltext-Images in article view.

		// START image resize Article Images in editor text in article view.
		if (
			$iAmAnArticle
			&& in_array($context, ['com_content.article'])
			&& $view === 'article'
			// Collection already done? If true leave!
			&& empty($article->articletext_imagesghsvs)
		) {
			if ($this->allImgSrc === null)
			{
				$this->allImgSrc = Bs3GhsvsItemHelper::getAllImgSrc($article->text);
			}

			// <img src=...> found in article text?
			if ($this->allImgSrc)
			{
				// Build *basic* $article->Imagesghsvs based upon $article->images and more.
				Bs3GhsvsItemHelper::getItemImagesghsvs($article);
				$collect_images = [];

				// First resize found images and collect results.
				if ($this->allImgSrc['src'])
				{
					if ($this->params->get('imageoptimizer_articletext') === 1)
					{
						$collect_images = Bs3GhsvsItemHelper::getImageResizeImages(
							'image_articletext',
							$this->allImgSrc['src']
						);
					}

					// Möglichkeit in einem com_content/article/xyz.php ein eigenes 'jlayout_articletext_print' unterzujubeln.
					// Anlass war gs-wesendorf, wo im Ausdruck die Bilder full sein müssen.
					// Derzeit existiert die Einstellung nur im Blog-Layout blogghsvs-standard.xml.
					if (
						$jlayout_articletext_print = trim($article->params->get('jlayout_articletext_print', ''))
					) {
						$this->params->set('jlayout_articletext', $jlayout_articletext_print);
					}

					// Kann man also im Plugin einstellen.
					if (($jlayout_articletext = trim($this->params->get('jlayout_articletext', 'ghsvs.article_image'))))
					{
						// The array indices are the same for $imgTags and $collect_images.
						// Egal welcher Index, aber 'quote' hat kleinsten String. Deshalb den.
						// Wir brauchen zum "Zählen" nur die $key.
						foreach ($this->allImgSrc['quote'] as $key => $dummy)
						{
							$imgs = [];

							if ($this->params->get('imageoptimizer_articletext') === 1)
							{
								// Todo: maybe I find the time to avoid this "Krücke".
								$imgs[0] = $collect_images[$key];
								$imgs['order'] = $collect_images['order'];
							}

							$displayData = [
								'attributes' => $this->allImgSrc['attributes'][$key],
								'imgs' => $imgs,
								'image' => $this->allImgSrc['src'][$key],
							];

							$figure = HTMLHelper::_(
								'bs3ghsvs.layout',
								$jlayout_articletext,
								$displayData
							);

							// Originalbild gegen figure/source Konstrukt austauschen.
							$article->text = str_replace(
								[
									'<p>' . $this->allImgSrc['all'][$key] . '</p>',
									$this->allImgSrc['all'][$key],
								],
								$figure,
								$article->text
							);
						}
					}
				}

				$article->Imagesghsvs->set('articletext_imagesghsvs', $collect_images);
				$article->articletext_imagesghsvs = true;
			}
		}
		###### image resize - END

		###### open graph - START
		if (
			$iAmAnArticle
			&& $isHtml === true
			&& $this->params->get('opengraphActive') === 1
			&& $context === 'com_content.article'
			&& $view === 'article'
		) {
			// Build basic $article->Imagesghsvs based upon $article->images and more.
			Bs3GhsvsItemHelper::getItemImagesghsvs($article);

			if (
				$this->params->get('imageoptimizer_intro_full') === 1
				&& $article->Imagesghsvs->get('fulltext_imagesghsvs')
				&& $this->params->get('image_fulltext')->active_og
			) {
				// Do we have a _og fulltext image?
				/* @since J!4.3 ArrayHelper::getColumn() V2.0 fails.
				Back to the roots of previous ArrayHelper version.
				*/
				$ogImages = array_column(
					(array) $article->Imagesghsvs->get('fulltext_imagesghsvs'),
					'_og',
					null
				);

				if (!empty($ogImages[0]['img-1']))
				{
					$this->ogCollection['com_content.article'][] = $ogImages[0]['img-1'];
				}
			}

			// Nothing found? Try standard images.
			if (!$this->ogCollection['com_content.article'])
			{
				$imgFields = [
					'image_fulltext',
					'image_fulltext_popupghsvs',
					'image_intro',
					'image_intro_popupghsvs',
				];

				foreach ($imgFields as $field)
				{
					if (($img = $article->Imagesghsvs->get($field, '')))
					{
						$this->ogCollection['com_content.article'][] = $img;
					}
				}
			}

			// Any images inside articletext?
			if (
				$this->params->get('imageoptimizer_articletext') === 1
				&& $article->Imagesghsvs->get('articletext_imagesghsvs')
				&& $this->params->get('image_articletext')->active_og
			) {
				// Do we have _og articletext images?
				/* @since J!4.3 ArrayHelper::getColumn() V2.0 fails.
				Back to the roots of previous ArrayHelper version.
				*/
				if ($ogImages = array_column(
					(array) $article->Imagesghsvs->get('articletext_imagesghsvs'), '_og', null))
				{
					// Reduce to only image paths.
					if ($ogImages = array_column((array) $ogImages, 'img-1', null))
					{
						$this->ogCollection['com_content.article'] = array_merge(
							$this->ogCollection['com_content.article'],
							$ogImages
						);
					}
				}
			}
			// Nothing found? Get all images by yourself from articletext.
			else
			{
				if ($this->allImgSrc === null)
				{
					$this->allImgSrc = Bs3GhsvsItemHelper::getAllImgSrc($article->text);
				}

				if ($this->allImgSrc)
				{
					$this->ogCollection['com_content.article'] = array_merge(
						$this->ogCollection['com_content.article'],
						$this->allImgSrc['src']
					);
				}
			}
		}
		###### open graph - END

		###### schema-org - START

		/* Vorübergehende Krücke, da ich BreadCrumbList separat aktivieren will, was
		für andere Settings ebenfalls folgen soll. */
		$structureddataActive = $this->params->get('structureddataActive', 0) === 1;
		$structureddataBreadcrumbListActive =
			$this->params->get('structureddataBreadcrumbListActive', 1) === 1
			&& !isset(static::$loaded[__METHOD__]['sd_breadcrumbList']);

		$doSd = $isHtml === true && $this->sd_robotsStateOk
			&& ($structureddataActive === true || $structureddataBreadcrumbListActive === true)
			&& $this->structuredataghsvsinstalled === true;

		if ($doSd === true)
		{
			\JLoader::register(
				'Bs3ghsvsStructuredData',
				__DIR__ . '/Helper/StructuredData.php'
			);

			if (empty($wa))
			{
				$doc = Factory::getDocument();
			}

			$prettyPrint = JDEBUG || $this->params->get('sd_prettyPrint', 0)
				? JSON_PRETTY_PRINT : 0;

			// start Schema BreadcrumbList
			if (
				$structureddataBreadcrumbListActive === true
				&& !isset(static::$loaded[__METHOD__]['sd_breadcrumbList']))
			{
				$waName = 'sd_breadcrumbList';

				if (
					$iAmAnArticle
					&& in_array($context, ['com_content.article'])
					&& $view === 'article'
					&& !empty($article->readmore_link)
					&& !empty($article->title)
				) {
					$schema = Bs3ghsvsStructuredData::sd_breadcrumbList($this->app, $article);
				}
				else
				{
					$schema = Bs3ghsvsStructuredData::sd_breadcrumbList($this->app);
				}

				// Don't use addScriptDeclaration in Joomla 3!
				// https://github.com/joomla/joomla-cms/pull/25117#issuecomment-518005517
				// https://github.com/joomla/joomla-cms/pull/25357
				if (empty($wa))
				{
					$doc->addCustomTag(Bs3ghsvsStructuredData::buildScriptTag($schema, $prettyPrint));
				}
				else
				{
					$wa->addInline(
						'script',
						json_encode($schema, $prettyPrint | JSON_UNESCAPED_UNICODE),
						['name' => 'plg_system_bs3ghsvs.' . $waName],
						['type' => 'application/ld+json']
					);
				}

				static::$loaded[__METHOD__]['sd_breadcrumbList'] = 1;
			}
			// end Schema BreadcrumbList

			if ($structureddataActive)
			{
				// start Schema Organization
				if (!isset(static::$loaded[__METHOD__]['sd_organization']))
				{
					$waName = 'sd_organization';

					if ($iAmAContactView)
					{
						$schema = Bs3ghsvsStructuredData::sd_contactPoint($article);
						$waName = 'sd_contactPoint';
					}
					else
					{
						$schema = Bs3ghsvsStructuredData::sd_organization(
							!$this->templateParams->get('isFrontpage')
						);
					}

					// Don't use addScriptDeclaration in Joomla 3!
					// https://github.com/joomla/joomla-cms/pull/25117#issuecomment-518005517
					// https://github.com/joomla/joomla-cms/pull/25357
					if (empty($wa))
					{
						$doc->addCustomTag(Bs3ghsvsStructuredData::buildScriptTag($schema, $prettyPrint));
					}
					else
					{
						$wa->addInline(
							'script',
							json_encode($schema, $prettyPrint | JSON_UNESCAPED_UNICODE),
							['name' => 'plg_system_bs3ghsvs.' . $waName],
							['type' => 'application/ld+json']
						);
					}

					// double Paranoia.
					static::$loaded[__METHOD__]['sd_organization'] = 1;
				}
				// end Schema Organization

				// start Schema Article
				if (
					$iAmAnArticle
					&& !isset(static::$loaded[__METHOD__]['sd_article'])
					&& in_array($context, ['com_content.article'])
					&& $view === 'article'
				) {
					$waName = 'sd_article';
					$schema = Bs3ghsvsStructuredData::sd_article($article, $this->allImgSrc);

					// Don't use addScriptDeclaration in Joomla 3!
					// https://github.com/joomla/joomla-cms/pull/25117#issuecomment-518005517
					// https://github.com/joomla/joomla-cms/pull/25357
					if (empty($wa))
					{
						$doc->addCustomTag(Bs3ghsvsStructuredData::buildScriptTag($schema, $prettyPrint));
					}
					else
					{
						$wa->addInline(
							'script',
							json_encode($schema, $prettyPrint | JSON_UNESCAPED_UNICODE),
							['name' => 'plg_system_bs3ghsvs.' . $waName],
							['type' => 'application/ld+json']
						);
					}

					// double Paranoia.
					static::$loaded[__METHOD__]['sd_article'] = 1;
				} // end Schema Article
			}
		}
		###### schema-org - END
	}

	public function onSubmitContact(&$contact, &$data)
	{
		if (!empty($data['contact_phoneghsvs']))
		{
			$data['contact_message'] .= "\n\n";
			$data['contact_message'] .= Text::_('COM_CONTACT_CONTACT_VIEW_GHSVS_TELEPHONE');
			$data['contact_message'] .= ': ' . $data['contact_phoneghsvs'];
		}

		return true;
	}

	public function onExtensionAfterDelete($context, $table)
	{
		$this->order();
	}

	public function onExtensionAfterSave($context, $table, $isNew)
	{
		$this->order();
	}

	public function onContentSearch($text = '', $phrase = '', $ordering = '', $areas = null)
	{
		// Wegen möglichem Fatal Error auf com_search, wenn bspw. Template einen unvorsichtigen Override enthält wie bei tpl_herzpraxis_astroid_ghsvs.
		$wa = self::getWa();
		return [];
	}

	public function onBeforeCompileHead()
	{
		if ($this->params->get('loadBootstrapEarly', 1))
		{
			// Load BS.
			HTMLHelper::_('bootstrap.framework');
		}
		//$wa = self::getWa();
		//$wa->usePreset('plg_system_bs3ghsvs.custom');
		// Auf manchen Seiten nötig sonst fatal error.
		// Vielleicht Methode schreiben, um nicht doppelt zu laden?
		// mit static::$loaded.
		$this->templateParams = $this->app->getTemplate(true)->params;

		#### Kill canonicals START
		// Kein Bock mehr. Kille canonical komplett und Schluss!
		if ($this->params->get('deleteCanonicals', 1))
		{
			$doc = Factory::getDocument();

			foreach ($doc->_links as $k => $array)
			{
				if ($array['relation'] === 'canonical')
				{
					unset($doc->_links[$k]);
				}
			}
		}
		#### Kill canonicals END

		#### Open Graph tags START
		if (
			$this->app->isClient('site')
			&& $this->params->get('opengraphActive') === 1
		) {
			$doc = Factory::getDocument();
			$doc->addCustomTag('<meta property="og:title" content="'
				. htmlentities($doc->getTitle(), ENT_QUOTES, 'utf-8') . '">');
			$doc->addCustomTag('<meta property="og:url" content="' . Uri::current()
				. '">');
			$doc->addCustomTag('<meta property="og:site_name" content="'
				. htmlentities($this->app->get('sitename'), ENT_QUOTES, 'utf-8') . '">');
			$doc->addCustomTag('<meta property="og:description" content="'
				. htmlentities((string) $doc->getDescription(), ENT_QUOTES, 'utf-8')
				. '">');

			if ($this->templateParams->get('isFrontpage'))
			{
				$doc->addCustomTag('<meta property="og:type" content="website">');
			}
			elseif ($this->app->input->get('view') === 'article')
			{
				$doc->addCustomTag('<meta property="og:type" content="article">');
			}

			$default_images_path = $this->params->get('og_default_images', 'images/fb_default_images');

			if (is_dir(JPATH_SITE . '/' . $default_images_path))
			{
				$filter = '\.(jpg|JPG|gif|GIF|jpeg|JPEG|png|PNG)$';
				$this->ogCollection['default_images'] = Folder::files(
					JPATH_SITE . '/' . $default_images_path,
					$filter
				);

				foreach ($this->ogCollection['default_images'] as $i => $img_url)
				{
					$this->ogCollection['default_images'][$i] = $default_images_path . '/' . $img_url;
				}
			}

			$this->ogCollection = array_merge(
				$this->ogCollection['com_content.article'],
				$this->ogCollection['mod_custom.content'],
				$this->ogCollection['default_images']
			);

			$this->ogCollection = array_unique($this->ogCollection);

			foreach ($this->ogCollection as $imagePath)
			{
				$doc->addCustomTag('<meta property="og:image" content="' . Bs3GhsvsItemHelper::addUriRoot($imagePath) . '">');
			}
		}
		#### Open Graph tags END
		return;
	}

	/**
	 * Structureddata. Kill microdata attributes if activated.
	 */
	public function onAfterRender()
	{
		if (!$this->app->isClient('site'))
		{
			return;
		}

		$done             = 0;
		$sd_killmicrodata = $this->params->get('structureddataActive', 0) === 1
			&& $this->params->get('sd_killmicrodata', 1) === 1;

		if ($sd_killmicrodata)
		{
			$html   = [];
			$all    = $this->app->getBody();
			$checks = ['<body ', '<body>'];
			$sepa   = '';

			foreach ($checks as $check)
			{
				if (strpos($all, $check) !== false)
				{
					$html = explode($check, $all);

					if (count($html) === 2)
					{
						$sepa = $check;
					}
					break;
				}
			}

			if ($sepa === '')
			{
				return;
			}

			if ($sd_killmicrodata && strpos($html[1], 'itemscope') !== false)
			{
				$html[1] = str_replace(
					[' itemscope ', ' itemtype=', ' itemprop='],
					[' data-itemscopeOff ', ' data-itemtypeOff=', ' data-itempropOff='],
					$html[1],
					$done
				);
			}

			if ($done)
			{
				$this->app->setBody(implode($sepa, $html));
			}
		}
	}

	protected function register()
	{
		$error = [];

		if (false === RegisterJHtml::register('bootstrap'))
		{
			$this->executeFe = false;
			$error[] = 'Bs3GhsvsRegisterBootstrap';
		}

		if (false === RegisterJHtml::register('bs3ghsvs'))
		{
			$this->executeFe = false;
			$error[] = 'Bs3GhsvsRegisterBs3ghsvs';
		}

		if (false === RegisterJHtml::register('iconghsvs'))
		{
			$this->executeFe = false;
			$error[] = 'Bs3GhsvsRegisterIcon';
		}

		if ($error)
		{
			$add = __METHOD__ . ': Something went completely wrong while registering these HTMLHelper-Methods: ' . implode(', ', $error);

			if (self::$log)
			{
				Log::add($add, Log::CRITICAL, 'bs3ghsvs');
			}

			$this->app->enqueueMessage($add, 'error');

			return false;
		}

		return true;
	}

	/**
	 * Reorder this plugin as FIRST one in table #__extensions.
	*/
	protected function order()
	{
		$ordering = $this->db->qn('ordering');
		$table = $this->db->qn('#__extensions');
		$type = $this->db->qn('type') . ' = ' . $this->db->q('plugin');
		$folder = $this->db->qn('folder') . '=' . $this->db->q('system');
		$element = $this->db->qn('element') . ' != ' . $this->db->q($this->_name);
		$query = $this->db->getQuery(true)
			->select('MIN(' . $ordering . ')')->from($table)
			->where($type)->where($folder)->where($element);

		$this->db->setQuery($query);

		$min = (int) $this->db->loadResult();
		$min = $min - 10;

		$query->clear()
			->update($table)->set($ordering . '=' . (int) $min)
			->where($type)->where($folder)->where(str_replace('!=', '=', $element))
			->where($ordering . '!=' . (int) $min);

		$this->db->setQuery($query);

		try
		{
			$this->db->execute();
		}
		catch (RuntimeException $e)
		{
			if (self::$log)
			{
				$add = __METHOD__ . ': DB-Sortierung konnte nicht geprüft/gesetzt werden.';
				Log::add($add, Log::CRITICAL, 'bs3ghsvs');
			}

			return false;
		}

		return true;
	}

	function onAjaxSessionBs3Ghsvs()
	{
		if (strtolower($this->app->input->server->get('HTTP_X_REQUESTED_WITH', '')) !== 'xmlhttprequest')
		{
			return 'Have fun, laugh louder';
			// return __METHOD__ . ' not allowed.';
		}

		// AJAX-Input.
		$input = $this->app->input;
		$cmd = $input->get('cmd', '', 'ALNUM');
		$key = trim($input->get('key', '', 'STRING'));

		if (!$key || !in_array($cmd, ['add', 'get', 'destroy']))
		{
			return;
		}

		$data = $input->get('data', '', 'STRING');

		$node  = static::$basepath;
		$session = Factory::getSession();
		$sessionData = $session->get($node);

		switch ($cmd)
		{
			case 'add':
				$sessionData[$key] = $data;
				$session->set($node, $sessionData);

				return($sessionData[$key] . ' written.');
				break;
			case 'get':
				return isset($sessionData[$key]) ? $sessionData[$key] : null;
				break;
			case 'destroy':
				$sessionData = null;
				$session->set($node, $sessionData);

				return '$sessionData destroyed';
				break;
		}

		return;
	}

	/**
	 * Geht wahrscheinlich besser. Ist zum Resizen von speziellen Modul-Bildern.
	 * $imagesToResizeCollect Die Bilder, schon als HTML-Tags. Sollen final mit figure HTML zurückgehen.
	 */
	public static function moduleImagesResizing(
		array $imagesToResizeCollect,
		$moduleParams = false
	) : array {
		$PlgParams = Bs3GhsvsHelper::getPluginParams();

		// Modules resizing should get an own setting. Too lazy at the moment.
		if ($PlgParams->get('imageoptimizer_articletext') !== 1)
		{
			return $imagesToResizeCollect;
		}

		// "cache" original keys. Some modules can have 'foto0', 'foto1'...
		$imagesToResizeCollectKeys = array_keys($imagesToResizeCollect);

		// Extract images HTML tags parts via preg_match all.
		$imagesInModule = Bs3GhsvsItemHelper::getAllImgSrc(implode($imagesToResizeCollect));

		$collect_images = [];
		$imagesToResizeCollectTemp = [];
		$layoutFound = false;

		// First resize found images and collect results.
		if (!empty($imagesInModule['src']))
		{
			// Resize and create resized images collection (contains width/height and so on, too).
			$collect_images = Bs3GhsvsItemHelper::getImageResizeImages(
				// Be careful. Already reserved: 'image_intro', 'image_fulltext', 'image_articletext'!
				'image_module',
				$imagesInModule['src'],
				// Which sizePostfixes to use (plugin settings).
				'image_articletext'
			);

			if ($moduleParams instanceof Registry)
			{
				if (!($jlayout_articletext = trim($moduleParams->get('jlayout_articletext', ''))))
				{
					$jlayout_articletext = trim($PlgParams->get(
						'jlayout_articletext',
						'ghsvs.article_image'
					));
				}
			}

			// Call resize and create figure HTML.
			if ($jlayout_articletext)
			{
				// The array indices are the same for $imgTags and $collect_images.
				// Egal welcher Index, aber 'quote' hat kleinsten String. Deshalb den.
				// Wir brauchen zum "Zählen" nur die $key.
				foreach ($imagesInModule['quote'] as $key => $dummy)
				{
					$displayData = [
						'attributes' => $imagesInModule['attributes'][$key],
						'pre'  => $imagesInModule['pre'][$key],
						'post' => $imagesInModule['post'][$key],
						'images' => $collect_images[$key],
					];

					$figure = HTMLHelper::_(
						'bs3ghsvs.layout',
						$jlayout_articletext,
						$displayData
					);

					if ($figure && !$layoutFound)
					{
						$layoutFound = true;
					}

					$imagesToResizeCollectTemp[$imagesToResizeCollectKeys[$key]] = $figure;
				}
			}
		}

		if ($layoutFound)
		{
			return $imagesToResizeCollectTemp;
		}

		return $imagesToResizeCollect;
	}

	/*
	Kann man dann wohl raushauen, die Methode hier.
	*/
	private static function getWa()
	{
		return Bs3GhsvsHelper::getWa();
	}
}
