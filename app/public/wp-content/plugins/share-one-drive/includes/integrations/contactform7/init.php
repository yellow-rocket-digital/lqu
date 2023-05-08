<?php

namespace TheLion\ShareoneDrive\Integrations;

use TheLion\ShareoneDrive\Processor;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class ContactForm
{
    public function __construct()
    {
        if (!defined('WPCF7_VERSION') || false === version_compare(WPCF7_VERSION, '5.0', '>=')) {
            return;
        }

        add_action('wpcf7_init', [$this, 'add_shortcode_handler']);
        add_action('wpcf7_admin_init', [$this, 'add_tag_generator'], 60);

        add_action('admin_enqueue_scripts', [$this, 'add_admin_scripts']);
        add_action('wpcf7_admin_footer', [$this, 'load_admin_scripts']);

        add_filter('wpcf7_mail_tag_replaced_shareonedrive*', [$this, 'set_email_tag'], 999, 4);
        add_filter('wpcf7_mail_tag_replaced_shareonedrive', [$this, 'set_email_tag'], 999, 4);

        add_filter('shareonedrive_private_folder_name', [$this, 'new_private_folder_name'], 10, 2);
        add_filter('shareonedrive_private_folder_name_guests', [$this, 'rename_private_folder_names_for_guests'], 10, 2);
    }

    public function add_admin_scripts($hook_suffix)
    {
        if (false === strpos($hook_suffix, 'wpcf7')) {
            return;
        }

        \TheLion\ShareoneDrive\Core::instance()->load_scripts();
        \TheLion\ShareoneDrive\Core::instance()->load_styles();

        wp_enqueue_script('WPCloudplugin.Libraries');
        wp_enqueue_script('ShareoneDrive.ShortcodeBuilder');
        wp_enqueue_style('ShareoneDrive');
    }

    public function load_admin_scripts()
    {
        wp_register_script('ShareoneDrive.ContactForm7', plugins_url('ContactForm7.js', __FILE__), ['jquery'], SHAREONEDRIVE_VERSION, true);
        wp_enqueue_script('ShareoneDrive.ContactForm7');
    }

    public function add_tag_generator()
    {
        if (class_exists('WPCF7_TagGenerator')) {
            $tag_generator = \WPCF7_TagGenerator::get_instance();
            $tag_generator->add('shareonedrive', 'Share-one-Drive', [$this, 'tag_generator_body']);
        }
    }

    public function tag_generator_body($contact_form, $args = '')
    {
        $args = wp_parse_args($args, []);
        $type = 'shareonedrive';

        $description = esc_html__('Generate a form-tag for this upload field.', 'wpcloudplugins'); ?>
<div class="control-box">
    <fieldset>
        <legend><?php echo sprintf(esc_html($description)); ?></legend>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php echo esc_html(esc_html__('Field type', 'contact-form-7')); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php echo esc_html(esc_html__('Field type', 'contact-form-7')); ?></legend>
                            <label><input type="checkbox" name="required" /> <?php echo esc_html(esc_html__('Required field', 'contact-form-7')); ?></label>
                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="<?php echo esc_attr($args['content'].'-name'); ?>"><?php echo esc_html(esc_html__('Name', 'contact-form-7')); ?></label></th>
                    <td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr($args['content'].'-name'); ?>" /></td>
                </tr>

                <tr>
                    <th scope="row"><label for="<?php echo esc_attr($args['content'].'-shortcode'); ?>"><?php echo esc_html(esc_html__('Shortcode', 'wpcloudplugins')); ?></label></th>
                    <td>
                        <input type="hidden" name="shortcode" class="shareonedrive-shortcode-value large-text option" id="<?php echo esc_attr($args['content'].'-shortcode'); ?>" />
                        <textarea id="shareonedrive-shortcode-decoded-value" rows="6" style="margin-bottom:15px;display:none;width: 400px;word-wrap: break-word;"></textarea>
                        <input type="button" class="button button-primary ShareoneDrive-CF-shortcodegenerator " value="<?php echo esc_attr(esc_html__('Build your shortcode here', 'wpcloudplugins')); ?>" />
                        <iframe id="shareonedrive-shortcode-iframe" src="about:blank" data-src='<?php echo SHAREONEDRIVE_ADMIN_URL; ?>?action=shareonedrive-getpopup&type=shortcodebuilder&asuploadbox=1&callback=wpcp_sod_cf7_add_content' width='100%' height='500' tabindex='-1' frameborder='0' style="display:none"></iframe>
                        <p class="share-one-drive-upload-folder description">You can use the available input fields in your form to name the upload folder based on user input. To do so, just add the <code>wpcp-use-input-{***}</code> to the Class attribute of your input field (i.e. <code>[text* your-name class:shareonedrive_private_folder_name]</code>).</p>
                    </td>
                </tr>

            </tbody>
        </table>
    </fieldset>
</div>

<div class="insert-box">
    <input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

    <div class="submitbox">
        <input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr(esc_html__('Insert Tag', 'contact-form-7')); ?>" />
    </div>

    <br class="clear" />

    <p class="description mail-tag"><label for="<?php echo esc_attr($args['content'].'-mailtag'); ?>"><?php echo sprintf(esc_html('To list the uploads in your email, insert the mail-tag (%s) in the Mail tab.'), '<strong><span class="mail-tag"></span></strong>'); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr($args['content'].'-mailtag'); ?>" /></label></p>
</div>
<?php
    }

    /**
     * Add shortcode handler to CF7.
     */
    public function add_shortcode_handler()
    {
        if (function_exists('wpcf7_add_form_tag')) {
            wpcf7_add_form_tag(
                ['shareonedrive', 'shareonedrive*'],
                [$this, 'shortcode_handler'],
                true
            );
        }
    }

    public function shortcode_handler($tag)
    {
        $tag = new \WPCF7_FormTag($tag);

        if (empty($tag->name)) {
            return '';
        }

        $required = ('*' == substr($tag->type, -1));
        if ($required) {
            add_filter('shareonedrive_shortcode_set_options', [$this, 'set_required_shortcode'], 10, 3);
        }

        // Shortcode
        $shortcode = base64_decode(urldecode($tag->get_option('shortcode', '', true)));
        $return = apply_filters('shareonedrive-wpcf7-render-shortcode', do_shortcode($shortcode));
        $return .= "<input type='hidden' name='".$tag->name."' class='fileupload-filelist fileupload-input-filelist'/>";

        wp_enqueue_script('ShareoneDrive.ContactForm7');

        return $return;
    }

    public function set_required_shortcode($options, $processor, $atts)
    {
        $options['class'] .= ' wpcf7-validates-as-required';

        return $options;
    }

    public function set_email_tag($output, $submission, $ashtml, $mail_tag)
    {
        $filelist = stripslashes($submission);

        return $this->render_uploaded_files_list($filelist, $ashtml);
    }

    public function render_uploaded_files_list($data, $ashtml = true)
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

        if ('cf7_upload_box' !== Processor::instance()->get_shortcode_option('class')) {
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
        if ('cf7_upload_box' !== Processor::instance()->get_shortcode_option('class')) {
            return $private_folder_name_guest;
        }

        $prefix = Processor::instance()->get_setting('userfolder_name_guest_prefix');

        return str_replace($prefix, '', $private_folder_name_guest);
    }
}
