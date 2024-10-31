<?php

if (!defined('ABSPATH')) {
	exit;
}

$HTMLHelper = pmpr_get_plugin_object()->getHelper()->getHTML();
$typeHelper = pmpr_get_plugin_object()->getHelper()->getType();

?>

<?php if (isset($description) && $description): ?>
	<div class="pb-5 main-content"><?php $HTMLHelper->renderHTML(wpautop($description)) ?></div>
<?php endif; ?>

<?php if (isset($items) && $items): ?>
	<div class="row">
		<?php foreach ($items as $item): ?>
			<div class="col-12 col-md-6" id="<?php echo esc_attr($typeHelper->arrayGetItem($item, 'name')); ?>">
				<table class="table compare-table">
					<tbody>
					<tr>
						<td style="border: none"></td>
						<th colspan="2" class="text-center bg-gray"><?php esc_html_e('Products', PR__PLG__PMPR) ?></th>
					</tr>
					<tr>
						<th style="border: none"></th>
						<?php foreach ($typeHelper->arrayGetItem($item, 'columns') as $column => $title): ?>
							<th id="<?php echo esc_attr($typeHelper->arrayGetItem($item, 'name')); ?>-<?php echo esc_attr($column); ?>"
								class="text-center bg-gray-light"
								style="font-size: 1rem"><?php esc_html_e($title); ?></th>
						<?php endforeach; ?>
					</tr>
					<?php foreach ($typeHelper->arrayGetItem($item, 'rows') as $row): ?>
						<tr>
							<td class="bg-gray-light"><?php esc_html_e($row->title); ?></td>
							<?php foreach ($typeHelper->arrayGetItem($item, 'columns') as $column => $title): ?>
								<td class="text-center bg-white">
                                    <?php $data = $typeHelper->arrayGetItem($row, 'data'); ?>
									<?php if ($typeHelper->arrayGetItem($data, $column) === 'yes'): ?>
										<span class="dashicons dashicons-yes-alt text-success"></span>
									<?php else: ?>
										<span class="dashicons dashicons-dismiss text-danger"></span>
									<?php endif; ?>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
