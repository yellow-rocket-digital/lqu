<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\ShareoneDrive;

class Events
{
    public function __construct()
    {
        if ('Yes' === Core::get_setting('log_events')) {
            $this->_load_hooks();
            $this->_install_cron_job();
        }
    }

    public function _load_hooks()
    {
        add_action('wp_ajax_nopriv_shareonedrive-event-stats', [$this, 'get_stats']);
        add_action('wp_ajax_shareonedrive-event-stats', [$this, 'get_stats']);

        add_action('wp_ajax_nopriv_shareonedrive-event-log', [$this, 'log_ajax_event']);
        add_action('wp_ajax_shareonedrive-event-log', [$this, 'log_ajax_event']);

        add_action('shareonedrive_log_event', [$this, 'log_event'], 10, 3);

        add_action('shareonedrive_send_event_summary', [$this, 'send_event_summary']);
    }

    public function _install_cron_job()
    {
        $summary_cron = wp_next_scheduled('shareonedrive_send_event_summary');
        if (false === $summary_cron && 'Yes' === Core::get_setting('event_summary')) {
            wp_schedule_event(time(), Core::get_setting('event_summary_period'), 'shareonedrive_send_event_summary');
        }
    }

    /**
     * Log new events
     * Hook into this function with something like: do_action('shareonedrive_log_event', 'shareonedrive_event_type', TheLion\ShareoneDrive\CacheNode $cached_entry, array('extra_data' => $value));.
     *
     * @param string                           $event
     * @param \TheLion\ShareoneDrive\CacheNode $cached_entry
     * @param array                            $extra_data
     */
    public function log_event($event, $cached_entry = null, $extra_data = [])
    {
        $new_event = [
            'plugin' => 'share-one-drive',
            'type' => $event,
            'user_id' => get_current_user_id(),
        ];

        if (!($cached_entry instanceof CacheNode) && !empty($cached_entry)) {
            $cached_entry = Client::instance()->get_entry($cached_entry);
        }

        // @var $cached_entry CacheNode
        if (!empty($cached_entry)) {
            $new_event['entry_id'] = $cached_entry->get_id();
            $new_event['account_id'] = App::get_current_account()->get_id();
            $new_event['drive_id'] = $cached_entry->get_drive_id();
            $new_event['entry_mimetype'] = $cached_entry->get_entry()->get_mimetype();
            $new_event['entry_is_dir'] = $cached_entry->get_entry()->is_dir();
            $new_event['entry_name'] = $cached_entry->get_name();

            if ($cached_entry->has_parents()) {
                $parents = $cached_entry->get_parents();
                $first_parent = reset($parents);
                $new_event['parent_id'] = $first_parent->get_id();
                $root_id = API::get_root_folder()->get_id();
                $new_event['parent_path'] = $first_parent->get_path($root_id);
            } else {
                $new_event['parent_id'] = '';
                $new_event['parent_path'] = '';
            }
        }

        if (empty($new_event['entry_mimetype'])) {
            $new_event['entry_mimetype'] = ($cached_entry->get_entry()->is_dir()) ? 'folder' : 'application/octet-stream';
        }

        $new_event['extra'] = json_encode($extra_data);
        $new_event['location'] = Helpers::get_page_url();

        $new_event = apply_filters('shareonedrive_new_event', $new_event, $event, $cached_entry, $extra_data, Processor::instance());

        try {
            Event_DB_Model::insert($new_event);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Cannot log Event on line %s: %s', __LINE__, $ex->getMessage()));
        }

        $new_event['text'] = $this->get_event_type($event)['text'];
        $new_event['description'] = strip_tags($this->get_event_description($new_event));

        do_action('shareonedrive_event_added', $event, $new_event, $cached_entry, Processor::instance());

        // Trigger webhook
        $webhook_data = [
            'entry' => null,
            'account' => [
                'id' => App::get_current_account()->get_id(),
                'name' => App::get_current_account()->get_name(),
                'email' => App::get_current_account()->get_email(),
                'image' => App::get_current_account()->get_image(),
            ],
        ];

        if (!empty($cached_entry)) {
            $webhook_data['entry'] = [
                'id' => $new_event['entry_id'],
                'name' => $new_event['entry_name'],
                'mimetype' => $new_event['entry_mimetype'],
                'size' => Helpers::bytes_to_size_1024($cached_entry->get_entry()->get_size()),
                'icon' => $cached_entry->get_entry()->get_icon(),
                'description' => $cached_entry->get_entry()->get_description(),
                'thumbnail' => $cached_entry->get_entry()->get_thumbnail_large(),
                'preview_url' => $cached_entry->get_entry()->get_preview_link(),
                'download_url' => $cached_entry->get_entry()->get_download_link(),
                'is_dir' => $new_event['entry_is_dir'],
                'parent_id' => $new_event['parent_id'],
                'parent_path' => $new_event['parent_path'],
            ];
        }

        WebHook::instance()->add($event, $new_event['description'], $webhook_data, get_current_user_id());
    }

    /**
     * Log JS events via AJAX call.
     */
    public function log_ajax_event()
    {
        if (!isset($_REQUEST['type'])) {
            exit;
        }

        Processor::instance()->start_process();

        if ('log_preview_event' === $_REQUEST['type'] && isset($_REQUEST['id'])) {
            // JS Preview Log Event
            $cached_entry = Client::instance()->get_entry($_REQUEST['id'], false);
            $this->log_event('shareonedrive_previewed_entry', $cached_entry);

            exit;
        }

        if ('log_failed_upload_event' === $_REQUEST['type']) {
            // JS Failed Upload Event

            $id = (empty($_REQUEST['id']) ? false : $_REQUEST['id']);
            $cached_entry = Client::instance()->get_entry($id, false);

            $this->log_event('shareonedrive_uploaded_failed', $cached_entry, $_REQUEST['data']);

            exit;
        }
    }

