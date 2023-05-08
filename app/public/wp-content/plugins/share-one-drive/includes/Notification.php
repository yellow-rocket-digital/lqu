<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\ShareoneDrive;

class Notification
{
    /**
     * Kind of notification.
     *
     * @var string
     */
    public $type;

    /**
     * Addresses of recipients.
     *
     * @var array
     */
    public $recipients = [];

    /**
     * Subject of notification.
     *
     * @var string
     */
    public $subject;

    /**
     * Message of notification.
     *
     * @var string
     */
    public $message;

    /**
     * From name for the email notification.
     *
     * @var string
     */
    public $from_name;

    /**
     * From email for the email notification.
     *
     * @var string
     */
    public $from_email;

    /**
     * Replyto email for the email notification.
     *
     * @var string
     */
    public $replyto_email;

    /**
     * HTML list of files for in message.
     *
     * @var string
     */
    public $entry_list;

    /**
     * Array of entries.
     *
     * @var array
     */
    public $entries;

    /**
     * List containing the placeholders and its values.
     *
     * @var array
     */
    public $placeholders;

    /**
     * The Root folder used when the notification is triggered (e.g. the upload folder).
     *
     * @var \TheLion\ShareoneDrive\CacheNode
     */
    public $folder;

    /**
     * True if the user who triggers the notification doesn't need to receive it, while listed as recipient.
     *
     * @var bool
     */
    public $skip_notification_for_current_user = false;

    public function __construct($notification_type, $entries)
    {
        $this->type = $notification_type;
        $this->entries = $entries;

        // Load current folder
        $this->folder = Client::instance()->get_folder(false, false);
        if (count($entries) > 0) {
            $first_entry = reset($entries);
            $parents = $first_entry->get_parents();
            $this->folder = reset($parents);
        }

        if ('1' === Processor::instance()->get_shortcode_option('notification_skip_email_currentuser') && is_user_logged_in()) {
            $this->skip_notification_for_current_user = true;
        }

        $this->_process_subject();
        $this->_process_message();
        $this->_process_entry_list();
        $this->_process_recipients();
        $this->_process_from();
    }

    /**
     * Send the actual notification.
     */
    public function send_notification()
    {
        // Create and set placeholders
        $this->_create_placeholders();
        $this->_fill_placeholders();

        // Skip notification of current user if needed
        if ($this->skip_notification_for_current_user) {
            $current_user = wp_get_current_user();
            $this->recipients = array_diff($this->recipients, [$current_user->user_email]);
        }

        do_action('shareonedrive_notification_before_send', $this);

        $colors = Processor::instance()->get_setting('colors');
        $template = apply_filters('shareonedrive_notification_set_template', SHAREONEDRIVE_ROOTDIR.'/templates/notifications/default_notification.php', $this);

        $subject = $this->get_subject();
        $message = $this->get_message();

        ob_start();

        include_once $template;
        $htmlmessage = Helpers::compress_html(ob_get_clean());

        // Send mail
        try {
            $headers = [
                'Content-Type: text/html; charset=UTF-8',
            ];

            // Set From if needed
            $from_email = sanitize_email($this->from_email);
            if (!empty($this->from_name) && !empty($from_email)) {
                $headers[] = 'From: '.$this->from_name.' <'.$from_email.'>';
            }

            // Set Reply if needed
            $replyto_email = sanitize_email($this->replyto_email);
            if (!empty($this->replyto_email) && !empty($replyto_email)) {
                $headers[] = 'Reply-To: '.$this->replyto_email.' <'.$replyto_email.'>';
            }

            $recipients = array_unique($this->get_recipients());

            foreach ($recipients as $recipient) {
                $recipient_subject = $this->_fill_recipient_placeholders($subject, $recipient);
                $recipient_html_message = $this->_fill_recipient_placeholders($htmlmessage, $recipient);
                $result = wp_mail($recipient, $recipient_subject, $recipient_html_message, $headers);
            }
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Could not send notification email on line %s: %s', __LINE__, $ex->getMessage()));
        }

        do_action('shareonedrive_log_event', 'shareonedrive_sent_notification', $this->folder, ['notification_type' => $this->type, 'subject' => $subject, 'recipients' => $recipients]);

        do_action('shareonedrive_notification_sent', $this);
    }

