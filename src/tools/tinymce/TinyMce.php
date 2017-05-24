<?php

namespace connector\tools\tinymce;

class TinyMce extends Connector {

    public function __construct() {
    }

    public function run()
    {
        $this->forwardToEditor();
    }

    public function setNode()
    {
        $node = $this->apiClient->getNode($_SESSION['node']);
        $_SESSION['content'] = file_get_contents($node->node->contentUrl . '&ticket=' . $_SESSION['ticket']);
        try {
            $temp = tmpfile();
            fwrite($temp, $_SESSION['content']);
            $metaDatas = stream_get_meta_data($temp);
            $tmpFilename = $metaDatas['uri'];
        } catch (Exception $e) {
            $this->log->error('Error creating temp file.');
            throw $e;
        }
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
