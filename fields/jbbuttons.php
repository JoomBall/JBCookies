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

class JFormFieldJBButtons extends JFormField {

    protected $type = 'Jbbuttons';

    public function getLabel() {
        return;
    }

    protected function getInput() {
    	
		$url_site_joomball			= $this->element['url_site_joomball'];
    	$url_site_joomball_download = $this->element['url_site_joomball_download'];
    	$url_site_joomball_demo		= $this->element['url_site_joomball_demo'];
    	$url_site_joomla_extensions = $this->element['url_site_joomla_extensions'];
    	
    	$html = '';
    	$style='style="margin: 0 5px;"';
    	
    	if ($url_site_joomball) :
    		$html .= '<a class="btn btn-info" href="'.$url_site_joomball.'" target="_blank" '.$style.'><span class="icon-home"></span> '.JText::_('JSITE').'</a>';
    	endif;
    	
        if ($url_site_joomball_download) :
    		$html .= '<a class="btn btn-success" href="'.$url_site_joomball_download.'" target="_blank" '.$style.'><span class="icon-download"></span> '.JText::_('MOD_JBCOOKIES_GLOBAL_DOWNLOAD').'</a>';
    	endif;
    	
		if ($url_site_joomball_demo) :
    		$html .= '<a class="btn btn-primary" href="'.$url_site_joomball_demo.'" target="_blank" '.$style.'><span class="icon-eye"></span> '.JText::_('MOD_JBCOOKIES_GLOBAL_DEMO').'</a>';
    	endif;
    	
        if ($url_site_joomla_extensions) :
    		$html .= '<a class="btn btn-primary" href="'.$url_site_joomla_extensions.'" target="_blank" '.$style.'><span class="icon-joomla"></span> '.JText::_('MOD_JBCOOKIES_GLOBAL_VOTE_EXTENSION').'</a>';
    	endif;
    	
    	return  $html;
    }
}