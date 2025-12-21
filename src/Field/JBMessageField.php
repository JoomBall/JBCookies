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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Installer\Installer;

class JBMessageField extends FormField {

    protected $type = 'JBMessage';

    protected function getInput() {
    	
    	$label = $this->element['label'] ? (string) $this->element['label'] : '';
    	$message = $this->element['message'] ? (string) $this->element['message'] : '';
    	
    	switch ($message) {
    		case 'space':
    			$style = 'border: 1px solid #BBBBBB; background-color: #F1F1F1; ';
    			$text_title = Text::_($label);
    			$text_message = '';
    			break;
    		case 'info':
    			$style = 'border: 1px solid #8bda8b; background-color: #cbfbcb; ';
    			$text_title = Text::_('JBINFORMATION').': ';
    			$text_message = Text::_($label);
    			break;
    		case 'note':
    			$html = array();

       			$html[] = '<div class="alert alert-info alert-dismissible">';
				$html[] = '<strong>'.Text::_('JFIELD_NOTE_DESC').': </strong>';
//				$html[] = '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
				$html[] = Text::_($label);
				$html[] = '</div>';
				echo implode('', $html);
				return;
    			break;
    		case 'version':
		    	$xml = $this->element['xml'];
		    	
		    	if ($xml) {
					$xml = Installer::parseXMLInstallFile($this->element['path'].$xml);
					if ($xml && isset($xml['version'])) {
						$version = $xml['version'];
					}
				}
		
				if (empty($version)) {
					return '';
				}
				
				$html = array();

       			$html[] = '<div class="alert alert-primary alert-dismissible">';
       			$html[] = '<strong>'.Text::_('JVERSION').': </strong>';
//     			$html[] = '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
				$html[] = $this->element['extension_name'].' ' . $version;
				$html[] = '</div>';
				echo implode('', $html);
				return;
    			break;
    		default:
    			$style = 'border: 1px solid #BBBBBB; background-color: #F1F1F1; ';
    			$text_title = '';
    			$text_message = Text::_($label);
    			break;
    	}
    	
        $html = array();

        $html[] = '<div style="clear:left;"></div>';
		$html[] = '<div style="'.$style.'max-width: 500px; margin: 5px 0; padding: 5px 10px; border-radius: 5px; font-size:12px;">';
		$html[] = '<strong style="color:#303030;">';
		$html[] = $text_title;
		$html[] = '</strong>';
		$html[] = $text_message;
		$html[] = '</div>';
		
		return implode('', $html);
    }

    protected function getLabel() {}

}
