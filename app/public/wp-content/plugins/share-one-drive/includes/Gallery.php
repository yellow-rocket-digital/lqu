<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\ShareoneDrive;

class Gallery
{
    private $_folder;
    private $_items;
    private $_search = false;
    private $_parentfolders = [];

    public function getImagesList()
    {
        $this->_folder = Client::instance()->get_folder();

        if (false !== $this->_folder) {
            $this->_items = $this->createItems();
            $this->renderImagesList();
        }
    }

    public function searchImageFiles()
    {
        if ('POST' !== $_SERVER['REQUEST_METHOD'] || !User::can_search()) {
            exit(-1);
        }

        $this->_search = true;
        $_REQUEST['query'] = wp_kses(stripslashes($_REQUEST['query']), 'strip');
        $this->_folder = Client::instance()->search_by_name($_REQUEST['query']);

        if (!empty($this->_folder['contents'])) {
            $this->_items = $this->createItems();

            $this->renderImagesList();
        }
    }

    public function setFolder($folder)
    {
        $this->_folder = $folder;
    }

    public function setParentFolder()
    {
        $this->_parentfolders = [];

        if (true === $this->_search) {
            return;
        }

        $currentfolder = $this->_folder['folder']->get_entry()->get_id();
        if ($currentfolder !== Processor::instance()->get_root_folder()) {
            // Get parent folder from known folder path
            $cacheparentfolder = Client::instance()->get_entry(Processor::instance()->get_root_folder());
            $folder_path = Processor::instance()->get_folder_path();
            $parentid = end($folder_path);
            if (false !== $parentid) {
                $cacheparentfolder = Client::instance()->get_entry($parentid);
            }

            /* Check if parent folder indeed is direct parent of entry
             * If not, return all known parents */
            $parentfolders = [];
            if (false !== $cacheparentfolder && $cacheparentfolder->has_children() && array_key_exists($currentfolder, $cacheparentfolder->get_children())) {
                $parentfolders[$cacheparentfolder->get_id()] = $cacheparentfolder->get_entry();
            } else {
                if ($this->_folder['folder']->has_parents()) {
                    foreach ($this->_folder['folder']->get_parents() as $parent) {
                        $parentfolders[$parent->get_id()] = $parent->get_entry();
                    }
                }
            }
            $this->_parentfolders = $parentfolders;
        }
    }

