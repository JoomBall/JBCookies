<?php
/**
 * @package     Joomla.Site
 * @subpackage  Modules - mod_jbcookies
 */

namespace JB\Module\JBCookies\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData()
    {
        $data   = parent::getLayoutData();
        $helper = $this->getHelperFactory()->getHelper('JbcookiesHelper');

        return array_merge($data, $helper->getDisplayData($data['params']));
    }
}
