<?php

class Connector {

    private $tool;
    private $data = array();

    public function __construct() {
        $this -> setParameters();
        $this -> switchTool();
        $this -> runTool();
    }


    private function setParameters() {
        $parameters = $this->extractParameters();
    }

    private function extractParameters() {
        $encrypted = $_REQUEST['data'];
        $decrypted = $this->decryptData($encrypted);
        $this->validate();
    }

    private function decryptData($encrypted) {
        $privateKey = $this->getPrivateKey();
    }

    private function getPrivateKey() {
        
    }

    private function validate() {

    }

    private function switchTool() {
        switch($_REQUEST['tool']) {
            case 'ONLY_OFFICE':
                $this -> tool = new OnlyOffice();
            break;
            default:
                echo 'Unknown tool';
            exit(0);
        }
    }

    private function runTool() {
    $this -> tool -> run();
    }
}