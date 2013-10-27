<?php

class BoxParams{


	const FILE_NAME = 'box.params.json';

	protected static $isFileLoaded = false;

	protected static $params = array();


	public static function getAccessToken(){
		return self::getParam('accessToken');
	}

	public static function getRefreshToken(){
		return self::getParam('refreshToken');
	}

	public static function setAccessToken($accessToken){
		return self::setParam('accessToken', $accessToken);
	}

	public static function setRefreshToken($refreshToken){
		return self::setParam('refreshToken', $refreshToken);
	}


	/******************
	 * PRIVATE METHODS
	 *****************/


	private static function getParam($param){
		self::readFile();
		if(isset(self::$params[$param])){
			return self::$params[$param];
		}
		return '';
	}

	private static function setParam($param, $value){
		self::readFile();
		self::$params[$param] = $value;
		self::writeFile();
	}

	private static function readFile($force = false){

		if(self::$isFileLoaded === false || $force === true){
			self::$params = json_decode(file_get_contents(dirname(__FILE__) .'/' .self::FILE_NAME), true);
			self::$isFileLoaded = true;
		}
	}

	private static function writeFile(){

		if(self::$isFileLoaded === true){
			file_put_contents(dirname(__FILE__) .'/' .self::FILE_NAME, json_encode(self::$params));
			// echo 'Params saved !' ."\n";
		}
	}
}

class BoxParamsException extends Exception{}