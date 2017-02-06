<?php

require_once 'EduRestClient.php';

class OnlyOfficeConnector extends EduRestClient {
	
    public function __construct() {

		//todo check params

		//if called from index / not from oo ajax script
		if(isset($_REQUEST['node'])) {
			$this -> setPerson();
			$this -> getFile($_REQUEST['node'], $_REQUEST['filetype']);
			$this -> forwardToEditor($_REQUEST['node'], $_REQUEST['filetype']);
		}
    }

	private function setPerson() {
		$_SESSION['person'] = $this->getPerson();
	}

	private function forwardToEditor($nodeId, $doctype) {
		$_SESSION['fileUrl'] = STORAGEURL . '/' . $nodeId . '.' . $doctype;
		include(EDITORPATH);
		exit();
	}

	 private function getFile($nodeId, $doctype) {

	     try {
			$node = $this->getNode($nodeId);

			if(!in_array('Write', $node->node->access)) {
				echo 'No writing permission for this node.';
				exit();
			}

			$_SESSION['node'] = $node;

			//node has no content -> create new document
			if($node->node->size === NULL) {
				$this -> createEmptyDocument($nodeId, $doctype);
			} else {
				$this -> fetchFileFromRepository($node, $nodeId, $doctype);
			}
        } catch (Exception $e) {
            error_log($e);
            return false;
        }
    }

	private function fetchFileFromRepository($node, $nodeId, $doctype) {
		$contentUrl = $node->node->downloadUrl . '&accessToken=' . $_REQUEST['accessToken'];

		$handle = fopen($contentUrl, "rb");
		if($handle === false) {
			error_log('Error opening ' . $contentUrl);
		}
		$content = stream_get_contents($handle);
		fclose($handle);
		if($content === false) {
			error_log('Error fetching content.');
			echo 'Could not fetch content';
			exit();
		}
		$handle = fopen(STORAGEPATH . '/' . $nodeId . '.' . $doctype , 'w');
		fwrite($handle, $content);
		fclose($handle);
	}

	private function createEmptyDocument($nodeId, $doctype) {
		try {
			copy(STORAGEPATH . '/templates/init.' . $doctype, STORAGEPATH . '/' . $nodeId . '.' . $doctype);
		} catch(Exception $e) {
			error_log($e);
			return false;
		}
	}

	public function saveDocument($storagePath) {
		try {
			$storagePathParts = explode('/', $storagePath);
			$doc = end($storagePathParts);
			$docExpl = explode('.', $doc);
			$nodeId = $docExpl[0];
			$doctype = $docExpl[1];

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

			$this -> createContentNode($nodeId, $storagePath, $mimetype);

			unlink($storagePath);

		} catch(Exception $e) {
			error_log('Error saving document ' . $nodeId);
		}

	}

    public function run() {
        $this -> showDialog();
    }

    public function showDialog() {
        include('./view/onlyoffice.phtml');
    }
}
