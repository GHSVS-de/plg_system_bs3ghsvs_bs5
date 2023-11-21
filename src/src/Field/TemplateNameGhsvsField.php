<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GHSVS\Plugin\System\Bs3Ghsvs\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use GHSVS\Plugin\System\Bs3Ghsvs\Helper\Bs3GhsvsTemplateHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Template Name field.
 *
 * @since  3.5
 */
class TemplateNameGhsvsField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.5
     */
    protected $type = 'TemplateNameGhsvs';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   1.6
     */
    public function getOptions()
    {
        $clientId = 0;

        // Get the templates for the selected client_id.
        $options = Bs3GhsvsTemplateHelper::getTemplateOptions($clientId);
        return array_merge(parent::getOptions(), $options);
    }
}
