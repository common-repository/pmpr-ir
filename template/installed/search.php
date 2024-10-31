<?php
if (!defined('ABSPATH')) {
	exit;
}

if (isset($args) && is_array($args)) {

	foreach ($args as $key => $value) {

        if ($value) {

			?><input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>"/><?php
		}
	}
}
?>
<p class="search-box">
    <label class="components-search-input" for="<?php echo esc_attr($input_id); ?>"><?php echo esc_html($text) ?></label>
    <input type="search" id="<?php echo esc_attr($input_id); ?>" data-type="<?php echo esc_attr($type); ?>"
           class="wp-filter-search installed-components-search" name="s" value="<?php _admin_search_query(); ?>"/>
	<?php submit_button($text, 'hide-if-js', '', false, ['id' => 'search-submit']); ?>
</p>
