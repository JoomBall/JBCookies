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
?>
<!--googleoff: all-->
<style type="text/css">  
	.jb.decline.link {
		color: <?php echo $color_links_decline; ?>;
		padding: 0;
	}
</style>
<!-- Template Decline -->
<div class="jb cookie-decline <?php echo $moduleclass_sfx; ?>">
	<p>
		<?php echo $params->get('show_decline_description', 1) ? $text_decline : ''; ?>
		<span class="btn btn-link jb decline link"><?php echo $aliasButton_decline; ?></span>
	</p>
</div>
<!--googleon: all-->


