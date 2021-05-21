<?php

namespace connector\lib;

define('APPID', 'educonnector');

class EduRestClient
{

    private $connectorId = '';
    private $authHeader = '';

    public function __construct($connectorId) {
        $this->connectorId = $connectorId;
        $this->authHeader = 'Cookie:JSESSIONID=' . $_SESSION[$this->connectorId]['sessionId'];
    }

    private function getHeaders() {
        $timestamp = round(microtime(true) * 1000);
        $signdata = APPID . $timestamp;
        $cryptographer = new \connector\lib\Cryptographer();
        $privkey = $cryptographer->getPrivateKey();
        $pkeyid = openssl_get_privatekey($privkey);
        openssl_sign($signdata, $signature, $pkeyid);
        $signature = base64_encode($signature);
        openssl_free_key($pkeyid);

        return array(
            $this->getAuthHeader(),
            'X-Edu-App-Id:' . APPID,
            'X-Edu-App-Sig:'.$signature,
            'X-Edu-App-Signed:'.$signdata,
            'X-Edu-App-Ts:'.$timestamp,
            'Accept: application/json'
        );
    }

    private function getAuthHeader() {
        return $this->authHeader;
    }

    public function setAuthHeader($authHeader) {
        $this->authHeader = $authHeader;
    }

    private function getApiUrl() {
        if(defined('FORCED_APIURL') && FORCED_APIURL)
            return FORCED_APIURL;
        return $_SESSION[$this->connectorId]['api_url'];
    }

    public function validateSession()
    {
        $ch = curl_init($this->getApiUrl() . 'authentication/v1/validateSession');
        $headers = $this->getHeaders();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $res = curl_exec($ch);
        if ($res === false) {
            throw new \Exception('Cannot reach API ' . $this->getApiUrl());
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode >= 200 && $httpcode < 308) {
            return json_decode($res);
        }
        throw new \Exception('Error validating session: ' . $this->getAuthHeader(), $httpcode);
    }

