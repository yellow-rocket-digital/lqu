<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\ShareoneDrive;

if (!function_exists('onedrive_api_php_client_autoload')) {
    require_once SHAREONEDRIVE_ROOTDIR.'/vendors/API/autoload.php';
}

class Client
{
    public $apifilefields = 'thumbnails(select=c48x48,medium,large,c1500x1500),children(expand=thumbnails(select=c48x48,medium,large,c1500x1500))';
    public $apifilefieldsexpire = 'thumbnails(select=c48x48,medium,large,c1500x1500),children(expand=thumbnails(select=c48x48,medium,large,c1500x1500))';
    public $apilistfilesfields = 'thumbnails(select=c48x48,medium,large,c1500x1500)';
    public $apilistfilesexpirefields = 'thumbnails(select=c48x48,medium,large,c1500x1500)';

    /**
     * The single instance of the class.
     *
     * @var Client
     */
    protected static $_instance;

    /**
     * Client Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Client - Client instance
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
     * Get folders and files.
     *
     * @param string $id
     * @param bool   $checkauthorized
     * @param mixed  $drive_id
     *
     * @return array|bool
     */
    public function get_folder($id = false, $checkauthorized = true)
    {
        if (false === $id) {
            $id = Processor::instance()->get_requested_entry();
        }

        try {
            $cached_node = API::get_folder($id);
        } catch (\Exception $ex) {
            return false;
        }

        // Check if folder is in the shortcode-set rootfolder
        if (true === $checkauthorized) {
            if (!Processor::instance()->_is_entry_authorized($cached_node)) {
                return false;
            }
        }

        return ['folder' => $cached_node, 'contents' => $cached_node->get_children()];
    }

    // Get entry

    /**
     * @param type  $id
     * @param type  $checkauthorized
     * @param mixed $drive_id
     *
     * @return bool|\TheLion\ShareoneDrive\CacheNode
     */
    public function get_entry($id = false, $checkauthorized = true)
    {
        if (false === $id) {
            $id = Processor::instance()->get_requested_entry();
        }

        try {
            $cached_node = API::get_entry($id);
        } catch (\Exception $ex) {
            return false;
        }

        if (true === $checkauthorized) {
            if ('root' !== $id && !Processor::instance()->_is_entry_authorized($cached_node)) {
                return false;
            }
        }

        return $cached_node;
    }

