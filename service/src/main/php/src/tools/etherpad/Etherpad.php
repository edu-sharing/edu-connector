<?php

namespace php\src\tools\etherpad;

class Etherpad extends \php\src\lib\Tool {

	public function run() {

		$courseId = $resource_link_id = $_SESSION[$this->connectorId]['node']->node->ref->id;
		$userId = $fname = $_SESSION[$this->connectorId]['user']->userName;
		$params = '?fname=' . $fname . '&course_id=' . $courseId . '&resource_link_id=' . $resource_link_id . '&user_id=' . $userId;		
		$padUrl = WWWURL . '/src/tools/etherpad/' . $params;


		//donot save pad url as wwwurl because it contains the user
		//if(!empty($node->node->properties->{'ccm:wwwurl'}[0])) {
		//	$this->updateReferenceUrl($_REQUEST['node'], $padUrl);
		//}

		header('HTTP/1.1 303 See other');
		header('Location: ' . $padUrl);	
		exit(0);
	}
}