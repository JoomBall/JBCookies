<?php
/**
 * @package			Joomla.Site
 * @subpackage		Modules - mod_jbcookies
 * 
 * @author			JoomBall! Project
 * @link			http://www.joomball.com
 * @copyright		Copyright © 2011-2026 JoomBall! Project. All Rights Reserved.
 * @license			GNU/GPL, http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<!--googleoff: all-->
<?php if ($params->get('show_icon', 1)) : ?>
	<div class="jb-cookie-decline <?php echo $params->get('icon_position', 'right'); ?> <?php echo $moduleclass_sfx; ?> robots-noindex robots-nofollow robots-nocontent" style="display: none;">
		<button class="btn btn-primary" type="button" aria-label="<?php echo Text::_('MOD_JBCOOKIES_LANG_HEADER_DEFAULT'); ?>">
			<img alt="Cookies" width="26" height="26" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgMTIwLjIzIDEyMi44OCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTIwLjIzIDEyMi44OCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+PHN0eWxlIHR5cGU9InRleHQvY3NzIj4uc3Qwe2ZpbGwtcnVsZTpldmVub2RkO2NsaXAtcnVsZTpldmVub2RkO308L3N0eWxlPjxnPjxwYXRoIGZpbGw9IiNmZmYiIGNsYXNzPSJzdDAiIGQ9Ik05OC4xOCwwYzMuMywwLDUuOTgsMi42OCw1Ljk4LDUuOThjMCwzLjMtMi42OCw1Ljk4LTUuOTgsNS45OGMtMy4zLDAtNS45OC0yLjY4LTUuOTgtNS45OCBDOTIuMjEsMi42OCw5NC44OCwwLDk4LjE4LDBMOTguMTgsMHogTTk5Ljc4LDUyLjA4YzUuMTYsNy43LDExLjY5LDEwLjA2LDIwLjE3LDQuODVjMC4yOCwyLjksMC4zNSw1Ljg2LDAuMiw4Ljg2IGMtMS42NywzMy4xNi0yOS45LDU4LjY5LTYzLjA2LDU3LjAyQzIzLjk0LDEyMS4xMy0xLjU5LDkyLjksMC4wOCw1OS43NUMxLjc0LDI2LjU5LDMwLjk1LDAuNzgsNjQuMSwyLjQ1IGMtMi45NCw5LjItMC40NSwxNy4zNyw3LjAzLDIwLjE1QzY0LjM1LDQ0LjM4LDc5LjQ5LDU4LjYzLDk5Ljc4LDUyLjA4TDk5Ljc4LDUyLjA4eiBNMzAuMDMsNDcuNzljNC45NywwLDguOTksNC4wMyw4Ljk5LDguOTkgcy00LjAzLDguOTktOC45OSw4Ljk5Yy00Ljk3LDAtOC45OS00LjAzLTguOTktOC45OVMyNS4wNyw0Ny43OSwzMC4wMyw0Ny43OUwzMC4wMyw0Ny43OXogTTU4LjM1LDU5LjI1YzIuODYsMCw1LjE4LDIuMzIsNS4xOCw1LjE4IGMwLDIuODYtMi4zMiw1LjE4LTUuMTgsNS4xOGMtMi44NiwwLTUuMTgtMi4zMi01LjE4LTUuMThDNTMuMTYsNjEuNTcsNTUuNDgsNTkuMjUsNTguMzUsNTkuMjVMNTguMzUsNTkuMjV6IE0zNS44Nyw4MC41OSBjMy40OSwwLDYuMzIsMi44Myw2LjMyLDYuMzJjMCwzLjQ5LTIuODMsNi4zMi02LjMyLDYuMzJjLTMuNDksMC02LjMyLTIuODMtNi4zMi02LjMyQzI5LjU1LDgzLjQxLDMyLjM4LDgwLjU5LDM1Ljg3LDgwLjU5IEwzNS44Nyw4MC41OXogTTQ5LjQ5LDMyLjIzYzIuNzQsMCw0Ljk1LDIuMjIsNC45NSw0Ljk1YzAsMi43NC0yLjIyLDQuOTUtNC45NSw0Ljk1Yy0yLjc0LDAtNC45NS0yLjIyLTQuOTUtNC45NSBDNDQuNTQsMzQuNDUsNDYuNzYsMzIuMjMsNDkuNDksMzIuMjNMNDkuNDksMzIuMjN6IE03Ni4zOSw4Mi44YzQuNTksMCw4LjMsMy43Miw4LjMsOC4zYzAsNC41OS0zLjcyLDguMy04LjMsOC4zIGMtNC41OSwwLTguMy0zLjcyLTguMy04LjNDNjguMDksODYuNTIsNzEuODEsODIuOCw3Ni4zOSw4Mi44TDc2LjM5LDgyLjh6IE05My44NywyMy4xYzMuMDgsMCw1LjU4LDIuNSw1LjU4LDUuNTggYzAsMy4wOC0yLjUsNS41OC01LjU4LDUuNThzLTUuNTgtMi41LTUuNTgtNS41OEM4OC4yOSwyNS42LDkwLjc5LDIzLjEsOTMuODcsMjMuMUw5My44NywyMy4xeiIvPjwvZz48L3N2Zz4="/>
		</button>
	</div>
<?php endif; ?>

<div class="modal jb-cookie <?php echo $moduleclass_sfx; ?> robots-noindex robots-nofollow robots-nocontent" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<?php if ($params->get('show_cookie_title', 1)) : ?>
					<p class="jb-cookie-title"><?php echo $title; ?></p>
				<?php endif; ?>
				<p class="jb-cookie-text"><?php echo $text; ?></p>
				<div class="d-flex flex-nowrap gap-3 w-100">
					<button class="jb-settings btn btn-outline-secondary flex-fill" type="button" data-bs-toggle="modal" data-bs-target="#jbcookies-preferences"><?php echo Text::_('MOD_JBCOOKIES_ACTION_SETTINGS'); ?></button>
					<button class="jb-accept btn btn-primary flex-fill" type="button"><?php echo $accept; ?></button>
				</div>
			</div>

			<?php if ($params->get('show_policy_cookies', 1)) : ?>
				<div class="modal-footer p-0">
					<?php if ($aLink) : ?>
						<a class="jb-policy" href="<?php echo $item->readmore_link; ?>" rel="nofollow"><?php echo Text::_('MOD_JBCOOKIES_GLOBAL_POLICY_COOKIES'); ?></a>
					<?php else: ?>
						<a class="jb-policy" href="#jbcookies" data-bs-toggle="modal" data-bs-target="#jbcookies" rel="nofollow"><?php echo Text::_('MOD_JBCOOKIES_GLOBAL_POLICY_COOKIES'); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
	
<?php if($params->get('show_policy_cookies', 1) && !$aLink) : ?>
	<div class="modal robots-noindex robots-nofollow robots-nocontent" id="jbcookies" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
		<div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<div class="modal-title"><?php echo $header; ?></div>
					<button type="button" class="btn-close" data-bs-target=".jb-cookie" data-bs-toggle="modal" aria-label="<?php echo Text::_('JLIB_HTML_BEHAVIOR_CLOSE'); ?>"></button>
				</div>
				<div class="modal-body">
					<?php echo $body; ?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-target=".jb-cookie" data-bs-toggle="modal"><?php echo Text::_('JLIB_HTML_BEHAVIOR_CLOSE'); ?></button>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
<?php require __DIR__ . '/partials/preferences.php'; ?>
<!--googleon: all-->