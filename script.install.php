<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class mod_jbcookiesInstallerScript {
	
	protected $extension = 'mod_jbcookies';
	
	public function __construct()
	{
//		$lang = JFactory::getLanguage();
//		$basePathLanguage = JPATH_ROOT . '/media/joomball/assets';
//		$lang->load('install', $basePathLanguage, null, false, true)
//			|| $lang->load('install', $basePathLanguage, $lang->getDefault(), false, true);
	}
	
	function install( $parent ) {
		$lang = JFactory::getLanguage();
		$basePathLanguage = JPATH_ROOT . '/media/joomball/assets';
		$lang->load('install', $basePathLanguage, null, false, true)
			|| $lang->load('install', $basePathLanguage, $lang->getDefault(), false, true);
		
		$status = new JObject();
		
		$result = null;
		
		self::displayInstall($parent, $result);
		
		?><h1><?php echo JText::_('JB_INSTALL_INSTALLATION'); ?></h1><?php
		
		return true;
	}
 
	function update( $parent ) {
		$lang = JFactory::getLanguage();
		$basePathLanguage = JPATH_ROOT . '/media/joomball/assets';
		$lang->load('install', $basePathLanguage, null, false, true)
			|| $lang->load('install', $basePathLanguage, $lang->getDefault(), false, true);
		
		$status = new JObject();
		
		$result = null;
		
		// Installing component manifest file version
		$this->release = $parent->get( "manifest" )->version;
		
		if ($this->release <= '3.0.9') {
			$pathSite = JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_jbcookies';
				
			// Eliminem Arxius innecesaris
			$files = array($pathSite.'/tmpl/custom.php', $pathSite.'/tmpl/decline.php');
		
			JFile::delete($files);
		}
		
		// Afegim plantilla
		self::displayInstall($parent, $result);
		
		?><h1><?php echo JText::_('JB_INSTALL_UPDATE'); ?></h1><?php
		
		$jversion = new JVersion();
		
		$this->last_version = $this->getParam('version');

		// Manifest file minimum Joomla version
		$this->minimum_joomla_release = $parent->get( "manifest" )->attributes()->version;   

		// Show the essential information at the install/update back-end
		echo '<p>Installing component manifest file version = ' . $this->release . '</p>';
		echo '<p>Current manifest cache component version = ' . $this->last_version . '</p>';
		echo '<p>Installing component manifest file minimum Joomla version = ' . $this->minimum_joomla_release . '</p>';
		echo '<p>Current Joomla version = ' . $jversion->getShortVersion() . '</p>';

		return true;
	}
	
	function getParam( $name ) {
		$db = JFactory::getDbo();
		$db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = ' . $db->quote($this->extension));
		$manifest = json_decode( $db->loadResult(), true );
		return $manifest[ $name ];
	}
	
 	/**
	 * creates a folder with empty html file
	 *
	 *
	 */
	public function createIndexFolder($path){
		if(JFolder::exists($path)) {
			return 'exist';
		}
		if(JFolder::create($path)) {
			if(!JFile::exists($path .DIRECTORY_SEPARATOR. 'index.html')){
				JFile::copy(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'index.html', $path .DIRECTORY_SEPARATOR. 'index.html');
			}
			return 'create';
		}
		return false;
	}
	
	/**
	 * delete a folder	 *
	 *
	 */
	public function deleteFolder($folderPath){
		if (is_dir($folderPath)) JFolder::delete($folderPath);
	}
	
	public function displayInstall($parent, $status) {
		// Installing component manifest file version
		$this->release = $parent->get( "manifest" )->version; ?>
		
		<style>
		<!--
			/* TAULA */
			.table th {
				padding-top: 40px;
			}
			
			.table td {
				font-weight: normal;;
			}
		-->
		</style>
		
		<h2><?php echo JText::_('JB_INSTALL_INSTALLATION_STATUS'); ?></h2>
		<table class="table table-striped">
			<thead>
				<tr>
					<th><?php echo JText::_('JB_INSTALL_EXTENSION'); ?></th>
					<th><?php echo JText::_('JVERSION'); ?></th>
					<th colspan="3"><?php echo JText::_('JB_INSTALL_DESCRIPTION'); ?></th>
					<th><?php echo JText::_('JSTATUS'); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<?php echo $this->extension; ?>
					</td>					
					<td>
						<?php echo $this->release; ?>
					</td>
					<td style="width: 100%;" colspan="3"><?php echo JText::_(strtoupper($this->extension).'_XML_DESCRIPTION'); ?></td>
					<td><span style="color:green"><strong><em><?php echo JText::_('JB_INSTALL_INSTALLED'); ?></em></strong></span></td>
				</tr>
			</tbody>
		</table>
		<?php
	}
}
?>