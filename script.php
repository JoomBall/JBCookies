<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Version;
use Joomla\CMS\Language\Text;

class mod_jbcookiesInstallerScript {
	
	protected $extension = 'mod_jbcookies';
	
	function install( $parent ) {
		$lang = Factory::getLanguage();
		$basePathLanguage = JPATH_ROOT . '/media/jbmedia';
		$lang->load('install', $basePathLanguage, null, false, true)
			|| $lang->load('install', $basePathLanguage, $lang->getDefault(), false, true);
		
		$status = new stdClass();
		
		$result = null;
		
		self::displayInstall($parent, $result);
		
		?><h1><?php echo JText::_('JB_INSTALL_INSTALLATION'); ?></h1><?php
		
		return true;
	}
 
	/**
	 * Method to update Joomla!
	 *
	 * @param   Installer  $installer  The class calling this method
	 *
	 * @return  void
	 */
	public function update($installer) {
		// Idioma es necessari aquí
		$lang = Factory::getLanguage();
		$basePathLanguage = JPATH_ROOT . '/media/jbmedia';
		$lang->load('install', $basePathLanguage, null, false, true)
			|| $lang->load('install', $basePathLanguage, $lang->getDefault(), false, true);
		
		$status = new stdClass();
		
//		echo '<pre>'; echo print_r($installer); echo '</pre>'; exit;
		
		$result = null;
		
		// Installing component manifest file version
		$this->release = $installer->getManifest()->version;
		
		if ($this->release <= '3.1.6') {
			$pathSite = JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_jbcookies';
				
			// Eliminem Arxius innecesaris
			$files = array($pathSite.'/tmpl/custom.php', $pathSite.'/tmpl/decline.php', $pathSite.'/tmpl/default_decline.php');
		
			File::delete($files);
		}
		
		if ($this->release <= '4.0.0') {
			$pathSite = JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_jbcookies';
		
			// Eliminem Arxius innecesaris
			$files = array($pathSite.'/index.html', $pathSite.'/helper.php', $pathSite.'/script.install.php',
								$pathSite.'/tmpl/index.html',
								$pathSite.'/language/index.html',
									$pathSite.'/language/ca-ES/index.html', $pathSite.'/language/ca-ES/ca-ES.mod_jbcookies.ini', $pathSite.'/language/ca-ES/ca-ES.mod_jbcookies.sys.ini',
									$pathSite.'/language/de-DE/index.html', $pathSite.'/language/de-DE/de-DE.mod_jbcookies.ini', $pathSite.'/language/de-DE/de-DE.mod_jbcookies.sys.ini',
									$pathSite.'/language/el-GR/index.html', $pathSite.'/language/el-GR/el-GR.mod_jbcookies.ini', $pathSite.'/language/el-GR/el-GR.mod_jbcookies.sys.ini',
									$pathSite.'/language/en-GB/index.html', $pathSite.'/language/en-GB/en-GB.mod_jbcookies.ini', $pathSite.'/language/en-GB/en-GB.mod_jbcookies.sys.ini',
									$pathSite.'/language/es-ES/index.html', $pathSite.'/language/es-ES/es-ES.mod_jbcookies.ini', $pathSite.'/language/es-ES/es-ES.mod_jbcookies.sys.ini',
									$pathSite.'/language/fr-FR/index.html', $pathSite.'/language/fr-FR/fr-FR.mod_jbcookies.ini', $pathSite.'/language/fr-FR/fr-FR.mod_jbcookies.sys.ini',
									$pathSite.'/language/it-IT/index.html', $pathSite.'/language/it-IT/it-IT.mod_jbcookies.ini', $pathSite.'/language/it-IT/it-IT.mod_jbcookies.sys.ini',
									$pathSite.'/language/nl-NL/index.html', $pathSite.'/language/nl-NL/nl-NL.mod_jbcookies.ini', $pathSite.'/language/nl-NL/nl-NL.mod_jbcookies.sys.ini',
									$pathSite.'/language/pl-PL/index.html', $pathSite.'/language/pl-PL/pl-PL.mod_jbcookies.ini', $pathSite.'/language/pl-PL/pl-PL.mod_jbcookies.sys.ini',
									$pathSite.'/language/pt-PT/index.html', $pathSite.'/language/pt-PT/pt-PT.mod_jbcookies.ini', $pathSite.'/language/pt-PT/pt-PT.mod_jbcookies.sys.ini',
									$pathSite.'/language/sv-SE/index.html', $pathSite.'/language/sv-SE/sv-SE.mod_jbcookies.ini', $pathSite.'/language/sv-SE/sv-SE.mod_jbcookies.sys.ini');
		
			File::delete($files);
			
			self::deleteFolder($pathSite.'/assets');
			self::deleteFolder($pathSite.'/fields');
		}
		
		// Afegim plantilla
		self::displayInstall($installer, $result);
		
		?><h1><?php echo JText::_('JB_INSTALL_UPDATE'); ?></h1><?php
		
		$jversion = new Version();
		
		$this->last_version = $this->getParam('version');

		// Manifest file minimum Joomla version
		$this->minimum_joomla_release = $installer->getManifest()->attributes()->version;   

		// Show the essential information at the install/update back-end
		echo '<p>Installing component manifest file version = ' . $this->release . '</p>';
		echo '<p>Current manifest cache component version = ' . $this->last_version . '</p>';
		echo '<p>Installing component manifest file minimum Joomla version = ' . $this->minimum_joomla_release . '</p>';
		echo '<p>Current Joomla version = ' . $jversion->getShortVersion() . '</p>';

		return true;
	}
	
	function getParam( $name ) {
		$db = Factory::getDbo();
		$db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = ' . $db->quote($this->extension));
		$manifest = json_decode($db->loadResult(), true);
		return $manifest[ $name ];
	}
	
 	/**
	 * creates a folder with empty html file
	 *
	 *
	 */
	public function createIndexFolder($path){
		if(Folder::exists($path)) {
			return 'exist';
		}
		if(Folder::create($path)) {
			if(!File::exists($path .DIRECTORY_SEPARATOR. 'index.html')){
				File::copy(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'index.html', $path .DIRECTORY_SEPARATOR. 'index.html');
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
		if (is_dir($folderPath)) Folder::delete($folderPath);
	}
	
	public function displayInstall($parent, $status) {
		// Installing component manifest file version
		$this->release = $parent->getManifest()->version; ?>
		
		<style>
			/* TAULA */
			.table th {
				padding-top: 40px;
			}
			
			.table td {
				font-weight: normal;;
			}
		</style>
		
		<h2><?php echo Text::_('JB_INSTALL_INSTALLATION_STATUS'); ?></h2>
		<table class="table table-striped">
			<thead>
				<tr>
					<th><?php echo Text::_('JB_INSTALL_EXTENSION'); ?></th>
					<th><?php echo Text::_('JVERSION'); ?></th>
					<th colspan="3"><?php echo Text::_('JB_INSTALL_DESCRIPTION'); ?></th>
					<th><?php echo Text::_('JSTATUS'); ?></th>
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
					<td style="width: 100%;" colspan="3"><?php echo Text::_(strtoupper($this->extension).'_XML_DESCRIPTION'); ?></td>
					<td><span style="color:green"><strong><em><?php echo Text::_('JB_INSTALL_INSTALLED'); ?></em></strong></span></td>
				</tr>
			</tbody>
		</table>
		<?php
	}
}
?>