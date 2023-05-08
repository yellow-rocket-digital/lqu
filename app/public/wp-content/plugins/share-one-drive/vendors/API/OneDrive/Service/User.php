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
class SODOneDrive_Service_User extends SODOneDrive_Service {

    const DRIVE = "https://api.onedrive.com/v1.0/me/";

    public $me;

    /**
     * Constructs the internal representation of the Drive service.
     *
     * @param SODOneDrive_Client $client
     */
    public function __construct(SODOneDrive_Client $client) {
        parent::__construct($client);
        $this->servicePath = 'v1.0/me/';
        $this->version = 'v1.0';
        $this->serviceName = 'me';

        $this->me = new SODOneDrive_Service_User_Me_Resource(
                $this, $this->serviceName, 'me', array(
            'methods' => array(
                'get' => array(
                    'path' => '',
                    'httpMethod' => 'GET',
                    'parameters' => array(
                        '$select' => array(
                            'location' => 'query',
                            'type' => 'string',
                        ),
                        'expand' => array(
                            'location' => 'query',
                            'type' => 'string',
                        )
                    ),
                ),
                'photometa' => array(
                    'path' => 'photos/<format>',
                    'httpMethod' => 'GET',
                    'parameters' => array(
                        'format' => array(
                            'location' => 'path',
                            'type' => 'string',
                            'required' => true,
                        )
                    ),
                ),
                'photo' => array(
                    'path' => 'photos/<format>/$value',
                    'httpMethod' => 'GET',
                    'parameters' => array(
                        'format' => array(
                            'location' => 'path',
                            'type' => 'string',
                            'required' => true,
                        )
                    ),
                ),
            )
                )
        );
    }

}

/**
 * The "about" collection of methods.
 * Typical usage is:
 *  <code>
 *   $driveService = new SODOneDrive_Service_Drive(...);
 *   $about = $driveService->about;
 *  </code>
 */
class SODOneDrive_Service_User_Me_Resource extends SODOneDrive_Service_Resource {

    /**
     * Gets the information about the current user along with Drive API settings
     * (about.get)
     *
     * @param array $optParams Optional parameters.
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
     * @return SODOneDrive_Service_Drive_Me
     */
    public function get($optParams = array()) {
        $params = array();
        $params = array_merge($params, $optParams);
        return $this->call('get', array($params), "SODOneDrive_Service_User_Me");
    }

    /**
     * @return string
     */
    public function photo($format, $optParams = array()) {
        $params = array('format' => $format);
        $params = array_merge($params, $optParams);
        return $this->call('photo', array($params));
    }

    public function photometa($format, $optParams = array()) {
        $params = array('format' => $format);
        $params = array_merge($params, $optParams);
        return $this->call('photometa', array($params), "SODOneDrive_Service_User_ProfilePhoto");
    }

}

class SODOneDrive_Service_User_Me extends SODOneDrive_Collection {

    public $displayName;
    public $surname;
    public $givenName;
    public $id;
    public $userPrincipalName;
    public $jobTitle;
    public $mail;
    public $mobilePhone;
    public $officeLocation;
    public $preferredLanguage;

    function getDisplayName() {
        return $this->displayName;
    }

    function getSurname() {
        return $this->surname;
    }

    function getGivenName() {
        return $this->givenName;
    }

    function getId() {
        return $this->id;
    }

    function getUserPrincipalName() {
        return $this->userPrincipalName;
    }

    function getJobTitle() {
        return $this->jobTitle;
    }

    function getMail() {
        return $this->mail;
    }

    function getMobilePhone() {
        return $this->mobilePhone;
    }

    function getOfficeLocation() {
        return $this->officeLocation;
    }

    function getPreferredLanguage() {
        return $this->preferredLanguage;
    }

    function setDisplayName($displayName) {
        $this->displayName = $displayName;
    }

    function setSurname($surname) {
        $this->surname = $surname;
    }

    function setGivenName($givenName) {
        $this->givenName = $givenName;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setUserPrincipalName($userPrincipalName) {
        $this->userPrincipalName = $userPrincipalName;
    }

    function setJobTitle($jobTitle) {
        $this->jobTitle = $jobTitle;
    }

    function setMail($mail) {
        $this->mail = $mail;
    }

    function setMobilePhone($mobilePhone) {
        $this->mobilePhone = $mobilePhone;
    }

    function setOfficeLocation($officeLocation) {
        $this->officeLocation = $officeLocation;
    }

    function setPreferredLanguage($preferredLanguage) {
        $this->preferredLanguage = $preferredLanguage;
    }

}

class SODOneDrive_Service_User_ProfilePhoto extends SODOneDrive_Collection {

    public $id;
    public $height;
    public $width;

    function getId() {
        return $this->id;
    }

    function getHeight() {
        return $this->height;
    }

    function getWidth() {
        return $this->width;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setHeight($height) {
        $this->height = $height;
    }

    function setWidth($width) {
        $this->width = $width;
    }

}
