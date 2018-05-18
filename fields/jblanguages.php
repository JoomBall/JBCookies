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

jimport('joomla.form.formfield');

class JFormFieldJBLanguages extends JFormField {

    protected $type = 'JBLanguages';

    public function getLabel() {
        return;
    }

    protected function getInput() {
		defined('JPATH_JBCOOKIES') or define('JPATH_JBCOOKIES', JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_jbcookies');

		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);
		
		// Load language
		JFactory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);
		
		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal');
		
    	// Translate placeholder text
		$hintTitle			= ' placeholder="' . JText::_('MOD_JBCOOKIES_LANG_TITLE') . '"';
		$hintText			= ' placeholder="' . JText::_('MOD_JBCOOKIES_LANG_TEXT') . '"';
		$hintHeader			= ' placeholder="' . JText::_('MOD_JBCOOKIES_LANG_HEADER') . '"';
		$hintBody			= ' placeholder="' . JText::_('MOD_JBCOOKIES_LANG_BODY') . '"';
		$hintAliasButton	= ' placeholder="' . JText::_('MOD_JBCOOKIES_LANG_ALIAS_BUTTON') . '"';
		$hintAliasLink		= ' placeholder="' . JText::_('MOD_JBCOOKIES_LANG_ALIAS_LINK') . '"';
		$hintAlink			= ' placeholder="' . JText::_('MOD_JBCOOKIES_LANG_ALINK') . '"';		

		// Initialize some field attributes.
		$class        = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$columns      = $this->columns ? ' cols="' . $this->columns . '"' : '';
		$rows         = $this->rows ? ' rows="' . $this->rows . '"' : '';
		
		$lang = JFactory::getLanguage();
		$current_lang = $lang->getTag();
		$langs = $lang->getKnownLanguages(JPATH_BASE);
		
		$jinput = JFactory::getApplication()->input;
		$id = $jinput->getInt('id');
		
		$db		= JFactory::getDbo();
		
		$query	= $db->getQuery(true);
		$query->select('m.params');
		$query->from('#__modules AS m');
		$query->where('m.id = '.(int) $id);
		// Get the options.
		$db->setQuery((string)$query);
		$paramsModule = $db->loadResult();
		
		// Convert the params field to a string.
		$parameter = new JRegistry;
		$parameter->loadString($paramsModule);
		$paramsModule = $parameter;
					
		$params = $paramsModule->get('lang', new JObject());
		$params = JArrayHelper::fromObject($params);
		
		$html = '';
		