    public function renderImagesList()
    {
        // Create HTML Filelist
        $imageslist_html = '';

        $filescount = 0;
        $folderscount = 0;

        if (count($this->_items) > 0) {
            // Limit the number of files if needed
            if ('-1' !== Processor::instance()->get_shortcode_option('max_files')) {
                $this->_items = array_slice($this->_items, 0, Processor::instance()->get_shortcode_option('max_files'));
            }

            $imageslist_html = "<div class='images image-collage'>";
            foreach ($this->_items as $item) {
                // Render folder div
                if ($item->is_dir()) {
                    $imageslist_html .= $this->renderDir($item);

                    $isparent = (isset($this->_folder['folder'])) ? $this->_folder['folder']->is_in_folder($item->get_id()) : false;
                    if (!$isparent) {
                        ++$folderscount;
                    }
                }
            }
        }

        $imageslist_html .= $this->renderNewFolder();

        if (count($this->_items) > 0) {
            $i = 0;
            foreach ($this->_items as $item) {
                // Render file div
                if (!$item->is_dir()) {
                    $hidden = (('0' !== Processor::instance()->get_shortcode_option('maximages')) && ($i >= Processor::instance()->get_shortcode_option('maximages')));
                    $imageslist_html .= $this->renderFile($item, $hidden);
                    ++$i;
                    ++$filescount;
                }
            }

            $imageslist_html .= '</div>';
        }

        // Create HTML Filelist title
        $file_path = '<ol class="wpcp-breadcrumb">';
        $userfolder = UserFolders::instance()->get_auto_linked_folder_for_user();
        $folder_path = Processor::instance()->get_folder_path();
        $root_folder_id = Processor::instance()->get_root_folder();
        $current_id = $this->_folder['folder']->get_entry()->get_id();
        $drive_id = $this->_folder['folder']->get_drive_id();

        $root_folder = Client::instance()->get_folder($root_folder_id);
        $root_text = '1' === Processor::instance()->get_shortcode_option('use_custom_roottext') ? Processor::instance()->get_shortcode_option('root_text') : $root_folder['folder']->get_name();

        if ($root_folder_id === $current_id) {
            $file_path .= "<li class='first-breadcrumb'><a href='#{$current_id}' class='folder current_folder' data-id='".$current_id."' data-drive-id='{$drive_id}'>{$root_text}</a></li>";
        } elseif (false === $this->_search || 'parent' === Processor::instance()->get_shortcode_option('searchfrom')) {
            foreach ($folder_path as $parent_id) {
                if ($parent_id === $root_folder_id) {
                    $file_path .= "<li class='first-breadcrumb'><a href='#{$parent_id}' class='folder' data-id='".$parent_id."' data-drive-id=''>{$root_text}</a></li>";
                } else {
                    $parent_folder = Client::instance()->get_folder($parent_id);
                    $file_path .= "<li><a href='#{$parent_id}' class='folder' data-id='".$parent_id."' data-drive-id='".$parent_folder['folder']->get_drive_id()."'>".$parent_folder['folder']->get_name().'</a></li>';
                }
            }

            $current_folder_name = apply_filters('shareonedrive_gallery_entry_text', $this->_folder['folder']->get_entry()->get_name(), $this->_folder['folder']->get_entry(), $this);
            $file_path .= "<li><a href='#{$current_id}' class='folder current_folder' data-id='".$current_id."' data-drive-id='".$this->_folder['folder']->get_drive_id()."'>".$current_folder_name.'</a></li>';
        }

        if (true === $this->_search) {
            $file_path .= "<li><a href='javascript:void(0)' class='folder'>".sprintf(esc_html__('Results for %s', 'wpcloudplugins'), "'".htmlentities($_REQUEST['query'])."'").'</a></li>';
        }

        $file_path .= '</ol>';

        // lastFolder contains current folder path of the user
        $folder_path = Processor::instance()->get_folder_path();
        if (true !== $this->_search && (end($folder_path) !== $this->_folder['folder']->get_entry()->get_id())) {
            $folder_path[] = $this->_folder['folder']->get_entry()->get_id();
        }

        if (true === $this->_search) {
            $lastFolder = Processor::instance()->get_last_folder();
        } else {
            $lastFolder = $this->_folder['folder']->get_entry()->get_id();
        }

        $response = json_encode([
            'folderPath' => base64_encode(json_encode($folder_path)),
            'driveId' => $this->_folder['folder']->get_drive_id(),
            'accountId' => $this->_folder['folder']->get_account_id(),
            'virtual' => false === $this->_search && in_array($this->_folder['folder']->get_virtual_folder(), ['drives', 'sites', 'site']),
            'lastFolder' => $lastFolder,
            'breadcrumb' => $file_path,
            'html' => $imageslist_html,
            'folderscount' => $folderscount,
            'filescount' => $filescount,
            'hasChanges' => defined('HAS_CHANGES'),
        ]);

        if (false === defined('HAS_CHANGES')) {
            $cached_request = new CacheRequest();
            $cached_request->add_cached_response($response);
        }

        echo $response;

        exit;
    }

