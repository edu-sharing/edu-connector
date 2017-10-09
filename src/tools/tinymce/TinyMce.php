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
                $arrApiUrl = parse_url($_SESSION[$this->connectorId]['api_url']);
                $arrContentUrl = parse_url($node->node->contentUrl);
                $contentUrl = $arrApiUrl['scheme'].'://'.$arrApiUrl['host'].':'.$arrApiUrl['port'].$arrContentUrl['path'].'?'.$arrContentUrl['query'] . '&com=internal';
            } else {
                $contentUrl = $node->node->contentUrl;
            }
            $_SESSION[$this->connectorId]['content'] = file_get_contents($contentUrl . '&ticket=' . $_SESSION[$this->connectorId]['ticket'] . '&params=display%3Ddownload');
        }

        if (in_array('Write', $node->node->access)) {
            $_SESSION[$this->connectorId]['readonly'] = 0;
            $this->apiClient->createTextContent($_SESSION[$this->connectorId]['node'], $_SESSION[$this->connectorId]['content'], $node->node->mimetype);
        } else {
            $_SESSION[$this->connectorId]['readonly'] = 1;
        }

        $node = $this->getNode();

        $_SESSION[$this->connectorId]['node'] = $node;
    }

    private function forwardToEditor()
    {
        header('Location: ' . WWWURL . '/src/tools/tinymce/?id=' . $this->connectorId);
        exit();
    }

}
