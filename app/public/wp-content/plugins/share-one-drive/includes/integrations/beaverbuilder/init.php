<?php

namespace TheLion\ShareoneDrive\Integrations;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * A class that handles loading custom modules and custom
 * fields if the builder is installed and activated.
 */
class FL_Init
{
    public $slug = 'wpcp_shareonedrive';

    public function __construct()
    {
        // Load custom modules.
        add_action('init', [$this, 'load_modules']);

        // Register custom fields.
        add_filter('fl_builder_custom_fields', [$this, 'register_fields']);

        // Enqueue custom field assets.
        add_action('init', [$this, 'enqueue_field_assets']);
    }

    /**
     * Loads our custom modules.
     */
    public function load_modules()
    {
        require_once SHAREONEDRIVE_ROOTDIR.'/includes/integrations/beaverbuilder/modules/wpcp_shareonedrive_module/wpcp_shareonedrive_module.php';
    }

    /**
     * Registers our custom fields.
     *
     * @param mixed $fields
     */
    public function register_fields($fields)
    {
        $fields[$this->slug] = SHAREONEDRIVE_ROOTDIR.'/includes/integrations/beaverbuilder/fields/field.php';

        return $fields;
    }

    /**
     * Enqueues our custom field assets only if the builder UI is active.
     */
    public function enqueue_field_assets()
    {
        if (!\FLBuilderModel::is_builder_active()) {
            return;
        }
    }
}

new FL_Init();
