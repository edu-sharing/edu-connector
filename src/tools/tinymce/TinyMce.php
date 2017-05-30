<?php

namespace connector\tools\tinymce;

class TinyMce extends \connector\lib\Tool {

    public function run()
    {
        $this->forwardToEditor();
    }

    public function setNode()
    {
        $node = $this->apiClient->getNode($_SESSION['node']);

        if ($node->node->size === NULL) {
            $_SESSION['content'] = '';
        } else {
            $_SESSION['content'] = file_get_contents($node->node->contentUrl . '&ticket=' . $_SESSION['ticket']);
        }
        if (in_array('Write', $node->node->access)) {
            $_SESSION['edit'] = true;
            $this->apiClient->createTextContent($_SESSION['node'], $_SESSION['content'], $node->node->mimetype, 'openedInTinyMCE');
        } else {
            $_SESSION['edit'] = false;
        }

        $node = $this->apiClient->getNode($_SESSION['node']);
        $_SESSION['node'] = $node;
    }

    private function forwardToEditor()
    {
        header('Location: ' . WWWURL . '/src/tools/tinymce/');
        exit();
    }

}
