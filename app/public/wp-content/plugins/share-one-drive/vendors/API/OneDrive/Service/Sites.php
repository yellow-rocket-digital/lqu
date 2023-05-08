<?php

class SODOneDrive_Service_Sites extends SODOneDrive_Service
{
    /** View and manage sites in SharePoint. */
    const SITES = 'https://api.onedrive.com/v1.0/sites/';

    public $sites;

    public $serviceName;
    /**
     * Constructs the internal representation of the Drive service.
     */
    public function __construct(SODOneDrive_Client $client)
    {
        parent::__construct($client);
        $this->servicePath = 'v1.0/sites/';
        $this->version = 'v1.0';
        $this->serviceName = 'sites';

        $this->sites = new SODOneDrive_Service_Sites_Resource(
            $this,
            $this->serviceName,
            'sites',
            [
                'methods' => [
                    'get' => [
                        'path' => '<id>/',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'expand' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'top' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                        ],
                    ], 'search' => [
                        'path' => '?search=<q>',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'q' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'expand' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'top' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                            'skiptoken' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'filter' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}

class SODOneDrive_Service_Sites_Resource extends SODOneDrive_Service_Resource
{
    /**
     * Get Site Information.
     *
     * @param array $optParams optional parameters
     * @param mixed $siteId
     *
     * @opt_param string q Query string for searching sites.
     *
     * @return SODOneDrive_Service_Site_Item
     */
    public function get($siteId, $optParams = [])
    {
        $params = ['id' => $siteId];

        $params = array_merge($params, $optParams);

        return $this->call('get', [$params], 'SODOneDrive_Service_Site_Item');
    }

    /**
     * Lists the sites.
     *
     * @param array $optParams optional parameters
     *
     * @opt_param string q Query string for searching sites.
     *
     * @return SODOneDrive_Service_Sites_SiteList
     */
    public function search($optParams = [])
    {
        $params = [];
        $params = array_merge($params, $optParams);

        return $this->call('search', [$params], 'SODOneDrive_Service_Sites_SiteList');
    }
}

class SODOneDrive_Service_Site_Item extends SODOneDrive_Collection
{
    public $id;
    public $name;
    public $displayName;
    public $eTag;
    public $createdDateTime;
    public $lastModifiedDateTime;
    public $description;
    public $webUrl;
    protected $drivesType = 'SODOneDrive_Service_Drive_Item';
    protected $drivesDataType = 'array';

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function setETag($eTag)
    {
        $this->eTag = $eTag;
    }

    public function getETag()
    {
        return $this->eTag;
    }

    public function setCreatedDateTime($createdDateTime)
    {
        $this->createdDateTime = $createdDateTime;
    }

    public function getCreatedDateTime()
    {
        return $this->createdDateTime;
    }

    public function setLastModifiedDateTime($lastModifiedDateTime)
    {
        $this->lastModifiedDateTime = $lastModifiedDateTime;
    }

    public function getLastModifiedDateTime()
    {
        return $this->lastModifiedDateTime;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setWebUrl($webUrl)
    {
        $this->webUrl = $webUrl;
    }

    public function getWebUrl()
    {
        return $this->webUrl;
    }

    public function setDrives(SODOneDrive_Service_Drive_Item $drives)
    {
        $this->drives = $drives;
    }

    public function getDrives()
    {
        return $this->drives;
    }
}

class SODOneDrive_Service_Sites_SiteList extends SODOneDrive_Collection
{
    protected $valueType = 'SODOneDrive_Service_Site_Item';
    protected $valueDataType = 'array';

    public function setValue(SODOneDrive_Service_Site_Item $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
