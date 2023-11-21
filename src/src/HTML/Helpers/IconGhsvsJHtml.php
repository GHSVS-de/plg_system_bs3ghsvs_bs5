<?php
namespace GHSVS\Plugin\System\Bs3Ghsvs\HTML\Helpers;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

abstract class IconGhsvsJHtml
{
	protected static $loaded = [];

	// media-Ordner:
	protected static $basepath = 'plg_system_bs3ghsvs';

	/**
	 * iconghsvs.print_popup
		* Override ermöglicht Verwendung einer print.php anstatt component.php, d.h. einfach eine print.php im Templateordner anlegen, wenn man component.php nicht mag.
		* Und location=yes.
	 *
	 * Method to generate a popup link to print an article
	 *
	 * @param   object    $article  The article information
	 * @param   Registry  $params   The item parameters
	 * @param   array     $attribs  Optional attributes for the link
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the popup link
	 */
	public static function print_popup(
		$article,
		$params,
		$attribs = [],
		$legacy = false,
		$tmpl = 'print',
		$iconClass = ''
	) {
		$app = Factory::getApplication();
		$input = $app->input;
		$request = $input->request;
		$template = $app->getTemplate();

		if (!is_file(JPATH_THEMES . '/' . $template . '/' . $tmpl . '.php'))
		{
			$tmpl = 'component';
		}

		$url  = \ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->language);
		$url .= '&tmpl=' . $tmpl . '&print=1&layout=default';

		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=yes';

		$text = HTMLHelper::_(
			'bs3ghsvs.layout',
			'joomla.content.icons.print_popup',
			['params' => $params, 'legacy' => $legacy]
		);

		$attribs['title']   = htmlspecialchars(
			Text::sprintf('PLG_SYSTEM_BS3GHSVS_PRINT', $article->title, ENT_QUOTES, 'UTF-8')
		);
		#$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";
		$attribs['rel']     = 'nofollow noopener noreferrer';

		$attribs['target'] = '_blank';
		$attribs = ArrayHelper::toString($attribs);
		$text = '<span class="visually-hidden">' . trim($text) . '</span>';

		if ($iconClass)
		{
			if (strpos($iconClass, '{') === false)
			{
				$iconClass = '<span class="' . $iconClass . '" aria-hidden="true"></span>';
			}
		}

		return '<a href="' . Route::_($url) . '" ' . $attribs . '>' . $iconClass . $text . '</a>';
		# return '<a href="' . Route::_($url) . '" target="_blank">' . $iconClass . $text . '</a>';
	}

	/**
	 * iconghsvs.print_screen
	 * Method to generate a link to print an article
	 *
	 * @param   Registry  $params   The item parameters
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the popup link
	 */
	public static function print_screen($params, $legacy = false)
	{
		$text = HTMLHelper::_(
			'bs3ghsvs.layout',
			'joomla.content.icons.print_screen',
			['params' => $params, 'legacy' => $legacy]
		);

		Factory::getDocument()->addScriptDeclaration(
			<<<JS
document.addEventListener('DOMContentLoaded', function()
{
	document.getElementById("a4printButton")
	.addEventListener('click', (e) => {
		window.print();
		e.stopImmediatePropagation();
		e.preventDefault();
	});
});
JS
		);

		return '<a id="a4printButton" href="#">' . $text . '</a>';
	}
}