    public function get_type()
    {
        return $this->type;
    }

    public function get_recipients()
    {
        return $this->recipients;
    }

    public function get_subject()
    {
        return $this->subject;
    }

    public function get_message()
    {
        return $this->message;
    }

    public function get_entries()
    {
        return $this->entries;
    }

    public function set_type($type)
    {
        $this->type = $type;
    }

    public function add_recipient($recipient, $id = null)
    {
        if (null !== $id) {
            $this->recipients[$id] = $recipient;
        } else {
            $this->recipients[] = $recipient;
        }
    }

    public function set_recipients($recipients)
    {
        $this->recipients = $recipients;
    }

    public function set_subject($subject)
    {
        $this->subject = $subject;
    }

    public function set_message($message)
    {
        $this->message = $message;
    }

    public function set_from_name($from_name)
    {
        $this->from_name = $from_name;
    }

    public function get_from_name()
    {
        return $this->from_name;
    }

    public function set_from_email($from_email)
    {
        $this->from_email = $from_email;
    }

    public function get_from_email()
    {
        return $this->from_email;
    }

    public function set_replyto_email($replyto_email)
    {
        $this->replyto_email = $replyto_email;
    }

    public function get_replyto_email()
    {
        return $this->replyto_email;
    }

    public function set_entries($entries)
    {
        $this->entries = $entries;
    }

    public function get_entry_list()
    {
        return $this->entry_list;
    }

    public function get_placeholders()
    {
        return $this->placeholders;
    }

    public function get_folder()
    {
        return $this->folder;
    }

    public function get_skip_notification_for_current_user()
    {
        return $this->skip_notification_for_current_user;
    }

    public function set_entry_list($entry_list)
    {
        $this->entry_list = $entry_list;
    }

    public function set_placeholders($placeholders)
    {
        $this->placeholders = $placeholders;
    }

    public function set_folder($folder)
    {
        $this->folder = $folder;
    }

    public function set_skip_notification_for_current_user($skip_notification_for_current_user)
    {
        $this->skip_notification_for_current_user = $skip_notification_for_current_user;
    }

    /**
     * Set subject of notification using the Global Template setting.
     */
    private function _process_subject()
    {
        switch ($this->type) {
            case 'download':
                if (1 === count($this->entries)) {
                    $template_key = 'download_template_subject';
                } else {
                    $template_key = 'download_template_subject_zip';
                }

                break;

            case 'upload':
                $template_key = 'upload_template_subject';

                break;

            case 'deletion':
            case 'deletion_multiple':
                $template_key = 'delete_template_subject';

                break;

            default:
                $template_key = '';
        }

        $subject_template = Processor::instance()->get_setting($template_key);
        $subject = apply_filters('shareonedrive_notification_set_subject', $subject_template, $this);

        $this->set_subject(trim($subject));
    }

    /**
     * Set message of notification using the Global Template setting.
     */
    private function _process_message()
    {
        switch ($this->type) {
            case 'download':
                $message_key = 'download_template';

                break;

            case 'upload':
                $message_key = 'upload_template';

                break;

            case 'deletion':
            case 'deletion_multiple':
                $message_key = 'delete_template';

                break;

            default:
                $message_key = '';
        }

        $message_template = Processor::instance()->get_setting($message_key);
        $message = apply_filters('shareonedrive_notification_set_message', $message_template, $this);

        $this->set_message(trim($message));
    }

    /**
     * Set file list of notification using the Global Template setting
     * This list is used inside the message.
     */
    private function _process_entry_list()
    {
        $entry_list_template = Processor::instance()->get_setting('filelist_template');
        $entry_list = apply_filters('shareonedrive_notification_set_entry_list', $entry_list_template, $this);

        $this->set_entry_list(trim($entry_list));
    }

