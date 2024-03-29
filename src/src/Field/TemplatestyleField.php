<?php
/*
Zeigt/Lädt auch Stile von nicht aktivierten Template-Erweiterungen.
*/

namespace GHSVS\Plugin\System\Bs3Ghsvs\Field;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;
use Joomla\CMS\Form\Field\GroupedlistField;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports a select grouped list of template styles
 *
 * @since  1.6
 */
class TemplatestyleField extends GroupedlistField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    public $type = 'TemplateStyle';

    /**
     * The client name.
     *
     * @var    mixed
     * @since  3.2
     */
    protected $clientName;

    /**
     * The template.
     *
     * @var    mixed
     * @since  3.2
     */
    protected $template;

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.2
     */
    public function __get($name)
    {
        switch ($name) {
            case 'clientName':
            case 'template':
                return $this->$name;
        }

        return parent::__get($name);
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to set the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'clientName':
            case 'template':
                $this->$name = (string) $value;
                break;

            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   3.2
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if ($result === true) {
            // Get the clientName template.
            $this->clientName = $this->element['client'] ? (string) $this->element['client'] : 'site';
            $this->template   = (string) $this->element['template'];
        }

        return $result;
    }

    /**
     * Method to get the list of template style options grouped by template.
     * Use the client attribute to specify a specific client.
     * Use the template attribute to specify a specific template
     *
     * @return  array[]  The field option objects as a nested array in groups.
     *
     * @since   1.6
     */
    protected function getGroups()
    {
        $groups = [];
        $lang   = Factory::getLanguage();

        // Get the client and client_id.
        $client = ApplicationHelper::getClientInfo($this->clientName, true);

        // Get the template.
        $template = $this->template;

        // Get the database object and a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Build the query.
        $query->select(
            [
                $db->quoteName('s.id'),
                $db->quoteName('s.title'),
                $db->quoteName('e.name'),
                $db->quoteName('s.template'),
            ]
        )
            ->from($db->quoteName('#__template_styles', 's'))
            ->where($db->quoteName('s.client_id') . ' = :clientId')
            ->bind(':clientId', $client->id, ParameterType::INTEGER)
            ->order(
                [
                    $db->quoteName('template'),
                    $db->quoteName('title'),
                ]
            );

        if ($template) {
            $query->where('s.template = ' . $db->quote($template));
        }

        $query->join('LEFT', '#__extensions as e on e.element=s.template')
            // ->where('e.enabled = 1')
            ->where($db->quoteName('e.type') . ' = ' . $db->quote('template'));

        // Set the query and load the styles.
        $db->setQuery($query);
        $styles = $db->loadObjectList();

        // Build the grouped list array.
        if ($styles) {
            foreach ($styles as $style) {
                $template = $style->template;
                $lang->load('tpl_' . $template . '.sys', $client->path)
                    || $lang->load('tpl_' . $template . '.sys', $client->path . '/templates/' . $template);
                $name = Text::_($style->name);

                // Initialize the group if necessary.
                if (!isset($groups[$name])) {
                    $groups[$name] = [];
                }

                $groups[$name][] = HTMLHelper::_('select.option', $style->id, $style->title . 'pups');
            }
        }

        // Merge any additional groups in the XML definition.
        $groups = array_merge(parent::getGroups(), $groups);

        return $groups;
    }
}
