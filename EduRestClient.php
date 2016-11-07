<?php

class EduRestClient {
	
	
	public function __construct() {

	}
	

	public function createNode($title) {
		
		$homeNodeId = $this -> getHomeNodeId();
	
		$fields = array(
				array('name' => '{http://www.alfresco.org/model/content/1.0}name', 'values' => array($title)),
		);
		
		$ch = curl_init(REPOURL . 'rest/node/v1/nodes/-home-/' . $homeNodeId . '/children?type=%7Bhttp%3A%2F%2Fwww.campuscontent.de%2Fmodel%2F1.0%7Dio');
		
		$headers = array('Authorization: Basic '. base64_encode("admin:admin"), 'Accept: application/json', 'Content-Type: application/json; charset=utf-8');
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
			$node = json_decode($res);
			return $node->node->ref->id;
		}
		error_log('Error creating node');
		return false;
		
	}
	
	public function createContentNode($nodeId, $contentpath, $mimetype) {

		$versionComment = time();
		$cfile = curl_file_create($contentpath, $mimetype, 'file');
		$fields = array('file' => $cfile);
		$ch = curl_init(REPOURL . 'rest/node/v1/nodes/-home-/' . $nodeId . '/content?versionComment=' . $versionComment . '&mimetype=' . $mimetype);
		$headers = array('Authorization: Basic '. base64_encode("admin:admin"), 'Accept: application/json', 'Content-Type: multipart/form-data');
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
		error_log('Error creating content node');
		return false;
	} 
	
	public function createReference($tool, $title, $url) {

		$homeNodeId = $this -> getHomeNodeId();
	
		$fields = array(
				array('name' => '{http://www.alfresco.org/model/content/1.0}name', 'values' => array($title)),
				array('name' => '{http://www.campuscontent.de/model/1.0}wwwurl', 'values' => array($url))
		);
		
		$ch = curl_init(REPOURL . 'rest/node/v1/nodes/-home-/' . $homeNodeId . '/children?type=%7Bhttp%3A%2F%2Fwww.campuscontent.de%2Fmodel%2F1.0%7Dio');
		
		$headers = array('Authorization: Basic '. base64_encode("admin:admin"), 'Accept: application/json', 'Content-Type: application/json; charset=utf-8');
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
	
	private function getHomeNodeId() {
		$ch = curl_init(REPOURL . 'rest/iam/v1/people/-home-/-me-');
		$headers = array('Authorization: Basic '. base64_encode("admin:admin"), 'Accept: application/json');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		$httpcode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		curl_close($ch);
		if ($httpcode >= 200 && $httpcode < 300) {
			$node = json_decode($res);
			return $node->person->homeFolder->id;
		}
		echo 'Error fetching homeNodeId';
		return false;
		
	}
	
	
}
