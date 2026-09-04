<?php

namespace connector\lib;

use connector\tools\h5p\H5PFramework;
use EduSharingApiClient\AppAuthException;
use EduSharingApiClient\EduSharingAuthHelper;
use EduSharingApiClient\EduSharingHelperBase;
use EduSharingApiClient\EduSharingNodeHelper;
use EduSharingApiClient\EduSharingNodeHelperConfig;
use EduSharingApiClient\SecuredNode;
use EduSharingApiClient\UrlHandling;

define('APPID', 'educonnector');

class EduRestClient
{
    private $connectorId = '';
    private $authHeader = '';
    private EduSharingAuthHelper $authHelper;
    private EduSharingNodeHelper $nodeHelper;

    public function __construct($connectorId) {
        $this->connectorId = $connectorId;
        $this->authHeader = 'Cookie:JSESSIONID=' . ($_SESSION[$this->connectorId]['sessionId'] ?: $this->connectorId);
        $privateKeyString = file_get_contents(DATA . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'private.key');
        // remove /rest/ from end
        $apiUrl = substr($this->getApiUrl(), 0, -6);
        $baseHelper  = new EduSharingHelperBase(
            $apiUrl,
            $privateKeyString,
            APPID
        );
        $nodeConfig       = new EduSharingNodeHelperConfig(new UrlHandling(false));
        $this->authHelper = new EduSharingAuthHelper($baseHelper);
        $this->nodeHelper = new EduSharingNodeHelper($baseHelper, $nodeConfig);
    }

