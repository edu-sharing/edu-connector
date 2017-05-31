<?php

namespace connector\lib;

class Connector
{

    private $tool;
    protected $log;
    protected $apiClient;

    public function __construct($log)
    {
        $this->log = $log;
        try {
            $this->setParameters();
            $this->apiClient = new EduRestClient();
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
            $_SESSION[$key] = $value;
        }
        if(substr($_SESSION['api_url'], -1) !== '/') {
            $_SESSION['api_url'] .= '/';
        }

        $_SESSION['csrftoken'] = bin2hex(openssl_random_pseudo_bytes(32));

        $_SESSION['WWWURL'] = WWWURL;

        //dev
        if(strpos($_SESSION['api_url'], 'localhost') !== false)
          $_SESSION['api_url'] = 'http://appserver7.metaventis.com:7153/edu-sharing/rest/';

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
                $this -> tool = new \connector\tools\tinymce\TinyMce($this->apiClient, $this->log);
                break;
            default:
                throw new \Exception('Unknown tool: ' . $_SESSION['tool'] . '.');
        }
    }
}