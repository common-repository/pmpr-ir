<?php

namespace Pmpr\Plugin\Pmpr\Interfaces;

/**
 * Interface Constants
 * @package Pmpr\Plugin\Pmpr\Interfaces
 */
interface Constants
{
    const APIKEY_LENGTH = 32;
    const PLUGIN_PREFIX = 'pmpr-plg-';

    const COMPONENTS_ACTION_RUNNING       = self::PLUGIN_PREFIX . 'component_action_running';
    const BACKGROUND_PROCESS_RUNNING      = self::PLUGIN_PREFIX . '_install_commons_running';
    const FETCHED_REQUIRED_COMPONENTS     = self::PLUGIN_PREFIX . '_fetched_required_components';
    const FETCH_COMMON_COMPONENTS_RUNNING = self::FETCHED_REQUIRED_COMPONENTS . '_running';

    const CONNECTION_AUTH_KEY = self::PLUGIN_PREFIX . 'connection_auth_key';

    // outputs
    const ARRAY  = 'array';
    const OBJECT = 'object';

    // statuses
    const DONE          = 'done';
    const FAILED        = 'failed';
    const ACTIVE        = 'active';
    const RUNNING       = 'running';
    const PENDING       = 'pending';
    const INACTIVE      = 'inactive';
    const COMPLETED     = 'completed';
    const IN_PROGRESS   = 'in-progress';

    // keys
    const ID               = 'id';
    const IDS              = 'ids';
    const IPS              = 'ips';
    const ALL              = 'all';
    const TAB              = 'tab';
    const VER              = 'ver';
    const JOB              = 'job';
    const URL              = 'url';
    const HOOK             = 'hook';
    const ECHO             = 'echo';
    const PAGE             = 'page';
    const TYPE             = 'type';
    const TEXT             = 'text';
    const SHOW             = 'show';
    const SLUG             = 'slug';
    const NAME             = 'name';
    const JSON             = 'json';
    const SIZE             = 'size';
    const VIEW             = 'view';
    const VALUE            = 'value';
    const IMAGE            = 'image';
    const TITLE            = 'title';
    const COUNT            = 'count';
    const ATTRS            = 'attrs';
    const STATE            = 'state';
    const COLOR            = 'color';
    const ENABLE           = 'enable';
    const PREFIX           = 'prefix';
    const STATUS           = 'status';
    const FIELDS           = 'fields';
    const RATING           = 'rating';
    const LOCALE           = 'locale';
    const CANCEL           = 'cancel';
    const BUTTON           = 'button';
    const API_KEY          = 'api_key';
    const BUTTONS          = 'buttons';
    const MESSAGE          = 'message';
    const REFRESH          = 'refresh';
    const PRIVATE          = 'private';
    const REQUIRE          = 'require';
    const LOADING          = 'loading';
    const SPINNER          = 'spinner';
    const CONTEXT          = 'context';
    const CONTENT          = 'content';
    const SIDEBAR          = 'sidebar';
    const VERSION          = 'version';
    const ELEMENT          = 'element';
    const REQUIRED         = 'required';
    const TEMPLATE         = 'template';
    const PER_PAGE         = 'per_page';
    const CALLBACK         = 'callback';
    const PRIORITY         = 'priority';
    const POSITION         = 'position';
    const NO_SUBMIT        = 'no_submit';
    const MENU_SLUG        = 'menu_slug';
    const PERMALINK        = 'permalink';
    const NOT_FOUND        = 'not_found';
    const MENU_TITLE       = 'menu_title';
    const NEW_VERSION      = 'new_version';
    const DESCRIPTION      = 'description';
    const AUTO_UPDATE      = 'auto_update';
    const PLURAL_NAME      = 'plural_name';
    const SINGULAR_NAME    = 'singular_name';
    const ALTERNATIVE_NAME = 'alternative_name';

    // modal and toggle
    const ACTION        = 'action';
    const DISMISS       = 'dismiss';
    const MODAL_STEPS   = 'modal_steps';

    // types
    const INFO      = 'info';
    const ERROR     = 'error';
    const DANGER    = 'danger';
    const PRIMARY   = 'primary';
    const WARNINIG  = 'warning';

    // sizes
    const SM = 'sm';
    const MD = 'md';
    const LG = 'lg';
    const XL = 'xl';

    // actions
    const UPDATE = 'update';
    const REMOVE = 'remove';

    // components full name
    const COMPONENT = 'component';
    const UTILITY   = 'utility';
    const COMMON    = 'common';
    const CUSTOM    = 'custom';
    const MODULE    = 'module';
    const COVER     = 'cover';

    // components short name
    const UTL = 'utl';
    const CMN = 'cmn';
    const CST = 'cst';
    const MDL = 'mdl';
    const CVR = 'cvr';

    // tabs
    const SEARCH    = 'search';
    const GENERAL   = 'general';
    const DEDICATED = 'dedicated';

    // contexts
    const INSTALL   = 'install';
    const INSTALLED = 'installed';

    // rest
    const ARGS                = 'args';
    const METHODS             = 'methods';
    const PERMISSION_CALLBACK = 'permission_callback';

    const UPDATE_COUNT                 = self::PLUGIN_PREFIX . 'update_count';
    const COMMONS_INSTALLATION_PROBLEM = 'commons_installation_problem';
}