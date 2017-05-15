<?php

class OnlyOffice extends EduRestClient {

    private $nodeId = '';
    private $fileType = '';

    public function __construct() {

		//todo check params

        $this->nodeId = $_REQUEST['node'];
        $this->fileType = $_REQUEST['filetype'];

		//if called from index / not from oo ajax script
		if(!empty($this->nodeId)) {
			$this -> setPerson();
			$this -> setNode();
			$this -> forwardToEditor();
		}
    }

	private function setPerson() {
		$_SESSION['person'] = $this->getPerson();
	}

	private function forwardToEditor() {
        $_SESSION['fileUrl'] = $_SESSION['node']->node->downloadUrl . '&accessToken=' . $_REQUEST['accessToken'];
        $_SESSION['fileType'] = $this->fileType;
        header('Location: ' . WWWURL . EDITORPATH);
		exit();
	}

    private function setNode() {
        try {
            $node = $this->getNode($this->nodeId);
            if(in_array('Write', $node->node->access)) {
                $_SESSION['edit'] = true;
            } else {
                $_SESSION['edit'] = false;
            }

            if($node->node->size === NULL) {
                $this -> createContentNode($this->nodeId, STORAGEPATH . '/templates/init.' . $this->fileType, $this -> getMimetype($this->fileType));
                $node = $this->getNode($this->nodeId);
            }

            $_SESSION['node'] = $node;
        } catch (Exception $e) {
            error_log($e);
            return false;
        }
    }

	public function getMimetype($doctype) {
        switch($doctype) {
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

    public function run() {
        $this -> showDialog();
    }

    public function showDialog() {
        include('./view/onlyoffice.phtml');
    }
}
