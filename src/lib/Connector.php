<?php

namespace connector\lib;

class Connector
{

    private $parameters;
    private $log;
    private $apiClient;

    public function __construct($log)
    {
        $this->log = $log;
        try {
            $this->setParameters();



            $this->apiClient = new EduRestClient();
            $this->apiClient->validateSession();

//method is only office specific! change that
            $this->setNode();
            $this->setUser();

var_dump($_SESSION['tool']);


            $this->startTool();
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

    private function setNode()
    {
        $node = $this->apiClient->getNode($_SESSION['node']);
        if (in_array('Write', $node->node->access)) {
            $_SESSION['edit'] = true;
        } else {
            $_SESSION['edit'] = false;
        }

        if ($node->node->size === NULL) {
            $this->apiClient->createContentNode($_SESSION['node'], STORAGEPATH . '/templates/init.' . $_SESSION['filetype'], \connector\tools\onlyoffice\OnlyOffice::getMimetype($_SESSION['filetype']));
            $node = $this->apiClient->getNode($_SESSION['node']);
        }
        $_SESSION['node'] = $node;
    }

    private function startTool()
    {
        switch ($_SESSION['tool']) {
            case 'ONLY_OFFICE':
                $tool = new \connector\tools\onlyoffice\OnlyOffice();
                $tool->run();
                break;
            default:
                throw new \Exception('Unknown tool: ' . $_SESSION['tool'] . '.');
        }
    }
}