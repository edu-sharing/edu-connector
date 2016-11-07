<?php 

define('REPOURL', 'http://appserver7.metaventis.com:7001/edu-sharing/');
$homeNodeId = '74b565d2-217b-4cb6-ace8-33b01ed6897b';

$fields = array(
		array('name' => '{http://www.alfresco.org/model/content/1.0}name', 'values' => array('title')),
		array('name' => '{http://www.campuscontent.de/model/1.0}wwwurl', 'values' => array('url'))
);

//var_dump(json_encode($fields));

//$ch = curl_init(REPOURL . '/rest/node/v1/nodes/-home-/' . $homeNodeId . '/children?type=%7Bhttp%3A%2F%2Fwww.campuscontent.de%2Fmodel%2F1.0%7Dio');


echo REPOURL . '/rest/node/v1/nodes/-home-/' . $homeNodeId . '/children?type=%7Bhttp%3A%2F%2Fwww.campuscontent.de%2Fmodel%2F1.0%7Dio';
echo '<br/>http://appserver7.metaventis.com:7001/edu-sharing/rest/node/v1/nodes/-home-/74b565d2-217b-4cb6-ace8-33b01ed6897b/children?type=%7Bhttp%3A%2F%2Fwww.campuscontent.de%2Fmodel%2F1.0%7Dio';
die();

$ch = curl_init('http://appserver7.metaventis.com:7001/edu-sharing/rest/node/v1/nodes/-home-/74b565d2-217b-4cb6-ace8-33b01ed6897b/children?type=%7Bhttp%3A%2F%2Fwww.campuscontent.de%2Fmodel%2F1.0%7Dio');

$headers = array('Authorization: Basic '. base64_encode("admin:admin"), 'Accept: application/json', 'Content-Type: application/json');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		$httpcode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		curl_close($ch);
		echo '#' . $httpcode . '#';
		if ($httpcode >= 200 && $httpcode < 300) {
			var_dump($res);
		}