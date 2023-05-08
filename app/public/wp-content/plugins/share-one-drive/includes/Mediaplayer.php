<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\ShareoneDrive;

class Mediaplayer
{
    private $_folder;
    private $_items;

    public function getMediaList()
    {
        $this->_folder = Client::instance()->get_folder();

        if (false === $this->_folder) {
            exit;
        }

        $sub_entries = Client::instance()->get_folder_recursive($this->_folder['folder']);
        $this->_folder['contents'] = array_merge($sub_entries, $this->_folder['contents']);
        $this->_items = $this->createItems();

        if (count($this->_items) > 0) {
            $response = json_encode($this->_items);

            $cached_request = new CacheRequest();
            $cached_request->add_cached_response($response);
            echo $response;
        }

        exit;
    }

    public function setFolder($folder)
    {
        $this->_folder = $folder;
    }

    public function createItems()
    {
        $covers = [];
        $captions = [];

        // Add covers and Captions
        if (count($this->_folder['contents']) > 0) {
            foreach ($this->_folder['contents'] as $key => $node) {
                $child = $node->get_entry();

                if (!isset($child->extension)) {
                    continue;
                }

                if (in_array(strtolower($child->extension), ['png', 'jpg', 'jpeg'])) {
                    // Add images to cover array
                    $covers[$child->get_basename()] = $child;
                    unset($this->_folder['contents'][$key]);
                } elseif (in_array(strtolower($child->extension), ['vtt', 'srt'])) {
                    /*
                     * SRT | VTT files are supported for captions:.
                     *
                     * Filename: Videoname.Caption Label.Language.VTT|SRT
                     */

                    preg_match('/(?<name>.*).(?<label>\w*).(?<language>\w*)\.(srt|vtt)$/Uu', $child->get_name(), $match, PREG_UNMATCHED_AS_NULL, 0);

                    if (0 === count($match) || empty($match['language'])) {
                        continue;
                    }

                    $video_name = $match['name'];

                    if (!isset($captions[$video_name])) {
                        $captions[$video_name] = [];
                    }

                    if (false === array_search($match['label'], array_column($captions[$video_name], 'label'))) {
                        $captions[$video_name][] = [
                            'label' => $match['label'],
                            'language' => $match['language'],
                            'src' => SHAREONEDRIVE_ADMIN_URL.'?action=shareonedrive-stream&id='.$child->get_id().'&dl=1&caption=1&account_id='.$this->_folder['folder']->get_account_id().'&drive_id='.$node->get_drive_id().'&listtoken='.Processor::instance()->get_listtoken(),
                        ];
                    }
                    unset($this->_folder['contents'][$key]);
                }
            }
        }

        $files = [];

        // Create Filelist array
        if (count($this->_folder['contents']) > 0) {
            $foldername = $this->_folder['folder']->get_entry()->get_name();

            foreach ($this->_folder['contents'] as $node) {
                $child = $node->get_entry();

                if (false === $this->is_media_file($node)) {
                    continue;
                }

                // Check if entry is allowed
                if (!Processor::instance()->_is_entry_authorized($node)) {
                    continue;
                }

                $basename = $child->get_basename();
                $extension = $child->get_extension();

                $thumb_width = 300;
                $thumb_height = round($thumb_width * 0.5625); // 16:9 ratio

                // FIX: Business accounts thumbnail links not working with empty height value
                if ($child->get_media('height') > 0 && $child->get_media('width') > 0) {
                    $thumb_height = round(($child->get_media('height') / $child->get_media('width')) * $thumb_width);
                }

                if (isset($covers[$basename])) {
                    $poster = $covers[$basename]->get_thumbnail_large();
                    $thumbnailsmall = $covers[$basename]->get_thumbnail_with_size($thumb_height, $thumb_width);
                } elseif (isset($covers[$foldername])) {
                    $poster = $covers[$foldername]->get_thumbnail_large();
                    $thumbnailsmall = $covers[$foldername]->get_thumbnail_with_size($thumb_height, $thumb_width);
                } else {
                    $poster = $child->get_thumbnail_large();
                    $thumbnailsmall = $child->get_thumbnail_with_size($thumb_height, $thumb_width);
                }

                $use_ID3 = '1' === Processor::instance()->get_shortcode_option('id3') && (null !== $child->get_media('title'));

                $folder_str = dirname($node->get_path($this->_folder['folder']->get_id()));
                $folder_str = trim(str_replace('\\', '/', $folder_str), '/');
                $path = $folder_str.$basename;

                // combine same files with different extensions
                if (!isset($files[$path])) {
                    $source_url = SHAREONEDRIVE_ADMIN_URL.'?action=shareonedrive-stream&id='.$child->get_id().'&dl=1&account_id='.$this->_folder['folder']->get_account_id().'&drive_id='.$node->get_drive_id().'&listtoken='.Processor::instance()->get_listtoken();
                    if ('Yes' !== Processor::instance()->get_setting('google_analytics')) {
                        $cached_source_url = get_transient('shareonedrive_stream_'.$child->get_id().'_'.$child->get_extension());
                        if (false !== $cached_source_url && false === filter_var($cached_source_url, FILTER_VALIDATE_URL)) {
                            $source_url = $cached_source_url;
                        }
                    }

                    $has_track_numbers = $use_ID3 && (null !== $child->get_media('track'));
                    if ($use_ID3) {
                        $track = ($has_track_numbers) ? $child->get_media('track').'. ' : '';
                        $title = $track.$child->get_media('title');
                        $album = $child->get_media('album');
                        $album = (!empty($album)) ? $child->get_media('album').' &#8226; ' : '';
                        $artist = (null !== $child->get_media('artist')) ? $child->get_media('artist') : '';
                    } else {
                        $title = $child->get_basename();
                        $album = '';
                        $artist = $child->get_description();
                    }

                    $last_edited = $child->get_last_edited();
                    $localtime = get_date_from_gmt(date('Y-m-d H:i:s', $last_edited));

                    $files[$path] = [
                        'title' => ($has_track_numbers) ? $title : $basename,
                        'name' => $path,
                        'artist' => $album.$artist,
                        'is_dir' => false,
                        'size' => $child->get_size(),
                        'id'=> $child->get_id(),
                        'last_edited' => $last_edited,
                        'last_edited_date_str' => !empty($last_edited) ? date_i18n(get_option('date_format'), strtotime($localtime)) : '',
                        'last_edited_time_str' => !empty($last_edited) ? date_i18n(get_option('time_format'), strtotime($localtime)) : '',
                        'created_time' => $child->get_created_time(),
                        'created_str' => $child->get_created_time_str(),
                        'folder' => $folder_str,
                        'poster' => $poster,
                        'thumb' => $thumbnailsmall,
                        'download' => (User::can_download()) ? str_replace('shareonedrive-stream', 'shareonedrive-download', $source_url) : false,
                        'share' => User::can_share(),
                        'deeplink' => User::can_deeplink(),
                        'source' => $source_url,
                        'captions' => isset($captions[$basename]) ? $captions[$basename] : [],
                        'type' => Helpers::get_mimetype($extension),
                        'width' => $child->get_media('width'),
                        'height' => $child->get_media('height'),
                        'duration' => $child->get_media('duration') / 1000, // ms to sec,
                        'linktoshop' => ('' !== Processor::instance()->get_shortcode_option('linktoshop')) ? Processor::instance()->get_shortcode_option('linktoshop') : false,
                    ];
                }
            }

            $files = Processor::instance()->sort_filelist($files);
        }

        if ('-1' !== Processor::instance()->get_shortcode_option('max_files')) {
            $files = array_slice($files, 0, Processor::instance()->get_shortcode_option('max_files'));
        }

        return array_values($files);
    }

    public function is_media_file(CacheNode $node)
    {
        $entry = $node->get_entry();

        if ($entry->is_dir()) {
            return false;
        }

        $extension = $entry->get_extension();
        $mimetype = $entry->get_mimetype();

        if ('audio' === Processor::instance()->get_shortcode_option('mode')) {
            $allowedextensions = ['mp3', 'm4a', 'ogg', 'oga', 'wav'];
            $allowedimimetypes = ['audio/mpeg', 'audio/mp4', 'audio/ogg', 'audio/x-wav'];
        } else {
            $allowedextensions = ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'webm'];
            $allowedimimetypes = ['video/mp4', 'video/ogg', 'video/webm'];
        }

        if (!empty($extension) && in_array($extension, $allowedextensions)) {
            return true;
        }

        return in_array($mimetype, $allowedimimetypes);
    }
}
