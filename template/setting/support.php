<?php

if (!defined('ABSPATH')) {
	exit;
}

$HTMLHelper  = pmpr_get_plugin_object()->getHelper()->getHTML();

if (isset($text, $items)) {
    $HTMLHelper->renderElement('p', ['class' => 'mb-3'], $text);
	$html = '';
	foreach ($items as $index => $item) {

		$index = number_format_i18n($index);
		$html  .= $HTMLHelper->createElement('li', ['class' => 'mb-2'], "{$index}. {$item}");
	}
    $HTMLHelper->renderElement('ul', ['class' => 'unstyle-list'], $html);
}