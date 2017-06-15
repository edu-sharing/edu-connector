<?php

namespace connector\lib;

class EduRestClient
{
    private $connectorId = '';
    private $authHeader = '';

    public function __construct($connectorId) {
        $this->connectorId = $connectorId;
        $this->authHeader = 'Cookie:JSESSIONID=' . $_SESSION[$this->connectorId]['sessionId'];
    }

    private function getAuthHeader() {
        return $this->authHeader;
    }

    public function setAuthHeader($authHeader) {
        $this->authHeader = $authHeader;
    }

    private function getApiUrl() {
        return $_SESSION[$this->connectorId]['api_url'];
    }

    public function validateSession()
    {
        $ch = curl_init($this->getApiUrl() . 'authentication/v1/validateSession');
        $headers = array($this->getAuthHeader(), 'Accept: application/json');
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
        if ($httpcode >= 200 && $httpcode < 300) {
            return json_decode($res);
        }
        //var_dump($res);
        throw new \Exception('Error validating session', $httpcode);
    }

    public function unlockNode($nodeId) {
        $ch = curl_init($this->getApiUrl() . 'node/v1/nodes/-home-/' . $nodeId . '/lock/unlock');
        $headers = array($this->getAuthHeader(), 'Accept: application/json');
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
        if ($httpcode >= 200 && $httpcode < 300) {
            return json_decode($res);
        }
        throw new \Exception('Error unlocking node', $httpcode);
    }

    public function createContentNodeEnhanced($nodeId, $content, $mimetype, $versionComment = '') {
        try {
            //throw \Exception(test);
            return self::createTextContent($nodeId, $content, $mimetype, $versionComment = '');
        } catch(\Exception $e) {
            if($e->getCode() === 401) {
                $log->error('Could not saved with session, trying ticket now');
                $this->setAuthHeader($this->getTicketHeader());
                return self::createTextContent($nodeId, $content, $mimetype, $versionComment = '');
            }
        }
    }

    private function getTicketHeader() {
        $paramstrusted = array("applicationId"  => 'educonnector',
            "ticket"  => session_id(), "ssoData"  => array(
                array('key'  => 'username', 'value'  => $_SESSION[$this->id]['user']->userName),
                array('key'  => 'lastname', 'value'  => $_SESSION[$this->connectorId]->profile->lastName),
                array('key'  => 'firstname', 'value'  => $_SESSION[$this->connectorId]->profile->firstName),
                array('key'  => 'email', 'value'  => $_SESSION[$this->connectorId]->profile->email)));
        try {
            $client = new mod_edusharing_sig_soap_client($this->authenticationservicewsdl);
            $return = $client->authenticateByTrustedApp($paramstrusted);
            $ticket = $return->authenticateByTrustedAppReturn->ticket;

            return 'TICKET:' . $ticket;

        } catch (\Exception $e) {
            throw new \Exception('ticketfehler');
        }

    }

    public function createTextContent($nodeId, $content, $mimetype, $versionComment = '')
    {
        $ch = curl_init($this->getApiUrl() . 'node/v1/nodes/-home-/' . $nodeId . '/textContent?versionComment=' . $versionComment . '&mimetype=' . $mimetype);
        $headers = array($this->getAuthHeader(), 'Accept: application/json', 'Content-Type: multipart/form-data');
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

        if ($httpcode >= 200 && $httpcode < 300) {
            return json_decode($res);
        }
        throw new \Exception('Error creating text content', $httpcode);
    }

    public function createContentNode($nodeId, $contentpath, $mimetype, $versionComment = '')
    {
        $ch = curl_init($this->getApiUrl() . 'node/v1/nodes/-home-/' . $nodeId . '/content?versionComment=' . $versionComment . '&mimetype=' . $mimetype);
        $headers = array($this->getAuthHeader(), 'Accept: application/json', 'Content-Type: multipart/form-data');
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
        curl_close($ch);

        if ($httpcode >= 200 && $httpcode < 300) {
            return json_decode($res);
        }
        throw new \Exception('Error creating content node HTTP STATUS ' . $httpcode, $httpcode);
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

        if ($httpcode >= 200 && $httpcode < 300) {
            return true;
        }
        echo 'Error updating node';
        return false;
    }*/

    public function getNode($nodeId)
    {
        $ch = curl_init($this->getApiUrl() . 'node/v1/nodes/-home-/' . $nodeId . '/metadata?propertyFilter=-all-');
        $headers = array($this->getAuthHeader(), 'Accept: application/json');
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
        if ($httpcode >= 200 && $httpcode < 300) {
            $node = json_decode($res);
            return $node;
        }
        throw new \Exception('Error fetching node', $httpcode);
    }


    public function getUser()
    {
        $ch = curl_init($this->getApiUrl() . 'iam/v1/people/-home-/-me-');
        $headers = array($this->getAuthHeader(), 'Accept: application/json');
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
        if ($httpcode >= 200 && $httpcode < 300) {
            $person = json_decode($res);
            return $person;
        }
        throw new \Exception('Error fetching person', $httpcode);
    }


}
