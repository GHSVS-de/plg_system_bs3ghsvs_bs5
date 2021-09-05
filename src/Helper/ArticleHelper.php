<?php
defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\Component\Content\Site\Helper\RouteHelper;

abstract class Bs3ghsvsArticle
{
	protected static $loaded = array();

	/**
	 * Get additional article data from #__bs3ghsvs_article.
	 *
	 * @param integer $articleId Value for db column article_id
	 * @param array $keys Values for db column key. If empty request all.
	 * @return array of arrays | boolean false
	*/
	public static function getExtraFields(int $articleId, array $keys = array(), bool $asArray = false)
	{
		if (!$articleId)
		{
			return false;
		}

		$keys = \array_flip($keys);
		ksort($keys);

		$sig = md5(serialize(array($articleId, $keys, $asArray)));

		if (isset(static::$loaded[__METHOD__][$sig]))
		{
			return static::$loaded[__METHOD__][$sig];
		}

		$prefix = 'article';
		$activeXml = Bs3GhsvsFormHelper::getActiveXml(
			$prefix,
			PlgSystemBS3Ghsvs::getPluginParams(),
			array(1) // stati
		);

		if (!$activeXml)
		{
			return false;
		}

		// Get the relevant values for column "key" in db table.
		foreach ($activeXml as $key => $status)
		{
			$keyValue = strtolower(substr_replace($key, '', 0, strlen($prefix)));

			if ($keys && !isset($keys[$keyValue]))
			{
				unset($activeXml[$key]);
				continue;
			}

			$activeXml[$key] = $keyValue;
		}

		if (!$activeXml)
		{
			return false;
		}

		// Load the article extra fields from the database.
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn(array('key', 'value')))
			->from($db->qn('#__bs3ghsvs_article'))
			->where($db->qn('article_id') . ' = ' . $articleId)
			->where($db->qn('key') . ' IN('
				. implode(', ', $db->q($activeXml)) . ')')
		;
		$result = $db->setQuery($query)->loadObjectList('key');

		if (!$result)
		{
			return false;
		}

		foreach ($result as $key => $value)
		{
			$result[$key] = json_decode($value->value, true);

			if (json_last_error() !== JSON_ERROR_NONE)
			{
				$result[$key] = $value->value;
			}
		}

		if ($asArray === false)
		{
			$result = new Registry($result);
		}
		static::$loaded[__METHOD__][$sig] = $result;