    /**
     * Event processor for Dashboard.
     */
    public function get_stats()
    {
        if (!Helpers::check_user_role(Core::get_setting('permissions_see_dashboard'))) {
            exit;
        }

        if (!isset($_REQUEST['type'])) {
            exit;
        }

        $nonce_verification = ('Yes' === Processor::instance()->get_setting('nonce_validation'));
        $allow_nonce_verification = apply_filters('share_one_drive_allow_nonce_verification', $nonce_verification);

        if ($allow_nonce_verification && is_user_logged_in()) {
            if (false === check_ajax_referer('shareonedrive-admin-action', false, false)) {
                error_log('[WP Cloud Plugin message]: '." Function get_stats() didn't receive a valid nonce");

                exit;
            }
        }

        $period = null;
        if (isset($_REQUEST['periodstart'], $_REQUEST['periodend'])) {
            $period = [
                'start' => $_REQUEST['periodstart'].' 00:00:00',
                'end' => $_REQUEST['periodend'].' 23:59:00',
            ];
        }

        $data = [];

        Processor::instance()->_set_gzip_compression();

        switch ($_REQUEST['type']) {
            case 'totals':
                $data = $this->get_totals($period);

                break;

            case 'activities':
                $data = $this->get_activities($period);

                break;

            case 'topdownloads':
                $data = $this->get_top_downloads($period);

                break;

            case 'topusers':
                $data = $this->get_top_users($period);

                break;

            case 'latestuploads':
                $data = $this->get_latest_uploads($period);

                break;

            case 'topuploads':
                $data = $this->get_top_uploads($period);

                break;

            case 'full-log':
                $data = $this->get_full_log([], $period);

                break;

            case 'get-detail':
                if (!isset($_REQUEST['detail']) || !isset($_REQUEST['id'])) {
                    exit;
                }

                switch ($_REQUEST['detail']) {
                    case 'user':
                        $this->render_user_detail($_REQUEST['id']);

                        break;

                    case 'entry':
                        $this->render_entry_detail($_REQUEST['id'], $_REQUEST['account_id'], $_REQUEST['drive_id']);

                        break;
                }

                break;

            case 'get-download':
                if (isset($_REQUEST['id'])) {
                    $this->start_download_by_id($_REQUEST['id'], $_REQUEST['account_id'], $_REQUEST['drive_id']);
                }

                break;
        }

        if (isset($_REQUEST['export'])) {
            echo $this->export_to_csv($data['data'], $_REQUEST['type']);
        } else {
            echo json_encode($data);
        }

        exit;
    }

    public function export_to_csv($dataset, $type)
    {
        $delimiter = ',';

        // create a file pointer
        $handle = fopen('php://output', 'w');

        // set UTF-8 headers
        fputs($handle, $bom = chr(0xEF).chr(0xBB).chr(0xBF));

        // set column headers
        fputcsv($handle, array_keys(reset($dataset)), $delimiter);

        // output each row of the data, format line as csv and write to file pointer
        foreach ($dataset as $raw_row) {
            $row = array_map(function ($key, $value) {
                if ('entry_name' === $key || 'parent_path' === $key) {
                    // return utf8_decode($value);
                }

                return trim(strip_tags($value));
            }, array_keys($raw_row), $raw_row);

            fputcsv($handle, $row, $delimiter);
        }

        fclose($handle);
    }

    /**
     * Start the download for entry with $id.
     *
     * @param string $id
     * @param string $entry_id
     * @param string $account_id
     * @param string $drive_id
     */
    public function start_download_by_id($entry_id, $account_id, $drive_id)
    {
        App::set_current_account_by_id($account_id);
        App::set_current_drive_id($drive_id);
        $cached_entry = Client::instance()->get_entry($entry_id, false);

        if (false === $cached_entry) {
            exit;
        }

        Client::instance()->download_content($cached_entry);
    }

    /**
     * Get totals per event_type.
     *
     * @global $wpdb
     *
     * @param string $where
     * @param mixed  $period
     *
     * @return array
     */
    public function get_totals($period)
    {
        $where = 1;
        // Get Totals for user
        if (isset($_REQUEST['detail'], $_REQUEST['id'])) {
            global $wpdb;

            switch ($_REQUEST['detail']) {
                case 'user':
                    $where = $wpdb->prepare('`user_id` = %s', [$_REQUEST['id']]);

                    break;

                case 'entry':
                    $where = $wpdb->prepare('`entry_id` = %s', [$_REQUEST['id']]);

                    break;
            }
        }

        if (!empty($period)) {
            if (is_int($period) || 1 === count($period)) {
                // If $period is an interval (e.g. when sending summary email
                $sql = ' AND (`datetime` BETWEEN DATE_SUB(NOW(), INTERVAL %d second) AND NOW())';
                $where .= $wpdb->prepare($sql, [$period]);
            } else {
                $sql = ' AND (`datetime` BETWEEN %s AND %s )';
                $where .= $wpdb->prepare($sql, [$period['start'], $period['end']]);
            }
        }

        $sql = 'SELECT `type`, COUNT(`id`) as total FROM `'.Event_DB_Model::table().'` WHERE '.$where.' GROUP BY `type`';

        $data = Event_DB_Model::get_custom_sql($sql, [], 'ARRAY_A');

        $totals = [];
        foreach ($data as $row) {
            $event = $this->get_event_type($row['type']);
            $row = array_merge($row, $event);

            $totals[$row['type']] = $row;
        }

        return $totals;
    }

    /**
     * Return a dataset for ChartJS.
     *
     * @param null|mixed $period
     *
     * @return array
     */
    public function get_activities($period = null)
    {
        // Get Events from Database
        if (empty($period)) {
            $sql = 'SELECT DATE(`datetime`) as date, `type`, COUNT(`id`) as total FROM `'.Event_DB_Model::table().'`  GROUP BY DATE(`datetime`), `type` ORDER BY  DATE(`datetime`) DESC';
            $data = Event_DB_Model::get_custom_sql($sql, [], 'ARRAY_A');
        } else {
            $sql = 'SELECT DATE(`datetime`) as date, `type`, COUNT(`id`) as total FROM `'.Event_DB_Model::table().'` WHERE (`datetime` BETWEEN %s AND %s) GROUP BY DATE(`datetime`), `type` ORDER BY  DATE(`datetime`) DESC';
            $values = [$period['start'].' 00:00:00', $period['end'].' 23:59:00'];
            $data = Event_DB_Model::get_custom_sql($sql, $values, 'ARRAY_A');
        }

        // Create return array
        $raw_chart_data = ['labels' => [], 'datasets' => []];

        // Create an empty array with all total set to 0 the selected period
        $start = new \DateTime($period['start']);
        $end = new \DateTime($period['end']);
        $end->add(\DateInterval::createFromDateString('1 day'));

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($start, $interval, $end);

        $empty_period = [];
        foreach ($period as $date) {
            $date_str = $date->format('d-m-Y');
            $empty_period[$date_str] = [
                'x' => $date_str,
                'y' => (int) 0,
            ];
        }

        // Create the dataset for each event type
        foreach ($data as $day) {
            if (!isset($raw_chart_data['datasets'][$day['type']])) {
                $event = $this->get_event_type($day['type']);
                $hide_on_init = !in_array($day['type'], ['shareonedrive_previewed_entry', 'shareonedrive_downloaded_entry', 'shareonedrive_streamed_entry']);

                $raw_chart_data['datasets'][$day['type']] = [
                    'label' => $event['text'],
                    'data' => [],
                    // 'fill' => false,
                    'spanGaps' => true,
                    'backgroundColor' => $event['colors']['light'],
                    'borderColor' => $event['colors']['normal'],
                    'pointBackgroundColor' => $event['colors']['dark'],
                    'borderWidth' => 2,
                    'data' => $empty_period,
                    'hidden' => $hide_on_init,
                ];
            }

            $date = new \DateTime($day['date']);

            $raw_chart_data['datasets'][$day['type']]['data'][$date->format('d-m-Y')] = [
                'x' => $date->format('d-m-Y'),
                'y' => (int) $day['total'],
            ];
        }

        // ChartJS doesn't like arrays with key names, so remove them all
        foreach ($raw_chart_data['datasets'] as &$dataset) {
            $dataset['data'] = array_values($dataset['data']);
        }

        $chart_data = ['labels' => $raw_chart_data['labels'], 'datasets' => @array_values($raw_chart_data['datasets'])];

        // BUG FIX for some PHP version do have trouble with the initial dataset
        if (empty($chart_data['datasets'])) {
            $raw_chart_data = json_decode(json_encode($raw_chart_data), true);
            $chart_data = ['labels' => $raw_chart_data['labels'], 'datasets' => array_values($raw_chart_data['datasets'])];
        }

        // Return the data
        return $chart_data;
    }

