<?php
GFForms::include_addon_framework();

class GFShareoneDriveAddOn extends GFAddOn
{
    protected $_version = '1.0';
    protected $_min_gravityforms_version = '1.9';
    protected $_slug = 'shareonedriveaddon';
    protected $_path = 'share-one-drive/includes/integrations/init.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms Share-one-Drive Add-On';
    protected $_short_title = 'Share-one-Drive Add-On';

    public function init()
    {
        parent::init();

        if (isset($this->_min_gravityforms_version) && !$this->is_gravityforms_supported($this->_min_gravityforms_version)) {
            return;
        }

        // Add a Share-one-Drive button to the advanced to the field editor
        add_filter('gform_add_field_buttons', [$this, 'shareonedrive_field']);
        add_filter('admin_enqueue_scripts', [$this, 'shareonedrive_extra_scripts']);

        // Now we execute some javascript technicalitites for the field to load correctly
        add_action('gform_editor_js', [$this, 'gform_editor_js']);
        add_filter('gform_field_input', [$this, 'shareonedrive_input'], 10, 5);

        // Add a custom setting to the field
        add_action('gform_field_standard_settings', [$this, 'shareonedrive_settings'], 10, 2);

        // Adds title to the custom field
        add_filter('gform_field_type_title', [$this, 'shareonedrive_title'], 10, 2);

        // Filter to add the tooltip for the field
        add_filter('gform_tooltips', [$this, 'add_shareonedrive_tooltips']);

        // Save some data for this field
        add_filter('gform_field_validation', [$this, 'shareonedrive_validation'], 10, 4);

        // Display values in a proper way
        add_filter('gform_entry_field_value', [$this, 'shareonedrive_entry_field_value'], 10, 4);
        add_filter('gform_entries_field_value', [$this, 'shareonedrive_entries_field_value'], 10, 4);
        add_filter('gform_merge_tag_filter', [$this, 'shareonedrive_merge_tag_filter'], 10, 5);

        // Add support for wpDataTables <> Gravity Form integration
        if (class_exists('WPDataTable')) {
            add_action('wpdatatables_before_get_table_metadata', [$this, 'render_wpdatatables_field'], 10, 1);
        }

        // Custom Private Folder names
        add_filter('shareonedrive_private_folder_name', [$this, 'new_private_folder_name'], 10, 2);
        add_filter('shareonedrive_private_folder_name_guests', [$this, 'rename_private_folder_names_for_guests'], 10, 2);
    }

    public function shareonedrive_extra_scripts()
    {
        if (GFForms::is_gravity_page()) {
            add_thickbox();
        }

        wp_enqueue_style('WPCP-GravityForms', plugins_url('style.css', __FILE__));
    }

    public function shareonedrive_field($field_groups)
    {
        foreach ($field_groups as &$group) {
            if ('advanced_fields' == $group['name']) {
                $group['fields'][] = [
                    'class' => 'button',
                    'value' => 'Share-one-Drive',
                    'date-type' => 'shareonedrive',
                    'onclick' => "StartAddField('shareonedrive');",
                ];

                break;
            }
        }

        return $field_groups;
    }

