<?php

/*
 * Copyright 2010 Google Inc.
 * Copyright 2015 www.wpcloudplugins.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

/**
 * Service definition for Drive (v2).
 *
 * <p>
 * The API to interact with Drive.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://api.onedrive.com/v1.0/drive/" target="_blank">Documentation</a>
 * </p>
 *
 * @author OneDrive, Inc.
 */
class SODOneDrive_Service_Drive extends SODOneDrive_Service
{
    /** View and manage the files in your OneDrive Drive. */
    const DRIVE = 'https://api.onedrive.com/v1.0/me/';

    public $about;
    public $drives;
    public $items;
    public $changes;

    public $serviceName;
    
    /**
     * Constructs the internal representation of the Drive service.
     */
    public function __construct(SODOneDrive_Client $client)
    {
        parent::__construct($client);
        $this->servicePath = 'v1.0/me/';
        $this->version = 'v1.0';
        $this->serviceName = 'drive';

        $this->about = new SODOneDrive_Service_Drive_About_Resource(
            $this,
            $this->serviceName,
            'about',
            [
                'methods' => [
                    'get' => [
                        'path' => '<driveId>/',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'select' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'expand' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'orderby' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'top' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'skiptoken' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->changes = new SODOneDrive_Service_Drive_Changes_Resource(
            $this,
            $this->serviceName,
            'changes',
            [
                'methods' => [
                    'get' => [
                        'path' => "<driveId>/items/<id>/delta(token='<token>')",
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'token' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ],
                    'getlatest' => [
                        'path' => '<driveId>/items/<id>/delta?token=latest',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->drives = new SODOneDrive_Service_Drive_Drives_Resource(
            $this,
            $this->serviceName,
            'drives',
            [
                'methods' => [
                    'get' => [
                        'path' => 'drives/<driveId>',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'type' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
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
                    ], 'list' => [
                        'path' => 'drives',
                        'httpMethod' => 'GET',
                        'parameters' => [
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

        $this->items = new SODOneDrive_Service_Drive_Items_Resource(
            $this,
            $this->serviceName,
            'items',
            [
                'methods' => [
                    'root' => [
                        'path' => 'drive/root',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'expand' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'top' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                        ],
                    ],
                    'copy' => [
                        'path' => '<driveId>/items/<id>/copy',
                        'httpMethod' => 'POST',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ], 'delete' => [
                        'path' => '<driveId>/items/<id>',
                        'httpMethod' => 'DELETE',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'if-match' => [
                                'location' => 'header',
                                'type' => 'string',
                                'required' => false,
                            ],
                        ],
                    ], 'get' => [
                        'path' => '<driveId>/<type>/<id>',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'type' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
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
                    ], 'children' => [
                        'path' => '<driveId>/<type>/<id>/children',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'type' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
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
                            'skiptoken' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'filter' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ], 'insert' => [
                        'path' => '<driveId>/items/<parent_item_id>/children',
                        'httpMethod' => 'POST',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'parent_item_id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ], 'expand' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ], 'upload' => [
                        'path' => '<driveId>/items/<parent_item_id>:/<filename>:/createUploadSession',
                        'httpMethod' => 'POST',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'filename' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'parent_item_id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'expand' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'select' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ], 'search' => [
                        'path' => "<driveId>/items/<id>/search(q='<q>')",
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'q' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'select' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'expand' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'orderby' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'top' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                            'skiptoken' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                        ],
                    ], 'patch' => [
                        'path' => '<driveId>/items/<id>',
                        'httpMethod' => 'PATCH',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'expand' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'if-match' => [
                                'location' => 'header',
                                'type' => 'string',
                                'required' => false,
                            ],
                            'prefer' => [
                                'location' => 'header',
                                'type' => 'string',
                                'required' => false,
                            ],
                        ],
                    ], 'update' => [
                        'path' => '<driveId>/items/<id>',
                        'httpMethod' => 'PUT',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'if-match' => [
                                'location' => 'header',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ], 'download' => [
                        'path' => '<driveId>/items/<id>/content',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ], 'export' => [
                        'path' => '<driveId>/items/<id>/content?format=<format>',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'format' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ], 'downloadthumbnail' => [
                        'path' => '<driveId>/items/<id>/thumbnails/<thumb-id>/<size>/content',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'thumb-id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'size' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ],
                    'createlink' => [
                        'path' => '<driveId>/items/<id>/createLink',
                        'httpMethod' => 'POST',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'type' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'scope' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ], 'preview' => [
                        'path' => '<driveId>/items/<id>/preview',
                        'httpMethod' => 'POST',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],                            
                            'id' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'viewer' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'chromeless' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'allowEdit' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'page' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                            'zoom' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}

/**
 * The "about" collection of methods.
 * Typical usage is:
 *  <code>
 *   $driveService = new SODOneDrive_Service_Drive(...);
 *   $about = $driveService->about;
 *  </code>.
 */
class SODOneDrive_Service_Drive_About_Resource extends SODOneDrive_Service_Resource
{
    /**
     * Gets the information about the current user along with Drive API settings
     * (about.get).
     *
     * @param array $optParams optional parameters
     *
     * @opt_param bool includeSubscribed When calculating the number of remaining
     * change IDs, whether to include public files the user has opened and shared
     * files. When set to false, this counts only change IDs for owned files and any
     * shared or public files that the user has explicitly added to a folder they
     * own.
     * @opt_param string maxChangeIdCount Maximum number of remaining change IDs to
     * count
     * @opt_param string startChangeId Change ID to start counting from when
     * calculating number of remaining change IDs
     *
     * @return SODOneDrive_Service_Drive_About
     */
    public function get($optParams = [])
    {
        $params = [];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('get', [$params], 'SODOneDrive_Service_Drive_About');
    }
}

class SODOneDrive_Service_Drive_Changes_Resource extends SODOneDrive_Service_Resource
{
    public function get($fileId, $optParams = [])
    {
        $params = ['id' => $fileId];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('get', [$params], 'SODOneDrive_Service_Drive_Changes');
    }

    public function getlatest($fileId, $optParams = [])
    {
        $params = ['id' => $fileId];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('getlatest', [$params], 'SODOneDrive_Service_Drive_Changes');
    }
}

class SODOneDrive_Service_Drive_Drives_Resource extends SODOneDrive_Service_Resource
{
    /**
     * List all drives.
     *
     * @param array $optParams optional parameters
     *
     * @return SODOneDrive_Service_Drives_FileList
     */
    public function list($optParams = [])
    {
        $params = [];
        $params = array_merge($params, $optParams);

        return $this->call('list', [$params], 'SODOneDrive_Service_Drives_FileList');
    }
}
/**
 * The "files" collection of methods.
 * Typical usage is:
 *  <code>
 *   $driveService = new SODOneDrive_Service_Drive(...);
 *   $files = $driveService->files;
 *  </code>.
 */
class SODOneDrive_Service_Drive_Items_Resource extends SODOneDrive_Service_Resource
{
    /**
     * Gets the root Drive.
     *
     * @param array $optParams optional parameters
     *
     * @return SODOneDrive_Service_Drive_Item
     */
    public function root($optParams = [])
    {
        $params = [];
        $params = array_merge($params, $optParams);

        return $this->call('root', [$params], 'SODOneDrive_Service_Drive_Item');
    }

    /**
     * Creates a copy of the specified file. (files.copy).
     *
     * @param string                $fileId    the ID of the file to copy
     * @param SODOneDrive_DriveFile $postBody
     * @param array                 $optParams optional parameters
     *
     * @opt_param bool convert Whether to convert this file to the corresponding
     * OneDrive Docs format.
     * @opt_param string ocrLanguage If ocr is true, hints at the language to use.
     * Valid values are ISO 639-1 codes.
     * @opt_param string visibility The visibility of the new file. This parameter
     * is only relevant when the source is not a native OneDrive Doc and
     * convert=false.
     * @opt_param bool pinned Whether to pin the head revision of the new copy. A
     * file can have a maximum of 200 pinned revisions.
     * @opt_param bool ocr Whether to attempt OCR on .jpg, .png, .gif, or .pdf
     * uploads.
     * @opt_param string timedTextTrackName The timed text track name.
     * @opt_param string timedTextLanguage The language of the timed text.
     *
     * @return SODOneDrive_Service_Drive_Item
     */
    public function copy($fileId, SODOneDrive_Service_Drive_Item $postBody, $optParams = [])
    {
        $params = ['id' => $fileId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('copy', [$params], 'SODOneDrive_Service_Drive_Item');
    }

    /**
     * Permanently deletes a file by ID. Skips the trash. The currently
     * authenticated user must own the file. (files.delete).
     *
     * @param string $fileId    the ID of the file to delete
     * @param array  $optParams optional parameters
     */
    public function delete($fileId, $optParams = [])
    {
        $params = ['id' => $fileId];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('delete', [$params]);
    }

    /**
     * Gets a file's metadata by ID. (files.get).
     *
     * @param string $fileId    the ID for the file in question
     * @param array  $optParams optional parameters
     *
     * @opt_param string projection This parameter is deprecated and has no
     * function.
     * @opt_param string revisionId Specifies the Revision ID that should be
     * downloaded. Ignored unless alt=media is specified.
     * @opt_param bool acknowledgeAbuse Whether the user is acknowledging the risk
     * of downloading known malware or other abusive files. Ignored unless alt=media
     * is specified.
     * @opt_param bool updateViewedDate Whether to update the view date after
     * successfully retrieving the file.
     *
     * @return SODOneDrive_Service_Drive_Item
     */
    public function get($fileId, $optParams = [])
    {
        $type = (false === strpos($fileId, '/')) ? 'items' : 'root:';

        $params = ['type' => $type, 'id' => $fileId];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('get', [$params], 'SODOneDrive_Service_Drive_Item');
    }

    /**
     * Gets the children in a folder.
     *
     * @param string $fileId    the ID for the file in question
     * @param array  $optParams optional parameters
     *
     * @return SODOneDrive_Service_Drive_FileList
     */
    public function children($fileId, $optParams = [])
    {
        $type = (false === strpos($fileId, '/')) ? 'items' : 'root:';

        if ('root:' === $type) {
            $fileId = $fileId.':';
        }
        $params = ['type' => $type, 'id' => $fileId];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('children', [$params], 'SODOneDrive_Service_Drive_FileList');
    }

    /**
     * Insert a new file. (files.insert).
     *
     * @param SODOneDrive_DriveFile $postBody
     * @param array                 $optParams        optional parameters
     * @param mixed                 $parent_folder_id
     *
     * @opt_param bool convert Whether to convert this file to the corresponding
     * OneDrive Docs format.
     * @opt_param bool useContentAsIndexableText Whether to use the content as
     * indexable text.
     * @opt_param string ocrLanguage If ocr is true, hints at the language to use.
     * Valid values are ISO 639-1 codes.
     * @opt_param string visibility The visibility of the new file. This parameter
     * is only relevant when convert=false.
     * @opt_param bool pinned Whether to pin the head revision of the uploaded file.
     * A file can have a maximum of 200 pinned revisions.
     * @opt_param bool ocr Whether to attempt OCR on .jpg, .png, .gif, or .pdf
     * uploads.
     * @opt_param string timedTextTrackName The timed text track name.
     * @opt_param string timedTextLanguage The language of the timed text.
     *
     * @return SODOneDrive_Service_Drive_Item
     */
    public function insert($parent_folder_id, SODOneDrive_Service_Drive_Item $entry, $optParams = [])
    {
        $params = ['parent_item_id' => $parent_folder_id, 'postBody' => $entry];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('insert', [$params], 'SODOneDrive_Service_Drive_Item');
    }

    public function upload($filename, $parent_item_id, $postBody, $optParams = [])
    {
        $params = ['filename' => $filename, 'parent_item_id' => $parent_item_id, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('upload', [$params], 'SODOneDrive_Service_Drive_Item');
    }

    /**
     * Lists the user's files. (files.listFiles).
     *
     * @param array $optParams optional parameters
     *
     * @opt_param string q Query string for searching files.
     * @opt_param string pageToken Page token for files.
     * @opt_param string corpus The body of items (files/documents) to which the
     * query applies.
     * @opt_param string projection This parameter is deprecated and has no
     * function.
     * @opt_param int maxResults Maximum number of files to return.
     *
     * @return SODOneDrive_Service_Drive_FileList
     */
    public function search($optParams = [])
    {
        $params = [];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('search', [$params], 'SODOneDrive_Service_Drive_FileList');
    }

    /**
     * Updates file metadata and/or content. This method supports patch semantics.
     * (files.patch).
     *
     * @param string                $fileId    the ID of the file to update
     * @param SODOneDrive_DriveFile $postBody
     * @param array                 $optParams optional parameters
     *
     * @opt_param string addParents Comma-separated list of parent IDs to add.
     * @opt_param bool updateViewedDate Whether to update the view date after
     * successfully updating the file.
     * @opt_param string removeParents Comma-separated list of parent IDs to remove.
     * @opt_param bool setModifiedDate Whether to set the modified date with the
     * supplied modified date.
     * @opt_param bool convert Whether to convert this file to the corresponding
     * OneDrive Docs format.
     * @opt_param bool useContentAsIndexableText Whether to use the content as
     * indexable text.
     * @opt_param string ocrLanguage If ocr is true, hints at the language to use.
     * Valid values are ISO 639-1 codes.
     * @opt_param bool pinned Whether to pin the new revision. A file can have a
     * maximum of 200 pinned revisions.
     * @opt_param bool newRevision Whether a blob upload should create a new
     * revision. If false, the blob data in the current head revision is replaced.
     * If true or not set, a new blob is created as head revision, and previous
     * revisions are preserved (causing increased use of the user's data storage
     * quota).
     * @opt_param bool ocr Whether to attempt OCR on .jpg, .png, .gif, or .pdf
     * uploads.
     * @opt_param string timedTextLanguage The language of the timed text.
     * @opt_param string timedTextTrackName The timed text track name.
     *
     * @return SODOneDrive_Service_Drive_Item
     */
    public function patch($fileId, $postBody, $optParams = [])
    {
        $params = ['id' => $fileId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('patch', [$params], 'SODOneDrive_Service_Drive_Item');
    }

    /**
     * Updates file metadata and/or content. (files.update).
     *
     * @param string                $fileId    the ID of the file to update
     * @param SODOneDrive_DriveFile $postBody
     * @param array                 $optParams optional parameters
     *
     * @opt_param string addParents Comma-separated list of parent IDs to add.
     * @opt_param bool updateViewedDate Whether to update the view date after
     * successfully updating the file.
     * @opt_param string removeParents Comma-separated list of parent IDs to remove.
     * @opt_param bool setModifiedDate Whether to set the modified date with the
     * supplied modified date.
     * @opt_param bool convert Whether to convert this file to the corresponding
     * OneDrive Docs format.
     * @opt_param bool useContentAsIndexableText Whether to use the content as
     * indexable text.
     * @opt_param string ocrLanguage If ocr is true, hints at the language to use.
     * Valid values are ISO 639-1 codes.
     * @opt_param bool pinned Whether to pin the new revision. A file can have a
     * maximum of 200 pinned revisions.
     * @opt_param bool newRevision Whether a blob upload should create a new
     * revision. If false, the blob data in the current head revision is replaced.
     * If true or not set, a new blob is created as head revision, and previous
     * revisions are preserved (causing increased use of the user's data storage
     * quota).
     * @opt_param bool ocr Whether to attempt OCR on .jpg, .png, .gif, or .pdf
     * uploads.
     * @opt_param string timedTextLanguage The language of the timed text.
     * @opt_param string timedTextTrackName The timed text track name.
     *
     * @return SODOneDrive_Service_Drive_Item
     */
    public function update($fileId, SODOneDrive_Service_Drive_Item $postBody, $optParams = [])
    {
        $params = ['id' => $fileId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('update', [$params], 'SODOneDrive_Service_Drive_Item');
    }

    public function download($fileId, $optParams = [])
    {
        $params = ['id' => $fileId];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('download', [$params]);
    }

    public function export($fileId, $optParams = [])
    {
        $params = ['id' => $fileId];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('export', [$params]);
    }

    public function downloadthumbnail($fileId, $thumbId, $thumbsize, $optParams = [])
    {
        $params = ['id' => $fileId, 'thumb-id' => $thumbId, 'size' => $thumbsize];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('downloadthumbnail', [$params]);
    }

    public function createlink($fileId, $postBody, $optParams = [])
    {
        $params = ['id' => $fileId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('createlink', [$params], 'SODOneDrive_Service_Drive_Permission');
    }

    public function preview($fileId, $postBody, $optParams = [])
    {
        $params = ['id' => $fileId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);
        if (empty($optParams['driveId']) || 'drive' === $optParams['driveId']) {
            $params['driveId'] = 'drive';
        } else {
            $params['driveId'] = 'drives/'.$optParams['driveId'];
        }

        return $this->call('preview', [$params], 'SODOneDrive_Service_Drive_PreviewUrl');
    }
}

class SODOneDrive_Service_Drive_About extends SODOneDrive_Collection
{
    public $id;
    public $driveType;
    protected $ownerType = 'SODOneDrive_Service_Drive_IdentitySet';
    protected $ownerDataType = 'array';
    protected $quotaType = 'SODOneDrive_Service_Drive_AboutQuota';
    protected $quotaDataType = 'array';

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setDriveType($driveType)
    {
        $this->driveType = $driveType;
    }

    public function getDriveType()
    {
        return $this->driveType;
    }

    public function setQuota($quota)
    {
        $this->quota = $quota;
    }

    public function getQuota()
    {
        return $this->quota;
    }
}

class SODOneDrive_Service_Drive_AboutQuota extends SODOneDrive_Model
{
    public $deleted;
    public $remaining;
    public $state;
    public $total;
    public $used;

    protected $internal_gapi_mappings = [];

    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    public function getDeleted()
    {
        return $this->deleted;
    }

    public function setRemaining($remaining)
    {
        $this->remaining = $remaining;
    }

    public function getRemaining()
    {
        return $this->remaining;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setUsed($used)
    {
        $this->used = $used;
    }

    public function getUsed()
    {
        return $this->used;
    }
}

class SODOneDrive_Service_Drive_Item extends SODOneDrive_Collection
{
    public $id;
    public $name;
    public $eTag;
    public $cTag;
    public $createdDateTime;
    public $lastModifiedDateTime;
    public $size;
    public $description;
    public $webUrl;
    protected $rootType = 'SODOneDrive_Service_Drive_Item';
    protected $rootDataType = '';
    protected $createdByType = 'SODOneDrive_Service_Drive_IdentitySet';
    protected $createdByDataType = 'array';
    protected $lastModifiedByType = 'SODOneDrive_Service_Drive_IdentitySet';
    protected $lastModifiedByDataType = 'array';
    protected $parentReferenceType = 'SODOneDrive_Service_Drive_ItemReference';
    protected $parentReferenceDataType = '';
    protected $fileType = 'SODOneDrive_Service_Drive_FileFacet';
    protected $fileDataType = '';
    protected $fileSystemInfoType = 'SODOneDrive_Service_Drive_FileSystemInfoFacet';
    protected $fileSystemInfoDataType = '';
    protected $folderType = 'SODOneDrive_Service_Drive_FolderFacet';
    protected $folderDataType = '';
    protected $imageType = 'SODOneDrive_Service_Drive_ImageFacet';
    protected $imageDataType = '';
    protected $photoType = 'SODOneDrive_Service_Drive_PhotoFacet';
    protected $photoDataType = '';
    protected $audioType = 'SODOneDrive_Service_Drive_AudioFacet';
    protected $audioDataType = '';
    protected $videoType = 'SODOneDrive_Service_Drive_VideoFacet';
    protected $videoDataType = '';
    protected $locationType = 'SODOneDrive_Service_Drive_LocationFacet';
    protected $locationDataType = '';
    protected $deletedType = 'SODOneDrive_Service_Drive_DeletedFacet';
    protected $deletedDataType = '';
    protected $packageType = 'SODOneDrive_Service_Drive_PackageFacet';
    protected $packageDataType = '';
    protected $sharepointIdsType = 'SODOneDrive_Service_Drive_SharepointIdsFacet';
    protected $sharepointIdsDataType = '';
    protected $specialFolderType = 'SODOneDrive_Service_Drive_SpecialFolderFacet';
    protected $specialFolderDataType = '';
    protected $remoteItemType = 'SODOneDrive_Service_Drive_RemoteItemFacet';
    protected $remoteItemDataType = '';
    protected $sharedType = 'SODOneDrive_Service_Drive_SharedFacet';
    protected $sharedDataType = 'array';
    protected $childrenType = 'SODOneDrive_Service_Drive_Item';
    protected $childrenDataType = 'array';
    protected $permissionsType = 'SODOneDrive_Service_Drive_Permission';
    protected $permissionsDataType = '';
    protected $thumbnailsType = 'SODOneDrive_Service_Drive_ThumbnailSet';
    protected $thumbnailsDataType = '';

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

    public function setETag($eTag)
    {
        $this->eTag = $eTag;
    }

    public function getETag()
    {
        return $this->eTag;
    }

    public function setCTag($cTag)
    {
        $this->cTag = $cTag;
    }

    public function getCTag()
    {
        return $this->cTag;
    }

    public function setRoot(SODOneDrive_Service_Drive_RootFacet $root)
    {
        $this->root = $root;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function setCreatedBy(SODOneDrive_Service_Drive_IdentitySet $createdBy)
    {
        $this->createdBy = $createdBy;
    }

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function setCreatedDateTime($createdDateTime)
    {
        $this->createdDateTime = $createdDateTime;
    }

    public function getCreatedDateTime()
    {
        return $this->createdDateTime;
    }

    public function setLastModifiedBy(SODOneDrive_Service_Drive_IdentitySet $lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;
    }

    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    public function setLastModifiedDateTime($lastModifiedDateTime)
    {
        $this->lastModifiedDateTime = $lastModifiedDateTime;
    }

    public function getLastModifiedDateTime()
    {
        return $this->lastModifiedDateTime;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setParentReference(SODOneDrive_Service_Drive_ItemReference $parentReference)
    {
        $this->parentReference = $parentReference;
    }

    public function getParentReference()
    {
        return $this->parentReference;
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

    public function setFile(SODOneDrive_Service_Drive_FileFacet $file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFileSystemInfo(SODOneDrive_Service_Drive_FileSystemInfoFacet $fileSystemInfo)
    {
        $this->fileSystemInfo = $fileSystemInfo;
    }

    public function getFileSystemInfo()
    {
        return $this->fileSystemInfo;
    }

    public function setFolder(SODOneDrive_Service_Drive_FolderFacet $folder)
    {
        $this->folder = $folder;
    }

    public function getFolder()
    {
        return $this->folder;
    }

    public function setImage(SODOneDrive_Service_Drive_ImageFacet $image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setPhoto(SODOneDrive_Service_Drive_PhotoFacet $photo)
    {
        $this->photo = $photo;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setAudio(SODOneDrive_Service_Drive_AudioFacet $audio)
    {
        $this->audio = $audio;
    }

    public function getAudio()
    {
        return $this->audio;
    }

    public function setVideo(SODOneDrive_Service_Drive_VideoFacet $video)
    {
        $this->video = $video;
    }

    public function getVideo()
    {
        return $this->video;
    }

    public function setLocation(SODOneDrive_Service_Drive_LocationFacet $location)
    {
        $this->location = $location;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setDeleted(SODOneDrive_Service_Drive_DeletedFacet $deleted)
    {
        $this->deleted = $deleted;
    }

    public function getDeleted()
    {
        return $this->deleted;
    }

    public function setPackage(SODOneDrive_Service_Drive_PackageFacet $package)
    {
        $this->package = $package;
    }

    public function getPackage()
    {
        return $this->package;
    }

    public function setSharepointIds(SODOneDrive_Service_Drive_SharepointIdsFacet $sharepointIds)
    {
        $this->sharepointIds = $sharepointIds;
    }

    public function getSharepointIds()
    {
        return $this->sharepointIds;
    }

    public function setSpecialFolder(SODOneDrive_Service_Drive_SpecialFolderFacet $specialFolder)
    {
        $this->specialFolder = $specialFolder;
    }

    public function getSpecialFolder()
    {
        return $this->specialFolder;
    }

    public function setRemoteItem(SODOneDrive_Service_Drive_RemoteItemFacet $remoteItem)
    {
        $this->remoteItem = $remoteItem;
    }

    public function getRemoteItem()
    {
        return $this->remoteItem;
    }

    public function setShared(SODOneDrive_Service_Drive_SharedFacet $shared)
    {
        $this->shared = $shared;
    }

    public function getShared()
    {
        return $this->shared;
    }

    public function setChildren(SODOneDrive_Service_Drive_Item $children)
    {
        $this->children = $children;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setPermissions(SODOneDrive_Service_Drive_Permission $permissions)
    {
        $this->permissions = $permissions;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function setThumbnails(SODOneDrive_Service_Drive_ThumbnailSet $thumbnails)
    {
        $this->thumbnails = $thumbnails;
    }

    public function getThumbnails()
    {
        return $this->thumbnails;
    }
}

class SODOneDrive_Service_Drive_RootFacet extends SODOneDrive_Model
{
}

class SODOneDrive_Service_Drive_IdentitySet extends SODOneDrive_Model
{
    protected $userType = 'SODOneDrive_Service_Drive_Identity';
    protected $userDataType = '';
    protected $applicationType = 'SODOneDrive_Service_Drive_Identity';
    protected $applicationDataType = '';
    protected $deviceType = 'SODOneDrive_Service_Drive_Identity';
    protected $deviceDataType = '';

    public function setUser(SODOneDrive_Service_Drive_Identity $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setApplication(SODOneDrive_Service_Drive_Identity $application)
    {
        $this->application = $application;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function setDevice(SODOneDrive_Service_Drive_Identity $device)
    {
        $this->device = $device;
    }

    public function getDevice()
    {
        return $this->device;
    }
}

class SODOneDrive_Service_Drive_Identity extends SODOneDrive_Model
{
    public $id;
    public $displayName;
    public $email;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }
}

class SODOneDrive_Service_Drive_ItemReference extends SODOneDrive_Model
{
    public $driveId;
    public $id;
    public $name;
    public $path;
    public $shareId;
    protected $sharepointIdsType = 'SODOneDrive_Service_Drive_SharepointIdsFacet';
    protected $sharepointIdsDataType = '';

    public function setDriveId($driveId)
    {
        $this->driveId = $driveId;
    }

    public function getDriveId()
    {
        return $this->driveId;
    }

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

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setShareId($shareId)
    {
        $this->shareId = $shareId;
    }

    public function getShareId()
    {
        return $this->shareId;
    }

    public function setSharepointIds(SODOneDrive_Service_Drive_SharepointIdsFacet $sharepointIds)
    {
        $this->sharepointIds = $sharepointIds;
    }

    public function getSharepointIds()
    {
        return $this->sharepointIds;
    }
}

class SODOneDrive_Service_Drive_SharepointIdsFacet extends SODOneDrive_Model
{
    public $listId;
    public $listItemId;
    public $listItemUniqueId;
    public $siteId;
    public $siteUrl;
    public $webId;

    public function setListId($listId)
    {
        $this->listId = $listId;
    }

    public function getListId()
    {
        return $this->listId;
    }

    public function setListItemId($listItemId)
    {
        $this->listItemId = $listItemId;
    }

    public function getListItemId()
    {
        return $this->listItemId;
    }

    public function setListItemUniqueId($listItemUniqueId)
    {
        $this->listItemUniqueId = $listItemUniqueId;
    }

    public function getListItemUniqueId()
    {
        return $this->listItemUniqueId;
    }

    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
    }

    public function getSiteId()
    {
        return $this->siteId;
    }

    public function setSiteUrl($siteUrl)
    {
        $this->siteUrl = $siteUrl;
    }

    public function getSiteUrl()
    {
        return $this->siteUrl;
    }

    public function setWebId($webId)
    {
        $this->webId = $webId;
    }

    public function getWebId()
    {
        return $this->webId;
    }
}

class SODOneDrive_Service_Drive_FileFacet extends SODOneDrive_Model
{
    public $mimeType;
    public $processingMetadata;
    protected $hashesType = 'SODOneDrive_Service_Drive_HashesType';
    protected $hashesDataType = '';

    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function setHashes(SODOneDrive_Service_Drive_HashesType $hashes)
    {
        $this->hashes = $hashes;
    }

    public function getHashes()
    {
        return $this->hashes;
    }

    public function setProcessingMetadata($processingMetadata)
    {
        $this->processingMetadata = $processingMetadata;
    }

    public function getProcessingMetadata()
    {
        return $this->processingMetadata;
    }
}

class SODOneDrive_Service_Drive_HashesType extends SODOneDrive_Model
{
    public $sha1Hash;
    public $crc32Hash;
    public $quickXorHash;

    public function setSha1Hash($sha1Hash)
    {
        $this->sha1Hash = $sha1Hash;
    }

    public function getSha1Hash()
    {
        return $this->sha1Hash;
    }

    public function setCrc32Hash($crc32Hash)
    {
        $this->crc32Hash = $crc32Hash;
    }

    public function getCrc32Hash()
    {
        return $this->crc32Hash;
    }

    public function setQuickXorHash($quickXorHash)
    {
        $this->quickXorHash = $quickXorHash;
    }

    public function getQuickXorHash()
    {
        return $this->quickXorHash;
    }
}

class SODOneDrive_Service_Drive_FileSystemInfoFacet extends SODOneDrive_Model
{
    public $createdDateTime;
    public $lastAccessedDateTime;
    public $lastModifiedDateTime;

    public function setCreatedDateTime($createdDateTime)
    {
        $this->createdDateTime = $createdDateTime;
    }

    public function getCreatedDateTime()
    {
        return $this->createdDateTime;
    }

    public function setLastAccessedDateTime($lastAccessedDateTime)
    {
        $this->lastAccessedDateTime = $lastAccessedDateTime;
    }

    public function getLastAccessedDateTime()
    {
        return $this->lastAccessedDateTime;
    }

    public function setLastModifiedDateTime($lastModifiedDateTime)
    {
        $this->lastModifiedDateTime = $lastModifiedDateTime;
    }

    public function getLastModifiedDateTime()
    {
        return $this->lastModifiedDateTime;
    }
}

class SODOneDrive_Service_Drive_FolderFacet extends SODOneDrive_Model
{
    public $childCount;
    protected $viewType = 'SODOneDrive_Service_Drive_FolderViewFacet';
    protected $viewDataType = '';

    public function setChildCount($childCount)
    {
        $this->childCount = $childCount;
    }

    public function getChildCount()
    {
        return $this->childCount;
    }

    public function setView(SODOneDrive_Service_Drive_FolderViewFacet $view)
    {
        $this->view = $view;
    }

    public function getView()
    {
        return $this->view;
    }
}

class SODOneDrive_Service_Drive_FolderViewFacet extends SODOneDrive_Model
{
    public $sortBy;
    public $sortOrder;
    public $viewType;

    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
    }

    public function getSortBy()
    {
        return $this->sortBy;
    }

    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function setViewType($viewType)
    {
        $this->viewType = $viewType;
    }

    public function getViewType()
    {
        return $this->viewType;
    }
}

class SODOneDrive_Service_Drive_ImageFacet extends SODOneDrive_Model
{
    public $width;
    public $height;

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getHeight()
    {
        return $this->height;
    }
}

class SODOneDrive_Service_Drive_PhotoFacet extends SODOneDrive_Model
{
    public $takenDateTime;
    public $cameraMake;
    public $cameraModel;
    public $fNumber;
    public $exposureDenominator;
    public $exposureNumerator;
    public $focalLength;
    public $iso;

    public function setTakenDateTime($takenDateTime)
    {
        $this->takenDateTime = $takenDateTime;
    }

    public function getTakenDateTime()
    {
        return $this->takenDateTime;
    }

    public function setCameraMake($cameraMake)
    {
        $this->cameraMake = $cameraMake;
    }

    public function getCameraMake()
    {
        return $this->cameraMake;
    }

    public function setCameraModel($cameraModel)
    {
        $this->cameraModel = $cameraModel;
    }

    public function getCameraModel()
    {
        return $this->cameraModel;
    }

    public function setFNumber($fNumber)
    {
        $this->fNumber = $fNumber;
    }

    public function getFNumber()
    {
        return $this->fNumber;
    }

    public function setExposureDenominator($exposureDenominator)
    {
        $this->exposureDenominator = $exposureDenominator;
    }

    public function getExposureDenominator()
    {
        return $this->exposureDenominator;
    }

    public function setExposureNumerator($exposureNumerator)
    {
        $this->exposureNumerator = $exposureNumerator;
    }

    public function getExposureNumerator()
    {
        return $this->exposureNumerator;
    }

    public function setFocalLength($focalLength)
    {
        $this->focalLength = $focalLength;
    }

    public function getFocalLength()
    {
        return $this->focalLength;
    }

    public function setIso($iso)
    {
        $this->iso = $iso;
    }

    public function getIso()
    {
        return $this->iso;
    }
}

class SODOneDrive_Service_Drive_AudioFacet extends SODOneDrive_Model
{
    public $album;
    public $albumArtist;
    public $artist;
    public $bitrate;
    public $composers;
    public $copyright;
    public $disc;
    public $discCount;
    public $duration;
    public $genre;
    public $hasDrm;
    public $isVariableBitrate;
    public $title;
    public $track;
    public $trackCount;
    public $year;

    public function setAlbum($album)
    {
        $this->album = $album;
    }

    public function getAlbum()
    {
        return $this->album;
    }

    public function setAlbumArtist($albumArtist)
    {
        $this->albumArtist = $albumArtist;
    }

    public function getAlbumArtist()
    {
        return $this->albumArtist;
    }

    public function setArtist($artist)
    {
        $this->artist = $artist;
    }

    public function getArtist()
    {
        return $this->artist;
    }

    public function setBitrate($bitrate)
    {
        $this->bitrate = $bitrate;
    }

    public function getBitrate()
    {
        return $this->bitrate;
    }

    public function setComposers($composers)
    {
        $this->composers = $composers;
    }

    public function getComposers()
    {
        return $this->composers;
    }

    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
    }

    public function getCopyright()
    {
        return $this->copyright;
    }

    public function setDisc($disc)
    {
        $this->disc = $disc;
    }

    public function getDisc()
    {
        return $this->disc;
    }

    public function setDiscCount($discCount)
    {
        $this->discCount = $discCount;
    }

    public function getDiscCount()
    {
        return $this->discCount;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setGenre($genre)
    {
        $this->genre = $genre;
    }

    public function getGenre()
    {
        return $this->genre;
    }

    public function setHasDrm($hasDrm)
    {
        $this->hasDrm = $hasDrm;
    }

    public function getHasDrm()
    {
        return $this->hasDrm;
    }

    public function setIsVariableBitrate($isVariableBitrate)
    {
        $this->isVariableBitrate = $isVariableBitrate;
    }

    public function getIsVariableBitrate()
    {
        return $this->isVariableBitrate;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTrack($track)
    {
        $this->track = $track;
    }

    public function getTrack()
    {
        return $this->track;
    }

    public function setTrackCount($trackCount)
    {
        $this->trackCount = $trackCount;
    }

    public function getTrackCount()
    {
        return $this->trackCount;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }

    public function getYear()
    {
        return $this->year;
    }
}

class SODOneDrive_Service_Drive_VideoFacet extends SODOneDrive_Model
{
    public $bitrate;
    public $duration;
    public $height;
    public $width;

    public function setBitrate($bitrate)
    {
        $this->bitrate = $bitrate;
    }

    public function getBitrate()
    {
        return $this->bitrate;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function getWidth()
    {
        return $this->width;
    }
}

class SODOneDrive_Service_Drive_LocationFacet extends SODOneDrive_Model
{
    public $altitude;
    public $latitude;
    public $longitude;

    public function setAltitude($altitude)
    {
        $this->altitude = $altitude;
    }

    public function getAltitude()
    {
        return $this->altitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }
}

class SODOneDrive_Service_Drive_DeletedFacet extends SODOneDrive_Model
{
}

class SODOneDrive_Service_Drive_PackageFacet extends SODOneDrive_Model
{
    public $type;

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }
}

class SODOneDrive_Service_Drive_SpecialFolderFacet extends SODOneDrive_Model
{
    public $name;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}

class SODOneDrive_Service_Drive_RemoteItemFacet extends SODOneDrive_Model
{
    public $id;
    public $name;
    public $lastModifiedDateTime;
    public $size;
    public $webUrl;
    protected $createdByType = 'SODOneDrive_Service_Drive_IdentitySet';
    protected $createdByDataType = 'array';
    protected $lastModifiedByType = 'SODOneDrive_Service_Drive_IdentitySet';
    protected $lastModifiedByDataType = 'array';
    protected $parentReferenceType = 'SODOneDrive_Service_Drive_ItemReference';
    protected $parentReferenceDataType = '';
    protected $fileType = 'SODOneDrive_Service_Drive_FileFacet';
    protected $fileDataType = '';
    protected $fileSystemInfoType = 'SODOneDrive_Service_Drive_FileSystemInfoFacet';
    protected $fileSystemInfoDataType = '';
    protected $folderType = 'SODOneDrive_Service_Drive_FolderFacet';
    protected $folderDataType = '';
    protected $sharedType = 'SODOneDrive_Service_Drive_SharedFacet';
    protected $sharedDataType = 'array';

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

    public function setCreatedBy(SODOneDrive_Service_Drive_IdentitySet $createdBy)
    {
        $this->createdBy = $createdBy;
    }

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function setLastModifiedBy(SODOneDrive_Service_Drive_IdentitySet $lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;
    }

    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    public function setLastModifiedDateTime($lastModifiedDateTime)
    {
        $this->lastModifiedDateTime = $lastModifiedDateTime;
    }

    public function getLastModifiedDateTime()
    {
        return $this->lastModifiedDateTime;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setParentReference(SODOneDrive_Service_Drive_ItemReference $parentReference)
    {
        $this->parentReference = $parentReference;
    }

    public function getParentReference()
    {
        return $this->parentReference;
    }

    public function setWebUrl($webUrl)
    {
        $this->webUrl = $webUrl;
    }

    public function getWebUrl()
    {
        return $this->webUrl;
    }

    public function setFile(SODOneDrive_Service_Drive_FileFacet $file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFileSystemInfo(SODOneDrive_Service_Drive_FileSystemInfoFacet $fileSystemInfo)
    {
        $this->fileSystemInfo = $fileSystemInfo;
    }

    public function getFileSystemInfo()
    {
        return $this->fileSystemInfo;
    }

    public function setFolder(SODOneDrive_Service_Drive_FolderFacet $folder)
    {
        $this->folder = $folder;
    }

    public function getFolder()
    {
        return $this->folder;
    }

    public function setShared(SODOneDrive_Service_Drive_SharedFacet $shared)
    {
        $this->shared = $shared;
    }

    public function getShared()
    {
        return $this->shared;
    }
}

class SODOneDrive_Service_Drive_SharedFacet extends SODOneDrive_Model
{
    public $scope;

    protected $ownerType = 'SODOneDrive_Service_Drive_IdentitySet';
    protected $ownerDataType = 'array';

    public function setOwner(SODOneDrive_Service_Drive_IdentitySet $owner)
    {
        $this->owner = $owner;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    public function getScope()
    {
        return $this->scope;
    }
}

class SODOneDrive_Service_Drive_Permission extends SODOneDrive_Model
{
    public $id;
    public $roles;
    public $shareId;
    public $expirationDateTime;
    public $hasPassword;
    protected $grantedToType = 'SODOneDrive_Service_Drive_IdentitySet';
    protected $grantedToDataType = 'array';
    protected $linkType = 'SODOneDrive_Service_Drive_SharingLink';
    protected $linkDataType = '';
    protected $inheritedFromType = 'SODOneDrive_Service_Drive_ItemReference';
    protected $inheritedFromDataType = '';

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setExpirationDateTime($expirationDateTime)
    {
        $this->expirationDateTime = $expirationDateTime;
    }

    public function getExpirationDateTime()
    {
        return $this->expirationDateTime;
    }

    public function getHasPassword()
    {
        return $this->hasPassword;
    }

    public function setHasPassword($hasPassword)
    {
        $this->hasPassword = $hasPassword;
    }

    public function setGrantedTo(SODOneDrive_Service_Drive_IdentitySet $grantedTo)
    {
        $this->grantedTo = $grantedTo;
    }

    public function getGrantedTo()
    {
        return $this->grantedTo;
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setLink(SODOneDrive_Service_Drive_SharingLink $link)
    {
        $this->link = $link;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setInheritedFrom(SODOneDrive_Service_Drive_ItemReference $inheritedFrom)
    {
        $this->inheritedFrom = $inheritedFrom;
    }

    public function getInheritedFrom()
    {
        return $this->inheritedFrom;
    }

    public function setShareId($shareId)
    {
        $this->shareId = $shareId;
    }

    public function getShareId()
    {
        return $this->shareId;
    }
}

class SODOneDrive_Service_Drive_SharingLink extends SODOneDrive_Model
{
    public $token;
    public $scope;
    public $preventsDownload;
    public $webUrl;
    public $type;
    protected $applicationType = 'SODOneDrive_Service_Drive_Identity';
    protected $applicationDataType = '';

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setWebUrl($webUrl)
    {
        $this->webUrl = $webUrl;
    }

    public function getWebUrl()
    {
        return $this->webUrl;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function setPreventsDownload($preventsDownload)
    {
        $this->preventsDownload = $preventsDownload;
    }

    public function getPreventsDownload()
    {
        return $this->preventsDownload;
    }
    public function setApplication(SODOneDrive_Service_Drive_Identity $application)
    {
        $this->application = $application;
    }

    public function getApplication()
    {
        return $this->application;
    }
}

class SODOneDrive_Service_Drive_PreviewUrl extends SODOneDrive_Model
{
    public $getUrl;
    public $postUrl;
    public $postParameters;

    public function getUrl()
    {
        return $this->getUrl;
    }

    public function getPostUrl()
    {
        return $this->postUrl;
    }

    public function getPostParameters()
    {
        return $this->postParameters;
    }
}

class SODOneDrive_Service_Drive_ThumbnailSet extends SODOneDrive_Model
{
    public $id;
    protected $smallType = 'SODOneDrive_Service_Drive_Thumbnail';
    protected $smallDataType = '';
    protected $c48x48Type = 'SODOneDrive_Service_Drive_Thumbnail';
    protected $c48x48DataType = '';
    protected $mediumType = 'SODOneDrive_Service_Drive_Thumbnail';
    protected $mediumDataType = '';
    protected $largeType = 'SODOneDrive_Service_Drive_Thumbnail';
    protected $largeDataType = '';
    protected $c1500x1500Type = 'SODOneDrive_Service_Drive_Thumbnail';
    protected $c1500x1500DataType = '';

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setSmall(SODOneDrive_Service_Drive_Thumbnail $small)
    {
        $this->small = $small;
    }

    public function getSmall()
    {
        return $this->small;
    }

    public function setC48x48(SODOneDrive_Service_Drive_Thumbnail $c48x48)
    {
        $this->c48x48 = $c48x48;
    }

    public function getC48x48()
    {
        return $this->c48x48;
    }

    public function setMedium(SODOneDrive_Service_Drive_Thumbnail $medium)
    {
        $this->medium = $medium;
    }

    public function getMedium()
    {
        return $this->medium;
    }

    public function setLarge(SODOneDrive_Service_Drive_Thumbnail $large)
    {
        $this->large = $large;
    }

    public function getLarge()
    {
        return $this->large;
    }

    public function setC1500x1500(SODOneDrive_Service_Drive_Thumbnail $c1500x1500)
    {
        $this->c1500x1500 = $c1500x1500;
    }

    public function getC1500x1500()
    {
        return $this->c1500x1500;
    }
}

class SODOneDrive_Service_Drive_Thumbnail extends SODOneDrive_Model
{
    public $width;
    public $height;
    public $url;
    public $sourceItemId;

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setSourceItemId($sourceItemId)
    {
        $this->sourceItemId = $sourceItemId;
    }

    public function getSourceItemId()
    {
        return $this->sourceItemId;
    }
}

class SODOneDrive_Service_Drive_FileList extends SODOneDrive_Collection
{
    protected $valueType = 'SODOneDrive_Service_Drive_Item';
    protected $valueDataType = 'array';

    public function setValue(SODOneDrive_Service_Drive_Item $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}

class SODOneDrive_Service_Drive_Changes extends SODOneDrive_Collection
{
    protected $valueType = 'SODOneDrive_Service_Drive_Item';
    protected $valueDataType = 'array';

    public function setValue(SODOneDrive_Service_Drive_Item $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}

class SODOneDrive_Service_Drives_FileList extends SODOneDrive_Collection
{
    protected $valueType = 'SODOneDrive_Service_Drive_Item';
    protected $valueDataType = 'array';

    public function setValue(SODOneDrive_Service_Drive_Item $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
