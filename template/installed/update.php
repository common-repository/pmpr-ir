<?php

use Pmpr\Plugin\Pmpr\Component\Process;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can(apply_filters('pmpr_menu_capability', 'activate_plugins'))) {

    wp_die(sprintf(__('Sorry, you are not allowed to manage %s for this site.', PR__PLG__PMPR), $title));
}

$HTMLHelper      = pmpr_get_plugin_object()->getHelper()->getHTML();
$typeHelper      = pmpr_get_plugin_object()->getHelper()->getType();
$componentHelper = pmpr_get_plugin_object()->getHelper()->getComponent();

$checkUpdate = $HTMLHelper->createAction([
    Constants::JOB     => 'check_update',
    Constants::TITLE   => __('Check Update', PR__PLG__PMPR),
    Constants::LOADING => true,
    Constants::ATTRS   => [
        'class' => 'pr-btn btn-outline-primary',
    ],
]);

?>
<div class="wrap">
    <?php $HTMLHelper->renderElement('h1', ['class' => 'wp-heading-inline'], $title); ?>

    <hr class="wp-header-end">

    <?php if ($componentHelper->isRequiredInstalled()): ?>
        <div class="pr-card my-2">
            <div class="pr-card-content">
                <h2 class="mt-0"><?php _e('Update Components', PR__PLG__PMPR) ?></h2>
                <?php if ($componentHelper->getUpdateCount() > 0): ?>
                    <?php if ($process instanceof Process
                        && $process->isScheduled($process::MIDNIGHT_UPDATE_JOB)): ?>

                        <p class="mb-3">
                            <?php
                            $date = $process->getScheduleDueTime($process::MIDNIGHT_UPDATE_JOB);
                            if ($date) {

                                printf(
                                    __('An update scheduled for midnight (%s).', PR__PLG__PMPR),
                                    $typeHelper->translateDate($date->format('Y-m-d H:i:s'))
                                );
                            }
                            ?>
                        </p>
                        <?php

                        $HTMLHelper->renderModal([
                            Constants::TITLE   => __('Update Now!', PR__PLG__PMPR),
                            Constants::PREFIX  => 'update_now',
                            Constants::CONTENT => __('During the installation of updates, the site maintenance mode is temporarily activated and after the installation is finished, the site will be available as before.', PR__PLG__PMPR),
                            Constants::BUTTONS => [
                                'cancel',
                                'update_now' => $HTMLHelper->createAction([
                                    Constants::TITLE => __('Install right now', PR__PLG__PMPR),
                                    Constants::JOB   => 'update_now',
                                    Constants::ATTRS => [
                                        'class' => 'pr-btn btn-primary',
                                    ],
                                ]),
                            ],
                        ], __('Update Now!', PR__PLG__PMPR), [
                            'class' => 'pr-btn btn-primary update-components-now',
                        ]);

                        $HTMLHelper->renderModal([
                            Constants::TITLE   => __('Cancel Midnight Update', PR__PLG__PMPR),
                            Constants::PREFIX  => 'cancel_midnight_update',
                            Constants::CONTENT => __('Are you sure about canceling the update schedule?', PR__PLG__PMPR),
                            Constants::BUTTONS => [
                                'cancel',
                                'cancel_update' => $HTMLHelper->createAction([
                                    Constants::TITLE => __('Cancel Update', PR__PLG__PMPR),
                                    Constants::JOB   => 'cancel_update',
                                    Constants::ATTRS => [
                                        'class' => 'pr-btn btn-primary',
                                    ],
                                ]),
                            ],
                        ], __('Cancel Midnight Update', PR__PLG__PMPR), [
                            'class' => 'pr-btn btn-outline-primary cancel-update-components ml-2',
                        ]);
                        ?>

                    <?php else: ?>

                        <p class="mb-3"><?php _e('A new update is available for some of Pmpr components, please install them.', PR__PLG__PMPR) ?></p>
                        <?php $HTMLHelper->renderModal([
                            Constants::TITLE   => __('Update Components', PR__PLG__PMPR),
                            Constants::PREFIX  => 'update_components',
                            Constants::CONTENT =>
                                $HTMLHelper->createDiv(__('During the installation of updates, the site maintenance mode is temporarily activated and after the installation is finished, the site will be available as before.', PR__PLG__PMPR), ['class' => 'mb-2'])
                                . $HTMLHelper->createStrong(__('If site traffic is high, schedule updates for midnight.', PR__PLG__PMPR), ['class' => 'm-0'])
                            ,
                            Constants::BUTTONS => [
                                'update_later' => $HTMLHelper->createAction([
                                    Constants::TITLE => __('Planning for midnight', PR__PLG__PMPR),
                                    Constants::JOB   => 'update_later',
                                    Constants::ATTRS => [
                                        'class' => 'pr-btn btn-primary',
                                    ],
                                ]),
                                'update_now'   => $HTMLHelper->createAction([
                                    Constants::TITLE => __('Install right now', PR__PLG__PMPR),
                                    Constants::JOB   => 'update_now',
                                    Constants::ATTRS => [
                                        'class' => 'pr-btn btn-secondary',
                                    ],
                                ]),
                            ],
                        ], __('Update Components', PR__PLG__PMPR), [
                            'class' => 'pr-btn btn-primary update-components',
                        ]); ?>
                        <?php $HTMLHelper->renderHTML($checkUpdate); ?>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="mb-3"><?php _e('All components are up to date, you can check new update by below button.', PR__PLG__PMPR) ?></p>
                    <?php $HTMLHelper->renderHTML($checkUpdate); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>


    <div class="upgrade">
        <?php
        foreach ($types as $type => $args) {

            if ($args['count'] > 0) {

                $typeTitle = sprintf('%s <span class="count">(%s)</span>', $args['title'], $args['count']);
            } else {

                $typeTitle = $args['title'];
            }

            $HTMLHelper->renderElement('h2', [], $typeTitle);

            $updates = $args['update'] ?? [];

            if ($updates) {

                $table->prepare_items($updates);
                $table->display();
            } else {

                $HTMLHelper->renderElement('p', [], sprintf($masks['updated'], $args['title']));
            }
        }
        ?>
    </div>
</div>

