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
            $client = new EduRestClient($this->connectorId);
            $data = $client->getContent($node);
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