    public function renderDir($item)
    {
        $return = '';

        $target_height = round(Processor::instance()->get_shortcode_option('targetheight'));
        $target_width = round($target_height * (4 / 3));

        $classmoveable = (User::can_move_folders()) ? 'moveable' : '';
        $isparent = (isset($this->_folder['folder'])) ? $this->_folder['folder']->is_in_folder($item->get_id()) : false;
        $has_thumbnails = (count($item->get_folder_thumbnails()) > 0);

        if ($isparent) {
            $return .= "<div class='image-container image-folder pf' data-id='".$item->get_id()."' data-drive-id='".$item->get_drive_id()."' data-name='".$item->get_basename()."'>";
        } else {
            $loadthumbs = $has_thumbnails ? '' : 'loadthumbs';
            $return .= "<div class='image-container image-folder entry {$classmoveable} {$loadthumbs}' data-id='".$item->get_id()."' data-name='".$item->get_basename()."'>";
        }
        $return .= "<a href='javascript:void(0);' title='".$item->get_name()."'>";

        $return .= "<div class='preloading'></div>";

        $return .= "<img class='image-folder-img' src='".SHAREONEDRIVE_ROOTPATH."/css/images/transparant.png' width='{$target_width}' height='{$target_height}' style='width:{$target_width}px !important;height:{$target_height}px !important; '/>";

        $maximages = 3;
        $iimages = 0;

        foreach ($item->get_folder_thumbnails() as $folder_thumbnails_set) {
            // Only use $maximages for the folder div
            if ($iimages >= $maximages) {
                break;
            }
            $thumbnail_url = (false !== $folder_thumbnails_set->getC48x48()) ? $folder_thumbnails_set->getC48x48()->getUrl() : $folder_thumbnails_set->getMedium()->getUrl();
            $thumbnail = $item->get_thumbnail_with_size($target_height, $target_width, 'center', $thumbnail_url);

            $return .= "<div class='folder-thumb thumb{$iimages}' style='width:".$target_width.'px;height:'.$target_height.'px;background-image: url('.$thumbnail.")'></div>";

            ++$iimages;
        }

        $text = apply_filters('shareonedrive_gallery_entry_text', $item->get_name(), $item, $this);
        $return .= "<div class='folder-text'><span><i class='eva eva-folder'></i>&nbsp;&nbsp;".($isparent ? '<strong>'.esc_html__('Previous folder', 'wpcloudplugins').' ('.$text.')</strong>' : $text).'</span></div>';

        $return .= '</a>';

        if (!$isparent) {
            $return .= "<div class='entry-info'>";
            $return .= $this->renderDescription($item);
            $return .= $this->renderButtons($item);
            $return .= $this->renderActionMenu($item);

            if (User::can_download_zip() || User::can_delete_folders() || User::can_move_folders() || User::can_copy_folders()) {
                $return .= "<div class='entry_checkbox entry-info-button '><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-info-".Processor::instance()->get_listtoken()."-{$item->get_id()}'/><label for='checkbox-info-".Processor::instance()->get_listtoken()."-{$item->get_id()}'></label></div>";
            }

            $return .= '</div>';
        }

        $return .= "<div class='entry-top-actions'>";
        $return .= $this->renderDescription($item);
        $return .= $this->renderButtons($item);
        $return .= $this->renderActionMenu($item);

        if (User::can_download_zip() || User::can_delete_folders() || User::can_move_folders() || User::can_copy_folders()) {
            $return .= "<div class='entry_checkbox entry-info-button '><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-".Processor::instance()->get_listtoken()."-{$item->get_id()}'/><label for='checkbox-".Processor::instance()->get_listtoken()."-{$item->get_id()}'></label></div>";
        }

        $return .= '</div>';

        $return .= "</div>\n";

        return $return;
    }

