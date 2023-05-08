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

class Entry extends EntryAbstract
{
    public $folder_thumbnails = [];
    public $child_count;
    public $remote_item;
    public $drive_id;

    public function convert_api_entry($api_entry)
    {
        // @var $api_entry \SODOneDrive_Service_Drive_Item

        if (!$api_entry instanceof \SODOneDrive_Service_Drive_Item) {
            error_log('[WP Cloud Plugin message]: '.sprintf('OneDrive response is not a valid Entry.'));

            exit;
        }

        // Normal Meta Data
        $this->set_id($api_entry->getId());
        $this->set_name($api_entry->getName());

        if (null !== $api_entry->getFolder()) {
            $this->set_is_dir(true);
            $this->set_child_count($api_entry->getFolder()->getChildCount());
        }

        $pathinfo = Helpers::get_pathinfo($api_entry->getName());
        if ($this->is_file() && isset($pathinfo['extension'])) {
            $this->set_extension(strtolower($pathinfo['extension']));
        }
        $this->set_mimetype_from_extension();

        if ($this->is_file()) {
            $this->set_basename(str_ireplace('.'.$this->get_extension(), '', $this->get_name()));
        } else {
            $this->set_basename($this->get_name());
        }

        $parent = $api_entry->getParentReference();
        if (!empty($parent)) {
            $this->drive_id = $parent->getDriveId();

            if (empty($parent->getId())) {
                $parent->setId('drives');
            }
            $this->set_parents([$parent->getId()]);
            $path = $parent->getPath().'/'.$this->get_name();
            $this->set_path(str_replace('/drive/root:/', '', $path));
        }

        $this->set_trashed(null !== $api_entry->getDeleted());

        $this->set_size($api_entry->getSize());
        $this->set_description($api_entry->getDescription());

        $file_system_info = $api_entry->getFileSystemInfo();

        $last_modified = $api_entry->getLastModifiedDateTime();
        if (!empty($file_system_info) && !empty($file_system_info->getLastModifiedDateTime())) {
            $last_modified = $file_system_info->getLastModifiedDateTime();
        }

        if (is_string($last_modified)) {
            $dtime = \DateTime::createFromFormat('Y-m-d\\TH:i:s.u\\Z', $last_modified, new \DateTimeZone('UTC'));

            // API can return two different formats :(
            if (false === $dtime) {
                $dtime = \DateTime::createFromFormat('Y-m-d\\TH:i:s\\Z', $last_modified, new \DateTimeZone('UTC'));
            }

            if ($dtime) {
                $this->set_last_edited($dtime->getTimestamp());
            }
        }

        $created_date = $api_entry->getCreatedDateTime();
        if (!empty($file_system_info) && !empty($file_system_info->getCreatedDateTime())) {
            $created_date = $file_system_info->getCreatedDateTime();
        }

        if (is_string($created_date)) {
            $dtime = \DateTime::createFromFormat('Y-m-d\\TH:i:s.u\\Z', $created_date, new \DateTimeZone('UTC'));

            // API can return two different formats :(
            if (false === $dtime) {
                $dtime = \DateTime::createFromFormat('Y-m-d\\TH:i:s\\Z', $created_date, new \DateTimeZone('UTC'));
            }

            if ($dtime) {
                $this->set_created_time($dtime->getTimestamp());
            }
        }

        /* Can File be previewed via OneDrive?
         * https://msdn.microsoft.com/en-us/library/office/dn659731.aspx#get_links_to_files_and_folders
         */
        $previewsupport = ['log', 'csv', 'doc', 'docx', 'odp', 'ods', 'odt', 'pot', 'potm', 'potx', 'pps', 'ppsx', 'ppsxm', 'ppt', 'pptm', 'pptx', 'rtf', 'xlsx', 'jpg', 'jpeg', 'gif', 'png', 'webp', 'mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga', 'flac'];
        $previewsupport = array_merge($previewsupport, ['3mf', 'cool', 'glb', 'gltf', 'obj', 'stl']); // 3-D Modeling/Printing
        $previewsupport = array_merge($previewsupport, ['dwg']); // AutoCAD
        $previewsupport = array_merge($previewsupport, ['fbx']); // AutoDesk
        $previewsupport = array_merge($previewsupport, ['epub']); // Open Ebook
        $previewsupport = array_merge($previewsupport, ['ai', 'pdf', 'psb', 'psd']); // Adobe
        $previewsupport = array_merge($previewsupport, ['html', 'txt']); // Other (Business only)

        $openwithonedrive = in_array($this->get_extension(), $previewsupport);
        if ($openwithonedrive) {
            $this->set_can_preview_by_cloud(true);
        }

        $this->set_preview_link($api_entry->getWebUrl());

        // Can File be edited via OneDrive
        $editsupport = ['doc', 'docx', 'odp', 'ods', 'odt', 'pot', 'potm', 'potx', 'pps', 'ppsx', 'ppsxm', 'ppt', 'pptm', 'pptx', 'xls', 'xlsx'];
        $editwithonedrive = in_array($this->get_extension(), $editsupport);
        if ($editwithonedrive) {
            $this->set_can_edit_by_cloud(true);
        }

        // Set the permissions
        $permissions = [
            'canpreview' => $openwithonedrive,
            'candownload' => true,
            'canmove' => true,
            'candelete' => true,
            'canadd' => true,
            'canrename' => true,
        ];
        $this->set_permissions($permissions);

        // Direct Download URL, not always available. Valid for just 1 hour!
        if (isset($api_entry['@microsoft.graph.downloadUrl'])) {
            $this->set_direct_download_link($api_entry['@microsoft.graph.downloadUrl']);
        }
        $this->set_save_as($this->create_save_as());

        // Icon
        $default_icon = $this->get_default_icon();
        $this->set_icon($default_icon);

        // If entry has media data available set it here
        $mediadata = [];
        $imagemetadata = $api_entry->getImage();

        if (!empty($imagemetadata)) {
            $mediadata['width'] = $imagemetadata->getWidth();
            $mediadata['height'] = $imagemetadata->getHeight();
        }

        $photometadata = $api_entry->getPhoto();
        if (!empty($photometadata)) {
            $date_taken = $photometadata->getTakenDateTime();
            $dtime = \DateTime::createFromFormat('Y-m-d\\TH:i:s.u\\Z', $date_taken, new \DateTimeZone('UTC'));

            // API can return two different formats :(
            if (false === $dtime) {
                $dtime = \DateTime::createFromFormat('Y-m-d\\TH:i:s\\Z', $date_taken, new \DateTimeZone('UTC'));
            }

            if ($dtime) {
                $mediadata['datetaken'] = $dtime->getTimestamp();
            }
        }

        $audiometadata = $api_entry->getAudio();
        if (!empty($audiometadata)) {
            $mediadata['duration'] = $audiometadata->getDuration();
            $mediadata['album'] = $audiometadata->getAlbum();
            $mediadata['artist'] = $audiometadata->getArtist();
            $mediadata['title'] = $audiometadata->getTitle();
            $mediadata['track'] = $audiometadata->getTrack();
        }

        $videometadata = $api_entry->getVideo();
        if (!empty($videometadata)) {
            $mediadata['width'] = $videometadata->getWidth();
            $mediadata['height'] = $videometadata->getHeight();
            $mediadata['duration'] = $videometadata->getDuration();
        }

        $this->set_media($mediadata);

        // Thumbnail
        $this->set_thumbnails($api_entry->getThumbnails());

        // Add some data specific for OneDrive Service
        $additional_data = [
        ];

        $this->set_additional_data($additional_data);
    }

