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
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;

class plgSystemBs3GhsvsInstallerScript extends InstallerScript
{
	/**
	 * A list of files to be deleted with method removeFiles().
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $deleteFiles = array(
		'/media/plg_system_bs3ghsvs/css/index.html',
		'/media/plg_system_bs3ghsvs/js/jquery/version.txt',
		'/media/plg_system_bs3ghsvs/js/jquery/jquery-3.4.1.js',
		'/media/plg_system_bs3ghsvs/js/jquery/jquery-3.4.1.min.js',
		'/media/plg_system_bs3ghsvs/js/jquery/jquery-3.4.1.min.map',
		'/media/plg_system_bs3ghsvs/js/jquery/jquery-3.4.1.slim.js',
		'/media/plg_system_bs3ghsvs/js/jquery/jquery-3.4.1.slim.min.js',
		'/media/plg_system_bs3ghsvs/js/jquery/jquery-3.4.1.slim.min.map',
		'/media/plg_system_bs3ghsvs/fontawesome-free/5/_V5.11.2/index.html',
		'/media/plg_system_bs3ghsvs/fontawesome-free/5/_V5.13.0/index.html',
		'/media/plg_system_bs3ghsvs/fontawesome-free/5/svgs/solid/haykal.svg',
		'/plugins/system/bs3ghsvs/vendor/spatie/schema-org/src/Contracts/LockerDeliveryContract.php',
		'/plugins/system/bs3ghsvs/vendor/spatie/schema-org/src/LockerDelivery.php',
		'/plugins/system/bs3ghsvs/vendor/spatie/schema-org/src/ParcelService.php',
		'/plugins/system/bs3ghsvs/vendor/spatie/schema-org/src/Contracts/ParcelServiceContract.php',
		'/media/plg_system_bs3ghsvs/svgs/bi/patch-check-fll.svg',
		'/media/plg_system_bs3ghsvs/svgs/bi/patch-exclamation-fll.svg',
		'/media/plg_system_bs3ghsvs/svgs/bi/patch-minus-fll.svg',
		'/media/plg_system_bs3ghsvs/svgs/bi/patch-plus-fll.svg',
		'/media/plg_system_bs3ghsvs/svgs/bi/patch-question-fll.svg',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/_code.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/_custom-forms.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/_input-group.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/_jumbotron.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/_media.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/_print.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/_jumbotron.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/mixins/_background-variant.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/mixins/_badge.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/mixins/_float.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/mixins/_grid-framework.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/mixins/_hover.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/mixins/_nav-divider.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/mixins/_screen-reader.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/mixins/_size.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/mixins/_table-row.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/mixins/_text-emphasis.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/mixins/_text-hide.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/mixins/_visibility.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_align.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_background.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_borders.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_clearfix.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_display.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_embed.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_flex.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_float.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_interactions.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_overflow.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_position.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_screenreaders.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_shadows.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_sizing.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_spacing.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_stretched-link.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_text.scss',
		'/media/plg_system_bs3ghsvs/scss/bootstrap/utilities/_visibility.scss',
		'/plugins/system/bs3ghsvs/html/animsitionghsvs.php',
		'/media/plg_system_bs3ghsvs/jquery-migrate/.eslintrc.json',
		'/media/plg_system_bs3ghsvs/jquery-migrate/jquery-migrate-1.4.1.js',
		'/media/plg_system_bs3ghsvs/jquery-migrate/jquery-migrate-1.4.1.min.js',
		'/media/plg_system_bs3ghsvs/jquery-migrate/jquery-migrate-3.1.0.js',
		'/media/plg_system_bs3ghsvs/jquery-migrate/jquery-migrate-3.1.0.minjs',
		'/media/plg_system_bs3ghsvs/jquery-migrate/jquery-migrate-3.3.0.js',
		'/media/plg_system_bs3ghsvs/jquery-migrate/jquery-migrate-3.3.0.min.js',
		'/plugins/system/bs3ghsvs/html/slicknavghsvs.php',
		'/media/plg_system_bs3ghsvs/fontawesome-free/attribution.js',
		'/plugins/system/bs3ghsvs/language/de-DE/de-DE.plg_system_bs3ghsvs-copy.ini',
		'/plugins/system/bs3ghsvs/language/en-GB/en-GB.plg_system_bs3ghsvs-copy.ini',
		'/plugins/system/bs3ghsvs/Field/lessenabled.php',
		'/plugins/system/bs3ghsvs/html/lessghsvs.php',
	);

	/**
	 * A list of folders to be deleted with method removeFiles().
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $deleteFolders = array(
		'/media/plg_system_bs3ghsvs/fontawesome-free/5/_V5.11.2',
		'/media/plg_system_bs3ghsvs/fontawesome-free/5/_V5.13.0',
		'/media/plg_system_bs3ghsvs/fontawesome-free/5',
		'/media/plg_system_bs3ghsvs/js/bootstrap/4',
		'/media/plg_system_bs3ghsvs/css/bootstrap/4',
		'/plugins/system/bs3ghsvs/Helper/schema-org',
		'/media/plg_system_bs3ghsvs/js/skipto',
		'/plugins/system/bs3ghsvs/vendor/scssphp/scssphp/bin',
		'/plugins/system/bs3ghsvs/vendor/bin',
		'/plugins/system/bs3ghsvs/vendor/scssphp',
		'/media/plg_system_bs3ghsvs/less',
		'/media/plg_system_bs3ghsvs/js/bootstrap/3',
		'/media/plg_system_bs3ghsvs/js/bootstrap/3.4.1',
		'/media/plg_system_bs3ghsvs/css/bootstrap/3',
		'/media/plg_system_bs3ghsvs/css/bootstrap/3.4.1',
		'/media/plg_system_bs3ghsvs/js/jquery/1.12.4',
		'/media/plg_system_bs3ghsvs/js/jquery/2.2.4',
		'/media/plg_system_bs3ghsvs/js/jquery/3.4.1',
		'/media/plg_system_bs3ghsvs/js/tocGhsvs',
		'/media/plg_system_bs3ghsvs/js/animsition',
		'/media/plg_system_bs3ghsvs/css/animsition',
		'/media/plg_system_bs3ghsvs/css/SlickNav',
		'/media/plg_system_bs3ghsvs/js/SlickNav',
		'/media/plg_system_bs3ghsvs/fontawesome-free/sprites',
		'/media/plg_system_bs3ghsvs/fontawesome-free/svgs',
		'/media/plg_system_bs3ghsvs/js/slide-in-panel',
		'/plugins/system/bs3ghsvs/vendor/spatie',
	);

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

			$ids = \array_keys($ids);
			$ids =\implode(',', $ids);

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