    public function unlockNode($nodeId) {
        $ch = curl_init($this->getApiUrl() . 'node/v1/nodes/-home-/' . $nodeId . '/lock/unlock');
        $headers = $this->getHeaders();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $res = curl_exec($ch);

        if ($res === false) {
            throw new \Exception('Cannot reach API');
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode >= 200 && $httpcode < 308) {
            return json_decode($res);
        }
        throw new \Exception('Error unlocking node ' . $nodeId, $httpcode);
    }
    public function getContent($node){
        if ($node->node->contentUrl){
            $contentUrl = $node->node->contentUrl; //repo-version 5.0 or older
        }else{
            $contentUrl = $node->node->downloadUrl;  //repo-version 5.1 or newer
        }

        $curlHeader = $this->getHeaders();

        if(defined('FORCE_INTERN_COM') && FORCE_INTERN_COM) {
            $apiUrlStr = $_SESSION[$this->connectorId]['api_url'];
            if(defined('FORCED_APIURL') && FORCED_APIURL){
                $apiUrlStr = FORCED_APIURL;
            }
            $arrApiUrl = parse_url($apiUrlStr);
            $arrContentUrl = parse_url($contentUrl);
            $contentUrl = $arrApiUrl['scheme'].'://'.$arrApiUrl['host'].':'.$arrApiUrl['port'].$arrContentUrl['path'].'?'.$arrContentUrl['query'] . '&com=internal';
            $curlHeader = array('Cookie:JSESSIONID=' . $_SESSION[$this->connectorId]['sessionId']);
        }

        $url = $contentUrl . '&ticket=' . $_SESSION[$this->connectorId]['ticket'] . '&params=display%3Ddownload';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeader);
        $data = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($httpcode >= 200 && $httpcode < 308) {
            return $data;
        }else{
            throw new \Exception("curl error " . $httpcode);
        }
    }

    private function getTicketHeader() {
        $paramstrusted = array("applicationId"  => APPID,
            "ticket"  => session_id(), "ssoData"  => array(
                array('key'  => 'userid', 'value' => $_SESSION[$this->connectorId]['user']->userName),
                array('key'  => 'lastname', 'value' => $_SESSION[$this->connectorId]['user']->profile->lastName),
                array('key'  => 'firstname', 'value' => $_SESSION[$this->connectorId]['user']->profile->firstName),
                array('key'  => 'email', 'value' => $_SESSION[$this->connectorId]['user']->profile->email)));
        try {
            $client = new \connector\lib\SigSoapClient($this->getApiUrl() . '../services/authbyapp?wsdl');
            $return = $client->authenticateByTrustedApp($paramstrusted);
            $ticket = $return->authenticateByTrustedAppReturn->ticket;
            return 'Authorization: EDU-TICKET ' . $ticket;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching edu-sharing ticket. Check catalina.out. ' . $e->getMessage(), $e->getCode());
        } catch (\SoapFault $s) {
            throw new \Exception('Error fetching edu-sharing ticket. Check catalina.out. ' . $s->getMessage(), $s->faultcode);
        }

    }

    public function createTextContent($nodeId, $content, $mimetype, $versionComment = '')
    {
        $ch = curl_init($this->getApiUrl() . 'node/v1/nodes/-home-/' . $nodeId . '/textContent?versionComment=' . $versionComment . '&mimetype=' . $mimetype);
        $headers = $this->getHeaders();
        $headers[] = 'Content-Type: multipart/form-data';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $res = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode >= 200 && $httpcode < 308) {
            return json_decode($res);
        }
        $errorStr = $_SESSION[$this->connectorId]['tool'] . ' Error creating text content for node "' . $nodeId . '" - repo "' . $_SESSION[$this->connectorId]['node'] -> node -> ref -> repo
            . '" - parent "' . $_SESSION[$this->connectorId]['node'] -> node -> parent -> id . '" - user "' . $_SESSION[$this->connectorId]['user'] -> authorityName . '"';
        throw new \Exception($errorStr, $httpcode);
    }
    
    public function createContentNodeEnhanced($nodeId, $contentpath, $mimetype, $versionComment = '') {
        try {
           return self::createContentNode($nodeId, $contentpath, $mimetype, $versionComment);
        } catch(\Exception $e) {
            if($e->getCode() === 401 || $e->getCode() === 403) {
                $this->setAuthHeader($this->getTicketHeader());
                return self::createContentNode($nodeId, $contentpath, $mimetype, $versionComment);
            }
            $errorStr = $_SESSION[$this->connectorId]['tool'] . ' Error creating content for node "' . $nodeId . '" - repo "' . $_SESSION[$this->connectorId]['node'] -> node -> ref -> repo
                . '" - parent "' . $_SESSION[$this->connectorId]['node'] -> node -> parent -> id . '" - user "' . $_SESSION[$this->connectorId]['user'] -> authorityName . '" - content path "' . $contentpath . '"';
            throw new \Exception($errorStr, $e->getCode());
        }
    }

    public function createContentNode($nodeId, $contentpath, $mimetype, $versionComment = '')
    {
        $ch = curl_init($this->getApiUrl() . 'node/v1/nodes/-home-/' . $nodeId . '/content?versionComment=' . $versionComment . '&mimetype=' . $mimetype);
        $headers = $this->getHeaders();
        $headers[] = 'Content-Type: multipart/form-data';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $cfile = curl_file_create($contentpath, $mimetype, 'file');
        $fields = array('file' => $cfile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $res = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode >= 200 && $httpcode < 308) {
            curl_close($ch);
            return json_decode($res);
        }

        $error = curl_error($ch);
        curl_close($ch);
        throw new \Exception('Error creating content node HTTP STATUS ' . $httpcode . '. Curl error ' . $error, $httpcode);
    }

/*
    public function updateReferenceUrl($nodeId, $url)
    {

        $fields = '{"ccm:wwwurl":["' . $url . '"]}';

        $ch = curl_init($_SESSION['api_url'] . 'node/v1/nodes/-home-/' . $nodeId . '/metadata');

        $headers = array('Cookie:JSESSIONID=' . $this->getSessionId(), 'Accept: application/json', 'Content-Type: application/json; charset=utf-8');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");

        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $res = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode >= 200 && $httpcode < 308) {
            return true;
        }
        echo 'Error updating node';
        return false;
    }*/

    public function getNode($nodeId)
    {
        $ch = curl_init($this->getApiUrl() . 'node/v1/nodes/-home-/' . $nodeId . '/metadata?propertyFilter=-all-');
        $headers = $this->getHeaders();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $res = curl_exec($ch);

        if ($res === false) {
            throw new \Exception('Cannot reach API');
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        if ($httpcode >= 200 && $httpcode < 308) {
            $node = json_decode($res);
            return $node;
        }
        throw new \Exception('Error fetching node ' . $nodeId, $httpcode);
    }


    public function getUser()
    {
        $ch = curl_init($this->getApiUrl() . 'iam/v1/people/-home-/-me-');
        $headers = $this->getHeaders();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $res = curl_exec($ch);

        if ($res === false) {
            throw new \Exception('Cannot reach API');
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode >= 200 && $httpcode < 308) {
            $person = json_decode($res);
            return $person;
        }
        throw new \Exception('Error fetching person', $httpcode);
    }


}
