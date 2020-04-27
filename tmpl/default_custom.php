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
	.jb.cookie.color p a {
		color: <?php echo $color_links; ?>;
	} 

	.jb.bgcolor {
		background: <?php echo $color_background; ?>;
	}
	
	.jb.cookie.top {
		border-bottom: 1px solid <?php echo $btn_border_color; ?>;
	}

	.jb.cookie.bottom {
		border-top: 1px solid <?php echo $color_links; ?>;
	}
	
	.jb.color {
		color: <?php echo $color_text; ?>;
	}
	
	.jb.accept {
		color: <?php echo $btn_text_color; ?>;
		background-image: -webkit-linear-gradient(<?php echo $btn_start_color; ?> 0%, <?php echo $btn_end_color; ?> 100%);
		background-image: -moz-linear-gradient(<?php echo $btn_start_color; ?> 0%, <?php echo $btn_end_color; ?> 100%);
		background-image: -o-linear-gradient(<?php echo $btn_start_color; ?> 0%, <?php echo $btn_end_color; ?> 100%);
		background-image: -webkit-gradient(linear, 0 0, 0 100%, from(<?php echo $btn_start_color; ?>), to(<?php echo $btn_end_color; ?>));
		background-image: linear-gradient(<?php echo $btn_start_color; ?> 0%, <?php echo $btn_end_color; ?> 100%);
		max-width: <?php echo $btn_width . 'px'; ?>;
		line-height: <?php echo $btn_height . 'px'; ?>;
		padding: 0;
		border: 1px solid <?php echo $btn_border_color; ?>;
	}
	
	.jb.decline.link {
		color: <?php echo $color_links_decline; ?>;
		padding: 0;
	}
</style>
<?php if ($params->get('show_decline', 1)) : ?>
	<!-- Template Decline -->
	<div class="jb cookie-decline <?php echo $moduleclass_sfx; ?>" style="display: none;">
		<p>
			<?php echo $params->get('show_decline_description', 1) ? $text_decline : ''; ?>
			<span class="btn btn-link jb decline link"><?php echo $aliasButton_decline; ?></span>
		</p>
	</div>
<?php endif; ?>
<?php if ($modal_framework == 'bootstrap') : ?>
	<!-- Template Default bootstrap -->
	<div class="jb cookie <?php echo $position; ?> color  <?php echo $moduleclass_sfx; ?>">
	   
		<!-- BG color -->
		<div class="jb cookie-bg bgcolor"></div>
	    
		<h2><?php echo $title; ?></h2>
	     
		<p class="jb color"><?php echo $text; ?>
			<?php if($show_info) : ?>
				<?php if($aLink) : ?>
					<a href="<?php echo $item->readmore_link; ?>"><?php echo $aliasLink; ?></a>
				<?php else: ?>
					<!-- Button to trigger modal -->
					<a href="#jbcookies" data-toggle="modal" data-target="#jbcookies"><?php echo $aliasLink; ?></a>
				<?php endif; ?>
			<?php endif; ?>
		</p>
	    
	    <div class="jb accept"><?php echo $aliasButton; ?></div>
	    
	</div>
	
	<?php if($show_info and !$aLink) : ?>
	    <!-- Modal -->
	    <?php if($framework_version) : // For Bootstrap 3-4 ?>
			<div class="modal" id="jbcookies" tabindex="-1" role="dialog">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<?php if ($params->get('bootstrap_version', 2) == 3) : ?>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title"><?php echo $header; ?></h4>
							<?php else : ?>
								<h4 class="modal-title"><?php echo $header; ?></h4>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<?php endif; ?>
						</div>
						<div class="modal-body">
							<?php echo $body; ?>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default btn-outline-secondary" data-dismiss="modal"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE'); ?></button>
						</div>
					</div>
				</div>
			</div>
		<?php else: ?>
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
	<?php endif; ?>

<?php else : ?>
	<!-- Template Default uikit -->
	<div class="jb cookie <?php echo $position; ?> color <?php echo $moduleclass_sfx; ?>">
	    
		<!-- BG color -->
		<div class="jb cookie-bg bgcolor"></div>
	    
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
	    
		<div class="jb accept"><?php echo $aliasButton; ?></div>
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


