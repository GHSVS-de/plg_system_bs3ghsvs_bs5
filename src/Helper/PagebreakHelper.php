<?php
/*
 *
JLoader::register('Bs3ghsvsPagebreak',JPATH_PLUGINS . '/system/bs3ghsvs/Helper/PagebreakHelper.php');
*/

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Utility\Utility;

use Joomla\Registry\Registry;

class Bs3ghsvsPagebreak
{
	protected static $loaded = [];

	/**
	* @var Registry|array|null null will load bs3ghsvs-Params.
	*/
	public static function buildSliders(&$theText, $id = null, $params = null)
	{
		$pluginParams = clone PlgSystemBS3Ghsvs::getPluginParams();

		if (is_array($params))
		{
			$params = new Registry($params);
			$pluginParams->merge($params);
		}

		if (is_null($id))
		{
			$id = uniqid();
		}

		$app = Factory::getApplication();
		$print = $app->input->getBool('print');
		$isRobot = (int) $app->client->robot;

		// JCE removes <p> around SHORTCODEs if setting in JCE yes. Therefore new regex since 2020-09.
		# Old: $regex = '#<p[^>]*>\s*{pagebreakghsvs-slider\s+(.+?)}\s*</p>#i';
		$not = '[^}\n\r\t]';
		$regex = '#(<p[^>]*>\s*){0,1}{pagebreakghsvs-slider\s+(' . $not
			. '+?)}(\s*</p>){0,1}#i';
		$regexEnd = '#(<p[^>]*>\s*){0,1}{pagebreakghsvs-slider' . $not
			. '+slidersEnd' . $not . '*}(\s*</p>){0,1}#iU';
		$toggleContainer = $pluginParams->get('toggleContainer', 'div');
		$headingTagGhsvs = $pluginParams->get('headingTagGhsvs', 'h4');
		$collector = $endedTextBlocks = [];

		/* Array mit mindestens 1 Text-Element, egal, ob slidersEnd gefunden oder
		nicht. */
		$endedTextBlocks = preg_split($regexEnd, $theText);

		// Finde falsch platzierte Eingaben von slidersEnd.
		foreach ($endedTextBlocks as $i => $endedText)
		{
			if (!trim($endedText))
			{
				unset($endedTextBlocks[$i]);
			}
		}

		// Now find embedded slides markers in each block.
		foreach ($endedTextBlocks as $i => $endedText)
		{
			preg_match_all($regex, $endedText, $matches, PREG_SET_ORDER);
			$text = preg_split($regex, $endedText);

			/* Teil vor erstem slide, der aber ggf. auch leer sein kann,
			falls erstes regex ganz am Anfang im Beitrag. */
			$collector[] = $text[0];

			// Es wurden weitere Panel-Regexe gefunden. Dann Accordion aufbauen
			if (count($text) > 1)
			{
				$selector = $dataParent = 'pagebreakghsvs' . $id . '_' . $i;

				if (!$print && !$isRobot)
				{
					$collector[] = HTMLHelper::_(
						'bootstrap.startAccordion',
						$selector,
						[
							/* Damit nur 1 Slide geöffnet werden kann, wird ein parent gesetzt!
							Wenn multiSelectable=0 => parent=TRUE */
							'parent' => !$pluginParams->get('multiSelectable', 0),
							]
					);
				}

				// Panels.
				foreach ($text as $key => $subtext)
				{
					// Ignore $matches[0]. Already collected above.
					if ($key)
					{
						$match = $matches[$key - 1];
						$match = (array) Utility::parseAttributes($match[2]);
						$title = $title2 = $class = '';
						$title = htmlspecialchars_decode($match['title'], ENT_COMPAT);
						$title = htmlspecialchars($title, ENT_COMPAT, 'utf-8');

						// PHP 8
						if (isset($match['title2'])) {
							$title2 = htmlspecialchars_decode($match['title2'], ENT_COMPAT);
							$title2 = htmlspecialchars($title2, ENT_COMPAT, 'utf-8');
						}

						$href = $selector . '_' . $key;

						if (!$print && !$isRobot)
						{
							$collector[] = HTMLHelper::_(
								'bootstrap.addSlide',
								$selector,
								$title,
								$href,
								$class,
								$toggleContainer,
								$title2
							);
						}

						$collector[] = '<' . $headingTagGhsvs
							. ' class="headAutoByPagebrekghsvs">'
							. $title . ($title2 ? ' - ' . $title2 : '')
							. '</' . $headingTagGhsvs . '>';

						$collector[] = $subtext;

						if (!$print && !$isRobot)
						{
							$collector[] = HTMLHelper::_('bootstrap.endSlide');
						}
						$collector[] = "\n<!--endSlide-->\n";
					}
				}

				if (!$print && !$isRobot)
				{
					$collector[] = HTMLHelper::_('bootstrap.endAccordion');

					// Aktive IDs in Session schreiben mit Ajax-Plugin.
					if ($pluginParams->get('activeToSession', 0) === 1)
					{
						$collector[] = HTMLHelper::_('bs3ghsvs.activeToSession', $dataParent);
					}
				}

				$collector[] = "\n<!--endAccordion-->\n";
			}
		}

		if ($collector)
		{
			$theText = implode('', $collector);
		}
	}
}
