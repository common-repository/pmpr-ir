<?php

if (!defined('ABSPATH')) {
    exit;
}

if (isset($tels, $whatsapp, $address)) {

    $HTMLHelper = pmpr_get_plugin_object()->getHelper()->getHTML();

    foreach ($tels as $tel) {

        $title = $tel['title'];
        $value = $tel['value'];
        $HTMLHelper->renderElement('p', ['class' => 'mb-2'], [
            $HTMLHelper->createSpan($title),
            $HTMLHelper->createElement('a', ['href' => "tel:{$value}"], $value),
        ]);
    }
}