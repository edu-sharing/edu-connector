<?php


die('use testclient.php');


define('CONNECTOR_URL', 'http://138.201.17.74/eduConDev/');


class client {
    
    public function callConnector($params) {
        $url = CONNECTOR_URL;
        $url .= '?tool=' . $params['tool'];
        if(!empty($params['nodeid']))
        	$url .= '&nodeid=' . $params['nodeid'];
        header('Location: ' . $url);
    }
}

$client = new Client();
//$client -> callConnector(array('nodeid' => 'a32a030f-d361-4390-8682-d48e2d47f603', 'tool' => 'moodle'));


header('Location: ' . CONNECTOR_URL . '?tool=edu-tool-onlyoffice&nodeid=91aaf89d-b628-45fe-8d64-bb77e14464f5&doctype=docx');
/*
$client -> callConnector(array(
    'nodeid' => '56870c08-952a-4ce9-ad18-d0eac66808c5',
    'tool' => 'edu-tool-onlyoffice',
    'doctype' => 'docx'));

*/