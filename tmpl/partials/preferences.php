<?php
/**
 * @package        Joomla.Site
 * @subpackage     Modules - mod_jbcookies
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$preferencesGroups = $cookiePreferencesConfig['groups'] ?? [];
$inventoryByGroup  = $cookiePreferencesConfig['inventory'] ?? [];
?>
<div class="modal robots-noindex robots-nofollow robots-nocontent" id="jbcookies-preferences" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="jbcookies-preferences-label" tabindex="-1">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<div class="modal-title" id="jbcookies-preferences-label"><?php echo Text::_('MOD_JBCOOKIES_MODAL_TITLE'); ?></div>
				<button type="button" class="btn-close" data-bs-target=".jb-cookie" data-bs-toggle="modal" aria-label="<?php echo Text::_('JLIB_HTML_BEHAVIOR_CLOSE'); ?>"></button>
			</div>
			<div class="modal-body">
				<?php if ($preferencesGroups) : ?>
					<div class="jb-cookie-preferences">
						<?php foreach ($preferencesGroups as $group) :
							$groupSlug   = $group['slug'];
							$groupId     = 'jb-toggle-' . $groupSlug;
							$groupCookies = $inventoryByGroup[$groupSlug] ?? [];
						?>
							<section class="jb-cookie-group border rounded p-3 mb-3" data-group="<?php echo $groupSlug; ?>">
								<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-2">
									<div>
										<h6 class="mb-1"><?php echo htmlspecialchars($group['title'], ENT_QUOTES, 'UTF-8'); ?></h6>
										<?php if (!empty($group['description'])) : ?>
											<p class="mb-0 text-muted small"><?php echo $group['description']; ?></p>
										<?php endif; ?>
									</div>
									<div class="form-check form-switch">
										<input class="form-check-input jb-cookie-toggle" type="checkbox" role="switch" id="<?php echo $groupId; ?>" data-group="<?php echo $groupSlug; ?>" data-default="<?php echo $group['default'] ? '1' : '0'; ?>" <?php echo $group['required'] ? 'checked disabled' : 'checked'; ?>>
										<label class="form-check-label small" for="<?php echo $groupId; ?>"></label>
									</div>
								</div>
								<?php if ($groupCookies) : ?>
									<p class="text-muted small mb-1"><?php echo Text::plural('MOD_JBCOOKIES_MODAL_COOKIES_FOUND', count($groupCookies), count($groupCookies)); ?></p>
									<ul class="jb-cookie-inventory list-unstyled small mb-0">
										<?php foreach ($groupCookies as $cookie) : ?>
											<li class="py-1 border-top">
												<strong><?php echo htmlspecialchars($cookie['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
												<?php if (!empty($cookie['provider'])) : ?>
													<span class="text-muted small">(<?php echo htmlspecialchars($cookie['provider'], ENT_QUOTES, 'UTF-8'); ?>)</span>
												<?php endif; ?>
												<?php if (!empty($cookie['description'])) : ?>
													<div class="text-muted small"><?php echo htmlspecialchars(Text::_($cookie['description']), ENT_QUOTES, 'UTF-8'); ?></div>
												<?php endif; ?>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php else : ?>
									<p class="text-muted small mb-0"><?php echo Text::_('MOD_JBCOOKIES_MODAL_EMPTY'); ?></p>
								<?php endif; ?>
							</section>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p class="text-muted small mb-0"><?php echo Text::_('MOD_JBCOOKIES_MODAL_EMPTY'); ?></p>
				<?php endif; ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-danger btn-sm jb-reject-all"><?php echo Text::_('MOD_JBCOOKIES_ACTION_REJECT'); ?></button>
				<button type="button" class="btn btn-success btn-sm jb-save-selection"><?php echo Text::_('MOD_JBCOOKIES_ACTION_SAVE'); ?></button>
				<button type="button" class="btn btn-primary btn-sm jb-accept-all"><?php echo Text::_('MOD_JBCOOKIES_ACTION_ACCEPT_ALL'); ?></button>
			</div>
		</div>
	</div>
</div>
