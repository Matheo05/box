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
		try{
			$response = $this->core->setRequest('folders/' .$folderId .'/items')->setParams(array('limit' => 1000))->send();
			return json_decode($response['content'],1);
		}
		catch(BoxCoreException $exception){
			throw new BoxException($exception->getMessage(), $exception->getCode());
		}
	}

	public function createFolder($folderName, $parentFolderId = 0){
		try{
			$response = $this->core->setMethod('POST')->setRequest('folders')->setBody(json_encode(array('name' => $folderName, 'parent' => array('id' => $parentFolderId))))->send();
			return json_decode($response['content'],1);
		}
		catch(BoxCoreException $exception){
			throw new BoxException($exception->getMessage(), $exception->getCode());
		}
	}


	/******************
	 * PRIVATE METHODS
	 *****************/
}

class BoxException extends Exception{}