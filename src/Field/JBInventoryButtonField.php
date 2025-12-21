<?php
/**
 * @package			Joomla.Site
 * @subpackage		Modules - mod_jbcookies
 * 
 * @author			JoomBall! Project
 * @link			http://www.joomball.com
 * @copyright		Copyright © 2011-2026 JoomBall! Project. All Rights Reserved.
 * @license			GNU/GPL, http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace JB\Module\JBCookies\Site\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

class JBInventoryButtonField extends FormField
{
    protected $type = 'JBInventoryButton';

    protected function getInput()
    {
        $app = Factory::getApplication();
        $token = Session::getFormToken();
        $siteRoot = Uri::root();

        // Detect if language filter plugin is enabled and get default language prefix
        $langPrefix = '';
        $plugins = \Joomla\CMS\Plugin\PluginHelper::getPlugin('system', 'languagefilter');
        if (!empty($plugins))
        {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('lang_code')
                ->from($db->quoteName('#__languages'))
                ->where($db->quoteName('published') . ' = 1')
                ->order($db->quoteName('ordering') . ' ASC');
            $db->setQuery($query, 0, 1);
            $defaultLang = $db->loadResult();
            
            if ($defaultLang)
            {
                $langParts = explode('-', $defaultLang);
                $langPrefix = strtolower($langParts[0]) . '/';
            }
        }

        // Build URL: if language prefix exists, omit index.php for proper routing
        if ($langPrefix)
        {
            $url = $siteRoot . $langPrefix . '?option=com_ajax&module=jbcookies&method=inventory&format=json&mode=scan&' . $token . '=1';
        }
        else
        {
            $url = $siteRoot . 'index.php?option=com_ajax&module=jbcookies&method=inventory&format=json&mode=scan&' . $token . '=1';
        }
        $label = Text::_($this->element['buttontext'] ?? 'MOD_JBCOOKIES_FIELD_INVENTORY_BUTTON_TEXT');
        
        $wa = Factory::getDocument()->getWebAssetManager();

        static $inventoryCssApplied = false;

        if (!$inventoryCssApplied)
        {
            $wa->addInlineStyle('.form-vertical div.subform-repeatable-group[data-base-name="cookie_prefs"] > .btn-toolbar + .control-group{flex-direction:inherit;}');
            $inventoryCssApplied = true;
        }

        if (!$wa->assetExists('script', 'mod_jbcookies.inventory'))
        {
            $wa->registerAndUseScript('mod_jbcookies.inventory', 'media/mod_jbcookies/js/inventory.min.js', [], ['defer' => true]);
        }
        else
        {
            $wa->useScript('mod_jbcookies.inventory');
        }

        $html  = '<div class="jb-inventory-scan">';
        $html .= '<button type="button" class="btn btn-outline-primary" data-jbcookies-inventory-button="1" data-url="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" data-site-root="' . htmlspecialchars($siteRoot, ENT_QUOTES, 'UTF-8') . '" data-lang-prefix="' . htmlspecialchars($langPrefix, ENT_QUOTES, 'UTF-8') . '">' . $label . '</button>';

        $html .= '</div>';

        return $html;
    }
}