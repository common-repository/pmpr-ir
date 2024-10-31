<?php

$HTMLHelper = pmpr_get_plugin_object()->getHelper()->getHTML();

?>
<div class="wrap">

    <?php $HTMLHelper->renderElement('h1', ['class' => 'wp-heading-inline'],
        $title . $HTMLHelper->createSpan(!empty($_GET['search']) ? __('&hellip;') : count($themes), ['class' => 'title-count theme-count'])
    ); ?>

    <?php $object->afterPageTitle(); ?>

    <hr class="wp-header-end">
</div>

