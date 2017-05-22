<?php

namespace connector\lib;

class Connector
{

    private $tool;
    private $log;
    private $apiClient;

    public function __construct($log)
    {
        $this->log = $log;
        try {
            $this->setParameters();
            $this->apiClient = new EduRestClient();
            $this->apiClient->validateSession();
            $this->switchTool();
            $this->tool->setNode();
            $this->setUser();
            $this->tool->run();
        } catch (\Exception $e) {
            $this->log->error($e->__toString());
            echo 'ERROR - Please contact your system administrator.';
            exit(0);
        }
    }

    private function setParameters()
    {
        $encrypted = base64_decode($_REQUEST['e']);
        $cryptographer = new Cryptographer($this->log);
        $decrypted = $cryptographer->decryptData($encrypted);
        $this->validate($decrypted);
        foreach($decrypted as $key => $value) {
            $_SESSION[$key] = $value;
        }
	$_SESSION['api_url'] = 'http://appserver7.metaventis.com:7151/edu-sharing/rest/';
    }

    private function validate($decrypted)
    {
        $offset = time() - $decrypted->ts;
        if ($offset > 100000000000)
            throw new \Exception('Timestamp validation failed. Offset is ' . $offset . ' seconds.');
        return true;
    }

    private function setUser()
    {
        $_SESSION['user'] = $this->apiClient->getUser();
    }

    private function switchTool()
    {
        switch ($_SESSION['tool']) {
            case 'ONLY_OFFICE':
                $this -> tool = new \connector\tools\onlyoffice\OnlyOffice();
                break;
            default:
                throw new \Exception('Unknown tool: ' . $_SESSION['tool'] . '.');
        }
    }
}