    public function set_thumbnails($thumbnails)
    {
        $thumbnail_icon = $this->get_default_icon();
        $thumbnail_icon_large = $this->get_icon_large();

        $this->set_thumbnail_icon($thumbnail_icon);
        $this->set_thumbnail_small($thumbnail_icon);
        $this->set_thumbnail_small_cropped($thumbnail_icon);
        $this->set_thumbnail_medium($thumbnail_icon_large);
        $this->set_thumbnail_large($thumbnail_icon_large);

        if (empty($thumbnails)) {
            return;
        }

        $thumbnail = reset($thumbnails);

        $this->set_has_own_thumbnail(true);

        if (null !== $thumbnail->getC48x48()) {
            $this->set_thumbnail_small($thumbnail->getC48x48()->getUrl());
            $this->set_thumbnail_small_cropped($thumbnail->getC48x48()->getUrl());
        } elseif (null !== $thumbnail->getMedium()) {
            $url_medium = $thumbnail->getMedium()->getUrl();
            $pattern = '/width=\d*&height=\d*/';
            $url_medium = preg_replace($pattern, 'height=48&width=48', $url_medium);
            $this->set_thumbnail_small($url_medium);
            $this->set_thumbnail_small_cropped($url_medium);
        }

        if (null !== $thumbnail->getMedium()) {
            $this->set_thumbnail_icon($thumbnail->getMedium()->getUrl());
            $this->set_thumbnail_medium($thumbnail->getMedium()->getUrl());
            $this->set_thumbnail_large($thumbnail->getMedium()->getUrl());
            $this->set_thumbnail_original($thumbnail->getMedium()->getUrl());

            /* Also update media if not availabe in the ImageFacet/PhotoFacet (Business Accounts)
             * to get an idea of the dimensions
             */
            if (null === $this->get_media('width')) {
                $this->media['width'] = $thumbnail->getMedium()->getWidth();
            }
            if (null === $this->get_media('height')) {
                $this->media['height'] = $thumbnail->getMedium()->getHeight();
            }
        }
        if (null !== $thumbnail->getLarge()) {
            $this->set_thumbnail_large($thumbnail->getLarge()->getUrl());
            $this->set_thumbnail_original($thumbnail->getLarge()->getUrl());

            /* Also update media if not availabe in the ImageFacet/PhotoFacet (Business Accounts)
             * to get an idea of the dimensions
             */
            if (null === $this->get_media('width')) {
                $this->media['width'] = $thumbnail->getLarge()->getWidth();
            }
            if (null === $this->get_media('height')) {
                $this->media['height'] = $thumbnail->getLarge()->getHeight();
            }
        }
        if (null !== $thumbnail->getC1500x1500()) {
            $this->set_thumbnail_original($thumbnail->getC1500x1500()->getUrl());
        }

        // Folder images contain multiple thumbnail sets
        if ($this->is_dir()) {
            $this->set_folder_thumbnails($thumbnails);
        }
    }