    /**
     * Get the top 25 to downloads.
     *
     * @param null|mixed $period
     *
     * @return array
     */
    public function get_top_downloads($period = null)
    {
        $columns = [
            [
                'db' => 'MAX(`entry_id`)', 'dt' => 'entry_id', 'field' => 'entry_id', 'as' => 'entry_id',
            ],
            [
                'db' => 'MAX(`account_id`)', 'dt' => 'account_id', 'field' => 'account_id', 'as' => 'account_id',
            ],
            [
                'db' => 'MAX(`drive_id`)', 'dt' => 'drive_id', 'field' => 'drive_id', 'as' => 'drive_id',
            ],
            [
                'db' => 'MAX(`entry_mimetype`)', 'dt' => 'icon', 'field' => 'entry_mimetype', 'as' => 'entry_mimetype', 'formatter' => function ($value, $row) {
                    return Helpers::get_default_icon($value, $row['entry_is_dir']);
                },
            ],
            [
                'db' => 'MAX(`parent_path`)', 'dt' => 'parent_path', 'field' => 'parent_path', 'as' => 'parent_path', 'formatter' => function ($value, $row) {
                    return $value;
                },
            ],
            [
                'db' => 'MAX(`entry_name`)', 'dt' => 'entry_name', 'field' => 'entry_name', 'as' => 'entry_name', 'formatter' => function ($value, $row) {
                    return $value;
                },
            ],
            [
                'db' => 'MAX(`entry_is_dir`)', 'dt' => 'entry_is_dir', 'field' => 'entry_is_dir', 'as' => 'entry_is_dir',
            ],
            [
                'db' => 'COUNT(`id`)', 'dt' => 'total', 'field' => 'total', 'as' => 'total',
            ],
        ];

        $where = "`plugin` = 'share-one-drive' AND `type` IN ('shareonedrive_downloaded_entry')";

        global $wpdb;

        if (!empty($period)) {
            if (is_int($period) || 1 === count($period)) {
                // If $period is an interval (e.g. when sending summary email
                $sql = ' AND (`datetime` BETWEEN DATE_SUB(NOW(), INTERVAL %d second) AND NOW())';
                $where .= $wpdb->prepare($sql, [$period]);
            } else {
                $sql = ' AND (`datetime` BETWEEN %s AND %s )';
                $where .= $wpdb->prepare($sql, [$period['start'], $period['end']]);
            }
        }

        $groupBy = '`entry_id`';

        return $this->get_results($columns, $where, $groupBy, true, '', '');
    }

    /**
     * Get the top 25 of the users with most downloads.
     *
     * @param null|mixed $period
     *
     * @return array
     */
    public function get_top_users($period = null)
    {
        $columns = [
            [
                'db' => 'MAX(`user_id`)', 'dt' => 'icon', 'field' => 'icon', 'as' => 'icon', 'formatter' => function ($value, $row) {
                    if (0 == $value) {
                        return SHAREONEDRIVE_ROOTPATH.'/css/images/usericon.png';
                    }
                    $user = get_userdata($value);

                    if (false === $user) {
                        return SHAREONEDRIVE_ROOTPATH.'/css/images/usericon.png';
                    }

                    $display_gravatar = get_avatar_url($user->user_email, 32);
                    if (false === $display_gravatar) {
                        // Gravatar is disabled, show default image.
                        $display_gravatar = SHAREONEDRIVE_ROOTPATH.'/css/images/usericon.png';
                    }

                    return $display_gravatar;
                },
            ],
            [
                'db' => 'MAX(`user_id`)', 'dt' => 'user_id', 'field' => 'user_id', 'as' => 'user_id',
            ],
            [
                'db' => 'MAX(`user_id`)', 'dt' => 'user_display_name', 'field' => 'user_id', 'as' => 'user_id', 'formatter' => function ($value, $row) {
                    if (0 == $value) {
                        return esc_html__('Visitors', 'wpcloudplugins');
                    }

                    $wp_user = get_userdata($value);

                    if (false === $wp_user) {
                        return esc_html__('Visitor').' #'.$value.' ('.esc_html__('Deleted').')';
                    }

                    return $wp_user->display_name;
                },
            ],
            [
                'db' => 'MAX(`user_id`)', 'dt' => 'user_name', 'field' => 'user_id', 'as' => 'user_id', 'formatter' => function ($value, $row) {
                    if (0 == $value) {
                        return esc_html__('Visitors', 'wpcloudplugins');
                    }

                    $wp_user = get_userdata($value);

                    if (false === $wp_user) {
                        return ' #'.$value.' ('.esc_html__('Deleted').')';
                    }

                    return $wp_user->user_login;
                },
            ],
            [
                'db' => 'COUNT(`entry_id`)', 'dt' => 'total', 'field' => 'total', 'as' => 'total',
            ],
        ];

        $where = "`plugin` = 'share-one-drive' AND `type` IN ('shareonedrive_downloaded_entry')";

        global $wpdb;

        if (!empty($period)) {
            if (is_int($period) || 1 === count($period)) {
                // If $period is an interval (e.g. when sending summary email
                $sql = ' AND (`datetime` BETWEEN DATE_SUB(NOW(), INTERVAL %d second) AND NOW())';
                $where .= $wpdb->prepare($sql, [$period]);
            } else {
                $sql = ' AND (`datetime` BETWEEN %s AND %s )';
                $where .= $wpdb->prepare($sql, [$period['start'], $period['end']]);
            }
        }

        $groupBy = '`user_id`';

        return $this->get_results($columns, $where, $groupBy, true, '', '');
    }

