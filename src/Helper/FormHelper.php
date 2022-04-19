<?php
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;

class Bs3GhsvsFormHelper
{
	protected static $loaded = [];

	public static function getActiveXml($key, $paras, $status = [1, 2])
	{
		$key = 'XmlActive' . ucfirst($key);
		$sig = $key . '_' . serialize($status);

		if (!isset(self::$loaded[$sig]))
		{
			$params = clone $paras;
			$XmlActive = $params->get($key);

			if (!is_object($XmlActive) || !count(get_object_vars($XmlActive)))
			{
				self::$loaded[$sig] = [];

				return [];
			}

			foreach ($XmlActive as $file => $active)
			{
				if (!in_array($active, $status) || strpos($file, 'xml_') !== 0)
				{
					unset($XmlActive->$file);
					continue;
				}
			}
			$XmlActive = (array) $XmlActive;

			foreach ($XmlActive as $file => $active)
			{
				unset($XmlActive[$file]);
				$file = substr($file, 4);
				$XmlActive[$file] = $active;
			}

			self::$loaded[$sig] = $XmlActive;
		}

		return self::$loaded[$sig];
	}

	/**
	 * Placeholder einsetzen statt Labels. Labels erhalten sr-only, damit Validierung noch klappt.
	 * Klasse form-control einsetzen für BS 3.
	 * In Fällen, wo ein JLayout (renderfield()) verwendet wird, das anschließend form-control setzt,
	 *  kann z.B. durch $ignore = ['form-control'] unterbunden werden, damit nicht doppelt gesetzt wird.
	 */
	public static function prepareFormFields(
		&$form,
		$hintSource = 'description',
		$overrides = [],
		$ignore = []
	) {
		$fields = $form->getGroup('');

		// Für welche Feldtypen keine Placeholder/Hints einsetzen:
		$excludeTypes  = [
		 'checkbox',
		 'radio',
		 'submit',
		 'radio',
		 'spacer',
		 'captcha',
		];

		foreach ($fields as $field)
		{
			if (in_array($field->getAttribute('type'), $excludeTypes))
			{
				continue;
			}

			if (!in_array('form-control', $ignore))
			{
				$class = $field->getAttribute('class');

				if (strpos($class, 'form-control') === false)
				{
					$form->setFieldAttribute(
						$field->fieldname,
						'class',
						$class . ' form-control findMich01',
						$field->group
					);
				}
			}

			if (
				!empty($overrides[$field->fieldname])
				&& $overrides[$field->fieldname]['group'] === $field->group
			) {
				$hint = $overrides[$field->fieldname]['str'];
			}
			elseif (!empty($field->getAttribute($hintSource)))
			{
				$hint = $field->getAttribute($hintSource);
			}
			else
			{
				$hint = $field->getAttribute('label');
			}

			if ($hint)
			{
				$hint = Text::_($hint);

				if (!$field->required)
				{
					$hint .= ' ' . Text::_('PLG_SYSTEM_BS3GHSVS_OPTIONAL');
				}
				else
				{
					$hint .= ' *';
				}

				$form->setFieldAttribute($field->fieldname, 'hint', $hint, $field->group);
				$form->setFieldAttribute($field->fieldname, 'translateHint', true, $field->group);

				if (!in_array('sr-only', $ignore))
				{
					$class = $field->getAttribute('labelclass');

					if (strpos($class, 'sr-only') === false)
					{
						$form->setFieldAttribute(
							$field->fieldname,
							'labelclass',
							$class . ' sr-only',
							$field->group
						);
					}
				}
			}
		}
	}
}
