<?php

require_once(dirname(__FILE__) .'/BoxConfig.class.php');
require_once(dirname(__FILE__) .'/BoxParams.class.php');

class BoxOAuth{

	public static function refreshToken($refreshToken = ''){

		/* Set params */
		$params = array();
		$params['grant_type'] = 'refresh_token';
		$params['refresh_token'] = BoxParams::getRefreshToken();
		$params['client_id'] = BoxConfig::getApplicationId();
		$params['client_secret'] = BoxConfig::getApplicationSecret();

		/* Set curl request */
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($curl, CURLOPT_URL, BoxConfig::getTokenUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		/* Send request */
		$response['content'] = curl_exec($curl);
		$response['status'] = curl_getinfo($curl);

		switch($response['status']['http_code']){
			case 200:
				/* Should be ok ! */
				$content = json_decode($response['content'], true);
				if(isset($content['access_token'], $content['refresh_token'])){
					// echo 'OK' ."\n";
					BoxParams::setAccessToken($content['access_token']);
					BoxParams::setRefreshToken($content['refresh_token']);
				}
				else{
					throw new BoxOAuthException('Refresh Token Error : ' .$response['content'], 401);
				}
				break;
				

			default:
				throw new BoxOAuthException('Refresh Token Error : ' .$response['content'], 401);
		}
	}
}

class BoxOAuthException extends Exception{}