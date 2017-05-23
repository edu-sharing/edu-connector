<?php

namespace connector\tools\tinymce;

use connector\lib\EduRestClient;

class TinyMce {

    private $apiClient;

    public function __construct(EduRestClient $apiClient) {
        $this->apiClient = $apiClient;
    }

    public function run()
    {   $this->setContent();
        $this->forwardToEditor();
    }

    private function setContent() {
       $_SESSION['content'] = file_get_contents($_SESSION['node']->node->contentUrl);
    }

    public function setNode()
    {
        $node = $this->apiClient->getNode($_SESSION['node']);
        $_SESSION['node'] = $node;
    }

    private function forwardToEditor()
    {
        header('Location: ' . WWWURL . '/src/tools/tinymce/');
        exit();
    }

}
