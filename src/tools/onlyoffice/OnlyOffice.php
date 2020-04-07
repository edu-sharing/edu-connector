<?php

namespace connector\tools\OnlyOffice;

class OnlyOffice extends \connector\lib\Tool {

    public function run()
    {
        $_SESSION[$this->connectorId]['fileUrl'] = $_SESSION[$this->connectorId]['node']->node->downloadUrl . '&ticket=' . $_SESSION[$this->connectorId]['ticket'];
        $_SESSION[$this->connectorId]['ticket'] = $_SESSION[$this->connectorId]['ticket'];

        $this->forwardToEditor();
    }

    /*
     * Fetch node from repository
     *
     * If node is a collection item, fetch original node if user has write permission.
     * Set edit mode.
     *
     */
    public function getNode() {
        $node = $this->apiClient->getNode($_SESSION[$this->connectorId]['node']);
        /*Deprecated because repository always sends orig. node id from now on (11.04.2019)*/
        if(in_array('ccm:collection_io_reference', $node->node->aspects)) {
            try {
                $originalId = $node->node->properties->{'ccm:original'}[0];
                $originalNode = $this->apiClient->getNode($originalId);
                if (in_array('Write', $originalNode->node->access)) {
                    $_SESSION[$this->connectorId]['edit'] = true;
                } else {
                    $_SESSION[$this->connectorId]['edit'] = false;
                }
                return $originalNode;
            } catch (\Exception $e) {
                $this->log->info('No accesss to original object ('.$e->getCode().')');
            }
        }
        if (in_array('Write', $node->node->access)) {
            $_SESSION[$this->connectorId]['edit'] = true;
        } else {
            $_SESSION[$this->connectorId]['edit'] = false;
        }
        return $node;
    }

    public function setNode()
    {
        $node = $this->getNode();

        if ($node->node->size === NULL) {
            $this->apiClient->createContentNode($node->node->ref->id, DOCROOT . '/src/tools/onlyoffice/storage/templates/init.' . $_SESSION[$this->connectorId]['filetype'], \connector\tools\onlyoffice\OnlyOffice::getMimetype($_SESSION[$this->connectorId]['filetype']), 'MAIN_FILE_UPLOAD');
            $node = $this->apiClient->getNode($node->node->ref->id);
        }
        $_SESSION[$this->connectorId]['node'] = $node;
    }

    private function forwardToEditor()
    {
        header('Location: ' . WWWURL . '/src/tools/onlyoffice/doceditor.php?id=' . $this->connectorId . '&ref=' . base64_encode($_SESSION[$this->connectorId]['node']->node->properties->{'virtual:permalink'}[0]));
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
