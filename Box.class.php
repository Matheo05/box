<?php 
require_once (dirname(__FILE__) .'/BoxCore.class.php');

class Box{

	protected $core;

	public function __construct(){
		try{
			$this->core = new BoxCore();
		}
		catch(BoxCoreException $exception){
			throw new BoxException($exception->getMessage(), $exception->getCode());
		}
	}

	public function readFolder($folderId = 0){
		/* Config and send the request */
		$this->core->setRequest('folders/' .$folderId .'/items')->setParams(array('limit' => 1000));
		$response = $this->send();

		/* Return content */
		return json_decode($response['content'],1);
	}

	public function createFolder($folderName, $parentFolderId = 0){
		/* Config and send the request */
		$this->core->setMethod('POST')->setRequest('folders')->setBody(json_encode(array('name' => $folderName, 'parent' => array('id' => $parentFolderId))));
		$response = $this->send();

		/* Return content */
		return json_decode($response['content'], true);
	}

	public function loadFolderByName($folderName, $parentFolderId = 0){
		/* Config and send the request */
		$this->core->setRequest('folders/' .$parentFolderId .'/items')->setParams(array('limit' => 1000));
		$response = $this->send();

		/* Decode response */		
		$items = json_decode($response['content'], true);
		// echo 'Items : ' .print_r($items,1) ."\n";
		/* Parse items */
		foreach($items['entries'] as $entry){

			/* Check type */
			if(strcmp('folder', $entry['type']) == 0){
				
				/* Check name */
				if(strcmp($folderName, $entry['name']) == 0){
					return $entry['id'];
				}
			}
		}

		/* If not found */
		return false;
	}

	public function uploadFile($srcFile, $dstFolderId = 0){
		/* Config and send the request */
		$this->core->setBaseUrl(BoxConfig::getUploadUrl())->setMethod('POST')->setRequest('files/content');
		$this->core->setParams(array('filename' => '@' .$srcFile, 'parent_id' => $dstFolderId));
		$this->core->setHeaders(array('Content-MD5: ' .sha1_file($srcFile)));
		$response = $this->send();

		/* Return content */
		return json_decode($response['content'],1);
	}


	/******************
	 * PRIVATE METHODS
	 *****************/

	/**
 	 * Method calling the BoxCore::send() method.
 	 * Mainly done to avoid to manage exception everywhere
 	 * @access private
 	 * @return array An array with 'status' and 'content'.
 	 * @throws BoxException  
 	 */
	private function send(){
		try{
			return $this->core->send();
		}
		catch(BoxCoreException $exception){
			throw new BoxException($exception->getMessage(), $exception->getCode());
		}
	}
}

class BoxException extends Exception{}