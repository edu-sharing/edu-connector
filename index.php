<?php

error_reporting(E_ERROR);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'MoodleConnector.php';
require_once 'EtherpadConnector.php';
require_once 'OnlyOfficeConnector.php';

class ConnectorSwitch {
       
    private $tool;
    
    public function __construct() {
        
    }
    
    public function switchTool() {
        switch($_GET['tool']) {
            case 'edu-szenario-moodle':
                $this -> tool = new MoodleConnector();
            break;
            case 'edu-tool-etherpad':
                $this -> tool = new EtherpadConnector();
            break;
			//nur über skript mit entspr. parametern ansprechbar, pausiert, da nun erst die authentifizierung her muss (lti)
			case 'edu-tool-onlyoffice':
				$this -> tool = new OnlyOfficeConnector();
            break;
            default:
                echo 'Unknown tool';
                exit(0);
        }
    }
    
    public function runTool() {
        $this -> tool -> run();
    }
        
    
}


$connectorSwitch = new ConnectorSwitch();
$connectorSwitch -> switchTool();
$connectorSwitch -> runTool();
