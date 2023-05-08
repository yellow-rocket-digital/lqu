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

class Account
{
    /**
     * Account ID.
     *
     * @var string
     */
    private $_id;

    /**
     * Account Name.
     *
     * @var string
     */
    private $_name;

    /**
     * Account Email.
     *
     * @var string
     */
    private $_email;

    /**
     * Account profile picture (url).
     *
     * @var string
     */
    private $_image;

    /**
     * Kind of Account.
     *
     * @var string
     */
    private $_type;

    /**
     * $_authorization contains the authorization token for the linked Cloud storage.
     *
     * @var \TheLion\ShareoneDrive\Authorization
     */
    private $_authorization;

    public function __construct($id, $name, $email, $type = null, $image = null)
    {
        $this->_id = $id;
        $this->_name = $name;
        $this->_email = $email;
        $this->_image = $image;
        $this->_type = $type;
        $this->_authorization = new Authorization($this);
    }

    public function __sleep()
    {
        // Don't store authorization class in DB */
        $keys = get_object_vars($this);
        unset($keys['_authorization']);

        return array_keys($keys);
    }

    public function __wakeup()
    {
        $this->_authorization = new Authorization($this);
    }

    public function get_id()
    {
        return $this->_id;
    }

    public function get_name()
    {
        return $this->_name;
    }

    public function get_email()
    {
        return $this->_email;
    }

    public function get_image()
    {
        if (empty($this->_image)) {
            return SHAREONEDRIVE_ROOTPATH.'/css/images/onedrive_logo.png';
        }

        return $this->_image;
    }

    public function set_id($_id)
    {
        $this->_id = $_id;
    }

    public function set_name($_name)
    {
        $this->_name = $_name;
    }

    public function set_email($_email)
    {
        $this->_email = $_email;
    }

    public function set_image($_image)
    {
        $this->_image = $_image;
    }

    public function get_type()
    {
        return $this->_type;
    }

    public function set_type($_type)
    {
        $this->_type = $_type;
    }

    /**
     * @return \TheLion\ShareoneDrive\StorageInfo
     */
    public function get_storage_info()
    {
        $transient_name = 'shareonedrive_'.$this->get_id().'_driveinfo';
        $storage_info = get_transient($transient_name);

        if (empty($storage_info)) {
            $storage_info = new StorageInfo();

            if ('service' === $this->get_type()) {
                // Service Accounts don't have any drive data
                $storage_info->set_quota_total(0);
                $storage_info->set_quota_used(0);
            } else {
                App::set_current_account($this);
                $storage_info_data = API::get_space_info();

                $storage_info->set_quota_total($storage_info_data->getQuota()->getTotal());
                $storage_info->set_quota_used($storage_info_data->getQuota()->getUsed());
            }
            set_transient($transient_name, $storage_info, DAY_IN_SECONDS);
        }

        return $storage_info;
    }

    /**
     * @return \TheLion\ShareoneDrive\Authorization
     */
    public function get_authorization()
    {
        return $this->_authorization;
    }
}
