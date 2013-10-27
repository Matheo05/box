<?php

require_once(dirname(__FILE__) .'/BoxOAuth.class.php');
require_once(dirname(__FILE__) .'/BoxParams.class.php');

class BoxCore{

	const DEFAULT_METHOD = 'GET';

	protected static $baseUrl;
	protected static $method;
	protected static $request;
	protected static $params;
	protected static $headers;
	// protected $accessToken;

	public function __construct(){
		$this->reset();
	}

	public function setBaseUrl($baseUrl = ''){

		$cleanBaseUrl = filter_var($baseUrl, FILTER_VALIDATE_URL);
		if($cleanBaseUrl !== false){
			$this->baseUrl = $cleanBaseUrl;
		}
		else{
			$this->baseUrl = BoxConfig::getApiUrl();
		}
		return $this;
	}

	/**
	 * Function to set the HTTP method to use. Default is GET
	 * @access private
	 * @param string $method The HTTP method
	 * @return \BoxCore To allow chaining
	 */
	public function setMethod($method = self::DEFAULT_METHOD) {
	    $this->method = $method;
	    return $this;
	}

	/**
	 * Function to set the HTTP request to use.
	 * @access private
	 * @param string $method The HTTP request
	 * @return \BoxCore To allow chaining
	 */
	public function setRequest($request = '') {
	    $this->request = $request;
	    return $this;
	}

	/**
	 * Function to set the HTTP params. In the body for POST and in the URL for GET request
	 * @access private
	 * @param array $method An array where each  key is the name of the param and the element is the value
	 * @return \BoxCore To allow chaining
	 */
	public function setParams($params = array()) {
	    // $this->params = http_build_query($params);
	    $this->params = $params;
	    return $this;
	}

	/**
	 * Function to set the HTTP request body.
	 * @access private
	 * @param string $body The string to set
	 * @return \BoxCore To allow chaining
	 */
	public function setBody($body = '') {
	    $this->params = $body;
	    return $this;
	}

	public function setHeaders($headers = array()){
		if(!empty($headers)){
			$this->headers = $headers;
		}
		else{
			$this->headers = array();
		}
		return $this;
	}

	/**
	 * Send the request to the Box REST API
	 * @access private
	 * @return array An array with 'status' and 'content'
	 */
	public function send() {

	    $returnValue = array('status' => '', 'content' => '');

	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_HEADER, false);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_URL, $this->baseUrl.$this->request);
	    // curl_setopt($curl, CURLOPT_VERBOSE, true);
	    // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	    $contentType = 'application/json';

	    /* Set method */
	    switch($this->method) {
			case 'GET':
			    curl_setopt($curl, CURLOPT_HTTPGET, true);
			    curl_setopt($curl, CURLOPT_URL, $this->baseUrl.$this->request.'?'.http_build_query($this->params));
			    break;

			case 'POST':
			    curl_setopt($curl, CURLOPT_POST, true);
			    curl_setopt($curl, CURLOPT_POSTFIELDS, $this->params);
			    $contentType = 'multipart/form-data';
			    break;
	    }
	    /* Set headers */
	    curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge($this->headers, array('Content-type: '.$contentType, 'Authorization: Bearer '.BoxParams::getAccessToken())));

	    /* Send */
	    $returnValue['content'] = curl_exec($curl);
	    $returnValue['status'] = curl_getinfo($curl);
	    //echo 'Response : ' .print_r($returnValue,1) ."\n";
	    //die();

	    /* Check if session expired */
	    if (isset($returnValue['status']['http_code']) && $returnValue['status']['http_code'] == 401) {
		
			switch($returnValue['status']['http_code']){
				case 200:
				case 201:
					$this->reset();
					return $returnValue;
					break;

				case 401:
					try{
						BoxOAuth::refreshToken();
						// echo 'BoxCore::send() -> auto resend !' ."\n";
						return $this->send();
					}
					catch(BoxOAuthException $exception){
						throw new BoxCoreException($exception->getMessage(), $exception->getCode());
					}
					break;

				default:
					throw new BoxCoreException('Unsupported HTTP code : ' .$returnValue['content'], $returnValue['status']['http_code']);
			}
	    }

	    /* Clean current values */
	    $this->reset();
	    return $returnValue;
	}

	/******************
	 * PRIVATE METHODS
	 *****************/

	/**
	 * Reset the request info to default values
	 * @access private
	 */
	private function reset() {
	    $this->setBaseUrl()->setMethod()->setRequest()->setParams()->setHeaders();
	}

}


class BoxCoreException extends Exception{}