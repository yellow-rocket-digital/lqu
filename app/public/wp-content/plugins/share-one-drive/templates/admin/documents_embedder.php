<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\ShareoneDrive;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Exit if no permission to embed files
if (!Helpers::check_user_role(Core::get_setting('permissions_add_embedded'))) {
    exit;
}

// Add own styles and script and remove default ones
$this->load_scripts();
$this->load_styles();

function remove_all_scripts()
{
    global $wp_scripts;
    $wp_scripts->queue = [];

    wp_enqueue_script('jquery-effects-fade');
    wp_enqueue_script('ShareoneDrive');
    wp_enqueue_script('ShareoneDrive.DocumentEmbedder');
}

function remove_all_styles()
{
    global $wp_styles;
    $wp_styles->queue = [];
    wp_enqueue_style('ShareoneDrive');
    wp_enqueue_style('WPCloudPlugins.AdminUI');
}

add_action('wp_print_scripts', __NAMESPACE__.'\\remove_all_scripts', 1000);
add_action('wp_print_styles', __NAMESPACE__.'\\remove_all_styles', 1000);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" class="wpcp-h-full wpcp-bg-gray-100">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php esc_html_e('Embed Files', 'wpcloudplugins'); ?></title>
    <?php wp_print_styles(); ?>
</head>

<body class="wpcp-h-full">
    <div id="wpcp" class="wpcp-app hidden" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
        <form action="#" data-callback="<?php echo isset($_REQUEST['callback']) ? $_REQUEST['callback'] : ''; ?>">
            <nav class="bg-brand-color-900 shadow">
                <div class="mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="flex-shrink-0 flex items-center">
                                <a href="https://www.wpcloudplugins.com"><img class="block h-8 w-auto" src="<?php echo SHAREONEDRIVE_ROOTPATH; ?>/css/images/wpcloudplugins-logo-light.png"></a>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="flex-shrink-0 relative wpcp-dropdown-menu">
                                <div>
                                    <button type="button" class="wpcp-dropdown-menu-button wpcp-button-secondary" aria-haspopup="true">
                                        <!-- Heroicon name: solid/plus-sm -->
                                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                        <span><?php esc_html_e('Embed Files', 'wpcloudplugins'); ?></span>
                                    </button>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

            </nav>

            <div class="">
                <main>
                    <div class="mx-auto">
                        <div class="">
                            <?php

                // Add File Browser

$allowed_extensions = 'csv|doc|docx|odp|ods|odt|pot|potm|potx|pps|ppsx|ppsxm|ppt|pptm|pptx|rtf|xlsx|jpg|jpeg|gif|png|pdf';
$allowed_extensions .= '|3mf|cool|glb|gltf|obj|stl'; // 3-D Modeling/Printing
$allowed_extensions .= '|dwg'; // AutoCAD
$allowed_extensions .= '|fbx'; // AutoDesk
$allowed_extensions .= '|epub'; // Open Ebook
$allowed_extensions .= '|ai|pdf|psb|psd'; // Adobe
$allowed_extensions .= '|html|txt'; // Other (Business Accounts only)

$atts = [
    'singleaccount' => '0',
    'dir' => 'root',
    'mode' => 'files',
    'showfiles' => '1',
    'upload' => '0',
    'delete' => '0',
    'rename' => '0',
    'addfolder' => '0',
    'viewrole' => 'all',
    'candownloadzip' => '0',
    'search' => '1',
    'showsharelink' => '0',
    'previewinline' => '0',
    'popup' => 'embedded',
    'includeext' => $allowed_extensions,
    '_random' => 'embed',
];

$user_folder_backend = apply_filters('shareonedrive_use_user_folder_backend', $this->settings['userfolder_backend']);

if ('No' !== $user_folder_backend) {
    $atts['userfolders'] = $user_folder_backend;

    $private_root_folder = $this->settings['userfolder_backend_auto_root'];
    if ('auto' === $user_folder_backend && !empty($private_root_folder) && isset($private_root_folder['id'])) {
        if (!isset($private_root_folder['account']) || empty($private_root_folder['account'])) {
            $main_account = Accounts::instance()->get_primary_account();
            $atts['account'] = $main_account->get_id();
        } else {
            $atts['account'] = $private_root_folder['account'];
        }

        $atts['dir'] = $private_root_folder['id'];

        if (!isset($private_root_folder['view_roles']) || empty($private_root_folder['view_roles'])) {
            $private_root_folder['view_roles'] = ['none'];
        }
        $atts['viewuserfoldersrole'] = implode('|', $private_root_folder['view_roles']);
    }
}

echo $this->create_template($atts);
?>
                        </div>
                    </div>
                </main>
                <footer>
                    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="border-t border-gray-200 py-4 text-sm text-gray-500 text-center sm:text-left">
                            <span class="block sm:inline">
                                <?php echo AdminLayout::render_notice(esc_html__('Please note that the embedded files do have the public sharing permission [anyone with link can view].', 'wpcloudplugins'), 'info'); ?>
                            </span>
                        </div>
                    </div>
                </footer>
            </div>

        </form>
    </div>

    <?php wp_print_scripts(); ?>
</body>

</html>