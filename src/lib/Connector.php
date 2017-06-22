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
            $this->log->error($e->getCode() . ' ' . $e->__toString());
            echo 'ERROR - Please contact your system administrator.';
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
        $_SESSION[$this->id]['api_url'] =  rtrim($_SESSION[$this->id]['api_url'], '/') . '/';
        $_SESSION[$this->id]['WWWURL'] = WWWURL;
    }

    private function validate($data)
    {
        $offset = time() - $data->ts;
        if ($offset > 10)
            throw new \Exception('Timestamp validation failed. Offset is ' . $offset . ' seconds.');

        if(false === filter_var($data->node, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-z0-9-]*$/"))))
            throw new \Exception('Invalid node ID');

        if(false === filter_var($data->ticket, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-zA-Z0-9_]*$/"))))
            throw new \Exception('Invalid ticket');

        if(false === filter_var($data->sessionId, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[A-Z0-9]*$/"))))
            throw new \Exception('Invalid session ID\'');

        if(false === filter_var($data->tool, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[A-Z_]*$/"))))
            throw new \Exception('Invalid tool');

        if(false === filter_var($data->api_url, FILTER_VALIDATE_URL))
            throw new \Exception('Invalid API URL');
    }

    private function setUser()
    {
        $_SESSION[$this->id]['user'] = $this->apiClient->getUser()->person;
    }

    private function startTool()
    {
        switch ($_SESSION[$this->id]['tool']) {
            case 'ONLY_OFFICE':
                $this -> tool = new \connector\tools\onlyoffice\OnlyOffice($this->apiClient, $this->log, $this->id);
                break;
            case 'ONYX':
                $this -> tool = new \connector\tools\onyx\Onyx($this->apiClient, $this->log, $this->id);
                break;
            case 'TINYMCE':
                $this -> tool = new \connector\tools\tinymce\TinyMce($this->apiClient, $this->log, $this->id);
                break;
            default:
                throw new \Exception('Unknown tool: ' . $_SESSION[$this->id]['tool'] . '.');
        }
    }
}