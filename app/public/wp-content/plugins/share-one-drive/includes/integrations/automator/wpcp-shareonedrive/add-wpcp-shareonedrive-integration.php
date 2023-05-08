<?php

use Uncanny_Automator\Recipe;

/**
 * Class Add_Wpcp_ShareoneDrive_Integration.
 */
class Add_Wpcp_ShareoneDrive_Integration
{
    use Recipe\Integrations;

    /**
     * Add_Wpcp_ShareoneDrive_Integration constructor.
     */
    public function __construct()
    {
        $this->setup();
    }

    protected function setup()
    {
        $this->set_integration('wpcp-shareonedrive');
        $this->set_external_integration(true);
        $this->set_name('Share-one-Drive (OneDrive / SharePoint)');
        $this->set_icon('onedrive_logo.svg');
        $this->set_icon_path(SHAREONEDRIVE_ROOTDIR.'/css/images/');
        $this->set_plugin_file_path(SHAREONEDRIVE_ROOTDIR.'/share-one-drive.php');
    }

    /**
     * @return bool
     */
    public function plugin_active()
    {
        return true;
    }
}