    /**
     * Get the top 25 of latest uploads.
     *
     * @param null|mixed $period
     *
     * @return array
     */
    public function get_latest_uploads($period = null)
    {
        $columns = [
            [
                'db' => 'MAX(`entry_id`)', 'dt' => 'entry_id', 'field' => 'entry_id', 'as' => 'entry_id',
            ],
            [
                'db' => 'MAX(`account_id`)', 'dt' => 'account_id', 'field' => 'account_id', 'as' => 'account_id',
            ],
            [
                'db' => 'MAX(`drive_id`)', 'dt' => 'drive_id', 'field' => 'drive_id', 'as' => 'drive_id',
            ],
            [
                'db' => 'MAX(`entry_mimetype`)', 'dt' => 'icon', 'field' => 'entry_mimetype', 'as' => 'entry_mimetype', 'formatter' => function ($value, $row) {
                    return Helpers::get_default_icon($value, $row['entry_is_dir']);
                },
            ],
            [
                'db' => 'MAX(`parent_path`)', 'dt' => 'parent_path', 'field' => 'parent_path', 'as' => 'parent_path', 'formatter' => function ($value, $row) {
                    return $value;
                },
            ],
            [
                'db' => 'MAX(`entry_name`)', 'dt' => 'entry_name', 'field' => 'entry_name', 'as' => 'entry_name', 'formatter' => function ($value, $row) {
                    return $value;
                },
            ],
            [
                'db' => 'MAX(`entry_is_dir`)', 'dt' => 'entry_is_dir', 'field' => 'entry_is_dir', 'as' => 'entry_is_dir',
            ],
            [
                'db' => 'MAX(`datetime`)', 'dt' => 'datetime', 'field' => 'datetime', 'as' => 'datetime', 'formatter' => function ($value, $row) {
                    return date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($value));
                },
            ],
        ];

        $where = "`plugin` = 'share-one-drive' AND `type` IN ('shareonedrive_uploaded_entry')";

        global $wpdb;

        if (!empty($period)) {
            if (is_int($period) || 1 === count($period)) {
                // If $period is an interval (e.g. when sending summary email
                $sql = ' AND (`datetime` BETWEEN DATE_SUB(NOW(), INTERVAL %d second) AND NOW())';
                $where .= $wpdb->prepare($sql, [$period]);
            } else {
                $sql = ' AND (`datetime` BETWEEN %s AND %s )';
                $where .= $wpdb->prepare($sql, [$period['start'], $period['end']]);
            }
        }

        $groupBy = '`entry_id`';

