<?php

namespace connector\tools\tinymce;

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
                $contentUrl = $node->node->contentUrl;
                $curlHeader = array();
                $url = $contentUrl . '&ticket=' . $_SESSION[$this->connectorId]['ticket'] . '&params=display%3Ddownload';
            }

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeader);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            $data = curl_exec($curl);
            curl_close($curl);
            $_SESSION[$this->connectorId]['content'] = $data;
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
