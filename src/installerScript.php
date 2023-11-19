<?php
/*
 * Use in your extension manifest file (any tag is optional!!!!!):
 * <minimumPhp>7.0.0</minimumPhp>
 * <minimumJoomla>3.9.0</minimumJoomla>
 * Yes, use 999999 to match '3.9'. Otherwise comparison will fail.
 * <maximumJoomla>3.9.999999</maximumJoomla>
 * <maximumPhp>7.3.999999</maximumPhp>
 * <allowDowngrades>1</allowDowngrades>
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Log\Log;

class plgSystemBs3GhsvsInstallerScript extends InstallerScript
{
	/**
	 * A list of files to be deleted with method removeFiles().
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $deleteFiles = [

		'/plugins/system/bs3ghsvs/html/animsitionghsvs.php',
		'/plugins/system/bs3ghsvs/html/slicknavghsvs.php',
		'/plugins/system/bs3ghsvs/html/___HALDE___.php',
		'/plugins/system/bs3ghsvs/html/addsprungmarketourlghsvs.php',
		'/plugins/system/bs3ghsvs/html/bootstrapaccordionghsvs.php',
		'/plugins/system/bs3ghsvs/html/bs3ghsvsghsvs.php',
		'/plugins/system/bs3ghsvs/html/menuslideghsvs.php',
		'/plugins/system/bs3ghsvs/html/searchmodulesghsvs.php',
		'/plugins/system/bs3ghsvs/html/slideinpanelghsvs.php',
		'/plugins/system/bs3ghsvs/html/smoothscrollghsvs.php',
		'/plugins/system/bs3ghsvs/html/tplhtmlghsvs.php',
		'/plugins/system/bs3ghsvs/html/venoboxghsvs.php',
		'/plugins/system/bs3ghsvs/html/lessghsvs.php',
		'/plugins/system/bs3ghsvs/html/footableghsvs.php',

		'/plugins/system/bs3ghsvs/language/de-DE/de-DE.plg_system_bs3ghsvs-copy.ini',
		'/plugins/system/bs3ghsvs/language/en-GB/en-GB.plg_system_bs3ghsvs-copy.ini',
		'/plugins/system/bs3ghsvs/language/de-DE/de-DE.plg_system_bs3ghsvs.ini',
		'/plugins/system/bs3ghsvs/language/en-GB/en-GB.plg_system_bs3ghsvs.ini',
		'/plugins/system/bs3ghsvs/language/en-GB/en-GB.plg_system_bs3ghsvs.sys.ini',

		'/plugins/system/bs3ghsvs/Field/lessenabled.php',
		'/plugins/system/bs3ghsvs/Field/articleswithextrafieldsinfo.php',
		'/plugins/system/bs3ghsvs/Field/enabledchecker.php',
		'/plugins/system/bs3ghsvs/myforms/modules.xml',
		'/plugins/system/bs3ghsvs/myforms/search_modules.xml',
		'/plugins/system/bs3ghsvs/myforms/templates.xml',
		'/plugins/system/bs3ghsvs/myforms/templates_less.xml',


		'/plugins/system/bs3ghsvs/Field/iconsghsvsinstalled.php',
		'/plugins/system/bs3ghsvs/Field/assetsbe.php',
		'/plugins/system/bs3ghsvs/Field/version.php',
		'/plugins/system/bs3ghsvs/Field/templatesjsonconfigurationinfo.php',
		'/plugins/system/bs3ghsvs/Field/imgresizeghsvsinstalled.php',
		'/plugins/system/bs3ghsvs/Field/structuredataghsvsinstalled.php',
		'/plugins/system/bs3ghsvs/src/Field/templatesjsonconfigurationinfo.php',

		'/plugins/system/bs3ghsvs/Helper/PagebreakHelper.php',
		'/plugins/system/bs3ghsvs/Helper/ItemHelper.php',
		'/plugins/system/bs3ghsvs/Helper/ArticleHelper.php',

		'/media/plg_system_bs3ghsvs/layouts/ghsvs/frontediting_modules_in_article.php',
		'/media/plg_system_bs3ghsvs/layouts/ghsvs/scroll-to-article-modal.php',

		'/media/plg_system_bs3ghsvs/js/category-blog-list-toggle.js',
		'/media/plg_system_bs3ghsvs/js/category-blog-list-toggle.min.js',
		'/media/plg_system_bs3ghsvs/js/addSprungmarkeToUrlGhsvs-uncompressed.js',
		'/media/plg_system_bs3ghsvs/js/caption.js',
		'/media/plg_system_bs3ghsvs/js/jquery.backToTop-uncompressed.js',
		'/media/plg_system_bs3ghsvs/js/jquery.backToTop.js',
		'/media/plg_system_bs3ghsvs/js/jquery.menuslideghsvs.js',
		'/media/plg_system_bs3ghsvs/js/jquery.sessionTogglerButton-uncompressed.js',
		'/media/plg_system_bs3ghsvs/js/jquery.sessionTogglerButton.js',
		'/media/plg_system_bs3ghsvs/js/jquery.topmenushowghsvs.js',
		'/media/plg_system_bs3ghsvs/js/template-uncompressed.js',
		'/media/plg_system_bs3ghsvs/js/Wo-sind-welche-functions-JQuery.xlsx',

		'/media/plg_system_bs3ghsvs/css/index.html',
		'/media/plg_system_bs3ghsvs/css/jquery.backToTop-uncompressed.css',
		'/media/plg_system_bs3ghsvs/css/jquery.backToTop.css',
		'/media/plg_system_bs3ghsvs/css/venobox-uncompressed.css',
		'/media/plg_system_bs3ghsvs/css/venobox.css',

	];

	/**
	 * A list of folders to be deleted with method removeFiles().
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $deleteFolders = [
		'/plugins/system/bs3ghsvs/Helper/schema-org',

		'/plugins/system/bs3ghsvs/vendor',
		'/media/plg_system_bs3ghsvs/less',
		'/media/plg_system_bs3ghsvs/fontawesome-free',

		'/media/plg_system_bs3ghsvs/css/animsition',
		'/media/plg_system_bs3ghsvs/css/bootstrap',
		'/media/plg_system_bs3ghsvs/css/bootstrap4',
		'/media/plg_system_bs3ghsvs/css/footable2',
		'/media/plg_system_bs3ghsvs/css/SlickNav',
		'/media/plg_system_bs3ghsvs/css/footable',

		'/media/plg_system_bs3ghsvs/js/skipto',
		'/media/plg_system_bs3ghsvs/js/tocGhsvs',
		'/media/plg_system_bs3ghsvs/js/animsition',
		'/media/plg_system_bs3ghsvs/js/SlickNav',
		'/media/plg_system_bs3ghsvs/js/jquery-migrate',
		'/media/plg_system_bs3ghsvs/js/slide-in-panel',
		'/media/plg_system_bs3ghsvs/js/footable',
		'/media/plg_system_bs3ghsvs/js/moment',
		'/media/plg_system_bs3ghsvs/js/bootstrap',
		'/media/plg_system_bs3ghsvs/js/jquery',
		'/media/plg_system_bs3ghsvs/js/__HALDE__',
		'/media/plg_system_bs3ghsvs/js/bootstrap4',
		'/media/plg_system_bs3ghsvs/js/footable2',
		'/media/plg_system_bs3ghsvs/js/venobox',

		'/plugins/system/bs3ghsvs/versions-installed',
		'/media/plg_system_bs3ghsvs/svgs',
		'/media/plg_system_bs3ghsvs/scss/bootstrap',

		'/plugins/system/bs3ghsvs/Field',
	];

	public function preflight($type, $parent)
	{
		$manifest = @$parent->getManifest();

		if ($manifest instanceof SimpleXMLElement)
		{
			if ($type === 'update' || $type === 'install' || $type === 'discover_install')
			{
				$minimumPhp = trim((string) $manifest->minimumPhp);
				$minimumJoomla = trim((string) $manifest->minimumJoomla);

				// Custom
				$maximumPhp = trim((string) $manifest->maximumPhp);
				$maximumJoomla = trim((string) $manifest->maximumJoomla);

				$this->minimumPhp = $minimumPhp ? $minimumPhp : $this->minimumPhp;
				$this->minimumJoomla = $minimumJoomla ? $minimumJoomla : $this->minimumJoomla;

				if ($maximumJoomla && version_compare(JVERSION, $maximumJoomla, '>'))
				{
					$msg = 'Your Joomla version (' . JVERSION . ') is too high for this extension. Maximum Joomla version is: ' . $maximumJoomla . '.';
					Log::add($msg, Log::WARNING, 'jerror');
				}

				// Check for the maximum PHP version before continuing
				if ($maximumPhp && version_compare(PHP_VERSION, $maximumPhp, '>'))
				{
					$msg = 'Your PHP version (' . PHP_VERSION . ') is too high for this extension. Maximum PHP version is: ' . $maximumPhp . '.';

					Log::add($msg, Log::WARNING, 'jerror');
				}

				if (isset($msg))
				{
					return false;
				}
			}

			if (trim((string) $manifest->allowDowngrades))
			{
				$this->allowDowngrades = true;
			}
		}

		if (!parent::preflight($type, $parent))
		{
			return false;
		}

		if ($type === 'update')
		{
			$this->removeOldUpdateservers();
		}

		return true;
	}

	/**
	 * Runs right after any installation action is preformed on the component.
	 *
	 * @param  string    $type   - Type of PostFlight action. Possible values are:
	 *                           - * install
	 *                           - * update
	 *                           - * discover_install
	 * @param  \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
	function postflight($type, $parent)
	{
		if ($type === 'update')
		{
			$this->removeFiles();
		}
	}

	/**
	 * Remove the outdated updateservers.
	 *
	 * @return  void
	 *
	 * @since   version after 2019.05.29
	 */
	protected function removeOldUpdateservers()
	{
		$db = Factory::getDbo();

		try
		{
			$query = $db->getQuery(true);

			$query->select('update_site_id')
				->from($db->qn('#__update_sites'))
				->where($db->qn('location') . ' = '
					. $db->q('https://raw.githubusercontent.com/GHSVS-de/upadateservers/master/bs3ghsvs2020-update.xml'), 'OR')
				->where($db->qn('location') . ' = '
					. $db->q('https://raw.githubusercontent.com/GHSVS-de/upadateservers/master/plg_system_bs3ghsvs-update.xml'), 'OR')
				->where($db->qn('location') . ' = '
					. $db->q('https://raw.githubusercontent.com/GHSVS-de/upadateservers/master/bs3ghsvs-update.xml'));

			$ids = $db->setQuery($query)->loadAssocList('update_site_id');

			if (!$ids)
			{
				return;
			}

			$ids = array_keys($ids);
			$ids =implode(',', $ids);

			// Delete from update sites
			$db->setQuery(
				$db->getQuery(true)
					->delete($db->qn('#__update_sites'))
					->where($db->qn('update_site_id') . ' IN (' . $ids . ')')
			)->execute();

			// Delete from update sites extensions
			$db->setQuery(
				$db->getQuery(true)
					->delete($db->qn('#__update_sites_extensions'))
					->where($db->qn('update_site_id') . ' IN (' . $ids . ')')
			)->execute();
		}
		catch (Exception $e)
		{
			return;
		}
	}
}