    public function gform_editor_js()
    {
        ?>
<script type='text/javascript'>
(function($) {
    'use strict';

    /* Which settings field should be visible for our custom field*/
    fieldSettings["shareonedrive"] = ".label_setting, .description_setting, .admin_label_setting, .error_message_setting, .css_class_setting, .visibility_setting, .rules_setting, .label_placement_setting, .shareonedrive_setting, .conditional_logic_field_setting, .conditional_logic_page_setting, .conditional_logic_nextbutton_setting"; //this will show all the fields of the Paragraph Text field minus a couple that I didn't want to appear.

    /* binding to the load field settings event to initialize */
    $(document).on("gform_load_field_settings", function(event, field, form) {
        if (field["ShareoneDriveShortcode"] !== undefined && field["ShareoneDriveShortcode"] !== '') {
            jQuery("#field_shareonedrive").val(field["ShareoneDriveShortcode"]);
        } else {
            /* Default value */
            var defaultvalue = '[shareonedrive  mode="upload" upload="1" uploadrole="all" upload_auto_start="0" userfolders="auto" viewuserfoldersrole="none"]';
            jQuery("#field_shareonedrive").val(defaultvalue);
        }
    });

    /* Shortcode Generator Popup */
    $('.ShareoneDrive-GF-shortcodegenerator').click(function() {
        var shortcode = jQuery("#field_shareonedrive").val();
        shortcode = shortcode.replace('[shareonedrive ', '').replace('"]', '');
        var query = encodeURIComponent(shortcode).split('%3D%22').join('=').split('%22%20').join('&');
        tb_show("Build Shortcode for Form", ajaxurl + '?action=shareonedrive-getpopup&' + query + '&type=shortcodebuilder&asuploadbox=1&callback=wpcp_sod_gf_add_content&TB_iframe=true&height=600&width=1024');
    });


    /* Callback function to add shortcode to GF field */
    if (typeof window.wpcp_sod_gf_add_content === 'undefined') {
        window.wpcp_sod_gf_add_content = function(data) {
            $('#field_shareonedrive').val(data);
            SetFieldProperty('ShareoneDriveShortcode', data);

            tb_remove();
        }
    }
})(jQuery);

function SetDefaultValues_shareonedrive(field) {
    field.label = '<?php esc_html_e('Attach your documents', 'wpcloudplugins'); ?>';
}
</script>
<?php
    }

    public function shareonedrive_input($input, $field, $value, $lead_id, $form_id)
    {
        if ('shareonedrive' == $field->type) {
            if (!$this->is_form_editor()) {
                $return = do_shortcode($field->ShareoneDriveShortcode);
                $return .= "<input type='hidden' name='input_".$field->id."' id='input_".$form_id.'_'.$field->id."'  class='fileupload-filelist fileupload-input-filelist' value='".(isset($_REQUEST['input_'.$field->id]) ? stripslashes($_REQUEST['input_'.$field->id]) : '')."'/>";

                return $return;
            }

            return '<div class="wpcp-wpforms-placeholder"></div>';
        }

        return $input;
    }

    public function shareonedrive_settings($position, $form_id)
    {
        if (1430 == $position) {
            ?>
<li class="shareonedrive_setting field_setting">
    <label for="field_shareonedrive">Share-one-Drive Shortcode <?php echo gform_tooltip('form_field_shareonedrive'); ?></label>
    <a href="#" class='button-primary ShareoneDrive-GF-shortcodegenerator '><?php esc_html_e('Build your shortcode', 'wpcloudplugins'); ?></a>
    <textarea id="field_shareonedrive" class="fieldwidth-3 fieldheight-2" onchange="SetFieldProperty('ShareoneDriveShortcode', this.value)"></textarea>
    <br /><small>Missing a Share-one-Drive Gravity Form feature? Please let me <a href="https://florisdeleeuwnl.zendesk.com/hc/en-us/requests/new" target="_blank">know</a>!</small>
</li>
<?php
        }
    }

    public function shareonedrive_title($title, $field_type)
    {
        if ('shareonedrive' === $field_type) {
            return 'Share-one-Drive'.esc_html__('Upload', 'wpcloudplugins');
        }

        return $title;
    }

    public function add_shareonedrive_tooltips($tooltips)
    {
        $tooltips['form_field_shareonedrive'] = '<h6>Share-one-Drive Shortcode</h6>'.esc_html__('Build your shortcode here', 'wpcloudplugins');

        return $tooltips;
    }

    public function shareonedrive_validation($result, $value, $form, $field)
    {
        if ('shareonedrive' !== $field->type) {
            return $result;
        }

        if (false === $field->isRequired) {
            return $result;
        }

        // Get information uploaded files from hidden input
        $filesinput = rgpost('input_'.$field->id);
        $uploadedfiles = json_decode($filesinput);

        if (empty($uploadedfiles)) {
            $result['is_valid'] = false;
            $result['message'] = esc_html__('This field is required. Please upload your files.', 'gravityforms');
        } else {
            $result['is_valid'] = true;
            $result['message'] = '';
        }

        return $result;
    }

