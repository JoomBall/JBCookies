<?php
/**
 * @package			Joomla.Site
 * @subpackage		Modules - mod_jbcookies
 * 
 * @author			JoomBall! Project
 * @link			http://www.joomball.com
 * @copyright		Copyright © 2011-2014 JoomBall! Project. All Rights Reserved.
 * @license			GNU/GPL, http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

if (isset($_COOKIE['jbcookies'])) :
	return ;
else :
	// Include jQuery
	JHtml::_('jquery.framework');
	
	// --> Afegeixo un arxiu d'estil
	JHTML::stylesheet('modules/mod_jbcookies/assets/css/jbcookies.css');
	
	$color_background = $params->get('color_background', 'black');
	$color_links = $params->get('color_links', 'blue');
	$position = $params->get('position', 'bottom');
	$show_info = $params->get('show_info', 1);
	$modal_framework = $params->get('modal', 'bootstrap');
	$show_article_modal = $params->get('show_article_modal', 1);
	$moduleclass_sfx	= htmlspecialchars($params->get('moduleclass_sfx'));
	
	$lang = JFactory::getLanguage();
	$currentLang = $lang->getTag();
	$langs = $params->get('lang');
	
	$title			= $langs->$currentLang->title ? $langs->$currentLang->title : JText::_('MOD_JBCOOKIES_LANG_TITLE_DEFAULT');
	$text			= $langs->$currentLang->text ? $langs->$currentLang->text : JText::_('MOD_JBCOOKIES_LANG_TEXT_DEFAULT');
	$header			= $langs->$currentLang->header ? $langs->$currentLang->header : JText::_('MOD_JBCOOKIES_LANG_HEADER_DEFAULT');
	$body			= $langs->$currentLang->body ? $langs->$currentLang->body : JText::_('MOD_JBCOOKIES_LANG_BODY_DEFAULT');
	$aliasButton	= $langs->$currentLang->alias_button ? $langs->$currentLang->alias_button : JText::_('MOD_JBCOOKIES_GLOBAL_ACCEPT');
	$aliasLink		= $langs->$currentLang->alias_link ? $langs->$currentLang->alias_link : JText::_('MOD_JBCOOKIES_GLOBAL_MORE_INFO');
	$aLink			= $langs->$currentLang->alink;

	if ($aLink) {
		if ($show_article_modal) {
			// Join Model
			JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_content/models', 'ContentModel');
		
			// Get an instance of the generic articles model
			$model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));
	
			$model->setState('filter.published', 1);
			
			$app = JFactory::getApplication('site');
	
			// Load the parameters.
			$params = $app->getParams();
			$model->setState('params', $params);
	
			// Filter by id
			$model->setState('article.id', (int) $aLink);
	
			// Retrieve Content
			$item = $model->getItem();
			
			if ($item->params->get('show_intro', '1') == '1')
			{
				$item->text = $item->introtext . ' ' . $item->fulltext;
			}
			elseif ($item->fulltext)
			{
				$item->text = $item->fulltext;
			}
			else
			{
				$item->text = $item->introtext;
			}
			
			$aLink = 0;
			$show_info = 1;
			$header = $item->title;
			$body = $item->text;
			
			if ($modal_framework == 'bootstrap') {
				// Load the modal behavior script.
				JHtml::_('behavior.modal', 'a.jbcookies');
			}
		} else {
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			
			$query->select('a.id, a.alias, a.catid');
			$query->from('#__content AS a');
			
			// Join on category table.
			$query->select('c.alias AS category_alias')
				->join('LEFT', '#__categories AS c on c.id = a.catid');
				
			// Join over the categories to get parent category titles
			$query->select('parent.id as parent_id, parent.alias as parent_alias')
				->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');
				
			$query->where('a.id = '.(int) $aLink);
			// Get the options.
			$db->setQuery((string)$query);
			$item = $db->loadObject();
			
			// Add router helpers.
			$item->slug			= $item->alias ? ($item->id.':'.$item->alias) : $item->id;
			$item->catslug		= $item->category_alias ? ($item->catid.':'.$item->category_alias) : $item->catid;
			$item->parent_slug	= $item->parent_alias ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;
		
			// No link for ROOT category
			if ($item->parent_alias == 'root')
			{
				$item->parent_slug = null;
			}
		
			require_once JPATH_BASE.'/components/com_content/helpers/route.php';
			
			// TODO: Change based on shownoauth
			$item->readmore_link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
		}
	} else {
		if ($modal_framework == 'bootstrap') {
			// Load the modal behavior script.
			JHtml::_('behavior.modal', 'a.jbcookies');
		}
	}
	
	
	
//echo '<pre>';
//	print_r($item);
//echo '</pre>';
	
	require JModuleHelper::getLayoutPath('mod_jbcookies',$params->get('layout', 'default'));
	
	if(isset($_POST['set_cookie'])):
		if($_POST['set_cookie']==1)
			setcookie("jbcookies", "yes", time()+3600*24*365, "/");
	endif; ?>
	
	<script type="text/javascript">
	    jQuery(document).ready(function () { 
		
			function setCookie(c_name,value,exdays)
			{
				var exdate=new Date();
				exdate.setDate(exdate.getDate() + exdays);
				var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString()) + "; path=/";
				document.cookie=c_name + "=" + c_value;
			}
			
			function readCookie(name) {
				var nameEQ = name + "=";
				var jb = document.cookie.split(';');
				for(var i=0;i < jb.length;i++) {
					var c = jb[i];
					while (c.charAt(0)==' ') c = c.substring(1,c.length);
						if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
					}
				return null;
			}
		    
			var $jb_cookie = jQuery('.jb.cookie');
			var $jb_infoaccept = jQuery('.jb.accept');
			var jbcookies = readCookie('jbcookies');
	
			if(!(jbcookies == "yes")){
			
				$jb_cookie.delay(1000).slideDown('fast'); 
	
				$jb_infoaccept.click(function(){
					setCookie("jbcookies","yes",365);
					jQuery.post('<?php echo JURI::current(); ?>', 'set_cookie=1', function(){});
					$jb_cookie.slideUp('slow');
				});
			} 
	    });
	</script>

<?php endif ?>