    public function set_mimetype_from_extension()
    {
        if ($this->is_dir()) {
            return null;
        }

        if (empty($this->extension)) {
            return '';
        }
        $mimetype = Helpers::get_mimetype($this->get_extension());
        $this->set_mimetype($mimetype);
    }

    public function get_default_icon()
    {
        return Helpers::get_default_icon($this->get_mimetype(), $this->is_dir());
    }

    public function get_icon_large()
    {
        return str_replace('32x32', '256x256', $this->get_icon());
    }

    public function create_save_as()
    {
        switch ($this->get_extension()) {
            case 'csv':
            case 'doc':
            case 'docx':
            case 'odp':
            case 'ods':
            case 'odt':
            case 'pot':
            case 'potm':
            case 'potx':
            case 'pps':
            case 'ppsx':
            case 'ppsxm':
            case 'ppt':
            case 'pptm':
            case 'pptx':
            case 'rtf':
            case 'xls':
            case 'xlsx':
                $save_as = [
                    'PDF' => ['mimetype' => 'application/pdf', 'extension' => 'pdf', 'icon' => 'eva-download'],
                ];

                break;

            default:
                return [];
        }

        return $save_as;
    }

    public function get_date_taken()
    {
        $date_taken = $this->get_media('datetaken');

        if (empty($date_taken)) {
            $date_taken = $this->get_last_edited();
        }

        return $date_taken;
    }

    public function get_thumbnail_with_size($height, $width, $crop = 'none', $resizeable_url = false)
    {
        if (false === $resizeable_url) {
            $resizeable_url = $this->get_thumbnail_small_cropped();
        }

        if (false === $this->has_own_thumbnail()) {
            return $this->get_thumbnail_large();
        }

        $pattern = '/width=\d*/';
        $new_url = preg_replace($pattern, 'width='.$width, $resizeable_url);

        $pattern = '/height=\d*/';
        $new_url = preg_replace($pattern, 'height='.$height, $new_url);

        $new_url .= (false === strpos($new_url, 'cropmode')) ? '&cropmode=none' : '';

        return str_replace('cropmode=none', 'cropmode='.$crop, $new_url);
    }

    public function set_folder_thumbnails($folder_thumbnails)
    {
        return $this->folder_thumbnails = $folder_thumbnails;
    }

    public function get_folder_thumbnails()
    {
        return $this->folder_thumbnails;
    }

    public function get_remote_item()
    {
        return $this->remote_item;
    }

    public function is_remote_item()
    {
        return null !== $this->remote_item;
    }

    public function set_child_count($child_count)
    {
        return $this->child_count = $child_count;
    }

    public function get_child_count()
    {
        return $this->child_count;
    }
}