    public function shareonedrive_entry_field_value($value, $field, $lead, $form)
    {
        if ('shareonedrive' !== $field->type) {
            return $value;
        }

        return $this->renderUploadedFiles(html_entity_decode($value));
    }

    public function render_wpdatatables_field($tableId)
    {
        add_filter('gform_get_input_value', [$this, 'shareonedrive_get_input_value'], 10, 4);
    }

    public function shareonedrive_get_input_value($value, $entry, $field, $input_id)
    {
        if ('shareonedrive' !== $field->type) {
            return $value;
        }

        return $this->renderUploadedFiles(html_entity_decode($value));
    }

    public function shareonedrive_entries_field_value($value, $form_id, $field_id, $entry)
    {
        $form = GFFormsModel::get_form_meta($form_id);

        if (is_array($form['fields'])) {
            foreach ($form['fields'] as $field) {
                if ('shareonedrive' === $field->type && $field_id == $field->id) {
                    return $this->renderUploadedFiles(html_entity_decode($value));
                }
            }
        }

        return $value;
    }

    public function shareonedrive_set_export_values($value, $form_id, $field_id, $lead)
    {
        $form = GFFormsModel::get_form_meta($form_id);

        if (is_array($form['fields'])) {
            foreach ($form['fields'] as $field) {
                if ('shareonedrive' === $field->type && $field_id == $field->id) {
                    return $this->renderUploadedFiles(html_entity_decode($value), false);
                }
            }
        }

        return $value;
    }

    public function shareonedrive_merge_tag_filter($value, $merge_tag, $modifier, $field, $rawvalue)
    {
        if ('shareonedrive' == $field->type) {
            return $this->renderUploadedFiles(html_entity_decode($value));
        }

        return $value;
    }

    public function renderUploadedFiles($data, $ashtml = true)
    {
        return apply_filters('shareonedrive_render_formfield_data', $data, $ashtml, $this);
    }

    /**
     * Function to change the Private Folder Name.
     *
     * @param string                           $private_folder_name
     * @param \TheLion\ShareoneDrive\Processor $processor
     *
     * @return string
     */
    public function new_private_folder_name($private_folder_name, $processor)
    {
        if (!isset($_COOKIE['WPCP-FORM-NAME-'.$processor->get_listtoken()])) {
            return $private_folder_name;
        }

        if ('gf_upload_box' !== \TheLion\ShareoneDrive\Processor::instance()->get_shortcode_option('class')) {
            return $private_folder_name;
        }

        $raw_name = sanitize_text_field($_COOKIE['WPCP-FORM-NAME-'.$processor->get_listtoken()]);
        $name = str_replace(['|', '/'], ' ', $raw_name);
        $filtered_name = \TheLion\ShareoneDrive\Helpers::filter_filename(stripslashes($name), false);

        return trim($filtered_name);
    }

    /**
     * Function to change the Private Folder Name for Guest users.
     *
     * @param string                           $private_folder_name_guest
     * @param \TheLion\ShareoneDrive\Processor $processor
     *
     * @return string
     */
    public function rename_private_folder_names_for_guests($private_folder_name_guest, $processor)
    {
        if ('gf_upload_box' !== \TheLion\ShareoneDrive\Processor::instance()->get_shortcode_option('class')) {
            return $private_folder_name_guest;
        }

        $prefix = \TheLion\ShareoneDrive\Processor::instance()->get_setting('userfolder_name_guest_prefix');

        return str_replace($prefix, '', $private_folder_name_guest);
    }
}

$GFShareoneDriveAddOn = new GFShareoneDriveAddOn();
// This filter isn't fired if inside class
add_filter('gform_export_field_value', [$GFShareoneDriveAddOn, 'shareonedrive_set_export_values'], 10, 4);