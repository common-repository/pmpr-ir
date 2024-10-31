<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can(apply_filters('pmpr_menu_capability', 'activate_plugins'))) {

    wp_die(sprintf(__('Sorry, you are not allowed to manage %s for this site.', PR__PLG__PMPR), $title));
}

$pagenum = $table->get_pagenum();
$action  = $table->current_action();

?>

<div class="wrap component-installed">
    <?php

    pmpr_get_plugin_object()->getHelper()->getHTML()->renderElement('h1', ['class' => 'wp-heading-inline'], $title);

    $object->afterPageTitle();

    if (isset($component)) {

        $component->compareNotice();
    }

    $table->search_result();
    ?>

    <hr class="wp-header-end">

    <?php $table->views(); ?>

    <form class="search-form search-plugins search-components" method="get">
        <?php $table->search_box(sprintf(__('Search Installed %s', PR__PLG__PMPR), $table->getArg('singular_name')), PR__PLG__PMPR); ?>
    </form>

    <form method="post" id="bulk-action-form">

        <input type="hidden" name="plugin_status" value="<?php echo esc_attr($status); ?>"/>
        <input type="hidden" name="paged" value="<?php echo esc_attr($pagenum); ?>"/>

        <?php $table->display(); ?>
    </form>
    <span class="spinner"></span>
</div>