    /**
     * Set recipients of notification using the shortcode setting.
     */
    private function _process_recipients()
    {
        $recipients_template_str = Processor::instance()->get_shortcode_option('notificationemail');
        $recipients_template_arr = array_map('trim', explode(',', $recipients_template_str));

        /* Add addresses of linked users if needed
         * Can't send notifications to linked users when folder is deleted */
        $linked_users_key = array_search('%linked_user_email%', $recipients_template_arr);
        if (false !== $linked_users_key) {
            unset($recipients_template_arr[$linked_users_key]);

            $linked_users = $this->folder->get_linked_users();

            foreach ($linked_users as $userdata) {
                $recipients_template_arr[] = $userdata->user_email;
            }
        }

        // Add addresses of WP User Roles
        $all_roles = $this->_get_user_roles();
        $listed_roles = [];

        foreach ($all_roles as $wp_role_id => $wp_role_name) {
            $user_role_key = array_search('%'.$wp_role_id.'%', $recipients_template_arr);

            if (false !== $user_role_key) {
                unset($recipients_template_arr[$user_role_key]);
                $listed_roles[] = $wp_role_id;
            }
        }

        $recipients_template_arr = array_merge($recipients_template_arr, $this->_get_emails_for_user_roles($listed_roles));

        // Make sure that all addresses are only listed once
        $recipients_template_arr = array_unique($recipients_template_arr);
        $recipients = apply_filters('shareonedrive_notification_set_recipients', $recipients_template_arr, $this);

        $this->set_recipients($recipients);
    }

    // Set the 'from' address of notification using the shortcode or global settings.

    private function _process_from()
    {
        $from_name = Processor::instance()->get_shortcode_option('notification_from_name');
        if (empty($from_name)) {
            $from_name = Processor::instance()->get_setting('notification_from_name');
        }
        $this->set_from_name(sanitize_text_field($from_name));

        $from_email = Processor::instance()->get_shortcode_option('notification_from_email');
        if (empty($from_email)) {
            $from_email = Processor::instance()->get_setting('notification_from_name');
        }
        $this->set_from_email($from_email);

        $replyto_email = Processor::instance()->get_shortcode_option('notification_replyto_email');
        if (empty($replyto_email)) {
            $replyto_email = Processor::instance()->get_setting('notification_replyto_email');
        }
        $this->set_replyto_email($replyto_email);
    }

