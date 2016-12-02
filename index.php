<?php
session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);

require_once 'config.php';
//require_once 'MoodleConnector.php';
//require_once 'EtherpadConnector.php';
require_once 'OnlyOfficeConnector.php';

class Connector {
       
    private $tool;
    
    public function __construct() {
        $this -> setupParameters();
        $this -> switchTool();
        $this -> runTool();
        
    }

    private function setupParameters() {
        $_SESSION['api_url'] = $_REQUEST['api_url'];
        $_SESSION['oauth_access_token'] = $_REQUEST['oauth_access_token'];
        $_SESSION['oauth_refresh_token'] = $_REQUEST['oauth_refresh_token'];
        $_SESSION['oauth_expires_in'] = $_REQUEST['oauth_expires_in'];
        $_SESSION['oauth_token_received'] = time();
    }
    
    private function switchTool() {
        switch($_REQUEST['tool']) {
            case 'edu-szenario-moodle':
                die('implement parameters');
                $this -> tool = new MoodleConnector();
            break;
            case 'edu-tool-etherpad':
                die('implement parameters');
                $this -> tool = new EtherpadConnector();
            break;
			case 'edu-tool-onlyoffice':
				$this -> tool = new OnlyOfficeConnector();
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

$connector = new Connector();
