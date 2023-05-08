<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\ShareoneDrive;

class ShortcodeBuilder
{
    public static $nav_tabs = [];
    public static $fields = [];

    /**
     * The single instance of the class.
     *
     * @var ShortcodeBuilder
     */
    protected static $_instance;

    public function __construct()
    {
        $this->load_nav_tabs();
        $this->load_fields();
    }

    public function render()
    {
        add_action('wp_print_scripts', [$this, 'enqueue_scripts'], 1000);
        add_action('wp_print_styles', [$this, 'enqueue_styles'], 1000);

        // Count number of openings for rating dialog
        $counter = get_option('share_one_drive_shortcode_opened', 0) + 1;
        update_option('share_one_drive_shortcode_opened', $counter);

        include SHAREONEDRIVE_ROOTDIR.'/templates/admin/shortcode_builder.php';
    }

    public function enqueue_scripts()
    {
        // Add own styles and script and remove default ones
        global $wp_scripts;
        $wp_scripts->queue = [];

        wp_enqueue_script('jquery-effects-fade');
        wp_enqueue_script('ShareoneDrive');
        wp_enqueue_script('ShareoneDrive.AdminUI');
        wp_enqueue_script('ShareoneDrive.ShortcodeBuilder');

        // Build Whitelist for permission selection
        $vars = [
            'whitelist' => json_encode(Helpers::get_all_users_and_roles()),
            'ajax_url' => SHAREONEDRIVE_ADMIN_URL,
        ];

        wp_localize_script('ShareoneDrive.AdminUI', 'WPCloudplugin_AdminUI_vars', $vars);
    }

    public function enqueue_styles()
    {
        global $wp_styles;
        $wp_styles->queue = [];
        wp_enqueue_style('ShareoneDrive');
        wp_enqueue_style('WPCloudPlugins.AdminUI');
    }

