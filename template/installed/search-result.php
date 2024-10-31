<?php
if (!defined('ABSPATH')) {
	exit;
}

if (isset($s) && strlen($s)) {

    $HTMLHelper = pmpr_get_plugin_object()->getHelper()->getHTML();

	$content = sprintf(
		__('Search results for: %s', PR__PLG__PMPR),
        $HTMLHelper->createStrong(urldecode($s))
	);

    $HTMLHelper->renderElement('span', [
		'class' => 'subtitle',
	], $content);
}
