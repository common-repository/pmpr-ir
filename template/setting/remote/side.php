<?php

if (!defined('ABSPATH')) {
	exit;
}

$HTMLHelper = pmpr_get_plugin_object()->getHelper()->getHTML();
$typeHelper = pmpr_get_plugin_object()->getHelper()->getType();

?>
<?php if (isset($logo) && $logo): ?>

    <a href="<?php echo esc_attr($url ?? ''); ?>" class="d-block my-4" target="_blank">
        <img src="<?php echo esc_attr($logo); ?>" style="width: 100%; max-width: 350px; height: auto"
             alt="<?php esc_attr_e('Pmpr Logo', PR__PLG__PMPR); ?>">
    </a>
<?php endif; ?>

<?php if (isset($items) && $items): ?>
	<?php if (isset($title) && $title): ?>
        <h3><?php echo esc_html($title) ?></h3>
	<?php endif; ?>
	<?php foreach ($items as $item): ?>
        <div class="d-flex mb-2">
            <img src="<?php echo esc_attr($typeHelper->arrayGetItem($item, 'icon')); ?>"
                 width="30" height="30" alt="<?php esc_attr_e('Badge Icon', PR__PLG__PMPR); ?>">
			<?php
			$url   = $typeHelper->arrayGetItem($item, 'url');
			$attrs = [
				'class'  => 'text-decoration-none ml-3 my-auto',
				'style'  => 'font-size: 14px',
				'target' => '_blank',
			];
			if ($url) {

				$attrs['href'] = $url;
				$element       = 'a';
			} else {

				$element = 'span';
			}
            $HTMLHelper->renderElement($element, $attrs, $typeHelper->arrayGetItem($item, 'title'))
			?>
        </div>
	<?php endforeach; ?>
<?php endif; ?>

<?php if (isset($stackoverflow) && $stackoverflow): ?>

	<?php
	$data   = $typeHelper->arrayGetItem($stackoverflow, 'data', []);
	$badges = $typeHelper->arrayGetItem($data, 'badges');
	$colors = [
		'gold'   => 'gold',
		'silver' => 'silver',
		'bronze' => '#b08d57',
	];
	?>
    <a href="<?php echo esc_attr($typeHelper->arrayGetItem($stackoverflow, 'url')); ?>" target="_blank"
       class="d-block my-4 text-decoration-none direction-ltr bg-white" style="border-radius: 5px; overflow: hidden;">
        <div class="d-flex">
            <img src="<?php echo esc_attr($typeHelper->arrayGetItem($stackoverflow, 'image')); ?>"
                 alt="<?php esc_attr_e('Stackoverflow Image', PR__PLG__PMPR); ?>"
                 width="75" height="75" style="background-color: #e3e3e3">
            <div class="mr-3">
                <h3 class="mb-2"><?php echo esc_html($typeHelper->arrayGetItem($stackoverflow, 'title')) ?></h3>
                <span style="font-size: 1rem; font-weight: bold"
                      class="ml-2"><?php echo esc_html($typeHelper->arrayGetItem($data, 'reputation')) ?></span>
				<?php foreach ($colors as $key => $color): ?>
                    <span style="font-size: 1rem" class="ml-1">
                        <span style="color: <?php echo esc_attr($color); ?>; font-size: 1.25rem">&#9679;</span>
                        <?php echo esc_html($typeHelper->arrayGetItem($badges, $key)); ?>
                    </span>
				<?php endforeach; ?>
            </div>
        </div>
    </a>
<?php endif; ?>
