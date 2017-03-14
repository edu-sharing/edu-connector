<?php

require_once 'config.php';
require_once 'EduRestClient.php';


class OnyxConnector extends EduRestClient {
	
	private $person;
    
    public function __construct() {

		if(isset($_REQUEST['node'])) {
			$this->person = $this->getPerson();
			$this -> forwardToEditor();	
		} else {
		    echo 'Missing parameter "node"';
		    exit();
        }
    }
	
	private function forwardToEditor() {
		header('Location: ' . ONYXURL . '?repository=' . $this->getRepoId() . '&hash=' . urlencode($this->getHash()));
		exit();
	}
	
	private function encrypt($data) {
		$publicKey = openssl_pkey_get_public(ONYXPUP);
		$encrypted = '';
		openssl_seal($data, $sealed, $ekeys, array($publicKey));
		openssl_free_key($publicKey);
		if(empty($sealed)) {
			echo 'Encryption error';
			exit();
		}
		return $sealed . '::' . $ekeys[0];
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
