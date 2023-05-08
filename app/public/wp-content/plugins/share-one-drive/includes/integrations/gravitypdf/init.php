<?php

namespace TheLion\ShareoneDrive\Integrations;

use TheLion\ShareoneDrive\Accounts;
use TheLion\ShareoneDrive\API;
use TheLion\ShareoneDrive\App;
use TheLion\ShareoneDrive\Client;
use TheLion\ShareoneDrive\Processor;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class GravityPDF
{
    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        if (false === get_option('gfpdf_current_version') && false === class_exists('GFPDF_Core')) {
            return;
        }

        add_action('gfpdf_post_save_pdf', [$this, 'shareonedrive_post_save_pdf'], 10, 5);
        add_filter('gfpdf_form_settings_advanced', [$this, 'shareonedrive_add_pdf_setting'], 10, 1);
    }

    /*
         * GravityPDF
         * Basic configuration in Form Settings -> PDF:
         *
         * Always Save PDF = YES
         * [ONEDRIVE] Export PDF = YES
         * [ONEDRIVE] ID = ID where the PDFs need to be stored
         */

    public function shareonedrive_add_pdf_setting($fields)
    {
        $fields['shareonedrive_save_to_onedrive'] = [
            'id' => 'shareonedrive_save_to_onedrive',
            'name' => '[ONEDRIVE] Export PDF',
            'desc' => 'Save the created PDF to OneDrive',
            'type' => 'radio',
            'options' => [
                'Yes' => esc_html__('Yes'),
                'No' => esc_html__('No'),
            ],
            'std' => esc_html__('No'),
        ];

        $main_account = Accounts::instance()->get_primary_account();

        $account_id = '';
        if (!empty($main_account)) {
            $account_id = $main_account->get_id();
        }

        $fields['shareonedrive_save_to_account_id'] = [
            'id' => 'shareonedrive_save_to_account_id',
            'name' => '[ONEDRIVE] Account ID',
            'desc' => 'Account ID where the PDFs need to be stored. E.g. <code>'.$account_id.'</code>. Or use <code>%upload_account_id%</code> for the Account ID for the upload location of the plugin Upload Box field.',
            'type' => 'text',
            'std' => $account_id,
        ];

        $drive_id = App::get_primary_drive_id();

        $fields['shareonedrive_save_to_drive_id'] = [
            'id' => 'shareonedrive_save_to_drive_id',
            'name' => '[ONEDRIVE] Drive ID',
            'desc' => 'Drive ID where the PDFs need to be stored. E.g. <code>'.$drive_id.'</code>. Or use <code>%upload_drive_id%</code> for the Account ID for the upload location of the plugin Upload Box field.',
            'type' => 'text',
            'std' => $drive_id,
        ];

        $fields['shareonedrive_save_to_onedrive_id'] = [
            'id' => 'shareonedrive_save_to_onedrive_id',
            'name' => '[ONEDRIVE] Folder ID',
            'desc' => 'Folder ID where the PDFs need to be stored. E.g. <code>64FA552F!1192</code> or <code>01EXLASDFSD54PWSELRRZ</code>. Or use <code>%upload_folder_id%</code> for the Account ID for the upload location of the plugin Upload Box field.',
            'type' => 'text',
            'std' => '',
        ];

        return $fields;
    }

    public function shareonedrive_post_save_pdf($pdf_path, $filename, $settings, $entry, $form)
    {
        if (!isset($settings['shareonedrive_save_to_onedrive']) || 'No' === $settings['shareonedrive_save_to_onedrive']) {
            return false;
        }

        $file = (object) [
            'tmp_path' => $pdf_path,
            'type' => mime_content_type($pdf_path),
            'name' => $filename,
            'size' => filesize($pdf_path),
        ];

        if (!isset($settings['shareonedrive_save_to_account_id'])) {
            // Fall back for older PDF configurations
            $settings['shareonedrive_save_to_account_id'] = Accounts::instance()->get_primary_account()->get_id();
        }

        if (!isset($settings['shareonedrive_save_to_drive_id'])) {
            // Fall back for older PDF configurations
            $settings['shareonedrive_save_to_drive_id'] = App::get_primary_drive_id();
        }

        // Placeholders
        list($upload_account_id, $upload_drive_id, $upload_folder_id) = $this->get_upload_location($entry, $form);

        if (false !== strpos($settings['shareonedrive_save_to_account_id'], '%upload_account_id%')) {
            $settings['shareonedrive_save_to_account_id'] = $upload_account_id;
        }

        if (false !== strpos($settings['shareonedrive_save_to_drive_id'], '%upload_drive_id%')) {
            $settings['shareonedrive_save_to_drive_id'] = $upload_drive_id;
        }
        if (false !== strpos($settings['shareonedrive_save_to_onedrive_id'], '%upload_folder_id%')) {
            $settings['shareonedrive_save_to_onedrive_id'] = $upload_folder_id;
        }

        // Filters
        $account_id = apply_filters('shareonedrive_gravitypdf_set_account_id', $settings['shareonedrive_save_to_account_id'], $settings, $entry, $form, Processor::instance());
        $drive_id = apply_filters('shareonedrive_gravitypdf_set_drive_id', $settings['shareonedrive_save_to_drive_id'], $settings, $entry, $form, Processor::instance());
        $folder_id = apply_filters('shareonedrive_gravitypdf_set_folder_id', $settings['shareonedrive_save_to_onedrive_id'], $settings, $entry, $form, Processor::instance());

        $cached_node = $this->shareonedrive_upload_gravify_pdf($file, $account_id, $drive_id, $folder_id);

        // Add url to PDF file in cloud
        $pdfs = \GPDFAPI::get_entry_pdfs($entry['id']);

        foreach ($pdfs as $pid => $pdf) {
            if ('Yes' === $pdf['shareonedrive_save_to_onedrive']) {
                $pdf['shareonedrive_pdf_url'] = $cached_node->get_entry()->get_preview_link();
                \GPDFAPI::update_pdf($form['id'], $pid, $pdf);
            }
        }
    }

    public function shareonedrive_upload_gravify_pdf($file, $account_id, $drive_id, $folder_id)
    {
        $requested_account = Accounts::instance()->get_account_by_id($account_id);
        if (null !== $requested_account) {
            App::set_current_account($requested_account);
        } else {
            error_log(sprintf("[WP Cloud Plugin message]: OneDrive account (ID: %s) as it isn't linked with the plugin", $account_id));

            exit;
        }

        try {
            API::set_drive_by_id($drive_id);

            return API::upload_file($file, $folder_id);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function get_upload_location($entry, $form)
    {
        $account_id = '';
        $drive_id = '';
        $folder_id = '';

        if (!is_array($form['fields'])) {
            return [$account_id, $drive_id, $folder_id];
        }

        foreach ($form['fields'] as $field) {
            if ('shareonedrive' !== $field->type) {
                continue;
            }

            if (!isset($entry[$field->id])) {
                continue;
            }

            $uploadedfiles = json_decode($entry[$field->id]);

            if ((null !== $uploadedfiles) && (count((array) $uploadedfiles) > 0)) {
                $first_entry = reset($uploadedfiles);

                $account_id = $first_entry->account_id;
                $requested_account = Accounts::instance()->get_account_by_id($account_id);
                App::set_current_account($requested_account);

                $drive_id = $first_entry->drive_id;
                App::set_current_drive_id($drive_id);

                $cached_entry = Client::instance()->get_entry($first_entry->hash, false);
                $parents = $cached_entry->get_parents();
                $folder_id = reset($parents)->get_id();
            }
        }

        return [$account_id, $drive_id, $folder_id];
    }
}

new GravityPDF();
