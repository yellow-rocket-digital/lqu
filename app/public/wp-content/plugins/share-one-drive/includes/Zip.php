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

class Zip
{
    /**
     * Unique ID.
     *
     * @var string
     */
    public $request_id;

    /**
     * Name of the zip file.
     *
     * @var string
     */
    public $zip_name;

    /**
     * Files that need to be added to ZIP.
     *
     * @var \TheLion\ShareoneDrive\CacheNode[]
     */
    public $entries = [];

    /**
     * Number of bytes that are downloaded so far.
     *
     * @var int
     */
    public $bytes_so_far = 0;

    /**
     * Bytes that need to be download in total.
     *
     * @var int
     */
    public $bytes_total = 0;

    /**
     * Current status.
     *
     * @var string
     */
    public $current_action = 'starting';

    /**
     * Message describing the current status.
     *
     * @var string
     */
    public $current_action_str = '';

    /**
     * @var \TheLion\ShareoneDrive\CacheNode[]
     */
    public $entries_downloaded = [];

    /**
     * @var \ZipStream\ZipStream
     */
    private $_zip_handler;

    public function __construct($request_id)
    {
        $this->request_id = $request_id;
    }

    public function do_zip()
    {
        $this->initialize();
        $this->current_action = 'indexing';
        $this->current_action_str = esc_html__('Selecting files...', 'wpcloudplugins');

        $this->index();
        $this->create();

        $this->current_action = 'downloading';
        $this->add_entries();

        $this->current_action = 'finalizing';
        $this->current_action_str = esc_html__('Almost ready', 'wpcloudplugins');
        $this->set_progress();
        $this->finalize();

        $this->current_action = 'finished';
        $this->current_action_str = esc_html__('Finished', 'wpcloudplugins');
        $this->set_progress();

        exit;
    }

    /**
     * Load the ZIP library and make sure that the root folder is loaded.
     */
    public function initialize()
    {
        ignore_user_abort(false);

        require_once SHAREONEDRIVE_ROOTDIR.'/vendors/ZipStream/vendor/autoload.php';

        // Check if file/folder is cached and still valid
        $cachedfolder = Client::instance()->get_folder();

        if (false === $cachedfolder || false === $cachedfolder['folder']) {
            return new \WP_Error('broke', esc_html__("Requested directory isn't allowed", 'wpcloudplugins'));
        }

        $folder = $cachedfolder['folder']->get_entry();

        // Check if entry is allowed
        if (!Processor::instance()->_is_entry_authorized($cachedfolder['folder'])) {
            return new \WP_Error('broke', esc_html__("Requested directory isn't allowed", 'wpcloudplugins'));
        }

        $this->zip_name = basename($folder->get_name()).'_'.time().'.zip';

        if (isset($_REQUEST['files']) && 1 === count($_REQUEST['files'])) {
            $single_entry = Client::instance()->get_entry($_REQUEST['files'][0]);
            $this->zip_name = basename($single_entry->get_name()).'_'.time().'.zip';
        }

        $this->set_progress();

        // Stop WP from buffering
        wp_ob_end_flush_all();
    }

    /**
     * Create the ZIP File.
     */
    public function create()
    {
        $options = new \ZipStream\Option\Archive();
        $options->setSendHttpHeaders(true);
        $options->setFlushOutput(true);
        $options->setContentType('application/octet-stream');
        header('X-Accel-Buffering: no');

        // create a new zipstream object
        $this->_zip_handler = new \ZipStream\ZipStream(\TheLion\ShareoneDrive\Helpers::filter_filename($this->zip_name), $options);
    }

    /**
     * Create a list of files and folders that need to be zipped.
     */
    public function index()
    {
        $requested_ids = [Processor::instance()->get_requested_entry()];

        if (isset($_REQUEST['files'])) {
            $requested_ids = $_REQUEST['files'];
        }

        foreach ($requested_ids as $fileid) {
            $cached_entry = Client::instance()->get_entry($fileid);

            if (false === $cached_entry) {
                continue;
            }

            $entry = $cached_entry->get_entry();

            if ($entry->is_dir()) {
                $entries_in_dir = Client::instance()->get_folder_recursive($cached_entry);
                $this->entries = array_merge($this->entries, $entries_in_dir);

                foreach ($entries_in_dir as $cached_entry) {
                    $this->bytes_total += $cached_entry->get_entry()->get_size();
                }
            } else {
                $this->entries[] = $cached_entry;
                $this->bytes_total += $entry->get_size();
            }

            $this->current_action_str = esc_html__('Selecting files...', 'wpcloudplugins').' ('.count($this->entries).')';
            $this->set_progress();
        }
    }