    /**
     * Create the placeholder which can be used in the different notification templates.
     */
    private function _create_placeholders()
    {
        $cloud_root_id = API::get_root_folder()->get_id();

        $this->placeholders = [
            '%admin_email%' => get_option('admin_email'),
            '%site_name%' => get_bloginfo(),
            '%number_of_files%' => count($this->entries),
            '%ip%' => Helpers::get_user_ip(),
            '%folder_name%' => $this->get_folder()->get_name(),
            '%folder_relative_path%' => $this->get_folder()->get_path(Processor::instance()->get_root_folder()),
            '%folder_absolute_path%' => $this->get_folder()->get_path($cloud_root_id),
            '%folder_url%' => $this->get_folder()->get_entry()->get_preview_link(),
        ];

        // Current user data
        $this->placeholders['%user_name%'] = (is_user_logged_in()) ? wp_get_current_user()->display_name : esc_html__('An anonymous user', 'wpcloudplugins');
        $this->placeholders['%user_email%'] = (is_user_logged_in()) ? wp_get_current_user()->user_email : '';
        $this->placeholders['%user_first_name%'] = (is_user_logged_in()) ? wp_get_current_user()->first_name : '';
        $this->placeholders['%user_last_name%'] = (is_user_logged_in()) ? wp_get_current_user()->last_name : '';

        // Account data
        $this->placeholders['%account_email%'] = App::get_current_account()->get_email();

        // Location data
        $location_data_required = $this->_is_placeholder_needed('%location%');
        if ($location_data_required) {
            $this->placeholders['%location%'] = Helpers::get_user_location();
        }

        // File list
        $filelist = '';
        foreach ($this->entries as $cached_node) {
            $entry = $cached_node->get_entry();

            $file_cloud_shared_url = '';
            $shared_link_required = $this->_is_placeholder_needed('%file_cloud_shared_url%');
            if ($shared_link_required) {
                $file_cloud_shared_url = Client::instance()->get_shared_link($entry, []);
            }

            // deeplink
            $deeplink = json_encode([
                'source' => md5(Processor::instance()->options['account'].Processor::instance()->options['root'].Processor::instance()->options['mode']),
                'account_id' => App::get_current_account()->get_id(),
                'drive_id' => $cached_node->get_drive_id(),
                'lastFolder' => Processor::instance()->get_last_folder(),
                'folderPath' => Processor::instance()->get_folder_path(),
                'focus_id' => $entry->get_id(),
            ]);

            $deeplink_url = Helpers::get_page_url().'?wpcp_link='.base64_encode($deeplink);

            $fileline = strtr($this->_update_depricated_placeholders($this->entry_list), [
                '%file_name%' => $entry->get_name(),
                '%file_lastedited%' => $entry->get_last_edited_str(false),
                '%file_created%' => $entry->get_created_time_str(false),
                '%file_size%' => Helpers::bytes_to_size_1024($entry->get_size()),
                '%file_cloud_shortlived_download_url%' => $entry->get_direct_download_link(),
                '%file_cloud_preview_url%' => $entry->get_preview_link(),
                '%file_cloud_shared_url%' => $file_cloud_shared_url,
                '%file_download_url%' => SHAREONEDRIVE_ADMIN_URL.'?action=shareonedrive-download&id='.$entry->get_id().'&account_id='.App::get_current_account()->get_id().'&drive_id='.$cached_node->get_drive_id().'&listtoken='.Processor::instance()->get_listtoken(),
                '%file_deeplink_url%' => $deeplink_url,
                '%file_relative_path%' => $cached_node->get_path(Processor::instance()->get_root_folder()),
                '%file_absolute_path%' => $cached_node->get_path($cloud_root_id),
                '%folder_relative_path%' => $this->get_folder()->get_path(Processor::instance()->get_root_folder()),
                '%folder_absolute_path%' => $this->get_folder()->get_path($cloud_root_id),
                '%folder_url%' => $this->get_folder()->get_entry()->get_preview_link(),
                '%file_icon%' => $entry->get_default_icon(),
            ]);
            $filelist .= $fileline;
        }
        $this->placeholders['%file_list%'] = $filelist;

        // Set entry placeholders for notifications with a single entry
        $cached_entry = reset($this->entries);
        $this->placeholders['%file_name%'] = $cached_entry->get_name();
        $this->placeholders['%file_size%'] = Helpers::bytes_to_size_1024($cached_entry->get_entry()->get_size());
        $this->placeholders['%file_relative_path%'] = $cached_entry->get_path(Processor::instance()->get_root_folder());
        $this->placeholders['%file_absolute_path%'] = $cached_entry->get_path($cloud_root_id);
        $this->placeholders['%file_icon%'] = $cached_entry->get_entry()->get_default_thumbnail_icon();
        $this->placeholders['%file_cloud_shortlived_download_url%'] = $entry->get_direct_download_link();
        $this->placeholders['%file_cloud_preview_url%'] = $entry->get_preview_link();
        $this->placeholders['%file_download_url%'] = SHAREONEDRIVE_ADMIN_URL.'?action=shareonedrive-download&id='.$entry->get_id().'&account_id='.App::get_current_account()->get_id().'&drive_id='.$cached_entry->get_drive_id().'&listtoken='.Processor::instance()->get_listtoken();

        $shared_link_required = $this->_is_placeholder_needed('%file_cloud_shared_url%');
        if ($shared_link_required) {
            $this->placeholders['%file_cloud_shared_url%'] = Client::instance()->get_shared_link($entry, []);
        }

        // Set page url
        $this->placeholders['%current_url%'] = Helpers::get_page_url();
        $this->placeholders['%page_name%'] = get_bloginfo();

        $post_id = url_to_postid(Helpers::get_page_url());

        if ($post_id > 0) {
            $this->placeholders['%page_name%'] = get_the_title($post_id);
        }

        // Add filter for custom placeholders
        $this->placeholders = apply_filters('shareonedrive_notification_create_placeholders', $this->placeholders, $this);
    }

