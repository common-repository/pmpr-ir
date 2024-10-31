<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wp-list-table <?php echo esc_attr(implode(' ', $table->get_table_classes())); ?>">
	<?php $table->screen->render_screen_reader_content('heading_list'); ?>
    <div id="the-list"<?php echo esc_html($data_attr); ?>>
		<?php $table->display_rows_or_placeholder(); ?>
    </div>
</div>
