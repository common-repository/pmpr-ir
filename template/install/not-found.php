<?php

if (!defined('ABSPATH')) {
	exit;
}

if (isset($error) && $error) { ?>
    <div class="inline error"><p><?php echo esc_html($error->get_error_message()); ?></p>
        <p class="hide-if-no-js">
            <button class="button try-again"><?php _e('Try Again', PR__PLG__PMPR); ?></button>
        </p>
    </div>
<?php } else if (isset($not_found)) { ?>
    <div class="no-plugin-results">
		<?php pmpr_get_plugin_object()->getHelper()->getHTML()->renderHTML($not_found); ?>
    </div>
	<?php
}