        return $this->get_results($columns, $where, $groupBy, true, '', '');
    }

    /**
     * Get the top 25 of the users with most uploads.
     *
     * @param null|mixed $period
     *
     * @return array
     */
    public function get_top_uploads($period = null)
    {
        $columns = [
            [
                'db' => 'MAX(`user_id`)', 'dt' => 'icon', 'field' => 'icon', 'as' => 'icon', 'formatter' => function ($value, $row) {
                    if (0 == $value) {
                        return SHAREONEDRIVE_ROOTPATH.'/css/images/usericon.png';
                    }
                    $user = get_userdata($value);

                    if (false === $user) {
                        return SHAREONEDRIVE_ROOTPATH.'/css/images/usericon.png';
                    }

                    $display_gravatar = get_avatar_url($user->user_email, 32);
                    if (false === $display_gravatar) {
                        // Gravatar is disabled, show default image.
                        $display_gravatar = SHAREONEDRIVE_ROOTPATH.'/css/images/usericon.png';
                    }

                    return $display_gravatar;
                },
            ],
            [
                'db' => 'MAX(`user_id`)', 'dt' => 'user_id', 'field' => 'user_id', 'as' => 'user_id',
            ],
            [
                'db' => 'MAX(`user_id`)', 'dt' => 'user_display_name', 'field' => 'user_id', 'as' => 'user_id', 'formatter' => function ($value, $row) {
                    if (0 == $value) {
                        return esc_html__('Visitors', 'wpcloudplugins');
                    }

                    $wp_user = get_userdata($value);

                    if (false === $wp_user) {
                        return esc_html__('Visitor').' #'.$value.' ('.esc_html__('Deleted').')';
                    }

                    return $wp_user->display_name;
                },
            ],
            [
                'db' => 'MAX(`user_id`)', 'dt' => 'user_name', 'field' => 'user_id', 'as' => 'user_id', 'formatter' => function ($value, $row) {
                    if (0 == $value) {
                        return esc_html__('Visitors', 'wpcloudplugins');
                    }

                    $wp_user = get_userdata($value);

                    if (false === $wp_user) {
                        return ' #'.$value.' ('.esc_html__('Deleted').')';
                    }

                    return $wp_user->user_login;
                },
            ],
            [
                'db' => 'COUNT(`entry_id`)', 'dt' => 'total', 'field' => 'total', 'as' => 'total',
            ],
        ];

        $where = "`plugin` = 'share-one-drive' AND `type` IN ('shareonedrive_uploaded_entry')";

        global $wpdb;

        if (!empty($period)) {
            if (is_int($period) || 1 === count($period)) {
                // If $period is an interval (e.g. when sending summary email
                $sql = ' AND (`datetime` BETWEEN DATE_SUB(NOW(), INTERVAL %d second) AND NOW())';
                $where .= $wpdb->prepare($sql, [$period]);
            } else {
                $sql = ' AND (`datetime` BETWEEN %s AND %s )';
                $where .= $wpdb->prepare($sql, [$period['start'], $period['end']]);
            }
        }

        $groupBy = '`user_id`';

        return $this->get_results($columns, $where, $groupBy, true, '', '');
    }

    /**
     * Get the events filtered via Datatables requests.
     *
     * @global $wpdb
     *
     * @param mixed      $filter_events
     * @param null|mixed $period
     *
     * @return array
     */
    public function get_full_log($filter_events = [], $period = null)
    {
        $columns = [
            [
                'db' => 'id', 'dt' => 'id', 'field' => 'id',
            ],
            [
                'db' => 'type', 'dt' => 'icon', 'field' => 'type', 'formatter' => function ($value, $row) {
                    $event = $this->get_event_type($value);

                    return $event['icon'];
                },
            ],
            [
                'db' => 'type', 'dt' => 'description', 'field' => 'type', 'formatter' => function ($value, $row) {
                    return $this->get_event_description($row);
                },
            ],
            [
                'db' => 'datetime', 'dt' => 'timestamp', 'field' => 'datetime',
            ],
            [
                'db' => 'datetime', 'dt' => 'datetime', 'field' => 'datetime', 'formatter' => function ($value, $row) {
                    return date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($value));
                },
            ],
            [
                'db' => 'type', 'dt' => 'type', 'field' => 'type', 'formatter' => function ($value, $row) {
                    $event = $this->get_event_type($value);

                    return $event['text'];
                },
            ],
            [
                'db' => 'user_id', 'dt' => 'user', 'field' => 'user_id', 'formatter' => function ($value, $row) {
                    if (0 == $value) {
                        return esc_html__('A visitor', 'wpcloudplugins');
                    }

                    $user = get_userdata($value);

                    if (false === $user) {
                        return ' #'.$value.' ('.esc_html__('Deleted').')';
                    }

                    return $user->display_name;
                },
            ],
            [
                'db' => 'user_id', 'dt' => 'user_id', 'field' => 'user_id',
            ],
            [
                'db' => 'user_id', 'dt' => 'user_icon', 'field' => 'icon', 'as' => 'icon', 'formatter' => function ($value, $row) {
                    if (0 == $value) {
                        return SHAREONEDRIVE_ROOTPATH.'/css/images/usericon.png';
                    }
                    $user = get_userdata($value);

                    if (false === $user) {
                        return SHAREONEDRIVE_ROOTPATH.'/css/images/usericon.png';
                    }

                    $display_gravatar = get_avatar_url($user->user_email, 32);
                    if (false === $display_gravatar) {
                        // Gravatar is disabled, show default image.
                        $display_gravatar = SHAREONEDRIVE_ROOTPATH.'/css/images/usericon.png';
                    }

                    return $display_gravatar;
                },
            ],
            [
                'db' => 'entry_id', 'dt' => 'entry_id', 'field' => 'entry_id',
            ],
            [
                'db' => 'account_id', 'dt' => 'account_id', 'field' => 'account_id', 'as' => 'account_id',
            ],
            [
                'db' => 'drive_id', 'dt' => 'drive_id', 'field' => 'drive_id', 'as' => 'drive_id',
            ],
            [
                'db' => 'entry_mimetype', 'dt' => 'entry_mimetype', 'field' => 'entry_mimetype',
            ],
            [
                'db' => 'entry_is_dir', 'dt' => 'entry_is_dir', 'field' => 'entry_is_dir',
            ],
            [
                'db' => 'entry_name', 'dt' => 'entry_name', 'field' => 'entry_name', 'formatter' => function ($value, $row) {
                    return $value;
                },
            ],
            [
                'db' => 'parent_id', 'dt' => 'parent_id', 'field' => 'parent_id',
            ],
            [
                'db' => 'parent_path', 'dt' => 'parent_path', 'field' => 'parent_path', 'formatter' => function ($value, $row) {
                    return $value;
                },
            ],
            [
                'db' => 'location', 'dt' => 'location', 'field' => 'location', 'formatter' => function ($value, $row) {
                    return str_replace(get_home_url(), '', urldecode($value));
                },
            ],
            [
                'db' => 'location', 'dt' => 'location_full', 'field' => 'location', 'formatter' => function ($value, $row) {
                    return urldecode($value);
                },
            ],
            [
                'db' => 'extra', 'dt' => 'extra', 'field' => 'extra', 'formatter' => function ($value, $row) {
                    if (empty($value)) {
                        return '';
                    }

                    $extra_data = json_decode($value, true);

                    $extra_data_str = '<ul>';
                    if (is_array($extra_data)) {
                        foreach ($extra_data as $key => $val) {
                            if (is_array($val)) {
                                $extra_data_str .= '<li>'.$key.' => '.print_r($val, true).'</li>';
                            } else {
                                $extra_data_str .= '<li>'.$key.' => '.$val.'</li>';
                            }
                        }
                    }
                    $extra_data_str .= '</ul>';

                    return $extra_data_str;
                },
            ],
        ];

        $where = "`plugin` = 'share-one-drive'";

        global $wpdb;
        if (!empty($_REQUEST['detail']) && !empty($_REQUEST['id'])) {
            switch ($_REQUEST['detail']) {
                case 'user':
                    $sql = ' AND `user_id` = %s ';

                    break;

                case 'entry':
                    $sql = ' AND `entry_id` = %s ';

                    break;
            }
            $where .= $wpdb->prepare($sql, [$_REQUEST['id']]);
        }

        if (!empty($filter_events)) {
            $where .= ' AND `type` IN ("'.implode('","', $filter_events).'") ';
        }

        if (!empty($period)) {
            if (is_int($period) || 1 === count($period)) {
                // If $period is an interval (e.g. when sending summary email
                $sql = ' AND (`datetime` BETWEEN DATE_SUB(NOW(), INTERVAL %d second) AND NOW())';
                $where .= $wpdb->prepare($sql, [$period]);
            } else {
                $sql = ' AND (`datetime` BETWEEN %s AND %s )';
                $where .= $wpdb->prepare($sql, [$period['start'], $period['end']]);
            }
        }

        return $this->get_results($columns, $where, null);
    }

    /**
     * Get the properties for the event types (e.g. Event name, Colors, Icons, etc).
     *
     * @param string $type
     *
     * @return array
     */
    public function get_event_type($type)
    {
        switch ($type) {
            case 'shareonedrive_previewed_entry':
                $text = esc_html__('Previewed', 'wpcloudplugins');
                $icon = 'eva-eye-outline';
                $colors = [
                    'light' => '#590E5450',
                    'normal' => '#590E54',
                    'dark' => '#7b1fa2',
                ];

                break;

            case 'shareonedrive_edited_entry':
                $text = esc_html__('Edited', 'wpcloudplugins');
                $icon = 'eva-edit-outline';
                $colors = [
                    'light' => '#590E5450',
                    'normal' => '#590E54',
                    'dark' => '#7b1fa2',
                ];

                break;

            case 'shareonedrive_downloaded_entry':
                $text = esc_html__('Downloaded', 'wpcloudplugins');
                $icon = 'eva-download';
                $colors = [
                    'light' => '#673ab750',
                    'normal' => '#673ab7',
                    'dark' => '#512da8',
                ];

                break;

            case 'shareonedrive_streamed_entry':
                $text = esc_html__('Streamed', 'wpcloudplugins');
                $icon = 'eva-play-circle-outline';
                $colors = [
                    'light' => '#3f51b550',
                    'normal' => '#3f51b5',
                    'dark' => '#303f9f',
                ];

                break;

            case 'shareonedrive_created_link_to_entry':
                $text = esc_html__('Shared', 'wpcloudplugins');
                $icon = 'eva-share-outline';
                $colors = [
                    'light' => '#00bcd450',
                    'normal' => '#00bcd4',
                    'dark' => '#0097a7',
                ];

                break;

            case 'shareonedrive_renamed_entry':
                $text = esc_html__('Renamed', 'wpcloudplugins');
                $icon = 'eva-edit-2-outline';
                $colors = [
                    'light' => '#00968850',
                    'normal' => '#009688',
                    'dark' => '#00796b',
                ];

                break;

            case 'shareonedrive_deleted_entry':
                $text = esc_html__('Deleted', 'wpcloudplugins');
                $icon = 'eva-trash-2-outline';
                $colors = [
                    'light' => '#f4433650',
                    'normal' => '#f44336',
                    'dark' => '#d32f2f',
                ];

                break;

            case 'shareonedrive_created_entry':
                $text = esc_html__('Created', 'wpcloudplugins');
                $icon = 'eva-plus-circle-outline';
                $colors = [
                    'light' => '#4caf5050',
                    'normal' => '#4caf50',
                    'dark' => '#388e3c',
                ];

                break;

            case 'shareonedrive_updated_description':
                $text = esc_html__('Description updated', 'wpcloudplugins');
                $icon = 'eva-message-square-outline';
                $colors = [
                    'light' => '#00968850',
                    'normal' => '#009688',
                    'dark' => '#00796b',
                ];

                break;

            case 'shareonedrive_updated_metadata':
                $text = esc_html__('Metadata updated', 'wpcloudplugins');
                $icon = 'eva-hash';
                $colors = [
                    'light' => '#ff572250',
                    'normal' => '#ff5722',
                    'dark' => '#e64a19',
                ];

                break;

            case 'shareonedrive_moved_entry':
                $text = esc_html__('Moved', 'wpcloudplugins');
                $icon = 'eva-corner-down-right';
                $colors = [
                    'light' => '#ffeb3b50',
                    'normal' => 'ffeb3b',
                    'dark' => '#fbc02d',
                ];

                break;

            case 'shareonedrive_uploaded_entry':
                $text = esc_html__('Uploaded', 'wpcloudplugins');
                $icon = 'eva-upload';
                $colors = [
                    'light' => '#cddc3950',
                    'normal' => '#cddc39',
                    'dark' => '#afb42b',
                ];

                break;

            case 'shareonedrive_uploaded_failed':
                $text = esc_html__('Upload failed', 'wpcloudplugins');
                $icon = 'eva-alert-circle-outline';
                $colors = [
                    'light' => '#f4433650',
                    'normal' => '#f44336',
                    'dark' => '#d32f2f',
                ];

                break;

            case 'shareonedrive_searched':
                $text = esc_html__('Searched', 'wpcloudplugins');
                $icon = 'eva-search';
                $colors = [
                    'light' => '#ffc10750',
                    'normal' => '#ffc107',
                    'dark' => '#ffa000',
                ];

                break;

            case 'shareonedrive_sent_notification':
                $text = esc_html__('Notification', 'wpcloudplugins');
                $icon = 'eva-email-outline';
                $colors = [
                    'light' => '#ff572250',
                    'normal' => '#ff5722',
                    'dark' => '#e64a19',
                ];

                break;

            case 'shareonedrive_error':
                $text = esc_html__('Warning', 'wpcloudplugins');
                $icon = 'eva-alert-circle-outline';
                $colors = [
                    'light' => '#f4433650',
                    'normal' => '#f44336',
                    'dark' => '#d32f2f',
                ];

                break;

            default:
                $text = $type;
                $icon = 'eva-star-outline';
                $colors = [
                    'light' => '#9e9e9e50',
                    'normal' => '#9e9e9e',
                    'dark' => '#424242',
                ];

                break;
        }

        return apply_filters('shareonedrive_events_set_properties', ['text' => $text, 'icon' => $icon, 'colors' => $colors], $type);
    }

    /**
     * Create an event description for a specific Event Database Row.
     *
     * @param string $event_row
     *
     * @return string
     */
    public function get_event_description($event_row)
    {
        // Get the User
        if ('0' === $event_row['user_id']) {
            $user = esc_html__('A visitor', 'wpcloudplugins');
        } elseif ($wp_user = get_userdata($event_row['user_id'])) {
            $user = $wp_user->display_name;
            $user = "<a href='#{$event_row['user_id']}' class='open-user-details' data-user-id='{$event_row['user_id']}'>{$user}</a>";
        } else {
            $user = esc_html__('User').' #'.$event_row['user_id'].' ('.esc_html__('Deleted').')';
        }

        // Is entry  a file or a folder
        $file_or_folder = ('1' === $event_row['entry_is_dir']) ? esc_html__('folder', 'wpcloudplugins') : esc_html__('file', 'wpcloudplugins');

        $account_id = empty($event_row['account_id']) ? Accounts::instance()->get_primary_account()->get_id() : $event_row['account_id'];

        $drive_id = empty($event_row['drive_id']) ? App::get_primary_drive_id() : $event_row['drive_id'];

        // Generate File link
        $entry_link = "<a href='#{$event_row['entry_id']}' title='{$event_row['parent_path']}/{$event_row['entry_name']}' class='open-entry-details' data-entry-id='{$event_row['entry_id']}' data-account-id='{$account_id}' data-drive-id='{$drive_id}'>{$event_row['entry_name']}</a>";

        // Generate link to parent folder
        $parent_folder_link = "<a href='#{$event_row['parent_id']}' title='{$event_row['parent_path']}' class='open-entry-details' data-entry-id='{$event_row['parent_id']}' data-account-id='{$account_id}' data-drive-id='{$drive_id}'>{$event_row['parent_path']}</a>";

        // Decode the Extra data field
        $data = json_decode($event_row['extra'], true);

        // Create the description
        switch ($event_row['type']) {
            case 'shareonedrive_previewed_entry':
                $description = sprintf(esc_html__('%s previewed the %s %s', 'wpcloudplugins'), $user, $file_or_folder, $entry_link);

                break;

            case 'shareonedrive_edited_entry':
                $description = sprintf(esc_html__('%s edited the %s %s', 'wpcloudplugins'), $user, $file_or_folder, $entry_link);

                break;

            case 'shareonedrive_downloaded_entry':
                $exported = ($data && isset($data['exported'])) ? $data['exported'] : false;
                $as_zip = ($data && isset($data['as_zip'])) ? $exported = 'ZIP' : false;

                $description = sprintf(esc_html__('%s downloaded the %s %s', 'wpcloudplugins'), $user, $file_or_folder, $entry_link);

                if (false !== $exported) {
                    $description = sprintf(esc_html__('%s downloaded the %s %s as %s file', 'wpcloudplugins'), $user, $file_or_folder, $entry_link, $exported);
                }

                break;

            case 'shareonedrive_streamed_entry':
                $description = sprintf(esc_html__('%s streamed the %s %s', 'wpcloudplugins'), $user, $file_or_folder, $entry_link);

                break;

            case 'shareonedrive_created_link_to_entry':
                $created_url = ($data && isset($data['url'])) ? "<a href='{$data['url']}' target='_blank'>".esc_html__('Shared link', 'wpcloudplugins').'</a>' : '';
                $description = sprintf(esc_html__('%s created a %s for the %s %s', 'wpcloudplugins'), $user, $created_url, $file_or_folder, $entry_link);

                break;

            case 'shareonedrive_renamed_entry':
                $previous_name = ($data && isset($data['oldname'])) ? $data['oldname'].' ' : '';
                $description = sprintf(esc_html__('%s renamed the %s %s to %s', 'wpcloudplugins'), $user, $file_or_folder, $previous_name, $entry_link);

                break;

            case 'shareonedrive_deleted_entry':
                $description = sprintf(esc_html__('%s deleted the %s %s', 'wpcloudplugins'), $user, $file_or_folder, $entry_link);

                break;

            case 'shareonedrive_created_entry':
                $description = sprintf(esc_html__('%s added the %s %s in %s', 'wpcloudplugins'), $user, $file_or_folder, $entry_link, $parent_folder_link);

                break;

            case 'shareonedrive_updated_description':
                $description = sprintf(esc_html__('%s updated the description of the %s %s', 'wpcloudplugins'), $user, $file_or_folder, $entry_link);

                break;

            case 'shareonedrive_updated_metadata':
                $metadata_field = ($data && isset($data['metadata_field'])) ? $data['metadata_field'] : '';
                $description = sprintf(esc_html__('%s updated the %s of the %s %s', 'wpcloudplugins'), $user, $metadata_field, $file_or_folder, $entry_link);

                break;

            case 'shareonedrive_moved_entry':
                $description = sprintf(esc_html__('%s moved the %s %s to %s', 'wpcloudplugins'), $user, $file_or_folder, $entry_link, $parent_folder_link);

                break;

            case 'shareonedrive_uploaded_entry':
                $description = sprintf(esc_html__('%s added the %s %s in %s', 'wpcloudplugins'), $user, $file_or_folder, $entry_link, $parent_folder_link);

                break;

            case 'shareonedrive_uploaded_failed':
                $name = ($data && isset($data['name'])) ? $data['name'] : 'unknown';
                $size = ($data && isset($data['size'])) ? $data['size'] : '-';
                $entry_link = "<a href='#{$event_row['entry_id']}' title='{$event_row['parent_path']}/{$event_row['entry_name']}' class='open-entry-details' data-entry-id='{$event_row['entry_id']}' data-account-id='{$account_id}' data-drive-id='{$drive_id}'>{$event_row['parent_path']}/{$event_row['entry_name']}</a>";

                $description = sprintf(esc_html__('%s tried to upload %s (%s) to %s', 'wpcloudplugins'), $user, '<a>'.$name.'</a>', Helpers::bytes_to_size_1024($size), $entry_link);

                break;

            case 'shareonedrive_searched':
                $search_query = ($data && isset($data['query'])) ? $data['query'] : '';
                $description = sprintf(esc_html__('%s searched for "%s" in %s', 'wpcloudplugins'), $user, $search_query, $entry_link);

                break;

            case 'shareonedrive_sent_notification':
                $subject = ($data && isset($data['subject'])) ? $data['subject'] : '';
                $recipients = ($data && isset($data['recipients'])) ? count($data['recipients']) : 1;
                $description = sprintf(esc_html__('Notification "%s" sent to %s recipient(s)', 'wpcloudplugins'), $subject, $recipients);

                break;

            case 'shareonedrive_error':
                $message = ($data && isset($data['message'])) ? $data['message'] : '';
                $description = sprintf(esc_html__('Warning: %s', 'wpcloudplugins'), $message);

                break;

            default:
                $description = sprintf(esc_html__('%s performed an action: %s for %s', 'wpcloudplugins'), $user, $event_row['type'], $entry_link);

                break;
        }

        return apply_filters('shareonedrive_events_set_description', $description, $event_row['type'], $event_row, $user, $file_or_folder, $entry_link, $parent_folder_link);
    }

    /**
     * Get the user details for Dashboard.
     *
     * @param int|string $user_id
     */
    public function render_user_detail($user_id)
    {
        $wp_user = get_userdata($user_id);
        $display_gravatar = get_avatar_url($wp_user->user_email, ['size' => 512]);

        $user_name = $wp_user->display_name;
        $roles_translated = [];
        $wp_roles = new \WP_Roles();

        foreach ($wp_user->roles as $role) {
            $roles_translated[] = $wp_roles->role_names[$role];
        }

        $return = [
            'user' => [
                'user_id' => $wp_user->ID,
                'user_name' => $user_name,
                'user_email' => $wp_user->user_email,
                'user_link' => get_edit_user_link($wp_user->ID),
                'user_roles' => implode(', ', $roles_translated),
                'avatar' => $display_gravatar,
            ],
        ];

        echo json_encode($return);

        exit;
    }

    /**
     * Get the details for a specific file.
     *
     * @param int|string $entry_id
     * @param string     $account_id
     * @param string     $drive_id
     */
    public function render_entry_detail($entry_id, $account_id, $drive_id)
    {
        $cloud_account = Accounts::instance()->get_account_by_id($account_id);
        App::set_current_drive_id($drive_id);

        if (empty($cloud_account)) {
            $cached_entry = false;
        } else {
            App::set_current_account($cloud_account);
            $cached_entry = Client::instance()->get_entry($entry_id, false);
        }

        if (false === $cached_entry) {
            $sql = 'SELECT * FROM `'.Event_DB_Model::table().'` WHERE `entry_id` = %s';
            $values = [$entry_id];
            $data = Event_DB_Model::get_custom_sql($sql, $values, 'ARRAY_A');

            $return = [
                'entry' => [
                    'entry_id' => $entry_id,
                    'entry_name' => $data[0]['entry_name'],
                    'entry_description' => esc_html__('The file you are looking for cannot be found.', 'wpcloudplugins').' '.esc_html__('The file is probably deleted from the cloud.', 'wpcloudplugins'),
                    'entry_link' => false,
                    'entry_thumbnails' => str_replace('32x32', '256x256', Helpers::get_default_icon($data[0]['entry_mimetype'], $data[0]['entry_is_dir'])),
                ],
            ];

            echo json_encode($return);

            exit;
        }

        $entry = $cached_entry->get_entry();

        $download_link = ($entry->is_file()) ? SHAREONEDRIVE_ADMIN_URL."?action=shareonedrive-event-stats&type=get-download&account_id={$account_id}&drive_id={$drive_id}&id=".$entry->get_id() : false;

        $crop = ('personal' === App::get_current_account()->get_type()) ? 'none' : 'center';
        $thumbnail_medium = $entry->get_thumbnail_with_size(350, 350, $crop);

        $return = [
            'entry' => [
                'entry_id' => $entry->get_id(),
                'entry_name' => $entry->get_name(),
                'entry_description' => ($entry->get_description()) ? $entry->get_description() : '',
                'entry_link' => $download_link,
                'entry_thumbnails' => $thumbnail_medium,
            ],
        ];

        echo json_encode($return);

        exit;
    }

    public function send_event_summary()
    {
        // Set events for in activity list
        $filter_events = apply_filters('shareonedrive_events_set_summary_events', [
            'shareonedrive_downloaded_entry',
            'shareonedrive_uploaded_entry',
            'shareonedrive_deleted_entry',
            'shareonedrive_moved_entry',
            'shareonedrive_renamed_entry',
            'shareonedrive_created_entry',
            'shareonedrive_streamed_entry',
            'shareonedrive_created_link_to_entry',
            'shareonedrive_searched',
            'shareonedrive_edited_entry',
        ], $this);

        $max_top_downloads = apply_filters('shareonedrive_events_set_summary_top_downloads', 10, $this);
        $max_events = apply_filters('shareonedrive_events_set_summary_max_events', 25, $this);

        // Set period selection
        $interval = 86400; // Day in seconds
        $wp_cron_schedules = wp_get_schedules();
        $summary_schedule = Processor::instance()->get_setting('event_summary_period');
        if (isset($wp_cron_schedules[$summary_schedule])) {
            $interval = $wp_cron_schedules[$summary_schedule]['interval'];
        }

        $interval = apply_filters('shareonedrive_events_set_summary_period', $interval, $this);

        $since = date_create();
        date_sub($since, date_interval_create_from_date_string($interval.' seconds'));

        // Generate summary data and sort it, used in template
        $topdownloads = $this->get_top_downloads($interval);
        usort($topdownloads['data'], function ($entry1, $entry2) {
            return $entry2['total'] - $entry1['total'];
        });

        $all_events = $this->get_full_log($filter_events, $interval);
        usort($all_events['data'], function ($entry1, $entry2) {
            return $entry1['timestamp'] < $entry2['timestamp'] ? +1 : -1;
        });

        // Don't send summary if no data is available
        if (0 === $topdownloads['recordsFiltered'] && 0 === $all_events['recordsFiltered']) {
            return;
        }

        // Template colors
        $colors = Processor::instance()->get_setting('colors');

        // Send mail
        try {
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            $recipients = array_unique(array_map('trim', explode(',', Processor::instance()->get_setting('event_summary_recipients'))));

            foreach ($recipients as $recipient) {
                // Generate Email content from template
                $subject = get_bloginfo().' | '.sprintf(esc_html__('%s activity for %s', 'wpcloudplugins'), 'Share-one-Drive', date_i18n('l j F '));
                $template = apply_filters('shareonedrive_events_set_summary_template', SHAREONEDRIVE_ROOTDIR.'/templates/notifications/event_summary.php', $this, $recipient);

                ob_start();

                include_once $template;
                $htmlmessage = Helpers::compress_html(ob_get_clean());

                $result = wp_mail($recipient, $subject, $htmlmessage, $headers);
            }
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.esc_html__('Could not send email').': '.$ex->getMessage());
        }
    }

    /**
     * Do all the SQL action for Datatables request via the SSP class.
     *
     * @param mixed $columns
     * @param mixed $where
     * @param mixed $groupBy
     * @param mixed $join
     * @param mixed $having
     * @param mixed $limit
     */
    public function get_results($columns, $where, $groupBy, $join = true, $having = '', $limit = '')
    {
        $table = Event_DB_Model::table();

        $primaryKey = 'id';

        require_once SHAREONEDRIVE_ROOTDIR.'/vendors/datatables/ssp.class.php';

        return SSP::simple($_GET, $table, $primaryKey, $columns, $join, $where, $groupBy, $having, $limit);
    }

    /**
     * Create the event table in the WordPress Database.
     *
     * @global  $wpdb
     */
    public static function install_database()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = Event_DB_Model::table();

        $sql = "CREATE TABLE {$table_name} (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `plugin` varchar(50) NOT NULL,
                    `type` varchar(50) NOT NULL,
                    `user_id` bigint(20) NOT NULL,
                    `entry_id` varchar(255) NOT NULL,
                    `entry_mimetype` varchar(255) NOT NULL,
                    `entry_is_dir` BOOLEAN NOT NULL, 
                    `entry_name` TEXT NOT NULL,
                    `account_id` varchar(255) ,
                    `drive_id` varchar(255) DEFAULT 'drive',                    
                    `parent_id` varchar(255) NOT NULL,
                    `parent_path` TEXT NOT NULL,
                    `location` TEXT,
                    `extra` TEXT NOT NULL,
                    UNIQUE KEY id (id)
                    ) {$charset_collate};";

        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function drop_database()
    {
        $table_name = Event_DB_Model::table();

        global $wpdb;
        $result = $wpdb->query("DROP TABLE IF EXISTS {$table_name}");

        return $result;
    }

    public static function truncate_database()
    {
        $table_name = Event_DB_Model::table();

        global $wpdb;
        $result = $wpdb->query("TRUNCATE {$table_name}");

        return $result;
    }

    public static function export()
    {
        $table_name = Event_DB_Model::table();

        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM {$table_name}", ARRAY_A);

        if (empty($results)) {
            return;
        }

        return $results;
    }

    public static function uninstall()
    {
        self::drop_database();

        $summary_cron_job = wp_next_scheduled('shareonedrive_send_event_summary');
        if (false !== $summary_cron_job) {
            wp_unschedule_event($summary_cron_job, 'shareonedrive_send_event_summary');
        }
    }
}

