<?php

use Pmpr\Plugin\Pmpr\Interfaces\Constants;

if (!defined('ABSPATH')) {
    exit;
}

$HTMLHelper  = pmpr_get_plugin_object()->getHelper()->getHTML();
$assetHelper = pmpr_get_plugin_object()->getHelper()->getAsset();

?>
<div class="pmpr-modal pmpr-modal-<?php echo esc_attr($type ?? 'default') ?>"
     id="<?php echo esc_attr($id ?? '') ?>">
    <div class="pmpr-modal-overlay"></div>
    <div class="pmpr-modal-container pmpr-modal-<?php echo esc_attr($name ?? '') ?>">
        <div class="pmpr-modal-frame">
            <div class="pmpr-modal-header">
                <span class="pmpr-modal-button close">&times;</span>
                <?php if (isset($modal_title) && $modal_title): ?>
                    <strong class="pmpr-modal-title"><?php echo esc_html($modal_title) ?></strong>
                <?php endif; ?>
            </div>

            <div class="pmpr-modal-body">
                <?php if (isset($modal_steps) && $modal_steps): ?>
                    <?php $index = 1; ?>
                    <?php foreach ($modal_steps as $key => $step): ?>
                        <div id="<?php echo esc_attr($key) ?>" data-step="<?php echo esc_attr($index); ?>"
                             class="pmpr-modal-step<?php echo esc_attr($index === 1 ? '' : ' show'); ?>">

                            <?php

                            if (is_string($step)) {

                                $HTMLHelper->renderHTML($step);
                            } else if (is_callable($step)) {

                                $step($key, $index);
                            } else if (is_array($step)) {

                                $template = $step[Constants::TEMPLATE] ?? '';
                                if ($template) {

                                    $content = $HTMLHelper->renderTemplate($template, [], false);
                                } else {

                                    $content = $step[Constants::CONTENT] ?? '';
                                }

                                $image = $step[Constants::IMAGE] ?? '';
                                if ($image) {

                                    $HTMLHelper->renderElement('img', [
                                        'alt'    => sprintf(__('Image of %s', PR__PLG__PMPR), $key),
                                        'src'    => $assetHelper->getURL($image),
                                        'class'  => 'pmpr-modal-step-image',
                                        'height' => 220,
                                    ]);
                                }

                                $title = $step[Constants::TITLE] ?? '';
                                if ($title) {

                                    $HTMLHelper->renderElement('h2', [
                                        'class' => 'pmpr-modal-step-title',
                                    ], $title);
                                }

                                $HTMLHelper->renderElement('div', [
                                    'class' => 'pmpr-modal-step-content',
                                ], $content);
                            }
                            ?>

                        </div>
                        <?php $index++; ?>
                    <?php endforeach; ?>

                <?php else: ?>
                    <div class="pmpr-modal-content show">
                        <?php $HTMLHelper->renderHTML($modal_content ?? ''); ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (isset($modal_buttons)): ?>
                <div class="pmpr-modal-footer">
                    <?php foreach ($modal_buttons ?? [] as $button) {

                        $HTMLHelper->renderHTML($button);
                    } ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