    private function getHeaders() {
        $timestamp = round(microtime(true) * 1000);
        $signdata = APPID . $timestamp;
        $cryptographer = new \connector\lib\Cryptographer();
        $privkey = $cryptographer->getPrivateKey();
        $pkeyid = openssl_get_privatekey($privkey);
        openssl_sign($signdata, $signature, $pkeyid,OPENSSL_ALGO_SHA512);
        $signature = base64_encode($signature);
        openssl_free_key($pkeyid);

        return array(
            $this->getAuthHeader(),
            'X-Edu-App-Id:' . APPID,
            'X-Edu-App-Sig:'.$signature,
            'X-Edu-App-Signed:'.$signdata,
            'X-Edu-App-SignedAlg:' . 'SHA512withRSA',
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
        throw new \Exception('Error validating session: ' . $this->getAuthHeader() . ' ' . $this->getApiUrl(), $httpcode);
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

    public function getNodeVersions($nodeId): array {
        $ch = curl_init($this->getApiUrl() . 'node/v1/nodes/-home-/' . $nodeId . '/versions');
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
            $versions = json_decode($res);
            return $versions->versions;
        }
        throw new \Exception('Error fetching node versions ' . $nodeId, $httpcode);
    }

    public function getContent($node, $downloadUrl = null, $isH5p = false){
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
        }

        if (!empty($downloadUrl)){
            $contentUrl = $downloadUrl;
        }

        $url = $contentUrl . '&ticket=' . $_SESSION[$this->connectorId]['ticket'] . '&params=display%3Ddownload';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if (! $isH5p) {
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        } else {
            $h5pFramework = new H5PFramework();
            $path         = $h5pFramework->getUploadedH5pPath();
            $filePath     = fopen($path, 'wb');
            curl_setopt($curl,CURLOPT_FILE, $filePath);
        }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeader);
        $data = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($isH5p) {
            fclose($filePath);
        }
        if ($httpcode >= 200 && $httpcode < 308) {
            return $data;
        }else{
            error_log("eduConnector: curl error " . $httpcode);
            throw new \Exception("curl error " . $httpcode);
        }
    }

    private function getTicketHeader() {
        $additionalfields = [
            'firstName' => $_SESSION[$this->connectorId]['user']->profile->firstName,
            'lastName'  => $_SESSION[$this->connectorId]['user']->profile->lastName,
            'email'     => $_SESSION[$this->connectorId]['user']->profile->email,
        ];
        try {
            $ticket = $this->authHelper->getTicketForUser($_SESSION[$this->connectorId]['user']->userName, $additionalfields);
            return 'Authorization: EDU-TICKET ' . $ticket;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching edu-sharing ticket. Check catalina.out. ' . $e->getMessage(), $e->getCode());
        }
    }

    public function createTextContent($nodeId, $content, $mimetype, $versionComment = '')
    {
        $ch = curl_init($this->getApiUrl() . 'node/v1/nodes/-home-/' . $nodeId . '/textContent?versionComment=' . $versionComment . '&mimetype=' . $mimetype);
        $headers = $this->getHeaders();
        $headers[] = 'Content-Type: application/json';
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
            if($e->getCode() === 401 || $e->getCode() === 403 || $e->getCode() === 500)  {
                $this->setAuthHeader($this->getTicketHeader());
                return self::createContentNode($nodeId, $contentpath, $mimetype, $versionComment);
            }
            $errorStr = $_SESSION[$this->connectorId]['tool'] . ' Error creating content for node "' . $nodeId . '" - repo "' . $_SESSION[$this->connectorId]['node'] -> node -> ref -> repo
                . '" - parent "' . $_SESSION[$this->connectorId]['node'] -> node -> parent -> id . '" - user "' . $_SESSION[$this->connectorId]['user'] -> authorityName . '" - content path "' . $contentpath . '"'
                . ' - api error (HTTP ' . $e->getCode() . '): ' . $e->getMessage();
            throw new \Exception($errorStr, $e->getCode(), $e);
        }
    }

    public function createContentNode($nodeId, $contentpath, $mimetype, $versionComment = '') {
        $url = $this->getApiUrl() . 'node/v1/nodes/-home-/' . $nodeId . '/content?mimetype=' . $mimetype;
        if (!empty($versionComment)) {
            $url .= '&versionComment=' . $versionComment;
        }
        $ch = curl_init($url);
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
        throw new \Exception('Error creating content node HTTP STATUS ' . $httpcode . '. Curl error "' . $error . '". Response "' . substr((string)$res, 0, 200) . '"', $httpcode);
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

    public function getNode($nodeId) {
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
        throw new \Exception('Error fetching node ' . $nodeId, $httpcode . ': ' . $res);
    }

    /**
     * return true if this node is writable by the current user and a writable note
     */
    public function isWritable($node): bool {
        $perm = $node->node->access;
        if ($node->node->accessEffective) {
            $perm = $node->node->accessEffective;
        }

        print_r($node->node->properties);
        print_r(property_exists($node->node->properties, 'ccm:published_original'));
        return in_array('Write', $perm) && !property_exists($node->node->properties, 'ccm:published_original');
    }

    public function getUser() {
        $ch      = curl_init($this->getApiUrl() . 'iam/v1/people/-home-/-me-');
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

    /**
     * @throws AppAuthException
     */
    public function getTicket(): string {
        $cacheKey = 'ticket';
        $cacheTimeKey = 'ticket_ts';

        if (
            isset($_SESSION[$this->connectorId][$cacheKey], $_SESSION[$this->connectorId][$cacheTimeKey]) &&
            (time() - (int)$_SESSION[$this->connectorId][$cacheTimeKey]) < 300
        ) {
            return $_SESSION[$this->connectorId][$cacheKey];
        }

        $additionalFields = [
            'firstName' => $_SESSION[$this->connectorId]['user']->profile->lastName,
            'lastName'  => $_SESSION[$this->connectorId]['user']->profile->lastName,
            'email'     => $_SESSION[$this->connectorId]['user']->profile->email,
        ];

        $ticket = $this->authHelper->getTicketForUser(
            $_SESSION[$this->connectorId]['user']->userName,
            $additionalFields
        );

        $_SESSION[$this->connectorId][$cacheKey] = $ticket;
        $_SESSION[$this->connectorId][$cacheTimeKey] = time();

        return $ticket;
    }

    /**
     * @throws \JsonException
     * @throws AppAuthException
     */
    public function getSecuredNode(string $nodeId, string $repoId, string $version = '-1'): SecuredNode {
        return $this->nodeHelper->getSecuredNode($this->getTicket(), $nodeId, $repoId, $version);
    }
}
