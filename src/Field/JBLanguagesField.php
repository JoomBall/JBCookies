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

namespace JB\Module\JBCookies\Site\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Log\Log;
use RuntimeException;

class JBLanguagesField extends FormField {

    protected $type = 'JBLanguages';

    public function getLabel() {
        return;
    }

    protected function getInput() {
		defined('JPATH_JBCOOKIES') or define('JPATH_JBCOOKIES', JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_jbcookies');

		// Including fallback code for HTML5 non supported browsers.
//		JHtml::_('jquery.framework');
//		JHtml::_('script', 'system/html5fallback.js', false, true);
		
		$lang = Factory::getLanguage();
		
		// Load language
		$lang->load('com_content', JPATH_ADMINISTRATOR);
		
		// Load the modal behavior script.
//		JHtml::_('behavior.modal', 'a.modal');
		
    	// Translate placeholder text
		$hintTitle			= ' placeholder="' . Text::_('MOD_JBCOOKIES_LANG_TITLE') . '"';
		$hintText			= ' placeholder="' . Text::_('MOD_JBCOOKIES_LANG_TEXT') . '"';
		$hintHeader			= ' placeholder="' . Text::_('MOD_JBCOOKIES_LANG_HEADER') . '"';
		$hintBody			= ' placeholder="' . Text::_('MOD_JBCOOKIES_LANG_BODY') . '"';
		$hintReject		= ' placeholder="' . Text::_('MOD_JBCOOKIES_ACTION_REJECT') . '"';
		$hintAccept	= ' placeholder="' . Text::_('MOD_JBCOOKIES_GLOBAL_ACCEPT') . '"';
		// $hintAliasLink		= ' placeholder="' . Text::_('MOD_JBCOOKIES_LANG_ALIAS_LINK') . '"';
		// $hintAlink			= ' placeholder="' . Text::_('MOD_JBCOOKIES_LANG_ALINK') . '"';		

		// Initialize some field attributes.
		$class        = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$columns      = $this->columns ? ' cols="' . $this->columns . '"' : '';
		$rows         = $this->rows ? ' rows="' . $this->rows . '"' : '';
		
		$current_lang = $lang->getTag();
		$langs = LanguageHelper::getKnownLanguages(JPATH_BASE);
		
//		unset($langs['en-GB']);
//		echo '<pre>'; print_r($langs); echo '</pre>'; //exit;
		
		$jinput = Factory::getApplication()->input;
		$id = $jinput->getInt('id');
		
		$db		= Factory::getDbo();
		
		$query	= $db->getQuery(true);
		$query->select('m.params');
		$query->from('#__modules AS m');
		$query->where('m.id = '.(int) $id);
		// Get the options.
		$db->setQuery((string)$query);
		$paramsModule = $db->loadResult();
		
		// Convert the params field to a string.
		$parameter = new Registry($paramsModule);
		$paramsModule = $parameter;
					
		$params = $paramsModule->get('lang', new \stdClass());
		$params = ArrayHelper::fromObject($params);
		
		$modalTitle    = Text::_('COM_CONTENT_SELECT_AN_ARTICLE');
		$html = '';
		
		foreach ($langs AS $lang) {
			
			
			$article	= array();
			$ini = JPATH_JBCOOKIES.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.$lang['tag'].DIRECTORY_SEPARATOR.'mod_jbcookies.ini'; 
			
			if (empty($params[$lang['tag']])) {
				$params[$lang['tag']]['title']			= '';
				$params[$lang['tag']]['text']			= '';
				$params[$lang['tag']]['header']			= '';
				$params[$lang['tag']]['body']			= '';
				$params[$lang['tag']]['reject']	= '';
				$params[$lang['tag']]['accept']	= '';
				// $params[$lang['tag']]['alias_link']		= '';
				// $params[$lang['tag']]['alink']			= '';
			} else {
				$params[$lang['tag']]['title']			= $params[$lang['tag']]['title'] ?? '';
				$params[$lang['tag']]['text']			= $params[$lang['tag']]['text'] ?? '';
				$params[$lang['tag']]['header']			= $params[$lang['tag']]['header'] ?? '';
				$params[$lang['tag']]['body']			= $params[$lang['tag']]['body'] ?? '';
				$params[$lang['tag']]['reject']	= $params[$lang['tag']]['reject'] ?? '';
				$params[$lang['tag']]['accept']	= $params[$lang['tag']]['accept'] ?? '';
				// $params[$lang['tag']]['alias_link']		= $params[$lang['tag']]['alias_link'] ?? '';
				// $params[$lang['tag']]['alink']			= $params[$lang['tag']]['alink'] ?? '';
			}
			
			// Article
			$aLink	= strtolower(str_replace('-', '_', '_' . $lang['tag']));
			$urlSelect	= 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;&amp;' . Session::getFormToken() . '=1&amp;function=jSelectArticle_' . $this->id . $aLink;
			
			$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
			
			// Add the modal field script to the document head.
			$wa->useScript('field.modal-fields');
			
			static $scriptSelect = null;
			
			if (is_null($scriptSelect))
			{
				$scriptSelect = array();
			}
			
			if (!isset($scriptSelect[$this->id . $aLink]))
			{
				$wa->addInlineScript("
				window.jSelectArticle_" . $this->id . $aLink . " = function (id, title, catid, object, url, language) {
					window.processModalSelect('Article', '" . $this->id . $aLink . "', id, title, catid, object, url, language);
				}",
						[],
						['type' => 'module']
						);
			
				Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');
			
				$scriptSelect[$this->id . $aLink] = true;
			}
			
			$title = '';
			
			if ((int) $params[$lang['tag']]['alink'] > 0)
			{
				$db	= Factory::getDbo();
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
					Log::add($e->getMessage(), Log::WARNING, 'jerror');
				}
			}
	
			if (empty($title))
			{
				$title = Text::_('COM_CONTENT_SELECT_AN_ARTICLE');
			}
			
			// $title = htmlspecialchars($title, ENT_COMPAT, 'UTF-8');
			
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
				$html .= '<div class="alert alert-info alert-dismissible" role="alert">';
//				$html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
				$html .= Text::_('MOD_JBCOOKIES_MSG_LANG_INFO');
				$html .= '</div>';
			} else {
				$html .= '<div class="alert alert-warning alert-dismissible" role="alert">';
//				$html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
				$html .= Text::_('MOD_JBCOOKIES_MSG_LANG_BLOCK');
				$html .= '</div>';
			}
			
			$html .= '<div class="row">';
			
				// Title
				$html .= '<div class="col-3">';
					$html .= '<div class="control-group">';
						$html .= '<textarea name="' . $this->name . '['.$lang['tag'].'][title]' . '" id="' . $this->id . '_' . $lang['tag'] . '"' . $columns . $rows . $class . $hintTitle . ' >'
						. $params[$lang['tag']]['title'] . '</textarea>';
					$html .= '</div>';
				$html .= '</div>';
				// Text
				$html .= '<div class="col-3">';
					$html .= '<div class="control-group">';
						$html .= '<textarea name="' . $this->name . '['.$lang['tag'].'][text]' . '" id="' . $this->id . '_' . $lang['tag'] . '"' . $columns . $rows . $class	. $hintText . ' >'
						. $params[$lang['tag']]['text'] . '</textarea>';
					$html .= '</div>';
				$html .= '</div>';
				// Body Modal
				$html .= '<div class="col-3">';
					$html .= '<div class="control-group">';
						$html .= '<textarea name="' . $this->name . '['.$lang['tag'].'][body]' . '" id="' . $this->id . '_' . $lang['tag'] . '"' . $columns . $rows . $class	. $hintBody . ' >'
						. $params[$lang['tag']]['body'] . '</textarea>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';

			$html .= '<div class="row">';
			
				// Alias Reject
				$html .= '<div class="col-3">';
					$html .= '<div class="control-group">';
						$html .= '<input type="text" name="' . $this->name . '['.$lang['tag'].'][reject]' . '" id="' . $this->id . '_' . $lang['tag'] . '" value="' . $params[$lang['tag']]['reject'] . '"' . $class . $hintReject . ' >';
					$html .= '</div>';
				$html .= '</div>';
				// Alias Button
				$html .= '<div class="col-3">';
					$html .= '<div class="control-group">';
						$html .= '<input type="text" name="' . $this->name . '['.$lang['tag'].'][accept]' . '" id="' . $this->id . '_' . $lang['tag'] . '" value="' . $params[$lang['tag']]['accept'] . '"' . $class . $hintAccept . ' >';
					$html .= '</div>';
				$html .= '</div>';
				// Header Modal
				$html .= '<div class="col-3">';
					$html .= '<div class="control-group">';
						$html .= '<input type="text" name="' . $this->name . '['.$lang['tag'].'][header]' . '" id="' . $this->id . '_' . $lang['tag'] . '" value="' . $params[$lang['tag']]['header'] . '"' . $class . $hintHeader . ' >';
					$html .= '</div>';
				$html .= '</div>';
				// Article Link
				$html .= '<div class="col-3">';
					$html .= '<div class="control-group">';
						// The current article display field.
						$article[] = '<span class="input-group">';
						$article[] = '<input type="text" class="form-control" id="' . $this->id . $aLink . '_name" value="' . $title . '" readonly size="35" />';
//						$article[] = '<a class="modal btn hasTooltip" title="' . JHtml::tooltipText('COM_CONTENT_CHANGE_ARTICLE') . '"  href="' . $link . '&amp;' . JSession::getFormToken() . '=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . JText::_('JSELECT') . '</a>';
				
						// Clear article button
//						$article[] = '<button id="' . $this->id . $aLink . '_clear" class="btn' . ($value ? '' : ' hidden') . '" onclick="return jClearArticle(\'' . $this->id . $aLink . '\')"><span class="icon-remove"></span> ' . JText::_('JCLEAR') . '</button>';
						$article[] = '<button'
								. ' class="btn btn-primary' . ($value ? ' hidden' : '') . '"'
										. ' id="' . $this->id . $aLink . '_select"'
												. ' data-bs-toggle="modal"'
														. ' type="button"'
																. ' data-bs-target="#ModalSelect' . $aLink . '">'
																		. '<span class="icon-file" aria-hidden="true"></span> ' . Text::_('JSELECT')
																		. '</button>';
						$article[] = '<button'
								. ' class="btn btn-secondary' . ($value ? '' : ' hidden') . '"'
										. ' id="' . $this->id . $aLink . '_clear"'
												. ' type="button"'
														. ' onclick="window.processModalParent(\'' . $this->id . $aLink . '\'); return false;">'
																. '<span class="icon-times" aria-hidden="true"></span> ' . Text::_('JCLEAR')
																. '</button>';
																		
						$article[] = '</span>';
						$article[] = '<input type="hidden" id="' . $this->id . $aLink . '_id"' . ' class="modal-value"' . ' name="' . $this->name . '['.$lang['tag'].'][alink]' . '" value="' . $value . '" />';
						$html .= implode("\n", $article);
					
					
					$html .= HTMLHelper::_(
							'bootstrap.renderModal',
							'ModalSelect' . $aLink,
							array(
									'title'       => $modalTitle,
									'url'         => $urlSelect,
									'height'      => '400px',
									'width'       => '800px',
									'bodyHeight'  => 70,
									'modalWidth'  => 80,
									'footer'      => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
									. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
							)
							);
					
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';
			
			$html .= '<hr>';
		
		}
		
		echo $html;
		
		return;
	}
}