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
            exit(0);


            $this->setNode();
            $this->setUser();
            $this->startTool();
        } catch (\Exception $e) {
            $this->log->error($e->__toString());
            echo 'ERROR - Please contact your system administrator.';
            exit(0);
        }
    }


    private function setParameters()
    {
        $encrypted = base64_decode($_REQUEST['data']);
        $cryptographer = new Cryptographer($this->log);
        $decrypted = $cryptographer->decryptData($encrypted);
        $decrypted = json_decode($decrypted, true);
        $this->validate($decrypted);
        foreach($decrypted as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    private function validate($decrypted)
    {
        $offset = time() - $decrypted['ts'];
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
        $node = $this->apiClient->getNode($this->parameters->node);
        if (in_array('Write', $node->node->access)) {
            $_SESSION['edit'] = true;
        } else {
            $_SESSION['edit'] = false;
        }

        if ($node->node->size === NULL) {
            $this->apiClient->createContentNode($this->parameters->node, STORAGEPATH . '/templates/init.' . $this->fileType, \connector\tools\onlyoffice\OnlyOffice::getMimetype($this->parameters->fileType));
            $node = $this->apiClient($this->parameters->node);
        }
        $_SESSION['node'] = $node;
    }

    private function startTool()
    {
        switch ($this->parameters->tool) {
            case 'ONLY_OFFICE':
                $tool = new \connector\tools\onlyoffice\OnlyOffice();
                $tool->run();
                break;
            default:
                throw new \Exception('Unknown tool: ' . $this->parameters->tool . '.');
        }
    }
}