		foreach ($langs AS $lang) {
			
			
			$article	= array();
			$ini = JPATH_JBCOOKIES.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.$lang['tag'].DIRECTORY_SEPARATOR.$lang['tag'].'.mod_jbcookies.ini'; 
			
			if (empty($params[$lang['tag']])) {
				$params[$lang['tag']]['title']			= '';
				$params[$lang['tag']]['text']			= '';
				$params[$lang['tag']]['header']			= '';
				$params[$lang['tag']]['body']			= '';
				$params[$lang['tag']]['alias_button']	= '';
				$params[$lang['tag']]['alias_link']		= '';
				$params[$lang['tag']]['alink']			= '';
			}
			
			// Article
			$aLink	= strtolower(str_replace('-', '_', '_' . $lang['tag']));
			$link	= 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle_' . $this->id . $aLink;
		
			
			// Build the script.
			$script = array();
	
			// Select button script
			$script[] = '	function jSelectArticle_' . $this->id . $aLink . '(id, title, catid, object) {';
			$script[] = '		document.getElementById("' . $this->id . $aLink . '_id").value = id;';
			$script[] = '		document.getElementById("' . $this->id . $aLink . '_name").value = title;';
	
			$script[] = '		jQuery("#' . $this->id . $aLink . '_clear").removeClass("hidden");';
	
			$script[] = '		SqueezeBox.close();';
			$script[] = '	}';
	
			// Clear button script
			static $scriptClear;
	
				$scriptClear = true;
	
				$script[] = '	function jClearArticle(id) {';
				$script[] = '		document.getElementById(id + "_id").value = "";';
				$script[] = '		document.getElementById(id + "_name").value = "' . htmlspecialchars(JText::_('COM_CONTENT_SELECT_AN_ARTICLE', true), ENT_COMPAT, 'UTF-8') . '";';
				$script[] = '		jQuery("#"+id + "_clear").addClass("hidden");';
				$script[] = '		if (document.getElementById(id + "_edit")) {';
				$script[] = '			jQuery("#"+id + "_edit").addClass("hidden");';
				$script[] = '		}';
				$script[] = '		return false;';
				$script[] = '	}';
	
			// Add the script to the document head.
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
			
			$title = '';
			
			if ((int) $params[$lang['tag']]['alink'] > 0)
			{
				$db	= JFactory::getDbo();
				$query = $db->getQuery(true)
					->select($db->quoteName('title'))
					->from($db->quoteName('#__content'))
					->where($db->quoteName('id') . ' = ' . (int) $params[$lang['tag']]['alink']);
				$db->setQuery($query);
	
				try
				{
					$title = $db->loadResult();
				}
				catch (RuntimeException $e)
				{
					JError::raiseWarning(500, $e->getMessage());
				}
			}
	
			if (empty($title))
			{
				$title = JText::_('COM_CONTENT_SELECT_AN_ARTICLE');
			}
			$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
			
			// The active article id field.
			if (0 == (int) $params[$lang['tag']]['alink'])
			{
				$value = '';
			}
			else
			{
				$value = (int) $params[$lang['tag']]['alink'];
			}

			$html .= '<h2>' . $lang['name'] . '</h2>';

			if (is_file($ini)) {
				$html .= '<div class="alert alert-info">';
				$html .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
				$html .= JText::_('MOD_JBCOOKIES_MSG_LANG_INFO');
				$html .= '</div>';
			} else {
				$html .= '<div class="alert alert-block">';
				$html .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
				$html .= JText::_('MOD_JBCOOKIES_MSG_LANG_BLOCK');
				$html .= '</div>';
			}
			
			$html .= '<div class="row-fluid">';
			
				$html .= '<div class="span12">';
				
					// Title
					$html .= '<div class="span3">';
						$html .= '<div class="control-group">';
							$html .= '<textarea name="' . $this->name . '['.$lang['tag'].'][title]' . '" id="' . $this->id . '_' . $lang['tag'] . '"' . $columns . $rows . $class . $hintTitle . ' >'
							. htmlspecialchars($params[$lang['tag']]['title'], ENT_COMPAT, 'UTF-8') . '</textarea>';
						$html .= '</div>';
					$html .= '</div>';
					// Text
					$html .= '<div class="span3">';
						$html .= '<div class="control-group">';
							$html .= '<textarea name="' . $this->name . '['.$lang['tag'].'][text]' . '" id="' . $this->id . '_' . $lang['tag'] . '"' . $columns . $rows . $class	. $hintText . ' >'
							. htmlspecialchars($params[$lang['tag']]['text'], ENT_COMPAT, 'UTF-8') . '</textarea>';
						$html .= '</div>';
					$html .= '</div>';
					// Header Modal
					$html .= '<div class="span3">';
						$html .= '<div class="control-group">';
							$html .= '<input type="text" name="' . $this->name . '['.$lang['tag'].'][header]' . '" id="' . $this->id . '_' . $lang['tag'] . '" value="' . htmlspecialchars($params[$lang['tag']]['header'], ENT_COMPAT, 'UTF-8') . '"' . $class . $hintHeader . ' >';
						$html .= '</div>';
					$html .= '</div>';
					// Body Modal
					$html .= '<div class="span3">';
						$html .= '<div class="control-group">';
							$html .= '<textarea name="' . $this->name . '['.$lang['tag'].'][body]' . '" id="' . $this->id . '_' . $lang['tag'] . '"' . $columns . $rows . $class	. $hintBody . ' >'
							. htmlspecialchars($params[$lang['tag']]['body'], ENT_COMPAT, 'UTF-8') . '</textarea>';
						$html .= '</div>';
					$html .= '</div>';
					
				$html .= '</div>';
				
			$html .= '</div>';
			
			$html .= '<div class="row-fluid">';
			
				// Alias Button
				$html .= '<div class="span3">';
					$html .= '<div class="control-group">';
						$html .= '<input type="text" name="' . $this->name . '['.$lang['tag'].'][alias_button]' . '" id="' . $this->id . '_' . $lang['tag'] . '" value="' . htmlspecialchars($params[$lang['tag']]['alias_button'], ENT_COMPAT, 'UTF-8') . '"' . $class . $hintAliasButton . ' >';
					$html .= '</div>';
				$html .= '</div>';
				// Alias Link
				$html .= '<div class="span3">';
					$html .= '<div class="control-group">';
						$html .= '<input type="text" name="' . $this->name . '['.$lang['tag'].'][alias_link]' . '" id="' . $this->id . '_' . $lang['tag'] . '" value="' . htmlspecialchars($params[$lang['tag']]['alias_link'], ENT_COMPAT, 'UTF-8') . '"' . $class . $hintAliasLink . ' >';
					$html .= '</div>';
				$html .= '</div>';
				// Article Link
				$html .= '<div class="span3">';
					$html .= '<div class="control-group">';
						// The current article display field.
						$article[] = '<span class="input-append">';
						$article[] = '<input type="text" class="input-medium" id="' . $this->id . $aLink . '_name" value="' . $title . '" disabled="disabled" size="35" />';
						$article[] = '<a class="modal btn hasTooltip" title="' . JHtml::tooltipText('COM_CONTENT_CHANGE_ARTICLE') . '"  href="' . $link . '&amp;' . JSession::getFormToken() . '=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . JText::_('JSELECT') . '</a>';
				
						// Clear article button
						$article[] = '<button id="' . $this->id . $aLink . '_clear" class="btn' . ($value ? '' : ' hidden') . '" onclick="return jClearArticle(\'' . $this->id . $aLink . '\')"><span class="icon-remove"></span> ' . JText::_('JCLEAR') . '</button>';
				
						$article[] = '</span>';
				
						$article[] = '<input type="hidden" id="' . $this->id . $aLink . '_id"' . ' name="' . $this->name . '['.$lang['tag'].'][alink]' . '" value="' . $value . '" />';
				
						$html .= implode("\n", $article);
					$html .= '</div>';
				$html .= '</div>';
				
			$html .= '</div>';
			
			$html .= '<hr>';
		
		}
		
		echo $html;
		
		return;
	}
}