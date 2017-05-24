<?php

namespace connector\lib;

class Tool {

    protected $apiClient;
    protected $log;

    public function __construct($apiClient, $log) {
        $this->apiClient = $apiClient;
        $this->log = $log;
    }

    public function setNode() {
        $node = $this->apiClient->getNode($_SESSION['node']);
        $_SESSION['node'] = $node;
    }
}