class Event_DB_Model
{
    public static $primary_key = 'id';

    public static function table()
    {
        global $wpdb;

        return $wpdb->prefix.'wp_cloudplugins_log_shareonedrive';
    }

    public static function get($value)
    {
        global $wpdb;

        return $wpdb->get_row(self::_fetch_sql($value));
    }

    public static function get_custom_sql($sql, $values, $output = 'OBJECT')
    {
        global $wpdb;
        if (!empty($values)) {
            $prepared = $wpdb->prepare($sql, $values);

            return $wpdb->get_results($prepared, $output);
        }

        return $wpdb->get_results($sql, $output);
    }

    public static function insert($data)
    {
        global $wpdb;
        $wpdb->insert(self::table(), $data);
    }

    public static function update($data, $where)
    {
        global $wpdb;
        $wpdb->update(self::table(), $data, $where);
    }

    public static function delete($value)
    {
        global $wpdb;
        $sql = sprintf('DELETE FROM %s WHERE %s = %%s', self::table(), static::$primary_key);

        return $wpdb->query($wpdb->prepare($sql, $value));
    }

    public static function insert_id()
    {
        global $wpdb;

        return $wpdb->insert_id;
    }

    public static function time_to_date($time)
    {
        return gmdate('Y-m-d H:i:s', $time);
    }

    public static function now()
    {
        return self::time_to_date(time());
    }

    public static function date_to_time($date)
    {
        return strtotime($date.' GMT');
    }

    private static function _fetch_sql($value)
    {
        global $wpdb;
        $sql = sprintf('SELECT * FROM %s WHERE %s = %%s', self::table(), static::$primary_key);

        return $wpdb->prepare($sql, $value);
    }
}
