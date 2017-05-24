<?php

namespace connector\tools\tinymce;

use connector\lib\EduRestClient;

class TinyMce {

    private $apiClient;

    public function __construct(EduRestClient $apiClient) {
        $this->apiClient = $apiClient;
    }

    public function run()
    {
        $this->forwardToEditor();
    }

    public function setNode()
    {
        $node = $this->apiClient->getNode($_SESSION['node']);
        $_SESSION['content'] = file_get_contents($node->node->contentUrl . '&ticket=' . $_SESSION['ticket']);
        $temp = tmpfile();
        fwrite($temp, $_SESSION['content']);
        $this->apiClient->createContentNode($_SESSION['node'], $temp, 'text/html', 'openedforediting');
        fclose($temp);
        $node = $this->apiClient->getNode($_SESSION['node']);
        $_SESSION['node'] = $node;
    }

    private function forwardToEditor()
    {
        header('Location: ' . WWWURL . '/src/tools/tinymce/');
        exit();
    }

}