    public function renderFile($item, $hidden = false)
    {
        $class = ($hidden) ? 'hidden' : '';

        $target_height = Processor::instance()->get_shortcode_option('targetheight'); // Max thumbnail size

        $classmoveable = (User::can_move_files()) ? 'moveable' : '';

        $return = "<div class='image-container {$class} entry {$classmoveable}' data-id='".$item->get_id()."' data-name='".$item->get_name()."'>";

        $thumbnail_url = $item->get_thumbnail_medium();
        $link = SHAREONEDRIVE_ADMIN_URL.'?action=shareonedrive-download&id='.urlencode($item->get_id()).'&dl=1&account_id='.$this->_folder['folder']->get_account_id().'&drive_id='.$this->_folder['folder']->get_drive_id().'&listtoken='.Processor::instance()->get_listtoken();

        $lightbox_type = 'image';
        $lightbox_data = 'data-options="thumbnail: \''.$thumbnail_url.'\'"';
        if (in_array($item->get_extension(), ['mp4', 'm4v', 'ogg', 'ogv', 'webmv'])) {
            $link = \str_replace('download', 'stream', $link);
            $lightbox_data = 'data-options="thumbnail: \''.$thumbnail_url.'\', mousewheel:false, html5video:{h264:\''.$link.'\', poster: \''.$item->get_thumbnail_large().'\', preload:\'auto\'}, videoType:\''.$item->get_mimetype().'\'"';
            $lightbox_type = 'video';
        }

        if ('onedrivethumbnail' === Processor::instance()->get_setting('loadimages') || false === User::can_download()) {
            $link = $item->get_thumbnail_original();
        }

        $description = htmlentities($item->get_description(), ENT_QUOTES | ENT_HTML401);
        $data_description = ((!empty($item->description)) ? "data-caption='{$description}'" : '');

        $return .= "<a href='".$link."' title='".htmlspecialchars($item->get_basename(), ENT_COMPAT | ENT_HTML401 | ENT_QUOTES)."'  class='ilightbox-group' data-type='{$lightbox_type}' {$lightbox_data} rel='ilightbox[".Processor::instance()->get_listtoken()."]' data-entry-id='{$item->get_id()}' {$data_description}>";

        $return .= "<div class='preloading'></div>";

        $width = $height = round($target_height);
        if ($item->get_media('width') && $item->get_media('height') !== $item->get_media('width')) {
            $width = round(($target_height / $item->get_media('height')) * $item->get_media('width'));
            $crop = 'none';
        } else {
            $crop = 'center';
        }

        $return .= "<img referrerPolicy='no-referrer' class='preloading {$class}'  src='".SHAREONEDRIVE_ROOTPATH."/css/images/transparant.png' data-src='".$item->get_thumbnail_with_size($height, $width, $crop)."' data-src-retina='".$item->get_thumbnail_with_size($height * 2, $width * 2, $crop)."' width='{$width}' height='{$height}' style='width:{$width}px !important;height:{$height}px !important;'/>";

        if ('1' === Processor::instance()->get_shortcode_option('show_filenames')) {
            $text = apply_filters('shareonedrive_gallery_entry_text', $item->get_basename(), $item, $this);
            $return .= "<div class='entry-text'><span>".$text.'</span></div>';
        }

        $return .= '</a>';

        if (false === empty($item->description)) {
            $return .= '<div class="entry-inline-description '.('1' === Processor::instance()->get_shortcode_option('show_descriptions_on_top') ? ' description-visible ' : '').('1' === Processor::instance()->get_shortcode_option('show_filenames') ? ' description-above-name ' : '').'"><span>'.nl2br($item->get_description()).'</span></div>';
        }

        $return .= "<div class='entry-info' data-id='{$item->get_id()}'>";
        $return .= "<div class='entry-info-name'>";
        $caption = apply_filters('shareonedrive_gallery_lightbox_caption', $item->get_basename(), $item, $this);
        $return .= '<span>'.$caption.'</span></div>';
        $return .= $this->renderButtons($item);
        $return .= "</div>\n";

        $return .= "<div class='entry-top-actions'>";
        if ('1' === Processor::instance()->get_shortcode_option('show_filenames')) {
            $return .= $this->renderDescription($item);
        }

        $return .= $this->renderButtons($item);
        $return .= $this->renderActionMenu($item);

        if (User::can_download_zip() || User::can_delete_files() || User::can_move_files() || User::can_copy_files()) {
            $return .= "<div class='entry_checkbox entry-info-button '><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-".Processor::instance()->get_listtoken()."-{$item->get_id()}'/><label for='checkbox-".Processor::instance()->get_listtoken()."-{$item->get_id()}'></label></div>";
        }

        $return .= '</div>';

        $return .= "</div>\n";

        return $return;
    }

