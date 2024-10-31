<?php
if (!defined('ABSPATH')) {
	exit;
}

use Pmpr\Plugin\Pmpr\Component\Item;

$HTMLHelper = pmpr_get_plugin_object()->getHelper()->getHTML();

if (isset($component) && $component instanceof Item) {
	?>
    <div class="plugin-card plugin-card-<?php echo esc_attr(sanitize_html_class($component->getName())); ?>">
        <div class="plugin-card-top">
            <div class="name column-name">
                <h3>
                    <a href="<?php echo esc_url($component->getPermalink()); ?>" target="_blank">
						<?php $HTMLHelper->renderElement('span', ['class' => 'plugin-title'], $component->getTitle()); ?>
                        <img src="<?php echo esc_url($component->getImageURL()); ?>" class="plugin-icon" alt=""/>
                    </a>
                </h3>
            </div>
            <div class="action-links">
				<?php
				if ($actions = $component->getActions()) {

					?>
                    <ul class="plugin-action-buttons">
						<?php
						foreach ($actions as $action) {

                            $HTMLHelper->renderElement('li', [], $action);
						}
						?>
                    </ul>
					<?php
				}
				?>
            </div>
            <div class="desc column-description">
				<?php $HTMLHelper->renderHTML(wpautop($component->getDescription())); ?>
            </div>
        </div>
        <div class="plugin-card-bottom">
            <div class="vers column-rating" style="width: 100%;">
				<?php
				if ($component->isPublished()) {

					wp_star_rating(
						[
							'rating' => (float)$component->getRating(),
							'type'   => 'percent',
							'number' => (int)$component->getRatingCount(),
						]
					);
					?>
                    <span class="num-ratings" aria-hidden="true">
                            (<?php echo esc_html(number_format_i18n((float)$component->getRatingCount())); ?>)
                        </span>
					<?php
				} else {

					?>
                    <strong><?php _e('Release Schedule:', PR__PLG__PMPR); ?></strong>
                    <br>
					<?php
					echo esc_html($component->getPublishDue());
				}
				?>
            </div>
            <div class="column-updated">
                <strong><?php _e('Last Updated:', PR__PLG__PMPR); ?></strong>
				<?php if ($component->isPublished() && ($lastUpdate = $component->getLastUpdate())): ?>

					<?php printf(__('%s ago', PR__PLG__PMPR), human_time_diff(strtotime($lastUpdate)), time()); ?>
				<?php else: ?>
					<?php esc_html_e('Unknown', PR__PLG__PMPR); ?>
				<?php endif; ?>
            </div>
			<?php if ($component->isPublished()): ?>
                <div class="column-downloaded">
					<?php
					$activeInstall = $component->getActiveInstall();
					if ($activeInstall >= 1000000) {

						$activeInstall     = floor($activeInstall / 1000000);
						$activeInstallText = sprintf(
							_nx('%s+ Million', '%s+ Million', $activeInstall, 'Active plugin installations', PR__PLG__PMPR),
							number_format_i18n($activeInstall)
						);
					} else if (0 === $activeInstall) {

						$activeInstallText = _x('Less Than 10', 'Active plugin installations', PR__PLG__PMPR);
					} else {

						$activeInstallText = number_format_i18n($activeInstall) . '+';
					}
					printf(__('%s Active Installations', PR__PLG__PMPR), $activeInstallText);
					?>
                </div>
			<?php endif; ?>
        </div>
    </div>
	<?php
}