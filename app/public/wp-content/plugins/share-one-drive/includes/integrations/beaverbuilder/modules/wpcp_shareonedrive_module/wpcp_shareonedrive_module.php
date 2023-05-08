<?php

namespace TheLion\ShareoneDrive\Integrations;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class FL_WPCP_ShareoneDrive_Module extends \FLBuilderModule
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'OneDrive/SharePoint',
            'description' => sprintf(\esc_html__('Insert your %s content', 'wpcloudplugins'), 'OneDrive/SharePoint'),
            'category' => 'WP Cloud Plugins',
            'dir' => SHAREONEDRIVE_ROOTDIR.'/includes/integrations/beaverbuilder/modules/wpcp_shareonedrive_module/',
            'url' => SHAREONEDRIVE_ROOTPATH.'/includes/integrations/beaverbuilder/modules/wpcp_shareonedrive_module/',
            'icon' => SHAREONEDRIVE_ROOTDIR.'/css/images/onedrive_logo.svg',
        ]);
    }

    public function get_icon($icon = '')
    {
        return file_get_contents($icon);
    }
}

// Register the module and its form settings.
\FLBuilder::register_module('\TheLion\ShareoneDrive\Integrations\FL_WPCP_ShareoneDrive_Module', [
    'general' => [ // Tab
        'title' => esc_html__('General'), // Tab title
        'sections' => [ // Tab Sections
            'general' => [ // Section
                'title' => esc_html__('Module configuration', 'wpcloudplugins'), // Section Title
                'fields' => [ // Section Fields
                    'raw_shortcode' => [
                        'type' => 'wpcp_shareonedrive',
                        'label' => esc_html__('Raw shortcode', 'wpcloudplugins'),
                        'default' => '[shareonedrive mode="files"]',
                    ],
                ],
            ],
        ],
    ],
]);
