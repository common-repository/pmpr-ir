<?php
if (!defined('ABSPATH')) {
	exit;
}

$which = $which ?? '';
$isTop = 'top' === $which;
?>

<?php !$isTop ?: wp_referer_field() ?>
<div class="tablenav <?php echo esc_attr($which) ?>">
	<?php if ($isTop): ?>
        <div class="alignleft actions">
			<?php
			/**
			 * Fires before the Plugin Install table header pagination is displayed.
			 *
			 * @since 2.7.0
			 */
			do_action('install_plugins_table_header');
			?>
        </div>
	<?php endif; ?>
	<?php $table->pagination($which); ?>
    <br class="clear"/>
</div>

