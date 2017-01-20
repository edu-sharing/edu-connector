<?php

require_once 'EduRestClient.php';

class EtherpadConnector extends EduRestClient {
	
	public function __construct() {
		
	}
	
	public function run() {

		//if(empty($_GET['title']))
		//	$this -> showDialog();


		$node = $this->getNode($_REQUEST['node']);
		$person = $this->getPerson();
		$courseId = $resource_link_id = $_REQUEST['node'];
		$userId = $fname = $person->userName;
		$params = '?fname=' . $fname . '&course_id=' . $courseId . '&resource_link_id=' . $resource_link_id . '&user_id=' . $userId;		
		$padUrl = WWWROOT . '/etherpad/' . $params;

		//if(!empty($node->node->properties->{'ccm:wwwurl'}[0])) {
			$this->updateReferenceUrl($_REQUEST['node'], $padUrl);
		//}


		header('HTTP/1.1 303 See other');
		header('Location: ' . $padUrl);	
		exit(0);


		//var_dump($person->userName);die();


	}

	
	public function showDialog() {
		include('./view/etherpad.phtml');
		exit(0);
	}
	
	
	
}