    /**
     * Update depricated placeholders in template to their new values.
     *
     * @param string $template
     *
     * @return string
     */
    private function _update_depricated_placeholders($template)
    {
        $template = str_replace('%sitename%', '%site_name%', $template);
        $template = str_replace('%user%', '%user_name%', $template);
        $template = str_replace('%visitor%', '%user_name%', $template);
        $template = str_replace('%filename%', '%file_name%', $template);
        $template = str_replace('%filesize%', '%file_size%', $template);
        $template = str_replace('%filepath%', '%file_path%', $template);
        $template = str_replace('%file_path%', '%file_relative_path%', $template);
        $template = str_replace('%fileicon%', '%file_icon%', $template);
        $template = str_replace('%fileurl%', '%file_url%', $template);
        $template = str_replace('%filelist%', '%file_list%', $template);
        $template = str_replace('%folder%', '%folder_name%', $template);
        $template = str_replace('%folderpath%', '%folder_path%', $template);
        $template = str_replace('%folder_path%', '%folder_relative_path%', $template);
        $template = str_replace('%file_url%', '%file_cloud_preview_url%', $template);

        return str_replace('%currenturl%', '%current_url%', $template);
    }

    /**
     * Check if a placeholder needs to be created for the notification
     * Prevent using too many resources when it's needed. (e.g. receiving user location).
     *
     * @param mixed $placeholder
     */
    private function _is_placeholder_needed($placeholder)
    {
        if (false !== strpos($this->subject, $placeholder)) {
            return true;
        }

        if (false !== strpos($this->message, $placeholder)) {
            return true;
        }

        if (false !== strpos($this->entry_list, $placeholder)) {
            return true;
        }

        foreach ($this->recipients as $recipient) {
            if (false !== strpos($recipient, $placeholder)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fill the placeholders before sending the notification.
     */
    private function _fill_placeholders()
    {
        $this->subject = strtr($this->_update_depricated_placeholders($this->subject), $this->placeholders);
        $this->message = strtr($this->_update_depricated_placeholders($this->message), $this->placeholders);

        $recipients = [];
        foreach ($this->recipients as $key => $recipient) {
            $recipients[$key] = strtr($this->_update_depricated_placeholders($recipient), $this->placeholders);
        }
        $this->recipients = $recipients;

        $this->from_name = strtr($this->_update_depricated_placeholders($this->from_name), $this->placeholders);
        $this->from_email = strtr($this->_update_depricated_placeholders($this->from_email), $this->placeholders);
        $this->replyto_email = strtr($this->_update_depricated_placeholders($this->replyto_email), $this->placeholders);
    }

    /**
     * Fill the recipient placeholders for individual html messages.
     *
     * @param string $html_message
     * @param string $recipient_email
     */
    private function _fill_recipient_placeholders($html_message, $recipient_email)
    {
        if (get_option('admin_email') === $recipient_email) {
            $user_data = new \stdClass();
            $user_data->user_login = esc_html__('Administrator', 'wpcloudplugins');
            $user_data->user_email = $recipient_email;
            $user_data->user_firstname = esc_html__('Administrator', 'wpcloudplugins');
            $user_data->user_lastname = '';
            $user_data->display_name = esc_html__('Administrator', 'wpcloudplugins');
            $user_data->ID = '';
        } else {
            $user_data = get_user_by('email', $recipient_email);
        }

        return strtr($html_message, [
            '%recipient_login%' => !empty($user_data) ? $user_data->user_login : '',
            '%recipient_email%' => !empty($user_data) ? $user_data->user_email : '',
            '%recipient_firstname%' => !empty($user_data) ? $user_data->user_firstname : '',
            '%recipient_lastname%' => !empty($user_data) ? $user_data->user_lastname : '',
            '%recipient_name%' => !empty($user_data) ? $user_data->display_name : '',
            '%recipient_ID%' => !empty($user_data) ? $user_data->ID : '',
        ]);
    }

    private function _get_user_roles()
    {
        // Get User Roles
        global $wp_roles;
        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }

        return $wp_roles->get_names();
    }

    private function _get_emails_for_user_roles($user_roles)
    {
        $emails = [];

        if (empty($user_roles)) {
            return $emails;
        }

        $args = [
            'role__in' => $user_roles,
            'fields' => ['user_email'],
        ];

        $users = get_users($args);

        foreach ($users as $wp_user) {
            $emails[$wp_user->user_email] = $wp_user->user_email;
        }

        return $emails;
    }
}
