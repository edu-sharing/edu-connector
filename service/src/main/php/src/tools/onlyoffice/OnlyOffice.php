<?php

namespace connector\tools\OnlyOffice;

use Slim\Http\Response;

class OnlyOffice extends \connector\lib\Tool {

    private bool $isNewDocument = true;

    public function run(Response $response) {
        //$_SESSION[$this->connectorId]['fileUrl'] = $_SESSION[$this->connectorId]['node']->node->downloadUrl . '&ticket=' . $_SESSION[$this->connectorId]['ticket'];
        $_SESSION[$this->connectorId]['fileUrl'] = WWWURL . '/oo-content?sessionId=' . $_SESSION[$this->connectorId]['sessionId'] . '&downloadUrl=' . $_SESSION[$this->connectorId]['node']->node->downloadUrl;
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
        /*Starting with repo 5.1 repository sends real node id, check is required */
        if(in_array('ccm:collection_io_reference', $node->node->aspects)) {
            try {
                $originalId = $node->node->properties->{'ccm:original'}[0];
                $originalNode = $this->apiClient->getNode($originalId);
                if ($this->apiClient->isWritable($node)) {
                    $_SESSION[$this->connectorId]['edit'] = true;
                } else {
                    $_SESSION[$this->connectorId]['edit'] = false;
                }
                return $originalNode;
            } catch (\Exception $e) {
                $this->log->info('No accesss to original object ('.$e->getCode().')');
                $_SESSION[$this->connectorId]['edit'] = false;
                return $node;
            }
        }
        if ($this->apiClient->isWritable($node)) {
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
        } else {
            $this->isNewDocument = false;
            $this->resolveProtectedFiletype($node);
        }
        $_SESSION[$this->connectorId]['node'] = $node;
    }

    /*
     * Tika reports all encrypted OOXML files (docx, xlsx, pptx) as application/x-tika-ooxml-protected,
     * so the filetype sent by the repository is ambiguous. Use the file extension of the node instead.
     */
    private function resolveProtectedFiletype($node)
    {
        if (($_SESSION[$this->connectorId]['mimetype'] ?? null) !== 'application/x-tika-ooxml-protected') {
            return;
        }
        $extension = strtolower(pathinfo($node->node->name ?? '', PATHINFO_EXTENSION));
        if (in_array($extension, ['docx', 'xlsx', 'pptx'], true)) {
            $_SESSION[$this->connectorId]['filetype'] = $extension;
        }
    }

    private function forwardToEditor()
    {
        $location = WWWURL . '/src/tools/onlyoffice/doceditor.php?id=' . $this->connectorId . '&ref=' . base64_encode($_SESSION[$this->connectorId]['node']->node->properties->{'virtual:permalink'}[0]);
        if ($this->isNewDocument) {
            $location .= '&initDoc=true';
        }
        if (!empty($_SESSION[$this->connectorId]['preferEdit'])) {
            $location .= '&requestEdit=true';
        }
        header('Location: ' . $location);
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
