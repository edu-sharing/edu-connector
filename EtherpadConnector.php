<?php

require_once 'EduRestClient.php';

class EtherpadConnector extends EduRestClient {
	
	const TOOL = 'edu-tool-etherpad';
	
	
	public function __construct() {
		
	}
	
	public function run() {

		if(empty($_GET['title']))
			$this -> showDialog();
		
		$courseId = $resource_link_id = uniqid('etherpad_');
		$userId = $fname = 'admin';
		
		$params = '?fname=' . $fname . '&course_id=' . $courseId . '&resource_link_id=' . $resource_link_id . '&user_id=' . $userId;
		
		$padUrl = WWWROOT . '/etherpad/' . $params;
		
		$this -> createReference(self::TOOL, trim($_GET['title']), $padUrl);
		
		header('HTTP/1.1 303 See other');
		header('Location: ' . $padUrl);
		
		exit(0);
		
	}

	
	public function showDialog() {
		include('./view/etherpad.phtml');
		exit(0);
	}
	
	
	
}