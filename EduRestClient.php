<?php

require_once 'config.php';

class EduRestClient {
	
	
	public function __construct() {

	}
	
	private function getAccessToken() {

		if(($_SESSION['oauth_expires_in'] + $_SESSION['oauth_token_received'] - time()) < 300) {
			$this -> refreshToken();
		}
		return $_SESSION['oauth_access_token'];

	}

	private function refreshToken() {

		$url = $_SESSION['api_url'] . './../oauth2/token';
		$postFields = 'grant_type=refresh_token&client_id=eduApp&client_secret=secret&refresh_token=' . $_SESSION['oauth_refresh_token'];
		$headers = array('Accept: application/json');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$return = curl_exec($ch);
		$httpcode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		curl_close($ch);

		if ($httpcode >= 200 && $httpcode < 300) {
			$_SESSION['oauth_access_token'] = $return->access_token;
            $_SESSION['oauth_refresh_token'] = $return->refresh_token;
            $_SESSION['oauth_expires_in'] = $return->expires_in;
            $_SESSION['oauth_token_received'] = time();
			return true;
		}
		error_log('Error refreshing tokens - HTTP Status ' . $httpcode);
		return false;
	
	}
/*
	public function createNode($title) {

		$url = $_SESSION['api_url'] . 'node/v1/nodes/-home-/' . $_SESSION['parent_id'] . '/children?type=%7Bhttp%3A%2F%2Fwww.campuscontent.de%2Fmodel%2F1.0%7Dio';
		$ch = curl_init($url);
		$headers = array('Authorization: Bearer '. $this->getAccessToken(), 'Accept: application/json', 'Content-Type: application/json; charset=utf-8');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, '{"{http://www.alfresco.org/model/content/1.0}name": ["'.$title.'"]}');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		$httpcode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		curl_close($ch);
		if ($httpcode >= 200 && $httpcode < 300) {
			$node = json_decode($res);
			return $node->node->ref->id;
		}
		error_log('Error creating io node - HTTP Status ' . $httpcode);
		return false;
		
	}*/
	
	public function createContentNode($nodeId, $contentpath, $mimetype) {

		$versionComment = '';
		$cfile = curl_file_create($contentpath, $mimetype, 'file');

		$fields = array('file' => $cfile);
		$ch = curl_init($_SESSION['api_url'] . 'node/v1/nodes/-home-/' . $nodeId . '/content?versionComment=' . $versionComment . '&mimetype=' . $mimetype);
		$headers = array('Authorization: Bearer '. $this->getAccessToken(), 'Accept: application/json', 'Content-Type: multipart/form-data');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SAFE_UPLOAD, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		$httpcode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		curl_close($ch);

		if ($httpcode >= 200 && $httpcode < 300) {
			return json_decode($res);
		}
		error_log('Error creating content node - HTTP Status ' . $httpcode);
		return false;
	} 


	public function updateReferenceUrl($nodeId, $url) {
		
		$fields = '{"ccm:wwwurl":["'.$url.'"]}';
		
		$ch = curl_init($_SESSION['api_url'] . 'node/v1/nodes/-home-/'.$nodeId.'/metadata');
		
		$headers = array('Authorization: Bearer '. $this->getAccessToken(), 'Accept: application/json', 'Content-Type: application/json; charset=utf-8');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");

		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		$httpcode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		curl_close($ch);

		if ($httpcode >= 200 && $httpcode < 300) {
			return true;
		}
		echo 'Error updating node';
		return false;		
	}
	

	/*CHECK
	MAY NOT WORK SINCE JSON ENCODED ARRAYS WON'T BE ACCEPTED BY REPOSITORY IN createNode()
	
	public function createReference($tool, $title, $url) {
	
		$fields = array(
				array('name' => '{http://www.alfresco.org/model/content/1.0}name', 'values' => array($title)),
				array('name' => '{http://www.campuscontent.de/model/1.0}wwwurl', 'values' => array($url))
		);
		
		$ch = curl_init($_SESSION['api_url'] . 'node/v1/nodes/-home-/' . $_SESSION['parent_id'] . '/children?type=%7Bhttp%3A%2F%2Fwww.campuscontent.de%2Fmodel%2F1.0%7Dio');
		
		$headers = array('Authorization: Bearer '. $this->getAccessToken(), 'Accept: application/json', 'Content-Type: application/json; charset=utf-8');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		$httpcode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		curl_close($ch);

		if ($httpcode >= 200 && $httpcode < 300) {
			return true;
		}
		echo 'Error setting node';
		return false;
	}
*/

	public function getNode($nodeId) {
		$ch = curl_init($_SESSION['api_url'] . 'node/v1/nodes/-home-/'.$nodeId.'/metadata');
		$headers = array('Authorization: Bearer '. $this->getAccessToken(), 'Accept: application/json');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		
		if($res === false) {
			echo 'Cannot reach API';
			exit();
		}
		$httpcode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		curl_close($ch);
		if ($httpcode >= 200 && $httpcode < 300) {
			$node = json_decode($res);
			return $node;
		}
		echo 'Error fetching node - HTTP STATUS ' . $httpcode;
		exit();
	}

		

protected function getPerson() {
		$ch = curl_init($_SESSION['api_url'] . 'iam/v1/people/-home-/-me-');
		$headers = array('Authorization: Bearer '. $this->getAccessToken(), 'Accept: application/json');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		$httpcode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		curl_close($ch);
		if ($httpcode >= 200 && $httpcode < 300) {
			$person = json_decode($res);
			return $person->person;
		}
		echo 'Error fetching person';
		return false;
		
	}
	
	
}
