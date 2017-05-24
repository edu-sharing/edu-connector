<?php

namespace connector\tools\tinymce;

class TinyMce {

    private $apiClient;

    public function __construct($apiClient) {
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
        $metaData = stream_get_meta_data($temp);
        $tmpFilename = $metaData['uri'];
        $this->apiClient->createContentNode($_SESSION['node'], $tmpFilename, 'text/html', 'openedforediting');
        fclose($temp);
        unlink($tmpFilename);
        $node = $this->apiClient->getNode($_SESSION['node']);
        $_SESSION['node'] = $node;
    }

    private function forwardToEditor()
    {
        header('Location: ' . WWWURL . '/src/tools/tinymce/');
        exit();
    }

}
