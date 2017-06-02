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

    public function setNode() {
        $node = $this->apiClient->getNode($_SESSION['node']);
        $_SESSION['node'] = $node;
    }
}