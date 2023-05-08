<?php
/**
 *
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\ShareoneDrive;

class Accounts
{
    /**
     * The single instance of the class.
     *
     * @var Accounts
     */
    protected static $_instance;

    /**
     * $_accounts contains all the accounts that are linked with the plugin.
     *
     * @var \TheLion\ShareoneDrive\Account[]
     */
    private $_accounts = [];

    /**
     * Are the accounts managed on Network level or per blog.
     *
     * @var bool
     */
    private $_use_network_accounts = false;

    public function __construct()
    {
        $this->_use_network_accounts = Processor::instance()->is_network_authorized();

        $this->_init_accounts();
    }

    /**
     * Accounts Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Accounts - Accounts instance
     *
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @return bool
     */
    public function has_accounts()
    {
        return count($this->_accounts) > 0;
    }

    /**
     * @return \TheLion\ShareoneDrive\Account[]
     */
    public function list_accounts()
    {
        return $this->_accounts;
    }

    /**
     * @return null|\TheLion\ShareoneDrive\Account
     */
    public function get_primary_account()
    {
        if (0 === count($this->_accounts)) {
            return null;
        }

        $first_account = reset($this->_accounts);

        if (false === $first_account->get_authorization()->has_access_token()) {
            return null;
        }

        return $first_account;
    }

    /**
     * @param string $id
     *
     * @return null|\TheLion\ShareoneDrive\Account
     */
    public function get_account_by_id($id)
    {
        if (false === isset($this->_accounts[(string) $id])) {
            return null;
        }

        return $this->_accounts[(string) $id];
    }

    /**
     * @param string $id
     * @param mixed  $email
     *
     * @return null|\TheLion\ShareoneDrive\Account
     */
    public function get_account_by_email($email)
    {
        foreach ($this->_accounts as $account) {
            if ($account->get_email() === $email) {
                return $account;
            }
        }

        return null;
    }

    /**
     * @param \TheLion\ShareoneDrive\Account $account
     *
     * @return $this
     */
    public function add_account(Account $account)
    {
        $this->_accounts[$account->get_id()] = $account;

        $this->save();

        return $this;
    }

    /**
     * @param string $account_id
     *
     * @return $this
     */
    public function remove_account($account_id)
    {
        $account = $this->get_account_by_id($account_id);

        if (null === $account) {
            return;
        }

        $account->get_authorization()->remove_token();

        unset($this->_accounts[$account_id]);

        $this->save();

        return $this;
    }

    /**
     * Function run once when upgrading from versions not supporting multiple accounts.
     */
    public static function upgrade_from_single()
    {
        require_once ABSPATH.'wp-includes/pluggable.php';

        // Update Events database, add account_id column
        Events::install_database();

        // Process per blog
        $blog_id = get_current_blog_id();
        if (Accounts::instance()->_use_network_accounts) {
            $token_path = SHAREONEDRIVE_CACHEDIR.'/network.access_token';
            $token_name = 'network.access_token';
        } else {
            $token_path = SHAREONEDRIVE_CACHEDIR."/{$blog_id}.access_token";
            $token_name = "{$blog_id}.access_token";
        }

        if (false === file_exists($token_path)) {
            // Blog doesn't have an active authorization
            return;
        }

        // Create account with temporarily data
        $account = new Account($blog_id, '', $blog_id);
        $account->get_authorization()->set_token_name($token_name);
        App::set_current_account($account);

        // Load Client for this account
        try {
            $client = App::instance()->start_sdk_client($account);
        } catch (\Exception $ex) {
            @unlink($token_path);

            return;
        }

        // Get & Update User Information
        try {
            $user_info = App::instance()->get_user()->me->get();
            $drive_info = App::instance()->get_drive()->about->get();
        } catch (\Exception $ex) {
            @unlink($token_path);

            return;
        }

        $account->set_id($user_info->getId());
        $account->set_name($user_info->getDisplayName());
        $account->set_email($user_info->getUserPrincipalName());

        // Get Drive information
        $account->set_type($drive_info->getDriveType());

        // Create new token file
        $authorization = $account->get_authorization();
        $access_token = $authorization->get_access_token();
        $authorization->set_account_id($account->get_id());
        $authorization->set_token_name(Helpers::filter_filename($account->get_email().'_'.$account->get_id(), false).'.access_token');
        $authorization->set_access_token($access_token);
        $authorization->unlock_token_file();

        try {
            // Generate Image for business accounts
            if ('personal' !== $account->get_type()) {
                $photo_info = App::instance()->get_user()->me->photometa('48x48');
                $content_type = $photo_info['@odata.mediaContentType'];

                $photo_data = App::instance()->get_user()->me->photo('48x48', ['alt' => 'media']);
                $image = "data:{$content_type};base64,".base64_encode($photo_data);
                $account->set_image($image);
            }
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Cannot obtain profile photo: %s', $ex->getMessage()));
        }

        // Remove old token file
        @unlink($token_path);

        // Add Account to DB
        Accounts::instance()->add_account($account);

        // Update all Manually linked folders
        $users = get_users(['fields' => ['ID'], 'blog_id' => $blog_id]);

        // Manually linked folders for users
        foreach ($users as $user) {
            $manually_linked_data = get_user_option('share_one_drive_linkedto', $user->ID);

            if (false === $manually_linked_data) {
                continue;
            }

            $manually_linked_data['accountid'] = $account->get_id();

            update_user_option($user->ID, 'share_one_drive_linkedto', $manually_linked_data, false);
        }

        // Manually linked folder for guests (currently stored on network level)
        $manually_linked_guests_data = get_site_option('share_one_drive_guestlinkedto');
        if (false !== $manually_linked_guests_data) {
            $manually_linked_guests_data['accountid'] = $account->get_id();
            update_site_option('share_one_drive_guestlinkedto', $manually_linked_guests_data);
        }

        App::clear_current_account();
        Processor::reset_complete_cache();
        Core::instance()->load_default_values();
    }

    public function save()
    {
        if ($this->_use_network_accounts) {
            Processor::instance()->set_network_setting('accounts', $this->_accounts);
        } else {
            Processor::instance()->set_setting('accounts', $this->_accounts);
        }
    }

    private function _init_accounts()
    {
        if ($this->_use_network_accounts) {
            $this->_accounts = Processor::instance()->get_network_setting('accounts', []);
        } else {
            $this->_accounts = Processor::instance()->get_setting('accounts', []);
        }
    }
}