    public function update_expired_entry(CacheNode $cached_node)
    {
        $entry = $cached_node->get_entry();

        try {
            $api_entry = App::instance()->get_drive()->items->get($entry->get_id(), ['driveId' => $cached_node->get_drive_id(), 'expand' => $this->apifilefieldsexpire]);
            $entry = new Entry($api_entry);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        return Cache::instance()->add_to_cache($entry);
    }

    public function update_expired_folder(CacheNode $cached_node)
    {
        $entry = $cached_node->get_entry();

        try {
            $results_children = App::instance()->get_drive()->items->children($entry->get_id(), ['driveId' => $cached_node->get_drive_id(), 'expand' => $this->apilistfilesexpirefields]);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        $files_in_folder = $results_children->getValue();
        $next_page_token = $results_children['@odata.nextLink'];

        // Get all files in folder
        while (!empty($next_page_token)) {
            $next_link = parse_url($next_page_token);
            parse_str($next_link['query'], $next_link_attributes);
            $next_page_token = $next_link_attributes['$skiptoken'];

            try {
                $more_files = App::instance()->get_drive()->items->children($entry->get_id(), ['driveId' => $cached_node->get_drive_id(), 'expand' => $this->apilistfilesfields, 'skiptoken' => $next_page_token]);
                $files_in_folder = array_merge($files_in_folder, $more_files->getValue());
                $next_page_token = isset($next_link_attributes['@odata.nextLink']) ? $next_link_attributes['@odata.nextLink'] : null;
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

                return false;
            }
        }

        $folder_items = [];
        $current_children = $cached_node->get_children();
        foreach ($files_in_folder as $api_entry) {
            $entry = new Entry($api_entry);
            Cache::instance()->add_to_cache($entry);
        }

        Cache::instance()->add_to_cache($cached_node->get_entry());

        return $cached_node;
    }

    // Search entry by name
    public function search_by_name($query)
    {
        if ('parent' === Processor::instance()->get_shortcode_option('searchfrom')) {
            $searched_folder_id = Processor::instance()->get_requested_entry();
        } else {
            $searched_folder_id = Processor::instance()->get_root_folder();
        }

        $searched_folder = $this->get_folder($searched_folder_id, true);
        $search_contents = ('1' === Processor::instance()->get_shortcode_option('searchcontents'));

        $results = API::search_by_name($query, $searched_folder_id, $search_contents);

        foreach ($results as $key => $node) {
            // Update the time_limit as this can take a while
            @set_time_limit(30);

            if (false === Processor::instance()->_is_entry_authorized($node)) {
                unset($results[$key]);

                continue;
            }

            if (false === $node->is_in_folder($searched_folder_id)) {
                unset($results[$key]);

                continue;
            }
        }

        return ['folder' => $searched_folder['folder'], 'contents' => $results];
    }

    // Delete multiple files from OneDrive

    public function delete_entries($entries_to_delete = [])
    {
        foreach ($entries_to_delete as $key => $target_entry_path) {
            $target_cached_entry = $this->get_entry($target_entry_path);

            if (false === $target_cached_entry) {
                unset($entries_to_delete[$key]);

                continue;
            }

            $target_entry = $target_cached_entry->get_entry();

            if ($target_entry->is_file() && false === User::can_delete_files()) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to delete %s as user is not allowed to remove files.', $target_entry->get_path()));
                unset($entries_to_delete[$key]);

                continue;
            }

            if ($target_entry->is_dir() && false === User::can_delete_folders()) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to delete %s as user is not allowed to remove folders.', $target_entry->get_path()));
                unset($entries_to_delete[$key]);

                continue;
            }

            if ('1' === Processor::instance()->get_shortcode_option('demo')) {
                unset($entries_to_delete[$key]);

                continue;
            }
        }

        try {
            $deleted_entries = API::delete($entries_to_delete);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

            return [];
        }

        if ('1' === Processor::instance()->get_shortcode_option('notificationdeletion')) {
            Processor::instance()->send_notification_email('deletion', $deleted_entries);
        }

        return $deleted_entries;
    }

    // Rename entry from OneDrive

    public function rename_entry($new_filename = null)
    {
        if ('1' === Processor::instance()->get_shortcode_option('demo')) {
            return new \WP_Error('broke', esc_html__('Failed to rename file.', 'wpcloudplugins'));
        }

        if (null === $new_filename) {
            return new \WP_Error('broke', esc_html__('No new name set', 'wpcloudplugins'));
        }

        // Get entry meta data
        $cached_node = Cache::instance()->is_cached(Processor::instance()->get_requested_entry(), App::get_current_drive_id());

        if (false === $cached_node) {
            $cached_node = $this->get_entry(Processor::instance()->get_requested_entry());
            if (false === $cached_node) {
                return new \WP_Error('broke', esc_html__('Failed to rename file.', 'wpcloudplugins'));
            }
        }

        // Check if user is allowed to delete from this dir
        if (!$cached_node->is_in_folder(Processor::instance()->get_last_folder())) {
            return new \WP_Error('broke', esc_html__('You are not authorized to rename files in this directory', 'wpcloudplugins'));
        }

        $entry = $cached_node->get_entry();

        // Check user permission
        if (!$entry->get_permission('canrename')) {
            return new \WP_Error('broke', esc_html__('You are not authorized to rename this file or folder', 'wpcloudplugins'));
        }

        // Check if entry is allowed
        if (!Processor::instance()->_is_entry_authorized($cached_node)) {
            return new \WP_Error('broke', esc_html__('You are not authorized to rename this file or folder', 'wpcloudplugins'));
        }

        if ($entry->is_dir() && (false === User::can_rename_folders())) {
            return new \WP_Error('broke', esc_html__('You are not authorized to rename folder', 'wpcloudplugins'));
        }

        if ($entry->is_file() && (false === User::can_rename_files())) {
            return new \WP_Error('broke', esc_html__('You are not authorized to rename this file', 'wpcloudplugins'));
        }

        $extension = $entry->get_extension();
        $name = (!empty($extension)) ? $new_filename.'.'.$extension : $new_filename;
        $updaterequest = ['name' => $name];

        try {
            API::set_drive_by_id($cached_node->get_drive_id());
            $renamed_entry = API::patch($entry->get_id(), $updaterequest);

            if (false !== $renamed_entry && null !== $renamed_entry) {
                Cache::instance()->update_cache();
            }

            do_action('shareonedrive_log_event', 'shareonedrive_renamed_entry', $renamed_entry, ['old_name' => $entry->get_name()]);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

            return new \WP_Error('broke', esc_html__('Failed to rename file.', 'wpcloudplugins'));
        }

        Cache::instance()->pull_for_changes(true);

        return $renamed_entry;
    }

