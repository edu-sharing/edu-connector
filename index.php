<?php

error_reporting(E_ERROR);
ini_set('display_errors', 1);

define('WWWURL', 'http://appserver9.metaventis.com/eduConnector');
define('WWWROOT', 'http://138.201.17.74/eduConnector');
define('DOCROOT', '/var/www/eduConnector');
define('REPOURL', 'http://appserver7.metaventis.com:7001/edu-sharing/');
define('CONTENT_URL', REPOURL . 'content');
define('APP_ID', 'educonnector');

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
			
			//dok anlegen (aus suche) ohne titel, keine referenz etc.
			case 'edu-tool-office':
                header('Location: http://appserver9.metaventis.com/eduConnector/_OnlineEditorsExamplePHP/');
				exit();
            break;
			
			//nur über skript mit entspr. parametern ansprechbar, pausiert, da nun erst die authentifizierung her muss (lti)
			case 'edu-tool-onlyoffice':
                //header('Location: http://appserver9.metaventis.com/eduConnector/OnlineEditorsExamplePHP/');
				//exit();
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
