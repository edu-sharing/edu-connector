<?php

namespace connector\tools\OnlyOffice;

class OnlyOffice extends \connector\lib\Tool {

    public function run()
    {
        $_SESSION[$this->connectorId]['fileUrl'] = $_SESSION[$this->connectorId]['node']->node->downloadUrl . '&ticket=' . $_SESSION[$this->connectorId]['ticket'];
        $this->forwardToEditor();
    }

    public function setNode()
    {
        $node = $this->getNode();

        if (in_array('Write', $node->node->access)) {
            $_SESSION[$this->connectorId]['edit'] = true;
        } else {
            $_SESSION[$this->connectorId]['edit'] = false;
        }

        if ($node->node->size === NULL) {
            $this->apiClient->createContentNode($node->node->ref->id, ONLYOFFICE_STORAGEPATH . '/templates/init.' . $_SESSION[$this->connectorId]['filetype'], \connector\tools\onlyoffice\OnlyOffice::getMimetype($_SESSION[$this->connectorId]['filetype']), 'MAIN_FILE_UPLOAD');
            $node = $this->apiClient->getNode($node->node->ref->id);
        }
        $_SESSION[$this->connectorId]['node'] = $node;
    }

    private function forwardToEditor()
    {
        header('Location: ' . ONLYOFFICE_EDITORURL . '?id=' . $this->connectorId);
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
                throw new \Exception();
        }
        return $mimetype;
    }
}
