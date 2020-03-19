<?php
/**
 * @package			Joomla.Site
 * @subpackage		Modules - mod_jbcookies
 *
 * @author			JoomBall! Project
 * @link			http://www.joomball.com
 * @copyright		Copyright © 2011-2018 JoomBall! Project. All Rights Reserved.
 * @license			GNU/GPL, http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class modJBCookiesHelper
{
	public static function getAjax()
	{
		$app = JFactory::getApplication();
		$extensions = $app->input->get('extensions', '', 'string');
		$conf = JFactory::getConfig();
		
		$options = array(
				'defaultgroup' => '',
				'storage'      => $conf->get('cache_handler', ''),
				'caching'      => true,
				'cachebase'    => $conf->get('cache_path', JPATH_SITE . '/cache')
		);
		
		$cache = JCache::getInstance('', $options);
		
		foreach (explode(',', $extensions) as $ext) {
			$cache->clean($ext);
		}
	}
}
?>
