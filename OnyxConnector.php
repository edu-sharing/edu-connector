<?php

require_once 'config.php';
require_once 'EduRestClient.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);

class OnyxConnector extends EduRestClient {
	
	const TOOL = 'edu-tool-onyx';

	private $person;
    
    public function __construct() {

		//todo check params

		//if called from index / not from oo ajax script
		if(isset($_REQUEST['node'])) {
			$this->person = $this->getPerson();
			$this -> forwardToEditor();
		}
    }
	
	private function forwardToEditor() {
		header('Location: ' . ONYXURL . '?repository=' . $this->getRepoId() . '&hash=' . urlencode($this->getHash()));
		exit();
	}
	
	private function encrypt($data) {
		$publicKey = openssl_pkey_get_public(ONYXPUP);
		$encrypted = '';
		openssl_public_encrypt($data, $encrypted, $publicKey);
		openssl_free_key($publicKey);
		return $encrypted;
	}

	private function getHash() {
		$hash = new stdClass;
		$hash-> first = $this->person->profile->firstName;
		$hash-> last = $this->person->profile->lastName;
		$hash-> mail = $this->person->profile->email;
		$hash-> inst = $this->person->homeFolder->repo;
		$hash-> username = $this->person->userName;
		$hash-> nodeid = $_REQUEST['node'];
		$hash-> accessToken = $_SESSION['oauth_access_token'];
        $hash-> refreshToken = $_SESSION['oauth_refresh_token'] ;
		$hash = json_encode($hash);
		$hash = $this->encrypt($hash);
		$hash = base64_encode($hash);
		return $hash;
	}


private function getRepoId() {
	return REPOSITORY;
}

	private function run() {

	}


}
