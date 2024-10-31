<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!isset($search_label, $search_placeholder)) {

    return;
}

$serverHelper = pmpr_get_plugin_object()->getHelper()->getServer();

$term = wp_unslash($serverHelper->getRequest('s', ''));
$term = esc_attr($term);

?>
<form class="search-form search-plugins search-components" method="get">
    <?php
    if (isset($args) && is_array($args)) {

        foreach ($args as $key => $value) {

            if ($value) {

                ?><input type="hidden" name="<?php echo esc_attr($key); ?>"
                         value="<?php echo esc_attr($value); ?>"/><?php
            }
        }
    }
    ?>
    <label for="search-component"><?php echo esc_html($search_label); ?></label>
    <input type="search" name="s" id="search-component" value="<?php echo esc_attr($term); ?>"
           class="wp-filter-search available-components-search"/>
    <?php submit_button(esc_html($search_label), 'hide-if-js', false, false, ['id' => 'search-submit']); ?>
</form>