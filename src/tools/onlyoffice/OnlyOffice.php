<?php

namespace connector\tools\OnlyOffice;

use connector\lib\Tool;

class OnlyOffice extends Tool {

    public function run()
    {
        $_SESSION['fileUrl'] = $_SESSION['node']->node->downloadUrl . $_SESSION['ticket'];
        $this->forwardToEditor();
    }

    public function setNode()
    {
        $node = $this->apiClient->getNode($_SESSION['node']);
        if (in_array('Write', $node->node->access)) {
            $_SESSION['edit'] = true;
        } else {
            $_SESSION['edit'] = false;
        }

        if ($node->node->size === NULL) {
            $this->apiClient->createContentNode($_SESSION['node'], STORAGEPATH . '/templates/init.' . $_SESSION['filetype'], \connector\tools\onlyoffice\OnlyOffice::getMimetype($_SESSION['filetype']));
            $node = $this->apiClient->getNode($_SESSION['node']);
        }
        $_SESSION['node'] = $node;
    }

    private function forwardToEditor()
    {
        header('Location: ' . WWWURL . EDITORPATH);
        exit();
    }

    public static function getMimetype($doctype)
    {
        switch ($doctype) {
            case 'docx':
                $mimetype = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
            case 'xlsx':
                $mimetype = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            case 'pptx':
                $mimetype = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                break;
            case 'odt':
                $mimetype = 'application/vnd.oasis.opendocument.text';
                break;
            case 'ods':
                $mimetype = 'application/vnd.oasis.opendocument.spreadsheet';
                break;
            case 'odp':
                $mimetype = 'application/vnd.oasis.opendocument.presentation';
                break;
            default:
                error_log('No mimetype specified');
                throw new Exception();
        }
        return $mimetype;
    }
}
