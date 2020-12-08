<?php

namespace connector\tools\tinymce;

use connector\lib\EduRestClient;

class TinyMce extends \connector\lib\Tool {

    public function run()
    {
        $_SESSION[$this->connectorId]['first_run'] = true;
        $this->forwardToEditor();
    }

    public function setNode()
    {
        $node = $this->getNode();

        if ($node->node->size === NULL) {
            $_SESSION[$this->connectorId]['content'] = '';
        } else {
            if(defined('FORCE_INTERN_COM') && FORCE_INTERN_COM) {
                $apiUrlStr = $_SESSION[$this->connectorId]['api_url'];
                if(defined('FORCED_APIURL') && FORCED_APIURL){
                    $apiUrlStr = FORCED_APIURL;
                }
                $arrApiUrl = parse_url($apiUrlStr);
                $arrContentUrl = parse_url($node->node->contentUrl);
                $contentUrl = $arrApiUrl['scheme'].'://'.$arrApiUrl['host'].':'.$arrApiUrl['port'].$arrContentUrl['path'].'?'.$arrContentUrl['query'] . '&com=internal';
                $curlHeader = array('Cookie:JSESSIONID=' . $_SESSION[$this->connectorId]['sessionId']);
                $url = $contentUrl . '&params=display%3Ddownload';
                $url = $url . '&ticket=' . $_SESSION[$this->connectorId]['ticket'];
            } else {
                if ($node->node->contentUrl){
                    $contentUrl = $node->node->contentUrl; //repo-version 5.0 or older
                }else{
                    $contentUrl = $node->node->downloadUrl;  //repo-version 5.1 or newer
                    // 5.1 and newer use signature based client
                    $client = new EduRestClient($this->connectorId);
                    $data = $client->getContent($node);
                    $_SESSION[$this->connectorId]['content'] = $data;
                }
                $curlHeader = array();
                $url = $contentUrl . '&ticket=' . $_SESSION[$this->connectorId]['ticket'] . '&params=display%3Ddownload';
            }
            if(!$data) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeader);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                $data = curl_exec($curl);
                $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
                if ($httpcode >= 200 && $httpcode < 308) {
                    $_SESSION[$this->connectorId]['content'] = $data;
                } else {
                    $this->log->info('Curl error! (httpcode: ' . $httpcode . ')');
                }
            }

        }

        if (in_array('Write', $node->node->access)) {
            $_SESSION[$this->connectorId]['readonly'] = 0;
            $this->apiClient->createTextContent($_SESSION[$this->connectorId]['node'], $_SESSION[$this->connectorId]['content'], $node->node->mimetype, 'EDITOR_UPLOAD,TINYMCE');
        } else {
            $_SESSION[$this->connectorId]['readonly'] = 1;
        }

        $node = $this->getNode();

        $_SESSION[$this->connectorId]['node'] = $node;
    }

    private function forwardToEditor()
    {
        header('Location: ' . WWWURL . '/src/tools/tinymce/?id=' . $this->connectorId . '&ref=' . base64_encode($_SESSION[$this->connectorId]['node']->node->properties->{'virtual:permalink'}[0]));
        exit();
    }

}
