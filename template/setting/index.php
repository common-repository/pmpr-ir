<?php

if (!defined('ABSPATH')) {
    exit;
}

$HTMLHelper = pmpr_get_plugin_object()->getHelper()->getHTML();
$typeHelper = pmpr_get_plugin_object()->getHelper()->getType();

?>
<div class="wrap pmpr-setting">
    <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
    <hr class="wp-header-end">
    <?php
    if (isset($id, $tabs, $current)
        && $id && $tabs && $current) {

        $html = '';
        foreach ($tabs as $key => $tab) {

            $url       = '#';
            $isCurrent = ($current['id'] ?? '') == $key;
            if (!$isCurrent) {

                $url = admin_url("admin.php?page={$id}&tab={$key}");
            }
            $html .= $HTMLHelper->createElement('a', [
                'href'  => $url,
                'class' => 'nav-tab' . ($isCurrent ? ' nav-tab-active' : ''),
            ], $tab['title']);
        }

        $HTMLHelper->renderElement('nav', ['class' => 'nav-tab-wrapper'], $html);

        $sidebar = $typeHelper->arrayGetItem($current, 'sidebar', false);
        if ($sidebar) {

            ?>
            <div id="poststuff">
            <div id="post-body" class="columns-2">
            <div id="postbox-container-1" class="postbox-container">
                <div class="sidebar">
                    <?php
                    if (is_callable($sidebar)) {

                        $sidebar();
                    } else if (is_string($sidebar)) {

                        $HTMLHelper->renderHTML($sidebar);
                    }
                    ?>
                </div>
            </div>
            <div id="postbox-container-2" class="postbox-container">
            <?php
        }
        ?>
        <form method="post" id="<?php echo esc_attr($id); ?>">
            <input type="hidden" name="tab" value="<?php echo esc_attr($current['id']); ?>"/>
            <input type="hidden" name="option_page" value="<?php echo esc_attr($id); ?>"/>
            <?php wp_nonce_field($id); ?>
            <table class="form-table" role="presentation">
                <tbody>
                <?php foreach ($current['fields'] as $name => $field): ?>
                    <?php if (is_array($field) && $field
                        && (!isset($field['show']) || $field['show'])): ?>
                        <tr>
                            <?php if (isset($field['label'])): ?>
                                <th scope="row">
                                    <label for="<?php echo esc_attr($name) ?>">
                                        <?php echo esc_html($field['label']); ?>
                                    </label>
                                </th>
                            <?php endif; ?>
                            <td>
                                <?php
                                if (isset($field['html'])) {

                                    $html = $field['html'];
                                    if (is_callable($html)) {

                                        $html = $html();
                                    }
                                    $HTMLHelper->renderHTML($html);
                                } else {

                                    $type     = $field['type'];
                                    $value    = $field['value'] ?? '';
                                    $class    = $field['class'] ?? '';
                                    $disabled = $field['disabled'] ?? false;

                                    $content = '';
                                    $attrs   = [
                                        'id'    => $name,
                                        'name'  => $name,
                                        'class' => 'regular-text' . ($class ? " {$class}" : ''),
                                    ];
                                    if ($disabled) {

                                        $attrs['disabled'] = '';
                                    }
                                    $element = 'input';
                                    switch ($type) {
                                        case 'text':
                                        case 'number':
                                        case 'password':

                                            $attrs['type']  = $type;
                                            $attrs['value'] = $value;
                                            break;
                                        case 'action':
                                            $element             = 'a';
                                            $content             = $field['label'] ?? '';
                                            $color               = $field['color'] ?? 'default';
                                            $attrs['href']       = '#';
                                            $attrs['class']      = "button button-{$color} pr-ajax-action";
                                            $attrs['data-nonce'] = wp_create_nonce(PR__PLG__PMPR . '_nonce_action');
                                            break;
                                        case 'select':

                                            $element = 'select';
                                            $options = $typeHelper->arrayGetItem($field, 'options');
                                            if ($options && is_array($options)) {

                                                foreach ($options as $option => $text) {

                                                    $optionAttr = ['value' => $option];
                                                    if ($option == $value) {

                                                        $optionAttr['selected'] = '';
                                                    }
                                                    $content .= $HTMLHelper->createElement('option', $optionAttr, $text);
                                                }
                                            }
                                            break;
                                    }
                                    $HTMLHelper->renderElement($element, $attrs, $content);
                                    if (isset($field['after'])) {

                                        $HTMLHelper->renderHTML($field['after']);
                                    }
                                    if (isset($field['desc'])
                                        && $field['desc']) {

                                        $HTMLHelper->renderElement('p', [
                                            'id'    => "{$name}_description",
                                            'class' => 'description',
                                        ], $field['desc']);
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            if (!isset($current['no_submit'])
                || !$current['no_submit']) {

                submit_button();
            }
            ?>
        </form>
        <?php
        if ($sidebar) {
            ?>
            </div>
            </div>
            </div>
            <?php
        }
    }
    ?>
</div>
