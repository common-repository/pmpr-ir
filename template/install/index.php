<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('install_plugins')) {

    wp_die(sprintf(__('Sorry, you are not allowed to install %s on this site.'), $title));
}

if (!in_array($tab, ['upload', 'uploading'])) {

    $wp_http_referer = sanitize_text_field($_REQUEST['_wp_http_referer'] ?? '');
    if (isset($wp_http_referer) && $wp_http_referer) {

        $paged       = sanitize_text_field($_REQUEST['paged'] ?? '');
        $request_uri = sanitize_text_field($_REQUEST['REQUEST_URI'] ?? '');
        $location    = remove_query_arg('_wp_http_referer', wp_unslash($request_uri));

        if (!empty($paged)) {
            $location = add_query_arg('paged', (int)$paged, $location);
        }

        wp_redirect($location);
        exit;
    }

    $total   = $table->get_pagination_arg('total_pages');
    $pagenum = $table->get_pagenum();

    if ($pagenum > $total && $total > 0) {

        wp_redirect(add_query_arg('paged', $total));
        exit;
    }
}

$title = esc_html($title);

/**
 * WordPress Administration Template Header.
 */
require_once ABSPATH . 'wp-admin/admin-header.php';
?>
    <div class="wrap component-install <?php echo esc_attr("plugin-install-tab-$tab"); ?>">
        <h1 class="wp-heading-inline">
            <?php echo esc_html($title); ?>
        </h1>

        <?php
        if (isset($component)) {

            $component->compareNotice();
        }
        ?>
        <hr class="wp-header-end">
        <?php if ('uploading' === $tab): ?>
            <?php $object->uploadProcess(); ?>
        <?php elseif ('upload' === $tab): ?>
            <div class="upload-component-wrap">
                <div class="upload-component upload-plugin">
                    <p class="install-help"><?php printf(__('If you have a %s in a .zip format, you may install or update it by uploading it here.', PR__PLG__PMPR), esc_html($args['singular_name'] ?? '')); ?></p>
                    <form method="post" enctype="multipart/form-data" class="wp-upload-form"
                          action="<?php echo esc_url(add_query_arg(['action' => 'upload-component', 'tab' => 'uploading'], $current_url)); ?>">
                        <?php wp_nonce_field('component-upload'); ?>
                        <label class="screen-reader-text" for="componentzip">
                            <?php printf(__('%s zip file', PR__PLG__PMPR), esc_html($args['singular_name'] ?? '')); ?>
                        </label>
                        <input type="file" id="componentzip" name="componentzip" accept=".zip"/>
                        <input type="hidden" id="type" name="type"
                               value="<?php echo esc_attr($args['type'] ?? ''); ?>"/>
                        <?php submit_button(_x('Install Now', 'component', PR__PLG__PMPR), '', 'install-component-submit', false); ?>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <?php $table->prepare_items(); ?>
            <?php $table->views(); ?>
            <form id="plugin-filter" method="post">
                <?php $table->display(); ?>
            </form>
        <?php endif; ?>
        <span class="spinner"></span>
    </div>

<?php
wp_print_admin_notice_templates();

/**
 * WordPress Administration Template Footer.
 */
require_once ABSPATH . 'wp-admin/admin-footer.php';
