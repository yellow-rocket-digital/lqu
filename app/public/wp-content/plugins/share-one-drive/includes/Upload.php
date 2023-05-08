<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\ShareoneDrive;

class Upload
{
    /**
     * @var WPCP_UploadHandler
     */
    private $upload_handler;

    public function __construct()
    {
        wp_using_ext_object_cache(false);
    }

    public function upload_pre_process()
    {
        do_action('shareonedrive_upload_pre_process', Processor::instance());

        $result = ['result' => 1];

        $result = apply_filters('shareonedrive_upload_pre_process_result', $result, Processor::instance());

        echo json_encode($result);
    }

    public function do_upload()
    {
        // Upload File to server
        if (!class_exists('WPCP_UploadHandler')) {
            require SHAREONEDRIVE_ROOTDIR.'/vendors/jquery-file-upload/server/UploadHandler.php';
        }

        if ('1' === Processor::instance()->get_shortcode_option('demo')) {
            // TO DO LOG + FAIL ERROR
            exit(-1);
        }

        $shortcode_max_file_size = Processor::instance()->get_shortcode_option('maxfilesize');
        $shortcode_min_file_size = Processor::instance()->get_shortcode_option('minfilesize');
        $accept_file_types = '/.('.Processor::instance()->get_shortcode_option('upload_ext').')$/i';
        $post_max_size_bytes = min(Helpers::return_bytes(ini_get('post_max_size')), Helpers::return_bytes(ini_get('upload_max_filesize')));
        $max_file_size = ('0' !== $shortcode_max_file_size) ? Helpers::return_bytes($shortcode_max_file_size) : $post_max_size_bytes;
        $min_file_size = (!empty($shortcode_min_file_size)) ? Helpers::return_bytes($shortcode_min_file_size) : -1;

        $options = [
            'access_control_allow_methods' => ['POST', 'PUT'],
            'accept_file_types' => $accept_file_types,
            'inline_file_types' => '/\.____$/i',
            'orient_image' => false,
            'image_versions' => [],
            'max_file_size' => $max_file_size,
            'min_file_size' => $min_file_size,
            'print_response' => false,
        ];

        $error_messages = [
            1 => esc_html__('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'wpcloudplugins'),
            2 => esc_html__('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'wpcloudplugins'),
            3 => esc_html__('The uploaded file was only partially uploaded', 'wpcloudplugins'),
            4 => esc_html__('No file was uploaded', 'wpcloudplugins'),
            6 => esc_html__('Missing a temporary folder', 'wpcloudplugins'),
            7 => esc_html__('Failed to write file to disk', 'wpcloudplugins'),
            8 => esc_html__('A PHP extension stopped the file upload', 'wpcloudplugins'),
            'post_max_size' => esc_html__('The uploaded file exceeds the post_max_size directive in php.ini', 'wpcloudplugins'),
            'max_file_size' => esc_html__('File is too big', 'wpcloudplugins'),
            'min_file_size' => esc_html__('File is too small', 'wpcloudplugins'),
            'accept_file_types' => esc_html__('Filetype not allowed', 'wpcloudplugins'),
            'max_number_of_files' => esc_html__('Maximum number of files exceeded', 'wpcloudplugins'),
            'max_width' => esc_html__('Image exceeds maximum width', 'wpcloudplugins'),
            'min_width' => esc_html__('Image requires a minimum width', 'wpcloudplugins'),
            'max_height' => esc_html__('Image exceeds maximum height', 'wpcloudplugins'),
            'min_height' => esc_html__('Image requires a minimum height', 'wpcloudplugins'),
        ];

        $this->upload_handler = new \WPCP_UploadHandler($options, false, $error_messages);
        $response = @$this->upload_handler->post(false);

        // Upload files to OneDrive
        foreach ($response['files'] as &$file) {
            $name = Helpers::filter_filename(stripslashes(rawurldecode($file->name)), false);
            $path = $_REQUEST['file_path'];

            // Rename, Prefix and Suffix file
            $file_extension = pathinfo($name, PATHINFO_EXTENSION);
            $file_name = pathinfo($name, PATHINFO_FILENAME);

            $name = Helpers::apply_placeholders(
                Processor::instance()->get_shortcode_option('upload_filename'),
                Processor::instance(),
                [
                    'file_name' => $file_name,
                    'file_extension' => empty($file_extension) ? '' : ".{$file_extension}",
                    'queue_index' => filter_var($_REQUEST['queue_index'] ?? 1, FILTER_SANITIZE_NUMBER_INT),
                ]
            );

            $name_parts = pathinfo($name);

            if (false !== strpos($name, '/') && !empty($name_parts['dirname'])) {
                $path = Helpers::clean_folder_path($path.$name_parts['dirname']);
            }

            $name = basename($name);

            // Set return Object
            $file->listtoken = Processor::instance()->get_listtoken();
            $file->name = $name;
            $file->hash = $_REQUEST['hash'];
            $file->path = $path;
            $file->description = sanitize_textarea_field(wp_unslash($_REQUEST['file_description']));
            $file->convert = false;

            // Set Progress
            $return = ['file' => $file, 'status' => ['bytes_up_so_far' => 0, 'total_bytes_up_expected' => $file->size, 'percentage' => 0, 'progress' => 'starting']];
            self::set_upload_progress($file->hash, $return);

            if (isset($file->error)) {
                $file->error = esc_html__('Uploading failed', 'wpcloudplugins').': '.$file->error;
                $return['file'] = $file;
                $return['status']['progress'] = 'upload-failed';
                self::set_upload_progress($file->hash, $return);
                echo json_encode($return);

                error_log('[WP Cloud Plugin message]: '.sprintf('Uploading failed: %s', $file->error));

                exit;
            }

            /** Check if the user hasn't reached its usage limit */
            $max_user_folder_size = Processor::instance()->get_shortcode_option('max_user_folder_size');
            if ('0' !== Processor::instance()->get_shortcode_option('user_upload_folders') && '-1' !== $max_user_folder_size) {
                $disk_usage_after_upload = Client::instance()->get_entry()->get_entry()->get_size() + $file->size;
                $max_allowed_bytes = Helpers::return_bytes($max_user_folder_size);
                if ($disk_usage_after_upload > $max_allowed_bytes) {
                    $return['status']['progress'] = 'upload-failed';
                    $file->error = esc_html__('You have reached your usage limit of', 'wpcloudplugins').' '.Helpers::bytes_to_size_1024($max_allowed_bytes);
                    $return['file'] = $file;

                    self::set_upload_progress($file->hash, $return);
                    echo json_encode($return);

                    exit;
                }
            }

            // Write file
            $chunkSizeBytes = 200 * 320 * 1000; // Multiple of 320kb, the recommended fragment size is between 5-10 MB.

            // Update Mime-type if needed (for IE8 and lower?)
            $file->type = Helpers::get_mimetype($file_extension);

            // Check if file already exists
            if (!empty($file->path)) {
                $file->name = $file->path.$file->name;
            }

            $filename = apply_filters('shareonedrive_upload_file_name', $file->name, Processor::instance());

            // Create new OneDrive File
            $body = [
                'item' => [
                    '@microsoft.graph.conflictBehavior' => ('1' === Processor::instance()->get_shortcode_option('overwrite')) ? 'replace' : 'rename',
                ],
            ];

            if (!empty($file->description) && User::can_edit_description()) {
                $body['item']['description'] = $file->description;
            }

            // Call the API with the media upload, defer so it doesn't immediately return.
            App::instance()->get_sdk_client()->setDefer(true);

            try {
                $request = App::instance()->get_drive()->items->upload($filename, Processor::instance()->get_last_folder(), $body, ['driveId' => App::get_current_drive_id()]);
            } catch (\Exception $ex) {
                $file->error = esc_html__('Not uploaded to the cloud', 'wpcloudplugins').': '.$ex->getMessage();
                $return['status']['progress'] = 'upload-failed';
                self::set_upload_progress($file->hash, $return);
                echo json_encode($return);

                error_log('[WP Cloud Plugin message]: '.sprintf('Not uploaded to the cloud on line %s: %s', __LINE__, $ex->getMessage()));

                exit;
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

            $filesize = filesize($file->tmp_path);
            $media->setFileSize($filesize);

            /* Start partialy upload
              Upload the various chunks. $status will be false until the process is
              complete. */
            try {
                $upload_status = false;
                $bytesup = 0;
                $handle = fopen($file->tmp_path, 'rb');
                while (!$upload_status && !feof($handle)) {
                    @set_time_limit(60);
                    $chunk = fread($handle, $chunkSizeBytes);
                    $upload_status = $media->nextChunk($chunk);
                    $bytesup += $chunkSizeBytes;

                    // Update progress
                    // Update the progress
                    $status = [
                        'bytes_up_so_far' => $bytesup,
                        'total_bytes_up_expected' => $file->size,
                        'percentage' => round(($bytesup / $file->size) * 100),
                        'progress' => 'uploading-to-cloud',
                    ];

                    $current = self::get_upload_progress($file->hash);
                    $current['status'] = $status;
                    self::set_upload_progress($file->hash, $current);
                }

                fclose($handle);
            } catch (\Exception $ex) {
                $file->error = esc_html__('Not uploaded to the cloud', 'wpcloudplugins').': '.$ex->getMessage();
                $return['file'] = $file;
                $return['status']['progress'] = 'upload-failed';
                self::set_upload_progress($file->hash, $return);

                echo json_encode($return);

                error_log('[WP Cloud Plugin message]: '.sprintf('Not uploaded to the cloud on line %s: %s', __LINE__, $ex->getMessage()));

                exit;
            }

            App::instance()->get_sdk_client()->setDefer(false);

            if (empty($upload_status)) {
                $file->error = esc_html__('Not uploaded to the cloud', 'wpcloudplugins');
                $return['file'] = $file;
                $return['status']['progress'] = 'upload-failed';
                self::set_upload_progress($file->hash, $return);

                echo json_encode($return);

                error_log('[WP Cloud Plugin message]: '.sprintf('Not uploaded to the cloud'));

                exit;
            }

            // check if uploaded file has size
            usleep(500000); // wait a 0.5 sec so OneDrive can create a thumbnail.
            $cached_entry = Client::instance()->get_entry($upload_status->getId());

            if (0 === $cached_entry->get_entry()->get_size()) {
                $file->error = esc_html__('Not succesfully uploaded to the cloud', 'wpcloudplugins');
                $return['status']['progress'] = 'upload-failed';

                return;
            }

            // Add new file to our Cache
            $file->completepath = $cached_entry->get_path(Processor::instance()->get_root_folder());
            $file->account_id = App::get_current_account()->get_id();
            $file->drive_id = $cached_entry->get_drive_id();
            $file->fileid = $cached_entry->get_id();
            $file->filesize = Helpers::bytes_to_size_1024($file->size);
            $file->link = urlencode($cached_entry->get_entry()->get_preview_link());
            $file->folderurl = false;
        }

        $return['file'] = $file;
        $return['status']['progress'] = 'upload-finished';
        $return['status']['percentage'] = '100';
        self::set_upload_progress($file->hash, $return);

        // Create response
        echo json_encode($return);

        exit;
    }

    public function do_upload_direct()
    {
        if ((!isset($_REQUEST['filename'])) || (!isset($_REQUEST['file_size'])) || (!isset($_REQUEST['mimetype']))) {
            exit;
        }

        if ('1' === Processor::instance()->get_shortcode_option('demo')) {
            echo json_encode(['result' => 0]);

            exit;
        }

        $size = $_REQUEST['file_size'];
        $path = Helpers::clean_folder_path($_REQUEST['file_path']);

        // Rename, Prefix and Suffix file
        $file_extension = pathinfo(stripslashes($_REQUEST['filename']), PATHINFO_EXTENSION);
        $file_name = pathinfo(stripslashes($_REQUEST['filename']), PATHINFO_FILENAME);

        $name = Helpers::apply_placeholders(
            Processor::instance()->get_shortcode_option('upload_filename'),
            Processor::instance(),
            [
                'file_name' => $file_name,
                'file_extension' => empty($file_extension) ? '' : ".{$file_extension}",
                'queue_index' => filter_var($_REQUEST['queue_index'] ?? 1, FILTER_SANITIZE_NUMBER_INT),
            ]
        );

        $name_parts = pathinfo($name);

        if (false !== strpos($name, '/') && !empty($name_parts['dirname'])) {
            $path = Helpers::clean_folder_path($path.$name_parts['dirname']);
        }

        $name = basename($name);

        $description = sanitize_textarea_field(wp_unslash($_REQUEST['file_description']));

        if (!empty($path)) {
            $name = $path.'/'.$name;
        }

        $name = apply_filters('shareonedrive_upload_file_name', $name, Processor::instance());

        /** Check if the user hasn't reached its usage limit */
        $max_user_folder_size = Processor::instance()->get_shortcode_option('max_user_folder_size');
        if ('0' !== Processor::instance()->get_shortcode_option('user_upload_folders') && '-1' !== $max_user_folder_size) {
            $disk_usage_after_upload = Client::instance()->get_entry()->get_entry()->get_size() + $size;
            $max_allowed_bytes = Helpers::return_bytes($max_user_folder_size);
            if ($disk_usage_after_upload > $max_allowed_bytes) {
                error_log('[WP Cloud Plugin message]: '.esc_html__('You have reached your usage limit of', 'wpcloudplugins').' '.Helpers::bytes_to_size_1024($max_allowed_bytes));
                echo json_encode(['result' => 0]);

                exit;
            }
        }

        // Call the API with the media upload, defer so it doesn't immediately return.
        App::instance()->get_sdk_client()->setDefer(true);

        // Create new OneDrive File
        $body = [
            'item' => [
                '@microsoft.graph.conflictBehavior' => ('1' === Processor::instance()->get_shortcode_option('overwrite')) ? 'replace' : 'rename',
            ],
        ];

        if (!empty($description) && User::can_edit_description()) {
            $body['item']['description'] = $description;
        }

        try {
            $request = App::instance()->get_drive()->items->upload($name, Processor::instance()->get_last_folder(), $body, ['driveId' => App::get_current_drive_id()]);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Not uploaded to the cloud on line %s: %s', __LINE__, $ex->getMessage()));
        }

        // Create a media file upload to represent our upload process.
        $origin = $_REQUEST['orgin'];
        $request_headers = $request->getRequestHeaders();
        $request_headers['Origin'] = $origin;
        $request->setRequestHeaders($request_headers);

        $chunkSizeBytes = 200 * 320 * 1000; // Multiple of 320kb, the recommended fragment size is between 5-10 MB.
        $media = new \SODOneDrive_Http_MediaFileUpload(
            App::instance()->get_sdk_client(),
            $request,
            null,
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize($size);

        try {
            $url = $media->getResumeUri();
            echo json_encode(['result' => 1, 'url' => $url, 'convert' => false]);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Not uploaded to the cloud on line %s: %s', __LINE__, $ex->getMessage()));
            echo json_encode(['result' => 0]);
        }

        exit;
    }

    public static function get_upload_progress($file_hash)
    {
        wp_using_ext_object_cache(false);

        return get_transient('shareonedrive_upload_'.substr($file_hash, 0, 40));
    }

    public static function set_upload_progress($file_hash, $status)
    {
        wp_using_ext_object_cache(false);

        // Update progress
        return set_transient('shareonedrive_upload_'.substr($file_hash, 0, 40), $status, HOUR_IN_SECONDS);
    }

    public function get_upload_status()
    {
        $hash = $_REQUEST['hash'];

        // Try to get the upload status of the file
        for ($_try = 1; $_try < 6; ++$_try) {
            $result = self::get_upload_progress($hash);

            if (false !== $result) {
                if ('upload-failed' === $result['status']['progress'] || 'upload-finished' === $result['status']['progress']) {
                    delete_transient('shareonedrive_upload_'.substr($hash, 0, 40));
                }

                break;
            }

            // Wait a moment, perhaps the upload still needs to start
            usleep(500000 * $_try);
        }

        if (false === $result) {
            $result = ['file' => false, 'status' => ['bytes_up_so_far' => 0, 'total_bytes_up_expected' => 0, 'percentage' => 0, 'progress' => 'upload-failed']];
        }

        echo json_encode($result);

        exit;
    }

    public function upload_convert()
    {
        // NOT IMPLEMENTED
    }

    public function upload_post_process()
    {
        if ((!isset($_REQUEST['files'])) || 0 === count($_REQUEST['files'])) {
            echo json_encode(['result' => 0]);

            exit;
        }

        // Update the cache to process all changes
        Cache::instance()->pull_for_changes(true);

        $uploaded_files = $_REQUEST['files'];
        $_uploaded_entries = [];
        $_email_entries = [];

        foreach ($uploaded_files as $file_id) {
            $cachedentry = Client::instance()->get_entry($file_id, false);

            if (false === $cachedentry) {
                continue;
            }

            // Load all meta data which wasn't received by the sync request
            $cachedentry = Client::instance()->update_expired_entry($cachedentry);

            // Upload Hook
            if (false === get_transient('shareonedrive_upload_'.$file_id)) {
                $cachedentry = apply_filters('shareonedrive_upload', $cachedentry, Processor::instance());
                do_action('shareonedrive_log_event', 'shareonedrive_uploaded_entry', $cachedentry);

                $_email_entries[] = $cachedentry;
            }

            $_uploaded_entries[] = $cachedentry;
        }

        // Send email if needed
        if (count($_email_entries) > 0) {
            if ('1' === Processor::instance()->get_shortcode_option('notificationupload')) {
                Processor::instance()->send_notification_email('upload', $_email_entries);
            }
        }

        // Return information of the files
        $files = [];
        foreach ($_uploaded_entries as $cachedentry) {
            $file = [];
            $file['name'] = $cachedentry->get_entry()->get_name();
            $file['type'] = $cachedentry->get_entry()->get_mimetype();
            $file['description'] = $cachedentry->get_entry()->get_description();
            $file['completepath'] = $cachedentry->get_path(Processor::instance()->get_root_folder());
            $file['account_id'] = App::get_current_account()->get_id();
            $file['drive_id'] = $cachedentry->get_drive_id();
            $file['fileid'] = $cachedentry->get_id();
            $file['filesize'] = Helpers::bytes_to_size_1024($cachedentry->get_entry()->get_size());
            $file['folderurl'] = false;
            $file['temp_thumburl'] = ($cachedentry->get_entry()->has_own_thumbnail()) ? $cachedentry->get_entry()->get_thumbnail_with_size(128, 128, false) : null;

            $file['link'] = urlencode($cachedentry->get_entry()->get_preview_link());
            if (apply_filters('shareonedrive_upload_post_process_createlink', '1' === Processor::instance()->get_shortcode_option('upload_create_shared_link'), $cachedentry, Processor::instance())) {
                $file['link'] = urlencode(Client::instance()->get_shared_link($cachedentry, []));
            }

            foreach ($cachedentry->get_parents() as $parent) {
                $folderurl = $parent->get_entry()->get_preview_link();
                $file['folderurl'] = urlencode($folderurl);
            }

            $files[$file['fileid']] = apply_filters('shareonedrive_upload_entry_information', $file, $cachedentry, Processor::instance());

            set_transient('shareonedrive_upload_'.$cachedentry->get_id(), true, HOUR_IN_SECONDS);
        }

        do_action('shareonedrive_upload_post_process', $_uploaded_entries, Processor::instance());

        // Clear Cached Requests
        CacheRequest::clear_request_cache();

        echo json_encode(['result' => 1, 'files' => $files]);
    }
}
