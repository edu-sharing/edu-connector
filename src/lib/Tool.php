<?php

namespace connector\lib;

class Tool {

    protected $apiClient;
    protected $log;
    protected $connectorId;

    public function __construct($apiClient, $log, $connectorId) {
        $this->apiClient = $apiClient;
        $this->log = $log;
        $this->connectorId = $connectorId;
    }

    //request paramater "node" is just the node ID - fetch node object
    public function setNode() {
        $_SESSION[$this->connectorId]['node'] = $this->getNode();
    }

    protected function getNode() {
        $node = $this->apiClient->getNode($_SESSION[$this->connectorId]['node']);
        return $node;
    }
}