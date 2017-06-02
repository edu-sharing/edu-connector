<?php

namespace connector\lib;

class Connector
{
    private $id;
    private $tool;
    protected $log;
    protected $apiClient;

    public function __construct($log)
    {
        $this->log = $log;
        $this->id = bin2hex(openssl_random_pseudo_bytes(32));
        try {
            $this->setParameters();
            $this->apiClient = new EduRestClient($this->id);
            $this->apiClient->validateSession();
            $this->startTool();
            $this->tool->setNode();
            $this->setUser();
            $this->tool->run();
        } catch (\Exception $e) {
            $this->log->error($e->__toString());
            echo 'ERROR - Please contact your system administrator.';

            //dev
            echo $e->__toString();
            exit(0);
        }
    }

    private function setParameters()
    {
        $encryptedData = base64_decode($_REQUEST['e']);
        $encryptedKey = base64_decode($_REQUEST['k']);
        $cryptographer = new Cryptographer($this->log);
        $decryptedData = $cryptographer->decryptData($encryptedData, $encryptedKey);
        $this->validate($decryptedData);
        foreach($decryptedData as $key => $value) {
            $_SESSION[$this->id][$key] = $value;
        }
        if(substr($_SESSION[$this->id]['api_url'], -1) !== '/') {
            $_SESSION[$this->id]['api_url'] .= '/';
        }

        $_SESSION[$this->id]['WWWURL'] = WWWURL;
    }

    private function validate($data)
    {
        $offset = time() - $data->ts;
        if ($offset > 100000000000)
            throw new \Exception('Timestamp validation failed. Offset is ' . $offset . ' seconds.');
        return true;
    }

    private function setUser()
    {
        $_SESSION['user'] = $this->apiClient->getUser()->person;
    }

    private function startTool()
    {
        switch ($_SESSION['tool']) {
           /* case 'ONLY_OFFICE':
                $this -> tool = new \connector\tools\onlyoffice\OnlyOffice($this->apiClient, $this->log);
                break;*/
            case 'TINYMCE':
                $this -> tool = new \connector\tools\tinymce\TinyMce($this->apiClient, $this->log, $this->id);
                break;
            default:
                throw new \Exception('Unknown tool: ' . $_SESSION['tool'] . '.');
        }
    }
}