    // Move & Copy
    public function move_entries($entries, $target, $copy = false)
    {
        $entries_to_move = [];

        $cached_target = $this->get_entry($target);
        $cached_current_folder = $this->get_entry(Processor::instance()->get_last_folder());

        if (false === $cached_target) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Failed to move as target folder %s is not found.', $target));

            return $entries_to_move;
        }

        foreach ($entries as $key => $entry_id) {
            $entries_to_move[$entry_id] = false; // Set after Request is finished

            $cached_node = $this->get_entry($entry_id);

            if (false === $cached_node) {
                unset($entries[$key]);

                continue;
            }

            $entry = $cached_node->get_entry();

            if (!$copy && $entry->is_dir() && (false === User::can_move_folders())) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to move %s as user is not allowed to move folders.', $cached_target->get_name()));
                unset($entries[$key]);

                continue;
            }

            if ($copy && $entry->is_dir() && (false === User::can_copy_folders())) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to copy %s as user is not allowed to copy folders.', $cached_target->get_name()));
                unset($entries[$key]);

                continue;
            }

            if (!$copy && $entry->is_file() && (false === User::can_move_files())) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to move %s as user is not allowed to move files.', $cached_target->get_name()));
                unset($entries[$key]);

                continue;
            }

            if ($copy && $entry->is_file() && (false === User::can_copy_files())) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to copy %s as user is not allowed to copy files.', $cached_target->get_name()));
                unset($entries[$key]);

                continue;
            }

            if ('1' === Processor::instance()->get_shortcode_option('demo')) {
                unset($entries[$key]);

                continue;
            }

            // Check if user is allowed to delete from this dir
            if (!$cached_node->is_in_folder($cached_current_folder->get_id())) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to move %s as user is not allowed to move items in this directory.', $cached_target->get_name()));
                unset($entries[$key]);

                continue;
            }

            // Check user permission
            if (!$entry->get_permission('canmove')) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to move %s as the sharing permissions on it prevent this.', $cached_target->get_name()));
                unset($entries[$key]);

                continue;
            }
        }

        try {
            $entries_to_move = API::move($entries, $target, $copy);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

            return $entries_to_move;
        }

        // Clear Cached Requests
        CacheRequest::clear_local_cache_for_shortcode(App::get_current_account()->get_id(), Processor::instance()->get_listtoken());

        return $entries_to_move;
    }

    // Edit description of entry

    public function update_description($new_description = null)
    {
        if (null === $new_description) {
            return new \WP_Error('broke', esc_html__('No new description set', 'wpcloudplugins'));
        }

        // Get entry meta data
        $cached_node = Cache::instance()->is_cached(Processor::instance()->get_requested_entry(), App::get_current_drive_id());

        if (false === $cached_node) {
            $cached_node = $this->get_entry(Processor::instance()->get_requested_entry());
            if (false === $cached_node) {
                return new \WP_Error('broke', esc_html__('Failed to edit file.', 'wpcloudplugins'));
            }
        }

        // Check if user is allowed to delete from this dir
        if (!$cached_node->is_in_folder(Processor::instance()->get_last_folder())) {
            return new \WP_Error('broke', esc_html__('You are not authorized to edit files in this directory', 'wpcloudplugins'));
        }

        $entry = $cached_node->get_entry();

        // Check if entry is allowed
        if (!Processor::instance()->_is_entry_authorized($cached_node)) {
            return new \WP_Error('broke', esc_html__('You are not authorized to edit this file or folder', 'wpcloudplugins'));
        }

        // Set new description, and update the entry
        $updaterequest = ['description' => $new_description];

        try {
            API::set_drive_by_id($cached_node->get_drive_id());
            $edited_entry = API::patch($entry->get_id(), $updaterequest);

            if (false !== $edited_entry && null !== $edited_entry) {
                do_action('shareonedrive_log_event', 'shareonedrive_updated_description', $edited_entry, ['description' => $new_description]);
                Cache::instance()->update_cache();
            }
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

            return new \WP_Error('broke', esc_html__('Failed to edit file.', 'wpcloudplugins'));
        }

        return $edited_entry->get_entry()->get_description();
    }

    // Add directory to OneDrive

    public function add_folder($new_folder_name = null)
    {
        if ('1' === Processor::instance()->get_shortcode_option('demo')) {
            return new \WP_Error('broke', esc_html__('Failed to add folder', 'wpcloudplugins'));
        }

        if (null === $new_folder_name) {
            return new \WP_Error('broke', esc_html__('No new foldername set', 'wpcloudplugins'));
        }

        // Get entry meta data of current folder
        $cached_node = Cache::instance()->is_cached(Processor::instance()->get_last_folder(), App::get_current_drive_id());

        if (false === $cached_node) {
            $cached_node = $this->get_entry(Processor::instance()->get_last_folder());
            if (false === $cached_node) {
                return new \WP_Error('broke', esc_html__('Failed to add file.', 'wpcloudplugins'));
            }
        }

        if (!Processor::instance()->_is_entry_authorized($cached_node)) {
            return new \WP_Error('broke', esc_html__('You are not authorized to add folders in this directory', 'wpcloudplugins'));
        }

        $currentfolder = $cached_node->get_entry();

        // Check user permission
        if (!$currentfolder->get_permission('canadd')) {
            return new \WP_Error('broke', esc_html__('You are not authorized to add a folder', 'wpcloudplugins'));
        }

        try {
            API::set_drive_by_id($cached_node->get_drive_id());
            $cached_node = API::create_folder($new_folder_name, $currentfolder->get_id());
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

            return new \WP_Error('broke', esc_html__('Failed to add folder', 'wpcloudplugins'));
        }

        return $cached_node;
    }

    public function get_folder_thumbnails($folders_ids = [], $height = 250, $width = 250, $maximages = 3)
    {
        $thumbnails = [];

        foreach ($folders_ids as $folder_id) {
            $folder_data = $this->get_folder($folder_id);
            $cached_folder_node = $folder_data['folder'];

            if (false === $cached_folder_node->has_children()) {
                continue;
            }

            $children = $folder_data['contents'];
            // Try to load default thumbnail (.thumb.jpg or .thumb.png){
            $default_thumb = Cache::instance()->get_node_by_name('.thumb.jpg', $cached_folder_node->get_drive_id(), $cached_folder_node);
            if (empty($default_thumb)) {
                $default_thumb = Cache::instance()->get_node_by_name('.thumb.png', $cached_folder_node->get_drive_id(), $cached_folder_node);
            }

            if (!empty($default_thumb)) {
                $children = [$default_thumb];
            }

            $thumbnails[$folder_id] = $folder_thumbs = [];

            // Else get $maximages images from the folder
            for ($i = 1; $i <= $maximages; ++$i) {
                $cached_child_node = current($children);
                $child_entry = $cached_child_node->get_entry();

                // Skip Folder children or files without thumbnail
                if ($cached_child_node->is_dir() || !$child_entry->has_own_thumbnail()) {
                    --$i;

                    if (false === next($children)) {
                        break;
                    }

                    continue;
                }

                // Get Thumbnail from child
                $thumbnail_url = $child_entry->get_thumbnail_with_size($height, $width, 'center');

                // Set an array of thumbnails for the requested folder ID
                $thumbnails[$folder_id][] = $thumbnail_url;

                // Set a new Thumbnailset for the folder
                $medium_thumbnail = new \SODOneDrive_Service_Drive_Thumbnail();
                $medium_thumbnail->setUrl($thumbnail_url);
                $thumbnail_set = new \SODOneDrive_Service_Drive_ThumbnailSet();
                $thumbnail_set->setC48x48($medium_thumbnail);
                $thumbnail_set->setMedium($medium_thumbnail);
                $thumbnail_set->setSmall($medium_thumbnail);

                $folder_thumbs[] = $thumbnail_set;
                // Stop if there are no further children
                if (false === next($children)) {
                    break;
                }
            }

            $cached_folder_node->get_entry()->set_folder_thumbnails($folder_thumbs);
            $cached_folder_node->get_entry()->set_has_own_thumbnail(true);
        }

        // Clear Cache
        Cache::instance()->set_updated();

        return $thumbnails;
    }

    public function preview_entry()
    {
        // Get file meta data
        $cached_node = $this->get_entry();

        if (false === $cached_node) {
            exit('-1');
        }

        $entry = $cached_node->get_entry();
        if (false === $entry->get_can_preview_by_cloud()) {
            exit('-1');
        }

        if (false === User::can_preview()) {
            exit('-1');
        }

        do_action('shareonedrive_log_event', 'shareonedrive_previewed_entry', $cached_node);

        // Preview for Image files
        if (in_array($entry->get_extension(), ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
            if (isset($_REQUEST['inline']) && User::can_download()) {
                $url = $this->get_shared_link($cached_node).'?raw=1';
                header('Location: '.$url);

                exit;
            }

            if ('onedrivethumbnail' === Processor::instance()->get_setting('loadimages') || false === User::can_download()) {
                if (null !== $entry->get_thumbnail_original()) {
                    header('Location: '.$entry->get_thumbnail_original());

                    exit;
                }
                if (null !== $entry->get_thumbnail_large()) {
                    header('Location: '.$entry->get_thumbnail_large());

                    exit;
                }
            }
        }

        // Preview for Media files in HTML5 Player + PDF files
        if (in_array($entry->get_extension(), ['jpg', 'jpeg', 'gif', 'png', 'webp', 'mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga', 'flac'])) {
            $temporarily_link = API::create_temporarily_download_url($cached_node->get_id());
            header('Location: '.$temporarily_link);

            exit;
        }

        // Business accounts can use the Preview API */
        if ('personal' !== App::get_current_account()->get_type()) {
            $preview_link = API::get_preview_link($cached_node->get_id());
            header('Location: '.$preview_link);

            exit;
        }

        // Personal accounts need to use Shared/Temporarily links
        if (in_array($entry->get_extension(), ['pdf'])) {
            $temporarily_link = API::get_embed_url($cached_node->get_id());
            header('Location: '.$temporarily_link);

            exit;
        }

        // Preview for Office files
        if (in_array($entry->get_extension(), ['csv', 'doc', 'docx', 'odp', 'ods', 'odt', 'pot', 'potm', 'potx', 'pps', 'ppsx', 'ppsxm', 'ppt', 'pptm', 'pptx', 'rtf', 'xls', 'xlsx'])) {
            if (User::can_edit()) {
                // If user has permissions to edit the file, show the preview in Office Online
                $shared_link = API::get_embed_url($cached_node->get_id());

                if (!empty($shared_link)) {
                    header('Location: '.$shared_link);

                    exit;
                }
            }
        }

        // Preview for all other formats
        $temporarily_link = API::create_temporarily_download_url($cached_node->get_id(), 'pdf');

        header('Location: '.$temporarily_link);

        exit;
    }

    public function edit_entry()
    {
        // Get file meta data
        $cached_node = $this->get_entry();

        if (false === $cached_node) {
            exit('-1');
        }

        $entry = $cached_node->get_entry();
        if (false === $entry->get_can_edit_by_cloud()) {
            exit('-1');
        }

        $edit_link = $this->get_shared_link($cached_node, ['type' => 'edit']);

        if (empty($edit_link)) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Cannot create a editable shared link %s', __LINE__));

            exit;
        }

        do_action('shareonedrive_edit', $cached_node);
        do_action('shareonedrive_log_event', 'shareonedrive_edited_entry', $cached_node);

        header('Location: '.$edit_link);

        exit;
    }

    // Download file

    public function download_entry()
    {
        // Check if file is cached and still valid
        $cached = Cache::instance()->is_cached(Processor::instance()->get_requested_entry(), App::get_current_drive_id());

        if (false === $cached) {
            $cached_node = $this->get_entry(Processor::instance()->get_requested_entry());
        } else {
            $cached_node = $cached;
        }

        if (false === $cached_node) {
            exit;
        }

        $entry = $cached_node->get_entry();

        // get the last-modified-date of this very file
        $lastModified = $entry->get_last_edited();
        // get a unique hash of this file (etag)
        $etagFile = md5($lastModified);
        // get the HTTP_IF_MODIFIED_SINCE header if set
        $ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
        // get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
        $etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastModified).' GMT');
        header("Etag: {$etagFile}");
        header('Expires: '.gmdate('D, d M Y H:i:s', time() + 60 * 5).' GMT');
        header('Cache-Control: must-revalidate');

        // check if page has changed. If not, send 304 and exit
        if (false !== $cached) {
            if (false !== $lastModified && (@strtotime($ifModifiedSince) == $lastModified || $etagHeader == $etagFile)) {
                // Send email if needed
                if ('1' === Processor::instance()->get_shortcode_option('notificationdownload')) {
                    Processor::instance()->send_notification_email('download', [$cached_node]);
                }

                do_action('shareonedrive_download', $cached_node);
                header('HTTP/1.1 304 Not Modified');

                exit;
            }
        }

        // Check if entry is allowed
        if (!Processor::instance()->_is_entry_authorized($cached_node)) {
            exit;
        }

        // Send email if needed
        if ('1' === Processor::instance()->get_shortcode_option('notificationdownload')) {
            Processor::instance()->send_notification_email('download', [$cached_node]);
        }

        // Redirect if needed
        if ('url' === $entry->get_extension()) {
            $download_url = API::create_temporarily_download_url($cached_node->get_id());

            $request = new \SODOneDrive_Http_Request($download_url, 'GET');

            try {
                $httpRequest = App::instance()->get_sdk_client()->getAuth()->authenticatedRequest($request);
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

                exit;
            }

            preg_match_all('/URL=(.*)/', $httpRequest->getResponseBody(), $location, PREG_SET_ORDER);

            if (2 === count($location[0])) {
                $temporarily_link = $location[0][1];
                header('Location: '.$temporarily_link);

                exit;
            }
        }

        if (isset($_REQUEST['redirect']) && ('redirect' === Processor::instance()->get_shortcode_option('onclick'))) {
            $shared_link = $this->get_shared_link($cached_node, ['type' => 'view']);
            header('Location: '.$shared_link);

            exit;
        }

        // Get the complete file
        $extension = (isset($_REQUEST['extension'])) ? $_REQUEST['extension'] : 'default';
        $this->download_content($cached_node, $extension);

        exit;
    }

    public function download_content(CacheNode $cached_node, $extension = 'default')
    {
        // If there is a temporarily download url present for this file, just redirect the user
        $stream = (isset($_REQUEST['action']) && 'shareonedrive-stream' === $_REQUEST['action'] && !isset($_REQUEST['caption']));
        $stored_url = ($stream) ? get_transient('shareonedrive'.$cached_node->get_id().'_'.$cached_node->get_entry()->get_extension()) : get_transient('shareonedrive'.$cached_node->get_id().'_'.$cached_node->get_entry()->get_extension());
        if (false !== $stored_url && filter_var($stored_url, FILTER_VALIDATE_URL)) {
            do_action('shareonedrive_download', $cached_node, $stored_url);
            header('Location: '.$stored_url);

            exit;
        }

        $temporarily_link = API::create_temporarily_download_url($cached_node->get_id(), $extension);

        // Download Hook
        do_action('shareonedrive_download', $cached_node, $temporarily_link);

        $event_type = ($stream) ? 'shareonedrive_streamed_entry' : 'shareonedrive_downloaded_entry';
        do_action('shareonedrive_log_event', $event_type, $cached_node);

        header('Location: '.$temporarily_link);

        set_transient('shareonedrive'.(($stream) ? 'stream' : 'download').'_'.$cached_node->get_id().'_'.$cached_node->get_entry()->get_extension(), $temporarily_link, MINUTE_IN_SECONDS * 10);

        exit;
    }

    public function download_via_proxy(Entry $entry, $url)
    {
        // Stop WP from buffering
        wp_ob_end_flush_all();

        set_time_limit(500);

        $filename = basename($entry->get_name());

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; '.sprintf('filename="%s"; ', rawurlencode($filename)).sprintf("filename*=utf-8''%s", rawurlencode($filename)));
        header("Content-length: {$entry->get_size()}");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $data) {
            echo $data;

            return strlen($data);
        });
        curl_exec($ch);
        curl_close($ch);

        exit;
    }

    public function stream_entry()
    {
        // Check if file is cached and still valid
        $cached = Cache::instance()->is_cached(Processor::instance()->get_requested_entry(), App::get_current_drive_id());

        if (false === $cached) {
            $cached_node = $this->get_entry(Processor::instance()->get_requested_entry());
        } else {
            $cached_node = $cached;
        }

        if (false === $cached_node) {
            exit;
        }

        $entry = $cached_node->get_entry();

        $extension = $entry->get_extension();
        $allowedextensions = ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'oga', 'wav', 'webm', 'flac', 'vtt', 'srt'];

        if (empty($extension) || !in_array($extension, $allowedextensions)) {
            exit;
        }

        // Download Captions directly
        if (in_array($extension, ['vtt', 'srt'])) {
            $temporarily_link = API::create_temporarily_download_url($cached_node->get_id());
            header('Location: '.$temporarily_link);

            exit;
        }

        $this->download_entry();
    }

    public function get_folder_recursive(CacheNode $cached_node, $list_of_cached_entries = [])
    {
        if (false === Processor::instance()->_is_entry_authorized($cached_node)) {
            return $list_of_cached_entries;
        }

        if ($cached_node->get_entry()->is_file()) {
            $list_of_cached_entries[$cached_node->get_id()] = $cached_node;

            return $list_of_cached_entries;
        }

        $result = $this->get_folder($cached_node->get_id());
        if (empty($result)) {
            return $list_of_cached_entries;
        }

        $cached_folder = $result['folder'];

        if (false === $cached_folder->has_children()) {
            return $list_of_cached_entries;
        }

        foreach ($cached_folder->get_children() as $cached_child_entry) {
            $new_of_cached_entries = $this->get_folder_recursive($cached_child_entry, $list_of_cached_entries);
            $list_of_cached_entries = array_merge($list_of_cached_entries, $new_of_cached_entries);
        }

        return $list_of_cached_entries;
    }

    public function has_temporarily_link($cached_node, $extension = 'default')
    {
        if ($cached_node instanceof Entry) {
            $cached_node = $this->get_entry($cached_node->get_id());
        }

        if (false !== $cached_node) {
            if ($temporarily_link = $cached_node->get_temporarily_link($extension)) {
                return true;
            }
        }

        return false;
    }

    public function get_shared_link($cached_node, $link_settings = ['type' => 'view'])
    {
        if ($cached_node instanceof Entry) {
            $cached_node = $this->get_entry($cached_node->get_id());
        }

        // Custom link settings for Business accounts
        if (empty($link_settings) && in_array(App::get_current_account()->get_type(), ['business'])) {
            // Add Password
            $password = Processor::instance()->get_shortcode_option('share_password');
            if (!empty($password)) {
                $link_settings['password'] = $password;
            }

            // Add Expire date
            $expire_after = Processor::instance()->get_shortcode_option('share_expire_after');
            if (!empty($expire_after)) {
                $expire_date = current_datetime()->modify('+'.$expire_after);
                $link_settings['expirationDateTime'] = $expire_date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
            }
        }

        if (empty($link_settings['type'])) {
            $link_settings['type'] = 'view';
        }

        if (empty($link_settings['scope']) && 'business' === App::get_current_account()->get_type()) {
            $link_settings['scope'] = Processor::instance()->get_setting('link_scope');
        }

        if (false !== $cached_node) {
            if ($shared_link = $cached_node->get_shared_link($link_settings)) {
                do_action('shareonedrive_log_event', 'shareonedrive_created_link_to_entry', $cached_node, ['url' => $shared_link]);

                return $shared_link;
            }
        }

        $shared_link = API::create_shared_url($cached_node->get_id(), $link_settings);
        do_action('shareonedrive_log_event', 'shareonedrive_created_link_to_entry', $cached_node, ['url' => $shared_link]);

        return $shared_link;
    }

    public function get_shared_link_for_output($entry_id = false, $as_editable = false)
    {
        $cached_node = $this->get_entry($entry_id);

        if (false === $cached_node) {
            exit(-1);
        }

        $entry = $cached_node->get_entry();

        if ($as_editable) {
            $shared_link = $this->get_shared_link($cached_node, ['type' => 'edit']);
        } else {
            $shared_link = $this->get_shared_link($cached_node, []);
        }

        $embed_link = API::get_embed_url($cached_node->get_id());

        return [
            'name' => $entry->get_name(),
            'extension' => $entry->get_extension(),
            'link' => API::shorten_url($shared_link, null, ['name' => $cached_node->get_name()]),
            'embeddedlink' => API::shorten_url($embed_link, null, ['name' => $cached_node->get_name()]),
            'size' => Helpers::bytes_to_size_1024($entry->get_size()),
            'error' => false,
        ];
    }

    // Pull for changes
    public function get_changes($change_token = false, $drive_id = null)
    {
        if (empty($change_token)) {
            try {
                $result = App::instance()->get_drive()->changes->getlatest('root', ['driveId' => $drive_id]);
                preg_match("/delta.token=\\'?([a-zA-Z0-9;%\\-=_]+)\\'?/", $result['@odata.deltaLink'], $matches);
                $new_change_token = $result['@odata.deltaLink']; // $matches[1];
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

                return false;
            }
        }

        $changes = [];

        while (null != $change_token) {
            try {
                $expected_class = 'SODOneDrive_Service_Drive_Changes';

                $httpRequest = new \SODOneDrive_Http_Request($change_token, 'GET');
                $httpRequest = App::instance()->get_sdk_client()->getAuth()->sign($httpRequest);
                $httpRequest->setExpectedClass('SODOneDrive_Service_Drive_Changes');
                $result = App::instance()->get_sdk_client()->execute($httpRequest);

                if (isset($result['@odata.nextLink'])) {
                    $change_token = $result['@odata.nextLink'];
                } else {
                    $change_token = null;
                    $new_change_token = $result['@odata.deltaLink'];
                }

                $changes = array_merge($changes, $result->getValue());
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

                return false;
            }
        }

        $list_of_update_entries = [];
        foreach ($changes as $change) {
            if ('root' === $change->getName()) {
                continue;
            }

            // File is updated
            $change_entry = new Entry($change);
            $list_of_update_entries[$change->getId()] = $change_entry;
        }

        return [$new_change_token, $list_of_update_entries];
    }

    /**
     * @deprecated
     *
     * @return \TheLion\ShareoneDrive\App
     */
    public function get_app()
    {
        Helpers::is_deprecated('function', 'get_app()', '\TheLion\ShareoneDrive\App::instance()');

        return App::instance();
    }

    /**
     * @deprecated
     *
     * @return \SODOneDrive_Client
     */
    public function get_library()
    {
        Helpers::is_deprecated('function', 'get_library()', '\TheLion\ShareoneDrive\App::instance()->get_sdk_client()');

        return App::instance()->get_sdk_client();
    }
}
