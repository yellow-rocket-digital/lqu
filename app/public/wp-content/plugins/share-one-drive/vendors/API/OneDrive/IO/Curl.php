<?php

/*
 * Copyright 2014 OneDrive Inc.
 * Copyright 2015 www.wpcloudplugins.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Curl based implementation of SODOneDrive_IO.
 *
 * @author Stuart Langley <slangley@google.com>
 */
if (!class_exists('SODOneDrive_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}

class SODOneDrive_IO_Curl extends SODOneDrive_IO_Abstract {

  // cURL hex representation of version 7.30.0
  const NO_QUIRK_VERSION = 0x071E00;

  private $options = array();
  
  private $handler;
 
  public function __construct(SODOneDrive_Client $client) {
    if (!extension_loaded('curl')) {
      $error = 'The cURL IO handler requires the cURL extension to be enabled';
      $client->getLogger()->critical($error);
      throw new SODOneDrive_IO_Exception($error);
    }

    parent::__construct($client);
  }

  public function getHandler(){

    if (!is_resource($this->handler) || 'curl' !== get_resource_type($this->handler)) {
        $this->handler = curl_init();
    // FIX cURL error 2: easy handle already used in multi handle
    //    } elseif (function_exists('curl_reset')){
    //        curl_reset($this->handler);
    } else {
        curl_close($this->handler);
        $this->handler = curl_init();
    }
    
    return $this->handler;
  }
    
  /**
   * Execute an HTTP Request
   *
   * @param SODOneDrive_Http_Request $request the http request to be executed
   * @return array containing response headers, body, and http code
   * @throws SODOneDrive_IO_Exception on curl or IO error
   */
  public function executeRequest(SODOneDrive_Http_Request $request) {
    $curl = $this->getHandler();
      
    curl_setopt($curl, CURLOPT_POSTFIELDS, null);

    if ($request->getPostBody()) {
      curl_setopt($curl, CURLOPT_POSTFIELDS, $request->getPostBody());
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER,array('x-curl-fix:'  => true));
    $requestHeaders = $request->getRequestHeaders();
    if ($requestHeaders && is_array($requestHeaders)) {
      $curlHeaders = array();
      foreach ($requestHeaders as $k => $v) {
        $curlHeaders[] = "$k: $v";
      }
      curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeaders);
    }

    // Fix problems with servers unable to connect to the API via an IPv6 address
    //curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($curl, CURLOPT_URL, $request->getUrl());

    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request->getRequestMethod());
    curl_setopt($curl, CURLOPT_USERAGENT, $request->getUserAgent());

    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
    if ($request->getFollowLocation()) {
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    }
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    
    // Force TLS 1.2 or higher, required as of 2022
    if (defined('CURL_SSLVERSION_TLSv1_2')) {
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    } else{
      // not always defined in PHP.
      curl_setopt($curl, CURLOPT_SSLVERSION, 6);
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);

    curl_setopt($curl, CURLOPT_ENCODING, null);
    if ($request->canGzip()) {
      curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
    }

    $options = $this->client->getClassConfig('SODOneDrive_IO_Curl', 'options');
    if (is_array($options)) {
      $this->setOptions($options);
    }

    foreach ($this->options as $key => $var) {
      curl_setopt($curl, $key, $var);
    }

    if (!isset($this->options[CURLOPT_CAINFO])) {
      curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . '/cacerts.pem');
    }
    
    
    // DEBUG: Verbose
    // curl_setopt($curl, CURLOPT_VERBOSE, true);
    // $verbose = fopen('php://temp', 'w+');
    // curl_setopt($curl, CURLOPT_STDERR, $verbose);

    $this->client->getLogger()->debug(
            'cURL request', array(
        'url' => $request->getUrl(),
        'method' => $request->getRequestMethod(),
        'headers' => $requestHeaders,
        'body' => $request->getPostBody()
            )
    );

    @set_time_limit(60);
    $response = curl_exec($curl);
        
    // DEBUG: Output Verbose
    // rewind($verbose);
    // $verboseLog = stream_get_contents($verbose);
    // error_log($verboseLog);
     
    if ($response === false) {
      $error = curl_error($curl);
      $code = curl_errno($curl);
      $map = $this->client->getClassConfig('SODOneDrive_IO_Exception', 'retry_map');

      $this->client->getLogger()->error('cURL ' . $error);
      throw new SODOneDrive_IO_Exception($error, $code, null, $map);
    }
    $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

    list($responseHeaders, $responseBody) = $this->parseHttpResponse($response, $headerSize);
    $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $this->client->getLogger()->debug(
            'cURL response', array(
        'code' => $responseCode,
        'headers' => $responseHeaders,
        'body' => $responseBody,
            )
    );

    return array($responseBody, $responseHeaders, $responseCode);
  }

  /**
   * Set options that update the transport implementation's behavior.
   * @param $options
   */
  public function setOptions($options) {
    $this->options = $options + $this->options;
  }

  
  public function clearOptions(){
      $this->options = array();
  }
  /**
   * Set the maximum request time in seconds.
   * @param $timeout in seconds
   */
  public function setTimeout($timeout) {
    // Since this timeout is really for putting a bound on the time
    // we'll set them both to the same. If you need to specify a longer
    // CURLOPT_TIMEOUT, or a higher CONNECTTIMEOUT, the best thing to
    // do is use the setOptions method for the values individually.
    $this->options[CURLOPT_CONNECTTIMEOUT] = $timeout;
    $this->options[CURLOPT_TIMEOUT] = $timeout;
  }

  /**
   * Get the maximum request time in seconds.
   * @return timeout in seconds
   */
  public function getTimeout() {
    return $this->options[CURLOPT_TIMEOUT];
  }

  /**
   * Test for the presence of a cURL header processing bug
   *
   * {@inheritDoc}
   *
   * @return boolean
   */
  protected function needsQuirk() {
    $ver = curl_version();
    $versionNum = $ver['version_number'];
    return $versionNum < SODOneDrive_IO_Curl::NO_QUIRK_VERSION;
  }

}