    /**
     * Add all requests files to Zip file.
     */
    public function add_entries()
    {
        if (count($this->entries) > 0) {
            foreach ($this->entries as $key => $cached_entry) {
                $this->add_entry_to_zip($cached_entry);

                unset($this->entries[$key]);
                $this->entries_downloaded[] = $cached_entry;

                do_action('shareonedrive_log_event', 'shareonedrive_downloaded_entry', $cached_entry, ['as_zip' => true]);

                $this->bytes_so_far += $cached_entry->get_entry()->get_size();
                $this->current_action_str = esc_html__('Downloading...', 'wpcloudplugins').'<br/>('.Helpers::bytes_to_size_1024($this->bytes_so_far).' / '.Helpers::bytes_to_size_1024($this->bytes_total).')';
                $this->set_progress();
            }
        }
    }

    /**
     * Download the request file and add it to the ZIP.
     *
     * @param CacheNode $file
     */
    public function add_entry_to_zip(CacheNode $cached_entry)
    {
        $relative_path = $cached_entry->get_path(Processor::instance()->get_last_folder());

        $fileOptions = new \ZipStream\Option\File();

        if (!empty($cached_entry->get_entry()->get_last_edited())) {
            $date = new \DateTime();
            $date->setTimestamp(strtotime($cached_entry->get_entry()->get_last_edited()));
            $fileOptions->setTime($date);
        }

        $fileOptions->setComment((string) $cached_entry->get_entry()->get_description());

        if ($cached_entry->get_entry()->is_dir()) {
            $this->_zip_handler->addFile(ltrim($relative_path, '/'), '');

            return;
        }

        // Download the File
        // Update the time_limit as this can take a while
        @set_time_limit(0);

        // Get Download Url
        $download_url = API::create_temporarily_download_url($cached_entry->get_id());

        if (false === $download_url) {
            return;
        }

        // Get file
        $request = new \SODOneDrive_Http_Request($download_url, 'GET');

        $download_stream = fopen('php://temp/maxmemory:'.(5 * 1024 * 1024), 'r+');

        App::instance()->get_sdk_client()->getIo()->setOptions(
            [
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FILE => $download_stream,
                CURLOPT_HEADER => false,
            ]
        );

        try {
            App::instance()->get_sdk_client()->getAuth()->authenticatedRequest($request);

            curl_close(App::instance()->get_sdk_client()->getIo()->getHandler());
        } catch (\Exception $ex) {
            fclose($download_stream);
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            exit;
        }

        App::instance()->get_sdk_client()->getIo()->clearOptions();

        rewind($download_stream);

        // Add file contents to zip
        try {
            $this->_zip_handler->addFileFromStream(trim($relative_path, '/'), $download_stream, $fileOptions);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('ZIP Error on line %s: %s', __LINE__, $ex->getMessage()));

            $this->current_action = 'failed';
            $this->set_progress();

            exit;
        }

        fclose($download_stream);
    }

    /**
     * Finalize the zip file.
     */
    public function finalize()
    {
        $this->set_progress();

        // Close zip
        $result = $this->_zip_handler->finish();

        // Send email if needed
        if ('1' === Processor::instance()->get_shortcode_option('notificationdownload')) {
            Processor::instance()->send_notification_email('download', $this->entries_downloaded);
        }

        // Download Zip Hook
        do_action('shareonedrive_download_zip', $this->entries_downloaded);
    }

    /**
     * Received progress information for the ZIP process from database.
     *
     * @param string $request_id
     */
    public static function get_progress($request_id)
    {
        return get_transient('shareonedrive_zip_'.substr($request_id, 0, 40));
    }

    /**
     * Set current progress information for ZIP process in database.
     */
    public function set_progress()
    {
        $status = [
            'id' => $this->request_id,
            'status' => [
                'bytes_so_far' => $this->bytes_so_far,
                'bytes_total' => $this->bytes_total,
                'percentage' => ($this->bytes_total > 0) ? (round(($this->bytes_so_far / $this->bytes_total) * 100)) : 0,
                'progress' => $this->current_action,
                'progress_str' => $this->current_action_str,
            ],
        ];

        // Update progress
        return set_transient('shareonedrive_zip_'.substr($this->request_id, 0, 40), $status, HOUR_IN_SECONDS);
    }

    /**
     * Get progress information for the ZIP process
     * Used to display a progress percentage on Front-End.
     *
     * @param string $request_id
     */
    public static function get_status($request_id)
    {
        // Try to get the upload status of the file
        for ($_try = 1; $_try < 6; ++$_try) {
            $result = self::get_progress($request_id);

            if (false !== $result) {
                if ('failed' === $result['status']['progress'] || 'finished' === $result['status']['progress']) {
                    delete_transient('shareonedrive_zip_'.substr($request_id, 0, 40));
                }

                break;
            }

            // Wait a moment, perhaps the upload still needs to start
            usleep(500000 * $_try);
        }

        if (false === $result) {
            $result = ['file' => false, 'status' => ['bytes_down_so_far' => 0, 'total_bytes_down_expected' => 0, 'percentage' => 0, 'progress' => 'failed']];
        }

        echo json_encode($result);

        exit;
    }
}