    /**
     * ShortcodeBuilder Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return ShortcodeBuilder - ShortcodeBuilder instance
     *
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        return self::$_instance;
    }

    private function load_nav_tabs()
    {
        $nav_tabs = [
            'module' => [
                'title' => esc_html__('Module', 'wpcloudplugins'),
                'description' => '',
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>',
                'modules' => ['all'],
            ],
            'content' => [
                'title' => esc_html__('Content', 'wpcloudplugins'),
                'description' => '',
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" /></svg>',
                'modules' => ['all'],
            ],
            'private_folders' => [
                'title' => esc_html__('Dynamic Folders', 'wpcloudplugins'),
                'description' => '',
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>',
                'modules' => ['all'],
            ],
            'actions' => [
                'title' => esc_html__('Actions', 'wpcloudplugins'),
                'description' => sprintf(esc_html__('Select via %s which User Roles are able to perform the actions', 'wpcloudplugins'), '<a href="#" onclick="">'.esc_html__('User Permissions', 'wpcloudplugins').'</a>'),
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>',
                'modules' => ['all'],
            ],
            'layout' => [
                'title' => esc_html__('Layout', 'wpcloudplugins'),
                'description' => '',
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>',
                'modules' => ['all'],
            ],
            'sorting' => [
                'title' => esc_html__('Sort Order', 'wpcloudplugins'),
                'description' => '',
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4" /></svg>',
                'modules' => ['files', 'gallery', 'audio', 'video', 'search', 'carousel'],
            ],
            'filters' => [
                'title' => esc_html__('Filters', 'wpcloudplugins'),
                'description' => '',
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>',
                'modules' => ['files', 'gallery', 'audio', 'video', 'search', 'carousel'],
            ],
            'upload' => [
                'title' => esc_html__('Upload Settings', 'wpcloudplugins'),
                'description' => '',
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>',
                'modules' => ['files', 'gallery', 'uploads'],
            ],
            'notifications' => [
                'title' => esc_html__('Notifications', 'wpcloudplugins'),
                'description' => '',
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>',
                'modules' => ['files', 'gallery', 'upload', 'search'], 'carousel',
            ],
        ];

        self::$nav_tabs = \apply_filters('shareonedrive_shortcodebuilder_tabs', $nav_tabs);
    }

    private function load_fields()
    {
        $fields = [];

        // Module fields
        $fields['module'] = [
            'module_panel' => [
                'title' => esc_html__('Module', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'accordion' => false,
                'modules' => ['all'],
                'fields' => [
                    'mode' => [
                        'title' => '',
                        'description' => esc_html__('Select which module you want to use to display your content.', 'wpcloudplugins'),
                        'default' => 'files',
                        'type' => 'radio_group',
                        'options' => [
                            'files' => ['title' => esc_html__('File Browser', 'wpcloudplugins'), 'imagesrc' => '', 'description' => 'Display all your content in a specific folder.'],
                            'upload' => ['title' => esc_html__('Upload Box', 'wpcloudplugins'), 'imagesrc' => '', 'description' => 'Let your users upload content to your cloud account.'],
                            'gallery' => ['title' => esc_html__('Gallery', 'wpcloudplugins'), 'imagesrc' => '', 'description' => esc_html__('Amazing gallery for images & videos.', 'wpcloudplugins').'<br/><small>'.sprintf(esc_html__('Supported formats: %s', 'wpcloudplugins'), 'gif, jpg, jpeg, png, bmp, mp4, m4v, ogg, ogv, webmv').'</small>'],
                            'carousel' => ['title' => esc_html__('Slider / Carousel', 'wpcloudplugins'), 'imagesrc' => '', 'description' => esc_html__('Slick slider for displaying images.', 'wpcloudplugins').'<br/><small>'.sprintf(esc_html__('Supported formats: %s', 'wpcloudplugins'), 'gif, jpg, jpeg, png, bmp').'</small>'],
                            'audio' => ['title' => esc_html__('Audio Player', 'wpcloudplugins'), 'imagesrc' => '', 'description' => esc_html__('Share your music.', 'wpcloudplugins').'<br/><small>'.sprintf(esc_html__('Supported formats: %s', 'wpcloudplugins'), 'mp3, m4a, ogg, oga, wav').'</small>'],
                            'video' => ['title' => esc_html__('Video Player', 'wpcloudplugins'), 'imagesrc' => '', 'description' => esc_html__('Stream your video files.', 'wpcloudplugins').'<br/><small>'.sprintf(esc_html__('Supported formats: %s', 'wpcloudplugins'), 'mp4, m4v, ogg, ogv, webm, webmv').'</small>'],
                            'search' => ['title' => esc_html__('Search Box', 'wpcloudplugins'), 'imagesrc' => '', 'description' => 'Search content via a search box.'],
                        ],
                        'modules' => ['all'],
                    ],
                ],
            ],
            'module_access_panel' => [
                'title' => esc_html__('Module Access', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'accordion' => false,
                'modules' => ['all'],
                'fields' => [
                    'viewrole' => [
                        'title' => esc_html__('Who can see this module?', 'wpcloudplugins'),
                        'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                        'default' => ['administrator', 'editor', 'author', 'contributor', 'subscriber', 'guest'],
                        'type' => 'user_selectbox',
                        'modules' => ['all'],
                    ],
                ],
            ],
        ];

        // Content fields
        $fields['content'] = [
            'content_panel' => [
                'title' => esc_html__('Content location', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'accordion' => false,
                'modules' => ['all'],
                'fields' => [
                    'singleaccount' => [
                        'title' => esc_html__('Point to specific cloud account', 'wpcloudplugins'),
                        'description' => esc_html__('Use a folder from one of the linked account. Disabling this option allows your users to navigate through the folders of all your linked cloud accounts.', 'wpcloudplugins'),
                        'default' => true,
                        'type' => 'checkbox',
                        'toggle_container' => '#content_full_panel',
                        'modules' => ['all'],
                    ],
                    'content_full_panel' => [
                        'title' => esc_html__('Content location', 'wpcloudplugins'),
                        'description' => '',
                        'type' => 'toggle_container',
                        'modules' => ['all'],
                        'fields' => [
                            'dir' => [
                                'title' => esc_html__('Select top folder', 'wpcloudplugins'),
                                'description' => esc_html__('Select which folder should be used as starting point, or in case the Smart Client Area is enabled should be used for the Private Folders.', 'wpcloudplugins'),
                                'default' => false,
                                'type' => 'folder_selectbox',
                                'shortcode_attr' => [
                                    'startid' => 'root',
                                    'popup' => 'shortcode_buider',
                                    'showfiles' => '1',
                                ],
                                'apply_backend_private_folder' => true,
                                'inline' => true,
                                'modules' => ['all'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // Private Folders fields
        $fields['private_folders'] = [
            'smartclient_panel' => [
                'title' => esc_html__('Dynamic Folders', 'wpcloudplugins'),
                'description' => esc_html__('Instead of using a static folder location, the module can also point to a dynamic folder location.', 'wpcloudplugins').' <a href="https://www.wpcloudplugins.com/wp-content/plugins/share-one-drive/_documentation/index.html#module-builder-options-dynamic-folder" target="_blank">('.esc_html__('Documentation', 'wpcloudplugins').')</a>',
                'type' => 'panel',
                'accordion' => false,
                'modules' => ['all'],
                'fields' => [
                    'userfolders' => [
                        'title' => esc_html__('Dynamic Mode', 'wpcloudplugins'),
                        'description' => esc_html__('Do you want to link your users manually to their Private Folder or should the plugin handle this automatically for you?', 'wpcloudplugins'),
                        'type' => 'radio_group',
                        'default' => 'off',
                        'options' => [
                            'off' => ['title' => esc_html__('Off'), 'description' => esc_html__('Use a static folder as select on the Content tab.', 'wpcloudplugins'), 'toggle_container' => '#private_folder_off_panel'],
                            'manual' => ['title' => esc_html__('Manual mode'), 'description' => sprintf(esc_html__('I will link the users manually via %sthis page%s.', 'wpcloudplugins'), '<a href="'.admin_url('admin.php?page=ShareoneDrive_settings_linkusers').'" target="_blank">', '</a>'), 'toggle_container' => '#private_folder_manual_panel'],
                            'auto' => ['title' => esc_html__('Auto mode'), 'description' => esc_html__('Let the plugin automatically manage the Private Folders for me in the folder that is selected on the Content tab.', 'wpcloudplugins'), 'toggle_container' => '#private_folder_auto_panel'],
                        ],
                        'modules' => ['all'],
                    ],
                    'private_folder_auto_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'indent' => true,
                        'modules' => ['all'],
                        'fields' => [
                            'userfoldernametemplate' => [
                                'title' => esc_html__('Name Template', 'wpcloudplugins'),
                                'description' => esc_html__('Template name for automatically created Private Folders.', 'wpcloudplugins').' '.esc_html__('Leave empty to use the value that is set globally.', 'wpcloudplugins'),
                                'default' => Core::get_setting('userfolder_name'),
                                'type' => 'textbox',
                                'notice' => sprintf(esc_html__('Available placeholders: %s', 'wpcloudplugins'), '').'<code>%user_login%</code>,  <code>%user_firstname%</code>, <code>%user_lastname%</code>, <code>%user_email%</code>, <code>%display_name%</code>, <code>%ID%</code>, <code>%user_role%</code>, <code>%usermeta_{key}%</code>, <code>%post_id%</code>, <code>%post_title%</code>, <code>%postmeta_{key}%</code>, <code>%acf_user_{field_name}%</code>, <code>%acf_post_{field_name}%</code>, <code>%date_{date_format}%</code>, <code>%yyyy-mm-dd%</code>, <code>%hh:mm%</code>, <code>%uniqueID%</code>, <code>%directory_separator% (/)</code>',
                                'notice_class' => 'info',
                                'modules' => ['all'],
                            ],
                            'use_usertemplatedir' => [
                                'title' => esc_html__('Use Template Folder', 'wpcloudplugins'),
                                'description' => esc_html__('Newly created Private Folders can be prefilled with files from a template. The content of the template folder selected will be copied to the user folder', 'wpcloudplugins'),
                                'type' => 'checkbox',
                                'default' => false,
                                'toggle_container' => '#private_folder_template_panel',
                                'modules' => ['all'],
                            ],
                            'private_folder_template_panel' => [
                                'title' => '',
                                'description' => '',
                                'type' => 'toggle_container',
                                'modules' => ['all'],
                                'fields' => [
                                    'usertemplatedir' => [
                                        'title' => '',
                                        'description' => '',
                                        'default' => false,
                                        'type' => 'folder_selectbox',
                                        'shortcode_attr' => [
                                            'startid' => 'root',
                                            'popup' => 'shortcode_buider',
                                            'showfiles' => '0',
                                        ],
                                        'apply_backend_private_folder' => true,
                                        'inline' => true,
                                        'modules' => ['all'],
                                    ],
                                ],
                            ],
                            'viewuserfoldersrole' => [
                                'title' => esc_html__('Full Access to all Private Folders', 'wpcloudplugins'),
                                'description' => esc_html__('By default only Administrator users will be able to navigate through all Private Folders. Add other user roles if they should be able to see all Private Folders as well.', 'wpcloudplugins'),
                                'default' => ['administrator'],
                                'type' => 'user_selectbox',
                                'modules' => ['all'],
                            ],
                            'maxuserfoldersize' => [
                                'title' => esc_html__('Quota', 'wpcloudplugins'),
                                'description' => esc_html__('Set maximum size of the User Folder (e.g. 10M, 100M, 1G). When the Upload function is enabled, the user will not be able to upload when the limit is reached.', 'wpcloudplugins').' '.esc_html__('Leave this field empty or set it to -1 for unlimited disk space.', 'wpcloudplugins'),
                                'default' => '',
                                'placeholder' => '10M, 100M, 1G',
                                'type' => 'textbox',
                                'modules' => ['files', 'gallery', 'upload'],
                            ],
                        ],
                    ],
                    'subfolder' => [
                        'title' => esc_html__('Open Subfolder', 'wpcloudplugins'),
                        'description' => esc_html__('Open a specific folder inside the selected folder. This can be useful in combination with the Private Folders feature. It allows you to create a Private Folder while provide access to a specific subfolder.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'textbox',
                        'notice' => sprintf(esc_html__('Available placeholders: %s', 'wpcloudplugins'), '').'<code>%user_login%</code>,  <code>%user_firstname%</code>, <code>%user_lastname%</code>, <code>%user_email%</code>, <code>%display_name%</code>, <code>%ID%</code>, <code>%user_role%</code>, <code>%usermeta_{key}%</code>, <code>%post_id%</code>, <code>%post_title%</code>, <code>%postmeta_{key}%</code>, <code>%acf_user_{field_name}%</code>, <code>%acf_post_{field_name}%</code>, <code>%date_{date_format}%</code>, <code>%yyyy-mm-dd%</code>, <code>%hh:mm%</code>, <code>%uniqueID%</code>, <code>%directory_separator% (/)</code>',
                        'notice_class' => 'info',
                        'toggle_container' => 'smartclient_settings_panel',
                        'modules' => ['all'],
                    ],
                ],
            ],
        ];

        // Actions fields
        $fields['actions'] = [
            'actions_panel' => [
                'title' => esc_html__('Basic Actions', 'wpcloudplugins'),
                'description' => esc_html__('Select which actions should be available for this module. For each action you can select which Roles or Users should be able to perform this action.', 'wpcloudplugins'),
                'type' => 'panel',
                'accordion' => false,
                'modules' => ['all'],
                'fields' => [
                    'onclick' => [
                        'title' => esc_html__('Default click behavior', 'wpcloudplugins'),
                        'description' => esc_html__('Select what should happen when you click on a file.', 'wpcloudplugins').' '.esc_html__('Make sure that the action is enabled via the setting below.', 'wpcloudplugins'),
                        'default' => 'preview',
                        'type' => 'select',
                        'options' => [
                            'preview' => ['title' => esc_html__('Preview File', 'wpcloudplugins')],
                            'download' => ['title' => esc_html__('Download File', 'wpcloudplugins')],
                            'edit' => ['title' => esc_html__('Edit File', 'wpcloudplugins')],
                            'redirect' => ['title' => sprintf(esc_html__('Open File in %s', 'wpcloudplugins'), 'OneDrive')],
                        ],
                        'modules' => ['files', 'search'],
                    ],
                    'preview' => [
                        'title' => esc_html__('Preview', 'wpcloudplugins'),
                        'description' => esc_html__('Who can preview files via this module?', 'wpcloudplugins'),
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />',
                        'default' => true,
                        'type' => 'checkbox',
                        'toggle_container' => '#previewrole_panel',
                        'modules' => ['files', 'search'],
                    ],
                    'previewrole_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'indent' => true,
                        'modules' => ['files', 'search'],
                        'fields' => [
                            'previewinline' => [
                                'title' => esc_html__('Inline Preview', 'wpcloudplugins'),
                                'description' => esc_html__('Open preview inside a lightbox. If disabled, the preview will open in a new tab.', 'wpcloudplugins'),
                                'default' => true,
                                'type' => 'checkbox',
                                'toggle_container' => '#previewinline_panel',
                                'modules' => ['files', 'search'],
                            ],
                            'previewrole' => [
                                'title' => esc_html__('Who can preview files?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['all'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'search'],
                            ],
                        ],
                    ],
                    'download' => [
                        'title' => esc_html__('Download', 'wpcloudplugins'),
                        'description' => esc_html__('Download content via this module.', 'wpcloudplugins'),
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />',
                        'default' => true,
                        'type' => 'checkbox',
                        'toggle_container' => '#downloadrole_panel',
                        'modules' => ['files', 'gallery', 'upload', 'audio', 'video', 'search', 'carousel'],
                    ],
                    'downloadrole_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'indent' => true,
                        'modules' => ['files', 'gallery', 'upload', 'audio', 'video', 'search', 'carousel'],
                        'fields' => [
                            'downloadrole' => [
                                'title' => esc_html__('Who can download?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['administrator', 'editor', 'author', 'contributor', 'subscriber', 'guest'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'gallery', 'upload', 'audio', 'video', 'search', 'carousel'],
                            ],
                            'candownloadzip' => [
                                'title' => esc_html__('Allow ZIP Downloads', 'wpcloudplugins'),
                                'description' => esc_html__('Lets users select multiple files and folder and download them as a ZIP package', 'wpcloudplugins'),
                                'default' => false,
                                'type' => 'checkbox',
                                'notice_class' => 'warning',
                                'notice' => esc_html__('The API does not support ZIP creation on the fly. Therefore, the ZIP file needs to be created (temporarily) on your server. For that reason, it is not recommended to enable this setting when you are working with large files or folders.', 'wpcloudplugins'),
                                'modules' => ['files', 'gallery', 'search'],
                            ],
                        ],
                    ],
                    'upload' => [
                        'title' => esc_html__('Upload', 'wpcloudplugins'),
                        'description' => esc_html__('Upload content to your Cloud account.', 'wpcloudplugins'),
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />',
                        'default' => false,
                        'type' => 'checkbox',
                        'toggle_container' => '#upload_panel',
                        'modules' => ['files', 'gallery', 'upload'],
                    ],
                    'upload_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'indent' => true,
                        'modules' => ['files', 'gallery', 'upload'],
                        'notice_class' => 'warning',
                        'notice' => esc_html__('Not logged in users are not allowed to perform uploads by default.', 'wpcloudplugins'),
                        'fields' => [
                            'uploadrole' => [
                                'title' => esc_html__('Who can upload?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['administrator', 'author', 'contributor', 'editor', 'subscriber'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'gallery', 'upload'],
                            ],
                        ],
                    ],
                    'search' => [
                        'title' => esc_html__('Search', 'wpcloudplugins'),
                        'description' => esc_html__('Search for files by filename and content (when files are indexed).', 'wpcloudplugins'),
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z" />',
                        'default' => true,
                        'type' => 'checkbox',
                        'toggle_container' => '#search_panel',
                        'modules' => ['files', 'gallery', 'search'],
                    ],
                    'playlist_search' => [
                        'title' => esc_html__('Search', 'wpcloudplugins'),
                        'description' => esc_html__('Search for files by filename and content (when files are indexed).', 'wpcloudplugins'),
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z" />',
                        'default' => false,
                        'type' => 'checkbox',
                        'modules' => ['audio', 'video'],
                    ],
                    'search_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'indent' => true,
                        'modules' => ['files', 'gallery', 'search'],
                        'fields' => [
                            'searchrole' => [
                                'title' => esc_html__('Who can search?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['all'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'gallery'],
                            ],                             
                            'searchcontents' => [
                                'title' => esc_html__('Full-Text search', 'wpcloudplugins'),
                                'description' => esc_html__('Search in file content, descriptions, tags and other metadata.', 'wpcloudplugins'),
                                'default' => false,
                                'type' => 'checkbox',
                                'modules' => ['files', 'gallery', 'search'],
                            ],
                            'searchterm' => [
                                'title' => esc_html__('Initial Search Term', 'wpcloudplugins'),
                                'description' => esc_html__('Add search terms if you want to start a search when the shortcode is rendered. Please note that this only affects the initial render. If you want to only show specific files, you can use the Filters tab', 'wpcloudplugins'),
                                'default' => '',
                                'type' => 'textbox',
                                'modules' => ['files', 'gallery', 'search'],
                            ],
                        ],
                    ],
                ],
            ],
            'actions_manipulation_panel' => [
                'title' => esc_html__('Files & Folder Actions', 'wpcloudplugins'),
                'description' => esc_html__('Select which actions should be available for this module. For each action you can select which Roles or Users should be able to perform this action.', 'wpcloudplugins'),
                'type' => 'panel',
                'modules' => ['all'],
                'accordion' => false,
                'fields' => [
                    'deeplink' => [
                        'title' => esc_html__('Direct Link', 'wpcloudplugins'),
                        'description' => esc_html__('Generate links to documents on your website. Only users with access to the module and its content will be able to open the link.', 'wpcloudplugins'),
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />',
                        'default' => false,
                        'type' => 'checkbox',
                        'toggle_container' => '#deeplinkrole_panel',
                        'modules' => ['files', 'gallery', 'search', 'audio', 'video'],
                    ],
                    'deeplinkrole_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'indent' => true,
                        'modules' => ['files', 'gallery', 'search', 'audio', 'video'],
                        'fields' => [
                            'deeplinkrole' => [
                                'title' => esc_html__('Who can link to content?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['all'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'gallery', 'search', 'audio', 'video'],
                            ],
                        ],
                    ],
                    'showsharelink' => [
                        'title' => esc_html__('Share', 'wpcloudplugins'),
                        'description' => esc_html__('Generate permanent shared links to your content in the Cloud.', 'wpcloudplugins'),
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />',
                        'default' => false,
                        'type' => 'checkbox',
                        'toggle_container' => '#sharerole_panel',
                        'modules' => ['all'],
                    ],
                    'sharerole_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'indent' => true,
                        'modules' => ['all'],
                        'fields' => [
                            'sharerole' => [
                                'title' => esc_html__('Who can share content?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['all'],
                                'type' => 'user_selectbox',
                                'modules' => ['all'],
                            ],
                            'share_password' => [
                                'title' => esc_html__('Password protection', 'wpcloudplugins'),
                                'description' => esc_html__('Specify the password to access the shared link. Leave empty to not use a password.', 'wpcloudplugins'),
                                'default' => '',
                                'placeholder' => esc_html__('No password.', 'wpcloudplugins'),
                                'type' => 'textbox',
                                'modules' => ['all'],
                                'account_types' => ['business'],
                            ],
                            'share_expire_after' => [
                                'title' => esc_html__('Link Expiration', 'wpcloudplugins'),
                                'description' => esc_html__('Expiration time of the shared link. By default the link will not expire.', 'wpcloudplugins'),
                                'placeholder' => esc_html__('No expire date.', 'wpcloudplugins'),
                                'default' => '',
                                'type' => 'textbox',
                                'modules' => ['all'],
                                'account_types' => ['business'],
                                'notice_class' => 'info',
                                'notice' => sprintf(esc_html__('You can set an interval using one of the following terms: %s. For example: %s.', 'wpcloudplugins'), '<code>x hours</code>, <code>x days</code>, <code>x months</code>, <code>x year</code>', '<strong>1 month</strong>'),
                            ],
                        ],
                    ],
                    'addfolder' => [
                        'title' => esc_html__('Create new folders', 'wpcloudplugins'),
                        'description' => esc_html__('Allow users to create folders.', 'wpcloudplugins'),
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />',
                        'default' => false,
                        'type' => 'checkbox',
                        'toggle_container' => '#addfolder_panel',
                        'modules' => ['files', 'gallery', 'search'],
                    ],
                    'addfolder_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'indent' => true,
                        'modules' => ['files', 'gallery', 'search'],
                        'fields' => [
                            'addfolderrole' => [
                                'title' => esc_html__('Who can create folders?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['administrator', 'editor'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'gallery', 'search'],
                            ],
                        ],
                    ],
                    'editdescription' => [
                        'title' => esc_html__('Add/Edit descriptions', 'wpcloudplugins'),
                        'description' => esc_html__('Allow users to add and edit descriptions.', 'wpcloudplugins'),
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />',
                        'default' => false,
                        'type' => 'checkbox',
                        'toggle_container' => '#editdescription_panel',
                        'modules' => ['files', 'upload', 'gallery', 'search'],
                        'account_types' => ['personal'],
                    ],
                    'editdescription_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'indent' => true,
                        'modules' => ['files', 'upload', 'gallery', 'search'],
                        'fields' => [
                            'editdescriptionrole' => [
                                'title' => esc_html__('Who can add/edit descriptions?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['administrator', 'editor'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'upload', 'gallery', 'search'],
                            ],
                        ],
                    ],
                    'edit' => [
                        'title' => esc_html__('Edit Microsoft Office documents', 'wpcloudplugins'),
                        'description' => esc_html__('Allow users to edit Google Documents and Office documents.', 'wpcloudplugins'),
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
                        'default' => false,
                        'type' => 'checkbox',
                        'toggle_container' => '#editrole_panel',
                        'modules' => ['files', 'search'],
                    ],
                    'editrole_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'indent' => true,
                        'modules' => ['files', 'search'],
                        'fields' => [
                            'editrole' => [
                                'title' => esc_html__('Who can edit documents?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['administrator', 'editor', 'subscriber'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'search'],
                            ],
                        ],
                    ],
                    'rename' => [
                        'title' => esc_html__('Rename', 'wpcloudplugins'),
                        'description' => esc_html__('Rename files and folders via this module.', 'wpcloudplugins'),
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />',
                        'default' => false,
                        'type' => 'checkbox',
                        'toggle_container' => '#rename_panel',
                        'modules' => ['files', 'gallery', 'search'],
                    ],
                    'rename_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'indent' => true,
                        'modules' => ['files', 'gallery', 'search'],
                        'fields' => [
                            'renamefilesrole' => [
                                'title' => esc_html__('Who can rename files?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['administrator', 'editor'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'upload', 'gallery', 'search'],
                            ],
                            'renamefoldersrole' => [
                                'title' => esc_html__('Who can rename folders?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['administrator', 'editor'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'gallery', 'search'],
                            ],
                        ],
                    ],
                    'move' => [
                        'title' => esc_html__('Move', 'wpcloudplugins'),
                        'description' => esc_html__('Move content to new folder locations.', 'wpcloudplugins'),
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />',
                        'default' => false,
                        'type' => 'checkbox',
                        'toggle_container' => '#move_panel',
                        'modules' => ['files', 'gallery', 'search'],
                    ],
                    'move_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'indent' => true,
                        'modules' => ['files', 'gallery', 'search'],
                        'fields' => [
                            'movefilesrole' => [
                                'title' => esc_html__('Who can move files?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['administrator', 'editor'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'gallery', 'search'],
                            ],
                            'movefoldersrole' => [
                                'title' => esc_html__('Who can move folders?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['administrator', 'editor'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'gallery', 'search'],
                            ],
                        ],
                    ],
                    'copy' => [
                        'title' => esc_html__('Copy', 'wpcloudplugins'),
                        'description' => esc_html__('Allow users to copy content.', 'wpcloudplugins'),
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />',
                        'default' => false,
                        'type' => 'checkbox',
                        'toggle_container' => '#copy_panel',
                        'modules' => ['files', 'gallery', 'search'],
                    ],
                    'copy_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'indent' => true,
                        'modules' => ['files', 'gallery', 'search'],
                        'fields' => [
                            'copyfilesrole' => [
                                'title' => esc_html__('Who can copy files?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['administrator', 'editor'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'gallery', 'search'],
                            ],
                            'copyfoldersrole' => [
                                'title' => esc_html__('Who can copy folders?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['administrator', 'editor'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'gallery', 'search'],
                            ],
                        ],
                    ],
                    'delete' => [
                        'title' => esc_html__('Delete', 'wpcloudplugins'),
                        'description' => esc_html__('Allow users to delete content on your Cloud account.', 'wpcloudplugins'),
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />',
                        'default' => false,
                        'type' => 'checkbox',
                        'toggle_container' => '#delete_panel',
                        'modules' => ['files', 'upload', 'gallery', 'search'],
                    ],
                    'delete_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'indent' => true,
                        'modules' => ['files', 'upload', 'gallery', 'search'],
                        'fields' => [
                            'deletefilesrole' => [
                                'title' => esc_html__('Who can delete files?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['administrator', 'editor'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'upload', 'gallery', 'search'],
                            ],
                            'deletefoldersrole' => [
                                'title' => esc_html__('Who can delete folders?', 'wpcloudplugins'),
                                'description' => esc_html__('Select which roles or users should be able to perform this action via the module.', 'wpcloudplugins'),
                                'default' => ['administrator', 'editor'],
                                'type' => 'user_selectbox',
                                'modules' => ['files', 'upload', 'gallery', 'search'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // Layout  fields
        $fields['layout'] = [
            'layout_filebrowser_view_panel' => [
                'title' => esc_html__('File Browser', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'modules' => ['files', 'search'],
                'accordion' => true,
                'fields' => [
                    'filelayout' => [
                        'title' => esc_html__('File Browser view', 'wpcloudplugins'),
                        'description' => '',
                        'default' => 'grid',
                        'type' => 'select',
                        'options' => [
                            'grid' => ['title' => esc_html__('Grid/Thumbnail View', 'wpcloudplugins'), 'toggle_container' => '#toggle-files-grid'],
                            'list' => ['title' => esc_html__('List View', 'wpcloudplugins'), 'toggle_container' => '#toggle-files-list'],
                        ],
                        'modules' => ['files', 'search'],
                    ],
                    'toggle-files-grid' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'modules' => ['files', 'search'],
                        'fields' => [
                            'fileinfo_on_hover' => [
                                'title' => esc_html__('Display filename on hover', 'wpcloudplugins'),
                                'description' => esc_html__('Display the file names in the thumbnail view only when hovering over the file. When disabled, the file names and actions will be displayed directly under the file. On touch devices, it will always displayed.', 'wpcloudplugins'),
                                'default' => false,
                                'type' => 'checkbox',
                                'modules' => ['files', 'search'],
                            ],
                        ],
                    ],
                    'toggle-files-list' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'modules' => ['files', 'search'],
                        'fields' => [
                            'hoverthumbs' => [
                                'title' => esc_html__('Show quick preview button', 'wpcloudplugins'),
                                'description' => esc_html__('Allow the user to see thumbnail of a file when hovering over an quick preview button.', 'wpcloudplugins'),
                                'default' => true,
                                'type' => 'checkbox',
                                'modules' => ['files', 'search'],
                            ],
                        ],
                    ],
                    'allow_switch_view' => [
                        'title' => esc_html__('Allow switching between views', 'wpcloudplugins'),
                        'description' => esc_html__('Should the user be allowed to change the view via the module on the Front-End?', 'wpcloudplugins'),
                        'default' => true,
                        'type' => 'checkbox',
                        'modules' => ['files', 'search'],
                    ],
                    'showext' => [
                        'title' => esc_html__('Show file extension', 'wpcloudplugins'),
                        'description' => '',
                        'default' => true,
                        'type' => 'checkbox',
                        'modules' => ['files', 'search'],
                    ],
                    'filesize' => [
                        'title' => esc_html__('Show file size', 'wpcloudplugins'),
                        'description' => '',
                        'default' => true,
                        'type' => 'checkbox',
                        'modules' => ['files', 'search'],
                    ],
                    'filedate' => [
                        'title' => esc_html__('Show last modified date', 'wpcloudplugins'),
                        'description' => '',
                        'default' => true,
                        'type' => 'checkbox',
                        'modules' => ['files', 'search'],
                    ],
                ],
            ],
            'layout_gallery_view_panel' => [
                'title' => esc_html__('Gallery', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'modules' => ['gallery'],
                'accordion' => true,
                'fields' => [
                    'showfilenames' => [
                        'title' => esc_html__('Show file names', 'wpcloudplugins'),
                        'description' => esc_html__('Display or Hide the file names in the gallery.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'modules' => ['gallery'],
                    ],
                    'showdescriptionsontop' => [
                        'title' => esc_html__('Descriptions always visible', 'wpcloudplugins'),
                        'description' => esc_html__('The description will appear on hover by default. When this setting is enabled it will always be visible.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'modules' => ['gallery'],
                    ],
                    'targetheight' => [
                        'title' => esc_html__('Image row height', 'wpcloudplugins'),
                        'description' => esc_html__('The ideal height you want your grid rows to be. The module will slightly adjusts the row height to fit the images in the masonary grid.', 'wpcloudplugins'),
                        'default' => 300,
                        'type' => 'number',
                        'modules' => ['gallery'],
                    ],
                    'padding' => [
                        'title' => esc_html__('Padding', 'wpcloudplugins'),
                        'description' => esc_html__('Space between images.', 'wpcloudplugins').' '.sprintf(esc_html__('You can use pixels or percentages. For instance: %s.', 'wpcloudplugins'), "'0px', '10px', '1vw', '2%' or 'clamp(1px, 1vw, 20px)'").' '.esc_html__('Leave empty for default value.', 'wpcloudplugins'),
                        'default' => '',
                        'placeholder' => Core::get_setting('layout_border_radius').'px',
                        'type' => 'textbox',
                        'modules' => ['gallery'],
                    ],
                    'border_radius' => [
                        'title' => esc_html__('Border radius', 'wpcloudplugins'),
                        'description' => esc_html__('The roundness of the image corners in pixels (px).', 'wpcloudplugins').' '.esc_html__('Leave empty for default value.', 'wpcloudplugins'),
                        'default' => '',
                        'placeholder' => Core::get_setting('layout_border_radius'),
                        'type' => 'number',
                        'min' => 0,
                        'modules' => ['gallery'],
                    ],
                    'maximages' => [
                        'title' => esc_html__('Number of images lazy loaded', 'wpcloudplugins'),
                        'description' => esc_html__('Number of images to be loaded when scrolling down the page. Set to 0 to load all images at once.', 'wpcloudplugins'),
                        'default' => 25,
                        'type' => 'number',
                        'modules' => ['gallery'],
                    ],
                ],
                'modules' => ['gallery'],
            ],
            'layout_mediaplayer_view_panel' => [
                'title' => esc_html__('Media Player', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'modules' => ['audio', 'video'],
                'accordion' => true,
                'fields' => [
                    'media_ratio' => [
                        'title' => esc_html__('Video aspect ratio', 'wpcloudplugins'),
                        'description' => esc_html__('Select the aspect ratio of your videos. The height of the video player will be set accordingly.', 'wpcloudplugins'),
                        'type' => 'select',
                        'options' => [
                            '1:1' => ['title' => esc_html__('Landscape', 'wpcloudplugins').' - 1:1'],
                            '3:2' => ['title' => esc_html__('Landscape', 'wpcloudplugins').' - 3:2'],
                            '4:3' => ['title' => esc_html__('Landscape', 'wpcloudplugins').' - 4:3'],
                            '16:9' => ['title' => esc_html__('Landscape', 'wpcloudplugins').' - 16:9'],
                            '21:9' => ['title' => esc_html__('Landscape', 'wpcloudplugins').' - 21:9'],
                            '2:3' => ['title' => esc_html__('Portrait', 'wpcloudplugins').' - 2:3'],
                            '3:4' => ['title' => esc_html__('Portrait', 'wpcloudplugins').' - 3:4'],
                            '9:16' => ['title' => esc_html__('Portrait', 'wpcloudplugins').' - 9:16'],
                            '9:21' => ['title' => esc_html__('Portrait', 'wpcloudplugins').' - 9:21'],
                            'responsive' => ['title' => esc_html__('Other', 'wpcloudplugins').' - '.esc_html__('Responsive (auto resize player based on video dimensions)', 'wpcloudplugins')],
                        ],
                        'default' => '16:9',
                        'modules' => ['video'],
                    ],
                    'autoplay' => [
                        'title' => esc_html__('Auto Play', 'wpcloudplugins'),
                        'description' => esc_html__('Start the media directly when the module is rendered.', 'wpcloudplugins'),
                        'default' => false,
                        'notice' => esc_html__('Autoplay is generally not recommended as it is seen as a negative user experience. It is also disabled in many browsers', 'wpcloudplugins'),
                        'notice_class' => 'warning',
                        'type' => 'checkbox',
                        'toggle_container' => '#playlist_panel',
                        'modules' => ['audio', 'video'],
                    ],
                    'mediabuttons' => [
                        'title' => esc_html__('Mediaplayer Buttons', 'wpcloudplugins'),
                        'description' => esc_html__('Set which buttons (if supported) should be visible in the mediaplayer.', 'wpcloudplugins'),
                        'type' => 'checkbox_button_group',
                        'options' => [
                            'prevtrack' => ['title' => esc_html__('Previous'),
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="w-5 h-5"><path fill="currentColor" d="M76 480h24c6.6 0 12-5.4 12-12V285l219.5 187.6c20.6 17.2 52.5 2.8 52.5-24.6V64c0-27.4-31.9-41.8-52.5-24.6L112 228.1V44c0-6.6-5.4-12-12-12H76c-6.6 0-12 5.4-12 12v424c0 6.6 5.4 12 12 12zM336 98.5v315.1L149.3 256.5 336 98.5z"></path></svg>', ],
                            'playpause' => ['title' => esc_html__('Play'),
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="w-5 h-5"><path fill="currentColor" d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6zM48 453.5v-395c0-4.6 5.1-7.5 9.1-5.2l334.2 197.5c3.9 2.3 3.9 8 0 10.3L57.1 458.7c-4 2.3-9.1-.6-9.1-5.2z"></path></svg>', ],
                            'nexttrack' => ['title' => esc_html__('Next'),
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="w-5 h-5"><path fill="currentColor" d="M372 32h-24c-6.6 0-12 5.4-12 12v183L116.5 39.4C95.9 22.3 64 36.6 64 64v384c0 27.4 31.9 41.8 52.5 24.6L336 283.9V468c0 6.6 5.4 12 12 12h24c6.6 0 12-5.4 12-12V44c0-6.6-5.4-12-12-12zM112 413.5V98.4l186.7 157.1-186.7 158z"></path></svg>', ],
                            'volume' => ['title' => esc_html__('Volume Slider'),
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 480 512" class="w-5 h-5"><path fill="currentColor" d="M394.23 100.85c-11.19-7.09-26.03-3.8-33.12 7.41s-3.78 26.03 7.41 33.12C408.27 166.6 432 209.44 432 256s-23.73 89.41-63.48 114.62c-11.19 7.09-14.5 21.92-7.41 33.12 6.51 10.28 21.12 15.03 33.12 7.41C447.94 377.09 480 319.09 480 256s-32.06-121.09-85.77-155.15zm-56 78.28c-11.58-6.33-26.19-2.16-32.61 9.45-6.39 11.61-2.16 26.2 9.45 32.61C327.98 228.28 336 241.63 336 256c0 14.37-8.02 27.72-20.92 34.81-11.61 6.41-15.84 21-9.45 32.61 6.43 11.66 21.05 15.8 32.61 9.45 28.23-15.55 45.77-45 45.77-76.87s-17.54-61.33-45.78-76.87zM231.81 64c-5.91 0-11.92 2.18-16.78 7.05L126.06 160H24c-13.26 0-24 10.74-24 24v144c0 13.25 10.74 24 24 24h102.06l88.97 88.95c4.87 4.87 10.88 7.05 16.78 7.05 12.33 0 24.19-9.52 24.19-24.02V88.02C256 73.51 244.13 64 231.81 64zM208 366.05L145.94 304H48v-96h97.94L208 145.95v220.1z"></path></svg>', ],
                            'current' => ['title' => '00:01',
                                'icon' => '<span class="h-5 text-xs">00:01</span>', ],
                            'duration' => ['title' => '59:59',
                                'icon' => '<span class="h-5 text-xs">-59:59</span>', ],
                            'skipback' => ['title' => esc_html__('Skip back 10 second'),
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-5 h-5"><path fill="currentColor" d="M267.5 281.2l192 159.4c20.6 17.2 52.5 2.8 52.5-24.6V96c0-27.4-31.9-41.8-52.5-24.6L267.5 232c-15.3 12.8-15.3 36.4 0 49.2zM464 130.3V382L313 256.6l151-126.3zM11.5 281.2l192 159.4c20.6 17.2 52.5 2.8 52.5-24.6V96c0-27.4-31.9-41.8-52.5-24.6L11.5 232c-15.3 12.8-15.3 36.4 0 49.2zM208 130.3V382L57 256.6l151-126.3z"></path></svg>', ],
                            'jumpforward' => ['title' => esc_html__('Jump forward 30 second'),
                                'icon' => '<svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-5 h-5"><path fill="currentColor" d="M244.5 230.8L52.5 71.4C31.9 54.3 0 68.6 0 96v320c0 27.4 31.9 41.8 52.5 24.6l192-160.6c15.3-12.8 15.3-36.4 0-49.2zM48 381.7V130.1l151 125.4L48 381.7zm452.5-150.9l-192-159.4C287.9 54.3 256 68.6 256 96v320c0 27.4 31.9 41.8 52.5 24.6l192-160.6c15.3-12.8 15.3-36.4 0-49.2zM304 381.7V130.1l151 125.4-151 126.2z"></path></svg>', ],
                            'speed' => ['title' => esc_html__('Speed Rate'),
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="w-5 h-5"><path fill="currentColor" d="M381.06 193.27l-75.76 97.4c-5.54-1.56-11.27-2.67-17.3-2.67-35.35 0-64 28.65-64 64 0 11.72 3.38 22.55 8.88 32h110.25c5.5-9.45 8.88-20.28 8.88-32 0-11.67-3.36-22.46-8.81-31.88l75.75-97.39c8.16-10.47 6.25-25.55-4.19-33.67-10.57-8.15-25.6-6.23-33.7 4.21zM288 32C128.94 32 0 160.94 0 320c0 52.8 14.25 102.26 39.06 144.8 5.61 9.62 16.3 15.2 27.44 15.2h443c11.14 0 21.83-5.58 27.44-15.2C561.75 422.26 576 372.8 576 320c0-159.06-128.94-288-288-288zm212.27 400H75.73C57.56 397.63 48 359.12 48 320 48 187.66 155.66 80 288 80s240 107.66 240 240c0 39.12-9.56 77.63-27.73 112z"></path></svg>', ],
                            'shuffle' => ['title' => esc_html__('Shuffle'),
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-5 h-5"><path fill="currentColor" d="M505 400l-79.2 72.9c-15.1 15.1-41.8 4.4-41.8-17v-40h-31c-3.3 0-6.5-1.4-8.8-3.9l-89.8-97.2 38.1-41.3 79.8 86.3H384v-48c0-21.4 26.7-32.1 41.8-17l79.2 71c9.3 9.6 9.3 24.8 0 34.2zM12 152h91.8l79.8 86.3 38.1-41.3-89.8-97.2c-2.3-2.5-5.5-3.9-8.8-3.9H12c-6.6 0-12 5.4-12 12v32c0 6.7 5.4 12.1 12 12.1zm493-41.9l-79.2-71C410.7 24 384 34.7 384 56v40h-31c-3.3 0-6.5 1.4-8.8 3.9L103.8 360H12c-6.6 0-12 5.4-12 12v32c0 6.6 5.4 12 12 12h111c3.3 0 6.5-1.4 8.8-3.9L372.2 152H384v48c0 21.4 26.7 32.1 41.8 17l79.2-73c9.3-9.4 9.3-24.6 0-33.9z"></path></svg>', ],
                            'loop' => ['title' => esc_html__('Loop'),
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-5 h-5"><path fill="currentColor" d="M512 256c0 83.813-68.187 152-152 152H136.535l55.762 54.545c4.775 4.67 4.817 12.341.094 17.064l-16.877 16.877c-4.686 4.686-12.284 4.686-16.971 0l-104-104c-4.686-4.686-4.686-12.284 0-16.971l104-104c4.686-4.686 12.284-4.686 16.971 0l16.877 16.877c4.723 4.723 4.681 12.393-.094 17.064L136.535 360H360c57.346 0 104-46.654 104-104 0-19.452-5.372-37.671-14.706-53.258a11.991 11.991 0 0 1 1.804-14.644l17.392-17.392c5.362-5.362 14.316-4.484 18.491 1.847C502.788 196.521 512 225.203 512 256zM62.706 309.258C53.372 293.671 48 275.452 48 256c0-57.346 46.654-104 104-104h223.465l-55.762 54.545c-4.775 4.67-4.817 12.341-.094 17.064l16.877 16.877c4.686 4.686 12.284 4.686 16.971 0l104-104c4.686-4.686 4.686-12.284 0-16.971l-104-104c-4.686-4.686-12.284-4.686-16.971 0l-16.877 16.877c-4.723 4.723-4.681 12.393.094 17.064L375.465 104H152C68.187 104 0 172.187 0 256c0 30.797 9.212 59.479 25.019 83.447 4.175 6.331 13.129 7.209 18.491 1.847l17.392-17.392a11.991 11.991 0 0 0 1.804-14.644z"></path></svg>', ],
                            'fullscreen' => ['title' => esc_html__('Fullscreen'),
                                'icon' => '<svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="w-5 h-5"><path fill="currentColor" d="M0 180V56c0-13.3 10.7-24 24-24h124c6.6 0 12 5.4 12 12v24c0 6.6-5.4 12-12 12H48v100c0 6.6-5.4 12-12 12H12c-6.6 0-12-5.4-12-12zM288 44v24c0 6.6 5.4 12 12 12h100v100c0 6.6 5.4 12 12 12h24c6.6 0 12-5.4 12-12V56c0-13.3-10.7-24-24-24H300c-6.6 0-12 5.4-12 12zm148 276h-24c-6.6 0-12 5.4-12 12v100H300c-6.6 0-12 5.4-12 12v24c0 6.6 5.4 12 12 12h124c13.3 0 24-10.7 24-24V332c0-6.6-5.4-12-12-12zM160 468v-24c0-6.6-5.4-12-12-12H48V332c0-6.6-5.4-12-12-12H12c-6.6 0-12 5.4-12 12v124c0 13.3 10.7 24 24 24h124c6.6 0 12-5.4 12-12z"></path></svg>', ],
                            'airplay' => ['title' => esc_html__('AirPlay'),
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16.9 13.9" class="w-5 h-5"><g id="airplay"><polygon fill="currentColor" points="0 0 16.9 0 16.9 10.4 13.2 10.4 11.9 8.9 15.4 8.9 15.4 1.6 1.5 1.6 1.5 8.9 5 8.9 3.6 10.4 0 10.4 0 0"/><polygon fill="currentColor"  points="2.7 13.9 8.4 7 14.2 13.9 2.7 13.9"/></g></svg>', ],
                            'chromecast' => ['title' => esc_html__('Chromecast'),
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16.3 13.4" class="w-5 h-5"><path id="chromecast" fill="currentColor" d="M80.4,13v2.2h2.2A2.22,2.22,0,0,0,80.4,13Zm0-2.9v1.5a3.69,3.69,0,0,1,3.7,3.68s0,0,0,0h1.5a5.29,5.29,0,0,0-5.2-5.2h0ZM93.7,4.9H83.4V6.1a9.59,9.59,0,0,1,6.2,6.2h4.1V4.9h0ZM80.4,7.1V8.6a6.7,6.7,0,0,1,6.7,6.7h1.4a8.15,8.15,0,0,0-8.1-8.2h0ZM95.1,1.9H81.8a1.54,1.54,0,0,0-1.5,1.5V5.6h1.5V3.4H95.1V13.7H89.9v1.5h5.2a1.54,1.54,0,0,0,1.5-1.5V3.4A1.54,1.54,0,0,0,95.1,1.9Z" transform="translate(-80.3 -1.9)"/></svg>', ],
                        ],
                        'default' => ['prevtrack', 'playpause', 'nexttrack', 'volume', 'current', 'duration', 'fullscreen'],
                        'modules' => ['audio', 'video'],
                    ],
                    'ads' => [
                        'title' => esc_html__('Enable Video Advertisements', 'wpcloudplugins'),
                        'description' => esc_html__('Supports VAST XML advertisments to offer monetization options for your videos. Currently, only  Linear MP4 elements are supported.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'toggle_container' => '#ads_panel',
                        'modules' => ['video'],
                    ],
                    'ads_panel' => [
                        'title' => '',
                        'description' => '',
                        'default' => false,
                        'type' => 'toggle_container',
                        'fields' => [
                            'ads_tag_url' => [
                                'title' => esc_html__('VAST XML Tag Url', 'wpcloudplugins'),
                                'description' => '',
                                'default' => Core::get_setting('mediaplayer_ads_tagurl'),
                                'type' => 'textbox',
                                'modules' => ['video'],
                            ],
                            'ads_skipable' => [
                                'title' => esc_html__('Enable Skip Button', 'wpcloudplugins'),
                                'description' => '',
                                'default' => false,
                                'type' => 'checkbox',
                                'toggle_container' => '#ads_skipable_panel',
                                'modules' => ['video'],
                            ],
                            'ads_skipable_panel' => [
                                'title' => '',
                                'description' => '',
                                'default' => false,
                                'type' => 'toggle_container',
                                'fields' => [
                                    'ads_skipable_after' => [
                                        'title' => esc_html__('Skip button visible after (seconds)', 'wpcloudplugins'),
                                        'description' => esc_html__('Allow user to skip advertisment after the following amount of seconds have elapsed', 'wpcloudplugins'),
                                        'default' => Core::get_setting('mediaplayer_ads_skipable_after'),
                                        'type' => 'textbox',
                                        'modules' => ['video'],
                                    ],
                                ],
                                'modules' => ['video'],
                            ],
                        ],
                        'modules' => ['video'],
                    ],
                    'id3' => [
                        'title' => esc_html__('Use ID3 metadata', 'wpcloudplugins'),
                        'description' => 'Use ID3 track/album/artist data if available.',
                        'default' => false,
                        'type' => 'checkbox',
                        'toggle_container' => '',
                        'modules' => ['audio', 'video'],
                        'account_types' => ['personal'],
                    ],
                    'showplaylist' => [
                        'title' => esc_html__('Show Playlist', 'wpcloudplugins'),
                        'description' => '',
                        'default' => true,
                        'type' => 'checkbox',
                        'toggle_container' => '#playlist_panel',
                        'modules' => ['audio', 'video'],
                    ],
                    'playlist_panel' => [
                        'title' => '',
                        'description' => '',
                        'default' => false,
                        'type' => 'toggle_container',
                        'modules' => ['audio', 'video'],
                        'fields' => [
                            'filelayout' => [
                                'title' => esc_html__('Playlist view', 'wpcloudplugins'),
                                'description' => '',
                                'default' => 'grid',
                                'type' => 'select',
                                'options' => [
                                    'grid' => ['title' => esc_html__('Grid/Thumbnail View', 'wpcloudplugins')],
                                    'list' => ['title' => esc_html__('List View', 'wpcloudplugins')],
                                ],
                                'notice_class' => 'info',
                                'notice' => esc_html__('Older media player skins do not support some of the settings.', 'wpcloudplugins'),                                                                
                                'modules' => ['audio', 'video'],
                            ],
                            'showplaylistonstart' => [
                                'title' => esc_html__('Playlist open on start', 'wpcloudplugins'),
                                'description' => esc_html__('Display the playlist directly when the module is rendered.', 'wpcloudplugins'),
                                'default' => true,
                                'type' => 'checkbox',
                                'modules' => ['audio', 'video'],
                            ],
                            'playlistinline' => [
                                'title' => esc_html__('Playlist opens on top of player', 'wpcloudplugins'),
                                'description' => esc_html__('Display the playlist above the video container.', 'wpcloudplugins'),
                                'default' => false,
                                'type' => 'checkbox',
                                'modules' => ['video'],
                            ],
                            'playlistautoplay' => [
                                'title' => esc_html__('Playlist autoplay', 'wpcloudplugins'),
                                'description' => esc_html__('Automatically start the next item in playlist once current one is finished.', 'wpcloudplugins'),
                                'default' => true,
                                'type' => 'checkbox',
                                'modules' => ['audio', 'video'],
                            ],
                            'playlistthumbnails' => [
                                'title' => esc_html__('Display thumbnails', 'wpcloudplugins'),
                                'description' => esc_html__('Add thumbnails for the items in the playlist.', 'wpcloudplugins'),
                                'default' => true,
                                'notice_class' => 'info',
                                'notice' => esc_html__('Set your own thumbnail by adding an image file with the same name as the media file. JPG and PNG file formats are supported.', 'wpcloudplugins'),
                                'type' => 'checkbox',
                                'modules' => ['audio', 'video'],
                            ],
                            'filedate' => [
                                'title' => esc_html__('Show last modified date', 'wpcloudplugins'),
                                'description' => esc_html__('Display the last modified date in the playlist.', 'wpcloudplugins'),
                                'default' => true,
                                'type' => 'checkbox',
                                'modules' => ['audio', 'video'],
                            ],
                            'linktoshop' => [
                                'title' => esc_html__('Link to webshop', 'wpcloudplugins'),
                                'description' => esc_html__('Display a purchase button for your media by adding an url to your webshop.', 'wpcloudplugins'),
                                'default' => '',
                                'placeholder' => 'https://www.yoursite.com/webshop/album',
                                'type' => 'textbox',
                                'modules' => ['audio', 'video'],
                            ],
                        ],
                    ],
                ],
                'modules' => ['audio', 'video'],
            ],
            'layout_carousel_view_panel' => [
                'title' => esc_html__('Slider Layout', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'modules' => ['carousel'],
                'accordion' => true,
                'fields' => [
                    'slide_height' => [
                        'title' => esc_html__('Slide height', 'wpcloudplugins'),
                        'description' => esc_html__('The height of your slides.', 'wpcloudplugins').' '.sprintf(esc_html__('You can use pixels or percentages. For instance: %s.', 'wpcloudplugins'), "'360px', '50vh', '70%'").' '.esc_html__('Leave empty for default value.', 'wpcloudplugins'),
                        'default' => '300px',
                        'type' => 'textbox',
                        'modules' => ['carousel'],
                    ],
                    'padding' => [
                        'title' => esc_html__('Slide padding', 'wpcloudplugins'),
                        'description' => esc_html__('Space between slides (in "px"). Cannot yet be used in combination with Auto size setting.', 'wpcloudplugins'),
                        'default' => '',
                        'placeholder' => Core::get_setting('layout_gap'),
                        'type' => 'number',
                        'min' => 0,
                        'modules' => ['carousel'],
                    ],
                    'border_radius' => [
                        'title' => esc_html__('Border radius', 'wpcloudplugins'),
                        'description' => esc_html__('The roundness of the image corners in pixels (px).', 'wpcloudplugins').' '.esc_html__('Leave empty for default value.', 'wpcloudplugins'),
                        'default' => '',
                        'placeholder' => Core::get_setting('layout_border_radius'),
                        'type' => 'number',
                        'min' => 0,
                        'modules' => ['carousel'],
                    ],
                    'slide_items' => [
                        'title' => esc_html__('Slides in viewport', 'wpcloudplugins'),
                        'description' => esc_html__('Number of slides being displayed in the viewport at the same time.', 'wpcloudplugins'),
                        'default' => 3,
                        'type' => 'number',
                        'min' => 0,
                        'step' => .1,
                        'modules' => ['carousel'],
                    ],
                    'axis' => [
                        'title' => esc_html__('Slide placement', 'wpcloudplugins'),
                        'description' => esc_html__('Arrangement of the slides. Arrange the slides in a row (horizontal) or column (vertical).', 'wpcloudplugins'),
                        'type' => 'select',
                        'options' => [
                            'horizontal' => ['title' => esc_html__('Horizontal', 'wpcloudplugins')],
                            'vertical' => ['title' => esc_html__('Vertical', 'wpcloudplugins')],
                        ],
                        'default' => 'horizontal',
                        'modules' => ['carousel'],
                    ],
                    'slide_center' => [
                        'title' => esc_html__('Centred slides', 'wpcloudplugins'),
                        'description' => esc_html__('Center the active slide in the viewport.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'modules' => ['carousel'],
                    ],
                    'slide_auto_size' => [
                        'title' => esc_html__('Auto size', 'wpcloudplugins'),
                        'description' => esc_html__('If enabled, the dimensions of each slide are its natural dimensions. If disabled, all slides will be the same size and the image will cover the slide.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'modules' => ['carousel'],
                        'notice_class' => 'warning',
                        'notice' => sprintf(esc_html__('The %s setting is experimental and may produce unexpected results with some settings. Use with caution.', 'wpcloudplugins'), '<strong>'.esc_html__('Auto size', 'wpcloudplugins').'</strong>'),
                    ],
                ],
                'modules' => ['carousel'],
            ],
            'layout_carousel_content_view_panel' => [
                'title' => esc_html__('Slide Content', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'modules' => ['carousel'],
                'accordion' => true,
                'fields' => [
                    'filedate' => [
                        'title' => esc_html__('Show last modified date', 'wpcloudplugins'),
                        'description' => esc_html__('Display the last modified date of the item.', 'wpcloudplugins'),
                        'default' => true,
                        'type' => 'checkbox',
                        'modules' => ['carousel'],
                    ],
                    'showfilenames' => [
                        'title' => esc_html__('Show file names', 'wpcloudplugins'),
                        'description' => esc_html__('Display or Hide the file names in the slider item.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'modules' => ['carousel'],
                    ],
                    'show_descriptions' => [
                        'title' => esc_html__('Show descriptions', 'wpcloudplugins'),
                        'description' => esc_html__('Display descriptions in the slide if available.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'toggle_container' => '#show_descriptions_panel',
                        'modules' => ['carousel'],
                    ],
                    'show_descriptions_panel' => [
                        'title' => '',
                        'description' => '',
                        'default' => true,
                        'type' => 'toggle_container',
                        'fields' => [
                            'description_position' => [
                                'title' => esc_html__('Description position', 'wpcloudplugins'),
                                'description' => esc_html__('Select the way in which the description is to be displayed.', 'wpcloudplugins'),
                                'type' => 'select',
                                'options' => [
                                    'button' => ['title' => esc_html__('Show description via info button.', 'wpcloudplugins')],
                                    'hover' => ['title' => esc_html__('Show description in slide content when hover over slide.', 'wpcloudplugins')],
                                    'inline' => ['title' => esc_html__('Show description directly in slide content.', 'wpcloudplugins')],
                                ],
                                'default' => 'hover',
                                'modules' => ['carousel'],
                            ],
                        ],
                        'modules' => ['carousel'],
                    ],
                ],
                'modules' => ['carousel'],
            ],
            'layout_carousel_navigation_view_panel' => [
                'title' => esc_html__('Slider Navigation', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'modules' => ['carousel'],
                'accordion' => true,
                'fields' => [
                    'navigation_dots' => [
                        'title' => esc_html__('Show dots', 'wpcloudplugins'),
                        'description' => esc_html__('Navigate through the slides using the dots below the slider.', 'wpcloudplugins'),
                        'default' => true,
                        'type' => 'checkbox',
                        'modules' => ['carousel'],
                    ],
                    'navigation_arrows' => [
                        'title' => esc_html__('Show arrows', 'wpcloudplugins'),
                        'description' => esc_html__('Navigate through the slides using arrows above the slider.', 'wpcloudplugins'),
                        'default' => true,
                        'type' => 'checkbox',
                        'modules' => ['carousel'],
                    ],
                    'slide_by' => [
                        'title' => esc_html__('Slides per animation', 'wpcloudplugins'),
                        'description' => esc_html__('Number of slides going on with one next click.', 'wpcloudplugins'),
                        'default' => 1,
                        'step' => .1,
                        'type' => 'number',
                        'modules' => ['carousel'],
                    ],
                    'slide_speed' => [
                        'title' => esc_html__('Animation duration', 'wpcloudplugins'),
                        'description' => esc_html__('Speed of the slide animation.', 'wpcloudplugins'),
                        'default' => 300,
                        'type' => 'number',
                        'step' => 100,
                        'modules' => ['carousel'],
                    ],
                    'carousel_autoplay' => [
                        'title' => esc_html__('Autoplay', 'wpcloudplugins'),
                        'description' => esc_html__('Toggles the automatic change of slides.', 'wpcloudplugins'),
                        'default' => true,
                        'type' => 'checkbox',
                        'toggle_container' => '#carousel_autoplay_panel',
                        'modules' => ['carousel'],
                    ],
                    'carousel_autoplay_panel' => [
                        'title' => '',
                        'description' => '',
                        'default' => true,
                        'type' => 'toggle_container',
                        'fields' => [
                            'pausetime' => [
                                'title' => esc_html__('Delay between slides', 'wpcloudplugins'),
                                'description' => esc_html__('Delay between cycles in milliseconds. Defaults to 5000.', 'wpcloudplugins'),
                                'default' => 5000,
                                'step' => 100,
                                'type' => 'number',
                                'modules' => ['carousel'],
                            ],
                            'hoverpause' => [
                                'title' => esc_html__('Pause on hover', 'wpcloudplugins'),
                                'description' => esc_html__('Stops sliding on mouseover.', 'wpcloudplugins'),
                                'default' => false,
                                'type' => 'checkbox',
                                'toggle_container' => '#carousel_slideshow_panel',
                                'modules' => ['carousel'],
                            ],
                            'direction' => [
                                'title' => esc_html__('Direction', 'wpcloudplugins'),
                                'description' => esc_html__('Direction of slide movement.', 'wpcloudplugins'),
                                'type' => 'select',
                                'options' => [
                                    'forward' => ['title' => esc_html__('Forward', 'wpcloudplugins')],
                                    'backward' => ['title' => esc_html__('Backward', 'wpcloudplugins')],
                                ],
                                'default' => 'forward',
                                'modules' => ['carousel'],
                            ],
                        ],
                        'modules' => ['carousel'],
                    ],
                ],
                'modules' => ['carousel'],
            ],
            'layout_view_panel' => [
                'title' => esc_html__('Header', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'modules' => ['files', 'gallery', 'search'],
                'accordion' => true,
                'fields' => [
                    'show_header' => [
                        'title' => esc_html__('Show header', 'wpcloudplugins'),
                        'description' => esc_html__('Display a header that shows the folder location and action buttons.', 'wpcloudplugins'),
                        'default' => true,
                        'type' => 'checkbox',
                        'toggle_container' => '#header_panel',
                        'modules' => ['files', 'gallery', 'search'],
                    ],
                    'header_panel' => [
                        'title' => '',
                        'description' => '',
                        'type' => 'toggle_container',
                        'fields' => [
                            'showrefreshbutton' => [
                                'title' => esc_html__('Show refresh button', 'wpcloudplugins'),
                                'description' => esc_html__('Add a refresh button in the header so users can refresh the data in the module and pull changes.', 'wpcloudplugins'),
                                'default' => true,
                                'type' => 'checkbox',
                                'modules' => ['files', 'gallery', 'search'],
                            ],
                            'showbreadcrumb' => [
                                'title' => esc_html__('Show breadcrumb', 'wpcloudplugins'),
                                'description' => esc_html__('Display the breadcrumb with the current folder location.', 'wpcloudplugins'),
                                'default' => true,
                                'type' => 'checkbox',
                                'toggle_container' => '#breadcrumb_panel',
                                'modules' => ['files', 'gallery', 'search'],
                            ],
                            'breadcrumb_panel' => [
                                'title' => '',
                                'description' => '',
                                'type' => 'toggle_container',
                                'fields' => [
                                    'use_custom_roottext' => [
                                        'title' => esc_html__('Use custom name for home folder', 'wpcloudplugins'),
                                        'description' => esc_html__('Instead of using the original top folder name, set a custom "Home" or "Start" text for the top folder in the breadcrumb path. This is useful if you do not want to reveal the top folder name.', 'wpcloudplugins'),
                                        'default' => true,
                                        'type' => 'checkbox',
                                        'toggle_container' => '#custom_root_panel',
                                        'modules' => ['files', 'gallery', 'search'],
                                    ],
                                    'custom_root_panel' => [
                                        'title' => '',
                                        'description' => '',
                                        'type' => 'toggle_container',
                                        'fields' => [
                                            'roottext' => [
                                                'title' => esc_html__('Custom text for the top folder', 'wpcloudplugins'),
                                                'description' => esc_html__('Set a custom text for the top folder in the breadcrumb folder path. For example: "Home" or "Start".', 'wpcloudplugins'),
                                                'default' => esc_html__('Start', 'wpcloudplugins'),
                                                'type' => 'textbox',
                                                'modules' => ['files', 'gallery', 'search'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'layout_lightbox_panel' => [
                'title' => esc_html__('Lightbox', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'modules' => ['files', 'gallery', 'search'],
                'accordion' => true,
                'fields' => [
                    'lightboxthumbs' => [
                        'title' => esc_html__('Show Thumbnails', 'wpcloudplugins'),
                        'description' => esc_html__('Show thumbnails of the files inside the Lightbox.', 'wpcloudplugins'),
                        'default' => true,
                        'type' => 'checkbox',
                        'modules' => ['files', 'gallery', 'search'],
                    ],
                    'lightboxnavigation' => [
                        'title' => esc_html__('Navigation', 'wpcloudplugins'),
                        'description' => esc_html__('Navigate through your documents in the inline preview. Disable when each document should be shown individually without navigation arrows.', 'wpcloudplugins'),
                        'default' => true,
                        'type' => 'checkbox',
                        'modules' => ['files', 'gallery', 'search'],
                    ],
                    'lightbox_open' => [
                        'title' => esc_html__('Open Lightbox on page load', 'wpcloudplugins'),
                        'description' => esc_html__('Automatically open the lightbox immediately after the module has loaded the content. Can be useful if you want the slideshow to start in full screen when the page opens.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'modules' => ['files', 'gallery'],
                    ],
                    'slideshow' => [
                        'title' => esc_html__('Enable Slideshow', 'wpcloudplugins'),
                        'description' => esc_html__('Enable the Slideshow mode for the Lightbox.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'toggle_container' => '#slideshow_panel',
                        'modules' => ['gallery'],
                    ],
                    'slideshow_panel' => [
                        'title' => '',
                        'description' => '',
                        'default' => false,
                        'type' => 'toggle_container',
                        'fields' => [
                            'pausetime' => [
                                'title' => esc_html__('Delay between slides', 'wpcloudplugins'),
                                'description' => esc_html__('Delay between cycles in milliseconds. Defaults to 5000.', 'wpcloudplugins'),
                                'default' => 5000,
                                'step' => 100,
                                'type' => 'number',
                                'modules' => ['gallery'],
                            ],
                        ],
                        'modules' => ['gallery'],
                    ],
                ],
            ],
            'layout_module_panel' => [
                'title' => esc_html__('Module Container', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'modules' => ['all'],
                'accordion' => true,
                'fields' => [
                    'maxwidth' => [
                        'title' => esc_html__('Module width', 'wpcloudplugins'),
                        'description' => esc_html__('Set maximum width for the plugin container.', 'wpcloudplugins').' '.sprintf(esc_html__('You can use pixels or percentages. For instance: %s.', 'wpcloudplugins'), "'360px', '48vw', '70%'").' '.esc_html__('Leave empty for default value.', 'wpcloudplugins'),
                        'default' => '100%',
                        'placeholder' => '100%',
                        'type' => 'textbox',
                        'modules' => ['all'],
                    ],
                    'maxheight' => [
                        'title' => esc_html__('Module height', 'wpcloudplugins'),
                        'description' => esc_html__('Set maximum height for the plugin container.', 'wpcloudplugins').' '.sprintf(esc_html__('You can use pixels or percentages. For instance: %s.', 'wpcloudplugins'), "'360px', '50vh', '70%'").' '.esc_html__('Leave empty for default value.', 'wpcloudplugins'),
                        'default' => '',
                        'placeholder' => '',
                        'type' => 'textbox',
                        'modules' => ['files', 'gallery', 'audio', 'video', 'search'],
                    ],
                    'themestyle' => [
                        'title' => esc_html__('Theme style', 'wpcloudplugins'),
                        'description' => esc_html__("Set the color theme to be used for this module. This will override the global theme style set on the plugin's main options page.", 'wpcloudplugins'),
                        'type' => 'select',
                        'options' => [
                            'default' => ['title' => esc_html__('Default', 'wpcloudplugins').'('.Core::get_setting('colors[style]').')'],
                            'dark' => ['title' => esc_html__('Dark', 'wpcloudplugins')],
                            'light' => ['title' => esc_html__('Light', 'wpcloudplugins')],
                        ],
                        'default' => 'default',
                        'modules' => ['all'],
                    ],
                    'class' => [
                        'title' => esc_html__('Custom CSS Classes', 'wpcloudplugins'),
                        'description' => esc_html__('Add your own custom classes to the plugin container. Multiple classes can be added seperated by a whitespace.', 'wpcloudplugins'),
                        'default' => '',
                        'placeholder' => '',
                        'type' => 'textbox',
                        'modules' => ['all'],
                    ],
                    'scrolltotop' => [
                        'title' => esc_html__('Scroll to Top', 'wpcloudplugins'),
                        'description' => esc_html__("Allow the user to quickly access the breadcrumb, folders and header actions by using the 'return to the top' button in the module.", 'wpcloudplugins'),
                        'default' => true,
                        'type' => 'checkbox',
                        'modules' => ['files', 'gallery', 'search'],
                    ],
                ],
            ],
        ];

        // Sorting fields
        $fields['sorting'] = [
            'sorting_panel' => [
                'title' => esc_html__('Sorting', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'modules' => ['files', 'gallery', 'audio', 'video', 'search', 'carousel'],
                'accordion' => false,
                'fields' => [
                    'sortfield' => [
                        'title' => esc_html__('Sort field', 'wpcloudplugins'),
                        'description' => esc_html__('Select the meta data field that should be used for sorting the content.', 'wpcloudplugins'),
                        'default' => 'name',
                        'type' => 'radio_group',
                        'options' => [
                            'name' => ['title' => esc_html__('Name', 'wpcloudplugins')],
                            'size' => ['title' => esc_html__('Size', 'wpcloudplugins')],
                            'datetaken' => ['title' => esc_html__('Date of creation', 'wpcloudplugins')],
                            'modified' => ['title' => esc_html__('Last modified date', 'wpcloudplugins')],
                            'shuffle' => ['title' => esc_html__('Shuffle/Random', 'wpcloudplugins')],
                        ],
                        'modules' => ['files', 'gallery', 'audio', 'video', 'search', 'carousel'],
                    ],
                    'sortorder' => [
                        'title' => esc_html__('Sort Order', 'wpcloudplugins'),
                        'description' => '',
                        'default' => 'asc',
                        'type' => 'radio_group',
                        'options' => [
                            'asc' => ['title' => esc_html__('Ascending', 'wpcloudplugins')],
                            'desc' => ['title' => esc_html__('Descending', 'wpcloudplugins')],
                        ],
                        'modules' => ['files', 'gallery', 'audio', 'video', 'search', 'carousel'],
                    ],
                ],
            ],
        ];

        // Filters fields
        $fields['filters'] = [
            'filters_amount_panel' => [
                'title' => esc_html__('Filters', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'accordion' => true,
                'modules' => ['files', 'gallery', 'audio', 'video', 'search', 'carousel'],
                'fields' => [
                    'showfiles' => [
                        'title' => esc_html__('Include files', 'wpcloudplugins'),
                        'description' => esc_html__('Display your files in the module.', 'wpcloudplugins'),
                        'default' => true,
                        'type' => 'checkbox',
                        'modules' => ['files', 'gallery', 'search'],
                    ],
                    'showfolders' => [
                        'title' => esc_html__('Include folders', 'wpcloudplugins'),
                        'description' => esc_html__('Display your folders and subfolders.', 'wpcloudplugins'),
                        'default' => true,
                        'type' => 'checkbox',
                        'modules' => ['files', 'gallery', 'search', 'audio', 'video'],
                    ],
                    'maxfiles' => [
                        'title' => esc_html__('Maximum number of files & folders', 'wpcloudplugins'),
                        'description' => esc_html__('Maximum number of files & folders to show in the module. Can be used for instance to only show the last 5 updated documents. Leave this field empty or set it to -1 for no limit.', 'wpcloudplugins'),
                        'default' => -1,
                        'min' => -1,
                        'type' => 'number',
                        'modules' => ['files', 'gallery', 'audio', 'video', 'search', 'carousel'],
                    ],
                ],
            ],
            'filters_extension_panel' => [
                'title' => esc_html__('Filter by file extension', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'accordion' => true,
                'modules' => ['files', 'gallery', 'audio', 'video', 'search', 'carousel'],
                'fields' => [
                    'includeext' => [
                        'title' => esc_html__('Show the following files', 'wpcloudplugins'),
                        'description' => esc_html__('Add extensions separated with a pipe symbol: | . E.g. (jpg|png|gif).', 'wpcloudplugins').' '.esc_html__('Leave empty to disable this filter.', 'wpcloudplugins'),
                        'default' => '',
                        'type' => 'textbox',
                        'modules' => ['files', 'gallery', 'audio', 'video', 'search', 'carousel'],
                    ],
                    'excludeext' => [
                        'title' => esc_html__('Hide the following files', 'wpcloudplugins'),
                        'description' => esc_html__('Add extensions separated with a pipe symbol: | . E.g. (jpg|png|gif).', 'wpcloudplugins').' '.esc_html__('Leave empty to disable this filter.', 'wpcloudplugins'),
                        'default' => '',
                        'type' => 'textbox',
                        'modules' => ['files', 'gallery', 'audio', 'video', 'search', 'carousel'],
                    ],
                ],
            ],
            'filters_name_panel' => [
                'title' => esc_html__('Filter by Name or ID', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'accordion' => true,
                'modules' => ['files', 'gallery', 'audio', 'video', 'search', 'carousel'],
                'fields' => [
                    'include' => [
                        'title' => esc_html__('Show the following files', 'wpcloudplugins'),
                        'description' => esc_html__('Add files or folders by name, ID or mimetype separated with a pipe symbol: | . E.g. (file1.jpg|long folder name).', 'wpcloudplugins').' '.esc_html__('Wildcards like * and ? are allowed.', 'wpcloudplugins'),
                        'default' => '',
                        'type' => 'textbox',
                        'modules' => ['files', 'gallery', 'audio', 'video', 'search', 'carousel'],
                    ],
                    'exclude' => [
                        'title' => esc_html__('Hide the following files', 'wpcloudplugins'),
                        'description' => esc_html__('Add files or folders by name, ID or mimetype separated with a pipe symbol: | . E.g. (file1.jpg|long folder name).', 'wpcloudplugins').' '.esc_html__('Wildcards like * and ? are allowed.', 'wpcloudplugins'),
                        'default' => '',
                        'type' => 'textbox',
                        'modules' => ['files', 'gallery', 'audio', 'video', 'search', 'carousel'],
                    ],
                ],
            ],
        ];

        // Upload fields
        $fields['upload'] = [
            'upload_settings_panel' => [
                'title' => esc_html__('Upload Settings', 'wpcloudplugins'),
                'description' => esc_html__('You can enable the upload functionality via the Actions tab.', 'wpcloudplugins'),
                'type' => 'panel',
                'accordion' => true,
                'modules' => ['files', 'upload', 'gallery'],
                'fields' => [
                    'upload_folder' => [
                        'title' => esc_html__('Allow folder upload', 'wpcloudplugins'),
                        'description' => esc_html__('Adds an Add Folder button to the upload form if the browser supports it. It allows the user to upload folders keeping their folder structure intact.', 'wpcloudplugins'),
                        'default' => true,
                        'type' => 'checkbox',
                        'modules' => ['files', 'upload', 'gallery'],
                    ],
                    'upload_auto_start' => [
                        'title' => esc_html__('Upload immediately', 'wpcloudplugins'),
                        'description' => esc_html__('Start the upload directly once it is selected on the users device.', 'wpcloudplugins'),
                        'default' => true,
                        'type' => 'checkbox',
                        'modules' => ['files', 'upload', 'gallery'],
                    ],
                    'overwrite' => [
                        'title' => esc_html__('Overwrite existing files', 'wpcloudplugins'),
                        'description' => esc_html__('Overwrite already existing files or auto-rename the uploaded files.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'modules' => ['files', 'upload', 'gallery'],
                    ],
                    // Deprecated
                    'upload_filename_prefix' => [
                        'title' => esc_html__('Prefix filename', 'wpcloudplugins'),
                        'description' => esc_html__('Add a prefix to the name of the uploaded files. This can include a folder path.', 'wpcloudplugins'),
                        'type' => 'textbox',
                        'default' => '',
                        'notice_class' => 'warning',
                        'notice' => esc_html__('This setting is deprecated.', 'wpcloudplugins').' '.sprintf(esc_html__('This setting is replaced with: %s .', 'wpcloudplugins'), '"Replace filename"'),
                        'modules' => ['files', 'upload', 'gallery'],
                        'deprecated' => true,
                    ],
                    'upload_filename' => [
                        'title' => esc_html__('File Rename, prefixes & suffixes', 'wpcloudplugins'),
                        'description' => esc_html__('Adjust the file name by adding prefixes, suffixes and replacing the file name itself.', 'wpcloudplugins'),
                        'type' => 'textbox',
                        'default' => '',
                        'placeholder' => '%file_name%%file_extension%',
                        'notice_class' => 'info',
                        'notice' => sprintf(esc_html__('Available placeholders: %s', 'wpcloudplugins'), '').'<code>%file_name%</code>, <code>%file_extension%</code>, <code>%queue_index%</code>, <code>%user_login%</code>,  <code>%user_firstname%</code>, <code>%user_lastname%</code>, <code>%user_email%</code>, <code>%display_name%</code>, <code>%ID%</code>, <code>%user_role%</code>, <code>%usermeta_{key}%</code>, <code>%post_id%</code>, <code>%post_title%</code>, <code>%postmeta_{key}%</code>, <code>%acf_user_{field_name}%</code>, <code>%acf_post_{field_name}%</code>, <code>%date_{date_format}%</code>, <code>%yyyy-mm-dd%</code>, <code>%hh:mm%</code>, <code>%uniqueID%</code>, <code>%directory_separator% (/)</code>',
                        'modules' => ['files', 'upload', 'gallery'],
                    ],
                    'upload_create_shared_link' => [
                        'title' => esc_html__('Create shared links', 'wpcloudplugins'),
                        'description' => esc_html__('Automatically create shared links for the uploaded files.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'modules' => ['files', 'upload', 'gallery'],
                    ],
                    'upload_button_text' => [
                        'title' => esc_html__('Custom button text', 'wpcloudplugins'),
                        'description' => '',
                        'type' => 'textbox',
                        'default' => '',
                        'placeholder' => esc_html__('Add your file', 'wpcloudplugins'),
                        'modules' => ['files', 'upload', 'gallery'],
                    ],
                    'upload_button_text_plural' => [
                        'title' => '',
                        'description' => esc_html__('Set a custom text for the "Add file(s)" button.', 'wpcloudplugins'),
                        'type' => 'textbox',
                        'default' => '',
                        'placeholder' => esc_html__('Add your files', 'wpcloudplugins'),
                        'modules' => ['files', 'upload', 'gallery'],
                    ],
                ],
            ],
            'upload_restrictions_panel' => [
                'title' => esc_html__('Upload Restrictions', 'wpcloudplugins'),
                'description' => esc_html__('Restict or limit the kind of files you want to receive via this upload module.', 'wpcloudplugins'),
                'type' => 'panel',
                'accordion' => true,
                'modules' => ['files', 'upload', 'gallery'],
                'fields' => [
                    'uploadext' => [
                        'title' => esc_html__('Restrict file extensions', 'wpcloudplugins'),
                        'description' => esc_html__('Add extensions separated with a pipe symbol: | . E.g. (jpg|png|gif).', 'wpcloudplugins').' '.esc_html__('Leave empty to disable this filter.', 'wpcloudplugins'),
                        'type' => 'textbox',
                        'default' => '',
                        'placeholder' => esc_html__('No restriction', 'wpcloudplugins'),
                        'modules' => ['files', 'upload', 'gallery'],
                    ],
                    'maxnumberofuploads' => [
                        'title' => esc_html__('Max uploads per session', 'wpcloudplugins'),
                        'description' => esc_html__('Number of maximum uploads per upload session.', 'wpcloudplugins').' '.esc_html__('Leave empty for no restriction.', 'wpcloudplugins'),
                        'type' => 'textbox',
                        'default' => '',
                        'placeholder' => esc_html__('No limit', 'wpcloudplugins'),
                        'modules' => ['files', 'upload', 'gallery'],
                    ],
                    'minfilesize' => [
                        'title' => esc_html__('Minimum file size', 'wpcloudplugins'),
                        'description' => esc_html__('Minimum file size for files that are selected for uploading (e.g. 5 MB).', 'wpcloudplugins').' '.esc_html__('Leave empty for no restriction.', 'wpcloudplugins'),
                        'type' => 'textbox',
                        'default' => '',
                        'placeholder' => esc_html__('No limit', 'wpcloudplugins'),
                        'modules' => ['files', 'upload', 'gallery'],
                    ],
                    'maxfilesize' => [
                        'title' => esc_html__('Maximum file size', 'wpcloudplugins'),
                        'description' => esc_html__('Maximum file size for files that are selected for uploading (e.g. 100 MB).', 'wpcloudplugins'),
                        'type' => 'textbox',
                        'default' => '',
                        'placeholder' => esc_html__('No limit', 'wpcloudplugins'),
                        'modules' => ['files', 'upload', 'gallery'],
                    ],
                ],
            ],
        ];

        // Notification fields
        $fields['notifications'] = [
            'notification_panel' => [
                'title' => esc_html__('Email notifications', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'modules' => ['files', 'gallery', 'search', 'upload', 'carousel'],
                'accordion' => true,
                'fields' => [
                    'notificationdownload' => [
                        'title' => esc_html__('Download notification', 'wpcloudplugins'),
                        'description' => esc_html__('Send an email notification when someone downloads content via this module.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'modules' => ['files', 'gallery', 'search', 'carousel'],
                    ],
                    'notificationupload' => [
                        'title' => esc_html__('Upload notification', 'wpcloudplugins'),
                        'description' => esc_html__('Send an email notification when someone uploads content via this module.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'modules' => ['files', 'gallery', 'upload'],
                    ],
                    'notificationdeletion' => [
                        'title' => esc_html__('Delete notification', 'wpcloudplugins'),
                        'description' => esc_html__('Send an email notification when someone deletes content via this module.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'modules' => ['files', 'gallery', 'search', 'upload'],
                    ],
                ],
            ],
            'notification_recipients_panel' => [
                'title' => esc_html__('Recipients', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'accordion' => true,
                'modules' => ['files', 'gallery', 'search', 'upload', 'carousel'],
                'fields' => [
                    'notificationemail' => [
                        'title' => esc_html__('Email addresses', 'wpcloudplugins'),
                        'description' => esc_html__('On which email address would you like to receive the notification?', 'wpcloudplugins').' '.wp_kses(__('Add multiple email addresses by separating them with a comma (<code>,</code>).', 'wpcloudplugins'), ['code' => []]).' '.sprintf(esc_html__('Available placeholders: %s', 'wpcloudplugins'), '<code>%admin_email%</code>, <code>%user_email%</code> (user that executes the action), <code>%account_email%</code> ,  <code>%linked_user_email%</code> (Private Folders owners), and role based placeholders like: <code>%editor%</code>, <code>%custom_wp_role%</code>'),
                        'default' => get_option('admin_email'),
                        'type' => 'textbox',
                        'notice_class' => 'info',
                        'notice' => sprintf(esc_html__('The placeholder %s can be used to send notications to the owner(s) of the Private Folder.', 'wpcloudplugins'), '<code>%linked_user_email%</code>.').' '.sprintf(esc_html__('When using this placeholder in combination with automatically linked Private Folders, the %sName Template%s should contain the placeholder %s.', 'wpcloudplugins'), '<a href="'.admin_url('admin.php?page=ShareoneDrive_settings#settings_userfolders').'" target="_blank">', '</a>', '<code>%user_email%</code>.').' '.esc_html__('I.e. the Private Folder name needs to contain the email address of the user.', 'wpcloudplugins'),
                        'modules' => ['files', 'gallery', 'search', 'upload', 'carousel'],
                    ],
                    'notification_skipemailcurrentuser' => [
                        'title' => esc_html__('Skip notification for current user', 'wpcloudplugins'),
                        'description' => esc_html__('Disable the notification for the user that executes the action.', 'wpcloudplugins'),
                        'default' => false,
                        'type' => 'checkbox',
                        'modules' => ['files', 'gallery', 'search', 'upload', 'carousel'],
                    ],
                ],
            ],
            'notification_email_panel' => [
                'title' => esc_html__('Sender information', 'wpcloudplugins'),
                'description' => '',
                'type' => 'panel',
                'accordion' => true,
                'modules' => ['files', 'gallery', 'search', 'upload', 'carousel'],
                'fields' => [
                    'notification_from_name' => [
                        'title' => esc_html__('From Name', 'wpcloudplugins'),
                        'description' => esc_html__('Enter the name you would like the notification email sent from, or use one of the available placeholders.', 'wpcloudplugins'),
                        'default' => Core::get_setting('notification_from_name'),
                        'type' => 'textbox',
                        'modules' => ['files', 'gallery', 'search', 'upload', 'carousel'],
                    ],
                    'notification_from_email' => [
                        'title' => esc_html__('From email address', 'wpcloudplugins'),
                        'description' => esc_html__('Enter an authorized email address you would like the notification email sent from. To avoid deliverability issues, always use your site domain in the from email.', 'wpcloudplugins'),
                        'default' => Core::get_setting('notification_from_email'),
                        'type' => 'textbox',
                        'modules' => ['files', 'gallery', 'search', 'upload', 'carousel'],
                    ],
                    'notification_replyto_email' => [
                        'title' => esc_html__('Reply-to address', 'wpcloudplugins'),
                        'description' => esc_html__('Enter an email address when you want a reply on the notification to go to an email address that is different than the From: address.', 'wpcloudplugins'),
                        'default' => Core::get_setting('notification_replyto_email'),
                        'type' => 'textbox',
                        'modules' => ['files', 'gallery', 'search', 'upload', 'carousel'],
                    ],
                ],
            ],
        ];

        self::$fields = \apply_filters('shareonedrive_shortcodebuilder_fields', $fields);
    }
}
