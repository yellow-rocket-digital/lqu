<?php
/*
 * API Class.
 *
 * Use the API to execute calls directly for the set cloud account.
 * You can use the API using WPCP_ONEDRIVE_API::get_entry(...)
 *
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\ShareoneDrive;

defined('ABSPATH') || exit; // Exit if accessed directly.

if (!function_exists('onedrive_api_php_client_autoload')) {
    require_once SHAREONEDRIVE_ROOTDIR.'/vendors/API/autoload.php';
}

class API
{
    public static $apifilefields = 'thumbnails(select=c48x48,medium,large,c1500x1500),children(expand=thumbnails(select=c48x48,medium,large,c1500x1500))';
    public static $apifilefieldsexpire = 'thumbnails(select=c48x48,medium,large,c1500x1500),children(expand=thumbnails(select=c48x48,medium,large,c1500x1500))';
    public static $apilistfilesfields = 'thumbnails(select=c48x48,medium,large,c1500x1500)';
    public static $apilistfilesexpirefields = 'thumbnails(select=c48x48,medium,large,c1500x1500)';

    /**
     * Set which cloud account should be used.
     *
     * @param string $account_id
     *
     * @return Account|false - Account
     */
    public static function set_account_by_id($account_id)
    {
        $account = Accounts::instance()->get_account_by_id($account_id);
        if (null === $account) {
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: Cannot use the requested account (ID: %s) as it is not linked with the plugin', __LINE__, $account_id));

            return false;
        }

        return App::set_current_account($account);
    }

    /**
     * Set which drive on the account should be used.
     *
     * @param string $drive_id ID of the OneDrive / SharePoint Drive
     */
    public static function set_drive_by_id($drive_id)
    {
        return APP::set_current_drive_id($drive_id);
    }

    /**
     * @param string $id     ID of the entry that should be loaded
     * @param array  $params
     *
     * @return API_Exception|CacheNode
     */
    public static function get_entry($id, $params = [])
    {
        // Load the root folder when needed
        self::get_root_folder();

        $driveid = App::get_current_drive_id();

        // Get entry from cache
        $cached_node = Cache::instance()->is_cached($id, $driveid);

        if (!empty($cached_node)) {
            return $cached_node;
        }

        do_action('shareonedrive_api_before_get_entry', $id);

        try {
            $api_entry = App::instance()->get_drive()->items->get($id, ['driveId' => $driveid, 'expand' => self::$apifilefields]);
        } catch (\Exception $ex) {
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            throw new API_Exception(esc_html__('Failed to load file.', 'wpcloudplugins'));
        }

        $entry = new Entry($api_entry);

        if (false === $entry->is_dir()) {
            $cached_node = Cache::instance()->add_to_cache($entry);
        } else {
            $cached_node = self::get_folder($id);
        }

        do_action('shareonedrive_api_after_get_entry', $cached_node);

        return $cached_node;
    }

    /**
     * Get folder information. Metadata of direct child files are loaded as well.
     *
     * @param string $id     ID of the folder that should be loaded
     * @param array  $params
     *
     * @return API_Exception|CacheNode
     */
    public static function get_folder($id, $params = [])
    {
        // Load the root folder when needed
        if ('root' !== $id) {
            self::get_root_folder();
        }

        $driveid = App::get_current_drive_id();

        // Get entry from cache
        $cached_node = Cache::instance()->is_cached($id, $driveid, 'id', false);

        if (!empty($cached_node)) {
            return $cached_node;
        }

        do_action('shareonedrive_api_before_get_folder', $id);

        $cached_node = Cache::instance()->get_node_by_id($id, $driveid);

        // Only reload the folder node itself if it doesn't exists. Otherwise, only load the children
        if (empty($cached_node)) {
            try {
                $results = App::instance()->get_drive()->items->get($id, ['driveId' => $driveid, 'expand' => self::$apilistfilesfields]);
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

                throw new API_Exception(esc_html__('Failed to load folder.', 'wpcloudplugins'));
            }

            $folder_entry = new Entry($results);
            $cached_node = Cache::instance()->add_to_cache($folder_entry);
        }

        try {
            $results_children = App::instance()->get_drive()->items->children($id, ['driveId' => $cached_node->get_drive_id(), 'expand' => self::$apilistfilesfields]);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            throw new API_Exception(esc_html__('Failed to load data in folder.', 'wpcloudplugins'));
        }

        $files_in_folder = $results_children->getValue();
        $next_page_token = $results_children['@odata.nextLink'];

        // Get all files in folder
        while (!empty($next_page_token)) {
            $next_link = parse_url($next_page_token);
            parse_str($next_link['query'], $next_link_attributes);
            $next_page_token = $next_link_attributes['$skiptoken'];

            try {
                $more_files = App::instance()->get_drive()->items->children($id, ['driveId' => $cached_node->get_drive_id(), 'expand' => self::$apilistfilesfields, 'skiptoken' => $next_page_token]);
                $files_in_folder = array_merge($files_in_folder, $more_files->getValue());
                $next_page_token = $more_files['@odata.nextLink'];
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

                return false;
            }
        }

        // Add all files in folder to cache
        foreach ($files_in_folder as $entry) {
            $item = new Entry($entry);
            Cache::instance()->add_to_cache($item);
        }

        $cached_node->set_loaded_children(true);
        Cache::instance()->update_cache();

        do_action('shareonedrive_api_after_get_folder', $cached_node);

        return $cached_node;
    }

    /**
     * Get root folder information. Metadata of direct child files are loaded as well.
     *
     * @return API_Exception|CacheNode
     */
    public static function get_root_folder()
    {
        $root_node = Cache::instance()->get_root_node();

        if (false !== $root_node && null !== $root_node->get_entry()) {
            return $root_node;
        }

        // Top OneDrive Folder
        $root_api = new \SODOneDrive_Service_Drive_Item();
        $root_api->setId('drives');
        $root_api->setName('OneDrive');
        $root_api->setFolder(new \SODOneDrive_Service_Drive_FolderFacet());
        $root_entry = new Entry($root_api, true);
        $root_entry->set_virtual_folder('drives');
        $cached_root_node = Cache::instance()->add_to_cache($root_entry);
        $cached_root_node->set_expired(null);
        $cached_root_node->set_root();
        $cached_root_node->set_loaded_children(true);
        $cached_root_node->set_virtual_folder('drives');
        Cache::instance()->set_root_node_id('drives');

        // SharePoint Drives
        self::get_all_site_drives();

        // OneDrives
        self::get_all_drives();

        Cache::instance()->set_updated();

        return Cache::instance()->get_root_node();
    }

    /**
     * Get all Drives for the user.
     *
     * @return API_Exception|CacheNode
     */
    public static function get_all_drives()
    {
        try {
            if ('personal' === App::get_current_account()->get_type()) {
                $api_drive = App::instance()->get_drive()->items->root();

                $api_drive->setName(esc_html__('My files', 'wpcloudplugins'));
                $parent = $api_drive->getParentReference();
                $parent->setId('drives');
                $api_drive->setParentReference($parent);
                $drive_entry = new Entry($api_drive);
                $drive_entry->set_virtual_folder('drive');
                $cached_drive_node = Cache::instance()->add_to_cache($drive_entry);
                $cached_drive_node->set_virtual_folder('drive');

                return $cached_drive_node;
            }
            $result = App::instance()->get_drive()->drives->list(['expand' => 'root']);

            $api_drives = $result->getValue();

            foreach ($api_drives as $api_drive) {
                $root_folder = $api_drive->root;
                $root_folder->setName($api_drive->getName());
                $parent = $root_folder->getParentReference();
                $parent->setId('drives');
                $root_folder->setParentReference($parent);
                $drive_entry = new Entry($root_folder);
                $drive_entry->set_virtual_folder('drive');
                $cached_drive_node = Cache::instance()->add_to_cache($drive_entry);
                $cached_drive_node->set_virtual_folder('drive');
            }

            return Cache::instance()->get_root_node()->get_children();
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }
    }

    /**
     * Get all SharePoint Drives information.
     *
     * @return API_Exception|CacheNode
     */
    public static function get_all_site_drives()
    {
        if ('personal' === App::get_current_account()->get_type()) {
            return false;
        }

        if ('Yes' !== Processor::instance()->get_setting('use_sharepoint')) {
            return false;
        }

        try {
            $result = App::instance()->get_sites()->sites->search(['q' => '']);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        $api_sites = $result->getValue();

        if (0 === count($api_sites)) {
            return;
        }

        // Create Sites folder
        $sites_api = new \SODOneDrive_Service_Drive_Item();
        $sites_api->setId('sites');
        $sites_api->setName('SharePoint');
        $sites_api->setFolder(new \SODOneDrive_Service_Drive_FolderFacet());
        $parent = new \SODOneDrive_Service_Drive_ItemReference();
        $parent->setId('drives');
        $sites_api->setParentReference($parent);
        $sites_entry = new Entry($sites_api, true);
        $sites_entry->set_virtual_folder('sites');
        $cached_sites_node = Cache::instance()->add_to_cache($sites_entry);
        $cached_sites_node->set_expired(null);
        $cached_sites_node->set_root();
        $cached_sites_node->set_loaded_children(true);
        $cached_sites_node->set_virtual_folder('sites');

        foreach ($api_sites as $api_site) {
            // Skip items not accessible via the API (without SPWeb ID). Perhaps not Document libraries?
            if (false !== stripos($api_site->getId(), '00000000-0000-0000-0000-000000000000')) {
                continue;
            }

            // Get Drives on site
            try {
                $api_site_info = App::instance()->get_sites()->sites->get($api_site->getId(), ['expand' => 'drives(expand=root)']);
            } catch (\Exception $ex) {
                // Skip this site if the API somehow prevents access to it
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

                continue;
            }

            // Create Site folder
            $site_api = new \SODOneDrive_Service_Drive_Item();
            $site_api->setId($api_site->getId());
            $site_api->setName($api_site->getDisplayName());
            $site_api->setFolder(new \SODOneDrive_Service_Drive_FolderFacet());
            $parent = new \SODOneDrive_Service_Drive_ItemReference();
            $parent->setId('sites');
            $site_api->setParentReference($parent);
            $site_entry = new Entry($site_api, true);
            $site_entry->set_virtual_folder('site');
            $cached_site_node = Cache::instance()->add_to_cache($site_entry);
            $cached_site_node->set_expired(null);
            $cached_site_node->set_root();
            $cached_site_node->set_loaded_children(true);
            $cached_site_node->set_virtual_folder('site');

            $site_drives = $api_site_info->getDrives();

            // Create Drives in Site
            foreach ($site_drives as $api_drive) {
                $root_folder = $api_drive->getRoot();
                $root_folder->setName($api_drive->getName());
                $parent = $root_folder->getParentReference();
                $parent->setId($cached_site_node->get_id());
                $root_folder->setParentReference($parent);
                $drive_entry = new Entry($root_folder);
                $drive_entry->set_virtual_folder('drive');
                $cached_drive_node = Cache::instance()->add_to_cache($drive_entry);
                $cached_drive_node->set_virtual_folder('drive');
            }
        }

        return $cached_sites_node;
    }

    /**
     * Get (and create) sub folder by path.
     *
     * @param string $parent_folder_id
     * @param string $subfolder_path
     * @param bool   $create_if_not_exists
     *
     * @return bool|\TheLion\ShareoneDrive\CacheNode
     */
    public static function get_sub_folder_by_path($parent_folder_id, $subfolder_path, $create_if_not_exists = false)
    {
        $cached_parent_folder = self::get_folder($parent_folder_id);

        if (empty($cached_parent_folder)) {
            return false;
        }

        if (empty($subfolder_path)) {
            return $cached_parent_folder;
        }

        $subfolders = array_filter(explode('/', $subfolder_path));
        $current_folder = array_shift($subfolders);

        // Try to load the subfolder at once
        $cached_sub_folder = Cache::instance()->get_node_by_name($current_folder, $cached_parent_folder->get_drive_id(), $parent_folder_id);

        /* If folder isn't in cache yet,
          * Update the parent folder to make sure the latest version is loaded */
        if (false === $cached_sub_folder) {
            Cache::instance()->pull_for_changes(true);
            $cached_sub_folder = Cache::instance()->get_node_by_name($current_folder, $cached_parent_folder->get_drive_id(), $parent_folder_id);
        }

        if (false === $cached_sub_folder && false === $create_if_not_exists) {
            return false;
        }

        // If the subfolder can't be found, create the sub folder
        if (!$cached_sub_folder) {
            // Create new folder object
            $newfolder = new \SODOneDrive_Service_Drive_Item();
            $newfolder->setName($current_folder);
            $newfolder->setFolder(new \SODOneDrive_Service_Drive_FolderFacet());

            try {
                $api_entry = App::instance()->get_drive()->items->insert($parent_folder_id, $newfolder, ['driveId' => $cached_parent_folder->get_drive_id(), 'expand' => self::$apifilefields]);
                // Add new file to our Cache
                $newentry = new Entry($api_entry);
                $cached_sub_folder = Cache::instance()->add_to_cache($newentry);

                do_action('shareonedrive_log_event', 'shareonedrive_created_entry', $cached_sub_folder);
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

                return false;
            }
        }

        return self::get_sub_folder_by_path($cached_sub_folder->get_id(), implode('/', $subfolders), $create_if_not_exists);
    }

    /**
     * Create a new folder in the Cloud Account.
     *
     * @param string $new_name  the name for the newly created folder
     * @param string $parent_id ID of the folder where the new folder should be created
     * @param array  $params
     *
     * @return API_Exception|CacheNode
     */
    public static function create_folder($new_name, $parent_id, $params = [])
    {
        $drive_id = App::get_current_drive_id();
        $parent_id = apply_filters('shareonedrive_api_create_folder_set_parent_id', $parent_id);
        $params = apply_filters('shareonedrive_api_create_folder_set_params', $params);

        // Create new folder object
        $newfolder = new \SODOneDrive_Service_Drive_Item();
        $newfolder->setName($new_name);
        $newfolder->setFolder(new \SODOneDrive_Service_Drive_FolderFacet());
        $newfolder['@microsoft.graph.conflictBehavior'] = 'rename';

        do_action('shareonedrive_api_before_create_folder', $new_name, $parent_id, $params);

        try {
            $api_entry = App::instance()->get_drive()->items->insert($parent_id, $newfolder, ['driveId' => $drive_id, 'expand' => self::$apifilefields]);

            $newentry = new Entry($api_entry);
            $node = Cache::instance()->add_to_cache($newentry);

            do_action('shareonedrive_log_event', 'shareonedrive_created_entry', $node);
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            throw new API_Exception(esc_html__('Failed to create folder.', 'wpcloudplugins'));
        }

        Cache::instance()->pull_for_changes(true);

        do_action('shareonedrive_api_after_create_folder', $node);

        return $node;
    }

    /**
     * Copy an entry to a new location.
     *
     * @param array  $entry_ids ID of the files that should be moved / copied
     * @param string $target_id ID of the folder where the files should be moved/copied to
     * @param array  $params
     *
     * @return API_Exception|CacheNode
     */
    public static function copy($entry_ids, $target_id, $params = [])
    {
        $entry_ids = apply_filters('shareonedrive_api_move_set_entry_ids', $entry_ids);
        $target_id = apply_filters('shareonedrive_api_move_set_target_id', $target_id);
        $params = apply_filters('shareonedrive_api_copy_set_params', $params);

        do_action('shareonedrive_api_before_copy', $entry_ids, $target_id, $params);

        $copied_entries = self::move($entry_ids, $target_id, true, $params);

        do_action('shareonedrive_api_after_copy', $copied_entries);

        return $copied_entries;
    }

    /**
     * Move an entry to a new location.
     *
     * @param array  $entry_ids ID of the files that should be moved / copied
     * @param string $target_id ID of the folder where the files should be moved/copied to
     * @param bool   $copy      Move or copy the entries. Default: copy = false
     * @param array  $params
     *
     * @return API_Exception|CacheNode
     */
    public static function move($entry_ids, $target_id, $copy = false, $params = [])
    {
        $entry_ids = apply_filters('shareonedrive_api_move_set_entry_ids', $entry_ids);
        $target_id = apply_filters('shareonedrive_api_move_set_target_id', $target_id);
        $copy = apply_filters('shareonedrive_api_move_set_copy', $copy);
        $params = apply_filters('shareonedrive_api_move_set_params', $params);

        do_action('shareonedrive_api_before_move', $entry_ids, $target_id, $copy, $params);

        $cached_target = self::get_entry($target_id);

        $entries_to_move = [];

        foreach ($entry_ids as $entry_id) {
            $entries_to_move[$entry_id] = false;

            $cached_entry = self::get_entry($entry_id);

            if (false === $cached_entry) {
                continue;
            }

            // Set new parent
            $new_parent = new \SODOneDrive_Service_Drive_ItemReference();
            $new_parent->setId($cached_target->get_id());
            $new_parent->setDriveId($cached_target->get_drive_id());
            $updaterequest = new \SODOneDrive_Service_Drive_Item();
            $updaterequest->setParentReference($new_parent);
            $updaterequest['@microsoft.graph.conflictBehavior'] = 'rename';

            try {
                if ($copy) {
                    App::instance()->get_drive()->items->copy($entry_id, $updaterequest, ['driveId' => $cached_entry->get_drive_id()]);
                    // Copying can take some time, so the target folder is removed from the cache and will be loaded if the user enters that directory
                    $cached_target->set_loaded(false);
                    $updated_entry = $cached_entry;
                    do_action('shareonedrive_log_event', 'shareonedrive_copied_entry', $updated_entry, ['original' => $cached_entry->get_name()]);
                } else {
                    self::set_drive_by_id($cached_entry->get_drive_id());
                    $updated_entry = self::patch($entry_id, $updaterequest);
                    do_action('shareonedrive_log_event', 'shareonedrive_moved_entry', $updated_entry);
                }

                $entries_to_move[$entry_id] = $updated_entry;
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));
                $entries_to_move[$entry_id] = false;

                continue;
            }
        }

        Cache::instance()->update_cache();

        // Clear Cached Requests
        CacheRequest::clear_local_cache_for_shortcode(App::get_current_account()->get_id(), Processor::instance()->get_listtoken());

        do_action('shareonedrive_api_after_move', $entries_to_move);

        return $entries_to_move;
    }

    /**
     * Delete files by their IDs.
     *
     * @param array $entry_ids array of IDs that need to be deleted
     * @param array $params
     *
     * @return API_Exception|CacheNode
     */
    public static function delete($entry_ids = [], $params = [])
    {
        do_action('shareonedrive_api_before_delete', $entry_ids, $params);

        $deleted_entries = [];

        foreach ($entry_ids as $key => $entry_id) {
            $target_node = self::get_entry($entry_id);
            $drive_id = $target_node->get_drive_id();

            /* Issue with if-match header
             * https://github.com/OneDrive/onedrive-api-docs/issues/131
             * If solved, change to:
             * $headers = array("if-match" => '*'); */
            $params = ['driveId' => $drive_id];

            try {
                $deleted_entry = App::instance()->get_drive()->items->delete($entry_id, $params);
                $deleted_entries[$entry_id] = $target_node;

                do_action('shareonedrive_log_event', 'shareonedrive_deleted_entry', $target_node, []);

                Cache::instance()->remove_from_cache($target_node->get_id(), $drive_id, 'deleted');
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));
                $deleted_entries[$entry_id] = false;

                continue;
            }
        }

        // Remove items from cache
        Cache::instance()->pull_for_changes(true);

        // Clear Cached Requests
        CacheRequest::clear_request_cache();

        do_action('shareonedrive_api_after_delete', $deleted_entries, $params);

        return $deleted_entries;
    }

    /**
     * Get the account information.
     *
     * @return SODOneDrive_Service_User_Me
     */
    public static function get_account_info()
    {
        $cache_key = 'shareonedrive_account_'.App::get_current_account()->get_id();
        if (empty($account_info = get_transient($cache_key, false))) {
            $account_info = App::instance()->get_user()->me->get();

            \set_transient($cache_key, $account_info, HOUR_IN_SECONDS);
        }

        return $account_info;
    }

    /**
     * Get the information about the available space.
     *
     * @return false|SODOneDrive_Service_Drive_About
     */
    public static function get_space_info()
    {
        if ('service' === App::get_current_account()->get_type()) {
            return false;
        }

        $cache_key = 'shareonedrive_account_'.App::get_current_account()->get_id().'_space';
        if (empty($space_info = get_transient($cache_key, false))) {
            $space_info = App::instance()->get_drive()->about->get();

            \set_transient($cache_key, $space_info, HOUR_IN_SECONDS);
        }

        return $space_info;
    }

    /**
     * Upload a file to the cloud using a simple file object.
     *
     * @param string      $upload_folder_id ID of the upload folder
     * @param null|string $description      Add a description to the file
     * @param bool        $overwrite        should we overwrite an existing file with the same name? If false, the file will be renamed
     * @param stdClass    $file             Object containg the file details. Same as file object in $_FILES.
     *                                      <code>
     *                                      $file = object {
     *                                      'name' : 'filename.ext',
     *                                      'type' : 'image/jpeg',
     *                                      'tmp_name'=> '...\php8D2C.tmp
     *                                      'size' => 1274994
     *                                      }
     *                                      </code>
     */
    public static function upload_file($file, $upload_folder_id, $description = null, $overwrite = false)
    {
        $drive_id = App::get_current_drive_id();

        $upload_folder_id = apply_filters('shareonedrive_api_upload_set_upload_folder_id', $upload_folder_id);
        $file->name = apply_filters('shareonedrive_api_upload_set_file_name', $file->name);
        $file = apply_filters('shareonedrive_api_upload_set_file', $file);
        $description = apply_filters('shareonedrive_api_upload_set_description', $description);
        $overwrite = apply_filters('shareonedrive_api_upload_set_overwrite', $overwrite);

        do_action('shareonedrive_api_before_upload', $upload_folder_id, $file, $description, $overwrite);

        // Create new OneDrive File
        $body = [
            'item' => [
                '@microsoft.graph.conflictBehavior' => ($overwrite) ? 'replace' : 'rename',
            ],
        ];

        if (!empty($description)) {
            $body['item']['description'] = $description;
        }

        // Do the actual upload
        $chunkSizeBytes = 200 * 320 * 1000; // Multiple of 320kb, the recommended fragment size is between 5-10 MB.

        App::instance()->get_sdk_client()->setDefer(true);

        try {
            $request = App::instance()->get_drive()->items->upload($file->name, $upload_folder_id, $body, ['driveId' => $drive_id]);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Not uploaded to the cloud on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        // Create a media file upload to represent our upload process.
        $media = new \SODOneDrive_Http_MediaFileUpload(
            App::instance()->get_sdk_client(),
            $request,
            null,
            null,
            true,
            $chunkSizeBytes
        );

        $media->setFileSize($file->size);

        try {
            $upload_status = false;
            $bytesup = 0;
            $handle = fopen($file->tmp_path, 'rb');
            while (!$upload_status && !feof($handle)) {
                @set_time_limit(60);
                $chunk = fread($handle, $chunkSizeBytes);
                $upload_status = $media->nextChunk($chunk);
                $bytesup += $chunkSizeBytes;
            }

            fclose($handle);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Not uploaded to the cloud on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        App::instance()->get_sdk_client()->setDefer(false);

        usleep(500000); // wait a 0.5 sec so OneDrive can create a thumbnail.
        $node = self::get_entry($upload_status->getId());

        do_action('shareonedrive_log_event', 'shareonedrive_uploaded_entry', $node);

        do_action('shareonedrive_api_after_upload', $node);

        return $node;
    }

    /**
     * Get a shortened url via the requested service.
     *
     * @param string $url
     * @param string $service
     * @param array  $params  Add extra data that can be used for certain services, e.g. ['name' => $node->get_name()]
     *
     * @return API_Exception|string The shortened url
     */
    public static function shorten_url($url, $service = null, $params = [])
    {
        if (empty($service)) {
            $service = Core::get_setting('shortlinks');
        }

        $service = apply_filters('shareonedrive_api_shorten_url_set_service', $service);

        do_action('shareonedrive_api_before_shorten_url', $url, $service, $params);

        if (false !== strpos($url, 'localhost')) {
            // Most APIs don't support localhosts
            return $url;
        }

        try {
            switch ($service) {
                case 'Bit.ly':
                    $response = wp_remote_post('https://api-ssl.bitly.com/v4/shorten', [
                        'body' => json_encode(
                            [
                                'long_url' => $url,
                            ]
                        ),
                        'headers' => [
                            'Authorization' => 'Bearer '.Core::get_setting('bitly_apikey'),
                            'Content-Type' => 'application/json',
                        ],
                    ]);

                    $data = json_decode($response['body'], true);

                    return $data['link'];

                case 'Shorte.st':
                    $response = wp_remote_get('https://api.shorte.st/s/'.Core::get_setting('shortest_apikey').'/'.$url);

                    $data = json_decode($response['body'], true);

                    return $data['shortenedUrl'];

                case 'Tinyurl':
                    $response = wp_remote_post('https://api.tinyurl.com/create?api_token='.Core::get_setting('tinyurl_apikey'), [
                        'body' => json_encode(
                            [
                                'url' => $url,
                                'domain' => Core::get_setting('tinyurl_domain'),
                            ]
                        ),
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ],
                    ]);

                    $data = json_decode($response['body'], true);

                    return (!empty($data['errors'])) ? htmlspecialchars(reset($data['errors']), ENT_QUOTES) : $data['data']['tiny_url'];

                case 'Rebrandly':
                    $response = wp_remote_post('https://api.rebrandly.com/v1/links', [
                        'body' => json_encode(
                            [
                                'title' => isset($params['name']) ? $params['name'] : '',
                                'destination' => $url,
                                'domain' => ['fullName' => Core::get_setting('rebrandly_domain')],
                            ]
                        ),
                        'headers' => [
                            'apikey' => Core::get_setting('rebrandly_apikey'),
                            'Content-Type' => 'application/json',
                            'workspace' => Core::get_setting('rebrandly_workspace'),
                        ],
                    ]);

                    $data = json_decode($response['body'], true);

                    return 'https://'.$data['shortUrl'];

                case 'None':
                default:
                    break;
            }
        } catch (\Exception $ex) {
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return $url;
        }

        $shortened_url = apply_filters('shareonedrive_api_shorten_url_set_shortened_url', $url);

        do_action('shareonedrive_api_after_shorten_url', $shortened_url);

        return $shortened_url;
    }

    /**
     * Update an file. This can be e.g. used to rename a file.
     *
     * @param string                               $id             ID of the entry that should be updated
     * @param string                               $drive_id       drive ID of the entry
     * @param array|SODOneDrive_Service_Drive_Item $update_request The content that should be patched. E.g. ['name'=>'new_name'].
     * @param array                                $_params        API request parameters
     *
     * @return API_Exception|CacheNode
     */
    public static function patch($id, $update_request = [], $_params = [])
    {
        $drive_id = App::get_current_drive_id();
        $update_request = apply_filters('shareonedrive_api_patch_set_update_request', $update_request);

        $params = array_merge(['expand' => self::$apilistfilesfields], $_params);

        do_action('shareonedrive_api_before_patch', $id, $update_request);

        try {
            $api_entry = App::instance()->get_drive()->items->patch($id, $update_request, $params);
            $entry = new Entry($api_entry);

            // Remove item from cache if it is moved
            if ($update_request instanceof \SODOneDrive_Service_Drive_Item && null !== $update_request->getParentReference()) {
                Cache::instance()->remove_from_cache($id, $drive_id, 'deleted');
            }

            $node = Cache::instance()->add_to_cache($entry);
            Cache::instance()->update_cache();
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            throw new API_Exception(esc_html__('Failed to patch file.', 'wpcloudplugins'));
        }

        do_action('shareonedrive_api_after_patch', $node);

        return $node;
    }

    /**
     * Search in a particular folder.
     *
     * @param string $query
     * @param string $search_in_id
     * @param bool   $search_contents
     *
     * @return API_Exception|CacheNode[]
     */
    public static function search_by_name($query, $search_in_id, $search_contents = true)
    {
        $searched_folder = self::get_folder($search_in_id);

        $query = apply_filters('shareonedrive_api_search_set_query', $query);

        // Find all items containing query
        $params = [
            'id' => $search_in_id,
            'driveId' => $searched_folder->get_drive_id(),
            'q' => stripslashes($query),
            'expand' => self::$apilistfilesfields,
        ];

        // Set all params
        $params = apply_filters('shareonedrive_api_search_set_params', $params);

        // Do the request
        $next_page_token = null;
        $files_found = [];
        $entries_found = [];
        $results = [];

        do_action('shareonedrive_log_event', 'shareonedrive_searched', $searched_folder, ['query' => $query]);

        do {
            try {
                $search_response = App::instance()->get_drive()->items->search($params);
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

                return [];
            }

            // Process the response
            $more_files = $search_response->getValue();
            $files_found = array_merge($files_found, $more_files);

            if (isset($search_response['@odata.nextLink'])) {
                $next_page_token = $search_response['@odata.nextLink'];
                $next_link = parse_url($next_page_token);
                parse_str($next_link['query'], $next_link_attributes);
                $next_page_token = isset($next_link_attributes['$skiptoken']) ? $next_link_attributes['$skiptoken'] : null;
            } else {
                $next_page_token = null;
            }
            $params['skiptoken'] = $next_page_token;
        } while (null !== $next_page_token);

        foreach ($files_found as $file) {
            if (false === $search_contents && false === stripos($file->getName(), $query)) {
                // Only find files query in name */
                continue;
            }

            $entries_found[$file->getId()] = new Entry($file);
        }

        foreach ($entries_found as $entry) {
            // Check if files are in cache
            $cachedentry = Cache::instance()->is_cached($entry->get_id(), $entry->get_drive_id());

            // If not found, add to cache
            if (false === $cachedentry) {
                $cachedentry = Cache::instance()->add_to_cache($entry);
            }

            $results[] = $cachedentry;
        }

        Cache::instance()->update_cache();

        return $results;
    }

    /**
     * Create an url to a preview of the file.
     *
     * @param string $id       ID of entry for which you want to get the preview
     * @param string $drive_id Drive ID where the file is located*
     * @param array  $params
     *
     * @return API_Exception|string
     */
    public static function create_preview_url($id, $params = [])
    {
        // Get file meta data
        $node = self::get_entry($id);

        if (false === $node) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Failed to find entry: %s', $id));

            return false;
        }

        $entry = $node->get_entry();
        if (false === $entry->get_can_preview_by_cloud()) {
            error_log('[WP Cloud Plugin message]: '.sprintf('File %s cannot be previewed.', $id));

            return false;
        }

        do_action('shareonedrive_api_before_create_preview_url', $node, $params);
        $params = apply_filters('shareonedrive_api_create_preview_url_set_params', $params);

        // Preview for Image files
        if (in_array($entry->get_extension(), ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
            $preview_url = self::create_shared_url($id, ['type' => 'view', 'scope' => 'anonymous']).'?raw=1';
        } elseif (in_array($entry->get_extension(), ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga', 'flac'])) {
            // Preview for Media files in HTML5 Player + PDF files
            $preview_url = self::create_temporarily_download_url($id);
        } elseif ('personal' !== App::get_current_account()->get_type()) {
            // Business accounts can use the Preview API */
            $preview_url = self::get_preview_link($id);
        } elseif (in_array($entry->get_extension(), ['pdf'])) {
            // Personal accounts need to use Shared/Temporarily links
            $preview_url = self::get_embed_url($id);
        } elseif (in_array($entry->get_extension(), ['csv', 'doc', 'docx', 'odp', 'ods', 'odt', 'pot', 'potm', 'potx', 'pps', 'ppsx', 'ppsxm', 'ppt', 'pptm', 'pptx', 'rtf', 'xls', 'xlsx'])) {
            // Preview for Office files
            $preview_url = self::get_embed_url($id);
        } else {
            // Preview for all other formats
            $preview_url = self::create_temporarily_download_url($id, 'pdf');
        }

        $link = apply_filters('shareonedrive_api_create_preview_url_set_link', $preview_url);

        do_action('shareonedrive_log_event', 'shareonedrive_previewed_entry', $node);
        do_action('shareonedrive_api_after_create_preview_url', $link);

        return $link;
    }

    /**
     * Get a Preview url for files on OneDrive Business
     * PREVIEW API ONLY WORKING FOR BUSINESS ACCOUNTS.
     *
     * @param string $id     ID of the file
     * @param array  $params
     */
    public static function get_preview_link($id, $params = [])
    {
        if ($preview_url = get_transient('shareonedrive_'.$id.'_preview_'.implode('_', array_values($params)))) {
            return $preview_url;
        }

        // Get file meta data
        $node = self::get_entry($id);

        if (false === $node) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Failed to find entry: %s', $id));

            return false;
        }

        try {
            $preview_url = App::instance()->get_drive()->items->preview($id, $params, ['driveId' => $node->get_drive_id()]);
            set_transient('shareonedrive_'.$id.'_preview_'.implode('_', array_values($params)), $preview_url->getUrl(), MINUTE_IN_SECONDS * 10);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        return $preview_url->getUrl();
    }

    /**
     * Get a public embed url for a file.
     *
     * @param string $id     ID of the entry for which you want to create the embed url
     * @param array  $params
     *
     * @return API_Exception|array Returns an array with shared link information
     */
    public static function get_embed_url($id, $params = [])
    {
        // Get file meta data
        $node = self::get_entry($id);

        if (false === $node) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Failed to find entry: %s', $id));

            return '';
        }

        $entry = $node->get_entry();
        if (false === $entry->get_can_preview_by_cloud()) {
            error_log('[WP Cloud Plugin message]: '.sprintf('File %s cannot be previewed.', $id));

            return '';
        }

        if (!isset($params['scope'])) {
            $params['scope'] = ('personal' === App::get_current_account()->get_type()) ? 'anonymous' : Processor::instance()->get_setting('link_scope');
        }

        do_action('shareonedrive_api_before_create_embedded_url', $id, $params);

        /* For images, just return the actual file
        * BUG in API: embedded url of image files don't work
        */
        if (in_array($entry->get_extension(), ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
            return SHAREONEDRIVE_ADMIN_URL.'?action=shareonedrive-embed-image&id='.$id.'&account_id='.$node->get_account_id().'&drive_id='.$node->get_drive_id();
        }

        /* Only OneDrive personal Accounts can create embedded links to documents
         * For the other accounts we need to add 'action=embedview' to the view url
         */

        if ('personal' === App::get_current_account()->get_type()) {
            $embed_supported = ['csv', 'doc', 'docx', 'odp', 'ods', 'odt', 'pot', 'potm', 'potx', 'pps', 'ppsx', 'ppsxm', 'ppt', 'pptm', 'pptx', 'rtf', 'xlsx', 'pdf'];
            $embed = in_array($entry->get_extension(), $embed_supported);

            $embedded_link = self::create_shared_url($id, ['type' => 'embed', 'scope' => $params['scope']]);
            $embedded_link = str_replace('redir?', 'embed?', $embedded_link);

            if ($embed) {
                $embedded_link .= (false === strpos($embedded_link, '&em=2')) ? '&em=2' : ''; // Open embedded file directly
                $embedded_link .= '&wdHideHeaders=True';
                $embedded_link .= '&wdDownloadButton=False';
            }

            return $embedded_link;
        }

        // Business accounts can use the Preview API */
        $preview_supported = ['3mf', 'cool', 'glb', 'gltf', 'obj', 'stl', 'dwg', 'fbx', 'epub', 'ai', 'pdf', 'psb', 'psd', 'html', 'txt'];
        if (false !== in_array($entry->get_extension(), $preview_supported)) {
            // Business accounts can use the Preview API */
            return SHAREONEDRIVE_ADMIN_URL.'?action=shareonedrive-embed-redirect&id='.$id.'&account_id='.$node->get_account_id().'&drive_id='.$node->get_drive_id();
        }

        $embedded_link = self::create_shared_url($id, ['type' => 'view', 'scope' => $params['scope']]);
        $embedded_link .= (strpos($embedded_link, '?')) ? '&action=embedview' : '?action=embedview';

        $embedded_link = apply_filters('shareonedrive_api_create_embed_url_set_link', $embedded_link);

        do_action('shareonedrive_api_after_create_embedded_url', $embedded_link);

        return $embedded_link;
    }

    /**
     * Create a public shared url for a file or folder.
     *
     * @param string $id     ID of the entry for which you want to create the shared url
     * @param array  $params
     *
     * @return API_Exception|string Returns the shared url
     */
    public static function create_shared_url($id, $params = [])
    {
        // Get file meta data
        $node = self::get_entry($id);

        if (false === $node) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Failed to find entry: %s', $id));

            return false;
        }

        $params = array_merge(['type' => 'view', 'scope' => 'anonymous'], $params);

        $params = apply_filters('shareonedrive_api_create_shared_url_set_params', $params, $node);

        do_action('shareonedrive_api_before_create_shared_url', $id, $params);

        try {
            /**
             * @var SODOneDrive_Service_Drive_Permission
             */
            $permission = App::instance()->get_drive()->items->createlink($id, $params, ['driveId' => $node->get_drive_id()]);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return esc_html__('The feature is disabled.', 'wpcloudplugins').' '.esc_html__('Please check the sharing permissions for the OneDrive / SharePoint library to see if sharing is allowed.', 'wpcloudplugins');
        }

        $url = $node->add_shared_link($permission, $params);

        Cache::instance()->set_updated();

        do_action('shareonedrive_log_event', 'shareonedrive_updated_metadata', $node, ['metadata_field' => 'Sharing Permissions']);

        do_action('shareonedrive_api_after_create_shared_url', $url);

        return $url;
    }

    /**
     * Create a temporarily download url for a file or folder.
     *
     * @param string $id     ID of the entry for which you want to create the temporarily download url
     * @param string $format Format for the downloaded file. Only 'default' currently supported
     * @param array  $params
     *
     * @return API_Exception|string
     */
    public static function create_temporarily_download_url($id, $format = 'default', $params = [])
    {
        // Get file meta data
        $node = self::get_entry($id);

        if (false === $node) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Failed to find entry: %s', $id));

            return false;
        }

        do_action('shareonedrive_api_before_create_temporarily_download_url', $id, $format, $params);

        // 1: Get Temporarily link from cache
        if (false !== $node) {
            if ($url = $node->get_temporarily_link($format)) {
                $url = apply_filters('shareonedrive_api_create_temporarily_download_url_set_url', $url);

                do_action('shareonedrive_api_after_create_temporarily_download_url', $id, $format, $url);

                return $url;
            }
        }

        // 2: Get Temporarily link via API
        try {
            // Get a Download link via the Graph API
            if ('default' === $format) {
                $url = App::instance()->get_drive()->items->download($id, ['driveId' => $node->get_drive_id()]);
            } else {
                $url = App::instance()->get_drive()->items->export($id, ['driveId' => $node->get_drive_id(), 'format' => $format]);
            }

            if (!empty($url)) {
                $node->add_temporarily_link($url, $format);
            } else {
                error_log(sprintf('[WP Cloud Plugin message]: Cannot generate temporarily download link:', __LINE__));

                return false;
            }
        } catch (\Exception $ex) {
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        Cache::instance()->set_updated();

        $url = $node->get_temporarily_link($format);
        $url = apply_filters('shareonedrive_api_create_temporarily_download_url_set_url', $url);

        do_action('shareonedrive_api_after_create_temporarily_download_url', $id, $format, $url);

        return $url;
    }

    /**
     * Create an url to an editable view of the file.
     *
     * @param string $id     ID of the entry for which you want to create the editable url
     * @param array  $params
     *
     * @return API_Exception|string
     */
    public static function create_edit_url($id, $params = ['type' => 'edit'])
    {
        // Get file meta data
        $node = self::get_entry($id);

        if (false === $node) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Failed to find entry: %s', $id));

            return false;
        }

        $entry = $node->get_entry();
        if (false === $entry->get_can_edit_by_cloud()) {
            error_log('[WP Cloud Plugin message]: '.sprintf('File %s cannot be edited.', $id));

            return false;
        }

        $params = apply_filters('shareonedrive_api_create_edit_url_set_params', $params);

        do_action('shareonedrive_api_before_create_edit_url', $id, $params);

        $link = self::create_shared_url($id, ['type' => 'edit']);

        do_action('shareonedrive_api_after_create_edit_url', $link);

        return apply_filters('shareonedrive_api_create_edit_url_set_url', $link, $params, $node);
    }
}

/**
 * API_Exception Class.
 *
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */
class API_Exception extends \Exception
{
}
