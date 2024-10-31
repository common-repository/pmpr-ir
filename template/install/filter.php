<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!empty($views)) {

    $HTMLHelper = pmpr_get_plugin_object()->getHelper()->getHTML();

	foreach ($views as $class => $view) {


		$views[$class] = $HTMLHelper->createElement('li', ['class' => $class], $view);
	}
    $HTMLHelper->renderElement('ul', ['class' => 'filter-links'], implode($views));
}
