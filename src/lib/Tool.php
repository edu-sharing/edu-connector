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

    protected function getNode() {
        $node = $this->apiClient->getNode($_SESSION[$this->connectorId]['node']);

        if(in_array('ccm:collection_io_reference', $node->node->aspects)) {
            $originalId = $node->node->properties->{'ccm:original'}[0];
            $node = $this->apiClient->getNode($originalId);
        }

        return $node;

    }
}