		return $result;
	}

	/** Load articles that have an entry in table #__bs3ghsvs_article with
	 *	provided $key. E.g. 'termin' or 'various.'
	 */
	public static function getArticlesWithExtraFieldType(string $key)
	{
		$dataField = 'fieldData';

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn(['a.id', 'a.title', 'introtext', 'catid', 'created',
			'a.modified', 'images', 'a.metadesc', 'a.access', 'a.language', 'ordering',
			'a.created_by', 'a.created_by_alias', 'a.metadata']))

			->select($db->qn('b.value', $dataField))
			->from($db->qn('#__bs3ghsvs_article', 'b'))
			->where($db->qn('key') . '=' . $db->q($key))

			// INNER: Nur Artikel mit Extrafeldern, (keine leeren Artikel/Extrafelder)
			->join('INNER', $db->qn('#__content', 'a') . ' ON '
				. $db->qn('b.article_id') . ' = ' . $db->qn('a.id')
			)

			->select($db->qn('c.access', 'category_access'))
			->join('LEFT', '#__categories AS c ON c.id = catid')

			->select($db->qn('ua.name', 'author'))
			->join('LEFT', $db->qn('#__users', 'ua') . ' ON '
				. $db->qn('ua.id') . ' = ' . $db->qn('a.created_by')
			)

			->order($db->qn('a.modified') . ' DESC')
			->where($db->qn('a.state') . '= 1');

		$result = $db->setQuery($query)->loadObjectList();

		if ($result)
		{
			$groups = Factory::getUser()->getAuthorisedViewLevels();

			foreach ($result as $i => $item)
			{
				$item->$dataField = new Registry($item->$dataField);

				if (
					!$item->$dataField->get('bs3ghsvs_' . $key . '_active')
					|| ! (in_array($item->access, $groups)
						&& in_array($item->category_access, $groups))
				){
					unset($result[$i]);
					continue;
				}

				$item->link = Route::_(ContentHelperRoute::getArticleRoute(
					$item->id, $item->catid, $item->language));

				if (strpos($item->metadata, '"author":""') === false)
				{
					$item->metadata = json_decode($item->metadata);

					if (!empty($item->metadata->author))
					{
						$item->author = $item->metadata->author;
					}
				}

				//$item->tags = new JHelperTags;
				//$item->tags->getItemTags('com_content.article', $item->id);
			}

			return $result;
		}

		return [];
	}

	/** Pick data of a aingle article from #__bs3ghsvs_article where key='extension'.
	*/
	public static function getExtensionData(int $articleId)
	{
		$extensionData = self::getExtraFields($articleId, ['extension']);

		if (
			!($extensionData instanceof Registry)
			|| self::isExtensionEmpty($extensionData->get('extension'))
		){
			return false;
		}

		return $extensionData->get('extension', new stdClass, 'OBJECT');
	}

	public static function getVariousData(int $articleId)
	{
		$data = self::getExtraFields($articleId, ['various']);

		if (
			!($data instanceof Registry)
			|| self::isVariousEmpty($data->get('various')))
		{
			return false;
		}

		return $data->get('various', new stdClass, 'OBJECT');
	}

	public static function getJcfieldsAsRegistry($item) : Registry
	{
		$JcfieldsAsRegistry = new Registry();

		if (!empty($item->jcfields) && is_array($item->jcfields))
		{
			$jcfields = array();

			foreach ($item->jcfields as $jcfield)
			{
				$jcfields[$jcfield->name] = $jcfield->rawvalue;
			}

			$JcfieldsAsRegistry = new Registry($jcfields);
		}

		return $JcfieldsAsRegistry;
	}

	public static function buildFlagImages(array $languages)
	{
		$flags = \array_flip($languages);

		foreach ($flags as $flag => $key)
		{
			$alt = Text::sprintf('PLG_SYSTEM_BS3GHSVS_EXTENSION_LANGUAGE_X', str_replace('_', '-', $flag));
			$flags[$flag] = HTMLHelper::_('image',
				'mod_languages/' . $flag . '.gif',
				$alt,
				array('class' => 'flag excludevenobox'),
				true
			);
		}
		return $flags;
	}

	/**
	 * Check if it's worth to go on with 'extension' datas on display actions.
	 */
	public static function isExtensionEmpty(
		$extensionData,
		array $required = array('name', 'description', 'url')
	){
		if (!is_object($extensionData))
		{
			return true;
		}

		foreach ($required as $key)
		{
			if (empty($extensionData->$key))
			{
				return true;
			}
		}
		return false;
	}
	/**
	 *
	 */
	public static function isVariousEmpty($various)
	{
		if (!is_object($various))
		{
			return true;
		}

		// Nearly impossible that this property exists AND is set to 0.
		// If it exists it should be alwys 1.
		if (empty($various->bs3ghsvs_various_active))
		{
			return true;
		}

		foreach ($various as $key => $value)
		{
			if (!empty($value))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * z.B. module-übergreifende id="..." aus JInput-Daten ermitteln.
	 * Beispiel sind modal-Buttons mit Content am Ende der Seite.
	 * $prefix muss mit einem [A-Za-z] anfangen!
	 */
	public static function buildUniqueIdFromJinput(string $prefix)
	{
		$jinput = Factory::getApplication()->input;

		$getThis = array(
			'Itemid',
			'option',
			'view',
			'catid',
			'id',
			'task'
		);

		$id = '';

		foreach ($getThis as $GetMe)
		{
			$idPart = $jinput->get($GetMe);

			// e.g. id on com_tags pages
			if (is_array($idPart))
			{
				$idPart = implode('', $idPart);
		}

			$id .= '_' . $idPart;
		}

		$id = $prefix . '_' . md5(ApplicationHelper::stringURLSafe(base64_encode($id)));
		return $id;
	}

###################################### 	 * NUR FÜR Datenbank-Migration! REMOVE LATER - START
	// if you need it See commit https://github.com/GHSVS-de/plg_system_bs3ghsvs_bs5/commit/4159c02ccafd489a80062cca9cacb49a6df449f1
###################################### REMOVE LATER - END
}
