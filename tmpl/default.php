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
?>
<!--googleoff: all-->
<?php if ($modal_framework == 'bootstrap') : ?>
	<!-- Template Default bootstrap -->
	<div class="jb cookie <?php echo $position; ?> <?php echo $color_background; ?> <?php echo $color_links; ?> <?php echo $moduleclass_sfx; ?>">
	    
		<!-- BG color -->
		<div class='jb cookie-bg <?php echo $color_background; ?>'></div>
	    
		<h2><?php echo $title; ?></h2>
	     
		<p><?php echo $text; ?>
			<?php if($show_info) : ?>
				<?php if($aLink) : ?>
					<a href="<?php echo $item->readmore_link; ?>"><?php echo $aliasLink; ?></a>
				<?php else: ?>
					<!-- Button to trigger modal -->
					<a href="#jbcookies" data-toggle="modal"><?php echo $aliasLink; ?></a>
				<?php endif; ?>
			<?php endif; ?>
		</p>
	    
		<div class="btn btn-primary jb accept <?php echo $color_links; ?>"><?php echo $aliasButton; ?></div>
	    
	</div>
	
	<?php if($show_info and !$aLink) : ?>
	    <!-- Modal -->
		<div id="jbcookies" class="modal hide fade">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3><?php echo $header; ?></h3>
			</div>
			<div class="modal-body">
				<?php echo $body; ?>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE'); ?></button>
			</div>
		</div>
	<?php endif; ?>

<?php else : ?>
	<!-- Template Default uikit -->
	<div class="jb cookie <?php echo $position; ?> <?php echo $color_background; ?> <?php echo $color_links; ?> <?php echo $moduleclass_sfx; ?>">
	    
		<!-- BG color -->
		<div class='jb cookie-bg <?php echo $color_background; ?>'></div>
	    
		<h2><?php echo $title; ?></h2>
	     
		<p><?php echo $text; ?>
			<?php if($show_info) : ?>
				<?php if($aLink) : ?>
					<a href="<?php echo $item->readmore_link; ?>"><?php echo $aliasLink; ?></a>
				<?php else: ?>
					<!-- Button to trigger modal -->
					<a href="#jbcookies" data-uk-modal><?php echo $aliasLink; ?></a>
				<?php endif; ?>
			<?php endif; ?>
		</p>
	    
		<div class="uk-button uk-button-success jb accept <?php echo $color_links; ?>"><?php echo $aliasButton; ?></div>
	</div>
	
	<?php if($show_info and !$aLink) : ?>
	    <!-- Modal -->
		<div id="jbcookies" class="uk-modal">
			<div class="uk-modal-dialog uk-modal-dialog-large">
				<button class="uk-modal-close uk-close" type="button"></button>
				<div class="uk-modal-header">
					<h2><?php echo $header; ?></h2>
				</div>
				<?php echo $body; ?>
				<div class="uk-modal-footer uk-text-right">
					<button class="uk-button uk-modal-close" type="button"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE'); ?></button>
				</div>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>
<!--googleon: all-->


