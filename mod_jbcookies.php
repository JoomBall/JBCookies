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

if (!empty($_COOKIE['jbcookies'])) : ?>
	<?php if (!$params->get('show_decline', 1)) { return; } ?>
	<?php
	$lang				= JFactory::getLanguage();
	$currentLang		= $lang->getTag();
	$langs				= $params->get('lang');
	$text_decline		= !empty($langs->$currentLang->text_decline) ? $langs->$currentLang->text_decline : JText::_('MOD_JBCOOKIES_LANG_TITLE_DEFAULT');
	$aliasButton_decline= !empty($langs->$currentLang->alias_button_decline) ? $langs->$currentLang->alias_button_decline : JText::_('MOD_JBCOOKIES_GLOBAL_DECLINE');
	$color_links_decline= $params->get('decline_btn_link_color', '#37a4fc');
	$moduleclass_sfx	= htmlspecialchars($params->get('moduleclass_sfx'));
	
	$params->set('view', 'default_decline'); // Modify view
	require JModuleHelper::getLayoutPath('mod_jbcookies', 'default'); ?>
	<?php JHtml::_('jquery.framework'); ?>
	<script type="text/javascript">
		jQuery(document).ready(function () {
			function setCookie(c_name,value,exdays)
			{
				var exdate=new Date();
				exdate.setDate(exdate.getDate() + exdays);
				var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString()) + "; path=/";
				document.cookie=c_name + "=" + c_value;
			}
			jQuery('.jb.decline').click(function(){
				setCookie("jbcookies","",0);
				window.location.reload();
			});
		});
	</script>
<?php else :
	// Include jQuery
	JHtml::_('jquery.framework');

	// --> Afegeixo un arxiu d'estil
	JHtml::_('stylesheet', 'modules/mod_jbcookies/assets/css/jbcookies.css', array('version' => 'auto'));
	
	if ($params->get('color_option', 'selectable') == 'selectable') :
		$color_background = $params->get('color_background', 'black');
		$color_links = $params->get('color_links', 'blue');
	else :
		$params->set('view', 'default_custom'); // Modify view
	
		$color_background = $params->get('color_background_custom', '#000000');
		$color_links = $params->get('color_links_custom', '#37a4fc');
		$color_text = $params->get('color_text_custom', '#ffffff');
		$btn_border_color = $params->get('btn_border_color_custom', '#024175');
		$btn_text_color = $params->get('btn_text_color_custom', '#ffffff');
		$btn_start_color = $params->get('btn_start_bgcolor_custom', '#37a4fc');
		$btn_end_color = $params->get('btn_end_bgcolor_custom', '#025fab');
		$btn_width = (int) $params->get('btn_width_custom', '100');
		$btn_height = (int) $params->get('btn_height_custom', '30');
	endif;
	
	$position = $params->get('position', 'bottom');
	$show_info = $params->get('show_info', 1);
	$modal_framework = $params->get('modal', 'bootstrap');
	$framework_version = ($params->get('bootstrap_version', 2) == 2) ? 0 : 1;
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
	$text_decline		= !empty($langs->$currentLang->text_decline) ? $langs->$currentLang->text_decline : JText::_('MOD_JBCOOKIES_LANG_TITLE_DEFAULT');
	$aliasButton_decline= !empty($langs->$currentLang->alias_button_decline) ? $langs->$currentLang->alias_button_decline : JText::_('MOD_JBCOOKIES_GLOBAL_DECLINE');
	$color_links_decline= $params->get('decline_btn_link_color', '#37a4fc');
	
	if ($show_info && $aLink) {
		if ($show_article_modal) {
			// Join Model
			JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_content/models', 'ContentModel');
		
			// Get an instance of the generic articles model
			$model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));
			$model->setState('filter.published', 1);
			
			$app = JFactory::getApplication('site');
	
			// Load the parameters.
			$paramsApp = $app->getParams();
			$model->setState('params', $paramsApp);
	
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
			
			if ($modal_framework == 'bootstrap' && !$framework_version) { // Bootstrap 2.3.2
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
			
			$item->readmore_link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
		}
	} else {
		if ($modal_framework == 'bootstrap' && !$framework_version) { // Bootstrap 2.3.2
			// Load the modal behavior script.
			JHtml::_('behavior.modal', 'a.jbcookies');
		}
	}
	
	require JModuleHelper::getLayoutPath('mod_jbcookies', $params->get('layout', 'default'));
?>
	
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
					setCookie("jbcookies","yes",<?php echo $params->get('expire', 1); ?>);
					$jb_cookie.slideUp('slow');
					jQuery('.jb.cookie-decline').fadeIn('slow', function() {});
				});
			}

			jQuery('.jb.decline').click(function(){
				setCookie("jbcookies","",0);
				window.location.reload();
			});
	    });
	</script>

<?php endif ?>