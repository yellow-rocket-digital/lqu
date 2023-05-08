<?php

/**
 * Class Wpcp_ShareoneDrive_Helpers.
 */
class Wpcp_ShareoneDrive_Helpers
{
    public $options;

    /**
     * @var bool
     */
    public $load_options;

    public function __construct()
    {
        $this->load_options = true;
    }

    public function setOptions(Wpcp_ShareoneDrive_Helpers $options)
    {
        $this->options = $options;
    }

    /**
     * @param string $label
     * @param string $option_code
     * @param mixed  $args
     *
     * @return mixed
     */
    public function list_events($label = null, $option_code = 'WPCP_SHAREONEDRIVE_EVENT_TYPE', $args = [])
    {
        if (!$this->load_options) {
            return Automator()->helpers->recipe->build_default_options_array($label, $option_code);
        }

        $token = key_exists('token', $args) ? $args['token'] : true;
        $is_ajax = key_exists('is_ajax', $args) ? $args['is_ajax'] : false;
        $target_field = key_exists('target_field', $args) ? $args['target_field'] : '';
        $end_point = key_exists('endpoint', $args) ? $args['endpoint'] : '';
        $options = [];

        $options =
         [
             'shareonedrive_previewed_entry' => esc_html__('File previewed', 'wpcloudplugins-automator'),
             'shareonedrive_edited_entry' => esc_html__('File edited', 'wpcloudplugins-automator'),
             'shareonedrive_downloaded_entry' => esc_html__('File downloaded', 'wpcloudplugins-automator'),
             'shareonedrive_streamed_entry' => esc_html__('File streamed', 'wpcloudplugins-automator'),
             'shareonedrive_created_link_to_entry' => esc_html__('File shared', 'wpcloudplugins-automator'),
             'shareonedrive_renamed_entry' => esc_html__('File renamed', 'wpcloudplugins-automator'),
             'shareonedrive_deleted_entry' => esc_html__('File deleted', 'wpcloudplugins-automator'),
             'shareonedrive_created_entry' => esc_html__('File created', 'wpcloudplugins-automator'),
             'shareonedrive_moved_entry' => esc_html__('File moved', 'wpcloudplugins-automator'),
             'shareonedrive_updated_description' => esc_html__('File description added', 'wpcloudplugins-automator'),
             'shareonedrive_uploaded_entry' => esc_html__('New File Uploaded', 'wpcloudplugins-automator'),
             'shareonedrive_uploaded_failed' => esc_html__('File Upload failed', 'wpcloudplugins-automator'),
         ];

        $option = [
            'option_code' => $option_code,
            'label' => 'Event Type',
            'input_type' => 'select',
            'required' => true,
            'supports_tokens' => $token,
            'is_ajax' => $is_ajax,
            'fill_values_in' => $target_field,
            'endpoint' => $end_point,
            'options' => $options,
            'supports_multiple_values' => true,
            'relevant_tokens' => [],
        ];

        return apply_filters('uap_option_list_wpcp_shareonedrive_events', $option);
    }

    /**
     * @param string $label
     * @param string $option_code
     *
     * @return mixed
     */
    public function list_users($label = null, $option_code = 'WPCP_SHAREONEDRIVE_USERS')
    {
        if (!$this->load_options) {
            return Automator()->helpers->recipe->build_default_options_array($label, $option_code);
        }

        if (!$label) {
            $label = esc_attr__('User or Role', 'wpcloudplugins-automator');
        }

        $data = ['-1' => esc_html__('Everyone', 'wpcloudplugins')];

        // Get Roles
        foreach (wp_roles()->roles as $role_name => $role_info) {
            $data[$role_name] = $role_info['name'];
        }

        // Get Users
        $users = get_users(['fields' => ['user_login', 'display_name', 'id']]);
        $users_arr = [];
        $user_count = count($users);
        $i = 1;

        foreach ($users as $wp_user) {
            if ($i > 5000) {
                // Don't show individual users for very large sites for performance reasons
                break;
            }
            $users_arr[(string) $wp_user->id] = htmlentities(str_replace('"', '', empty($wp_user->display_name) ? $wp_user->user_login : $wp_user->display_name));
            ++$i;
        }

        asort($users_arr);

        $data += $users_arr;

        $option = [
            'option_code' => $option_code,
            'label' => $label,
            'input_type' => 'select',
            'required' => true,
            'options' => $data,
            'custom_value_description' => esc_attr__('User slug', 'wpcloudplugins-automator'),
            'supports_multiple_values' => true,
        ];

        return apply_filters('uap_option_list_wpcp_shareonedrive_users', $option);
    }
}