    public function renderDescription($item)
    {
        $html = '';

        if ('personal' !== App::get_current_account()->get_type()) { // Only avialable for OneDrive Personal accounts
            return $html;
        }

        if (empty($item->description)) {
            return $html;
        }

        $metadata = [
            'modified' => "<i class='eva eva-clock-outline'></i> ".$item->get_last_edited_str(false),
            'size' => ($item->get_size() > 0) ? Helpers::bytes_to_size_1024($item->get_size()) : '',
        ];

        $html .= "<div class='entry-info-button entry-description-button'><i class='eva eva-info-outline eva-lg'></i>\n";
        $html .= "<div class='tippy-content-holder'>";
        $html .= "<div class='description-textbox'>";
        $html .= "<div class='description-file-name'>".htmlspecialchars($item->get_name(), ENT_COMPAT | ENT_HTML401 | ENT_QUOTES, 'UTF-8').'</div>';
        $html .= "<div class='description-text'>".nl2br($item->get_description()).'</div>';
        $html .= "<div class='description-file-info'>".implode(' &bull; ', array_filter($metadata)).'</div>';

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function renderButtons($item)
    {
        $html = '';

        if (User::can_share()) {
            $html .= "<div class='entry-info-button entry_action_shortlink' title='".esc_html__('Share', 'wpcloudplugins')."' tabindex='0'><i class='eva eva-share-outline eva-lg'></i>\n";
            $html .= '</div>';
        }

        if (User::can_deeplink()) {
            $html .= "<div class='entry-info-button entry_action_deeplink' title='".esc_html__('Direct link', 'wpcloudplugins')."' tabindex='0'><i class='eva eva-link eva-lg'></i>\n";
            $html .= '</div>';
        }

        if (User::can_download() && $item->is_file()) {
            $html .= "<div class='entry-info-button entry_action_download' title='".esc_html__('Download', 'wpcloudplugins')."' tabindex='0'><a href='".SHAREONEDRIVE_ADMIN_URL.'?action=shareonedrive-download&account_id='.App::get_current_account()->get_id().'&drive_id='.$this->_folder['folder']->get_drive_id().'&id='.$item->get_id().'&dl=1&listtoken='.Processor::instance()->get_listtoken()."' download='".$item->get_name()."' class='entry_action_download' title='".esc_html__('Download', 'wpcloudplugins')."'><i class='eva eva-download eva-lg'></i></a>\n";
            $html .= '</div>';
        }

        return $html;
    }

    public function renderActionMenu($item)
    {
        $html = '';

        if ($item->is_virtual_folder()) {
            return $html;
        }

        $usercanread = User::can_download();
        $usercanshare = User::can_share();
        $usercandeeplink = User::can_deeplink();
        $usercanrename = ($item->is_dir()) ? User::can_rename_folders() : User::can_rename_files();
        $usercanmove = ($item->is_dir()) ? User::can_move_folders() : User::can_move_files();
        $usercandelete = ($item->is_dir()) ? User::can_delete_folders() : User::can_delete_files();
        $usercaneditdescription = User::can_edit_description();
        $usercancopy = (($item->is_dir()) ? User::can_copy_folders() : User::can_copy_files());

        // Download
        if ($usercanread && $item->is_dir() && '1' === Processor::instance()->get_shortcode_option('can_download_zip')) {
            $html .= "<li><a class='entry_action_download' download='".$item->get_name()."' data-name='".$item->get_name()."' title='".esc_html__('Download', 'wpcloudplugins')."'><i class='eva eva-download eva-lg'></i>&nbsp;".esc_html__('Download', 'wpcloudplugins').'</a></li>';

            if ($usercaneditdescription || $usercanrename || $usercanmove) {
                $html .= "<li class='list-separator'></li>";
            }
        }

        // Descriptions
        if ($usercaneditdescription) {
            if (empty($item->description)) {
                $html .= "<li><a class='entry_action_description' title='".esc_html__('Add description', 'wpcloudplugins')."'><i class='eva eva-message-square-outline eva-lg'></i>&nbsp;".esc_html__('Add description', 'wpcloudplugins').'</a></li>';
            } else {
                $html .= "<li><a class='entry_action_description' title='".esc_html__('Edit description', 'wpcloudplugins')."'><i class='eva eva-message-square-outline eva-lg'></i>&nbsp;".esc_html__('Edit description', 'wpcloudplugins').'</a></li>';
            }
        }

        // Rename
        if ($usercanrename) {
            $html .= "<li><a class='entry_action_rename' title='".esc_html__('Rename', 'wpcloudplugins')."'><i class='eva eva-edit-2-outline eva-lg'></i>&nbsp;".esc_html__('Rename', 'wpcloudplugins').'</a></li>';
        }

        // Move
        if ($usercanmove) {
            $html .= "<li><a class='entry_action_move' title='".esc_html__('Move to', 'wpcloudplugins')."'><i class='eva eva-corner-down-right eva-lg'></i>&nbsp;".esc_html__('Move to', 'wpcloudplugins').'</a></li>';
        }

        // Copy
        if ($usercancopy) {
            $html .= "<li><a class='entry_action_copy' title='".esc_html__('Make a copy', 'wpcloudplugins')."'><i class='eva eva-copy-outline eva-lg'></i>&nbsp;".esc_html__('Make a copy', 'wpcloudplugins').'</a></li>';
        }

        // Delete
        if ($usercandelete) {
            $html .= "<li class='list-separator'></li>";
            $html .= "<li><a class='entry_action_delete' title='".esc_html__('Delete', 'wpcloudplugins')."'><i class='eva eva-trash-2-outline eva-lg'></i>&nbsp;".esc_html__('Delete', 'wpcloudplugins').'</a></li>';
        }

        $html = apply_filters('shareonedrive_set_action_menu', $html, $item);

        if ('' !== $html) {
            return "<div class='entry-info-button entry-action-menu-button' title='".esc_html__('More actions', 'wpcloudplugins')."' tabindex='0'><i class='eva eva-more-vertical-outline'></i><div id='menu-".$item->get_id()."' class='entry-action-menu-button-content tippy-content-holder'><ul data-id='".$item->get_id()."' data-name='".$item->get_basename()."'>".$html."</ul></div></div>\n";
        }

        return $html;
    }

    public function renderNewFolder()
    {
        $html = '';

        if (
            false === User::can_add_folders()
            || true === $this->_search
            || '1' === Processor::instance()->get_shortcode_option('show_breadcrumb')
        ) {
            return $html;
        }

        $height = Processor::instance()->get_shortcode_option('targetheight');
        $html .= "<div class='image-container image-folder image-add-folder grey newfolder'>";
        $html .= "<a title='".esc_html__('Add folder', 'wpcloudplugins')."'>";
        $html .= "<img class='preloading' src='".SHAREONEDRIVE_ROOTPATH."/css/images/transparant.png' data-src='".plugins_url('css/images/gallery-add-folder.png', dirname(__FILE__))."' data-src-retina='".plugins_url('css/images/gallery-add-folder.png', dirname(__FILE__))."' width='{$height}' height='{$height}' style='width:".$height.'px;height:'.$height."px;'/>";
        $html .= "<div class='folder-text'><span><i class='eva eva-folder-add-outline eva-lg'></i>&nbsp;&nbsp;".esc_html__('Add folder', 'wpcloudplugins').'</span></div>';
        $html .= '</a>';
        $html .= "</div>\n";

        return $html;
    }

    public function createItems()
    {
        $imagearray = [];

        $this->setParentFolder();

        // Add folders and files to filelist
        if (count($this->_folder['contents']) > 0) {
            foreach ($this->_folder['contents'] as $node) {
                $child = $node->get_entry();

                // Check if entry is allowed
                if (!Processor::instance()->_is_entry_authorized($node)) {
                    continue;
                }

                // Check if entry has thumbnail
                if (!$child->has_own_thumbnail() && $child->is_file()) {
                    continue;
                }

                // Hide Folder thumbs
                if ('.thumb' === $child->get_basename()) {
                    continue;
                }

                $imagearray[] = $child;
            }

            $imagearray = Processor::instance()->sort_filelist($imagearray);
        }

        // Add 'back to Previous folder' if needed
        if (isset($this->_folder['folder'])) {
            $folder = $this->_folder['folder']->get_entry();

            $add_parent_folder_item = true;

            if ($this->_search || $folder->get_id() === Processor::instance()->get_root_folder()) {
                $add_parent_folder_item = false;
            } elseif ('1' === Processor::instance()->get_shortcode_option('show_breadcrumb')) {
                $add_parent_folder_item = false;
            }

            if ($add_parent_folder_item) {
                foreach ($this->_parentfolders as $parentfolder) {
                    array_unshift($items, $parentfolder);
                }
            }
        }

        return $imagearray;
    }
}
