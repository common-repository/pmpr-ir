<?php

if (!defined('ABSPATH')) {
	exit;
}

if (isset($font)) {
	?>

    <style id="pmpr-admin-font">
        .ab-label,
        .ui-widget,
        .media-modal,
        .ui-widget button,
        .media-frame select,
        .media-frame, label,
        .media-frame .search,
        .media-frame textarea,
        h1, h2, h3, h4, h5, h6,
        .datepicker-plot-area,
        .media-frame, .media-modal,
        .media-frame input[type=url],
        .media-frame input[type=tel],
        .media-frame input[type=text],
        .media-frame input[type=email],
        .media-frame input[type=number],
        .media-frame input[type=search],
        .media-frame input[type=password],
        body, input, textarea, select, option, .pr-modal,
        #adminmenuwrap *, #wpadminbar *:not(.ab-icon) {

            font-family: <?php echo esc_html($font) ?>, Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif !important;
            font-weight: normal;
        }
    </style>
	<?php
}