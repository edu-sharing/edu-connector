<?php

define('CONNECTOR_URL', 'http://138.201.17.74/eduConnector/');


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
$client -> callConnector(array('nodeid' => 'a32a030f-d361-4390-8682-d48e2d47f603', 'tool' => 'moodle'));
