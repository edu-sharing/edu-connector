<?php

define('EDITORURL', WWWURL . '/OnlineEditorsExamplePHP/doceditor.php');
define('STORAGEFOLDER', 'storage');
define('STORAGEPATH', DOCROOT . '/OnlineEditorsExamplePHP/' . STORAGEFOLDER);
define('STORAGEURL', WWWURL . '/OnlineEditorsExamplePHP/' . STORAGEFOLDER);


require_once 'EduRestClient.php';

class OnlyOfficeConnector extends EduRestClient {
	
	const TOOL = 'edu-tool-onlyoffice';
    
    private $nodeId = '';
    private $title = '';
	private $doctype = '';
    
    public function __construct() {
		
		if(isset($_REQUEST['doctype']))
			$this -> doctype = $_REQUEST['doctype'];
		
		if(isset($_REQUEST['createdocument']) && $_REQUEST['createdocument'] == 'yes' && isset($_REQUEST['title']) && !empty($_REQUEST['title'])){
			
			$fileName = $this -> createEmptyDocument($_REQUEST['title']);
			$this -> forwardToEditor($fileName);
		}
		
		if(isset($_REQUEST['nodeid'])) {
			$fileName = $this -> getFile($_REQUEST['nodeid']);
			$this -> forwardToEditor($fileName);
		}
    }
	
	private function forwardToEditor($fileName) {
		header('Location: ' . EDITORURL . '?fileUrl=' . urlencode(STORAGEURL . '/' . $fileName));
		exit();
	}
	
	 private function getFile($nodeId) {
                    
        try {       
            $timestamp = round(microtime(true) * 1000);
            $signData = $nodeId . $timestamp;
            include('keyPair.php');       
            $pkeyid = openssl_get_privatekey($private);      
            openssl_sign($signData, $signature, $pkeyid);
            $signature = urlencode(base64_encode($signature));
            openssl_free_key($pkeyid); 
            $contentUrl = CONTENT_URL;
            $contentUrl .= '?appId=' . APP_ID;
            $contentUrl .= '&nodeId=' . $nodeId;
            $contentUrl .= '&timeStamp=' . $timestamp;
            $contentUrl .= '&authToken=' . $signature;

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
            
			$fileName = $this -> getUniquifier() . $nodeId . '.' . $this -> doctype;
			
            $handle = fopen(STORAGEPATH . '/' . $fileName , 'w');
            fwrite($handle, $content);
            fclose($handle);

			return $fileName;

        } catch (Exception $e) {
            error_log($e);   
            return false;
        }
    }
	
	private function getUniquifier($new = false) {
		if($new)
			return 'M_INIT_';
		else
			return 'M_EDIT_';
	}
	
	private function createEmptyDocument($title) {
		try {
			$fileName = $this -> getUniquifier(true) . $title . '.' . $this -> doctype;
			copy(STORAGEPATH . '/templates/init.' . $this -> doctype, STORAGEPATH . '/' . $fileName);
			return $fileName;
		} catch(Exception $e) {
			error_log($e);
			return false;
		}
	}
	
	public function saveDocument($storagePath) {

		try {
	
			if(strpos($storagePath, 'M_INIT_') !== false) {
				$filenameArr = explode('M_INIT_', $storagePath);
				$nodeId = $this -> createNode($filenameArr[1]);
			} else if(strpos($storagePath, 'M_EDIT_') !== false) {
				$filenameArr = explode('M_EDIT_', $storagePath);
				$pos = strrpos($filenameArr[1], '.');
				if ($pos !== false)
					$nodeId = substr($filenameArr[1], 0, $pos );
			}
			
			if(empty($nodeId)) {
				error_log('No valid nodeId');
				exit();
			}

			switch(array_pop(explode('.', $storagePath))) {
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
					exit();
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
